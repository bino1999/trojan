<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - Job #<?= str_pad($jobData->job_id, 4, '0', STR_PAD_LEFT) ?></title>
    <style>
        /* Base Styles */
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #fff;
            margin: 0;
            padding: 0;
        }

        /* A4 Paper Size */
        @page {
            size: A4;
            margin: 15mm;

            /* Remove page numbers and URLs in print */
            @top-left {
                content: none;
            }

            @top-center {
                content: none;
            }

            @top-right {
                content: none;
            }

            @bottom-left {
                content: none;
            }

            @bottom-center {
                content: none;
            }

            @bottom-right {
                content: none;
            }
        }

        .container {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            padding: 15mm;
            box-sizing: border-box;
            background: white;
        }

        /* Header Section */
        .company-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }

        .company-info {
            flex: 1;
        }

        .company-logo {
            max-width: 150px;
            height: auto;
        }

        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
            margin: 0;
        }

        .company-details {
            font-size: 12px;
            color: #7f8c8d;
            margin: 5px 0 0 0;
        }

        /* Invoice Header */
        .invoice-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .invoice-title {
            font-size: 28px;
            font-weight: bold;
            color: #2c3e50;
            margin: 0;
        }

        .invoice-meta {
            text-align: right;
        }

        .invoice-number {
            font-weight: bold;
            margin-bottom: 5px;
        }

        /* Customer Info */
        .customer-info {
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 30px;
            border: 1px solid #eee;
        }

        .info-row {
            display: flex;
            margin-bottom: 1px;
        }

        .info-label {
            font-weight: bold;
            min-width: 120px;
        }

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
            border: 1px solid #dee2e6;
        }

        td {
            padding: 5px 12px;
            border: 1px solid #dee2e6;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        /* Summary Section */
        .summary {
            width: 50%;
            margin-left: auto;
            margin-bottom: 30px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }

        .summary-label {
            font-weight: bold;
        }

        .total-row {
            font-weight: bold;
            font-size: 16px;
            border-top: 2px solid #2c3e50;
            margin-top: 5px;
            padding-top: 10px;
        }

        /* Footer */
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            font-size: 12px;
            color: #7f8c8d;
            text-align: center;
        }

        /* Print Specific Styles */
        @media print {
            body {
                background: none;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .container {
                box-shadow: none;
                padding: 0;
            }

            .no-print {
                display: none;
            }

            /* Force black and white for print */
            * {
                color: black !important;
                background: white !important;
            }

            /* Borders for print */
            table,
            th,
            td {
                border-color: #ddd !important;
            }

            .customer-info {
                border-color: #ddd !important;
            }
        }

        /* Status Badges - visible on screen only */
        @media screen {
            .status-badge {
                display: inline-block;
                padding: 3px 8px;
                border-radius: 3px;
                font-size: 12px;
                font-weight: bold;
                text-transform: uppercase;
            }

            .status-pending {
                background-color: #f39c12;
                color: white;
            }

            .status-ongoing {
                background-color: #3498db;
                color: white;
            }

            .status-hold {
                background-color: #e74c3c;
                color: white;
            }

            .status-cancelled {
                background-color: #95a5a6;
                color: white;
            }

            .status-completed {
                background-color: #2ecc71;
                color: white;
            }
        }

        @media print {
            .status-badge {
                border: 1px solid #000;
                padding: 2px 6px;
                display: inline-block;
            }
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
        <!-- Company Header with Logo -->
        <div class="company-header">
            <div class="company-info">
                <h1 class="company-name">TROJA AUTO HUB</h1>
                <p class="company-details">
                    No 664/1, Yatihena, Malwana, Biyagama<br>
                    Phone: (011) 2 99 68 68 | Hotline: 075 500 500 9 | Email: info@trojaautohub.com<br>
                </p>
            </div>
            <img src="<?php echo base_url('assets/images/logo.png'); ?>" alt="Troja Logo" class="company-logo">
        </div>

        <!-- Invoice Header -->
        <div class="invoice-header">
            <div>
                <h1 class="invoice-title">INVOICE</h1>
            </div>
            <div class="invoice-meta">
                <div class="invoice-number">Invoice #<?= str_pad($jobData->job_id, 4, '0', STR_PAD_LEFT) ?></div>
                <div>Date: <?= date('F j, Y', strtotime($jobData->service_date)) ?></div>
            </div>
        </div>

        <!-- Customer Information -->
        <div class="customer-info">
            <div class="info-row d-flex justify-content-between">
                <div>
                    <span class="info-label">Customer Name :</span>
                    <span><?= $jobData->customer_name ?></span>
                </div>
                <div>
                    <span class="info-label">Next Mileage :</span>
                    <span><?= number_format($jobData->next_service_mileage) ?> km</span>
                </div>
            </div>
            <div class="info-row d-flex justify-content-between">
                <div>
                    <span class="info-label">Contact Number :</span>
                    <span><?= $jobData->job_contact_no ?></span>
                </div>
                <div>
                    <span class="info-label">Next Service Date :</span>
                    <span><?= date('Y-m-d', strtotime($jobData->next_service_date)) ?></span>
                </div>
            </div>
            <div class="info-row d-flex justify-content-between">
                <div>
                    <span class="info-label">Payment Method :</span>
                    <span><?= $paidMode ?></span>
                </div>
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
                min-width: 150px;
            }

            .info-row.d-flex {
                justify-content: space-between;
            }
        </style>


        <!-- Items Table -->
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Description</th>
                    <th class="text-right">Qty</th>
                    <th class="text-right">Unit Price</th>
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
                        <td><?= $no++ ?></td>
                        <td><?= $item_name ?></td>
                        <td class="text-right"><?= $item->quantity ?></td>
                        <td class="text-right"><?= number_format($item->price, 2) ?></td>
                        <td class="text-right"><?= number_format($discount, 2) ?></td>
                        <td class="text-right"><?= number_format($item->total_price, 2) ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>


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
                <div class="summary-label">Discount (%):</div>
                <div> <?= $jobData->discount_percent ?> %</div>
            </div>
            <div class="summary-row">
                <div class="summary-label">Discount Amount:</div>
                <div>- <?= number_format($discounted_amount, 2) ?> LKR</div>
            </div>
            <div class="summary-row total-row">
                <div class="summary-label">Total Amount:</div>
                <div><?= number_format($jobData->final_bill_amount, 2) ?> LKR</div>
            </div>
            <div class="summary-row">
                <div class="summary-label">Amount Paid:</div>
                <div><?= number_format($jobData->total_paid, 2) ?> LKR</div>
            </div>
            <div class="summary-row total-row">
                <div class="summary-label">Balance Due:</div>
                <div><?= number_format($jobData->total_balance, 2) ?> LKR</div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Thank you for your business!</p>
            <p>Payment terms: Net 03 days. Please make checks payable to Troja Auto Hub.</p>
            <p class="no-print">If the print dialog didn't open automatically, <button onclick="window.print()">click here to print</button></p>
        </div>
    </div>
</body>

</html>