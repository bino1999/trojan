<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Expenses extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('expenses_model');
        $this->load->model('wallet_model');
    }

    public function expensesManage()
    {
        $this->require_permission('expenses.view');
        $data['mainMenuName'] = 'Expenses';
        $data['subMenuName'] = 'Expenses';

        $data['expenseCategories'] = $this->expenses_model->loadExpensesCategory();
        $data['users'] = $this->expenses_model->loadUsers();
        $data['sections'] = $this->expenses_model->loadSection();
        $data['members'] = $this->expenses_model->loadMembers();

        $this->load->view('layout/header');
        $this->load->view('layout/top_navbar');
        $this->load->view('layout/left_sidebar', $data);
        $this->load->view('expenses/expenses-manage', $data);
        $this->load->view('layout/footer');
    }

    public function getNextVoucherNumber()
    {
        $nextVoucher = $this->expenses_model->getNextVoucherNumber();
        echo json_encode(['voucher_number' => $nextVoucher]);
    }


    public function saveExpense()
{
    $this->require_permission('expenses.create');
    if ($this->input->method() === 'post') {

        $this->form_validation->set_rules('expense_category', 'Expense Category', 'required|trim');
        $this->form_validation->set_rules('expense_amount', 'Amount', 'required|numeric|trim');
        $this->form_validation->set_rules('expense_date', 'Date', 'required|trim');
        $this->form_validation->set_rules('payment_method', 'Payment Method', 'required|trim');
        $this->form_validation->set_rules('person_name', 'Person Name', 'required|trim');
        $this->form_validation->set_rules('person_phone', 'Person Phone', 'required|trim');

        $paymentMethod = $this->input->post('payment_method');

        if ($paymentMethod === 'cheque') {
            $this->form_validation->set_rules('cheque_date', 'Cheque Date', 'required|trim');
        }

        if ($this->form_validation->run() == FALSE) {
            echo json_encode([
                'status' => 'error',
                'message' => validation_errors()
            ]);
            return;
        }

        if ($paymentMethod === 'cheque') {
            $cheque_date = $this->input->post('cheque_date');
        }else{
            $cheque_date = null;
        }

        // Validate expense_date is today or within last 7 days
        $expenseDate = strtotime($this->input->post('expense_date'));
        $today = strtotime(date('Y-m-d'));
        $sevenDaysAgo = strtotime('-7 days');

        if ($expenseDate > $today || $expenseDate < $sevenDaysAgo) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Expense Date must be today or within the last 7 days.'
            ]);
            return;
        }

        // Auto-generate voucher number
        $voucherNumber = $this->expenses_model->getNextVoucherNumber();

        $data = [
            'category_id'     => $this->input->post('expense_category'),
            'amount'          => $this->input->post('expense_amount'),
            'expense_date'    => $this->input->post('expense_date'),
            'employee_id'     => $this->input->post('expense_user') ?: null,
            'member_id'       => null, // No longer used since we removed the member field
            'person_name'     => $this->input->post('person_name'),
            'person_phone'    => $this->input->post('person_phone'),
            'section_id'      => null, // No longer used
            'payment_method'  => $paymentMethod,
            'reference_no'    => $voucherNumber,
            'cheque_date'     => $cheque_date,
            'description'     => $this->input->post('description'),
            'created_by'      => $this->session->userdata('userid'),
            'created_at'      => date('Y-m-d H:i:s'),
            'updated_at'      => date('Y-m-d H:i:s'),
        ];

        $insertId = $this->expenses_model->saveExpense($data);

        if ($insertId) {
            // Map payment method to account slug via wallet model's private mapping by passing explicit slug here
            $method = strtolower($paymentMethod);
            $accountSlugMap = [
                'cash' => 'in_hand',
                'credit' => 'credits',
                'card' => 'card_machine',
                'cheque' => 'bank',
                'bank transfer' => 'bank',
                'bank_transfer' => 'bank'
            ];

            $accountSlug = isset($accountSlugMap[$method]) ? $accountSlugMap[$method] : null;

            if ($accountSlug) {
                $expenseAmount = floatval($this->input->post('expense_amount'));
                $referenceNo = $voucherNumber;
                $descBase = !empty($this->input->post('description')) ? $this->input->post('description') : 'Expense recorded';
                $description = $descBase . ' - Voucher #' . $referenceNo;

                $expenseProcessData = [
                    'account_slug' => $accountSlug,
                    'amount' => $expenseAmount,
                    'description' => $description,
                    'reference_type' => 'expense',
                    'reference_id' => $insertId,
                    'created_by' => $this->session->userdata('userid')
                ];

                $walletResult = $this->wallet_model->processExpense($expenseProcessData);

                if ($walletResult['status'] !== 'success') {
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'Expense saved but wallet update failed: ' . $walletResult['message']
                    ]);
                    return;
                }
            }

            echo json_encode([
                'status' => 'success',
                'message' => 'Expense saved successfully.'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to save expense.'
            ]);
        }
    }
}



    public function loadExpenses()
    {
        $this->require_permission('expenses.view');
        $sdate = $this->input->post('sdate');
        $edate = $this->input->post('edate');
        $expenseCategory = $this->input->post('expenseCategory');
        $employeeId = $this->input->post('employeeId');
        $data['expenses'] = $this->expenses_model->loadExpenses($sdate, $edate, $expenseCategory, $employeeId);
        
        $this->load->view('expenses/expense-list', $data);
    }

    public function delete()
{
    $this->require_permission('expenses.delete');
    $id = $this->input->post('id');
    
    if (!$id) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid Expense ID.']);
        return;
    }

    $expense = $this->expenses_model->getById($id);

    if (!$expense) {
        echo json_encode(['status' => 'error', 'message' => 'Expense not found.']);
        return;
    }

    $createdDate = date('Y-m-d', strtotime($expense->created_at));
    $today = date('Y-m-d');

    if ($createdDate !== $today) {
        echo json_encode(['status' => 'error', 'message' => 'Only today\'s records can be deleted.']);
        return;
    }

    $deleted = $this->expenses_model->deleteExpense($id);
    if ($deleted) {
        echo json_encode(['status' => 'success', 'message' => 'Expense deleted successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to delete expense.']);
    }
}





}
