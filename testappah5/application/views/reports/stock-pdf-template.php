<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Stock Report - <?= date('Y-m-d H:i:s') ?></title>
	<style>
		@page { 
			size: A4 landscape; 
			margin: 1cm; 
		}
		body { 
			font-family: Arial, sans-serif; 
			font-size: 9px; 
			line-height: 1.3;
			margin: 0;
			padding: 0;
		}
		.header {
			text-align: center;
			margin-bottom: 20px;
			border-bottom: 2px solid #333;
			padding-bottom: 10px;
		}
		.header h1 {
			margin: 0;
			font-size: 18px;
			color: #333;
		}
		.header h2 {
			margin: 5px 0 0 0;
			font-size: 12px;
			color: #666;
			font-weight: normal;
		}
		.summary-cards {
			display: flex;
			justify-content: space-between;
			margin-bottom: 20px;
		}
		.summary-card {
			background: #f8f9fa;
			border: 1px solid #dee2e6;
			padding: 10px;
			text-align: center;
			flex: 1;
			margin: 0 5px;
		}
		.summary-card h3 {
			margin: 0;
			font-size: 14px;
			color: #333;
		}
		.summary-card p {
			margin: 5px 0 0 0;
			font-size: 10px;
			color: #666;
		}
		.filters {
			background: #e9ecef;
			padding: 10px;
			margin-bottom: 20px;
			border-radius: 4px;
		}
		.filters h4 {
			margin: 0 0 10px 0;
			font-size: 12px;
			color: #333;
		}
		.filters p {
			margin: 2px 0;
			font-size: 9px;
			color: #666;
		}
		table { 
			width: 100%; 
			border-collapse: collapse; 
			margin-bottom: 20px;
		}
		th, td { 
			border: 1px solid #000; 
			padding: 4px 6px; 
			text-align: left;
		}
		th { 
			background: #343a40; 
			color: white; 
			font-weight: bold; 
			font-size: 8px;
		}
		tr:nth-child(even) {
			background: #f8f9fa;
		}
		.text-right {
			text-align: right;
		}
		.text-center {
			text-align: center;
		}
		.badge {
			padding: 2px 6px;
			border-radius: 3px;
			font-size: 7px;
			font-weight: bold;
		}
		.badge-success { background: #28a745; color: white; }
		.badge-warning { background: #ffc107; color: #212529; }
		.badge-danger { background: #dc3545; color: white; }
		.badge-info { background: #17a2b8; color: white; }
		.footer {
			margin-top: 30px;
			text-align: center;
			font-size: 8px;
			color: #666;
			border-top: 1px solid #ccc;
			padding-top: 10px;
		}
		.section-title {
			background: #6c757d;
			color: white;
			padding: 8px;
			margin: 20px 0 10px 0;
			font-weight: bold;
			font-size: 10px;
		}
	</style>
</head>
<body>
	<div class="header">
		<h1>📊 Troja Service - Comprehensive Stock Report</h1>
		<h2>Generated on <?= date('Y-m-d H:i:s') ?></h2>
	</div>
	
	<!-- Summary Cards -->
	<div class="summary-cards">
		<div class="summary-card">
			<h3><?= number_format($stock_summary->total_products ?? 0) ?></h3>
			<p>Total Products</p>
		</div>
		<div class="summary-card">
			<h3>Rs. <?= number_format($stock_summary->total_value_retail ?? 0, 2) ?></h3>
			<p>Total Stock Value</p>
		</div>
		<div class="summary-card">
			<h3><?= number_format($stock_summary->low_stock_count ?? 0) ?></h3>
			<p>Low Stock Items</p>
		</div>
		<div class="summary-card">
			<h3><?= number_format($stock_summary->out_of_stock_count ?? 0) ?></h3>
			<p>Out of Stock</p>
		</div>
	</div>

	<!-- Filters Applied -->
	<?php if (!empty($filters['search']) || !empty($filters['category']) || !empty($filters['brand']) || !empty($filters['supplier']) || !empty($filters['stock_status'])): ?>
	<div class="filters">
		<h4>Filters Applied:</h4>
		<?php if (!empty($filters['search'])): ?><p><strong>Search:</strong> <?= htmlspecialchars($filters['search']) ?></p><?php endif; ?>
		<?php if (!empty($filters['category'])): ?><p><strong>Category:</strong> <?= htmlspecialchars($filters['category']) ?></p><?php endif; ?>
		<?php if (!empty($filters['brand'])): ?><p><strong>Brand:</strong> <?= htmlspecialchars($filters['brand']) ?></p><?php endif; ?>
		<?php if (!empty($filters['supplier'])): ?><p><strong>Supplier:</strong> <?= htmlspecialchars($filters['supplier']) ?></p><?php endif; ?>
		<?php if (!empty($filters['stock_status']) && $filters['stock_status'] !== 'all'): ?><p><strong>Stock Status:</strong> <?= htmlspecialchars($filters['stock_status']) ?></p><?php endif; ?>
	</div>
	<?php endif; ?>

	<!-- Current Stock Table -->
	<div class="section-title">Current Stock Levels</div>
	<table>
		<thead>
			<tr>
				<th>Product</th>
				<th>SKU</th>
				<th>Category</th>
				<th>Brand</th>
				<th>Supplier</th>
				<th class="text-right">Stock</th>
				<th class="text-right">Reorder</th>
				<th>Status</th>
				<th class="text-right">Cost Price</th>
				<th class="text-right">Sale Price</th>
				<th class="text-right">Value</th>
			</tr>
		</thead>
		<tbody>
			<?php if (!empty($stock_data)): ?>
				<?php foreach ($stock_data as $item): ?>
				<tr>
					<td><?= htmlspecialchars($item->product_name ?? '-') ?></td>
					<td><?= htmlspecialchars($item->sku ?? '-') ?></td>
					<td><?= htmlspecialchars($item->category_name ?? '-') ?></td>
					<td><?= htmlspecialchars($item->brand_name ?? '-') ?></td>
					<td><?= htmlspecialchars($item->supplier_name ?? '-') ?></td>
					<td class="text-right"><?= number_format($item->available_stock ?? 0) ?></td>
					<td class="text-right"><?= number_format($item->reorder_level ?? 0) ?></td>
					<td>
						<?php 
						$status = $item->stock_status ?? 'normal';
						$badgeClass = $status === 'normal' ? 'badge-success' : ($status === 'low_stock' ? 'badge-warning' : 'badge-danger');
						$statusText = $status === 'normal' ? 'Normal' : ($status === 'low_stock' ? 'Low Stock' : 'Out of Stock');
						?>
						<span class="badge <?= $badgeClass ?>"><?= $statusText ?></span>
					</td>
					<td class="text-right">Rs. <?= number_format($item->company_price ?? 0, 2) ?></td>
					<td class="text-right">Rs. <?= number_format($item->sale_price ?? 0, 2) ?></td>
					<td class="text-right">Rs. <?= number_format($item->stock_value_retail ?? 0, 2) ?></td>
				</tr>
				<?php endforeach; ?>
			<?php else: ?>
				<tr><td colspan="11" class="text-center">No stock data available</td></tr>
			<?php endif; ?>
		</tbody>
	</table>

	<!-- Low Stock Alerts -->
	<?php if (!empty($low_stock_alerts)): ?>
	<div class="section-title">Low Stock Alerts</div>
	<table>
		<thead>
			<tr>
				<th>Product</th>
				<th>SKU</th>
				<th class="text-right">Current Stock</th>
				<th class="text-right">Reorder Level</th>
				<th>Brand</th>
				<th>Category</th>
				<th>Supplier</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($low_stock_alerts as $alert): ?>
			<tr>
				<td><?= htmlspecialchars($alert->product_name ?? '-') ?></td>
				<td><?= htmlspecialchars($alert->sku ?? '-') ?></td>
				<td class="text-right"><?= number_format($alert->available_stock ?? 0) ?></td>
				<td class="text-right"><?= number_format($alert->reorder_level ?? 0) ?></td>
				<td><?= htmlspecialchars($alert->brand_name ?? '-') ?></td>
				<td><?= htmlspecialchars($alert->category_name ?? '-') ?></td>
				<td><?= htmlspecialchars($alert->supplier_name ?? '-') ?></td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<?php endif; ?>

	<!-- Supplier Analysis -->
	<?php if (!empty($supplier_data)): ?>
	<div class="section-title">Supplier Analysis</div>
	<table>
		<thead>
			<tr>
				<th>Supplier</th>
				<th class="text-right">Products</th>
				<th class="text-right">Total Stock</th>
				<th class="text-right">Stock Value</th>
				<th class="text-right">Avg Cost</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($supplier_data as $supplier): ?>
			<tr>
				<td><?= htmlspecialchars($supplier->supplier_name ?? '-') ?></td>
				<td class="text-right"><?= number_format($supplier->total_products ?? 0) ?></td>
				<td class="text-right"><?= number_format($supplier->total_stock_quantity ?? 0) ?></td>
				<td class="text-right">Rs. <?= number_format($supplier->total_stock_value ?? 0, 2) ?></td>
				<td class="text-right">Rs. <?= number_format($supplier->avg_cost_price ?? 0, 2) ?></td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<?php endif; ?>

	<!-- Category Analysis -->
	<?php if (!empty($category_data)): ?>
	<div class="section-title">Category Analysis</div>
	<table>
		<thead>
			<tr>
				<th>Category</th>
				<th class="text-right">Products</th>
				<th class="text-right">Total Stock</th>
				<th class="text-right">Cost Value</th>
				<th class="text-right">Retail Value</th>
				<th class="text-right">Profit Margin</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($category_data as $category): ?>
			<?php 
			$profitMargin = $category->total_stock_value_retail > 0 ? 
				(($category->total_stock_value_retail - $category->total_stock_value_cost) / $category->total_stock_value_retail * 100) : 0;
			?>
			<tr>
				<td><?= htmlspecialchars($category->category_name ?? '-') ?></td>
				<td class="text-right"><?= number_format($category->total_products ?? 0) ?></td>
				<td class="text-right"><?= number_format($category->total_stock_quantity ?? 0) ?></td>
				<td class="text-right">Rs. <?= number_format($category->total_stock_value_cost ?? 0, 2) ?></td>
				<td class="text-right">Rs. <?= number_format($category->total_stock_value_retail ?? 0, 2) ?></td>
				<td class="text-right"><?= number_format($profitMargin, 1) ?>%</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<?php endif; ?>

	<div class="footer">
		<p>Generated on <?= date('Y-m-d H:i:s') ?> | Troja Service Management System</p>
		<p>This report contains real-time stock information and analysis</p>
	</div>
</body>
</html>
