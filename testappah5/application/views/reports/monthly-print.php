<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Monthly_Report</title>
	<style>
		@page { size: A4 portrait; margin: 1cm; }
		body { font-family: Arial, sans-serif; font-size: 10px; }
		table { width: 100%; border-collapse: collapse; }
		th, td { border: 1px solid #000; padding: 6px 8px; }
		th.title { background: #f0f4f7; text-align: left; }
		tr.section th { background: #f8f9fa; font-weight: bold; }
		tr.highlight th, tr.highlight td { background: #e6f2f5; font-weight: bold; }
	</style>
</head>
<body onload="window.print()">
	<h3>Custom Monthly Report - <?= htmlspecialchars($year) ?> <?= date('F', mktime(0,0,0,$month,1)) ?></h3>
	<table>
		<tbody>
			<tr class="section"><th class="title">Income</th><td></td></tr>
			<tr class="highlight"><th class="title">Total Month Income</th><td>Rs. <?= number_format(($monthIncomeTotal ?? (($monthCash+$monthCard+$monthBank+$monthCredit) ?? 0)), 2) ?></td></tr>
			<tr><th>Cash</th><td>Rs. <?= number_format($monthCash ?? 0, 2) ?></td></tr>
			<tr><th>Card</th><td>Rs. <?= number_format($monthCard ?? 0, 2) ?></td></tr>
			<tr><th>Bank Transfer</th><td>Rs. <?= number_format($monthBank ?? 0, 2) ?></td></tr>
			<tr><th>Credit</th><td>Rs. <?= number_format($monthCredit ?? 0, 2) ?></td></tr>

			<tr class="section"><th class="title">Expenses</th><td></td></tr>
			<tr class="highlight"><th class="title">Total Expenses</th><td>Rs. <?= number_format($monthExpTotal ?? 0, 2) ?></td></tr>
			<tr><th>In House Expenses</th><td>Rs. <?= number_format($monthExpInHouse ?? 0, 2) ?></td></tr>
			<tr><th>Used Item Expenses</th><td>Rs. <?= number_format($monthExpUsedItem ?? 0, 2) ?></td></tr>
			<tr class="highlight"><th class="title">Profit</th><td>Rs. <?= number_format($monthProfit ?? 0, 2) ?></td></tr>

			<tr class="section"><th class="title">Mechanic</th><td></td></tr>
			<tr><th>Item buy cost</th><td>Rs. <?= number_format($monthMechanicBuy ?? 0, 2) ?></td></tr>
			<tr><th>Item sell amount</th><td>Rs. <?= number_format($monthMechanicSell ?? 0, 2) ?></td></tr>
			<tr class="highlight"><th class="title">Mechanic Item Profit</th><td>Rs. <?= number_format($monthMechanicProfit ?? 0, 2) ?></td></tr>
			<tr><th>Mechanic Labour charge</th><td>Rs. <?= number_format($monthMechanicLabour ?? 0, 2) ?></td></tr>

			<tr class="section"><th class="title">Bank</th><td></td></tr>
			<tr><th>Bank deposit (In Hand → Bank)</th><td>Rs. <?= number_format($bankDeposit ?? 0, 2) ?></td></tr>
			<tr><th>Bank total credits</th><td>Rs. <?= number_format($monthBankTotal ?? 0, 2) ?></td></tr>
			<tr><th>Bank cheque payments</th><td>Rs. <?= number_format($monthBankChequePayment ?? 0, 2) ?></td></tr>
			<tr><th>Return to In Hand (Bank/Card → In Hand)</th><td>Rs. <?= number_format($monthReturnToInHand ?? 0, 2) ?></td></tr>

			<tr class="section"><th class="title">Suppliers</th><td></td></tr>
			<tr><th>Month Supplier Invoice amount</th><td>Rs. <?= number_format($monthSupplierInvoiceAmount ?? 0, 2) ?></td></tr>
			<tr><th>Month Supplier Payment amount</th><td>Rs. <?= number_format($monthSupplierPaymentAmount ?? 0, 2) ?></td></tr>
			<tr><th>Totally Supplier Invoice Amount</th><td>Rs. <?= number_format($totallySupplierInvoiceAmount ?? 0, 2) ?></td></tr>
			<tr><th>All Supplier Invoice Payment</th><td>Rs. <?= number_format($allSupplierInvoicePayment ?? 0, 2) ?></td></tr>
			<tr><th>All Supplier Payment Balance</th><td>Rs. <?= number_format($allSupplierPaymentBalance ?? 0, 2) ?></td></tr>

			<tr class="section"><th class="title">Loans</th><td></td></tr>
			<tr><th>Other Company Loan IN</th><td>Rs. <?= number_format($companyLoanIn ?? 0, 2) ?></td></tr>
			<tr><th>Other Company Loan OUT</th><td>Rs. <?= number_format($companyLoanOut ?? 0, 2) ?></td></tr>

			<tr class="section"><th class="title">Credits</th><td></td></tr>
			<tr><th>Month Credit Settle Payment</th><td>Rs. <?= number_format($monthCreditSettle ?? 0, 2) ?></td></tr>
			<tr><th>All Customer Credit Amount</th><td>Rs. <?= number_format($allCustomerCreditAmount ?? 0, 2) ?></td></tr>
		</tbody>
	</table>
</body>
</html>
