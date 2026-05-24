<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Internal Receipt - Bill #<?= str_pad($internal_bill_id, 4, '0', STR_PAD_LEFT) ?></title>
    <style>
        /* Base Styles for 76mm Printer */
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.2;
            color: #000;
            background-color: #fff;
            margin: 0;
            padding: 2mm;
            width: 76mm;
        }

        @page {
            size: 76mm auto;
            margin: 0;
        }

        /* Header Section */
        .company-header {
            text-align: center;
            margin-bottom: 3mm;
            padding-bottom: 2mm;
            border-bottom: 1px dashed #000;
        }

        .company-logo {
            max-width: 50%;
            margin: 5px 0;
        }

        .company-name {
            font-size: 14px;
            font-weight: bold;
            margin: 0;
            padding: 0;
        }

        .company-details {
            font-size: 10px;
            margin: 1mm 0 0 0;
        }

        /* Invoice Header */
        .invoice-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3mm;
            font-size: 11px;
            border-bottom: 1px dashed #000;
            padding-bottom: 2mm;
        }

        .invoice-title {
            font-size: 13px;
            font-weight: bold;
            margin: 0;
            color: #d32f2f;
        }

        /* Employee Info */
        .employee-info {
            margin-bottom: 3mm;
            font-size: 11px;
            background-color: #f5f5f5;
            padding: 2mm;
            border-radius: 2mm;
        }

        .info-row {
            display: flex;
            margin-bottom: 1mm;
        }

        .info-label {
            font-weight: bold;
            min-width: 80px;
        }

        /* Items List */
        .items-list {
            width: 100%;
            margin-bottom: 3mm;
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
            padding: 2mm 0;
        }

        .item-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2mm;
            padding-bottom: 1mm;
            border-bottom: 1px dotted #ccc;
        }

        .item-desc {
            flex: 3;
            font-weight: bold;
        }

        .item-qty {
            flex: 1;
            text-align: center;
            font-weight: bold;
        }


        /* Footer */
        .footer {
            font-size: 10px;
            text-align: center;
            border-top: 1px dashed #000;
            padding-top: 2mm;
            margin-top: 3mm;
        }

        /* Barcode styling */
        .barcode {
            margin: 2mm 0;
            font-family: monospace;
            font-weight: bold;
            font-size: 14px;
        }

        /* Note Section */
        .note-section {
            margin-top: 3mm;
            padding: 2mm;
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 2mm;
        }

        .note-title {
            font-weight: bold;
            margin-bottom: 1mm;
            color: #856404;
        }

        /* Internal Badge */
        .internal-badge {
            background-color: #d32f2f;
            color: white;
            padding: 1mm 2mm;
            border-radius: 1mm;
            font-size: 10px;
            font-weight: bold;
            display: inline-block;
            margin-bottom: 2mm;
        }
    </style>
</head>
<body onload="window.print()">
    <div class="container">
        <!-- Company Header -->
        <div class="company-header">
            <img src="<?= $logo_url ?>" alt="Logo" class="company-logo" />
            <h1 class="company-name"><?= $shop_name ?></h1>
            <div class="internal-badge">INTERNAL USE ONLY</div>
        </div>

        <!-- Invoice Header -->
        <div class="invoice-header">
            <div>
                <h2 class="invoice-title">INTERNAL RECEIPT</h2>
            </div>
            <div>
                <div><strong>#<?= str_pad($internal_bill_id, 4, '0', STR_PAD_LEFT) ?></strong></div>
                <div>
                    <?= isset($bill[0]->created_at) && $bill[0]->created_at
    ? date('d/m/Y H:i', strtotime($bill[0]->created_at))
    : 'N/A' ?>
                </div>
            </div>
        </div>

        <!-- Employee Info -->
        <div class="employee-info">
            <div class="info-row">
                <span class="info-label">Employee:</span>
                <span><?= htmlspecialchars($bill[0]->full_employee_name ?? $bill[0]->employee_name ?? 'N/A') ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Issued By:</span>
                <span><?= htmlspecialchars($bill[0]->created_by_name ?? 'N/A') ?></span>
            </div>
        </div>

        <!-- Items List -->
        <div class="items-list">
            <?php if (!empty($items)): ?>
                <?php foreach ($items as $item): ?>
                    <div class="item-row">
                        <div class="item-desc">
                            <?= htmlspecialchars($item->product_name) ?>
                            <?php if (!empty($item->sku)): ?>
                                <br><small style="font-size: 9px; color: #666;">SKU: <?= htmlspecialchars($item->sku) ?></small>
                            <?php endif; ?>
                        </div>
                        <div class="item-qty"><?= $item->quantity ?></div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="item-row"><em>No items found</em></div>
            <?php endif; ?>
        </div>


        <!-- Note Section -->
        <?php if (!empty($bill[0]->note)): ?>
        <div class="note-section">
            <div class="note-title">Note:</div>
            <div style="font-size: 11px;"><?= htmlspecialchars($bill[0]->note) ?></div>
        </div>
        <?php endif; ?>

        <!-- Footer -->
        <div class="footer">
            <div style="font-weight: bold; margin-bottom: 2mm;">Internal Use Only</div>
            <div class="barcode">* <?= str_pad($internal_bill_id, 4, '0', STR_PAD_LEFT) ?> *</div>
            <div style="margin-top: 2mm;">Thank you!</div>
        </div>
    </div>
</body>
</html>
