<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Accounts extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('wallet_model');
    }

    public function in_hand()
    {
        $this->require_permission('accounts.inhand');
        $data = $this->loadAccount('in_hand', 'Accounts - In Hand');
        $this->load->view('layout/header');
        $this->load->view('layout/top_navbar');
        $this->load->view('layout/left_sidebar', $data);
        $this->load->view('accounts/account_view', $data);
        $this->load->view('layout/footer');
    }

    public function bank()
    {
        $this->require_permission('accounts.bank');
        $data = $this->loadAccount('bank', 'Accounts - Bank');
        $this->load->view('layout/header');
        $this->load->view('layout/top_navbar');
        $this->load->view('layout/left_sidebar', $data);
        $this->load->view('accounts/account_view', $data);
        $this->load->view('layout/footer');
    }

    public function credits()
    {
        $this->require_permission('accounts.credits');
        $data = $this->loadCreditsAccount('credits', 'Accounts - Credits');
        $this->load->view('layout/header');
        $this->load->view('layout/top_navbar');
        $this->load->view('layout/left_sidebar', $data);
        $this->load->view('accounts/credits_view', $data);
        $this->load->view('layout/footer');
    }

    public function loan()
    {
        $this->require_permission('accounts.loans');
        $data['mainMenuName'] = 'Accounts - Loan';
        $data['subMenuName'] = 'loan';

        // Ensure a single loan account exists with 0 balance if not present
        $loan = $this->db->get_where('accounts', ['slug' => 'loan'])->row();
        if (!$loan) {
            $this->db->insert('accounts', [
                'slug' => 'loan',
                'name' => 'Loan',
                'balance' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            $loan = $this->db->get_where('accounts', ['slug' => 'loan'])->row();
        }

        $data['account'] = $loan;

        // Load two transaction lists filtered by custom field loan_direction (in|out)
        $this->db->order_by('created_at', 'DESC');
        $this->db->limit(50);
        $data['txns_in'] = $this->db->get_where('account_transactions', ['account_slug' => 'loan', 'loan_direction' => 'in'])->result();

        $this->db->order_by('created_at', 'DESC');
        $this->db->limit(50);
        $data['txns_out'] = $this->db->get_where('account_transactions', ['account_slug' => 'loan', 'loan_direction' => 'out'])->result();

        $this->load->view('layout/header');
        $this->load->view('layout/top_navbar');
        $this->load->view('layout/left_sidebar', $data);
        $this->load->view('accounts/loan_view', $data);
        $this->load->view('layout/footer');
    }

    public function loan_add()
    {
        $this->require_permission('accounts.loans');
        if ($this->input->method() !== 'post') {
            show_error('Invalid method', 405);
        }

        $direction = $this->input->post('direction'); // in|out
        $amount = floatval($this->input->post('amount'));
        $description = $this->input->post('description');

        if (!in_array($direction, ['in','out'], true) || $amount <= 0) {
            $this->output->set_content_type('application/json')->set_output(json_encode(['status' => 'error', 'message' => 'Invalid input']));
            return;
        }

        $company = $this->input->post('company_name');
        $expected = $this->input->post('expected_return_date');
        $result = $this->wallet_model->addLoanTransaction(
            $direction,
            $amount,
            $description,
            'loan',
            null,
            $this->session->userdata('userid'),
            $company,
            $expected
        );

        $this->output->set_content_type('application/json')->set_output(json_encode($result));
    }

    public function loan_return()
    {
        $this->require_permission('accounts.loans');
        try {
            if ($this->input->method() !== 'post') { 
                $this->output->set_content_type('application/json')->set_output(json_encode(['status'=>'error','message'=>'Invalid method']));
                return;
            }
            
            $parentId = intval($this->input->post('parent_txn_id'));
            $amount = floatval($this->input->post('amount'));
            $description = $this->input->post('description');
            
            if ($parentId <= 0 || $amount <= 0) {
                $this->output->set_content_type('application/json')->set_output(json_encode(['status'=>'error','message'=>'Invalid input: parent_id='.$parentId.', amount='.$amount]));
                return;
            }
            
            // Check if loan is already fully paid back
            $parent = $this->db->get_where('account_transactions', ['txn_id' => $parentId, 'account_slug' => 'loan'])->row();
            if (!$parent) {
                $this->output->set_content_type('application/json')->set_output(json_encode(['status'=>'error','message'=>'Parent loan not found']));
                return;
            }
            
            $originalAmount = floatval($parent->amount);
            $returnedAmount = floatval($parent->returned_total);
            
            if ($returnedAmount >= $originalAmount) {
                $this->output->set_content_type('application/json')->set_output(json_encode(['status'=>'error','message'=>'This loan is already fully paid back!']));
                return;
            }
            
            if (($returnedAmount + $amount) > $originalAmount) {
                $this->output->set_content_type('application/json')->set_output(json_encode(['status'=>'error','message'=>'Return amount exceeds remaining balance. Remaining: LKR '.number_format($originalAmount - $returnedAmount, 2)]));
                return;
            }
            
            $res = $this->wallet_model->returnLoan($parentId, $amount, $description, $this->session->userdata('userid'));
            $this->output->set_content_type('application/json')->set_output(json_encode($res));
            
        } catch (Exception $e) {
            $this->output->set_content_type('application/json')->set_output(json_encode(['status'=>'error','message'=>'Server error: '.$e->getMessage()]));
        }
    }

    public function bank_transfer()
    {
        $this->require_permission('accounts.banktransfer');
        $data = $this->loadAccount('bank', 'Accounts - Bank Transfer');
        $this->load->view('layout/header');
        $this->load->view('layout/top_navbar');
        $this->load->view('layout/left_sidebar', $data);
        $this->load->view('accounts/account_view', $data);
        $this->load->view('layout/footer');
    }

    public function transfer()
    {
        $this->require_permission('accounts.banktransfer');
        if ($this->input->method() !== 'post') {
            $this->output->set_content_type('application/json')->set_output(json_encode(['status'=>'error','message'=>'Invalid method']));
            return;
        }
        
        $fromSlug = $this->input->post('from_slug');
        $toSlug = $this->input->post('to_slug');
        $amount = floatval($this->input->post('amount'));
        $reason = $this->input->post('reason');
        $note = $this->input->post('note');
        
        if (!$fromSlug || !$toSlug || $amount <= 0 || !$reason) {
            $this->output->set_content_type('application/json')->set_output(json_encode(['status'=>'error','message'=>'Missing required fields']));
            return;
        }
        
        $result = $this->wallet_model->transferBetweenAccounts(
            $fromSlug, 
            $toSlug, 
            $amount, 
            $reason, 
            $note, 
            $this->session->userdata('userid')
        );
        
        $this->output->set_content_type('application/json')->set_output(json_encode($result));
    }

    public function card_machine()
    {
        $this->require_permission('accounts.cardmachine');
        $data = $this->loadAccount('card_machine', 'Accounts - Card Machine');
        $this->load->view('layout/header');
        $this->load->view('layout/top_navbar');
        $this->load->view('layout/left_sidebar', $data);
        $this->load->view('accounts/account_view', $data);
        $this->load->view('layout/footer');
    }

    public function cheque()
    {
        $this->require_permission('accounts.cheque');
        $data = $this->loadChequePayments('Accounts - Cheque');
        $this->load->view('layout/header');
        $this->load->view('layout/top_navbar');
        $this->load->view('layout/left_sidebar', $data);
        $this->load->view('accounts/cheque_view', $data);
        $this->load->view('layout/footer');
    }

    public function receive_cheque()
    {
        $this->require_permission('accounts.cheque');
        if ($this->input->method() !== 'post') {
            $this->output->set_content_type('application/json')->set_output(json_encode(['status' => 'error', 'message' => 'Invalid method']));
            return;
        }

        $source = strtolower(trim($this->input->post('source'))); // quick_bill | service_job | purchase_order
        $paymentId = intval($this->input->post('payment_id'));

        if (!$source || $paymentId <= 0) {
            $this->output->set_content_type('application/json')->set_output(json_encode(['status' => 'error', 'message' => 'Missing required fields']));
            return;
        }

        // Prevent double-clearing: check account_transactions for an existing cheque_cleared entry with this payment_id
        $existing = $this->db->get_where('account_transactions', [
            'reference_type' => 'cheque_cleared',
            'reference_id' => $paymentId
        ])->row();
        if ($existing) {
            $this->output->set_content_type('application/json')->set_output(json_encode(['status' => 'success', 'message' => 'Cheque already received to bank']));
            return;
        }

        // Load payment by source
        $amount = 0; $desc = ''; $customerId = null; $refText = ''; $bankName = ''; $chequeNo = '';
        try {
            if ($source === 'quick_bill') {
                $p = $this->db->get_where('quick_bill_payments', ['id' => $paymentId])->row();
                if (!$p || strtolower($p->payment_method) !== 'cheque') throw new Exception('Payment not found or not a cheque');
                $amount = floatval($p->paid_amount);
                $bankName = $p->bank_name ?: '';
                $chequeNo = $p->card_or_cheque_no ?: '';
                $bill = $this->db->get_where('quick_bill', ['quick_bill_id' => $p->quick_bill_id])->row();
                $customerId = $bill ? ($bill->customer_id ?? null) : null;
                $refText = 'Quick Bill #' . ($p->quick_bill_id ?? '');
                $desc = 'Cheque received to bank for ' . $refText . ($chequeNo ? (' (Cheque: ' . $chequeNo . ')') : '') . ($bankName ? (' - ' . $bankName) : '');
            } elseif ($source === 'service_job') {
                $p = $this->db->get_where('services_job_payments', ['id' => $paymentId])->row();
                if (!$p || strtolower($p->payment_method) !== 'cheque') throw new Exception('Payment not found or not a cheque');
                $amount = floatval($p->paid_amount);
                $bankName = $p->bank_name ?: '';
                $chequeNo = $p->cheque_number ?: '';
                $job = $this->db->get_where('services_job', ['job_id' => $p->service_job_id])->row();
                $customerId = $job ? ($job->customer_id ?? null) : null;
                $refText = 'Service Job #' . ($p->service_job_id ?? '');
                $desc = 'Cheque received to bank for ' . $refText . ($chequeNo ? (' (Cheque: ' . $chequeNo . ')') : '') . ($bankName ? (' - ' . $bankName) : '');
            } elseif ($source === 'purchase_order') {
                $p = $this->db->get_where('purchase_order_payments', ['id' => $paymentId])->row();
                if (!$p || strtolower($p->payment_method) !== 'cheque') throw new Exception('Payment not found or not a cheque');
                $amount = floatval($p->paid_amount);
                $bankName = $p->bank_name ?: '';
                $chequeNo = $p->card_or_cheque_no ?: '';
                $po = $this->db->get_where('purchase_orders', ['po_id' => $p->po_id])->row();
                $customerId = null; // Supplier payment
                $refText = 'PO #' . ($p->po_id ?? '');
                $desc = 'Cheque received to bank for ' . $refText . ($chequeNo ? (' (Cheque: ' . $chequeNo . ')') : '') . ($bankName ? (' - ' . $bankName) : '');
            } else {
                throw new Exception('Invalid source');
            }

            if ($amount <= 0) throw new Exception('Invalid amount');

            // Credit bank via wallet as bank_transfer
            $paymentData = [
                'payment_method' => 'bank_transfer',
                'amount' => $amount,
                'description' => $desc,
                'reference_type' => 'cheque_cleared',
                'reference_id' => $paymentId,
                'customer_id' => $customerId,
                'created_by' => $this->session->userdata('userid')
            ];

            $result = $this->wallet_model->processPayment($paymentData);
            if ($result['status'] !== 'success') throw new Exception($result['message']);

            $this->output->set_content_type('application/json')->set_output(json_encode(['status' => 'success', 'message' => 'Cheque received to bank successfully']));
        } catch (Exception $e) {
            $this->output->set_content_type('application/json')->set_output(json_encode(['status' => 'error', 'message' => $e->getMessage()]));
        }
    }

    private function loadAccount($slug, $menuTitle)
    {
        // Load account (tables and seeds are created via SQL migration)
        $account = $this->db->get_where('accounts', ['slug' => $slug])->row();

        // Get filter parameters
        $txnType = $this->input->get('txn_type');
        $dateFrom = $this->input->get('date_from');
        $dateTo = $this->input->get('date_to');

        // Load transactions with filters
        $this->db->order_by('created_at', 'DESC');
        $this->db->limit(50); // Increased limit to show more transactions
        
        // Apply filters
        if ($txnType) {
            $this->db->where('txn_type', $txnType);
        }
        if ($dateFrom) {
            $this->db->where('DATE(created_at) >=', $dateFrom);
        }
        if ($dateTo) {
            $this->db->where('DATE(created_at) <=', $dateTo);
        }
        
        $txns = $this->db->get_where('account_transactions', ['account_slug' => $slug])->result();

        return [
            'mainMenuName' => $menuTitle,
            'subMenuName' => $slug,
            'account' => $account,
            'transactions' => $txns,
            'filters' => [
                'txn_type' => $txnType,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ]
        ];
    }

    private function loadChequePayments($menuTitle)
    {
        $dateFrom = $this->input->get('date_from');
        $dateTo = $this->input->get('date_to');
        $customerId = $this->input->get('customer_id');
        $supplierId = $this->input->get('supplier_id');
        $status = $this->input->get('status') ?: 'uncleared'; // for default UI selection only

        $payments = [];
        $alerts = [];
        $today = date('Y-m-d');

        // Quick bill cheques
        $this->db->from('quick_bill_payments');
        $this->db->where('payment_method', 'cheque');
        if ($dateFrom) $this->db->where('DATE(payment_date) >=', $dateFrom);
        if ($dateTo) $this->db->where('DATE(payment_date) <=', $dateTo);
        $qbCheques = $this->db->get()->result();

        foreach ($qbCheques as $p) {
            $bill = $this->db->get_where('quick_bill', ['quick_bill_id' => $p->quick_bill_id])->row();
            if ($customerId && $bill && (int)$bill->customer_id !== (int)$customerId) continue;
            $cust = $bill ? $this->db->get_where('customers', ['customers_id' => $bill->customer_id])->row() : null;
            $cleared = $this->db->get_where('account_transactions', ['reference_type' => 'cheque_cleared', 'reference_id' => $p->id])->row() ? true : false;
            $cleared = $this->db->get_where('account_transactions', ['reference_type' => 'cheque_cleared', 'reference_id' => $p->id])->row() ? true : false;
            $daysUntil = null;
            if (!empty($p->cheque_date)) {
                $daysUntil = floor((strtotime($p->cheque_date) - strtotime($today)) / 86400);
            }
            $payObj = (object) [
                'payment_id' => $p->id,
                'source' => 'Quick Bill',
                'reference' => $p->quick_bill_id,
                'party' => $cust ? $cust->name : 'Walk-in',
                'amount' => $p->paid_amount,
                'card_or_cheque_no' => $p->card_or_cheque_no,
                'cheque_date' => $p->cheque_date,
                'bank_name' => $p->bank_name,
                'note' => $p->note,
                'payment_date' => $p->payment_date,
                'cleared' => $cleared,
                'days_until' => $daysUntil,
            ];
            $payments[] = $payObj;
            if (!$cleared && $daysUntil !== null && in_array($daysUntil, [7,3,0], true)) {
                $alerts[] = [
                    'msg' => ($daysUntil === 0 ? 'Cheque due today' : ($daysUntil . ' day(s) until cheque due')) . ' - QB-' . $p->quick_bill_id . ' (' . ($p->card_or_cheque_no ?: 'No#') . ')',
                    'level' => $daysUntil === 0 ? 'warning' : 'info'
                ];
            }
        }

        // Service job cheques
        $this->db->from('services_job_payments');
        $this->db->where('payment_method', 'cheque');
        if ($dateFrom) $this->db->where('DATE(payment_date) >=', $dateFrom);
        if ($dateTo) $this->db->where('DATE(payment_date) <=', $dateTo);
        $jobCheques = $this->db->get()->result();

        foreach ($jobCheques as $p) {
            $job = $this->db->get_where('services_job', ['job_id' => $p->service_job_id])->row();
            if ($customerId && $job && (int)$job->customer_id !== (int)$customerId) continue;
            $cust = $job ? $this->db->get_where('customers', ['customers_id' => $job->customer_id])->row() : null;
            $cleared = $this->db->get_where('account_transactions', ['reference_type' => 'cheque_cleared', 'reference_id' => $p->id])->row() ? true : false;
            $cleared = $this->db->get_where('account_transactions', ['reference_type' => 'cheque_cleared', 'reference_id' => $p->id])->row() ? true : false;
            $daysUntil = null;
            if (!empty($p->cheque_date)) {
                $daysUntil = floor((strtotime($p->cheque_date) - strtotime($today)) / 86400);
            }
            $payObj = (object) [
                'payment_id' => $p->id,
                'source' => 'Service Job',
                'reference' => $p->service_job_id,
                'party' => $cust ? $cust->name : 'Unknown',
                'amount' => $p->paid_amount,
                'card_or_cheque_no' => $p->cheque_number,
                'cheque_date' => $p->cheque_date,
                'bank_name' => $p->bank_name,
                'note' => $p->note,
                'payment_date' => $p->payment_date,
                'cleared' => $cleared,
                'days_until' => $daysUntil,
            ];
            $payments[] = $payObj;
            if (!$cleared && $daysUntil !== null && in_array($daysUntil, [7,3,0], true)) {
                $alerts[] = [
                    'msg' => ($daysUntil === 0 ? 'Cheque due today' : ($daysUntil . ' day(s) until cheque due')) . ' - JOB-' . $p->service_job_id . ' (' . ($p->cheque_number ?: 'No#') . ')',
                    'level' => $daysUntil === 0 ? 'warning' : 'info'
                ];
            }
        }

        // Purchase order cheques
        $this->db->from('purchase_order_payments');
        $this->db->where('payment_method', 'cheque');
        if ($dateFrom) $this->db->where('DATE(payment_date) >=', $dateFrom);
        if ($dateTo) $this->db->where('DATE(payment_date) <=', $dateTo);
        $poCheques = $this->db->get()->result();

        foreach ($poCheques as $p) {
            $po = $this->db->get_where('purchase_orders', ['po_id' => $p->po_id])->row();
            if ($supplierId && $po && (int)$po->supplier_id !== (int)$supplierId) continue;
            $sup = $po ? $this->db->get_where('suppliers', ['supplier_id' => $po->supplier_id])->row() : null;
            $cleared = $this->db->get_where('account_transactions', ['reference_type' => 'cheque_cleared', 'reference_id' => $p->id])->row() ? true : false;
            $cleared = $this->db->get_where('account_transactions', ['reference_type' => 'cheque_cleared', 'reference_id' => $p->id])->row() ? true : false;
            $daysUntil = null;
            if (!empty($p->cheque_date)) {
                $daysUntil = floor((strtotime($p->cheque_date) - strtotime($today)) / 86400);
            }
            $payObj = (object) [
                'payment_id' => $p->id,
                'source' => 'Purchase Order',
                'reference' => $p->po_id,
                'party' => $sup ? $sup->name : 'Unknown',
                'amount' => $p->paid_amount,
                'card_or_cheque_no' => $p->card_or_cheque_no,
                'cheque_date' => $p->cheque_date,
                'bank_name' => $p->bank_name,
                'note' => $p->note,
                'payment_date' => $p->payment_date,
                'cleared' => $cleared,
                'days_until' => $daysUntil,
            ];
            $payments[] = $payObj;
            if (!$cleared && $daysUntil !== null && in_array($daysUntil, [7,3,0], true)) {
                $alerts[] = [
                    'msg' => ($daysUntil === 0 ? 'Cheque due today' : ($daysUntil . ' day(s) until cheque due')) . ' - PO-' . $p->po_id . ' (' . ($p->card_or_cheque_no ?: 'No#') . ')',
                    'level' => $daysUntil === 0 ? 'warning' : 'info'
                ];
            }
        }

        // Apply status filter (default to show only uncleared)
        // NOTE: Always return full list; client handles live toggle filtering

        // Sort by payment_date desc
        usort($payments, function($a, $b){
            return strtotime($b->payment_date) <=> strtotime($a->payment_date);
        });

        // Ensure an account entry exists for menu and sidebar context
        $account = $this->db->get_where('accounts', ['slug' => 'bank'])->row();
        if (!$account) {
            $this->db->insert('accounts', [
                'slug' => 'bank',
                'name' => 'Bank',
                'balance' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            $account = $this->db->get_where('accounts', ['slug' => 'bank'])->row();
        }

        // Load dropdowns
        $customers = $this->db->select('customers_id, name')->order_by('name', 'ASC')->get('customers')->result();
        $suppliers = $this->db->select('supplier_id, name')->order_by('name', 'ASC')->get('suppliers')->result();

        return [
            'mainMenuName' => $menuTitle,
            'subMenuName' => 'cheque',
            'account' => $account,
            'payments' => $payments,
            'customers' => $customers,
            'suppliers' => $suppliers,
            'filters' => [
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'customer_id' => $customerId,
                'supplier_id' => $supplierId,
                'status' => $status,
            ],
            'alerts' => $alerts
        ];
    }

    private function loadCreditsAccount($slug, $menuTitle)
    {
        // Load account
        $account = $this->db->get_where('accounts', ['slug' => $slug])->row();

        // Get filter parameters
        $customerId = $this->input->get('customer_id');
        $supplierId = $this->input->get('supplier_id');
        $dateFrom = $this->input->get('date_from');
        $dateTo = $this->input->get('date_to');
        $txnType = $this->input->get('txn_type');

        // Load credit transactions with customer/supplier information
        $this->db->select('at.*, c.name as customer_name, s.name as supplier_name, qb.quick_bill_id, qb.bill_amount as total_bill_amount, qb.credit_person_name, qb.credit_person_phone, qb.credit_due_date, po.po_id, po.bill_no as po_bill_no, po.total_amount as po_total_amount, po.credit_person_name as po_credit_person_name, po.credit_person_phone as po_credit_person_phone, sj.job_id, sj.final_bill_amount as job_total_amount, sj.credit_person as job_credit_person, sj.credit_remark as job_credit_remark, sj.credit_pay_date as job_credit_due_date');
        $this->db->from('account_transactions at');
        $this->db->join('customers c', 'c.customers_id = at.customer_id', 'left');
        $this->db->join('quick_bill qb', 'qb.quick_bill_id = at.reference_id AND at.reference_type = "quick_bill"', 'left');
        $this->db->join('purchase_orders po', 'po.po_id = at.reference_id AND at.reference_type = "purchase_order"', 'left');
        $this->db->join('suppliers s', 's.supplier_id = po.supplier_id', 'left');
        $this->db->join('services_job sj', 'sj.job_id = at.reference_id AND at.reference_type = "service_job"', 'left');
        $this->db->where('at.account_slug', $slug);
        $this->db->where('at.txn_type', 'credit');
        
        // Apply filters
        if ($customerId) {
            $this->db->where('at.customer_id', $customerId);
        }
        if ($supplierId) {
            $this->db->where('po.supplier_id', $supplierId);
        }
        if ($dateFrom) {
            $this->db->where('DATE(at.created_at) >=', $dateFrom);
        }
        if ($dateTo) {
            $this->db->where('DATE(at.created_at) <=', $dateTo);
        }
        if ($txnType) {
            $this->db->where('at.txn_type', $txnType);
        }
        
        $this->db->order_by('at.created_at', 'DESC');
        $this->db->limit(50);
        $transactions = $this->db->get()->result();

        // Filter to show only ongoing credits (not fully paid)
        $ongoingCredits = [];
        foreach ($transactions as $credit) {
            $isOngoing = false;

            // Check if still ongoing based on reference type
            if ($credit->reference_type === 'quick_bill') {
                // Check if quick_bill has remaining balance > 0
                $bill = $this->db->select('total_balance, total_paid')
                    ->where('quick_bill_id', $credit->quick_bill_id)
                    ->get('quick_bill')
                    ->row();
                
                if ($bill && $bill->total_balance > 0) {
                    $isOngoing = true;
                    $credit->total_paid_amount = $bill->total_paid;
                    $credit->remaining_amount = $bill->total_balance;
                }
                
            } elseif ($credit->reference_type === 'service_job') {
                // Check if service job has remaining balance > 0
                $job = $this->db->select('total_balance, total_paid')
                    ->where('job_id', $credit->job_id)
                    ->get('services_job')
                    ->row();
                
                if ($job && $job->total_balance > 0) {
                    $isOngoing = true;
                    $credit->total_paid_amount = $job->total_paid;
                    $credit->remaining_amount = $job->total_balance;
                }
                
            } elseif ($credit->reference_type === 'purchase_order') {
                // Check if purchase order has remaining balance > 0
                $po = $this->db->select('total_amount, total_paid')
                    ->where('po_id', $credit->po_id)
                    ->get('purchase_orders')
                    ->row();
                
                if ($po && ($po->total_amount - $po->total_paid) > 0) {
                    $isOngoing = true;
                    $credit->total_paid_amount = $po->total_paid;
                    $credit->remaining_amount = $po->total_amount - $po->total_paid;
                }
            }

            if ($isOngoing) {
                $ongoingCredits[] = $credit;
            }
        }

        $transactions = $ongoingCredits;

        // Load all customers and suppliers for filter dropdowns
        $customers = $this->db->select('customers_id, name')->order_by('name', 'ASC')->get('customers')->result();
        $suppliers = $this->db->select('supplier_id, name')->order_by('name', 'ASC')->get('suppliers')->result();

        return [
            'mainMenuName' => $menuTitle,
            'subMenuName' => $slug,
            'account' => $account,
            'transactions' => $transactions,
            'customers' => $customers,
            'suppliers' => $suppliers,
            'filters' => [
                'customer_id' => $customerId,
                'supplier_id' => $supplierId,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'txn_type' => $txnType,
            ]
        ];
    }

    public function processCreditPayment()
    {
        $this->require_permission('accounts.credits');
        if ($this->input->method() !== 'post') {
            $this->output->set_content_type('application/json')->set_output(json_encode(['status' => 'error', 'message' => 'Invalid method']));
            return;
        }

        $txnId = $this->input->post('txn_id');
        $paymentMethod = $this->input->post('payment_method');
        $amount = floatval($this->input->post('amount'));
        $cardOrChequeNo = $this->input->post('card_or_cheque_no');
        $chequeDate = $this->input->post('cheque_date');
        $bankName = $this->input->post('bank_name');
        $note = $this->input->post('note');
        $transactionDate = $this->input->post('transaction_date') ?: date('Y-m-d');

        // Validate required fields
        if (!$txnId || !$paymentMethod || $amount <= 0) {
            $this->output->set_content_type('application/json')->set_output(json_encode(['status' => 'error', 'message' => 'Missing required fields']));
            return;
        }

        // Get the original credit transaction
        $creditTxn = $this->db->get_where('account_transactions', ['txn_id' => $txnId, 'account_slug' => 'credits'])->row();
        if (!$creditTxn) {
            $this->output->set_content_type('application/json')->set_output(json_encode(['status' => 'error', 'message' => 'Credit transaction not found']));
            return;
        }

        // Check if payment amount exceeds credit amount
        if ($amount > $creditTxn->amount) {
            $this->output->set_content_type('application/json')->set_output(json_encode(['status' => 'error', 'message' => 'Payment amount cannot exceed credit amount']));
            return;
        }

        $this->db->trans_start();
        try {
            // Process the payment through wallet model
            $paymentData = [
                'payment_method' => $paymentMethod,
                'amount' => $amount,
                'description' => $this->generateCreditPaymentDescription($creditTxn, $amount, $paymentMethod, $cardOrChequeNo, $bankName),
                'reference_type' => 'credit_payment',
                'reference_id' => $txnId,
                'customer_id' => $creditTxn->customer_id,
                'created_by' => $this->session->userdata('userid'),
                'card_or_cheque_no' => $cardOrChequeNo,
                'cheque_date' => $chequeDate,
                'bank_name' => $bankName,
                'note' => $note
            ];

            $result = $this->wallet_model->processPayment($paymentData);
            
            if ($result['status'] !== 'success') {
                throw new Exception($result['message']);
            }

            // Update the original credit transaction with payment amount
            $remainingAmount = $creditTxn->amount - $amount;

            if ($remainingAmount <= 0) {
                // If fully paid, keep the transaction for history but set amount to 0
                $this->db->set('amount', 0);
                $this->db->set('updated_at', date('Y-m-d H:i:s'));
                $this->db->where('txn_id', $txnId);
                $this->db->update('account_transactions');
            } else {
                // Update with remaining amount
                $this->db->set('amount', $remainingAmount);
                $this->db->set('updated_at', date('Y-m-d H:i:s'));
                $this->db->where('txn_id', $txnId);
                $this->db->update('account_transactions');
            }

            // Update the credits account balance (reduce by payment amount)
            $this->db->set('balance', 'balance - ' . $amount, FALSE);
            $this->db->where('slug', 'credits');
            $this->db->update('accounts');

            // Update the related bill or purchase order
            $this->updateRelatedBillOrOrder($creditTxn, $amount, $transactionDate);

            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                throw new Exception('Transaction failed');
            }

            $message = $remainingAmount <= 0 ? 
                'Credit payment processed successfully - Credit fully paid!' : 
                'Credit payment processed successfully - Remaining balance: LKR ' . number_format($remainingAmount, 2);

            $this->output->set_content_type('application/json')->set_output(json_encode([
                'status' => 'success',
                'message' => $message,
                'remaining_amount' => $remainingAmount,
                'fully_paid' => $remainingAmount <= 0
            ]));

        } catch (Exception $e) {
            $this->db->trans_rollback();
            $this->output->set_content_type('application/json')->set_output(json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]));
        }
    }

    private function generateCreditPaymentDescription($creditTxn, $amount, $paymentMethod, $cardOrChequeNo, $bankName)
    {
        $method = ucfirst($paymentMethod);
        $amountFormatted = number_format($amount, 2);
        
        $description = "Credit payment of LKR {$amountFormatted} via {$method}";
        
        if ($creditTxn->reference_type === 'quick_bill') {
            $description .= " for Quick Bill #{$creditTxn->reference_id}";
        } elseif ($creditTxn->reference_type === 'purchase_order') {
            $description .= " for Purchase Order #{$creditTxn->reference_id}";
        }

        if ($cardOrChequeNo) {
            $description .= " (Ref: {$cardOrChequeNo})";
        }
        
        if ($bankName) {
            $description .= " - {$bankName}";
        }

        return $description;
    }

    private function updateRelatedBillOrOrder($creditTxn, $paymentAmount, $transactionDate = null)
    {
        if (!$transactionDate) {
            $transactionDate = date('Y-m-d');
        }
        if ($creditTxn->reference_type === 'quick_bill') {
            // Update quick bill with payment - now simple since credit is not counted as paid
            $this->db->set('total_paid', 'total_paid + ' . $paymentAmount, FALSE);
            $this->db->set('total_balance', 'total_balance - ' . $paymentAmount, FALSE);
            $this->db->set('updated_at', date('Y-m-d H:i:s'));
            $this->db->where('quick_bill_id', $creditTxn->reference_id);
            $this->db->update('quick_bill');

            // Add payment record to quick_bill_payments
            $paymentRecord = [
                'quick_bill_id' => $creditTxn->reference_id,
                'payment_method' => $this->input->post('payment_method'),
                'paid_amount' => $paymentAmount,
                'card_or_cheque_no' => $this->input->post('card_or_cheque_no'),
                'cheque_date' => $this->input->post('cheque_date'),
                'bank_name' => $this->input->post('bank_name'),
                'note' => $this->input->post('note'),
                'payment_date' => $transactionDate . ' ' . date('H:i:s'),
                'created_by' => $this->session->userdata('userid')
            ];
            $this->db->insert('quick_bill_payments', $paymentRecord);

        } elseif ($creditTxn->reference_type === 'service_job') {
            // Update service job with payment
            $this->db->set('total_paid', 'total_paid + ' . $paymentAmount, FALSE);
            $this->db->set('total_balance', 'total_balance - ' . $paymentAmount, FALSE);
            $this->db->set('updated_at', date('Y-m-d H:i:s'));
            $this->db->where('job_id', $creditTxn->reference_id);
            $this->db->update('services_job');

            // Add payment record to services_job_payments
            $paymentRecord = [
                'service_job_id' => $creditTxn->reference_id,
                'payment_method' => $this->input->post('payment_method'),
                'paid_amount' => $paymentAmount,
                'cheque_number' => $this->input->post('card_or_cheque_no'),
                'cheque_date' => $this->input->post('cheque_date'),
                'bank_name' => $this->input->post('bank_name'),
                'note' => $this->input->post('note'),
                'payment_date' => $transactionDate . ' ' . date('H:i:s'),
                'created_by' => $this->session->userdata('userid')
            ];
            $this->db->insert('services_job_payments', $paymentRecord);

        } elseif ($creditTxn->reference_type === 'purchase_order') {
            // Update purchase order with payment
            $this->db->set('total_paid', 'total_paid + ' . $paymentAmount, FALSE);
            $this->db->set('updated_at', date('Y-m-d H:i:s'));
            $this->db->where('po_id', $creditTxn->reference_id);
            $this->db->update('purchase_orders');

            // Add payment record to purchase_order_payments
            $paymentRecord = [
                'po_id' => $creditTxn->reference_id,
                'payment_method' => $this->input->post('payment_method'),
                'paid_amount' => $paymentAmount,
                'card_or_cheque_no' => $this->input->post('card_or_cheque_no'),
                'cheque_date' => $this->input->post('cheque_date'),
                'bank_name' => $this->input->post('bank_name'),
                'note' => $this->input->post('note'),
                'payment_date' => $transactionDate . ' ' . date('H:i:s'),
                'created_by' => $this->session->userdata('userid')
            ];
            $this->db->insert('purchase_order_payments', $paymentRecord);
        }
    }

    public function recalc_running_balances()
    {
        $this->require_permission('accounts.credits');
        $slug = $this->input->get('slug');

        if ($slug) {
            $result = $this->wallet_model->recalculateRunningBalancesForAccount($slug);
        } else {
            $result = $this->wallet_model->recalculateRunningBalancesForAll();
        }

        // If request is ajax/json
        if ($this->input->is_ajax_request()) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'success', 'result' => $result]));
            return;
        }

        echo '<pre>' . htmlspecialchars(print_r($result, true)) . '</pre>';
    }

    public function credit_history()
    {
        $this->require_permission('accounts.credits');
        $data = $this->loadCreditPaymentHistory('Credit Payment History');
        
        $this->load->view('layout/header');
        $this->load->view('layout/top_navbar');
        $this->load->view('layout/left_sidebar', $data);
        $this->load->view('accounts/credit_history_view', $data);
        $this->load->view('layout/footer');
    }

    private function loadCreditPaymentHistory($menuTitle)
    {
        // Get filter parameters
        $customerId = $this->input->get('customer_id');
        $supplierId = $this->input->get('supplier_id');
        $dateFrom = $this->input->get('date_from');
        $dateTo = $this->input->get('date_to');
        $paymentMethod = $this->input->get('payment_method');

        // Load credit transactions that have been fully paid
        // We'll check the payment tables to see which credits have been fully paid
        $this->db->select('
            at.txn_id,
            at.amount as credit_amount,
            at.description,
            at.created_at as credit_date,
            at.customer_id,
            c.name as customer_name,
            qb.quick_bill_id,
            qb.bill_amount as total_bill_amount,
            qb.credit_person_name,
            qb.credit_person_phone,
            po.po_id,
            po.bill_no as po_bill_no,
            po.total_amount as po_total_amount,
            po.credit_person_name as po_credit_person_name,
            po.credit_person_phone as po_credit_person_phone,
            s.name as supplier_name,
            sj.job_id,
            sj.final_bill_amount as job_total_amount,
            sj.credit_person as job_credit_person,
            sj.credit_remark as job_credit_remark,
            at.reference_type
        ');
        $this->db->from('account_transactions at');
        $this->db->join('customers c', 'c.customers_id = at.customer_id', 'left');
        $this->db->join('quick_bill qb', 'qb.quick_bill_id = at.reference_id AND at.reference_type = "quick_bill"', 'left');
        $this->db->join('purchase_orders po', 'po.po_id = at.reference_id AND at.reference_type = "purchase_order"', 'left');
        $this->db->join('suppliers s', 's.supplier_id = po.supplier_id', 'left');
        $this->db->join('services_job sj', 'sj.job_id = at.reference_id AND at.reference_type = "service_job"', 'left');
        $this->db->where('at.account_slug', 'credits');
        $this->db->where('at.txn_type', 'credit');
        
        // Apply filters
        if ($customerId) {
            $this->db->where('at.customer_id', $customerId);
        }
        if ($supplierId) {
            $this->db->where('po.supplier_id', $supplierId);
        }
        if ($dateFrom) {
            $this->db->where('DATE(at.created_at) >=', $dateFrom);
        }
        if ($dateTo) {
            $this->db->where('DATE(at.created_at) <=', $dateTo);
        }
        
        $this->db->order_by('at.created_at', 'DESC');
        $creditTransactions = $this->db->get()->result();

        // Filter to show only fully paid credits by checking balance fields
        $fullyPaidCredits = [];
        foreach ($creditTransactions as $credit) {
            $isFullyPaid = false;
            $totalPaid = 0;
            $lastPaymentDate = null;

            // Check if fully paid based on reference type
            if ($credit->reference_type === 'quick_bill') {
                // Check if quick_bill has remaining balance <= 0
                $bill = $this->db->select('total_balance, total_paid')
                    ->where('quick_bill_id', $credit->quick_bill_id)
                    ->get('quick_bill')
                    ->row();
                
                if ($bill && $bill->total_balance <= 0) {
                    $isFullyPaid = true;
                    $totalPaid = $bill->total_paid;
                    
                    // Get last payment date
                    $lastPayment = $this->db->select('payment_date')
                        ->where('quick_bill_id', $credit->quick_bill_id)
                        ->order_by('payment_date', 'DESC')
                        ->get('quick_bill_payments')
                        ->row();
                    $lastPaymentDate = $lastPayment ? $lastPayment->payment_date : null;
                }
                
            } elseif ($credit->reference_type === 'service_job') {
                // Check if service job has remaining balance <= 0
                $job = $this->db->select('total_balance, total_paid')
                    ->where('job_id', $credit->job_id)
                    ->get('services_job')
                    ->row();
                
                if ($job && $job->total_balance <= 0) {
                    $isFullyPaid = true;
                    $totalPaid = $job->total_paid;
                    
                    // Get last payment date
                    $lastPayment = $this->db->select('payment_date')
                        ->where('service_job_id', $credit->job_id)
                        ->order_by('payment_date', 'DESC')
                        ->get('services_job_payments')
                        ->row();
                    $lastPaymentDate = $lastPayment ? $lastPayment->payment_date : null;
                }
                
            } elseif ($credit->reference_type === 'purchase_order') {
                // Check if purchase order has remaining balance <= 0
                $po = $this->db->select('total_amount, total_paid')
                    ->where('po_id', $credit->po_id)
                    ->get('purchase_orders')
                    ->row();
                
                if ($po && ($po->total_amount - $po->total_paid) <= 0) {
                    $isFullyPaid = true;
                    $totalPaid = $po->total_paid;
                    
                    // Get last payment date
                    $lastPayment = $this->db->select('payment_date')
                        ->where('po_id', $credit->po_id)
                        ->order_by('payment_date', 'DESC')
                        ->get('purchase_order_payments')
                        ->row();
                    $lastPaymentDate = $lastPayment ? $lastPayment->payment_date : null;
                }
            }

            if ($isFullyPaid) {
                $credit->total_paid_amount = $totalPaid;
                $credit->last_payment_date = $lastPaymentDate;
                $fullyPaidCredits[] = $credit;
            }
        }

        $creditTransactions = $fullyPaidCredits;

        // Create payment records for display
        $allPayments = [];
        foreach ($creditTransactions as $credit) {
            // Determine the appropriate fields based on reference type
            $totalBillAmount = 0;
            $creditPersonName = '';
            $creditPersonPhone = '';
            $referenceId = 0;
            
            if ($credit->reference_type === 'quick_bill') {
                $totalBillAmount = $credit->total_bill_amount;
                $creditPersonName = $credit->credit_person_name;
                $creditPersonPhone = $credit->credit_person_phone;
                $referenceId = $credit->quick_bill_id;
            } elseif ($credit->reference_type === 'service_job') {
                $totalBillAmount = $credit->job_total_amount;
                $creditPersonName = $credit->job_credit_person;
                $creditPersonPhone = ''; // Job invoices don't have phone field
                $referenceId = $credit->job_id;
            } elseif ($credit->reference_type === 'purchase_order') {
                $totalBillAmount = $credit->po_total_amount;
                $creditPersonName = $credit->po_credit_person_name;
                $creditPersonPhone = $credit->po_credit_person_phone;
                $referenceId = $credit->po_id;
            }
            
            // Create a payment record for display
            $allPayments[] = (object) [
                'payment_id' => $credit->txn_id,
                'payment_method' => 'credit',
                'paid_amount' => $credit->credit_amount,
                'total_paid_amount' => $credit->total_paid_amount,
                'remaining_amount' => isset($credit->remaining_amount) ? $credit->remaining_amount : 0,
                'card_or_cheque_no' => '',
                'cheque_date' => '',
                'bank_name' => '',
                'note' => $credit->description,
                'payment_date' => $credit->last_payment_date ?: $credit->credit_date,
                'credit_date' => $credit->credit_date,
                'created_by' => 1,
                'quick_bill_id' => $credit->quick_bill_id ?? null,
                'job_id' => $credit->job_id ?? null,
                'po_id' => $credit->po_id ?? null,
                'total_bill_amount' => $totalBillAmount,
                'credit_person_name' => $creditPersonName,
                'credit_person_phone' => $creditPersonPhone,
                'customer_name' => $credit->customer_name,
                'supplier_name' => $credit->supplier_name ?? '',
                'reference_type' => $credit->reference_type
            ];
        }

        // Augment history: include fully paid Quick Bills that had an initial credit recorded in payments table
        // This covers cases where the original account_transactions credit row was deleted earlier
        $this->db->from('quick_bill qb');
        $this->db->join('quick_bill_payments qbp', 'qbp.quick_bill_id = qb.quick_bill_id', 'inner');
        $this->db->where('qb.total_balance <=', 0);
        $this->db->where('qbp.payment_method', 'Credit');
        if ($customerId) {
            $this->db->where('qb.customer_id', $customerId);
        }
        if ($dateFrom) {
            $this->db->where('DATE(qb.created_at) >=', $dateFrom);
        }
        if ($dateTo) {
            $this->db->where('DATE(qb.created_at) <=', $dateTo);
        }
        $qbCredits = $this->db->select('qb.quick_bill_id, qb.bill_amount, qb.total_paid, qb.total_balance, qb.created_at, c.name as customer_name, qb.credit_person_name, qb.credit_person_phone')
            ->join('customers c', 'c.customers_id = qb.customer_id', 'left')
            ->group_by('qb.quick_bill_id')
            ->get()
            ->result();

        // Build a set of existing reference ids to avoid duplicates
        $existingQbIds = [];
        foreach ($allPayments as $p) {
            if ($p->reference_type === 'quick_bill' && !empty($p->quick_bill_id)) {
                $existingQbIds[(int)$p->quick_bill_id] = true;
            }
        }

        foreach ($qbCredits as $qb) {
            if (isset($existingQbIds[(int)$qb->quick_bill_id])) {
                continue; // already included from account_transactions
            }

            // Find last payment date from payments table
            $lastPayment = $this->db->select('payment_date')
                ->where('quick_bill_id', $qb->quick_bill_id)
                ->order_by('payment_date', 'DESC')
                ->get('quick_bill_payments')
                ->row();

            $allPayments[] = (object) [
                'payment_id' => $qb->quick_bill_id, // use ref id as stable identifier
                'payment_method' => 'credit',
                'paid_amount' => $qb->bill_amount, // initial credit equals bill amount captured at time of credit
                'total_paid_amount' => $qb->total_paid,
                'remaining_amount' => 0,
                'card_or_cheque_no' => '',
                'cheque_date' => '',
                'bank_name' => '',
                'note' => 'Credit cleared',
                'payment_date' => $lastPayment ? $lastPayment->payment_date : $qb->created_at,
                'credit_date' => $qb->created_at,
                'created_by' => 1,
                'quick_bill_id' => $qb->quick_bill_id,
                'job_id' => null,
                'po_id' => null,
                'total_bill_amount' => $qb->bill_amount,
                'credit_person_name' => $qb->credit_person_name,
                'credit_person_phone' => $qb->credit_person_phone,
                'customer_name' => $qb->customer_name,
                'supplier_name' => '',
                'reference_type' => 'quick_bill'
            ];
        }

        // Load all customers and suppliers for filter dropdowns
        $customers = $this->db->select('customers_id, name')->order_by('name', 'ASC')->get('customers')->result();
        $suppliers = $this->db->select('supplier_id, name')->order_by('name', 'ASC')->get('suppliers')->result();

        return [
            'mainMenuName' => $menuTitle,
            'subMenuName' => 'credit_history',
            'payments' => $allPayments,
            'customers' => $customers,
            'suppliers' => $suppliers,
            'filters' => [
                'customer_id' => $customerId,
                'supplier_id' => $supplierId,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'payment_method' => $paymentMethod,
            ]
        ];
    }
}


