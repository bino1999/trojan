<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class VehicleCategory extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('settings_model');
    }

    public function manageVehicleCategory()
    {
        $this->require_permission('settings.vehiclecategories');
        $data['mainMenuName'] = 'settings';
        $data['subMenuName'] = 'vehicle-category-manage'; 

        $data['results'] = $this->settings_model->loadVehicleCategory();    
        $this->load->view('layout/header');
        $this->load->view('layout/top_navbar'); 
        $this->load->view('layout/left_sidebar', $data);
        $this->load->view('masters/vehicle-category-manage');
        $this->load->view('layout/footer');
    }

    public function saveVehicleCategory()
{
    $this->require_permission('settings.vehiclecategories');
    $this->load->library('form_validation');
    $this->form_validation->set_rules('name', 'Category Name', 'required|trim');

    if ($this->form_validation->run() == FALSE) {
        echo json_encode(['status' => 'error', 'message' => validation_errors()]);
    } else {
        $name = strtoupper($this->input->post('name', true));
        $exists = $this->settings_model->checkVehicleCategoryExists($name);

        if ($exists) {
            echo json_encode(['status' => 'error', 'message' => 'Record already exists!']);
        } else {
            $this->settings_model->saveVehicleCategory($name);
            echo json_encode(['status' => 'success', 'message' => 'New category added successfully!']);
        }
    }
}


public function updateVehicleCategory() {
    $this->require_permission('settings.vehiclecategories');
    // Load form validation
    $this->load->library('form_validation');
    $this->form_validation->set_rules(
        'name',
        'Category Name',
        'required|trim|regex_match[/^[a-zA-Z0-9 ]+$/]',
        array('regex_match' => 'The %s field may only contain letters, numbers, and spaces.')
    );
    

    if ($this->form_validation->run() == FALSE) {
        // Validation failed
        echo json_encode(['status' => 'error', 'message' => validation_errors()]);
    } else {
        
        $recordId = $this->input->post('id');
        $name = strtoupper($this->input->post('name'));  // Convert to upper case

        $result = $this->settings_model->checkVehicleCategoryExists($name);

        if ($result && $result->vehicleCategoryId  != $recordId) {
            //name already exists
            echo json_encode(['status' => 'error', 'message' => 'Record already exists!']);
        } else {
            // Update in the database
            $data = [
                'vehicleCategoryName' => $name,
                'updatedAt' => date('Y-m-d H:i:s')
            ];
            $this->db->where('vehicleCategoryId', $recordId);
            $this->db->update('vehicle_categories', $data);

            echo json_encode(['status' => 'success', 'message' => 'Vehicle category updated successfully!', 'data' => $data]);
        }
    }
}

public function deleteVehicleCategory() {
    $this->require_permission('settings.vehiclecategories');
    $recordId = $this->input->post('recordId');
    $result = $this->settings_model->loadVehicleCategory($recordId);  
    
    if ($result) {
        $this->db->where('vehicleCategoryId', $recordId);
        $this->db->update('vehicle_categories', ['isDeleted' => 1]);
        echo json_encode(['status' => 'success', 'message' => 'Record deleted successfully!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Record not found.']);
    }  

}

public function undoDeleteVehicleCategory() {
    $this->require_permission('settings.vehiclecategories');
    $recordId = $this->input->post('recordId');
    $result = $this->settings_model->loadVehicleCategory($recordId);  
    
    if ($result) {
        $this->db->where('vehicleCategoryId', $recordId);
        $this->db->update('vehicle_categories', ['isDeleted' => 0]);
        echo json_encode(['status' => 'success', 'message' => 'Record activated successfully!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Record not found.']);
    }  

}


}
?>