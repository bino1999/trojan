<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - Job #<?= str_pad($jobData->job_id, 4, '0', STR_PAD_LEFT) ?></title>
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

        /* Remove all margins and padding for printer */
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
            margin-bottom: 1mm;
        }

        .info-label {
            font-weight: bold;
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

        /* Utility Classes */
        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .barcode {
            text-align: center;
            margin: 2mm 0;
        }

        /* Status Badge */
        .status-badge {}

        /* Table Styles */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        th {
            background-color: #f8f9fa;
            color: #212529;
            text-align: left;
            padding: 12px;
            font-weight: 500;
        }

        td {
            padding: 5px 12px;
        }


        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }
    </style>
</head>

<body onload="window.print()">


    <?php
    $paidMode = 'Not Paid';
    if ($jobLastPayment) {
        $paidMode = $jobLastPayment->payment_method;
    } else {
        $paidMode = 'Not Paid';
    }
    ?>

    <div class="container">
        <!-- Company Header -->
        <div class="company-header">
            <div style="margin-top: 10px;margin-bottom: 10px">
                <img width="50%" src="<?php echo base_url('assets/images/logo.png'); ?>" alt="Troja Logo" class="company-logo">
            </div>

            <h1 class="company-name">TROJA AUTO HUB</h1>
            <p class="company-details">
                No 664/1, Yatihena, Malwana, Biyagama<br>
                Phone: (011) 2 99 68 68 | Hotline: 075 500 500 9 | Email: info@trojaautohub.com<br>
            </p>
        </div>

        <!-- Invoice Header -->
        <div class="invoice-header">
            <div>
                <h2 class="invoice-title">INVOICE</h2>
            </div>
            <div>
                <div>#<?= str_pad($jobData->job_id, 4, '0', STR_PAD_LEFT) ?></div>
                <div><?= date('d/m/Y', strtotime($jobData->service_date)) ?></div>
            </div>
        </div>

        <!-- Customer Information -->
        <div class="customer-info">
            <div class="info-row">
                <span class="info-label">Customer</span>: <?= $jobData->customer_name ?>
            </div>
            <div class="info-row">
                <span class="info-label">Contact</span>: <?= $jobData->job_contact_no ?>
            </div>
            <div class="info-row">
                <span class="info-label">Next Mileage</span>: <?= $jobData->next_service_mileage ?>
            </div>
            <div class="info-row">
                <span class="info-label">Next Service Date</span>: <?= $jobData->next_service_date ?>
            </div>
            <div class="info-row">
                <span class="info-label">Paid Mode</span>:
                <?php
                echo $paidMode;
                ?>
            </div>
        </div>

        <style>
            .customer-info {
                margin-bottom: 5px;
            }

            .info-row {
                display: flex;
                margin-bottom: 1px;
            }

            .info-label {
                font-weight: bold;
                min-width: 120px;
            }

            .info-row.d-flex {
                justify-content: space-between;
            }
        </style>


        <!-- Items List -->
        <div class="items-list">
            <table>
            <thead>
                <tr>
                    <th class="text-right">Qty</th>
                    <th class="text-right">Price</th>
                    <th class="text-right">Discount</th>
                    <th class="text-right">Amount</th>
                </tr>
            </thead>
            <tbody>

            <?php
            $no = 1;
            $total_line_discount = 0;
                $total_item_cost = 0;
            foreach ($jobItems as $item) {
                $item_name = $item->item_type == 'service' ? $item->item_name : ($item->item_type == 'sparepart' ? $item->package_name : ($item->item_type == 'product' ? $item->product_name : ($item->item_type == 'labour' ? $item->description : ($item->item_type == 'other' ? $item->description : 'Unknown Item Type'))));

                // Calculate actual discount amount
            if ($item->discount_percent > 0) {
                $discount = ($item->price * $item->quantity) * ($item->discount_percent / 100);
            } elseif ($item->discount_amount > 0) {
                $discount = $item->discount_amount;
            } else {
                $discount = 0;
            }

            $total_line_discount += $discount;
            $item_cost = $item->price * $item->quantity;
            $total_item_cost += $item_cost;
            ?>
            <tr>
                        <td colspan="4"><?= $item_name ?></td>
                    </tr>
                <tr>
                        <td class="text-left"><?= $item->quantity ?> </td>
                        <td class="text-right"><?= number_format($item->price, 2) ?></td>
                        <td class="text-right"><?= number_format($discount, 2) ?></td>
                        <td class="text-right"><?= number_format($item->total_price, 2) ?></td>
                    </tr>
            <?php } ?>
            </tbody>
            </table>
        </div>

        <?php
        $discounted_amount = isset($jobData->discount_amount) ? $jobData->discount_amount : 0;
        if ($jobData->discount_percent > 0 && $jobData->discount_amount == 0) {
            $discounted_amount  = ($jobData->job_cost * $jobData->discount_percent) / 100;
        }
        ?>

        <!-- Summary Section -->
        <div class="summary">
            <div class="summary-row">
                <div class="summary-label">Total:</div>
                <div><?= number_format($total_item_cost, 2) ?> LKR</div>
            </div>
            <div class="summary-row">
                <div class="summary-label">Line Discount:</div>
                <div><?= number_format($total_line_discount, 2) ?> LKR</div>
            </div>
            <div class="summary-row">
                <div class="summary-label">Subtotal:</div>
                <div><?= number_format($jobData->job_cost, 2) ?> LKR</div>
            </div>
            <div class="summary-row">
                <div class="summary-label">Discount (<?= $jobData->discount_percent ?>%):</div>
                <div>- <?= number_format($discounted_amount, 2) ?></div>
            </div>
            <div class="summary-row total-row">
                <div class="summary-label">Total:</div>
                <div><?= number_format($jobData->final_bill_amount, 2) ?></div>
            </div>
            <div class="summary-row">
                <div class="summary-label">Paid:</div>
                <div><?= number_format($jobData->total_paid, 2) ?></div>
            </div>
            <div class="summary-row total-row">
                <div class="summary-label">Balance:</div>
                <div><?= number_format($jobData->total_balance, 2) ?></div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div>Thank you for your business!</div>
            <div class="barcode">* <?= $jobData->job_id ?> *</div>
            <div>www.trojaautohub.com</div>
        </div>
    </div>
</body>

</html>