<div class="page-content">
	<div class="container-fluid">
		<div class="row">
			<div class="col-12"><h4>Custom Monthly Report</h4></div>
		</div>
		<div class="row mb-3">
			<div class="col-md-8 col-lg-7">
				<div class="row g-2">
					<div class="col-6 col-md-4">
						<label class="form-label">Year</label>
						<input type="number" id="monthYear" class="form-control" value="<?= date('Y') ?>">
					</div>
					<div class="col-6 col-md-4">
						<label class="form-label">Month</label>
						<select id="monthNum" class="form-select">
							<?php for($m=1;$m<=12;$m++): ?>
							<option value="<?= $m ?>" <?= ($m==(int)date('n'))?'selected':'' ?>><?= date('F', mktime(0,0,0,$m,1)) ?></option>
							<?php endfor; ?>
						</select>
					</div>
					<div class="col-6 col-md-2 d-flex align-items-end"><button id="btnLoadMonthly" class="btn btn-primary w-100">Load</button></div>
					<div class="col-6 col-md-2 d-flex align-items-end"><button id="btnPrintMonthly" class="btn btn-secondary w-100">Print</button></div>
                    <div class="col-6 col-md-2 d-flex align-items-end"><button id="btnPdfMonthly" class="btn btn-success w-100">PDF</button></div>
                    <div class="col-6 col-md-4 d-flex align-items-end"><button id="btnDetailedMonthly" class="btn btn-info w-100">Detailed Report</button></div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-8 col-lg-7">
				<table class="table table-bordered table-sm align-middle" id="monthlyTable">
					<tbody>
						<tr class="table-primary"><th>MONTH</th><td id="monthTitle">-</td></tr>
						<tr class="table-light"><th>MONTH Selection</th><td id="monthSelection">-</td></tr>

						<tr class="table-secondary"><th colspan="2">Income</th></tr>
						<tr class="table-success fw-bold"><th>Total Month Income</th><td id="monthIncomeTotal">-</td></tr>
						<tr><td class="ps-4">Cash</td><td id="monthCash">-</td></tr>
						<tr><td class="ps-4">Card</td><td id="monthCard">-</td></tr>
						<tr><td class="ps-4">Bank Transfer</td><td id="monthBank">-</td></tr>
						<tr><td class="ps-4">Credit</td><td id="monthCredit">-</td></tr>

						<tr class="table-secondary"><th colspan="2">Expenses</th></tr>
						<tr class="table-danger fw-bold"><th>Total Expenses</th><td id="monthExpTotal">-</td></tr>
						<tr><td class="ps-4">In House Expenses</td><td id="monthExpInHouse">-</td></tr>
						<tr><td class="ps-4">Used Item Expenses</td><td id="monthExpUsedItem">-</td></tr>

						<tr class="table-info"><th>Profit</th><td id="monthProfit">-</td></tr>

						<tr class="table-secondary"><th colspan="2">Mechanic</th></tr>
						<tr><td class="ps-4">Item buy cost</td><td id="monthMechanicBuy">-</td></tr>
						<tr><td class="ps-4">Item sell amount</td><td id="monthMechanicSell">-</td></tr>
						<tr class="table-warning fw-bold"><th>Mechanic Item Profit</th><td id="monthMechanicProfit">-</td></tr>
						<tr><td class="ps-4">Mechanic Labour charge</td><td id="monthMechanicLabour">-</td></tr>

						<tr class="table-secondary"><th colspan="2">Bank</th></tr>
						<tr><td class="ps-4">Bank deposit (In Hand → Bank)</td><td id="monthBankDeposit">-</td></tr>
						<tr><td class="ps-4">Bank total credits</td><td id="monthBankTotal">-</td></tr>
						<tr><td class="ps-4">Bank cheque payments</td><td id="monthBankChequePayment">-</td></tr>
						<tr><td class="ps-4">Return to In Hand (Bank/Card → In Hand)</td><td id="monthReturnToInHand">-</td></tr>

						<tr class="table-secondary"><th colspan="2">Suppliers</th></tr>
						<tr><td class="ps-4">Month Supplier Invoice amount</td><td id="monthSupplierInvoice">-</td></tr>
						<tr><td class="ps-4">Month Supplier Payment amount</td><td id="monthSupplierPayment">-</td></tr>
						<tr><td class="ps-4">Totally Supplier Invoice Amount</td><td id="totallySupplierInvoice">-</td></tr>
						<tr><td class="ps-4">All Supplier Invoice Payment</td><td id="allSupplierInvoicePayment">-</td></tr>
						<tr><td class="ps-4">All Supplier Payment Balance</td><td id="allSupplierPaymentBalance">-</td></tr>

						<tr class="table-secondary"><th colspan="2">Loans</th></tr>
						<tr><td class="ps-4">Other Company Loan IN</td><td id="companyLoanIn">-</td></tr>
						<tr><td class="ps-4">Other Company Loan OUT</td><td id="companyLoanOut">-</td></tr>

						<tr class="table-secondary"><th colspan="2">Credits</th></tr>
						<tr><td class="ps-4">Month Credit Settle Payment</td><td id="monthCreditSettle">-</td></tr>
						<tr><td class="ps-4">All Customer Credit Amount</td><td id="allCustomerCreditAmount">-</td></tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
<script>
(function(){
	function fmt(n){ return (Number(n||0)).toLocaleString(undefined,{minimumFractionDigits:2, maximumFractionDigits:2}); }
	function rs(n){ return 'Rs. ' + fmt(n||0); }
	function load(){
		const year = document.getElementById('monthYear').value;
		const month = document.getElementById('monthNum').value;
		fetch('<?= base_url('reports/monthly/data'); ?>', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:new URLSearchParams({year, month}) })
		.then(r=>r.json()).then(j=>{
			if(j.status!=='success'){ alert('Failed to load'); return; }
			const d=j.data; const mName = new Date(d.year, d.month-1, 1).toLocaleString(undefined,{month:'long'});
			document.getElementById('monthTitle').textContent = mName.toUpperCase();
			document.getElementById('monthSelection').textContent = d.year+' '+mName;
			document.getElementById('monthCash').textContent = rs(d.monthCash);
			document.getElementById('monthCard').textContent = rs(d.monthCard);
			document.getElementById('monthBank').textContent = rs(d.monthBank);
			document.getElementById('monthCredit').textContent = rs(d.monthCredit);
			document.getElementById('monthIncomeTotal').textContent = rs(d.monthIncomeTotal);
			document.getElementById('monthExpTotal').textContent = rs(d.monthExpTotal);
			document.getElementById('monthExpInHouse').textContent = rs(d.monthExpInHouse);
			document.getElementById('monthExpUsedItem').textContent = rs(d.monthExpUsedItem);
			document.getElementById('monthProfit').textContent = rs(d.monthProfit);
			document.getElementById('monthCreditSettle').textContent = rs(d.monthCreditSettle);
			document.getElementById('allCustomerCreditAmount').textContent = rs(d.allCustomerCreditAmount);
			document.getElementById('monthBankDeposit').textContent = rs(d.bankDeposit);
			document.getElementById('monthBankTotal').textContent = rs(d.monthBankTotal);
			document.getElementById('monthBankChequePayment').textContent = rs(d.monthBankChequePayment);
			document.getElementById('monthReturnToInHand').textContent = rs(d.monthReturnToInHand);
			document.getElementById('monthSupplierInvoice').textContent = rs(d.monthSupplierInvoiceAmount);
			document.getElementById('monthSupplierPayment').textContent = rs(d.monthSupplierPaymentAmount);
			document.getElementById('totallySupplierInvoice').textContent = rs(d.totallySupplierInvoiceAmount);
			document.getElementById('allSupplierInvoicePayment').textContent = rs(d.allSupplierInvoicePayment);
			document.getElementById('allSupplierPaymentBalance').textContent = rs(d.allSupplierPaymentBalance);
			document.getElementById('companyLoanIn').textContent = rs(d.companyLoanIn);
			document.getElementById('companyLoanOut').textContent = rs(d.companyLoanOut);
			document.getElementById('monthMechanicBuy').textContent = rs(d.monthMechanicBuy ?? 0);
			document.getElementById('monthMechanicSell').textContent = rs(d.monthMechanicSell ?? 0);
			document.getElementById('monthMechanicProfit').textContent = rs((d.monthMechanicProfit ?? 0));
			document.getElementById('monthMechanicLabour').textContent = rs(d.monthMechanicLabour ?? 0);
		}).catch(()=>alert('Server error'));
	}
	document.getElementById('btnLoadMonthly').addEventListener('click', load);
    document.getElementById('btnDetailedMonthly').addEventListener('click', function(){
        const year = document.getElementById('monthYear').value;
        const month = document.getElementById('monthNum').value;
        const url = '<?= base_url('reports/monthly/detailed'); ?>' + '?year=' + encodeURIComponent(year) + '&month=' + encodeURIComponent(month);
        window.open(url, '_blank');
    });
	document.getElementById('btnPrintMonthly').addEventListener('click', function(){
		const year = document.getElementById('monthYear').value;
		const month = document.getElementById('monthNum').value;
		window.open('<?= base_url('reports/monthly/print'); ?>?year='+encodeURIComponent(year)+'&month='+encodeURIComponent(month),'_blank');
	});
	document.getElementById('btnPdfMonthly').addEventListener('click', function(){
		const year = document.getElementById('monthYear').value;
		const month = document.getElementById('monthNum').value;
		window.open('<?= base_url('reports/monthlyPdf'); ?>?year='+encodeURIComponent(year)+'&month='+encodeURIComponent(month),'_blank');
	});
	load();
})();
</script>
