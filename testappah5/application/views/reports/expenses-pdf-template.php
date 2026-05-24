<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Expenses Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        
        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #2c3e50;
        }
        
        .header p {
            margin: 5px 0 0 0;
            color: #7f8c8d;
        }
        
        .filters {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .filters h3 {
            margin: 0 0 10px 0;
            font-size: 14px;
            color: #495057;
        }
        
        .filter-row {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .filter-item {
            flex: 1;
            min-width: 150px;
        }
        
        .filter-label {
            font-weight: bold;
            color: #495057;
        }
        
        .summary-cards {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            gap: 15px;
        }
        
        .summary-card {
            flex: 1;
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            text-align: center;
            border-left: 4px solid #007bff;
        }
        
        .summary-card h4 {
            margin: 0 0 5px 0;
            font-size: 18px;
            color: #007bff;
        }
        
        .summary-card p {
            margin: 0;
            color: #6c757d;
            font-size: 12px;
        }
        
        .section {
            margin-bottom: 30px;
        }
        
        .section h3 {
            background-color: #343a40;
            color: white;
            padding: 10px 15px;
            margin: 0 0 15px 0;
            font-size: 14px;
            border-radius: 3px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        th, td {
            border: 1px solid #dee2e6;
            padding: 8px;
            text-align: left;
            vertical-align: top;
        }
        
        th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #495057;
            font-size: 11px;
        }
        
        td {
            font-size: 10px;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .fw-bold {
            font-weight: bold;
        }
        
        .text-muted {
            color: #6c757d;
        }
        
        .badge {
            display: inline-block;
            padding: 2px 6px;
            font-size: 9px;
            font-weight: bold;
            border-radius: 3px;
            color: white;
        }
        
        .badge-success { background-color: #28a745; }
        .badge-warning { background-color: #ffc107; color: #212529; }
        .badge-danger { background-color: #dc3545; }
        .badge-info { background-color: #17a2b8; }
        .badge-secondary { background-color: #6c757d; }
        
        .page-break {
            page-break-before: always;
        }
        
        .chart-placeholder {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 20px;
            text-align: center;
            color: #6c757d;
            margin: 20px 0;
        }
        
        .no-data {
            text-align: center;
            color: #6c757d;
            font-style: italic;
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>💰 Comprehensive Expenses Report</h1>
        <p>Generated on <?= date('F j, Y \a\t g:i A') ?></p>
    </div>

    <!-- Filters Section -->
    <div class="filters">
        <h3>Report Filters</h3>
        <div class="filter-row">
            <div class="filter-item">
                <span class="filter-label">Date Range:</span><br>
                <?= date('M j, Y', strtotime($filters['date_from'])) ?> - <?= date('M j, Y', strtotime($filters['date_to'])) ?>
            </div>
            <?php if ($filters['category']): ?>
            <div class="filter-item">
                <span class="filter-label">Category:</span><br>
                <?= $filters['category'] ?>
            </div>
            <?php endif; ?>
            <?php if ($filters['employee']): ?>
            <div class="filter-item">
                <span class="filter-label">Employee:</span><br>
                <?= $filters['employee'] ?>
            </div>
            <?php endif; ?>
            <?php if ($filters['search']): ?>
            <div class="filter-item">
                <span class="filter-label">Search:</span><br>
                <?= $filters['search'] ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="summary-cards">
        <div class="summary-card">
            <h4>$<?= number_format($summary['total_expenses'], 2) ?></h4>
            <p>Total Expenses</p>
        </div>
        <div class="summary-card">
            <h4><?= number_format($summary['total_count']) ?></h4>
            <p>Total Transactions</p>
        </div>
        <div class="summary-card">
            <h4>$<?= number_format($summary['avg_amount'], 2) ?></h4>
            <p>Average Amount</p>
        </div>
        <div class="summary-card">
            <h4><?= $summary['top_category'] ? $summary['top_category']->category_name : '-' ?></h4>
            <p>Top Category</p>
        </div>
    </div>

    <!-- All Expenses Section -->
    <div class="section">
        <h3>📋 All Expenses (<?= count($expenses_data) ?> records)</h3>
        <?php if (empty($expenses_data)): ?>
            <div class="no-data">No expenses found for the selected period.</div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Description</th>
                        <th>Category</th>
                        <th>Receipt #</th>
                        <th>Payment Method</th>
                        <th class="text-right">Amount</th>
                        <th>Status</th>
                        <th>Employee</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($expenses_data as $expense): ?>
                    <tr>
                        <td><?= date('M j, Y', strtotime($expense->expense_date)) ?></td>
                        <td>
                            <div class="fw-bold"><?= htmlspecialchars($expense->description ?: '-') ?></div>
                            <small class="text-muted">ID: <?= $expense->expense_id ?></small>
                        </td>
                        <td><?= htmlspecialchars($expense->category_name ?: '-') ?></td>
                        <td><?= htmlspecialchars($expense->receipt_number ?: '-') ?></td>
                        <td><?= htmlspecialchars($expense->payment_method ?: '-') ?></td>
                        <td class="text-right fw-bold">$<?= number_format($expense->amount, 2) ?></td>
                        <td class="text-center">
                            <?php
                            $statusClass = '';
                            switch($expense->status) {
                                case 'pending': $statusClass = 'badge-warning'; break;
                                case 'approved': $statusClass = 'badge-success'; break;
                                case 'rejected': $statusClass = 'badge-danger'; break;
                                case 'paid': $statusClass = 'badge-info'; break;
                                default: $statusClass = 'badge-secondary';
                            }
                            ?>
                            <span class="badge <?= $statusClass ?>"><?= ucfirst($expense->status ?: 'Unknown') ?></span>
                        </td>
                        <td><?= htmlspecialchars($expense->employee_name ?: '-') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <div class="page-break"></div>

    <!-- Category Analysis Section -->
    <div class="section">
        <h3>🏷️ Category Analysis</h3>
        <?php if (empty($category_analysis)): ?>
            <div class="no-data">No category analysis data available.</div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Category</th>
                        <th class="text-right">Total Expenses</th>
                        <th class="text-right">Total Amount</th>
                        <th class="text-right">Average Amount</th>
                        <th class="text-right">Min Amount</th>
                        <th class="text-right">Max Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($category_analysis as $category): ?>
                    <tr>
                        <td class="fw-bold"><?= htmlspecialchars($category->category_name ?: 'Uncategorized') ?></td>
                        <td class="text-right"><?= number_format($category->total_expenses) ?></td>
                        <td class="text-right fw-bold">$<?= number_format($category->total_amount, 2) ?></td>
                        <td class="text-right">$<?= number_format($category->avg_amount, 2) ?></td>
                        <td class="text-right">$<?= number_format($category->min_amount, 2) ?></td>
                        <td class="text-right">$<?= number_format($category->max_amount, 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <!-- Employee Analysis Section -->
    <div class="section">
        <h3>👥 Employee Analysis</h3>
        <?php if (empty($employee_analysis)): ?>
            <div class="no-data">No employee analysis data available.</div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Employee ID</th>
                        <th class="text-right">Total Expenses</th>
                        <th class="text-right">Total Amount</th>
                        <th class="text-right">Average Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($employee_analysis as $employee): ?>
                    <tr>
                        <td class="fw-bold"><?= htmlspecialchars($employee->employee_name ?: 'Unknown') ?></td>
                        <td><?= htmlspecialchars($employee->employee_id ?: '-') ?></td>
                        <td class="text-right"><?= number_format($employee->total_expenses) ?></td>
                        <td class="text-right fw-bold">$<?= number_format($employee->total_amount, 2) ?></td>
                        <td class="text-right">$<?= number_format($employee->avg_amount, 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <!-- Monthly Trends Section -->
    <div class="section">
        <h3>📈 Monthly Trends</h3>
        <?php if (empty($monthly_trends)): ?>
            <div class="no-data">No trends data available.</div>
        <?php else: ?>
            <div class="chart-placeholder">
                <strong>Monthly Trends Chart</strong><br>
                <small>Chart visualization would be displayed here in the web interface</small>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>Month</th>
                        <th class="text-right">Total Expenses</th>
                        <th class="text-right">Total Amount</th>
                        <th class="text-right">Average per Transaction</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($monthly_trends as $trend): ?>
                    <tr>
                        <td class="fw-bold"><?= date('F Y', strtotime($trend->month . '-01')) ?></td>
                        <td class="text-right"><?= number_format($trend->total_expenses) ?></td>
                        <td class="text-right fw-bold">$<?= number_format($trend->total_amount, 2) ?></td>
                        <td class="text-right">$<?= number_format($trend->total_amount / $trend->total_expenses, 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <!-- Payment Methods Breakdown -->
    <?php if (!empty($summary['payment_methods'])): ?>
    <div class="section">
        <h3>💳 Payment Methods Breakdown</h3>
        <table>
            <thead>
                <tr>
                    <th>Payment Method</th>
                    <th class="text-right">Count</th>
                    <th class="text-right">Total Amount</th>
                    <th class="text-right">Percentage</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $totalAmount = $summary['total_expenses'];
                foreach ($summary['payment_methods'] as $method): 
                    $percentage = $totalAmount > 0 ? ($method->total_amount / $totalAmount) * 100 : 0;
                ?>
                <tr>
                    <td class="fw-bold"><?= htmlspecialchars($method->payment_method ?: 'Unknown') ?></td>
                    <td class="text-right"><?= number_format($method->count) ?></td>
                    <td class="text-right fw-bold">$<?= number_format($method->total_amount, 2) ?></td>
                    <td class="text-right"><?= number_format($percentage, 1) ?>%</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <div style="margin-top: 50px; text-align: center; color: #6c757d; font-size: 10px;">
        <p>Report generated by Troja Service Management System</p>
        <p>This report contains confidential information and should be handled accordingly.</p>
    </div>
</body>
</html>
