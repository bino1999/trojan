<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
	/* Expenses Report Styling */
	.table-responsive {
		max-height: 600px;
		overflow-y: auto;
	}
	
	/* Ensure proper column alignment */
	#expensesTable th,
	#expensesTable td {
		vertical-align: middle;
		white-space: nowrap;
	}
	
	/* Make some columns wider for better readability */
	#expensesTable th:nth-child(1),
	#expensesTable td:nth-child(1) {
		min-width: 80px;
	}
	
	#expensesTable th:nth-child(2),
	#expensesTable td:nth-child(2) {
		min-width: 200px;
		white-space: normal;
	}
	
	#expensesTable th:nth-child(3),
	#expensesTable td:nth-child(3) {
		min-width: 150px;
	}
	
	#expensesTable th:nth-child(4),
	#expensesTable td:nth-child(4) {
		min-width: 120px;
	}
	
	#expensesTable th:nth-child(5),
	#expensesTable td:nth-child(5) {
		min-width: 100px;
	}
	
	/* Numeric columns - right align */
	#expensesTable th:nth-child(6),
	#expensesTable td:nth-child(6) {
		text-align: right;
		min-width: 100px;
	}
	
	/* Status column */
	#expensesTable th:nth-child(7),
	#expensesTable td:nth-child(7) {
		min-width: 100px;
		text-align: center;
	}
	
	/* Payment method column */
	#expensesTable th:nth-child(8),
	#expensesTable td:nth-child(8) {
		min-width: 120px;
	}
	
	/* Employee column */
	#expensesTable th:nth-child(9),
	#expensesTable td:nth-child(9) {
		min-width: 150px;
	}
	
	/* Style all tables consistently */
	.table th,
	.table td {
		vertical-align: middle;
	}
	
	/* Category analysis table styling */
	#categoryTable th:nth-child(2),
	#categoryTable td:nth-child(2),
	#categoryTable th:nth-child(3),
	#categoryTable td:nth-child(3),
	#categoryTable th:nth-child(4),
	#categoryTable td:nth-child(4),
	#categoryTable th:nth-child(5),
	#categoryTable td:nth-child(5),
	#categoryTable th:nth-child(6),
	#categoryTable td:nth-child(6) {
		text-align: right;
	}
	
	/* Employee analysis table styling */
	#employeeTable th:nth-child(2),
	#employeeTable td:nth-child(2),
	#employeeTable th:nth-child(3),
	#employeeTable td:nth-child(3),
	#employeeTable th:nth-child(4),
	#employeeTable td:nth-child(4) {
		text-align: right;
	}
	
	/* Responsive adjustments */
	@media (max-width: 1200px) {
		#expensesTable th:nth-child(2),
		#expensesTable td:nth-child(2) {
			min-width: 150px;
		}
	}
	
	@media (max-width: 992px) {
		.table-responsive {
			font-size: 0.875rem;
		}
	}
</style>

<div class="page-content">
	<div class="container-fluid">
		<!-- Header Section -->
		<div class="row mb-4">
			<div class="col-12">
				<div class="d-flex justify-content-between align-items-center">
					<div>
						<h4 class="mb-1">💰 Comprehensive Expenses Report</h4>
						<p class="text-muted mb-0">Detailed expense analysis and financial insights</p>
					</div>
					<div>
						<button id="btnRefreshExpenses" class="btn btn-primary me-2">
							<i class="fas fa-sync-alt"></i> Refresh
						</button>
						<button id="btnTestDatabase" class="btn btn-info me-2">
							<i class="fas fa-database"></i> Test DB
						</button>
						<button id="btnExpensesPdf" class="btn btn-success">
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
							<div class="col-md-2">
								<label class="form-label">Date From</label>
								<input type="date" id="dateFrom" class="form-control" value="<?= date('Y-m-01') ?>">
							</div>
							<div class="col-md-2">
								<label class="form-label">Date To</label>
								<input type="date" id="dateTo" class="form-control" value="<?= date('Y-m-d') ?>">
							</div>
							<div class="col-md-2">
								<label class="form-label">Category</label>
								<select id="categoryFilter" class="form-select">
									<option value="">All Categories</option>
								</select>
							</div>
							<div class="col-md-2">
								<label class="form-label">Employee</label>
								<select id="employeeFilter" class="form-select">
									<option value="">All Employees</option>
								</select>
							</div>
							<div class="col-md-3">
								<label class="form-label">Search</label>
								<input type="text" id="searchInput" class="form-control" placeholder="Description, receipt number, or employee...">
							</div>
							<div class="col-md-1">
								<label class="form-label">&nbsp;</label>
								<button id="btnApplyFilters" class="btn btn-primary w-100">
									<i class="fas fa-search"></i>
								</button>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Summary Cards -->
		<div class="row mb-4">
			<div class="col-md-3">
				<div class="card bg-primary text-white">
					<div class="card-body">
						<div class="d-flex justify-content-between">
							<div>
								<h4 class="mb-0" id="totalExpenses">$0.00</h4>
								<p class="mb-0">Total Expenses</p>
							</div>
							<div class="align-self-center">
								<i class="fas fa-dollar-sign fa-2x"></i>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-md-3">
				<div class="card bg-info text-white">
					<div class="card-body">
						<div class="d-flex justify-content-between">
							<div>
								<h4 class="mb-0" id="totalCount">0</h4>
								<p class="mb-0">Total Transactions</p>
							</div>
							<div class="align-self-center">
								<i class="fas fa-receipt fa-2x"></i>
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
								<h4 class="mb-0" id="avgAmount">$0.00</h4>
								<p class="mb-0">Average Amount</p>
							</div>
							<div class="align-self-center">
								<i class="fas fa-chart-line fa-2x"></i>
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
								<h4 class="mb-0" id="topCategory">-</h4>
								<p class="mb-0">Top Category</p>
							</div>
							<div class="align-self-center">
								<i class="fas fa-tag fa-2x"></i>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Main Content -->
		<div class="row">
			<div class="col-12">
				<div class="card">
					<div class="card-header">
						<ul class="nav nav-tabs card-header-tabs" id="expensesTab" role="tablist">
							<li class="nav-item" role="presentation">
								<button class="nav-link active" id="expenses-tab" data-bs-toggle="tab" data-bs-target="#expenses" type="button" role="tab">
									<i class="fas fa-list"></i> All Expenses
								</button>
							</li>
							<li class="nav-item" role="presentation">
								<button class="nav-link" id="category-tab" data-bs-toggle="tab" data-bs-target="#category" type="button" role="tab">
									<i class="fas fa-tags"></i> Category Analysis
								</button>
							</li>
							<li class="nav-item" role="presentation">
								<button class="nav-link" id="employee-tab" data-bs-toggle="tab" data-bs-target="#employee" type="button" role="tab">
									<i class="fas fa-users"></i> Employee Analysis
								</button>
							</li>
							<li class="nav-item" role="presentation">
								<button class="nav-link" id="trends-tab" data-bs-toggle="tab" data-bs-target="#trends" type="button" role="tab">
									<i class="fas fa-chart-line"></i> Monthly Trends
								</button>
							</li>
						</ul>
					</div>
					<div class="card-body">
						<div class="tab-content" id="expensesTabContent">
							<!-- All Expenses Tab -->
							<div class="tab-pane fade show active" id="expenses" role="tabpanel">
								<div class="table-responsive">
									<table class="table table-hover" id="expensesTable">
										<thead class="table-dark">
											<tr>
												<th>Date</th>
												<th>Description</th>
												<th>Category</th>
												<th>Receipt #</th>
												<th>Payment Method</th>
												<th>Amount</th>
												<th>Status</th>
												<th>Method</th>
												<th>Employee</th>
											</tr>
										</thead>
										<tbody id="expensesTableBody">
											<tr>
												<td colspan="9" class="text-center">
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
							<div class="tab-pane fade" id="category" role="tabpanel">
								<div class="table-responsive">
									<table class="table table-hover" id="categoryTable">
										<thead class="table-dark">
											<tr>
												<th>Category</th>
												<th>Total Expenses</th>
												<th>Total Amount</th>
												<th>Average Amount</th>
												<th>Min Amount</th>
												<th>Max Amount</th>
											</tr>
										</thead>
										<tbody id="categoryTableBody">
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

							<!-- Employee Analysis Tab -->
							<div class="tab-pane fade" id="employee" role="tabpanel">
								<div class="table-responsive">
									<table class="table table-hover" id="employeeTable">
										<thead class="table-dark">
											<tr>
												<th>Employee</th>
												<th>Employee ID</th>
												<th>Total Expenses</th>
												<th>Total Amount</th>
												<th>Average Amount</th>
											</tr>
										</thead>
										<tbody id="employeeTableBody">
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

							<!-- Monthly Trends Tab -->
							<div class="tab-pane fade" id="trends" role="tabpanel">
								<div class="row">
									<div class="col-12">
										<canvas id="trendsChart" height="100"></canvas>
									</div>
								</div>
								<div class="row mt-4">
									<div class="col-12">
										<div class="table-responsive">
											<table class="table table-hover" id="trendsTable">
												<thead class="table-dark">
													<tr>
														<th>Month</th>
														<th>Total Expenses</th>
														<th>Total Amount</th>
														<th>Average per Transaction</th>
													</tr>
												</thead>
												<tbody id="trendsTableBody">
													<tr>
														<td colspan="4" class="text-center">
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
	</div>
</div>

<script>
	// Global variables
	let currentFilters = {
		date_from: document.getElementById('dateFrom').value,
		date_to: document.getElementById('dateTo').value,
		category: '',
		employee: '',
		search: ''
	};

	// Initialize on page load
	document.addEventListener('DOMContentLoaded', function() {
		try {
			loadExpensesData();
			loadFilterOptions();
			setupEventListeners();
		} catch (error) {
			console.error('Error initializing expenses report:', error);
			alert('Error initializing expenses report. Please refresh the page.');
		}
	});

	// Setup event listeners
	function setupEventListeners() {
		document.getElementById('btnApplyFilters').addEventListener('click', applyFilters);
		document.getElementById('btnRefreshExpenses').addEventListener('click', loadExpensesData);
		document.getElementById('btnTestDatabase').addEventListener('click', testDatabase);
		document.getElementById('btnExpensesPdf').addEventListener('click', exportPdf);
		
		// Real-time search
		document.getElementById('searchInput').addEventListener('input', debounce(applyFilters, 500));
	}

	// Load expenses data
	function loadExpensesData() {
		showLoading();
		console.log('Loading expenses data with filters:', currentFilters);
		
		fetch('<?= base_url('reports/expensesData'); ?>', {
			method: 'POST',
			headers: {'Content-Type': 'application/x-www-form-urlencoded'},
			body: new URLSearchParams(currentFilters)
		})
		.then(r => {
			console.log('Response status:', r.status);
			if (!r.ok) {
				throw new Error(`HTTP error! status: ${r.status}`);
			}
			return r.json();
		})
		.then(data => {
			console.log('Expenses data response:', data); // Debug log
			if (data.status !== 'success') {
				console.error('Expenses data error:', data);
				alert('Failed to load expenses data: ' + (data.message || 'Unknown error'));
				return;
			}

			// Check if data exists
			if (!data.data) {
				console.error('No data in response');
				alert('No data received from server');
				return;
			}

			// Update summary cards
			const summary = data.data.summary || {};
			document.getElementById('totalExpenses').textContent = formatCurrency(summary.total_expenses || 0);
			document.getElementById('totalCount').textContent = formatNumber(summary.total_count || 0);
			document.getElementById('avgAmount').textContent = formatCurrency(summary.avg_amount || 0);
			document.getElementById('topCategory').textContent = summary.top_category ? summary.top_category.category_name : '-';

			// Update tables
			updateExpensesTable(data.data.expenses_data || []);
			updateCategoryTable(data.data.category_analysis || []);
			updateEmployeeTable(data.data.employee_analysis || []);
			updateTrendsTable(data.data.monthly_trends || []);
			updateTrendsChart(data.data.monthly_trends || []);
		})
		.catch(error => {
			console.error('Server error:', error);
			alert('Server error occurred while loading expenses data. Please try again.');
		});
	}

	// Load filter options
	function loadFilterOptions() {
		// Load categories
		fetch('<?= base_url('reports/getCategories'); ?>')
		.then(r => r.json())
		.then(response => {
			const categorySelect = document.getElementById('categoryFilter');
			categorySelect.innerHTML = '<option value="">All Categories</option>';
			if (response.status === 'success' && response.data) {
				response.data.forEach(category => {
					categorySelect.innerHTML += `<option value="${category.expensesCategoryId}">${category.expensesCategoryName}</option>`;
				});
			}
		})
		.catch(() => {
			// Fallback if the endpoint doesn't exist
			const categorySelect = document.getElementById('categoryFilter');
			categorySelect.innerHTML = '<option value="">All Categories</option>';
		});

		// Load employees - using a simple query since we know the structure
		fetch('<?= base_url('reports/getEmployees'); ?>')
		.then(r => r.json())
		.then(response => {
			const employeeSelect = document.getElementById('employeeFilter');
			employeeSelect.innerHTML = '<option value="">All Employees</option>';
			if (response.status === 'success' && response.data) {
				response.data.forEach(employee => {
					employeeSelect.innerHTML += `<option value="${employee.UserID}">${employee.FirstName} ${employee.LastName}</option>`;
				});
			}
		})
		.catch(() => {
			// Fallback if the endpoint doesn't exist
			const employeeSelect = document.getElementById('employeeFilter');
			employeeSelect.innerHTML = '<option value="">All Employees</option>';
		});
	}

	// Apply filters
	function applyFilters() {
		currentFilters = {
			date_from: document.getElementById('dateFrom').value,
			date_to: document.getElementById('dateTo').value,
			category: document.getElementById('categoryFilter').value,
			employee: document.getElementById('employeeFilter').value,
			search: document.getElementById('searchInput').value
		};
		loadExpensesData();
	}

	// Update expenses table
	function updateExpensesTable(expensesData) {
		const tbody = document.getElementById('expensesTableBody');
		if (expensesData.length === 0) {
			tbody.innerHTML = '<tr><td colspan="9" class="text-center text-muted">No expenses found</td></tr>';
			return;
		}

		tbody.innerHTML = expensesData.map(expense => `
			<tr>
				<td>${new Date(expense.expense_date).toLocaleDateString()}</td>
				<td>
					<div class="fw-bold">${expense.description || '-'}</div>
					<small class="text-muted">ID: ${expense.expense_id}</small>
				</td>
				<td>${expense.category_name || '-'}</td>
				<td>${expense.receipt_number || '-'}</td>
				<td>${expense.payment_method || '-'}</td>
				<td class="text-end fw-bold">${formatCurrency(expense.amount)}</td>
				<td>${getStatusBadge(expense.status)}</td>
				<td>${expense.payment_method || '-'}</td>
				<td>${expense.employee_name || '-'}</td>
			</tr>
		`).join('');
	}

	// Update category analysis table
	function updateCategoryTable(categoryData) {
		const tbody = document.getElementById('categoryTableBody');
		if (categoryData.length === 0) {
			tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">No category data found</td></tr>';
			return;
		}

		tbody.innerHTML = categoryData.map(category => `
			<tr>
				<td class="fw-bold">${category.category_name || 'Uncategorized'}</td>
				<td class="text-end">${formatNumber(category.total_expenses)}</td>
				<td class="text-end fw-bold">${formatCurrency(category.total_amount)}</td>
				<td class="text-end">${formatCurrency(category.avg_amount)}</td>
				<td class="text-end">${formatCurrency(category.min_amount)}</td>
				<td class="text-end">${formatCurrency(category.max_amount)}</td>
			</tr>
		`).join('');
	}

	// Update employee analysis table
	function updateEmployeeTable(employeeData) {
		const tbody = document.getElementById('employeeTableBody');
		if (employeeData.length === 0) {
			tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No employee data found</td></tr>';
			return;
		}

		tbody.innerHTML = employeeData.map(employee => `
			<tr>
				<td class="fw-bold">${employee.employee_name || 'Unknown'}</td>
				<td>${employee.employee_id || '-'}</td>
				<td class="text-end">${formatNumber(employee.total_expenses)}</td>
				<td class="text-end fw-bold">${formatCurrency(employee.total_amount)}</td>
				<td class="text-end">${formatCurrency(employee.avg_amount)}</td>
			</tr>
		`).join('');
	}

	// Update trends table
	function updateTrendsTable(trendsData) {
		const tbody = document.getElementById('trendsTableBody');
		if (trendsData.length === 0) {
			tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">No trends data found</td></tr>';
			return;
		}

		tbody.innerHTML = trendsData.map(trend => `
			<tr>
				<td class="fw-bold">${formatMonth(trend.month)}</td>
				<td class="text-end">${formatNumber(trend.total_expenses)}</td>
				<td class="text-end fw-bold">${formatCurrency(trend.total_amount)}</td>
				<td class="text-end">${formatCurrency(trend.total_amount / trend.total_expenses)}</td>
			</tr>
		`).join('');
	}

	// Update trends chart
	function updateTrendsChart(trendsData) {
		// Check if Chart.js is loaded
		if (typeof Chart === 'undefined') {
			console.warn('Chart.js is not loaded. Skipping chart creation.');
			// Show a message in the chart area
			const chartContainer = document.getElementById('trendsChart').parentElement;
			chartContainer.innerHTML = '<div class="text-center text-muted p-4"><i class="fas fa-chart-line fa-3x mb-3"></i><p>Chart library not available</p></div>';
			return;
		}

		// Check if there's data to display
		if (!trendsData || trendsData.length === 0) {
			console.warn('No trends data available for chart.');
			const chartContainer = document.getElementById('trendsChart').parentElement;
			chartContainer.innerHTML = '<div class="text-center text-muted p-4"><i class="fas fa-chart-line fa-3x mb-3"></i><p>No trends data available</p></div>';
			return;
		}

		const ctx = document.getElementById('trendsChart').getContext('2d');
		
		// Destroy existing chart if it exists
		if (window.trendsChart && typeof window.trendsChart.destroy === 'function') {
			window.trendsChart.destroy();
		}

		const labels = trendsData.map(trend => formatMonth(trend.month));
		const amounts = trendsData.map(trend => parseFloat(trend.total_amount));
		const counts = trendsData.map(trend => parseInt(trend.total_expenses));

		try {
			window.trendsChart = new Chart(ctx, {
				type: 'line',
				data: {
					labels: labels,
					datasets: [{
						label: 'Total Amount ($)',
						data: amounts,
						borderColor: 'rgb(75, 192, 192)',
						backgroundColor: 'rgba(75, 192, 192, 0.2)',
						tension: 0.1,
						yAxisID: 'y'
					}, {
						label: 'Number of Expenses',
						data: counts,
						borderColor: 'rgb(255, 99, 132)',
						backgroundColor: 'rgba(255, 99, 132, 0.2)',
						tension: 0.1,
						yAxisID: 'y1'
					}]
				},
				options: {
					responsive: true,
					interaction: {
						mode: 'index',
						intersect: false,
					},
					scales: {
						x: {
							display: true,
							title: {
								display: true,
								text: 'Month'
							}
						},
						y: {
							type: 'linear',
							display: true,
							position: 'left',
							title: {
								display: true,
								text: 'Amount ($)'
							}
						},
						y1: {
							type: 'linear',
							display: true,
							position: 'right',
							title: {
								display: true,
								text: 'Number of Expenses'
							},
							grid: {
								drawOnChartArea: false,
							},
						}
					}
				}
			});
		} catch (error) {
			console.error('Error creating chart:', error);
			const chartContainer = document.getElementById('trendsChart').parentElement;
			chartContainer.innerHTML = '<div class="text-center text-muted p-4"><i class="fas fa-exclamation-triangle fa-3x mb-3"></i><p>Error creating chart</p></div>';
		}
	}

	// Export PDF
	function exportPdf() {
		const params = new URLSearchParams(currentFilters);
		window.open('<?= base_url('reports/expensesPdf'); ?>?' + params.toString(), '_blank');
	}

	// Test database connection
	function testDatabase() {
		console.log('Testing database connection...');
		
		fetch('<?= base_url('reports/testDatabase'); ?>')
		.then(r => r.json())
		.then(data => {
			console.log('Database test result:', data);
			if (data.status === 'success') {
				alert('Database Test Results:\n' +
					'Connection: ' + data.data.connection + '\n' +
					'Expenses Count: ' + data.data.expenses_count + '\n' +
					'Users Count: ' + data.data.users_count + '\n' +
					'Categories Count: ' + data.data.categories_count + '\n' +
					'Tables: ' + data.data.tables.join(', '));
			} else {
				alert('Database test failed: ' + data.message);
			}
		})
		.catch(error => {
			console.error('Database test error:', error);
			alert('Database test failed: ' + error.message);
		});
	}

	// Utility functions
	function formatCurrency(amount) {
		return new Intl.NumberFormat('en-US', {
			style: 'currency',
			currency: 'USD'
		}).format(amount || 0);
	}

	function formatNumber(number) {
		return new Intl.NumberFormat('en-US').format(number || 0);
	}

	function formatMonth(monthString) {
		const [year, month] = monthString.split('-');
		const date = new Date(year, month - 1);
		return date.toLocaleDateString('en-US', { year: 'numeric', month: 'long' });
	}

	function getStatusBadge(status) {
		const badges = {
			'pending': '<span class="badge bg-warning">Pending</span>',
			'approved': '<span class="badge bg-success">Approved</span>',
			'rejected': '<span class="badge bg-danger">Rejected</span>',
			'paid': '<span class="badge bg-info">Paid</span>'
		};
		return badges[status] || '<span class="badge bg-secondary">Unknown</span>';
	}

	function showLoading() {
		document.getElementById('expensesTableBody').innerHTML = '<tr><td colspan="9" class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></td></tr>';
		document.getElementById('categoryTableBody').innerHTML = '<tr><td colspan="6" class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></td></tr>';
		document.getElementById('employeeTableBody').innerHTML = '<tr><td colspan="5" class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></td></tr>';
		document.getElementById('trendsTableBody').innerHTML = '<tr><td colspan="4" class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></td></tr>';
	}

	function hideLoading() {
		document.getElementById('expensesTableBody').innerHTML = '<tr><td colspan="9" class="text-center text-muted">No data available</td></tr>';
		document.getElementById('categoryTableBody').innerHTML = '<tr><td colspan="6" class="text-center text-muted">No data available</td></tr>';
		document.getElementById('employeeTableBody').innerHTML = '<tr><td colspan="5" class="text-center text-muted">No data available</td></tr>';
		document.getElementById('trendsTableBody').innerHTML = '<tr><td colspan="4" class="text-center text-muted">No data available</td></tr>';
	}

	function debounce(func, wait) {
		let timeout;
		return function executedFunction(...args) {
			const later = () => {
				clearTimeout(timeout);
				func(...args);
			};
			clearTimeout(timeout);
			timeout = setTimeout(later, wait);
		};
	}
</script>
