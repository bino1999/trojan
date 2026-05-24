<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Quick Bill - #<?= str_pad($bill[0]->quick_bill_id, 4, '0', STR_PAD_LEFT) ?></title>
    <style>
        /* Base Styles for A4 Printer */
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.3;
            color: #000;
            background-color: #fff;
            margin: 0;
            padding: 0;
            width: 100%;
        }

        /* A4 Page Setup (Landscape) */
        @page {
            size: A4 landscape;
            margin: 8mm;
        }

        /* Container sized to A5 area on A4 landscape (left half), full height 210mm */
        .a4-container {
            width: 148.5mm;          /* half of A4 width (297mm) */
            min-height: 210mm;       /* full A4 height in landscape */
            margin-left: 0;          /* stick to left side */
            margin-right: auto;      /* leave right half blank */
            padding: 6mm;
            box-sizing: border-box;
            page-break-inside: avoid;
        }

        @media print {
            body {
                margin: 0;
                padding: 0;
            }
            
            .a4-container {
                width: 148.5mm;      /* left half */
                min-height: 210mm;   /* fixed height */
                padding: 6mm;
                margin-left: 0;
                margin-right: auto;
            }

            /* Avoid breaking key blocks across pages */
            .company-header,
            .invoice-header,
            .customer-info,
            .items-section,
            .totals-section,
            .payment-info,
            .signature-section,
            .footer {
                page-break-inside: avoid;
            }

            /* Compact spacing for print to keep within A5 */
            body { font-size: 10px; }
            .company-header { margin-bottom: 10px; padding-bottom: 8px; }
            .company-logo { max-width: 60px; max-height: 60px; margin-bottom: 6px; }
            .company-name { font-size: 16px; }
            .invoice-header { margin-bottom: 10px; padding: 8px; }
            .customer-info { margin-bottom: 10px; padding: 8px; }
            .items-section { margin-bottom: 10px; }
            .items-table th, .items-table td { padding: 4px; }
            .totals-section { margin-bottom: 10px; padding: 8px; }
            .total-row { margin-bottom: 4px; }
            .total-row.final { padding-top: 6px; margin-top: 6px; font-size: 14px; }
            .payment-info { margin-bottom: 10px; padding: 8px; }
            .signature-section { margin-top: 16px; }
            .footer { margin-top: 16px; padding-top: 8px; }
        }

        /* Header Section */
        .company-header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #333;
        }

        .company-logo {
            max-width: 80px;
            max-height: 80px;
            margin: 0 auto 10px;
            display: block;
        }

        .company-name {
            font-size: 20px;
            font-weight: bold;
            margin: 0;
            padding: 0;
            color: #333;
        }

        .company-details {
            font-size: 12px;
            margin: 5px 0 0 0;
            color: #666;
        }

        /* Invoice Header */
        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }

        .invoice-title {
            font-size: 18px;
            font-weight: bold;
            margin: 0;
            color: #333;
        }

        .invoice-details {
            text-align: right;
        }

        .invoice-number {
            font-size: 16px;
            font-weight: bold;
            color: #007bff;
        }

        .invoice-date {
            font-size: 12px;
            color: #666;
            margin-top: 3px;
        }

        /* Customer Info */
        .customer-info {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }

        .customer-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #333;
        }

        .info-row {
            display: flex;
            margin-bottom: 8px;
        }

        .info-label {
            font-weight: bold;
            min-width: 120px;
            color: #555;
        }

        .info-value {
            color: #333;
        }

        /* Items List */
        .items-section {
            margin-bottom: 20px;
        }

        .items-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #333;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #ddd;
            font-size: 11px;
        }

        .items-table th {
            background-color: #007bff;
            color: white;
            padding: 8px;
            text-align: left;
            font-weight: bold;
            font-size: 11px;
        }

        .items-table td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
            vertical-align: top;
        }

        .items-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .item-name {
            font-weight: bold;
            color: #333;
        }

        .item-details {
            font-size: 10px;
            color: #666;
            margin-top: 3px;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        /* Totals Section */
        .totals-section {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 12px;
        }

        .total-row.final {
            font-size: 16px;
            font-weight: bold;
            color: #007bff;
            border-top: 2px solid #007bff;
            padding-top: 10px;
            margin-top: 10px;
        }

        /* Payment Information */
        .payment-info {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }

        .payment-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #333;
        }

        /* Signature Section */
        .signature-section {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
        }

        .signature-box {
            text-align: center;
            width: 45%;
        }

        .signature-line {
            border-top: 1px solid #333;
            margin-top: 40px;
            padding-top: 8px;
        }

        .signature-text {
            font-size: 11px;
            color: #666;
            margin-top: 5px;
        }

        /* Footer */
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
            text-align: center;
            color: #666;
            font-size: 11px;
        }

        /* Print specific styles */
        @media print {
            .no-print {
                display: none;
            }
            
            body {
                font-size: 11px;
            }
            
            .company-name {
                font-size: 18px;
            }
            
            .invoice-title {
                font-size: 16px;
            }
            
            .items-table {
                font-size: 10px;
            }
            
            .items-table th,
            .items-table td {
                padding: 6px;
            }

            /* Force 80% scale to avoid 2nd page overflow */
            .a4-container {
                transform: scale(0.95);
                transform-origin: top left;
            }
        }

        /* Page break control */
        .page-break {
            page-break-before: always;
        }

        .avoid-break {
            page-break-inside: avoid;
        }
    </style>
</head>
<body>
    <div class="a4-container">
        <!-- Company Header -->
        <div class="company-header">
            <?php if (!empty($logo_url)): ?>
                <img src="<?= $logo_url ?>" alt="Company Logo" class="company-logo">
            <?php endif; ?>
            <h1 class="company-name"><?= $shop_name ?></h1>
            <p class="company-details"><?= $shop_address ?></p>
            <p class="company-details"><?= $shop_phone ?></p>
        </div>

        <!-- Invoice Header -->
        <div class="invoice-header">
            <div>
                <h2 class="invoice-title">QUICK BILL</h2>
            </div>
            <div class="invoice-details">
                <div class="invoice-number">Bill #<?= str_pad($bill[0]->quick_bill_id, 4, '0', STR_PAD_LEFT) ?></div>
                <div class="invoice-date"><?= date('d/m/Y H:i', strtotime($bill[0]->created_at)) ?></div>
            </div>
        </div>

        <!-- Customer Information -->
        <div class="customer-info">
            <h3 class="customer-title">Customer Details</h3>
            <?php if ($bill[0]->customer_id == 0): ?>
                <div class="info-row">
                    <span class="info-label">Customer:</span>
                    <span class="info-value">Walk-in Customer</span>
                </div>
            <?php else: ?>
                <div class="info-row">
                    <span class="info-label">Customer:</span>
                    <span class="info-value"><?= htmlspecialchars($bill[0]->customer_name ?? 'N/A') ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Mobile:</span>
                    <span class="info-value"><?= htmlspecialchars($bill[0]->customer_mobile ?? 'N/A') ?></span>
                </div>
            <?php endif; ?>
            <?php if (!empty($bill[0]->description)): ?>
                <div class="info-row">
                    <span class="info-label">Notes:</span>
                    <span class="info-value"><?= htmlspecialchars($bill[0]->description) ?></span>
                </div>
            <?php endif; ?>
        </div>

        <!-- Items List -->
        <div class="items-section avoid-break">
            <h3 class="items-title">Items</h3>
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Details</th>
                        <th class="text-right">Quantity</th>
                        <th class="text-right">Unit Price</th>
                        <th class="text-right">Discount</th>
                        <th class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $totalAmount = 0;
                    $totalItemDiscounts = 0;
                    foreach ($items as $item): 
                        $itemTotal = $item->quantity * $item->price;
                        $totalAmount += $itemTotal;
                        $lineDiscount = 0;
                        if (!empty($item->discount_percent) && $item->discount_percent > 0) {
                            $lineDiscount = ($itemTotal * $item->discount_percent / 100.0);
                            $totalItemDiscounts += $lineDiscount;
                        } elseif (!empty($item->discount_amount) && $item->discount_amount > 0) {
                            // Amount discounts are per unit; multiply by quantity
                            $lineDiscount = (float)$item->discount_amount * (float)$item->quantity;
                            $totalItemDiscounts += $lineDiscount;
                        }
                    ?>
                    <tr>
                        <td class="item-name"><?= htmlspecialchars($item->item_name ?? 'Unknown Item') ?></td>
                        <td class="item-details">
                            <?php if (!empty($item->sku)): ?>
                                SKU: <?= htmlspecialchars($item->sku) ?><br>
                            <?php endif; ?>
                            <?php if (!empty($item->unit)): ?>
                                Unit: <?= htmlspecialchars($item->unit) ?>
                            <?php endif; ?>
                        </td>
                        <td class="text-right"><?= number_format($item->quantity, 2) ?></td>
                        <td class="text-right">Rs. <?= number_format($item->price, 2) ?></td>
                        <td class="text-right">- Rs. <?= number_format($lineDiscount, 2) ?></td>
                        <td class="text-right">Rs. <?= number_format(max(0, $itemTotal - $lineDiscount), 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Totals Section -->
        <div class="totals-section avoid-break">
            <div class="total-row">
                <span>Subtotal:</span>
                <span>Rs. <?= number_format($bill[0]->bill_amount ?? $totalAmount, 2) ?></span>
            </div>
            <?php if (!empty($totalItemDiscounts) && $totalItemDiscounts > 0): ?>
            <div class="total-row">
                <span>Item Discounts:</span>
                <span>- Rs. <?= number_format($totalItemDiscounts, 2) ?></span>
            </div>
            <?php endif; ?>
            <?php 
                $billDiscountAmount = (float)($bill[0]->discount_amount ?? 0);
                $billDiscountPercent = (float)($bill[0]->discount_percent ?? 0);
            ?>
            <?php if ($billDiscountAmount > 0 || $billDiscountPercent > 0): ?>
            <div class="total-row">
                <span>Total Bill Discount<?= $billDiscountPercent > 0 ? ' ('.rtrim(rtrim(number_format($billDiscountPercent, 2, '.', ''), '0'), '.').'%)' : '' ?>:</span>
                <span>- Rs. <?= number_format($billDiscountAmount, 2) ?></span>
            </div>
            <?php endif; ?>
            <div class="total-row final">
                <span>Total Amount:</span>
                <span>Rs. <?= number_format($bill[0]->final_bill_amount, 2) ?></span>
            </div>
            <?php if (isset($bill[0]->total_paid)): ?>
            <div class="total-row">
                <span>Paid:</span>
                <span>Rs. <?= number_format($bill[0]->total_paid, 2) ?></span>
            </div>
            <?php endif; ?>
            <?php if (isset($bill[0]->total_balance)): ?>
            <div class="total-row">
                <span>Balance:</span>
                <span>Rs. <?= number_format($bill[0]->total_balance, 2) ?></span>
            </div>
            <?php endif; ?>
        </div>

        <!-- Payment Information -->
        <?php if (!empty($payments)): ?>
        <div class="payment-info avoid-break">
            <h3 class="payment-title">Payment Details</h3>
            <?php foreach ($payments as $payment): ?>
            <div class="info-row">
                <span class="info-label">Payment Method:</span>
                <span class="info-value"><?= htmlspecialchars($payment->payment_method) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Amount Paid:</span>
                <span class="info-value">Rs. <?= number_format($payment->paid_amount, 2) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Date:</span>
                <span class="info-value"><?= date('d/m/Y', strtotime($payment->payment_date)) ?></span>
            </div>
            <?php if (!empty($payment->note)): ?>
            <div class="info-row">
                <span class="info-label">Notes:</span>
                <span class="info-value"><?= htmlspecialchars($payment->note) ?></span>
            </div>
            <?php endif; ?>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Signature Section -->
        <div class="signature-section avoid-break">
            <div class="signature-box">
                <div class="signature-line"></div>
                <p class="signature-text">Customer Signature</p>
            </div>
            <div class="signature-box">
                <div class="signature-line"></div>
                <p class="signature-text">Authorized Signature</p>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Thank you for your business!</p>
            <p>Generated on: <?= date('d/m/Y H:i:s') ?></p>
        </div>
    </div>

    <script>
        // Auto-print when page loads
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>
