<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Expenses_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    public function saveExpense($data)
    {
        $result = $this->db->insert('expenses', $data);
        if ($result) {
            return $this->db->insert_id();
        }
        return false;
    }

    public function loadExpensesCategory($id = null)
    {
        $this->db->select('*');
        $this->db->from('expenses_categories');
        $this->db->where('isDeleted', 0);
        if ($id > 0) {
            $this->db->where('expensesCategoryId', $id);
        }
        $this->db->order_by('expensesCategoryName', 'ASC');
        $query = $this->db->get();
        return $query->result();
    }

    public function loadUsers($UserID = null)
    {
        $this->db->select('*');
        $this->db->from('users');
        $this->db->where('IsActive', 1);
        if ($UserID > 0) {
            $this->db->where('UserID', $UserID);
        }
        $this->db->order_by('FirstName', 'ASC');
        $query = $this->db->get();
        return $query->result();
    }

    public function loadSection($employeeSectionId = null)
    {
        $this->db->select('*');
        $this->db->from('employee_sections');
        $this->db->where('isDeleted', 0);
        if ($employeeSectionId > 0) {
            $this->db->where('employeeSectionId', $employeeSectionId);
        }
        $this->db->order_by('employeeSectionName', 'ASC');
        $query = $this->db->get();
        return $query->result();
    }

    public function loadExpenses($sdate, $edate, $category_id, $employeeId = null)
{
    $this->db->select('
        expenses.*,
        expenses_categories.expensesCategoryName AS category_name,
        created_by_user.UserName AS UserName,
        created_by_user.LastName AS created_by_lname,
        employee_user.FirstName AS employee_fname,
        employee_user.LastName AS employee_lname,
        member_user.FirstName AS member_fname,
        member_user.LastName AS member_lname,
        section.employeeSectionName AS section_name
    ');
    $this->db->from('expenses');
    $this->db->join('expenses_categories', 'expenses_categories.expensesCategoryId = expenses.category_id', 'left');
    $this->db->join('users AS created_by_user', 'created_by_user.UserID = expenses.created_by', 'left');
    $this->db->join('users AS employee_user', 'employee_user.UserID = expenses.employee_id', 'left');
    $this->db->join('users AS member_user', 'member_user.UserID = expenses.member_id', 'left');
    $this->db->join('employee_sections AS section', 'section.employeeSectionId = expenses.section_id', 'left');
    $this->db->where('expense_date >=', $sdate);
    $this->db->where('expense_date <=', $edate);
    if ($category_id > 0) {
        $this->db->where('expenses.category_id', $category_id);
    }
    if (!empty($employeeId)) {
        $this->db->where('expenses.employee_id', $employeeId);
    }
    $this->db->order_by('expenses.id', 'DESC');
    $query = $this->db->get();
    return $query->result();
}

public function getById($id)
{
    return $this->db->get_where('expenses', ['id' => $id])->row();
}

public function deleteExpense($id)
{
    return $this->db->delete('expenses', ['id' => $id]);
}

public function getNextVoucherNumber()
{
    // Get the highest voucher number that is numeric
    $this->db->select('MAX(CAST(reference_no AS UNSIGNED)) as max_voucher');
    $this->db->from('expenses');
    $this->db->where('reference_no REGEXP', '^[0-9]+$');
    $query = $this->db->get();
    $result = $query->row();
    
    $nextVoucher = 100; // Start from 100
    if ($result && $result->max_voucher) {
        $nextVoucher = $result->max_voucher + 1;
    }
    
    return $nextVoucher;
}

public function loadMembers($memberId = null)
{
    $this->db->select('*');
    $this->db->from('users');
    $this->db->where('IsActive', 1);
    if ($memberId > 0) {
        $this->db->where('UserID', $memberId);
    }
    $this->db->order_by('FirstName', 'ASC');
    $query = $this->db->get();
    return $query->result();
}


}
