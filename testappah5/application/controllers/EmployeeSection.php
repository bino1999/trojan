<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class EmployeeSection extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('settings_model');
    }


    public function manageEmployeeSection()
    {
        $this->require_permission('settings.employeesections');
        $data['mainMenuName'] = 'settings';
        $data['subMenuName'] = 'employee-section-manage'; 

        $data['results'] = $this->settings_model->loadEmployeeSection();    
        $this->load->view('layout/header');
        $this->load->view('layout/top_navbar'); 
        $this->load->view('layout/left_sidebar', $data);
        $this->load->view('masters/employee-section-manage');
        $this->load->view('layout/footer');
    }

    public function saveEmployeeSection()
{
    $this->require_permission('settings.employeesections');
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
        $exists = $this->settings_model->getEmployeeSectionByName($name);

        if ($exists) {
            echo json_encode(['status' => 'error', 'message' => 'Record already exists!']);
        } else {
            $this->settings_model->saveEmployeeSection($name);
            echo json_encode(['status' => 'success', 'message' => 'New category added successfully!']);
        }
    }
}


public function updateEmployeeSection() {
    $this->require_permission('settings.employeesections');
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

        $result = $this->settings_model->getEmployeeSectionByName($name);

        if ($result && $result->vehicleCategoryId  != $recordId) {
            //name already exists
            echo json_encode(['status' => 'error', 'message' => 'Record already exists!']);
        } else {
            // Update in the database
            $data = [
                'employeeSectionName' => $name,
                'updatedAt' => date('Y-m-d H:i:s')
            ];
            $this->db->where('employeeSectionId', $recordId);
            $this->db->update('employee_sections', $data);

            echo json_encode(['status' => 'success', 'message' => 'Record updated successfully!', 'data' => $data]);
        }
    }
}

public function deleteEmployeeSection() {
    $this->require_permission('settings.employeesections');
    $recordId = $this->input->post('recordId');
    $result = $this->settings_model->loadEmployeeSection($recordId);  
    
    if ($result) {
        $this->db->where('employeeSectionId', $recordId);
        $this->db->update('employee_sections', ['isDeleted' => 1]);
        echo json_encode(['status' => 'success', 'message' => 'Record deleted successfully!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Record not found.']);
    }  

}

public function undoDeleteEmployeeSection() {
    $this->require_permission('settings.employeesections');
    $recordId = $this->input->post('recordId');
    $result = $this->settings_model->loadEmployeeSection($recordId);  
    
    if ($result) {
        $this->db->where('employeeSectionId', $recordId);
        $this->db->update('employee_sections', ['isDeleted' => 0]);
        echo json_encode(['status' => 'success', 'message' => 'Record activated successfully!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Record not found.']);
    }  

}


}
?>