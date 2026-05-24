<style>
	/* Stock Report Table Styling */
	.table-responsive {
		max-height: 600px;
		overflow-y: auto;
	}
	
	/* Ensure proper column alignment */
	#stockTable th,
	#stockTable td {
		vertical-align: middle;
		white-space: nowrap;
	}
	
	/* Make some columns wider for better readability */
	#stockTable th:nth-child(1),
	#stockTable td:nth-child(1) {
		min-width: 200px;
		white-space: normal;
	}
	
	#stockTable th:nth-child(2),
	#stockTable td:nth-child(2) {
		min-width: 120px;
	}
	
	#stockTable th:nth-child(3),
	#stockTable td:nth-child(3) {
		min-width: 150px;
	}
	
	#stockTable th:nth-child(4),
	#stockTable td:nth-child(4) {
		min-width: 100px;
	}
	
	#stockTable th:nth-child(5),
	#stockTable td:nth-child(5) {
		min-width: 120px;
	}
	
	/* Numeric columns - right align */
	#stockTable th:nth-child(6),
	#stockTable td:nth-child(6),
	#stockTable th:nth-child(7),
	#stockTable td:nth-child(7),
	#stockTable th:nth-child(9),
	#stockTable td:nth-child(9),
	#stockTable th:nth-child(10),
	#stockTable td:nth-child(10),
	#stockTable th:nth-child(11),
	#stockTable td:nth-child(11) {
		text-align: right;
		min-width: 100px;
	}
	
	/* Status column */
	#stockTable th:nth-child(8),
	#stockTable td:nth-child(8) {
		min-width: 100px;
		text-align: center;
	}
	
	/* Location column */
	#stockTable th:nth-child(12),
	#stockTable td:nth-child(12) {
		min-width: 120px;
	}
	
	/* Responsive adjustments */
	@media (max-width: 1200px) {
		#stockTable th:nth-child(1),
		#stockTable td:nth-child(1) {
			min-width: 150px;
		}
	}
	
	@media (max-width: 992px) {
		.table-responsive {
			font-size: 0.875rem;
		}
	}
	
	/* Style all tables consistently */
	.table th,
	.table td {
		vertical-align: middle;
	}
	
	/* Movements table styling */
	#movementsTable th:nth-child(4),
	#movementsTable td:nth-child(4),
	#movementsTable th:nth-child(5),
	#movementsTable td:nth-child(5),
	#movementsTable th:nth-child(6),
	#movementsTable td:nth-child(6) {
		text-align: right;
	}
	
	/* Suppliers table styling */
	#suppliersTable th:nth-child(2),
	#suppliersTable td:nth-child(2),
	#suppliersTable th:nth-child(3),
	#suppliersTable td:nth-child(3),
	#suppliersTable th:nth-child(4),
	#suppliersTable td:nth-child(4),
	#suppliersTable th:nth-child(5),
	#suppliersTable td:nth-child(5) {
		text-align: right;
	}
	
	/* Categories table styling */
	#categoriesTable th:nth-child(2),
	#categoriesTable td:nth-child(2),
	#categoriesTable th:nth-child(3),
	#categoriesTable td:nth-child(3),
	#categoriesTable th:nth-child(4),
	#categoriesTable td:nth-child(4),
	#categoriesTable th:nth-child(5),
	#categoriesTable td:nth-child(5),
	#categoriesTable th:nth-child(6),
	#categoriesTable td:nth-child(6) {
		text-align: right;
	}
	
	/* Alerts table styling */
	#alertsTable th:nth-child(3),
	#alertsTable td:nth-child(3),
	#alertsTable th:nth-child(4),
	#alertsTable td:nth-child(4) {
		text-align: right;
	}
</style>

<div class="page-content">
	<div class="container-fluid">
		<!-- Header Section -->
		<div class="row mb-4">
			<div class="col-12">
				<div class="d-flex justify-content-between align-items-center">
					<div>
						<h4 class="mb-1">📊 Comprehensive Stock Report</h4>
						<p class="text-muted mb-0">Real-time inventory analysis and management</p>
					</div>
					<div>
						<button id="btnRefreshStock" class="btn btn-primary me-2">
							<i class="fas fa-sync-alt"></i> Refresh
						</button>
						<button id="btnStockPdf" class="btn btn-success">
							<i class="fas fa-file-pdf"></i> Export PDF
						</button>
					</div>
				</div>
			</div>
		</div>

		<!-- Filters Section -->
		<div class="row mb-4">
			<div class="col-12">
				<div class="card">
					<div class="card-header">
						<h6 class="mb-0"><i class="fas fa-filter"></i> Filters & Search</h6>
					</div>
					<div class="card-body">
						<div class="row g-3">
							<div class="col-md-3">
								<label class="form-label">Search Products</label>
								<input type="text" id="searchInput" class="form-control" placeholder="Product name, SKU, or barcode...">
							</div>
							<div class="col-md-2">
								<label class="form-label">Category</label>
								<select id="categoryFilter" class="form-select">
									<option value="">All Categories</option>
								</select>
							</div>
							<div class="col-md-2">
								<label class="form-label">Brand</label>
								<select id="brandFilter" class="form-select">
									<option value="">All Brands</option>
								</select>
							</div>
							<div class="col-md-2">
								<label class="form-label">Supplier</label>
								<select id="supplierFilter" class="form-select">
									<option value="">All Suppliers</option>
								</select>
							</div>
							<div class="col-md-2">
								<label class="form-label">Stock Status</label>
								<select id="stockStatusFilter" class="form-select">
									<option value="all">All Stock</option>
									<option value="normal">Normal</option>
									<option value="low">Low Stock</option>
									<option value="out">Out of Stock</option>
								</select>
							</div>
							<div class="col-md-1 d-flex align-items-end">
								<button id="btnApplyFilters" class="btn btn-outline-primary w-100">
									<i class="fas fa-search"></i>
								</button>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Summary Cards -->
		<div class="row mb-4" id="summaryCards">
			<div class="col-md-3">
				<div class="card bg-primary text-white">
					<div class="card-body">
						<div class="d-flex justify-content-between">
							<div>
								<h6 class="card-title">Total Products</h6>
								<h3 id="totalProducts">-</h3>
							</div>
							<div class="align-self-center">
								<i class="fas fa-boxes fa-2x opacity-75"></i>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-md-3">
				<div class="card bg-success text-white">
					<div class="card-body">
						<div class="d-flex justify-content-between">
							<div>
								<h6 class="card-title">Total Stock Value</h6>
								<h3 id="totalStockValue">-</h3>
							</div>
							<div class="align-self-center">
								<i class="fas fa-dollar-sign fa-2x opacity-75"></i>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-md-3">
				<div class="card bg-warning text-white">
					<div class="card-body">
						<div class="d-flex justify-content-between">
							<div>
								<h6 class="card-title">Low Stock Items</h6>
								<h3 id="lowStockCount">-</h3>
							</div>
							<div class="align-self-center">
								<i class="fas fa-exclamation-triangle fa-2x opacity-75"></i>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-md-3">
				<div class="card bg-danger text-white">
					<div class="card-body">
						<div class="d-flex justify-content-between">
							<div>
								<h6 class="card-title">Out of Stock</h6>
								<h3 id="outOfStockCount">-</h3>
							</div>
							<div class="align-self-center">
								<i class="fas fa-times-circle fa-2x opacity-75"></i>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Main Content Tabs -->
		<div class="row">
			<div class="col-12">
				<div class="card">
					<div class="card-header">
						<ul class="nav nav-tabs card-header-tabs" id="stockTabs" role="tablist">
							<li class="nav-item" role="presentation">
								<button class="nav-link active" id="current-stock-tab" data-bs-toggle="tab" data-bs-target="#current-stock" type="button" role="tab">
									<i class="fas fa-warehouse"></i> Current Stock
								</button>
							</li>
							<li class="nav-item" role="presentation">
								<button class="nav-link" id="movements-tab" data-bs-toggle="tab" data-bs-target="#movements" type="button" role="tab">
									<i class="fas fa-exchange-alt"></i> Stock Movements
								</button>
							</li>
							<li class="nav-item" role="presentation">
								<button class="nav-link" id="suppliers-tab" data-bs-toggle="tab" data-bs-target="#suppliers" type="button" role="tab">
									<i class="fas fa-truck"></i> Supplier Analysis
								</button>
							</li>
							<li class="nav-item" role="presentation">
								<button class="nav-link" id="categories-tab" data-bs-toggle="tab" data-bs-target="#categories" type="button" role="tab">
									<i class="fas fa-tags"></i> Category Analysis
								</button>
							</li>
							<li class="nav-item" role="presentation">
								<button class="nav-link" id="alerts-tab" data-bs-toggle="tab" data-bs-target="#alerts" type="button" role="tab">
									<i class="fas fa-bell"></i> Low Stock Alerts
								</button>
							</li>
						</ul>
					</div>
					<div class="card-body">
						<div class="tab-content" id="stockTabContent">
							<!-- Current Stock Tab -->
							<div class="tab-pane fade show active" id="current-stock" role="tabpanel">
								<div class="table-responsive">
									<table class="table table-hover" id="stockTable">
										<thead class="table-dark">
											<tr>
												<th>Product</th>
												<th>SKU/Barcode</th>
												<th>Category</th>
												<th>Brand</th>
												<th>Supplier</th>
												<th>Available Stock</th>
												<th>Reorder Level</th>
												<th>Status</th>
												<th>Cost Price</th>
												<th>Sale Price</th>
												<th>Stock Value</th>
												<th>Location</th>
											</tr>
										</thead>
										<tbody id="stockTableBody">
											<tr>
												<td colspan="12" class="text-center">
													<div class="spinner-border text-primary" role="status">
														<span class="visually-hidden">Loading...</span>
													</div>
												</td>
											</tr>
										</tbody>
									</table>
								</div>
							</div>

							<!-- Stock Movements Tab -->
							<div class="tab-pane fade" id="movements" role="tabpanel">
								<div class="table-responsive">
									<table class="table table-hover" id="movementsTable">
										<thead class="table-dark">
											<tr>
												<th>Date</th>
												<th>Type</th>
												<th>Product</th>
												<th>Quantity</th>
												<th>Unit Price</th>
												<th>Total Value</th>
												<th>Source</th>
												<th>Reference</th>
											</tr>
										</thead>
										<tbody id="movementsTableBody">
											<tr>
												<td colspan="8" class="text-center">
													<div class="spinner-border text-primary" role="status">
														<span class="visually-hidden">Loading...</span>
													</div>
												</td>
											</tr>
										</tbody>
									</table>
								</div>
							</div>

							<!-- Supplier Analysis Tab -->
							<div class="tab-pane fade" id="suppliers" role="tabpanel">
								<div class="table-responsive">
									<table class="table table-hover" id="suppliersTable">
										<thead class="table-dark">
											<tr>
												<th>Supplier</th>
												<th>Products</th>
												<th>Total Stock</th>
												<th>Stock Value</th>
												<th>Avg Cost Price</th>
											</tr>
										</thead>
										<tbody id="suppliersTableBody">
											<tr>
												<td colspan="5" class="text-center">
													<div class="spinner-border text-primary" role="status">
														<span class="visually-hidden">Loading...</span>
													</div>
												</td>
											</tr>
										</tbody>
									</table>
								</div>
							</div>

							<!-- Category Analysis Tab -->
							<div class="tab-pane fade" id="categories" role="tabpanel">
								<div class="table-responsive">
									<table class="table table-hover" id="categoriesTable">
										<thead class="table-dark">
											<tr>
												<th>Category</th>
												<th>Products</th>
												<th>Total Stock</th>
												<th>Cost Value</th>
												<th>Retail Value</th>
												<th>Profit Margin</th>
											</tr>
										</thead>
										<tbody id="categoriesTableBody">
											<tr>
												<td colspan="6" class="text-center">
													<div class="spinner-border text-primary" role="status">
														<span class="visually-hidden">Loading...</span>
													</div>
												</td>
											</tr>
										</tbody>
									</table>
								</div>
							</div>

							<!-- Low Stock Alerts Tab -->
							<div class="tab-pane fade" id="alerts" role="tabpanel">
								<div class="table-responsive">
									<table class="table table-hover" id="alertsTable">
										<thead class="table-dark">
											<tr>
												<th>Product</th>
												<th>SKU</th>
												<th>Current Stock</th>
												<th>Reorder Level</th>
												<th>Urgency</th>
												<th>Brand</th>
												<th>Category</th>
												<th>Supplier</th>
											</tr>
										</thead>
										<tbody id="alertsTableBody">
											<tr>
												<td colspan="8" class="text-center">
													<div class="spinner-border text-primary" role="status">
														<span class="visually-hidden">Loading...</span>
													</div>
												</td>
											</tr>
										</tbody>
									</table>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
(function(){
	let currentFilters = {
		category: '',
		brand: '',
		supplier: '',
		stock_status: 'all',
		search: ''
	};

	function formatCurrency(amount) {
		return 'Rs. ' + (Number(amount || 0)).toLocaleString(undefined, {
			minimumFractionDigits: 2,
			maximumFractionDigits: 2
		});
	}

	function formatNumber(num) {
		return (Number(num || 0)).toLocaleString();
	}

	function getStockStatusBadge(status) {
		const badges = {
			'normal': '<span class="badge bg-success">Normal</span>',
			'low_stock': '<span class="badge bg-warning">Low Stock</span>',
			'out_of_stock': '<span class="badge bg-danger">Out of Stock</span>'
		};
		return badges[status] || '<span class="badge bg-secondary">Unknown</span>';
	}

	function getUrgencyBadge(ratio) {
		if (ratio <= 0.2) return '<span class="badge bg-danger">Critical</span>';
		if (ratio <= 0.5) return '<span class="badge bg-warning">High</span>';
		if (ratio <= 0.8) return '<span class="badge bg-info">Medium</span>';
		return '<span class="badge bg-success">Low</span>';
	}

	function loadStockData() {
		fetch('<?= base_url('reports/stockData'); ?>', {
			method: 'POST',
			headers: {'Content-Type': 'application/x-www-form-urlencoded'},
			body: new URLSearchParams(currentFilters)
		})
		.then(r => r.json())
		.then(data => {
			if (data.status !== 'success') {
				console.error('Stock data error:', data);
				alert('Failed to load stock data: ' + (data.message || 'Unknown error'));
				return;
			}

			// Update summary cards
			const summary = data.data.stock_summary;
			document.getElementById('totalProducts').textContent = formatNumber(summary.total_products);
			document.getElementById('totalStockValue').textContent = formatCurrency(summary.total_value_retail);
			document.getElementById('lowStockCount').textContent = formatNumber(summary.low_stock_count);
			document.getElementById('outOfStockCount').textContent = formatNumber(summary.out_of_stock_count);

			// Update stock table
			updateStockTable(data.data.stock_data);
			updateMovementsTable(data.data.movement_data);
			updateSuppliersTable(data.data.supplier_data);
			updateCategoriesTable(data.data.category_data);
			updateAlertsTable(data.data.low_stock_alerts);
		})
		.catch(() => alert('Server error'));
	}

	function updateStockTable(stockData) {
		const tbody = document.getElementById('stockTableBody');
		if (stockData.length === 0) {
			tbody.innerHTML = '<tr><td colspan="12" class="text-center text-muted">No stock data found</td></tr>';
			return;
		}

		tbody.innerHTML = stockData.map(item => `
			<tr>
				<td>
					<div class="fw-bold">${item.product_name || '-'}</div>
					<small class="text-muted">${item.description || ''}</small>
				</td>
				<td>
					<div>${item.sku || '-'}</div>
					<small class="text-muted">${item.barcode || ''}</small>
				</td>
				<td>${item.category_name || '-'}</td>
				<td>${item.brand_name || '-'}</td>
				<td>${item.supplier_name || '-'}</td>
				<td class="text-end fw-bold">${formatNumber(item.available_stock)}</td>
				<td class="text-end">${formatNumber(item.reorder_level)}</td>
				<td>${getStockStatusBadge(item.stock_status)}</td>
				<td class="text-end">${formatCurrency(item.company_price)}</td>
				<td class="text-end">${formatCurrency(item.sale_price)}</td>
				<td class="text-end fw-bold">${formatCurrency(item.stock_value_retail)}</td>
				<td>
					${item.rack_no ? `Rack: ${item.rack_no}` : ''}
					${item.bin_no ? `<br>Bin: ${item.bin_no}` : ''}
				</td>
			</tr>
		`).join('');
	}

	function updateMovementsTable(movementsData) {
		const tbody = document.getElementById('movementsTableBody');
		if (movementsData.length === 0) {
			tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted">No movement data found</td></tr>';
			return;
		}

		tbody.innerHTML = movementsData.map(movement => `
			<tr>
				<td>${new Date(movement.movement_date).toLocaleDateString()}</td>
				<td>
					<span class="badge ${movement.movement_type === 'IN' ? 'bg-success' : 'bg-danger'}">
						${movement.movement_type}
					</span>
				</td>
				<td>${movement.product_name}</td>
				<td class="text-end">${formatNumber(movement.quantity)}</td>
				<td class="text-end">${formatCurrency(movement.company_price)}</td>
				<td class="text-end">${formatCurrency(movement.quantity * movement.company_price)}</td>
				<td>${movement.source}</td>
				<td>${movement.reference_id}</td>
			</tr>
		`).join('');
	}

	function updateSuppliersTable(suppliersData) {
		const tbody = document.getElementById('suppliersTableBody');
		if (suppliersData.length === 0) {
			tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No supplier data found</td></tr>';
			return;
		}

		tbody.innerHTML = suppliersData.map(supplier => `
			<tr>
				<td class="fw-bold">${supplier.supplier_name}</td>
				<td class="text-end">${formatNumber(supplier.total_products)}</td>
				<td class="text-end">${formatNumber(supplier.total_stock_quantity)}</td>
				<td class="text-end fw-bold">${formatCurrency(supplier.total_stock_value)}</td>
				<td class="text-end">${formatCurrency(supplier.avg_cost_price)}</td>
			</tr>
		`).join('');
	}

	function updateCategoriesTable(categoriesData) {
		const tbody = document.getElementById('categoriesTableBody');
		if (categoriesData.length === 0) {
			tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">No category data found</td></tr>';
			return;
		}

		tbody.innerHTML = categoriesData.map(category => {
			const profitMargin = category.total_stock_value_retail > 0 ? 
				((category.total_stock_value_retail - category.total_stock_value_cost) / category.total_stock_value_retail * 100) : 0;
			
			return `
				<tr>
					<td class="fw-bold">${category.category_name}</td>
					<td class="text-end">${formatNumber(category.total_products)}</td>
					<td class="text-end">${formatNumber(category.total_stock_quantity)}</td>
					<td class="text-end">${formatCurrency(category.total_stock_value_cost)}</td>
					<td class="text-end">${formatCurrency(category.total_stock_value_retail)}</td>
					<td class="text-end fw-bold ${profitMargin > 0 ? 'text-success' : 'text-danger'}">
						${profitMargin.toFixed(1)}%
					</td>
				</tr>
			`;
		}).join('');
	}

	function updateAlertsTable(alertsData) {
		const tbody = document.getElementById('alertsTableBody');
		if (alertsData.length === 0) {
			tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted">No low stock alerts</td></tr>';
			return;
		}

		tbody.innerHTML = alertsData.map(alert => {
			const ratio = alert.reorder_level > 0 ? alert.available_stock / alert.reorder_level : 0;
			return `
				<tr>
					<td class="fw-bold">${alert.product_name}</td>
					<td>${alert.sku || '-'}</td>
					<td class="text-end fw-bold">${formatNumber(alert.available_stock)}</td>
					<td class="text-end">${formatNumber(alert.reorder_level)}</td>
					<td>${getUrgencyBadge(ratio)}</td>
					<td>${alert.brand_name || '-'}</td>
					<td>${alert.category_name || '-'}</td>
					<td>${alert.supplier_name || '-'}</td>
				</tr>
			`;
		}).join('');
	}

	// Event listeners
	document.getElementById('btnRefreshStock').addEventListener('click', loadStockData);
	document.getElementById('btnApplyFilters').addEventListener('click', function() {
		currentFilters.search = document.getElementById('searchInput').value;
		currentFilters.category = document.getElementById('categoryFilter').value;
		currentFilters.brand = document.getElementById('brandFilter').value;
		currentFilters.supplier = document.getElementById('supplierFilter').value;
		currentFilters.stock_status = document.getElementById('stockStatusFilter').value;
		loadStockData();
	});

	document.getElementById('btnStockPdf').addEventListener('click', function() {
		const params = new URLSearchParams(currentFilters);
		window.open('<?= base_url('reports/stockPdf'); ?>?' + params.toString(), '_blank');
	});

	// Load initial data
	loadStockData();
})();
</script>
