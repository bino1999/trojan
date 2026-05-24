<div class="page-content">
	<div class="container-fluid">
		<div class="row">
			<div class="col-12">
				<h4>Custom Daily Report</h4>
			</div>
		</div>

		<div class="row mb-3">
			<div class="col-md-8 col-lg-7">
				<div class="row g-2">
					<div class="col-12 col-sm-6 col-md-4">
						<label for="reportDate" class="form-label">Select Date</label>
						<input type="date" id="reportDate" class="form-control" value="<?= date('Y-m-d') ?>">
					</div>
					<div class="col-6 col-md-4 d-flex align-items-end">
						<button id="btnLoadReport" class="btn btn-primary w-100">Load</button>
					</div>
					<div class="col-6 col-md-4 d-flex align-items-end">
						<button id="btnPrintReport" class="btn btn-secondary w-100">Print (A4)</button>
					</div>
					<div class="col-6 col-md-4 d-flex align-items-end">
						<button id="btnDownloadPdf" class="btn btn-success w-100">Download PDF</button>
					</div>
					<div class="col-6 col-md-4 d-flex align-items-end">
						<button id="btnDetailedReport" class="btn btn-info w-100">Detailed Report</button>
					</div>
				</div>
			</div>
		</div>

		<div class="row">
			<div class="col-md-8 col-lg-7">
				<table class="table table-bordered table-sm align-middle">
					<tbody id="dailyReportTable">
						<tr class="table-light"><th colspan="2">Opening</th></tr>
						<tr class="table-warning"><th>Day Start Cash in Hand</th><td id="day_start_cash_in_hand">-</td></tr>
						<tr><td colspan="2" style="background:#f8f9fa"></td></tr>

						<tr class="table-light"><th colspan="2">Income Overview</th></tr>
						<tr><th>Day Quick Bill Income</th><td id="day_quick_bill_income">-</td></tr>
						<tr><th>Day Services Income</th><td></td></tr>
						<tr><td class="ps-4">- Day Services Job (service_type = normal)</td><td id="day_services_job_income">-</td></tr>
						<tr><td class="ps-4">- Day Mechanical Job (service_type = mechanical)</td><td id="day_mechanical_job_income">-</td></tr>
						<tr><td class="ps-4">- Day Common Jobs (normal + mechanical + estimate)</td><td id="day_common_jobs_income">-</td></tr>
						<tr class="table-info"><th>Day Income Total</th><td id="day_income_total">-</td></tr>
						<tr><td colspan="2" style="background:#f8f9fa"></td></tr>

						<tr class="table-light"><th colspan="2">Payment Methods (Received)</th></tr>
						<tr><th>Day Cash Income</th><td id="day_cash_income">-</td></tr>
						<tr><th>Day Card Payment</th><td id="day_card_payment">-</td></tr>
						<tr><th>Day Bank Transfer</th><td id="day_bank_transfer">-</td></tr>
						<tr><th>Day Cheque Payment</th><td id="day_cheque_payment">-</td></tr>
						<tr><th>Day Credit Amount</th><td id="day_credit_amount">-</td></tr>
						<tr><td colspan="2" style="background:#f8f9fa"></td></tr>

						<tr class="table-light"><th colspan="2">Cash & Expenses</th></tr>
						<tr class="table-success"><th>All Cash In Hand</th><td id="all_cash_in_hand">-</td></tr>
						<tr class="table-danger"><th>Day Expenses</th><td id="day_expenses">-</td></tr>
						<tr><td class="ps-4">- Expense Cash</td><td id="expense_cash">-</td></tr>
						<tr><td class="ps-4">- Expense Card</td><td id="expense_card">-</td></tr>
						<tr><td class="ps-4">- Expense Bank Transfer</td><td id="expense_bank_transfer">-</td></tr>
						<tr><td class="ps-4">- Expense Cheque</td><td id="expense_cheque">-</td></tr>
						<tr><th>Bank Deposit</th><td id="bank_deposit">-</td></tr>
						<tr><td colspan="2" style="background:#f8f9fa"></td></tr>

						<tr class="table-light"><th colspan="2">Balances</th></tr>
						<tr><th>Day End Cash in Hand</th><td id="day_end_cash_in_hand">-</td></tr>
						<tr><th>Day End Bank Balance</th><td id="day_end_bank_balance">-</td></tr>
						<tr><th>Day End Card Payment Balance</th><td id="day_end_card_balance">-</td></tr>
						<tr><td colspan="2" style="background:#f8f9fa"></td></tr>

						<tr class="table-light"><th colspan="2">Mechanic Outsource</th></tr>
						<tr class="table-secondary"><th>Mechanic Item Buy Cost</th><td id="mechanic_item_buy_cost">-</td></tr>
						<tr class="table-secondary"><th>Mechanic Item Sell Amount</th><td id="mechanic_item_sell_amount">-</td></tr>
						<tr class="table-secondary"><th>Mechanic Profit</th><td id="mechanic_profit">-</td></tr>
						<tr><td colspan="2" style="background:#f8f9fa"></td></tr>

						<tr class="table-primary"><th>Day Profit</th><td id="day_profit">-</td></tr>
						<tr><td colspan="2" style="background:#f8f9fa"></td></tr>

						<tr class="table-secondary"><th colspan="2">Monthly Summary (Selected Month)</th></tr>
						<tr class="table-success fw-bold"><th>Total Month Income</th><td id="monthIncomeTotal">-</td></tr>
						<tr><td class="ps-4">Cash</td><td id="monthCash">-</td></tr>
						<tr><td class="ps-4">Card</td><td id="monthCard">-</td></tr>
						<tr><td class="ps-4">Bank Transfer</td><td id="monthBank">-</td></tr>
						<tr><td class="ps-4">Credit</td><td id="monthCredit">-</td></tr>
						<tr class="table-danger fw-bold"><th>Total Expenses</th><td id="monthExpTotal">-</td></tr>
						<tr><td class="ps-4">In House Expenses</td><td id="monthExpInHouse">-</td></tr>
						<tr><td class="ps-4">Used Item Expenses</td><td id="monthExpUsedItem">-</td></tr>
						<tr class="table-info"><th>Profit</th><td id="monthProfit">-</td></tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>

<script>
(function() {
	function fmt(n){ return (Number(n||0)).toLocaleString(undefined,{minimumFractionDigits:2, maximumFractionDigits:2}); }
	function setRow(id, val){ const el=document.getElementById(id); if(!el) return; el.textContent = (val===null||val===undefined||val==='') ? '' : 'Rs. ' + fmt(val); }
	function load(){
		const date = document.getElementById('reportDate').value;
		const btn = document.getElementById('btnLoadReport');
		btn.disabled = true; btn.textContent = 'Loading...';
		fetch('<?= base_url('reports/daily/data'); ?>', {
			method: 'POST',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
			body: new URLSearchParams({ date })
		}).then(r=>r.json()).then(json=>{
			if(json.status==='success'){
				const d = json.data;
				setRow('day_start_cash_in_hand', d.day_start_cash_in_hand);
				setRow('day_quick_bill_income', d.day_quick_bill_income);
				setRow('day_services_job_income', d.day_services_job_income);
				setRow('day_mechanical_job_income', d.day_mechanical_job_income);
				setRow('day_common_jobs_income', d.day_common_jobs_income);
				setRow('day_income_total', d.day_income_total);
				setRow('day_cash_income', d.day_cash_income);
				setRow('day_card_payment', d.day_card_payment);
				setRow('day_bank_transfer', d.day_bank_transfer);
				setRow('day_cheque_payment', d.day_cheque_payment);
				setRow('day_credit_amount', d.day_credit_amount);
				setRow('all_cash_in_hand', d.all_cash_in_hand);
				setRow('day_expenses', d.day_expenses);
				setRow('expense_cash', d.expense_cash);
				setRow('expense_card', d.expense_card);
				setRow('expense_bank_transfer', d.expense_bank_transfer);
				setRow('expense_cheque', d.expense_cheque);
				setRow('bank_deposit', d.bank_deposit);
				setRow('day_end_cash_in_hand', d.day_end_cash_in_hand);
				setRow('day_end_bank_balance', d.day_end_bank_balance);
				setRow('day_end_card_balance', d.day_end_card_balance);
				setRow('mechanic_item_buy_cost', d.mechanic_item_buy_cost);
				setRow('mechanic_item_sell_amount', d.mechanic_item_sell_amount);
				setRow('mechanic_profit', d.mechanic_profit);
				setRow('day_profit', d.day_profit);
				// Monthly summary (selected month)
				setRow('monthIncomeTotal', d.monthIncomeTotal);
				setRow('monthCash', d.monthCash);
				setRow('monthCard', d.monthCard);
				setRow('monthBank', d.monthBank);
				setRow('monthCredit', d.monthCredit);
				setRow('monthExpTotal', d.monthExpTotal);
				setRow('monthExpInHouse', d.monthExpInHouse);
				setRow('monthExpUsedItem', d.monthExpUsedItem);
				setRow('monthProfit', d.monthProfit);
			} else {
				alert('Failed to load report');
			}
		}).catch(()=> alert('Server error loading report'))
		.finally(()=>{ btn.disabled=false; btn.textContent='Load'; });
	}
	document.getElementById('btnLoadReport').addEventListener('click', load);
	document.getElementById('btnPrintReport').addEventListener('click', function(){
		const date = document.getElementById('reportDate').value;
		const url = '<?= base_url('reports/daily/print'); ?>' + '?date=' + encodeURIComponent(date);
		window.open(url, '_blank');
	});
	document.getElementById('btnDownloadPdf').addEventListener('click', function(){
		const date = document.getElementById('reportDate').value;
		const url = '<?= base_url('reports/daily/pdf'); ?>' + '?date=' + encodeURIComponent(date);
		window.open(url, '_blank');
	});
	document.getElementById('btnDetailedReport').addEventListener('click', function(){
		const date = document.getElementById('reportDate').value;
		const url = '<?= base_url('reports/daily/detailed'); ?>' + '?date=' + encodeURIComponent(date);
		window.open(url, '_blank');
	});
	// auto-load for today
	load();
})();
</script>
