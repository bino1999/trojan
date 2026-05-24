<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ExpensesCategory extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('settings_model');
    }


    public function manageExpensesCategory()
    {
        $this->require_permission('settings.expensecategories');
        $data['mainMenuName'] = 'settings';
        $data['subMenuName'] = 'expenses-category-manage'; 

        $data['results'] = $this->settings_model->loadExpensesCategory();    
        $this->load->view('layout/header');
        $this->load->view('layout/top_navbar'); 
        $this->load->view('layout/left_sidebar', $data);
        $this->load->view('masters/expenses-category-manage');
        $this->load->view('layout/footer');
    }

    public function saveExpensesCategory()
{
    $this->require_permission('settings.expensecategories');
    $this->load->library('form_validation');
    $this->form_validation->set_rules(
        'name',
        'Name',
        'required|trim|regex_match[/^[a-zA-Z0-9 ]+$/]',
        array('regex_match' => 'The %s field may only contain letters, numbers, and spaces.')
    );

    if ($this->form_validation->run() == FALSE) {
        echo json_encode(['status' => 'error', 'message' => validation_errors()]);
    } else {
        $name = strtoupper($this->input->post('name', true));
        $exists = $this->settings_model->getExpensesCategoryByName($name);

        if ($exists) {
            echo json_encode(['status' => 'error', 'message' => 'Record already exists!']);
        } else {
            $this->settings_model->saveExpensesCategory($name);
            echo json_encode(['status' => 'success', 'message' => 'New category added successfully!']);
        }
    }
}


public function updateExpensesCategory() {
    $this->require_permission('settings.expensecategories');
    // Load form validation
    $this->load->library('form_validation');
    $this->form_validation->set_rules(
        'name',
        'Name',
        'required|trim|regex_match[/^[a-zA-Z0-9 ]+$/]',
        array('regex_match' => 'The %s field may only contain letters, numbers, and spaces.')
    );
    

    if ($this->form_validation->run() == FALSE) {
        // Validation failed
        echo json_encode(['status' => 'error', 'message' => validation_errors()]);
    } else {
        
        $recordId = $this->input->post('id');
        $name = strtoupper($this->input->post('name'));  // Convert to upper case

        $result = $this->settings_model->getExpensesCategoryByName($name);

        if ($result && $result->vehicleCategoryId  != $recordId) {
            //name already exists
            echo json_encode(['status' => 'error', 'message' => 'Record already exists!']);
        } else {
            // Update in the database
            $data = [
                'ExpensesCategoryName' => $name,
                'updatedAt' => date('Y-m-d H:i:s')
            ];
            $this->db->where('ExpensesCategoryId', $recordId);
            $this->db->update('expenses_categories', $data);

            echo json_encode(['status' => 'success', 'message' => 'Record updated successfully!', 'data' => $data]);
        }
    }
}

public function deleteExpensesCategory() {
    $this->require_permission('settings.expensecategories');
    $recordId = $this->input->post('recordId');
    $result = $this->settings_model->loadExpensesCategory($recordId);  
    
    if ($result) {
        $this->db->where('ExpensesCategoryId', $recordId);
        $this->db->update('expenses_categories', ['isDeleted' => 1]);
        echo json_encode(['status' => 'success', 'message' => 'Record deleted successfully!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Record not found.']);
    }  

}

public function undoDeleteExpensesCategory() {
    $this->require_permission('settings.expensecategories');
    $recordId = $this->input->post('recordId');
    $result = $this->settings_model->loadExpensesCategory($recordId);  
    
    if ($result) {
        $this->db->where('ExpensesCategoryId', $recordId);
        $this->db->update('expenses_categories', ['isDeleted' => 0]);
        echo json_encode(['status' => 'success', 'message' => 'Record activated successfully!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Record not found.']);
    }  

}


}
?>