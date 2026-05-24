<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Daily Report - <?= htmlspecialchars($report_date) ?></title>
    <style>
        @page {
            size: A4 portrait;
            margin: 1.5cm;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 0;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }
        
        .header h1 {
            margin: 0 0 5px 0;
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }
        
        .header p {
            margin: 0;
            font-size: 12px;
            color: #666;
        }
        
        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        
        .report-table th,
        .report-table td {
            border: 1px solid #333;
            padding: 8px 10px;
            text-align: left;
            font-size: 10px;
            vertical-align: top;
        }
        
        .report-table th {
            background-color: #f5f5f5;
            font-weight: bold;
            color: #333;
        }
        
        .section-header {
            background-color: #e9ecef !important;
            font-weight: bold;
            font-size: 11px;
            text-align: center;
        }
        
        .highlight-row {
            background-color: #e6f2f5 !important;
            font-weight: bold;
        }
        
        .sub-item {
            padding-left: 20px !important;
            font-size: 9px;
            color: #555;
        }
        
        .amount {
            text-align: right;
            font-weight: bold;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 9px;
            color: #666;
            border-top: 1px solid #ccc;
            padding-top: 10px;
        }
        
        .no-data {
            text-align: center;
            font-style: italic;
            color: #999;
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Daily Business Report</h1>
        <p>Date: <?= date('F j, Y', strtotime($report_date)) ?></p>
    </div>
    
    <table class="report-table">
        <tbody>
            <!-- Opening Section -->
            <tr>
                <th class="section-header" colspan="2">Opening Balance</th>
            </tr>
            <tr class="highlight-row">
                <th>Day Start Cash in Hand</th>
                <td class="amount">Rs. <?= number_format($day_start_cash_in_hand ?? 0, 2) ?></td>
            </tr>
            
            <!-- Income Overview Section -->
            <tr>
                <th class="section-header" colspan="2">Income Overview</th>
            </tr>
            <tr>
                <th>Quick Bill Income</th>
                <td class="amount">Rs. <?= number_format($day_quick_bill_income ?? 0, 2) ?></td>
            </tr>
            <tr>
                <th>Services Income</th>
                <td class="amount">Rs. <?= number_format(($day_services_job_income ?? 0) + ($day_mechanical_job_income ?? 0), 2) ?></td>
            </tr>
            <tr>
                <td class="sub-item">- Normal Services</td>
                <td class="amount">Rs. <?= number_format($day_services_job_income ?? 0, 2) ?></td>
            </tr>
            <tr>
                <td class="sub-item">- Mechanical Services</td>
                <td class="amount">Rs. <?= number_format($day_mechanical_job_income ?? 0, 2) ?></td>
            </tr>
            <tr>
                <td class="sub-item">- Common Jobs Total</td>
                <td class="amount">Rs. <?= number_format($day_common_jobs_income ?? 0, 2) ?></td>
            </tr>
            <tr class="highlight-row">
                <th>Total Day Income</th>
                <td class="amount">Rs. <?= number_format($day_income_total ?? 0, 2) ?></td>
            </tr>
            
            <!-- Payment Methods Section -->
            <tr>
                <th class="section-header" colspan="2">Payment Methods Received</th>
            </tr>
            <tr>
                <th>Cash Payments</th>
                <td class="amount">Rs. <?= number_format($day_cash_income ?? 0, 2) ?></td>
            </tr>
            <tr>
                <th>Card Payments</th>
                <td class="amount">Rs. <?= number_format($day_card_payment ?? 0, 2) ?></td>
            </tr>
            <tr>
                <th>Bank Transfers</th>
                <td class="amount">Rs. <?= number_format($day_bank_transfer ?? 0, 2) ?></td>
            </tr>
            <tr>
                <th>Cheque Payments</th>
                <td class="amount">Rs. <?= number_format($day_cheque_payment ?? 0, 2) ?></td>
            </tr>
            <tr>
                <th>Credit Amount</th>
                <td class="amount">Rs. <?= number_format($day_credit_amount ?? 0, 2) ?></td>
            </tr>
            
            <!-- Cash & Expenses Section -->
            <tr>
                <th class="section-header" colspan="2">Cash & Expenses</th>
            </tr>
            <tr class="highlight-row">
                <th>Total Cash in Hand</th>
                <td class="amount">Rs. <?= number_format($all_cash_in_hand ?? 0, 2) ?></td>
            </tr>
            <tr class="highlight-row">
                <th>Day Expenses</th>
                <td class="amount">Rs. <?= number_format($day_expenses ?? 0, 2) ?></td>
            </tr>
            <tr>
                <td class="sub-item">- Cash Expenses</td>
                <td class="amount">Rs. <?= number_format($expense_cash ?? 0, 2) ?></td>
            </tr>
            <tr>
                <td class="sub-item">- Card Expenses</td>
                <td class="amount">Rs. <?= number_format($expense_card ?? 0, 2) ?></td>
            </tr>
            <tr>
                <td class="sub-item">- Bank Transfer Expenses</td>
                <td class="amount">Rs. <?= number_format($expense_bank_transfer ?? 0, 2) ?></td>
            </tr>
            <tr>
                <td class="sub-item">- Cheque Expenses</td>
                <td class="amount">Rs. <?= number_format($expense_cheque ?? 0, 2) ?></td>
            </tr>
            <tr>
                <th>Bank Deposit</th>
                <td class="amount">Rs. <?= number_format($bank_deposit ?? 0, 2) ?></td>
            </tr>
            
            <!-- Balances Section -->
            <tr>
                <th class="section-header" colspan="2">End of Day Balances</th>
            </tr>
            <tr>
                <th>Cash in Hand</th>
                <td class="amount">Rs. <?= number_format($day_end_cash_in_hand ?? 0, 2) ?></td>
            </tr>
            <tr>
                <th>Bank Balance</th>
                <td class="amount">Rs. <?= number_format($day_end_bank_balance ?? 0, 2) ?></td>
            </tr>
            <tr>
                <th>Card Payment Balance</th>
                <td class="amount">Rs. <?= number_format($day_end_card_balance ?? 0, 2) ?></td>
            </tr>
            
            <!-- Mechanic Outsource Section -->
            <tr>
                <th class="section-header" colspan="2">Mechanic Outsource</th>
            </tr>
            <tr>
                <th>Item Buy Cost</th>
                <td class="amount">Rs. <?= number_format($mechanic_item_buy_cost ?? 0, 2) ?></td>
            </tr>
            <tr>
                <th>Item Sell Amount</th>
                <td class="amount">Rs. <?= number_format($mechanic_item_sell_amount ?? 0, 2) ?></td>
            </tr>
            <tr class="highlight-row">
                <th>Mechanic Profit</th>
                <td class="amount">Rs. <?= number_format($mechanic_profit ?? 0, 2) ?></td>
            </tr>
            
            <!-- Day Profit -->
            <tr class="highlight-row">
                <th style="background-color: #d4edda !important;">Day Profit</th>
                <td class="amount" style="background-color: #d4edda !important;">Rs. <?= number_format($day_profit ?? 0, 2) ?></td>
            </tr>
            
            <!-- Monthly Summary Section -->
            <tr>
                <th class="section-header" colspan="2">Monthly Summary (<?= date('F Y', strtotime($report_date)) ?>)</th>
            </tr>
            <tr class="highlight-row">
                <th>Total Month Income</th>
                <td class="amount">Rs. <?= number_format($monthIncomeTotal ?? 0, 2) ?></td>
            </tr>
            <tr>
                <td class="sub-item">- Cash</td>
                <td class="amount">Rs. <?= number_format($monthCash ?? 0, 2) ?></td>
            </tr>
            <tr>
                <td class="sub-item">- Card</td>
                <td class="amount">Rs. <?= number_format($monthCard ?? 0, 2) ?></td>
            </tr>
            <tr>
                <td class="sub-item">- Bank Transfer</td>
                <td class="amount">Rs. <?= number_format($monthBank ?? 0, 2) ?></td>
            </tr>
            <tr>
                <td class="sub-item">- Credit</td>
                <td class="amount">Rs. <?= number_format($monthCredit ?? 0, 2) ?></td>
            </tr>
            <tr class="highlight-row">
                <th>Total Month Expenses</th>
                <td class="amount">Rs. <?= number_format($monthExpTotal ?? 0, 2) ?></td>
            </tr>
            <tr>
                <td class="sub-item">- In House Expenses</td>
                <td class="amount">Rs. <?= number_format($monthExpInHouse ?? 0, 2) ?></td>
            </tr>
            <tr>
                <td class="sub-item">- Used Item Expenses</td>
                <td class="amount">Rs. <?= number_format($monthExpUsedItem ?? 0, 2) ?></td>
            </tr>
            <tr class="highlight-row">
                <th>Month Profit</th>
                <td class="amount">Rs. <?= number_format($monthProfit ?? 0, 2) ?></td>
            </tr>
        </tbody>
    </table>
    
    <div class="footer">
        <p>Generated on <?= date('F j, Y \a\t g:i A') ?> | Daily Business Report</p>
    </div>
</body>
</html>
