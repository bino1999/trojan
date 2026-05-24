<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Payment_model extends CI_Model
{

    public function loadJobPayment($job_id) {
    $this->db->select('services_job_payments.*, users.UserName');
    $this->db->from('services_job_payments');
    $this->db->join('users', 'users.UserID = services_job_payments.created_by', 'left');
    $this->db->where('deleted_by', null);
    $this->db->where('service_job_id', $job_id);
    $this->db->order_by('id', 'DESC');    
    $query = $this->db->get();
    return $query->result(); 
}

public function loadPayments($sdate=null, $edate=null, $paymentMethod=null, $receiptStatus=null) {
    $this->db->select('services_job_payments.*, users.UserName');
    $this->db->from('services_job_payments');
    $this->db->join('users', 'users.UserID = services_job_payments.created_by', 'left');

    if($paymentMethod){
        $this->db->where('payment_method', $paymentMethod);
    }

    if($receiptStatus=='deleted'){
        $this->db->where('deleted_by >', 0);
    }

    if($receiptStatus=='active'){ //deleted_by null or deleted_by=0
        $this->db->where('deleted_by', null);
        $this->db->or_where('deleted_by', 0);
    }


    //$this->db->where('deleted_by', null);
    if($sdate && $edate){
        $this->db->where('DATE(created_at) >=', $sdate);
        $this->db->where('DATE(created_at) <=', $edate);
    }
    $this->db->order_by('id', 'DESC');    
    $query = $this->db->get();
    return $query->result(); 
}

public function loadBillPayments($sdate=null, $edate=null, $paymentMethod=null, $receiptStatus=null) {
    $this->db->select('quick_bill_payments.*, users.UserName');
    $this->db->from('quick_bill_payments');
    $this->db->join('users', 'users.UserID = quick_bill_payments.created_by', 'left');

    if($paymentMethod){
        $this->db->where('payment_method', $paymentMethod);
    }

    if($receiptStatus=='deleted'){
        $this->db->where('deleted_by >', 0);
    }

    if($receiptStatus=='active'){ //deleted_by null or deleted_by=0
        $this->db->where('deleted_by', null);
        $this->db->or_where('deleted_by', 0);
    }


    //$this->db->where('deleted_by', null);
    if($sdate && $edate){
        $this->db->where('DATE(created_at) >=', $sdate);
        $this->db->where('DATE(created_at) <=', $edate);
    }
    $this->db->order_by('id', 'DESC');    
    $query = $this->db->get();
    return $query->result(); 
}

public function returnCheque($cheque_id, $reason) {
    $data = array(
        'cheque_return_by' => $this->session->userdata('userid'),
        'cheque_return_note' => $reason,
        'cheque_return_at' => date('Y-m-d H:i:s')
    );

    $this->db->where('id', $cheque_id);
    return $this->db->update('services_job_payments', $data);
}


}