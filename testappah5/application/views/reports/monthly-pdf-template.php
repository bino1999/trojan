<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Monthly Report - <?= htmlspecialchars($report_year) ?> <?= date('F', mktime(0,0,0,$report_month,1)) ?></title>
	<style>
		@page { 
			size: A4 portrait; 
			margin: 1cm; 
		}
		body { 
			font-family: Arial, sans-serif; 
			font-size: 10px; 
			line-height: 1.4;
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
			font-size: 14px;
			color: #666;
			font-weight: normal;
		}
		table { 
			width: 100%; 
			border-collapse: collapse; 
			margin-bottom: 20px;
		}
		th, td { 
			border: 1px solid #000; 
			padding: 6px 8px; 
			text-align: left;
		}
		th.title { 
			background: #f0f4f7; 
			text-align: left; 
			font-weight: bold;
		}
		tr.section th { 
			background: #f8f9fa; 
			font-weight: bold; 
			font-size: 11px;
		}
		tr.highlight th, tr.highlight td { 
			background: #e6f2f5; 
			font-weight: bold; 
		}
		tr.total th, tr.total td {
			background: #d4edda;
			font-weight: bold;
		}
		.amount {
			text-align: right;
		}
		.footer {
			margin-top: 30px;
			text-align: center;
			font-size: 9px;
			color: #666;
			border-top: 1px solid #ccc;
			padding-top: 10px;
		}
	</style>
</head>
<body>
	<div class="header">
		<h1>Troja Service - Monthly Report</h1>
		<h2><?= htmlspecialchars($report_year) ?> <?= date('F', mktime(0,0,0,$report_month,1)) ?></h2>
	</div>
	
	<table>
		<tbody>
			<tr class="section"><th class="title" colspan="2">Income</th></tr>
			<tr class="highlight"><th class="title">Total Month Income</th><td class="amount">Rs. <?= number_format(($monthIncomeTotal ?? (($monthCash+$monthCard+$monthBank+$monthCredit) ?? 0)), 2) ?></td></tr>
			<tr><th>Cash</th><td class="amount">Rs. <?= number_format($monthCash ?? 0, 2) ?></td></tr>
			<tr><th>Card</th><td class="amount">Rs. <?= number_format($monthCard ?? 0, 2) ?></td></tr>
			<tr><th>Bank Transfer</th><td class="amount">Rs. <?= number_format($monthBank ?? 0, 2) ?></td></tr>
			<tr><th>Credit</th><td class="amount">Rs. <?= number_format($monthCredit ?? 0, 2) ?></td></tr>

			<tr class="section"><th class="title" colspan="2">Expenses</th></tr>
			<tr class="highlight"><th class="title">Total Expenses</th><td class="amount">Rs. <?= number_format($monthExpTotal ?? 0, 2) ?></td></tr>
			<tr><th>In House Expenses</th><td class="amount">Rs. <?= number_format($monthExpInHouse ?? 0, 2) ?></td></tr>
			<tr><th>Used Item Expenses</th><td class="amount">Rs. <?= number_format($monthExpUsedItem ?? 0, 2) ?></td></tr>
			<tr class="total"><th class="title">Profit</th><td class="amount">Rs. <?= number_format($monthProfit ?? 0, 2) ?></td></tr>

			<tr class="section"><th class="title" colspan="2">Mechanic</th></tr>
			<tr><th>Item buy cost</th><td class="amount">Rs. <?= number_format($monthMechanicBuy ?? 0, 2) ?></td></tr>
			<tr><th>Item sell amount</th><td class="amount">Rs. <?= number_format($monthMechanicSell ?? 0, 2) ?></td></tr>
			<tr class="highlight"><th class="title">Mechanic Item Profit</th><td class="amount">Rs. <?= number_format($monthMechanicProfit ?? 0, 2) ?></td></tr>
			<tr><th>Mechanic Labour charge</th><td class="amount">Rs. <?= number_format($monthMechanicLabour ?? 0, 2) ?></td></tr>

			<tr class="section"><th class="title" colspan="2">Bank</th></tr>
			<tr><th>Bank deposit (In Hand → Bank)</th><td class="amount">Rs. <?= number_format($bankDeposit ?? 0, 2) ?></td></tr>
			<tr><th>Bank total credits</th><td class="amount">Rs. <?= number_format($monthBankTotal ?? 0, 2) ?></td></tr>
			<tr><th>Bank cheque payments</th><td class="amount">Rs. <?= number_format($monthBankChequePayment ?? 0, 2) ?></td></tr>
			<tr><th>Return to In Hand (Bank/Card → In Hand)</th><td class="amount">Rs. <?= number_format($monthReturnToInHand ?? 0, 2) ?></td></tr>

			<tr class="section"><th class="title" colspan="2">Suppliers</th></tr>
			<tr><th>Month Supplier Invoice amount</th><td class="amount">Rs. <?= number_format($monthSupplierInvoiceAmount ?? 0, 2) ?></td></tr>
			<tr><th>Month Supplier Payment amount</th><td class="amount">Rs. <?= number_format($monthSupplierPaymentAmount ?? 0, 2) ?></td></tr>
			<tr><th>Totally Supplier Invoice Amount</th><td class="amount">Rs. <?= number_format($totallySupplierInvoiceAmount ?? 0, 2) ?></td></tr>
			<tr><th>All Supplier Invoice Payment</th><td class="amount">Rs. <?= number_format($allSupplierInvoicePayment ?? 0, 2) ?></td></tr>
			<tr><th>All Supplier Payment Balance</th><td class="amount">Rs. <?= number_format($allSupplierPaymentBalance ?? 0, 2) ?></td></tr>

			<tr class="section"><th class="title" colspan="2">Loans</th></tr>
			<tr><th>Other Company Loan IN</th><td class="amount">Rs. <?= number_format($companyLoanIn ?? 0, 2) ?></td></tr>
			<tr><th>Other Company Loan OUT</th><td class="amount">Rs. <?= number_format($companyLoanOut ?? 0, 2) ?></td></tr>

			<tr class="section"><th class="title" colspan="2">Credits</th></tr>
			<tr><th>Month Credit Settle Payment</th><td class="amount">Rs. <?= number_format($monthCreditSettle ?? 0, 2) ?></td></tr>
			<tr><th>All Customer Credit Amount</th><td class="amount">Rs. <?= number_format($allCustomerCreditAmount ?? 0, 2) ?></td></tr>
		</tbody>
	</table>
	
	<div class="footer">
		<p>Generated on <?= date('Y-m-d H:i:s') ?> | Troja Service Management System</p>
	</div>
</body>
</html>
