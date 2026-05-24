<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Receipt - Bill #<?= str_pad($bill[0]->quick_bill_id, 4, '0', STR_PAD_LEFT) ?></title>
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
            margin-bottom: 2mm;
            padding-bottom: 2mm;
            border-bottom: 1px dashed #000;
        }

        .company-logo {
            max-width: 50%;
            margin: 10px 0;
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
        }

        .invoice-title {
            font-size: 13px;
            font-weight: bold;
            margin: 0;
        }

        /* Customer Info */
        .customer-info {
            margin-bottom: 3mm;
            font-size: 11px;
        }

        .info-row {
            display: flex;
            margin-bottom: 1mm;
        }

        .info-label {
            font-weight: bold;
            min-width: 110px;
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
            margin-bottom: 1mm;
        }

        .item-desc {
            flex: 2;
        }

        .item-qty {
            flex: 0.5;
            text-align: center;
        }

        .item-price {
            flex: 1;
            text-align: right;
        }

        .item-total {
            flex: 1;
            text-align: right;
            font-weight: bold;
        }

        /* Summary Section */
        .summary {
            width: 100%;
            margin-bottom: 3mm;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1mm;
        }

        .summary-label {
            font-weight: bold;
        }

        .total-row {
            font-weight: bold;
            border-top: 1px dashed #000;
            padding-top: 1mm;
            margin-top: 1mm;
        }

        /* Footer */
        .footer {
            font-size: 10px;
            text-align: center;
            border-top: 1px dashed #000;
            padding-top: 2mm;
        }

        /* Barcode styling */
        .barcode {
            margin: 2mm 0;
            font-family: monospace;
            font-weight: bold;
        }
    </style>
</head>
<body onload="window.print()">
    <div class="container">
        <!-- Company Header -->
        <div class="company-header">
            <img src="<?= $logo_url ?>" alt="Logo" class="company-logo" />
            <h1 class="company-name"><?= $shop_name ?></h1>
            <p class="company-details">
                    No 664/1, Yatihena, Malwana, Biyagama<br>
                    Phone: (011) 2 99 68 68 | Hotline: 075 500 500 9 | Email: info@trojaautohub.com<br>
                </p>
        </div>

        <!-- Invoice Header -->
        <div class="invoice-header">
            <div>
                <h2 class="invoice-title">RECEIPT</h2>
            </div>
            <div>
                <div>#<?= str_pad($quick_bill_id, 4, '0', STR_PAD_LEFT) ?></div>
                <div>
                    <?= isset($bill[0]->created_at) && $bill[0]->created_at
    ? date('d/m/Y H:i', strtotime($bill[0]->created_at))
    : 'N/A' ?>

                </div>
            </div>
        </div>

        <!-- Customer Info -->
        <div class="customer-info">
            <div class="info-row">
                <span class="info-label">Customer</span>: <?= htmlspecialchars($bill[0]->customer_name ?? 'Walk-in') ?>
            </div>
            <?php if (!empty($bill[0]->customer_mobile)): ?>
            <div class="info-row">
                <span class="info-label">Phone</span>: <?= htmlspecialchars($bill[0]->customer_mobile) ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Items List -->
        <div class="items-list">
            <?php if (!empty($items)): ?>
    <?php foreach ($items as $item): ?>
        <?php 
            $lineTotalBefore = ($item->price * $item->quantity);
            $lineDiscountCalc = 0;
            if ($item->discount_percent > 0) {
                $lineDiscountCalc = ($lineTotalBefore * $item->discount_percent / 100);
            } elseif ($item->discount_amount > 0) {
                // Amount discounts are per unit; multiply by quantity
                $lineDiscountCalc = (float)$item->discount_amount * (float)$item->quantity;
            }
            $lineTotalAfter = max(0, $lineTotalBefore - $lineDiscountCalc);
        ?>
        <div class="item-row">
            <div class="item-desc">
                <?= htmlspecialchars($item->item_name) ?>
                <?php if (!empty($item->sku)): ?>
                    <br><small style="font-size: 9px; color: #666;">SKU: <?= htmlspecialchars($item->sku) ?></small>
                <?php endif; ?>
            </div>
            <div class="item-qty"><?= $item->quantity ?> <?= htmlspecialchars($item->unit ?? '') ?></div>
            <div class="item-price"><?= number_format($item->price, 2) ?></div>
            <div class="item-total"><?= number_format($lineTotalAfter, 2) ?></div>
        </div>
        <?php if ($item->discount_percent > 0 || $item->discount_amount > 0): ?>
        <div class="item-row" style="font-size: 11px; color: #333; font-weight: bold;">
            <div class="item-desc">Discount<?= $item->discount_percent > 0 ? ' ('.$item->discount_percent.'%)' : '' ?>:</div>
            <div class="item-qty"></div>
            <div class="item-price"></div>
            <div class="item-total">- <?= number_format($lineDiscountCalc, 2) ?></div>
        </div>
        <?php endif; ?>
    <?php endforeach; ?>
<?php else: ?>
    <div class="item-row"><em>No items found</em></div>
<?php endif; ?>
        </div>



        <!-- Summary Section -->
        <div class="summary">
            <?php 
            // Calculate subtotal from items (before discounts) and include row-level discounts in totals
            $subtotal = 0;
            foreach ($items as $item) {
                $subtotal += ($item->price * $item->quantity);
            }
            
            // Calculate total item discounts (percent of line total or per-unit amount × qty)
            $totalItemDiscounts = 0;
            foreach ($items as $item) {
                if ($item->discount_percent > 0) {
                    $totalItemDiscounts += ($item->price * $item->quantity * $item->discount_percent / 100);
                } elseif ($item->discount_amount > 0) {
                    $totalItemDiscounts += ((float)$item->discount_amount * (float)$item->quantity);
                }
            }
            ?>
            <div class="summary-row">
                <div class="summary-label">Subtotal:</div>
                <div><?= number_format($subtotal, 2) ?></div>
            </div>
            <?php if ($totalItemDiscounts > 0): ?>
            <div class="summary-row">
                <div class="summary-label">Item Discounts:</div>
                <div>- <?= number_format($totalItemDiscounts, 2) ?></div>
            </div>
            <?php endif; ?>
            <?php 
                $billAmount = isset($bill[0]->bill_amount) ? (float)$bill[0]->bill_amount : ($subtotal - $totalItemDiscounts);
                $billDiscountAmount = (float)($bill[0]->discount_amount ?? 0);
                $billDiscountPercent = (float)($bill[0]->discount_percent ?? 0);
            ?>
            
            <?php if ($billDiscountAmount > 0 || $billDiscountPercent > 0): ?>
            <div class="summary-row">
                <div class="summary-label">Bill Discount<?= $billDiscountPercent > 0 ? ' ('.rtrim(rtrim(number_format($billDiscountPercent, 2, '.', ''), '0'), '.').'%)' : '' ?>:</div>
                <div>- <?= number_format($billDiscountAmount, 2) ?></div>
            </div>
            <?php endif; ?>
            <div class="summary-row total-row">
                <div class="summary-label">Total:</div>
                <div><?= number_format($bill[0]->final_bill_amount ?? 0, 2) ?></div>
            </div>
            <?php if (isset($bill[0]->total_paid)): ?>
            <div class="summary-row">
                <div class="summary-label">Paid:</div>
                <div><?= number_format($bill[0]->total_paid, 2) ?></div>
            </div>
            <?php endif; ?>
            <?php if (isset($bill[0]->total_balance)): ?>
            <div class="summary-row total-row">
                <div class="summary-label">Balance:</div>
                <div><?= number_format($bill[0]->total_balance, 2) ?></div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Payment Methods -->
        <?php if (!empty($payments)): ?>
        <div style="margin-top: 3mm; border-top: 1px dashed #000; padding-top: 2mm;">
            <div style="font-weight: bold; margin-bottom: 2mm;">Payment Methods</div>
            <?php foreach ($payments as $payment): ?>
            <div style="display: flex; justify-content: space-between; margin-bottom: 1mm; font-size: 11px;">
                <div>
                    <strong><?= htmlspecialchars($payment->payment_method) ?></strong>
                    <?php if (!empty($payment->card_or_cheque_no)): ?>
                        - <?= htmlspecialchars($payment->card_or_cheque_no) ?>
                    <?php endif; ?>
                    <?php if (!empty($payment->bank_name)): ?>
                        (<?= htmlspecialchars($payment->bank_name) ?>)
                    <?php endif; ?>
                </div>
                <div><strong>Rs. <?= number_format($payment->paid_amount, 2) ?></strong></div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Footer -->
        <div class="footer">
            <div>Thank you for your business!</div>
            <div class="barcode">* <?= str_pad($bill[0]->quick_bill_id, 4, '0', STR_PAD_LEFT) ?> *</div>
            <div>www.yourshopwebsite.com</div>
        </div>
        

    </div>
</body>
</html>
