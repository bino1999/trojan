<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Reports extends MY_Controller {
	public function __construct()
	{
		parent::__construct();
		$this->load->model('QuickBill_model');
		$this->load->model('Services_model');
		$this->load->model('Expenses_model');
		$this->load->model('Wallet_model');
		$this->load->model('Purchase_model');
	}

	public function daily()
	{
		$this->require_permission('reports.view');
		$data['mainMenuName'] = 'report';
		$data['subMenuName'] = 'daily-report';

		$this->load->view('layout/header');
		$this->load->view('layout/top_navbar');
		$this->load->view('layout/left_sidebar', $data);
		$this->load->view('reports/daily', $data);
		$this->load->view('layout/footer');
	}

	public function dailyData()
	{
		$date = $this->input->post('date') ?: date('Y-m-d');
		$start = $date;
		$end = $date;

		// Month window for the selected date
		$monthStart = date('Y-m-01', strtotime($date));
		$monthEnd = date('Y-m-t', strtotime($date));

		// Day Start Cash in Hand = running balance at the start of selected day
		$openingTxn = $this->db->select('running_balance')
			->from('account_transactions')
			->where('account_slug', 'in_hand')
			->where('created_at <', $start . ' 00:00:00')
			->order_by('created_at', 'DESC')
			->order_by('txn_id', 'DESC')
			->limit(1)
			->get()->row();
		$startCashInHand = $openingTxn ? floatval($openingTxn->running_balance) : 0.0;

		$startBank = $this->Wallet_model->getAccountBalance('bank');
		$startCard = $this->Wallet_model->getAccountBalance('card_machine');

		// Quick Bill totals for the day (headline only)
		$qb = $this->db->select_sum('final_bill_amount', 'total')
			->where('DATE(created_at) >=', $start)
			->where('DATE(created_at) <=', $end)
			->get('quick_bill')->row();
		$dayQuickBillIncome = floatval($qb->total ?: 0);

		// Aggregate payments by method (Quick Bills)
		$qbPayments = $this->db->select('LOWER(payment_method) as method, SUM(paid_amount) as total', false)
			->from('quick_bill_payments')
			->where('DATE(payment_date) >=', $start)
			->where('DATE(payment_date) <=', $end)
			->group_by('LOWER(payment_method)')
			->get()->result();

		// Aggregate payments by method (Service Jobs only; exclude rows linked to quick bills)
		$sjPayments = $this->db->select('LOWER(payment_method) as method, SUM(paid_amount) as total', false)
			->from('services_job_payments')
			->where('DATE(created_at) >=', $start)
			->where('DATE(created_at) <=', $end)
			->where('deleted_by IS NULL', null, false)
			->where('cheque_return_by IS NULL', null, false)
			->where('quick_bill_id IS NULL', null, false)
			->where('service_job_id IS NOT NULL', null, false)
			->group_by('LOWER(payment_method)')
			->get()->result();

		$methodTotals = [];
		foreach ($qbPayments as $p) { $key = strtolower($p->method); $methodTotals[$key] = ($methodTotals[$key] ?? 0) + floatval($p->total); }
		foreach ($sjPayments as $p) { $key = strtolower($p->method); $methodTotals[$key] = ($methodTotals[$key] ?? 0) + floatval($p->total); }

		$dayCashIncome    = $methodTotals['cash'] ?? 0;
		$dayCardPayment   = $methodTotals['card'] ?? 0;
		$dayChequePayment = $methodTotals['cheque'] ?? 0;
		$dayBankTransfer  = ($methodTotals['bank transfer'] ?? 0) + ($methodTotals['bank_transfer'] ?? 0);
		$dayCreditAmount  = $methodTotals['credit'] ?? 0;

		$normalized = "REPLACE(LOWER(service_type), ' ', '')";

		$dayServicesJobIncome = (float) $this->db->select('COALESCE(SUM(final_bill_amount),0) AS total', false)
			->from('services_job')
			->where("DATE(service_date) >=", $start)
			->where("DATE(service_date) <=", $end)
			->group_start()->where("{$normalized} = 'normal'", null, false)->or_where("{$normalized} = 'n'", null, false)->group_end()
			->get()->row()->total;

		$dayMechanicalJobIncome = (float) $this->db->select('COALESCE(SUM(final_bill_amount),0) AS total', false)
			->from('services_job')
			->where("DATE(service_date) >=", $start)
			->where("DATE(service_date) <=", $end)
			->group_start()->where("{$normalized} = 'mechanical'", null, false)->or_where("{$normalized} = 'm'", null, false)->group_end()
			->get()->row()->total;

		$dayCommonJobsIncome = (float) $this->db->select('COALESCE(SUM(final_bill_amount),0) AS total', false)
			->from('services_job')
			->where("DATE(service_date) >=", $start)
			->where("DATE(service_date) <=", $end)
			->where("( (FIND_IN_SET('normal', {$normalized}) > 0) + (FIND_IN_SET('mechanical', {$normalized}) > 0) + (FIND_IN_SET('estimate', {$normalized}) > 0) + (FIND_IN_SET('n', {$normalized}) > 0) + (FIND_IN_SET('m', {$normalized}) > 0) + (FIND_IN_SET('e', {$normalized}) > 0) ) >= 2", null, false)
			->get()->row()->total;

		$dayServicesIncome = null;
		$dayIncomeTotal = $dayQuickBillIncome + $dayServicesJobIncome + $dayMechanicalJobIncome + $dayCommonJobsIncome;

		// Expenses by payment method
		$expRows = $this->db->select('LOWER(payment_method) as method, SUM(amount) as total', false)
			->from('expenses')
			->where('expense_date >=', $start)
			->where('expense_date <=', $end)
			->group_by('LOWER(payment_method)')
			->get()->result();
		$expTotals = [];
		foreach ($expRows as $er) { $expTotals[strtolower($er->method)] = floatval($er->total); }
		$dayExpensesCash    = $expTotals['cash'] ?? 0;
		$dayExpensesCard    = $expTotals['card'] ?? 0;
		$dayExpensesCheque  = $expTotals['cheque'] ?? 0;
		$dayExpensesBankTr  = ($expTotals['bank transfer'] ?? 0) + ($expTotals['bank_transfer'] ?? 0);
		$dayExpensesTotal   = $dayExpensesCash + $dayExpensesCard + $dayExpensesCheque + $dayExpensesBankTr;

		// Bank deposit: transfers from In Hand to Bank (credit into bank via transfer)
		$bankDeposit = (float) $this->db->select('COALESCE(SUM(amount),0) AS total', false)
			->from('account_transactions')
			->where('account_slug', 'bank')
			->where('txn_type', 'credit')
			->where('reference_type', 'transfer')
			->where("DATE(created_at) >=", $start)
			->where("DATE(created_at) <=", $end)
			->like('description', 'Transfer from', 'both')
			->like('description', 'In Hand', 'both')
			->get()->row()->total;

		$allCashInHand = $startCashInHand + $dayCashIncome;

		$dayEndCashInHand = $this->Wallet_model->getAccountBalance('in_hand');
		$dayEndBankBalance = $this->Wallet_model->getAccountBalance('bank');
		$dayEndCardBalance = $this->Wallet_model->getAccountBalance('card_machine');

		// Mechanic outsource totals for the day
		$buyRow = $this->db->select('COALESCE(SUM(op.purchased_price),0) AS total', false)
			->from('outsource_parts op')
			->join('services_job sj', 'sj.job_id = op.service_job_id', 'left')
			->where('DATE(sj.service_date) >=', $start)
			->where('DATE(sj.service_date) <=', $end)
			->get()->row();
		$mechanicItemBuyCost = (float) ($buyRow ? $buyRow->total : 0);

		$sellRow = $this->db->select('COALESCE(SUM(op.invoice_price),0) AS total', false)
			->from('outsource_parts op')
			->join('services_job sj', 'sj.job_id = op.service_job_id', 'left')
			->where('DATE(sj.service_date) >=', $start)
			->where('DATE(sj.service_date) <=', $end)
			->get()->row();
		$mechanicItemSellAmount = (float) ($sellRow ? $sellRow->total : 0);
		$mechanicProfit = $mechanicItemSellAmount - $mechanicItemBuyCost;

		// COGS for Quick Bills: sum company_price * (qty - returns)
		$qbCostRow = $this->db->select('COALESCE(SUM(poi.company_price * (qbi.quantity - IFNULL(qbi.return_quantity,0))),0) AS total', false)
			->from('quick_bill_items qbi')
			->join('quick_bill qb', 'qb.quick_bill_id = qbi.quick_bill_id', 'left')
			->join('purchase_order_items poi', 'poi.po_item_id = qbi.po_item_id', 'left')
			->where('DATE(qb.created_at) >=', $start)
			->where('DATE(qb.created_at) <=', $end)
			->get()->row();
		$quickBillCOGS = (float) ($qbCostRow ? $qbCostRow->total : 0);

		// COGS for Service Job products: sum company_price * quantity
		$sjCostRow = $this->db->select('COALESCE(SUM(poi.company_price * sji.quantity),0) AS total', false)
			->from('services_job_items sji')
			->join('services_job sj', 'sj.job_id = sji.service_job_id', 'left')
			->join('purchase_order_items poi', 'poi.po_item_id = sji.po_item_id', 'left')
			->where('sji.item_type', 'product')
			->where('DATE(sj.service_date) >=', $start)
			->where('DATE(sj.service_date) <=', $end)
			->get()->row();
		$serviceJobCOGS = (float) ($sjCostRow ? $sjCostRow->total : 0);

		$totalCostForProfit = $dayExpensesTotal + $quickBillCOGS + $serviceJobCOGS + $mechanicItemBuyCost;
		$dayProfit = $dayIncomeTotal - $totalCostForProfit;

		// =====================
		// Monthly summary (for the month of selected date)
		// =====================
		$monthlyMethodTotals = [];
		$qbPayM = $this->db->select('LOWER(payment_method) as method, SUM(paid_amount) as total', false)
			->from('quick_bill_payments')
			->where('DATE(payment_date) >=', $monthStart)
			->where('DATE(payment_date) <=', $monthEnd)
			->group_by('LOWER(payment_method)')
			->get()->result();
		foreach ($qbPayM as $p) { $k = strtolower($p->method); $monthlyMethodTotals[$k] = ($monthlyMethodTotals[$k] ?? 0) + (float)$p->total; }
		$sjPayM = $this->db->select('LOWER(payment_method) as method, SUM(paid_amount) as total', false)
			->from('services_job_payments')
			->where('DATE(created_at) >=', $monthStart)
			->where('DATE(created_at) <=', $monthEnd)
			->where('deleted_by IS NULL', null, false)
			->where('cheque_return_by IS NULL', null, false)
			->where('quick_bill_id IS NULL', null, false)
			->where('service_job_id IS NOT NULL', null, false)
			->group_by('LOWER(payment_method)')
			->get()->result();
		foreach ($sjPayM as $p) { $k = strtolower($p->method); $monthlyMethodTotals[$k] = ($monthlyMethodTotals[$k] ?? 0) + (float)$p->total; }
		$monthCash = $monthlyMethodTotals['cash'] ?? 0;
		$monthCard = $monthlyMethodTotals['card'] ?? 0;
		$monthBank = ($monthlyMethodTotals['bank transfer'] ?? 0) + ($monthlyMethodTotals['bank_transfer'] ?? 0);
		$monthCheque = $monthlyMethodTotals['cheque'] ?? 0;
		$monthCredit = $monthlyMethodTotals['credit'] ?? 0;
		$monthIncomeTotal = $monthCash + $monthCard + $monthBank + $monthCredit;

		$expRowsM = $this->db->select('LOWER(payment_method) as method, SUM(amount) as total', false)
			->from('expenses')
			->where('expense_date >=', $monthStart)
			->where('expense_date <=', $monthEnd)
			->group_by('LOWER(payment_method)')
			->get()->result();
		$expTotalsM = [];
		foreach ($expRowsM as $er) { $expTotalsM[strtolower($er->method)] = (float)$er->total; }
		$monthExpCash = $expTotalsM['cash'] ?? 0;
		$monthExpCard = $expTotalsM['card'] ?? 0;
		$monthExpCheque = $expTotalsM['cheque'] ?? 0;
		$monthExpBank = ($expTotalsM['bank transfer'] ?? 0) + ($expTotalsM['bank_transfer'] ?? 0);
		$monthExpInHouse = $monthExpCash + $monthExpCard + $monthExpBank + $monthExpCheque;

		$sjCostRowM = $this->db->select('COALESCE(SUM(poi.company_price * sji.quantity),0) AS total', false)
			->from('services_job_items sji')
			->join('services_job sj', 'sj.job_id = sji.service_job_id', 'left')
			->join('purchase_order_items poi', 'poi.po_item_id = sji.po_item_id', 'left')
			->where('sji.item_type', 'product')
			->where('DATE(sj.service_date) >=', $monthStart)
			->where('DATE(sj.service_date) <=', $monthEnd)
			->get()->row();
		$monthExpUsedItem = (float)($sjCostRowM ? $sjCostRowM->total : 0);
		$monthExpTotal = $monthExpInHouse + $monthExpUsedItem;
		$monthProfit = $monthIncomeTotal - $monthExpTotal;

		$response = [
			'date' => $date,
			'day_start_cash_in_hand' => $startCashInHand,
			'day_quick_bill_income' => $dayQuickBillIncome,
			'day_services_income' => $dayServicesIncome,
			'day_services_job_income' => $dayServicesJobIncome,
			'day_mechanical_job_income' => $dayMechanicalJobIncome,
			'day_common_jobs_income' => $dayCommonJobsIncome,
			'day_income_total' => $dayIncomeTotal,
			'day_cash_income' => $dayCashIncome,
			'day_card_payment' => $dayCardPayment,
			'day_bank_transfer' => $dayBankTransfer,
			'day_cheque_payment' => $dayChequePayment,
			'day_credit_amount' => $dayCreditAmount,
			'day_credit_settle' => null,
			'all_cash_in_hand' => $allCashInHand,
			'day_expenses' => $dayExpensesTotal,
			'expense_cash' => $dayExpensesCash,
			'expense_card' => $dayExpensesCard,
			'expense_bank_transfer' => $dayExpensesBankTr,
			'expense_cheque' => $dayExpensesCheque,
			'cheque_payment' => null,
			'bank_deposit' => $bankDeposit,
			'day_end_cash_in_hand' => $dayEndCashInHand,
			'day_end_bank_balance' => $dayEndBankBalance,
			'day_end_card_balance' => $dayEndCardBalance,
			'mechanic_item_buy_cost' => $mechanicItemBuyCost,
			'mechanic_item_sell_amount' => $mechanicItemSellAmount,
			'mechanic_profit' => $mechanicProfit,
			'day_profit' => $dayProfit,
			// Monthly summary in daily report
			'monthIncomeTotal' => $monthIncomeTotal,
			'monthCash' => $monthCash,
			'monthCard' => $monthCard,
			'monthBank' => $monthBank,
			'monthCredit' => $monthCredit,
			'monthExpTotal' => $monthExpTotal,
			'monthExpInHouse' => $monthExpInHouse,
			'monthExpUsedItem' => $monthExpUsedItem,
			'monthProfit' => $monthProfit,
			'income_minus_expenses_used_item' => null
		];

		echo json_encode(['status' => 'success', 'data' => $response]);
	}

	public function dailyPrint()
	{
		$date = $this->input->get('date') ?: date('Y-m-d');
		// Reuse dailyData logic by calling it internally to get data
		$_POST['date'] = $date;
		ob_start();
		$this->dailyData();
		$json = ob_get_clean();
		$payload = json_decode($json, true);
		if (!$payload || $payload['status'] !== 'success') {
			show_error('Failed to generate report');
			return;
		}
		$data = $payload['data'];
		$data['report_date'] = $date;

		// Render to a print-friendly HTML view that auto-opens the print dialog
		$this->load->view('reports/daily-print', $data);
	}

	public function dailyPdf()
	{
		$date = $this->input->get('date') ?: date('Y-m-d');
		// Reuse dailyData logic by calling it internally to get data
		$_POST['date'] = $date;
		ob_start();
		$this->dailyData();
		$json = ob_get_clean();
		$payload = json_decode($json, true);
		if (!$payload || $payload['status'] !== 'success') {
			show_error('Failed to generate report');
			return;
		}
		$data = $payload['data'];
		$data['report_date'] = $date;

		// Load PDF library
		$this->load->library('pdf');
		
		// Generate PDF
		$html = $this->load->view('reports/daily-pdf-template', $data, true);
		
		$this->pdf->loadHtml($html);
		$this->pdf->setPaper('A4', 'portrait');
		$this->pdf->render();
		
		$filename = 'Daily_Report_' . $date . '.pdf';
		$this->pdf->stream($filename);
	}

	public function dailyDetailed()
	{
		$date = $this->input->get('date') ?: date('Y-m-d');
		$data['mainMenuName'] = 'report';
		$data['subMenuName'] = 'daily-detailed-report';
		$data['report_date'] = $date;

		// Get detailed Quick Bill transactions
		$data['quick_bills'] = $this->db->select('
			qb.quick_bill_id,
			qb.description,
			qb.final_bill_amount,
			qb.created_at,
			c.name as customer_name,
			c.mobile as customer_mobile
		')
		->from('quick_bill qb')
		->join('customers c', 'c.customers_id = qb.customer_id', 'left')
		->where('DATE(qb.created_at)', $date)
		->order_by('qb.created_at', 'ASC')
		->get()->result();

		// Get detailed Services transactions (Normal)
		$data['normal_services'] = $this->db->select('
			sj.job_id,
			sj.description,
			sj.final_bill_amount,
			sj.service_date,
			sj.created_at,
			c.name as customer_name,
			c.mobile as customer_mobile,
			v.vehicle_no
		')
		->from('services_job sj')
		->join('customers c', 'c.customers_id = sj.customer_id', 'left')
		->join('vehicles v', 'v.vehicle_id = sj.vehicle_id', 'left')
		->where('DATE(sj.service_date)', $date)
		->where('sj.service_type', 'normal')
		->where('sj.status', 4) // Completed
		->order_by('sj.service_date', 'ASC')
		->get()->result();

		// Get detailed Services transactions (Mechanical)
		$data['mechanical_services'] = $this->db->select('
			sj.job_id,
			sj.description,
			sj.final_bill_amount,
			sj.service_date,
			sj.created_at,
			c.name as customer_name,
			c.mobile as customer_mobile,
			v.vehicle_no
		')
		->from('services_job sj')
		->join('customers c', 'c.customers_id = sj.customer_id', 'left')
		->join('vehicles v', 'v.vehicle_id = sj.vehicle_id', 'left')
		->where('DATE(sj.service_date)', $date)
		->where('sj.service_type', 'mechanical')
		->where('sj.status', 4) // Completed
		->order_by('sj.service_date', 'ASC')
		->get()->result();

		// Get detailed payment breakdown
		$data['payment_details'] = $this->getDetailedPayments($date);

		// Get detailed expenses
		$data['expense_details'] = $this->getDetailedExpenses($date);

		// Get detailed mechanic outsource transactions
		$data['mechanic_outsource'] = $this->getDetailedMechanicOutsource($date);

		// Get detailed balance transactions
		$data['balance_transactions'] = $this->getDetailedBalanceTransactions($date);

		// Get profit calculations
		$data['profit_details'] = $this->getDetailedProfitCalculations($date);

		$this->load->view('layout/header');
		$this->load->view('layout/top_navbar');
		$this->load->view('layout/left_sidebar', $data);
		$this->load->view('reports/daily-detailed', $data);
		$this->load->view('layout/footer');
	}

	private function getDetailedPayments($date)
	{
		// Quick Bill Payments
		$qb_payments = $this->db->select('
			qbp.paid_amount,
			qbp.payment_method,
			qbp.payment_date,
			qb.quick_bill_id,
			c.name as customer_name
		')
		->from('quick_bill_payments qbp')
		->join('quick_bill qb', 'qb.quick_bill_id = qbp.quick_bill_id', 'left')
		->join('customers c', 'c.customers_id = qb.customer_id', 'left')
		->where('DATE(qbp.payment_date)', $date)
		->order_by('qbp.payment_date', 'ASC')
		->get()->result();

		// Services Payments
		$service_payments = $this->db->select('
			sjp.paid_amount,
			sjp.payment_method,
			sjp.payment_date,
			sj.job_id,
			c.name as customer_name
		')
		->from('services_job_payments sjp')
		->join('services_job sj', 'sj.job_id = sjp.service_job_id', 'left')
		->join('customers c', 'c.customers_id = sj.customer_id', 'left')
		->where('DATE(sjp.payment_date)', $date)
		->order_by('sjp.payment_date', 'ASC')
		->get()->result();

		return [
			'quick_bill_payments' => $qb_payments,
			'service_payments' => $service_payments
		];
	}

	private function getDetailedExpenses($date)
	{
		return $this->db->select('
			e.id as expense_id,
			e.description,
			e.amount,
			e.payment_method,
			e.expense_date,
			ec.expensesCategoryName as category_name
		')
		->from('expenses e')
		->join('expenses_categories ec', 'ec.expensesCategoryId = e.category_id', 'left')
		->where('DATE(e.expense_date)', $date)
		->order_by('e.expense_date', 'ASC')
		->get()->result();
	}

	private function getDetailedMechanicOutsource($date)
	{
		return $this->db->select('
			op.id,
			op.item_name,
			op.purchased_from,
			op.purchased_price,
			op.invoice_price,
			op.created_at,
			sj.job_id,
			c.name as customer_name,
			v.vehicle_no
		')
		->from('outsource_parts op')
		->join('services_job sj', 'sj.job_id = op.service_job_id', 'left')
		->join('customers c', 'c.customers_id = sj.customer_id', 'left')
		->join('vehicles v', 'v.vehicle_id = sj.vehicle_id', 'left')
		->where('DATE(sj.service_date)', $date)
		->order_by('op.created_at', 'ASC')
		->get()->result();
	}

	private function getDetailedBalanceTransactions($date)
	{
		// Get account transactions for the day
		$transactions = $this->db->select('
			at.txn_id,
			at.account_slug,
			at.description,
			at.amount,
			at.txn_type,
			at.payment_method,
			at.created_at,
			at.running_balance
		')
		->from('account_transactions at')
		->where('DATE(at.created_at)', $date)
		->order_by('at.created_at', 'ASC')
		->get()->result();

		// Group by account type
		$grouped = [
			'in_hand' => [],
			'bank' => [],
			'card_machine' => []
		];

		foreach ($transactions as $txn) {
			if (isset($grouped[$txn->account_slug])) {
				$grouped[$txn->account_slug][] = $txn;
			}
		}

		return $grouped;
	}

	private function getDetailedProfitCalculations($date)
	{
		// Get COGS for Quick Bills
		$qb_cogs = [];
		$qb_query = $this->db->select('
			qb.quick_bill_id,
			qb.final_bill_amount,
			COALESCE(SUM(poi.company_price * qbi.quantity), 0) as cogs,
			c.name as customer_name
		')
		->from('quick_bill qb')
		->join('customers c', 'c.customers_id = qb.customer_id', 'left')
		->join('quick_bill_items qbi', 'qbi.quick_bill_id = qb.quick_bill_id', 'left')
		->join('purchase_order_items poi', 'poi.po_item_id = qbi.po_item_id', 'left')
		->where('DATE(qb.created_at)', $date)
		->group_by('qb.quick_bill_id')
		->order_by('qb.created_at', 'ASC');
		
		$qb_result = $qb_query->get();
		if ($qb_result) {
			$qb_cogs = $qb_result->result();
		}

		// Get COGS for Services
		$service_cogs = [];
		$service_query = $this->db->select('
			sj.job_id,
			sj.final_bill_amount,
			COALESCE(SUM(poi.company_price * sji.quantity), 0) as cogs,
			c.name as customer_name,
			v.vehicle_no
		')
		->from('services_job sj')
		->join('customers c', 'c.customers_id = sj.customer_id', 'left')
		->join('vehicles v', 'v.vehicle_id = sj.vehicle_id', 'left')
		->join('services_job_items sji', 'sji.service_job_id = sj.job_id', 'left')
		->join('purchase_order_items poi', 'poi.po_item_id = sji.po_item_id', 'left')
		->where('DATE(sj.service_date)', $date)
		->where('sj.status', 4) // Completed
		->group_by('sj.job_id')
		->order_by('sj.service_date', 'ASC');
		
		$service_result = $service_query->get();
		if ($service_result) {
			$service_cogs = $service_result->result();
		}

		return [
			'quick_bill_profits' => $qb_cogs,
			'service_profits' => $service_cogs
		];
	}

	public function monthly()
	{
		$this->require_permission('reports.view');
		$data['mainMenuName'] = 'report';
		$data['subMenuName'] = 'monthly-report';
		$this->load->view('layout/header');
		$this->load->view('layout/top_navbar');
		$this->load->view('layout/left_sidebar', $data);
		$this->load->view('reports/monthly');
		$this->load->view('layout/footer');
	}

	public function monthlyDetailed()
	{
		$year = (int)($this->input->get('year') ?: date('Y'));
		$month = (int)($this->input->get('month') ?: date('n'));
		$start = sprintf('%04d-%02d-01', $year, $month);
		$end = date('Y-m-t', strtotime($start));

		$data['mainMenuName'] = 'report';
		$data['subMenuName'] = 'monthly-detailed-report';
		$data['report_year'] = $year;
		$data['report_month'] = $month;

		// Detailed Quick Bills in month
		$data['quick_bills'] = $this->db->select('qb.quick_bill_id, qb.description, qb.final_bill_amount, qb.created_at, c.name as customer_name, c.mobile as customer_mobile')
			->from('quick_bill qb')
			->join('customers c','c.customers_id = qb.customer_id','left')
			->where('DATE(qb.created_at) >=', $start)
			->where('DATE(qb.created_at) <=', $end)
			->order_by('qb.created_at','ASC')->get()->result();

		// Detailed Services - Normal
		$data['normal_services'] = $this->db->select('sj.job_id, sj.description, sj.final_bill_amount, sj.service_date, sj.created_at, c.name as customer_name, c.mobile as customer_mobile, v.vehicle_no')
			->from('services_job sj')
			->join('customers c','c.customers_id = sj.customer_id','left')
			->join('vehicles v','v.vehicle_id = sj.vehicle_id','left')
			->where('DATE(sj.service_date) >=', $start)
			->where('DATE(sj.service_date) <=', $end)
			->where('sj.service_type','normal')
			->where('sj.status', 4)
			->order_by('sj.service_date','ASC')->get()->result();

		// Detailed Services - Mechanical
		$data['mechanical_services'] = $this->db->select('sj.job_id, sj.description, sj.final_bill_amount, sj.service_date, sj.created_at, c.name as customer_name, c.mobile as customer_mobile, v.vehicle_no')
			->from('services_job sj')
			->join('customers c','c.customers_id = sj.customer_id','left')
			->join('vehicles v','v.vehicle_id = sj.vehicle_id','left')
			->where('DATE(sj.service_date) >=', $start)
			->where('DATE(sj.service_date) <=', $end)
			->where('sj.service_type','mechanical')
			->where('sj.status', 4)
			->order_by('sj.service_date','ASC')->get()->result();

		// Payment breakdown (reuse daily helpers but iterate month)
		$data['payment_details'] = [
			'quick_bill_payments' => $this->db->select('qbp.paid_amount, qbp.payment_method, qbp.payment_date, qb.quick_bill_id, c.name as customer_name')
				->from('quick_bill_payments qbp')
				->join('quick_bill qb','qb.quick_bill_id = qbp.quick_bill_id','left')
				->join('customers c','c.customers_id = qb.customer_id','left')
				->where('DATE(qbp.payment_date) >=', $start)
				->where('DATE(qbp.payment_date) <=', $end)
				->order_by('qbp.payment_date','ASC')->get()->result(),
			'service_payments' => $this->db->select('sjp.paid_amount, sjp.payment_method, sjp.payment_date, sj.job_id, c.name as customer_name')
				->from('services_job_payments sjp')
				->join('services_job sj','sj.job_id = sjp.service_job_id','left')
				->join('customers c','c.customers_id = sj.customer_id','left')
				->where('DATE(sjp.payment_date) >=', $start)
				->where('DATE(sjp.payment_date) <=', $end)
				->order_by('sjp.payment_date','ASC')->get()->result()
		];

		// Expenses in month
		$data['expense_details'] = $this->db->select('e.id as expense_id, e.description, e.amount, e.payment_method, e.expense_date, ec.expensesCategoryName as category_name')
			->from('expenses e')
			->join('expenses_categories ec','ec.expensesCategoryId = e.category_id','left')
			->where('DATE(e.expense_date) >=', $start)
			->where('DATE(e.expense_date) <=', $end)
			->order_by('e.expense_date','ASC')->get()->result();

		// Balance transactions grouped by account in month
		$transactions = $this->db->select('at.txn_id, at.account_slug, at.description, at.amount, at.txn_type, at.payment_method, at.created_at, at.running_balance')
			->from('account_transactions at')
			->where('DATE(at.created_at) >=', $start)
			->where('DATE(at.created_at) <=', $end)
			->order_by('at.created_at','ASC')->get()->result();
		$grouped = ['in_hand'=>[],'bank'=>[],'card_machine'=>[]]; foreach($transactions as $t){ if(isset($grouped[$t->account_slug])) $grouped[$t->account_slug][]=$t; }
		$data['balance_transactions'] = $grouped;

		// Profit style COGS for month
		$data['profit_details'] = [
			'quick_bill_profits' => $this->db->select('qb.quick_bill_id, qb.final_bill_amount, COALESCE(SUM(poi.company_price * qbi.quantity),0) as cogs, c.name as customer_name')
				->from('quick_bill qb')
				->join('customers c','c.customers_id = qb.customer_id','left')
				->join('quick_bill_items qbi','qbi.quick_bill_id = qb.quick_bill_id','left')
				->join('purchase_order_items poi','poi.po_item_id = qbi.po_item_id','left')
				->where('DATE(qb.created_at) >=', $start)
				->where('DATE(qb.created_at) <=', $end)
				->group_by('qb.quick_bill_id')
				->order_by('qb.created_at','ASC')->get()->result(),
			'service_profits' => $this->db->select('sj.job_id, sj.final_bill_amount, COALESCE(SUM(poi.company_price * sji.quantity),0) as cogs, c.name as customer_name, v.vehicle_no')
				->from('services_job sj')
				->join('customers c','c.customers_id = sj.customer_id','left')
				->join('vehicles v','v.vehicle_id = sj.vehicle_id','left')
				->join('services_job_items sji','sji.service_job_id = sj.job_id','left')
				->join('purchase_order_items poi','poi.po_item_id = sji.po_item_id','left')
				->where('DATE(sj.service_date) >=', $start)
				->where('DATE(sj.service_date) <=', $end)
				->where('sj.status', 4)
				->group_by('sj.job_id')
				->order_by('sj.service_date','ASC')->get()->result()
		];

		$this->load->view('layout/header');
		$this->load->view('layout/top_navbar');
		$this->load->view('layout/left_sidebar', $data);
		$this->load->view('reports/monthly-detailed', $data);
		$this->load->view('layout/footer');
	}

	public function monthlyData()
	{
		$year = (int)($this->input->post('year') ?: date('Y'));
		$month = (int)($this->input->post('month') ?: date('n'));
		$start = sprintf('%04d-%02d-01', $year, $month);
		$end = date('Y-m-t', strtotime($start));

		$oldDebug = $this->db->db_debug;
		$this->db->db_debug = FALSE;
		$errors = [];
		$run = function($builder) use (&$errors){ $q=$builder->get(); $err=$this->db->error(); if(!empty($err['code'])){ $errors[]=$err['message']; return null; } return $q; };

		// Income by method (quick bills + service jobs)
		$q = $run($this->db->select_sum('final_bill_amount','total')->from('quick_bill')->where('DATE(created_at) >=',$start)->where('DATE(created_at) <=',$end));
		$monthQuickBillIncome = $q ? (float)($q->row()->total ?? 0) : 0;
		$q = $run($this->db->select('LOWER(payment_method) as method, SUM(paid_amount) as total',false)->from('quick_bill_payments')->where('DATE(payment_date) >=',$start)->where('DATE(payment_date) <=',$end)->group_by('LOWER(payment_method)'));
		$qbPay = $q ? $q->result() : [];
		$q = $run($this->db->select('LOWER(payment_method) as method, SUM(paid_amount) as total',false)->from('services_job_payments')->where('DATE(created_at) >=',$start)->where('DATE(created_at) <=',$end)->where('deleted_by IS NULL', null, false)->where('cheque_return_by IS NULL', null, false)->where('quick_bill_id IS NULL', null, false)->where('service_job_id IS NOT NULL', null, false)->group_by('LOWER(payment_method)'));
		$sjPay = $q ? $q->result() : [];
		$mt=[]; foreach($qbPay as $p){ $k=strtolower($p->method); $mt[$k]=($mt[$k]??0)+(float)$p->total; } foreach($sjPay as $p){ $k=strtolower($p->method); $mt[$k]=($mt[$k]??0)+(float)$p->total; }
		$monthCash=$mt['cash']??0; $monthCard=$mt['card']??0; $monthBank=($mt['bank transfer']??0)+($mt['bank_transfer']??0); $monthCheque=$mt['cheque']??0; $monthCredit=$mt['credit']??0;

		// Expenses by payment method for reference and In-house (sum of expenses table)
		$q = $run($this->db->select('LOWER(payment_method) as method, SUM(amount) as total',false)
			->from('expenses')
			->where('expense_date >=',$start)
			->where('expense_date <=',$end)
			->group_by('LOWER(payment_method)'));
		$et=[]; if($q){ foreach($q->result() as $e){ $et[strtolower($e->method)]=(float)$e->total; } }
		$monthExpCash=$et['cash']??0; $monthExpCard=$et['card']??0; $monthExpBank=($et['bank transfer']??0)+($et['bank_transfer']??0); $monthExpCheque=$et['cheque']??0;
		$monthExpInHouse = $monthExpCash + $monthExpCard + $monthExpBank + $monthExpCheque;

		// Total month income (sum of payment methods shown in UI)
		$monthIncomeTotal = $monthCash + $monthCard + $monthBank + $monthCredit;

		// Used item expenses: service_job_items products company price * qty (fallback when po_item_id null)
		$q = $run($this->db->select("COALESCE(SUM( (CASE WHEN sji.po_item_id IS NOT NULL THEN poi.company_price ELSE (SELECT COALESCE(MAX(company_price),0) FROM purchase_order_items WHERE product_id = sji.item_id) END) * sji.quantity ),0) AS total", false)
			->from('services_job_items sji')
			->join('services_job sj','sj.job_id=sji.service_job_id','left')
			->join('purchase_order_items poi','poi.po_item_id=sji.po_item_id','left')
			->where('sji.item_type','product')
			->where('DATE(sj.service_date) >=',$start)
			->where('DATE(sj.service_date) <=',$end));
		$monthExpUsedItem = $q ? (float)$q->row()->total : 0;

		// Total Expenses and Profit per requirement
		$monthExpTotal = $monthExpInHouse + $monthExpUsedItem;
		$monthProfit = $monthIncomeTotal - $monthExpTotal;

		// Credit settle placeholder and "All Customer Credit Amount" as month credit payments
		$monthCreditSettle = 0;
		$allCustomerCreditAmount = $monthCredit; // Sum of credit payments in selected month

		// Bank deposits: total amount transferred from In Hand to Bank during month
		$q = $run(
			$this->db
				->select('COALESCE(SUM(t.amount),0) AS total', false)
				->from('account_transactions t')
				->join('account_transactions d', 'd.txn_id = t.reference_id', 'left')
				->where('t.account_slug', 'bank')
				->where('t.txn_type', 'credit')
				->where('t.reference_type', 'transfer')
				->where('d.account_slug', 'in_hand')
				->where('d.txn_type', 'debit')
				->where('DATE(t.created_at) >=', $start)
				->where('DATE(t.created_at) <=', $end)
		);
		$bankDeposit = $q ? (float)$q->row()->total : 0;
		$q = $run($this->db->select('COALESCE(SUM(amount),0) AS total',false)->from('account_transactions')->where('account_slug','bank')->where('txn_type','credit')->where('DATE(created_at) >=',$start)->where('DATE(created_at) <=',$end));
		$monthBankTotal = $q ? (float)$q->row()->total : 0;
		$monthBankChequePayment = $monthCheque;
		// Returns to In Hand: total amount transferred from Bank back to In Hand during month
		$q = $run(
			$this->db
				->select('COALESCE(SUM(t.amount),0) AS total', false)
				->from('account_transactions t')
				->join('account_transactions d', 'd.txn_id = t.reference_id', 'left')
				->where('t.account_slug', 'in_hand')
				->where('t.txn_type', 'credit')
				->where('t.reference_type', 'transfer')
				->group_start()
					->where('d.account_slug', 'bank')
					->or_where('d.account_slug', 'card_machine')
				->group_end()
				->where('d.txn_type', 'debit')
				->where('DATE(t.created_at) >=', $start)
				->where('DATE(t.created_at) <=', $end)
		);
		$monthReturnToInHand = $q ? (float)$q->row()->total : 0;

		// Suppliers
		// Month Supplier Invoice amount: sum of PO totals by bill_date in selected month
		$q = $run($this->db->select('COALESCE(SUM(total_amount),0) AS total',false)
			->from('purchase_orders')
			->where('DATE(bill_date) >=',$start)
			->where('DATE(bill_date) <=',$end)
		);
		$monthSupplierInvoiceAmount = $q ? (float)$q->row()->total : 0;

		// Month Supplier Payment amount: payments recorded in the selected month (single source to avoid double-counting)
		$q = $run($this->db->select('COALESCE(SUM(paid_amount),0) AS total',false)
			->from('purchase_order_payments')
			->where('DATE(payment_date) >=',$start)
			->where('DATE(payment_date) <=',$end)
		);
		$monthSupplierPaymentAmount = $q ? (float)$q->row()->total : 0;

		// All-time totals
		$q = $run($this->db->select('COALESCE(SUM(total_amount),0) AS total',false)->from('purchase_orders'));
		$totallySupplierInvoiceAmount = $q ? (float)$q->row()->total : 0;
		$q = $run($this->db->select('COALESCE(SUM(paid_amount),0) AS total',false)->from('purchase_order_payments'));
		$allSupplierInvoicePayment = $q ? (float)$q->row()->total : 0;
		$monthSupplierBalance = $monthSupplierInvoiceAmount - $monthSupplierPaymentAmount;
		$allSupplierPaymentBalance = $totallySupplierInvoiceAmount - $allSupplierInvoicePayment;

		// Mechanic outsource parts: buy and sell within month
		$q = $run($this->db->select('COALESCE(SUM(op.purchased_price),0) AS total', false)
			->from('outsource_parts op')
			->join('services_job sj', 'sj.job_id = op.service_job_id', 'left')
			->where('DATE(sj.service_date) >=', $start)
			->where('DATE(sj.service_date) <=', $end));
		$monthMechanicBuy = $q ? (float)$q->row()->total : 0;
		$q = $run($this->db->select('COALESCE(SUM(op.invoice_price),0) AS total', false)
			->from('outsource_parts op')
			->join('services_job sj', 'sj.job_id = op.service_job_id', 'left')
			->where('DATE(sj.service_date) >=', $start)
			->where('DATE(sj.service_date) <=', $end));
		$monthMechanicSell = $q ? (float)$q->row()->total : 0;
		$monthMechanicProfit = $monthMechanicSell - $monthMechanicBuy;

		// Mechanic labour charges: services_job_items item_type='labour' within month
		$q = $run($this->db->select('COALESCE(SUM( (sji.price - IFNULL(sji.discount_amount,0) - (sji.price * IFNULL(sji.discount_percent,0)/100)) * IFNULL(sji.quantity,1) ),0) AS total', false)
			->from('services_job_items sji')
			->join('services_job sj', 'sj.job_id = sji.service_job_id', 'left')
			->where('sji.item_type', 'labour')
			->where('DATE(sj.service_date) >=', $start)
			->where('DATE(sj.service_date) <=', $end));
		$monthMechanicLabour = $q ? (float)$q->row()->total : 0;

		// Loans
		$q = $run($this->db->select('COALESCE(SUM(amount),0) AS total',false)->from('account_transactions')->where('account_slug','loan')->where('txn_type','credit')->where('DATE(created_at) >=',$start)->where('DATE(created_at) <=',$end));
		$companyLoanIn = $q ? (float)$q->row()->total : 0;
		$q = $run($this->db->select('COALESCE(SUM(amount),0) AS total',false)->from('account_transactions')->where('account_slug','loan')->where('txn_type','debit')->where('DATE(created_at) >=',$start)->where('DATE(created_at) <=',$end));
		$companyLoanOut = $q ? (float)$q->row()->total : 0;

		$this->db->db_debug = $oldDebug;
		if(!empty($errors)){ log_message('error','Monthly report errors: '.print_r($errors,true)); }

		$response = compact(
			'year','month','monthQuickBillIncome','monthCash','monthCard','monthBank','monthCheque','monthCredit','monthIncomeTotal',
			'monthExpTotal','monthExpInHouse','monthExpUsedItem',
			'monthProfit','monthCreditSettle','allCustomerCreditAmount','bankDeposit','monthBankTotal','monthBankChequePayment','monthReturnToInHand',
			'monthSupplierInvoiceAmount','monthSupplierPaymentAmount','monthSupplierBalance','totallySupplierInvoiceAmount','allSupplierInvoicePayment','allSupplierPaymentBalance',
			'companyLoanIn','companyLoanOut','monthMechanicBuy','monthMechanicSell','monthMechanicProfit','monthMechanicLabour'
		);
		echo json_encode(['status'=>'success','data'=>$response]);
	}

	public function monthlyPrint()
	{
		$year = (int)($this->input->get('year') ?: date('Y'));
		$month = (int)($this->input->get('month') ?: date('n'));
		$_POST['year']=$year; $_POST['month']=$month;
		ob_start(); $this->monthlyData(); $json = ob_get_clean();
		$payload = json_decode($json,true); if(!$payload || $payload['status']!=='success'){ show_error('Failed to load monthly data'); return; }
		$data = $payload['data'];
		$this->load->view('reports/monthly-print', $data);
	}

	public function monthlyPdf()
	{
		$year = (int)($this->input->get('year') ?: date('Y'));
		$month = (int)($this->input->get('month') ?: date('n'));
		// Reuse monthlyData logic by calling it internally to get data
		$_POST['year'] = $year;
		$_POST['month'] = $month;
		ob_start();
		$this->monthlyData();
		$json = ob_get_clean();
		$payload = json_decode($json, true);
		if (!$payload || $payload['status'] !== 'success') {
			show_error('Failed to generate report');
			return;
		}
		$data = $payload['data'];
		$data['report_year'] = $year;
		$data['report_month'] = $month;

		// Load PDF library
		$this->load->library('pdf');
		
		// Generate PDF
		$html = $this->load->view('reports/monthly-pdf-template', $data, true);
		
		$this->pdf->loadHtml($html);
		$this->pdf->setPaper('A4', 'portrait');
		$this->pdf->render();
		
		$monthName = date('F', mktime(0, 0, 0, $month, 1));
		$filename = 'Monthly_Report_' . $monthName . '_' . $year . '.pdf';
		$this->pdf->stream($filename);
	}

	public function stock()
	{
		$this->require_permission('reports.inventory');
		$data['mainMenuName'] = 'report';
		$data['subMenuName'] = 'stock-report';
		$this->load->view('layout/header');
		$this->load->view('layout/top_navbar');
		$this->load->view('layout/left_sidebar', $data);
		$this->load->view('reports/stock');
		$this->load->view('layout/footer');
	}

	public function stockData()
	{
		$category = $this->input->post('category');
		$brand = $this->input->post('brand');
		$supplier = $this->input->post('supplier');
		$stock_status = $this->input->post('stock_status'); // all, low, out, normal
		$search = $this->input->post('search');

		// Get current stock levels
		$stock_data = $this->getCurrentStock($category, $brand, $supplier, $stock_status, $search);
		
		// Get stock movement data
		$movement_data = $this->getStockMovements();
		
		// Get supplier analysis
		$supplier_data = $this->getSupplierStockAnalysis();
		
		// Get category analysis
		$category_data = $this->getCategoryStockAnalysis();
		
		// Get low stock alerts
		$low_stock_alerts = $this->getLowStockAlerts();
		
		// Get stock summary
		$stock_summary = $this->getStockSummary();

		$response = [
			'stock_data' => $stock_data,
			'movement_data' => $movement_data,
			'supplier_data' => $supplier_data,
			'category_data' => $category_data,
			'low_stock_alerts' => $low_stock_alerts,
			'stock_summary' => $stock_summary
		];

		echo json_encode(['status' => 'success', 'data' => $response]);
	}

	private function getCurrentStock($category = null, $brand = null, $supplier = null, $stock_status = null, $search = null)
	{
		$this->db->select('
			p.product_id,
			p.product_name,
			p.sku,
			p.barcode,
			p.sale_price as product_sale_price,
			MAX(poi.sale_price) as sale_price,
			p.cost_price,
			p.stock_quantity,
			p.reorder_level,
			ib.itemBrandName as brand_name,
			ic.itemCategoryName as category_name,
			GROUP_CONCAT(DISTINCT s.name SEPARATOR \', \') as supplier_name,
			SUM(poi.available_stock) as available_stock,
			MAX(poi.rack_no) as rack_no,
			MAX(poi.bin_no) as bin_no,
			MAX(poi.company_price) as company_price,
			MAX(poi.purchase_date) as purchase_date,
			SUM(poi.available_stock * poi.company_price) as stock_value_cost,
			SUM(poi.available_stock * poi.sale_price) as stock_value_retail
		');
		$this->db->from('products p');
		$this->db->join('item_brands ib', 'ib.itemBrandId = p.item_brand_id', 'left');
		$this->db->join('item_categories ic', 'ic.itemCategoryId = p.item_category_id', 'left');
		$this->db->join('purchase_order_items poi', 'poi.product_id = p.product_id', 'left');
		$this->db->join('suppliers s', 's.supplier_id = poi.supplier_id', 'left');
		$this->db->where('p.is_deleted', 0);
		$this->db->where('p.is_active', 1);

		if ($category) {
			$this->db->where('ic.itemCategoryId', $category);
		}
		if ($brand) {
			$this->db->where('ib.itemBrandId', $brand);
		}
		if ($supplier) {
			$this->db->where('poi.supplier_id', $supplier);
		}
		if ($search) {
			$this->db->group_start();
			$this->db->like('p.product_name', $search);
			$this->db->or_like('p.sku', $search);
			$this->db->or_like('p.barcode', $search);
			$this->db->group_end();
		}

		$this->db->group_by('p.product_id, p.product_name, p.sku, p.barcode, p.sale_price, p.cost_price, p.stock_quantity, p.reorder_level, ib.itemBrandName, ic.itemCategoryName');

		// Stock status filter applied via HAVING on the aggregated available_stock
		if ($stock_status && $stock_status !== 'all') {
			if ($stock_status === 'low') {
				$this->db->having('SUM(poi.available_stock) > 0 AND SUM(poi.available_stock) <= p.reorder_level', null, false);
			} elseif ($stock_status === 'out') {
				$this->db->having('SUM(poi.available_stock) <=', 0);
			} else {
				$this->db->having('SUM(poi.available_stock) >', 0);
			}
		} else {
			$this->db->having('SUM(poi.available_stock) >', 0);
		}

		$this->db->order_by('SUM(poi.available_stock)', 'ASC');
		$query = $this->db->get();
		$results = $query->result();

		// Add stock_status calculation in PHP
		foreach ($results as $item) {
			if ($item->available_stock <= 0) {
				$item->stock_status = 'out_of_stock';
			} elseif ($item->available_stock <= $item->reorder_level) {
				$item->stock_status = 'low_stock';
			} else {
				$item->stock_status = 'normal';
			}
		}

		return $results;
	}

	private function getStockMovements()
	{
		// Get recent stock movements from services and quick bills
		$movements = [];
		
		// Stock out from services
		$service_movements = $this->db->select('
			sji.created_at as movement_date,
			"OUT" as movement_type,
			sji.quantity,
			p.product_name,
			poi.company_price,
			"Service Job" as source,
			sj.job_id as reference_id
		')
		->from('services_job_items sji')
		->join('products p', 'p.product_id = sji.item_id')
		->join('purchase_order_items poi', 'poi.po_item_id = sji.po_item_id')
		->join('services_job sj', 'sj.job_id = sji.service_job_id')
		->where('sji.item_type', 'product')
		->where('sji.confirmed_by >', 0)
		->where('sji.created_at >=', date('Y-m-d', strtotime('-30 days')))
		->order_by('sji.created_at', 'DESC')
		->limit(50)
		->get()->result();

		// Stock out from quick bills
		$qb_movements = $this->db->select('
			qbi.created_at as movement_date,
			"OUT" as movement_type,
			qbi.quantity,
			p.product_name,
			poi.company_price,
			"Quick Bill" as source,
			qb.quick_bill_id as reference_id
		')
		->from('quick_bill_items qbi')
		->join('products p', 'p.product_id = qbi.item_id')
		->join('purchase_order_items poi', 'poi.po_item_id = qbi.po_item_id')
		->join('quick_bill qb', 'qb.quick_bill_id = qbi.quick_bill_id')
		->where('qbi.created_at >=', date('Y-m-d', strtotime('-30 days')))
		->order_by('qbi.created_at', 'DESC')
		->limit(50)
		->get()->result();

		// Stock in from purchases
		$purchase_movements = $this->db->select('
			poi.created_at as movement_date,
			"IN" as movement_type,
			poi.quantity,
			p.product_name,
			poi.company_price,
			"Purchase Order" as source,
			poi.po_id as reference_id
		')
		->from('purchase_order_items poi')
		->join('products p', 'p.product_id = poi.product_id')
		->where('poi.created_at >=', date('Y-m-d', strtotime('-30 days')))
		->order_by('poi.created_at', 'DESC')
		->limit(50)
		->get()->result();

		$movements = array_merge($service_movements, $qb_movements, $purchase_movements);
		
		// Sort by date
		usort($movements, function($a, $b) {
			return strtotime($b->movement_date) - strtotime($a->movement_date);
		});

		return array_slice($movements, 0, 100); // Return last 100 movements
	}

	private function getSupplierStockAnalysis()
	{
		return $this->db->select('
			s.supplier_id,
			s.name as supplier_name,
			COUNT(DISTINCT poi.product_id) as total_products,
			SUM(poi.available_stock) as total_stock_quantity,
			SUM(poi.available_stock * poi.company_price) as total_stock_value,
			AVG(poi.company_price) as avg_cost_price
		')
		->from('suppliers s')
		->join('purchase_order_items poi', 'poi.supplier_id = s.supplier_id')
		->where('poi.available_stock >', 0)
		->group_by('s.supplier_id, s.name')
		->order_by('total_stock_value', 'DESC')
		->get()->result();
	}

	private function getCategoryStockAnalysis()
	{
		return $this->db->select('
			ic.itemCategoryId,
			ic.itemCategoryName as category_name,
			COUNT(DISTINCT p.product_id) as total_products,
			SUM(poi.available_stock) as total_stock_quantity,
			SUM(poi.available_stock * poi.company_price) as total_stock_value_cost,
			SUM(poi.available_stock * poi.sale_price) as total_stock_value_retail
		')
		->from('item_categories ic')
		->join('products p', 'p.item_category_id = ic.itemCategoryId')
		->join('purchase_order_items poi', 'poi.product_id = p.product_id')
		->where('p.is_deleted', 0)
		->where('p.is_active', 1)
		->where('poi.available_stock >', 0)
		->group_by('ic.itemCategoryId, ic.itemCategoryName')
		->order_by('total_stock_value_retail', 'DESC')
		->get()->result();
	}

	private function getLowStockAlerts()
	{
		return $this->db->select('
			p.product_name,
			p.sku,
			poi.available_stock,
			p.reorder_level,
			ib.itemBrandName as brand_name,
			ic.itemCategoryName as category_name,
			s.name as supplier_name
		')
		->from('products p')
		->join('purchase_order_items poi', 'poi.product_id = p.product_id')
		->join('item_brands ib', 'ib.itemBrandId = p.item_brand_id', 'left')
		->join('item_categories ic', 'ic.itemCategoryId = p.item_category_id', 'left')
		->join('suppliers s', 's.supplier_id = poi.supplier_id', 'left')
		->where('p.is_deleted', 0)
		->where('p.is_active', 1)
		->where('poi.available_stock <=', 'p.reorder_level', false)
		->where('poi.available_stock >', 0)
		->order_by('(poi.available_stock / p.reorder_level)', 'ASC')
		->get()->result();
	}

	private function getStockSummary()
	{
		$summary = $this->db->select('
			COUNT(DISTINCT p.product_id) as total_products,
			SUM(poi.available_stock) as total_quantity,
			SUM(poi.available_stock * poi.company_price) as total_value_cost,
			SUM(poi.available_stock * poi.sale_price) as total_value_retail,
			COUNT(CASE WHEN poi.available_stock <= 0 THEN 1 END) as out_of_stock_count,
			COUNT(CASE WHEN poi.available_stock <= p.reorder_level AND poi.available_stock > 0 THEN 1 END) as low_stock_count
		')
		->from('products p')
		->join('purchase_order_items poi', 'poi.product_id = p.product_id')
		->where('p.is_deleted', 0)
		->where('p.is_active', 1)
		->get()->row();

		return $summary;
	}

	public function stockPdf()
	{
		$category = $this->input->get('category');
		$brand = $this->input->get('brand');
		$supplier = $this->input->get('supplier');
		$stock_status = $this->input->get('stock_status');
		$search = $this->input->get('search');

		// Get stock data
		$_POST['category'] = $category;
		$_POST['brand'] = $brand;
		$_POST['supplier'] = $supplier;
		$_POST['stock_status'] = $stock_status;
		$_POST['search'] = $search;

		ob_start();
		$this->stockData();
		$json = ob_get_clean();
		$payload = json_decode($json, true);
		
		if (!$payload || $payload['status'] !== 'success') {
			show_error('Failed to generate stock report');
			return;
		}

		$data = $payload['data'];
		$data['filters'] = [
			'category' => $category,
			'brand' => $brand,
			'supplier' => $supplier,
			'stock_status' => $stock_status,
			'search' => $search
		];

		// Load PDF library
		$this->load->library('pdf');
		
		// Generate PDF
		$html = $this->load->view('reports/stock-pdf-template', $data, true);
		
		$this->pdf->loadHtml($html);
		$this->pdf->setPaper('A4', 'landscape');
		$this->pdf->render();
		
		$filename = 'Stock_Report_' . date('Y-m-d_H-i-s') . '.pdf';
		$this->pdf->stream($filename);
	}

	/**
	 * Expenses Report - Main View
	 */
	public function expenses()
	{
		$this->require_permission('reports.view');
		$data['mainMenuName'] = 'report';
		$data['subMenuName'] = 'expenses-report';

		$this->load->view('layout/header');
		$this->load->view('layout/top_navbar');
		$this->load->view('layout/left_sidebar', $data);
		$this->load->view('reports/expenses', $data);
		$this->load->view('layout/footer');
	}

	/**
	 * Get Expenses Data via AJAX
	 */
	public function expensesData()
	{
		$date_from = $this->input->post('date_from') ?: date('Y-m-01');
		$date_to = $this->input->post('date_to') ?: date('Y-m-d');
		$category = $this->input->post('category');
		$employee = $this->input->post('employee');
		$search = $this->input->post('search');

		// Debug logging
		log_message('debug', 'ExpensesData called with: date_from=' . $date_from . ', date_to=' . $date_to . ', category=' . $category . ', employee=' . $employee . ', search=' . $search);

		try {
			// Test basic database connection first
			$testQuery = $this->db->get('expenses', 1);
			log_message('debug', 'Test query result count: ' . $testQuery->num_rows());
			
			// Test if tables exist
			$tables = $this->db->list_tables();
			log_message('debug', 'Available tables: ' . implode(', ', $tables));
			
			$expensesData = $this->getExpensesData($date_from, $date_to, $category, $employee, $search);
			log_message('debug', 'Expenses data count: ' . count($expensesData));
			
			$categoryAnalysis = $this->getExpensesCategoryAnalysis($date_from, $date_to);
			log_message('debug', 'Category analysis count: ' . count($categoryAnalysis));
			
			$employeeAnalysis = $this->getExpensesEmployeeAnalysis($date_from, $date_to);
			log_message('debug', 'Employee analysis count: ' . count($employeeAnalysis));
			
			$monthlyTrends = $this->getExpensesMonthlyTrends($date_from, $date_to);
			log_message('debug', 'Monthly trends count: ' . count($monthlyTrends));
			
			$summary = $this->getExpensesSummary($date_from, $date_to);
			log_message('debug', 'Summary data: ' . json_encode($summary));

			$response = [
				'status' => 'success',
				'data' => [
					'expenses_data' => $expensesData,
					'category_analysis' => $categoryAnalysis,
					'employee_analysis' => $employeeAnalysis,
					'monthly_trends' => $monthlyTrends,
					'summary' => $summary
				]
			];

			$this->output
				->set_content_type('application/json')
				->set_output(json_encode($response));

		} catch (Exception $e) {
			// Debug logging
			log_message('error', 'ExpensesData error: ' . $e->getMessage());
			log_message('error', 'ExpensesData stack trace: ' . $e->getTraceAsString());
			
			$response = [
				'status' => 'error',
				'message' => 'Failed to fetch expenses data: ' . $e->getMessage()
			];

			$this->output
				->set_content_type('application/json')
				->set_output(json_encode($response));
		}
	}

	/**
	 * Generate Expenses PDF
	 */
	public function expensesPdf()
	{
		$date_from = $this->input->get('date_from') ?: date('Y-m-01');
		$date_to = $this->input->get('date_to') ?: date('Y-m-d');
		$category = $this->input->get('category');
		$employee = $this->input->get('employee');
		$search = $this->input->get('search');

		try {
			$expensesData = $this->getExpensesData($date_from, $date_to, $category, $employee, $search);
			$categoryAnalysis = $this->getExpensesCategoryAnalysis($date_from, $date_to);
			$employeeAnalysis = $this->getExpensesEmployeeAnalysis($date_from, $date_to);
			$monthlyTrends = $this->getExpensesMonthlyTrends($date_from, $date_to);
			$summary = $this->getExpensesSummary($date_from, $date_to);

			$data = [
				'expenses_data' => $expensesData,
				'category_analysis' => $categoryAnalysis,
				'employee_analysis' => $employeeAnalysis,
				'monthly_trends' => $monthlyTrends,
				'summary' => $summary,
				'filters' => [
					'date_from' => $date_from,
					'date_to' => $date_to,
					'category' => $category,
					'employee' => $employee,
					'search' => $search
				]
			];

			// Load PDF library
			$this->load->library('pdf');
			
			// Generate PDF
			$html = $this->load->view('reports/expenses-pdf-template', $data, true);
			
			$this->pdf->loadHtml($html);
			$this->pdf->setPaper('A4', 'landscape');
			$this->pdf->render();
			
			$filename = 'Expenses_Report_' . date('Y-m-d_H-i-s') . '.pdf';
			$this->pdf->stream($filename);

		} catch (Exception $e) {
			show_error('Failed to generate expenses report');
			return;
		}
	}

	/**
	 * Get Detailed Expenses Data
	 */
	private function getExpensesData($date_from, $date_to, $category = null, $employee = null, $search = null)
	{
		try {
			$this->db->select('
				e.id as expense_id,
				e.expense_date,
				e.amount,
				e.description,
				e.reference_no as receipt_number,
				e.payment_method,
				"approved" as status,
				e.created_at,
				ec.expensesCategoryName as category_name,
				CONCAT(u.FirstName, " ", u.LastName) as employee_name,
				u.EmployeeID as employee_id
			');
			$this->db->from('expenses e');
			$this->db->join('expenses_categories ec', 'ec.expensesCategoryId = e.category_id', 'left');
			$this->db->join('users u', 'u.UserID = e.created_by', 'left');
			$this->db->where('e.expense_date >=', $date_from);
			$this->db->where('e.expense_date <=', $date_to);

			if ($category) {
				$this->db->where('e.category_id', $category);
			}
			if ($employee) {
				$this->db->where('e.created_by', $employee);
			}
			if ($search) {
				$this->db->group_start();
				$this->db->like('e.description', $search);
				$this->db->or_like('e.reference_no', $search);
				$this->db->or_like('CONCAT(u.FirstName, " ", u.LastName)', $search);
				$this->db->group_end();
			}

			$this->db->order_by('e.expense_date', 'DESC');
			$this->db->order_by('e.created_at', 'DESC');

			$query = $this->db->get();
			return $query->result();
		} catch (Exception $e) {
			log_message('error', 'getExpensesData error: ' . $e->getMessage());
			return [];
		}
	}

	/**
	 * Get Expenses Category Analysis
	 */
	private function getExpensesCategoryAnalysis($date_from, $date_to)
	{
		try {
			$this->db->select('
				ec.expensesCategoryName as category_name,
				COUNT(e.id) as total_expenses,
				SUM(e.amount) as total_amount,
				AVG(e.amount) as avg_amount,
				MIN(e.amount) as min_amount,
				MAX(e.amount) as max_amount
			');
			$this->db->from('expenses e');
			$this->db->join('expenses_categories ec', 'ec.expensesCategoryId = e.category_id', 'left');
			$this->db->where('e.expense_date >=', $date_from);
			$this->db->where('e.expense_date <=', $date_to);
			$this->db->group_by('e.category_id, ec.expensesCategoryName');
			$this->db->order_by('total_amount', 'DESC');

			$query = $this->db->get();
			return $query->result();
		} catch (Exception $e) {
			log_message('error', 'getExpensesCategoryAnalysis error: ' . $e->getMessage());
			return [];
		}
	}

	/**
	 * Get Expenses Employee Analysis
	 */
	private function getExpensesEmployeeAnalysis($date_from, $date_to)
	{
		try {
			$this->db->select('
				CONCAT(u.FirstName, " ", u.LastName) as employee_name,
				u.EmployeeID as employee_id,
				COUNT(e.id) as total_expenses,
				SUM(e.amount) as total_amount,
				AVG(e.amount) as avg_amount
			');
			$this->db->from('expenses e');
			$this->db->join('users u', 'u.UserID = e.created_by', 'left');
			$this->db->where('e.expense_date >=', $date_from);
			$this->db->where('e.expense_date <=', $date_to);
			$this->db->group_by('e.created_by, u.FirstName, u.LastName, u.EmployeeID');
			$this->db->order_by('total_amount', 'DESC');

			$query = $this->db->get();
			return $query->result();
		} catch (Exception $e) {
			log_message('error', 'getExpensesEmployeeAnalysis error: ' . $e->getMessage());
			return [];
		}
	}

	/**
	 * Get Monthly Trends
	 */
	private function getExpensesMonthlyTrends($date_from, $date_to)
	{
		try {
			$this->db->select('
				DATE_FORMAT(e.expense_date, "%Y-%m") as month,
				COUNT(e.id) as total_expenses,
				SUM(e.amount) as total_amount
			');
			$this->db->from('expenses e');
			$this->db->where('e.expense_date >=', $date_from);
			$this->db->where('e.expense_date <=', $date_to);
			$this->db->group_by('DATE_FORMAT(e.expense_date, "%Y-%m")');
			$this->db->order_by('month', 'ASC');

			$query = $this->db->get();
			return $query->result();
		} catch (Exception $e) {
			log_message('error', 'getExpensesMonthlyTrends error: ' . $e->getMessage());
			return [];
		}
	}

	/**
	 * Get Expenses Summary
	 */
	private function getExpensesSummary($date_from, $date_to)
	{
		try {
			// Total expenses
			$total_expenses_query = $this->db->select('SUM(amount) as total')
				->from('expenses')
				->where('expense_date >=', $date_from)
				->where('expense_date <=', $date_to)
				->get();
			$total_expenses = $total_expenses_query->row() ? $total_expenses_query->row()->total : 0;

			// Total count
			$total_count_query = $this->db->select('COUNT(*) as count')
				->from('expenses')
				->where('expense_date >=', $date_from)
				->where('expense_date <=', $date_to)
				->get();
			$total_count = $total_count_query->row() ? $total_count_query->row()->count : 0;

			// Average amount
			$avg_amount = $total_count > 0 ? $total_expenses / $total_count : 0;

			// Top category
			$top_category_query = $this->db->select('ec.expensesCategoryName as category_name, SUM(e.amount) as total_amount')
				->from('expenses e')
				->join('expenses_categories ec', 'ec.expensesCategoryId = e.category_id', 'left')
				->where('e.expense_date >=', $date_from)
				->where('e.expense_date <=', $date_to)
				->group_by('e.category_id')
				->order_by('total_amount', 'DESC')
				->limit(1)
				->get();
			$top_category = $top_category_query->row();

			// Payment method breakdown
			$payment_methods_query = $this->db->select('
				payment_method,
				COUNT(*) as count,
				SUM(amount) as total_amount
			')
			->from('expenses')
			->where('expense_date >=', $date_from)
			->where('expense_date <=', $date_to)
			->group_by('payment_method')
			->get();
			$payment_methods = $payment_methods_query->result();

			return [
				'total_expenses' => $total_expenses,
				'total_count' => $total_count,
				'avg_amount' => $avg_amount,
				'top_category' => $top_category,
				'payment_methods' => $payment_methods
			];
		} catch (Exception $e) {
			log_message('error', 'getExpensesSummary error: ' . $e->getMessage());
			return [
				'total_expenses' => 0,
				'total_count' => 0,
				'avg_amount' => 0,
				'top_category' => null,
				'payment_methods' => []
			];
		}
	}

	/**
	 * Get Employees for Filter
	 */
	public function getEmployees()
	{
		$this->db->select('UserID, FirstName, LastName');
		$this->db->from('users');
		$this->db->where('IsActive', 1);
		$query = $this->db->get();
		
		$response = [
			'status' => 'success',
			'data' => $query->result()
		];

		$this->output
			->set_content_type('application/json')
			->set_output(json_encode($response));
	}

	/**
	 * Get Categories for Filter
	 */
	public function getCategories()
	{
		$this->db->select('expensesCategoryId, expensesCategoryName');
		$this->db->from('expenses_categories');
		$this->db->where('isDeleted', 0);
		$query = $this->db->get();
		
		$response = [
			'status' => 'success',
			'data' => $query->result()
		];

		$this->output
			->set_content_type('application/json')
			->set_output(json_encode($response));
	}

	/**
	 * Test database connection and tables
	 */
	public function testDatabase()
	{
		try {
			// Test database connection
			$connection = $this->db->initialize();
			
			// List all tables
			$tables = $this->db->list_tables();
			
			// Test expenses table
			$expensesCount = $this->db->count_all('expenses');
			
			// Test users table
			$usersCount = $this->db->count_all('users');
			
			// Test expenses_categories table
			$categoriesCount = $this->db->count_all('expenses_categories');
			
			$response = [
				'status' => 'success',
				'data' => [
					'connection' => 'OK',
					'tables' => $tables,
					'expenses_count' => $expensesCount,
					'users_count' => $usersCount,
					'categories_count' => $categoriesCount
				]
			];
			
		} catch (Exception $e) {
			$response = [
				'status' => 'error',
				'message' => $e->getMessage()
			];
		}

		$this->output
			->set_content_type('application/json')
			->set_output(json_encode($response));
	}
}
