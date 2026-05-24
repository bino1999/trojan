<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Daily_Report_<?= htmlspecialchars($report_date) ?></title>
	<style>
		@page { size: A4 portrait; margin: 1cm; }
		html, body { margin: 0; padding: 0; }
		body { font-family: Arial, sans-serif; font-size: 10px; color: #000; background: #fff; }
		.header { text-align: center; margin-bottom: 12px; border-bottom: 2px solid #333; padding-bottom: 8px; }
		.header h1 { margin: 0; font-size: 16px; }
		.header p { margin: 2px 0; font-size: 10px; }
		table { width: 100%; border-collapse: collapse; }
		th, td { border: 1px solid #000; padding: 6px 8px; font-size: 10px; }
		th.title { background: #f0f4f7; text-align: left; }
		tr.highlight td, tr.highlight th { background: #e6f2f5; font-weight: bold; }
		tr.section td, tr.section th { background: #f8f9fa; font-weight: bold; border-color: #000; }
		.small { font-size: 9px; }
		@media print {
			.no-print { display: none !important; }
		}
	</style>
</head>
<body onload="window.print()">
	<div class="header">
		<h1>Custom Daily Report</h1>
		<p>Date: <?= htmlspecialchars($report_date) ?></p>
	</div>
	<table>
		<tbody>
			<tr class="section"><th class="title" colspan="2">Opening</th></tr>
			<tr class="highlight"><th class="title">Day Start Cash in Hand</th><td>Rs. <?= number_format($day_start_cash_in_hand ?? 0, 2) ?></td></tr>

			<tr class="section"><th class="title" colspan="2">Income Overview</th></tr>
			<tr><th class="title">Day Quick Bill Income</th><td>Rs. <?= number_format($day_quick_bill_income ?? 0, 2) ?></td></tr>
			<tr><th class="title">Day Services Income</th><td></td></tr>
			<tr><td class="small">- Day Services Job (service_type = normal)</td><td>Rs. <?= number_format($day_services_job_income ?? 0, 2) ?></td></tr>
			<tr><td class="small">- Day Mechanical Job (service_type = mechanical)</td><td>Rs. <?= number_format($day_mechanical_job_income ?? 0, 2) ?></td></tr>
			<tr><td class="small">- Day Common Jobs (normal + mechanical + estimate)</td><td>Rs. <?= number_format($day_common_jobs_income ?? 0, 2) ?></td></tr>
			<tr class="highlight"><th class="title">Day Income Total</th><td>Rs. <?= number_format($day_income_total ?? 0, 2) ?></td></tr>

			<tr class="section"><th class="title" colspan="2">Payment Methods (Received)</th></tr>
			<tr><th class="title">Day Cash Income</th><td>Rs. <?= number_format($day_cash_income ?? 0, 2) ?></td></tr>
			<tr><th class="title">Day Card Payment</th><td>Rs. <?= number_format($day_card_payment ?? 0, 2) ?></td></tr>
			<tr><th class="title">Day Bank Transfer</th><td>Rs. <?= number_format($day_bank_transfer ?? 0, 2) ?></td></tr>
			<tr><th class="title">Day Cheque Payment</th><td>Rs. <?= number_format($day_cheque_payment ?? 0, 2) ?></td></tr>
			<tr><th class="title">Day Credit Amount</th><td>Rs. <?= number_format($day_credit_amount ?? 0, 2) ?></td></tr>

			<tr class="section"><th class="title" colspan="2">Cash & Expenses</th></tr>
			<tr class="highlight"><th class="title">All Cash In Hand</th><td>Rs. <?= number_format($all_cash_in_hand ?? 0, 2) ?></td></tr>
			<tr class="highlight"><th class="title">Day Expenses</th><td>Rs. <?= number_format($day_expenses ?? 0, 2) ?></td></tr>
			<tr><td class="small">- Expense Cash</td><td>Rs. <?= number_format($expense_cash ?? 0, 2) ?></td></tr>
			<tr><td class="small">- Expense Card</td><td>Rs. <?= number_format($expense_card ?? 0, 2) ?></td></tr>
			<tr><td class="small">- Expense Bank Transfer</td><td>Rs. <?= number_format($expense_bank_transfer ?? 0, 2) ?></td></tr>
			<tr><td class="small">- Expense Cheque</td><td>Rs. <?= number_format($expense_cheque ?? 0, 2) ?></td></tr>
			<tr><th class="title">Bank Deposit</th><td>Rs. <?= number_format($bank_deposit ?? 0, 2) ?></td></tr>

			<tr class="section"><th class="title" colspan="2">Balances</th></tr>
			<tr><th class="title">Day End Cash in Hand</th><td>Rs. <?= number_format($day_end_cash_in_hand ?? 0, 2) ?></td></tr>
			<tr><th class="title">Day End Bank Balance</th><td>Rs. <?= number_format($day_end_bank_balance ?? 0, 2) ?></td></tr>
			<tr><th class="title">Day End Card Payment Balance</th><td>Rs. <?= number_format($day_end_card_balance ?? 0, 2) ?></td></tr>

			<tr class="section"><th class="title" colspan="2">Mechanic Outsource</th></tr>
			<tr class="highlight"><th class="title">Mechanic Item Buy Cost</th><td>Rs. <?= number_format($mechanic_item_buy_cost ?? 0, 2) ?></td></tr>
			<tr class="highlight"><th class="title">Mechanic Item Sell Amount</th><td>Rs. <?= number_format($mechanic_item_sell_amount ?? 0, 2) ?></td></tr>
			<tr class="highlight"><th class="title">Mechanic Profit</th><td>Rs. <?= number_format($mechanic_profit ?? 0, 2) ?></td></tr>

			<tr class="highlight"><th class="title">Day Profit</th><td>Rs. <?= number_format($day_profit ?? 0, 2) ?></td></tr>

			<tr class="section"><th class="title" colspan="2">Monthly Summary (Selected Month)</th></tr>
			<tr class="highlight"><th class="title">Total Month Income</th><td>Rs. <?= number_format($monthIncomeTotal ?? 0, 2) ?></td></tr>
			<tr><th class="title">Cash</th><td>Rs. <?= number_format($monthCash ?? 0, 2) ?></td></tr>
			<tr><th class="title">Card</th><td>Rs. <?= number_format($monthCard ?? 0, 2) ?></td></tr>
			<tr><th class="title">Bank Transfer</th><td>Rs. <?= number_format($monthBank ?? 0, 2) ?></td></tr>
			<tr><th class="title">Credit</th><td>Rs. <?= number_format($monthCredit ?? 0, 2) ?></td></tr>
			<tr class="highlight"><th class="title">Total Expenses</th><td>Rs. <?= number_format($monthExpTotal ?? 0, 2) ?></td></tr>
			<tr><th class="title">In House Expenses</th><td>Rs. <?= number_format($monthExpInHouse ?? 0, 2) ?></td></tr>
			<tr><th class="title">Used Item Expenses</th><td>Rs. <?= number_format($monthExpUsedItem ?? 0, 2) ?></td></tr>
			<tr class="highlight"><th class="title">Profit</th><td>Rs. <?= number_format($monthProfit ?? 0, 2) ?></td></tr>
		</tbody>
	</table>
</body>
</html>
