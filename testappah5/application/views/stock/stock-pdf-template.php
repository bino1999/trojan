<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Stock Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
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
            font-size: 16px;
            font-weight: bold;
        }
        .header p {
            margin: 3px 0;
            font-size: 10px;
        }
        .filters {
            margin-bottom: 15px;
            font-size: 9px;
        }
        .filters strong {
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #333;
            padding: 4px;
            text-align: left;
            font-size: 8px;
        }
        th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 8px;
            color: #666;
        }
        .no-data {
            text-align: center;
            font-style: italic;
            color: #666;
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>STOCK REPORT</h1>
        <p>Generated on: <?= date('Y-m-d H:i:s') ?></p>
        <?php if ($filters['has_date_filter']): ?>
            <p>Report Period: <?= $filters['sdate'] ?> to <?= $filters['edate'] ?></p>
        <?php else: ?>
            <p>Report Period: All Time (No Date Filter Applied)</p>
        <?php endif; ?>
    </div>

    <div class="filters">
        <strong>Applied Filters:</strong>
        <?php if (!empty($filters['category_id'])): ?>
            Category: <?= $filters['category_id'] ?> | 
        <?php endif; ?>
        <?php if (!empty($filters['brand_id'])): ?>
            Brand: <?= $filters['brand_id'] ?> | 
        <?php endif; ?>
        <?php if (!empty($filters['supplier_id'])): ?>
            Supplier: <?= $filters['supplier_id'] ?> | 
        <?php endif; ?>
        <?php if (empty($filters['category_id']) && empty($filters['brand_id']) && empty($filters['supplier_id'])): ?>
            No category/brand/supplier filters applied
        <?php endif; ?>
    </div>

    <?php if (!empty($stock)): ?>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Description</th>
                    <th>SKU</th>
                    <th>Supplier</th>
                    <th>Product Type</th>
                    <th>Open Balance</th>
                    <th>Stock In</th>
                    <th>Stock Out</th>
                    <th>Closing Balance</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $i = 1;
                foreach ($stock as $item): 
                ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td><?= strtoupper($item->product_name) ?></td>
                        <td><?= $item->sku ?></td>
                        <td><?= isset($item->supplier_name) ? $item->supplier_name : 'N/A' ?></td>
                        <td><?= isset($item->uom) ? $item->uom : 'Piece' ?></td>
                        <td><?= number_format($item->open_balance, 2) ?></td>
                        <td><?= number_format($item->stock_in, 2) ?></td>
                        <td><?= number_format($item->stock_out, 2) ?></td>
                        <td><?= number_format($item->closing_balance, 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="no-data">
            No stock data found for the selected filters.
        </div>
    <?php endif; ?>

    <div class="footer">
        <p>This report was generated from the Stock Management System</p>
        <p>Total Records: <?= count($stock) ?></p>
    </div>
</body>
</html>
