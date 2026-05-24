<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Payments extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('payment_model');
    }

    public function paymentManage()
    {
        $this->require_permission('payments.view');
        $data['mainMenuName'] = 'Payments';
        $data['subMenuName'] = 'Payments';

        $this->load->view('layout/header');
        $this->load->view('layout/top_navbar');
        $this->load->view('layout/left_sidebar', $data);
        $this->load->view('payment/payment-manage', $data);
        $this->load->view('layout/footer');
    }

    public function loadPayments()
    {
        $this->require_permission('payments.view');
        $sdate = $this->input->post('sdate');
        $edate = $this->input->post('edate');
        $paymentMethod = $this->input->post('paymentMethod');
        $receiptStatus = $this->input->post('receiptStatus');
        $typeOfPayment = $this->input->post('typeOfPayment');

// Check if POST value exists
if (!empty($typeOfPayment)) {
    // Save it to session
    $this->session->set_userdata('type_of_payment', $typeOfPayment);
} else {
    // If not posted, try to get it from session
    $typeOfPayment = $this->session->userdata('type_of_payment');

    // If not in session, default to 'job'
    if (empty($typeOfPayment)) {
        $typeOfPayment = 'job';
    }
}


        $data['paymentMethod'] = $paymentMethod;
        $data['receiptStatus'] = $receiptStatus;

        if($typeOfPayment == 'job') {
        $data['payments'] = $this->payment_model->loadPayments($sdate, $edate, $paymentMethod, $receiptStatus);
        $this->load->view('payment/payment-manage-list', $data);
        }else if($typeOfPayment == 'bill') {
        $data['payments'] = $this->payment_model->loadBillPayments($sdate, $edate, $paymentMethod, $receiptStatus);
        $this->load->view('payment/quick-bill-payment-manage-list', $data);
        }else {
            echo '<div class="alert alert-danger">Invalid payment type specified.</div>';
        }
    }

    public function returnCheque()
    {
        $this->require_permission('payments.edit');
        $cheque_id = $this->input->post('cheque_id');
        $reason = $this->input->post('reason');

        if ($this->payment_model->returnCheque($cheque_id, $reason)) {
            echo json_encode(['status' => 'success', 'message' => 'Cheque returned successfully.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to return cheque.']);
        }
    }


}
