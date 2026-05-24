<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class VehicleBrand extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('settings_model');
    }


    public function manageVehicleBrand()
    {
        $this->require_permission('settings.vehiclebrands');
        $data['mainMenuName'] = 'settings';
        $data['subMenuName'] = 'vehicle-brand-manage'; 

        $data['results'] = $this->settings_model->loadVehicleBrand();    
        $this->load->view('layout/header');
        $this->load->view('layout/top_navbar'); 
        $this->load->view('layout/left_sidebar', $data);
        $this->load->view('masters/vehicle-brand-manage');
        $this->load->view('layout/footer');
    }

    public function saveVehicleBrand()
{
    $this->require_permission('settings.vehiclebrands');
    $this->load->library('form_validation');
    $this->form_validation->set_rules('name', 'Category Name', 'required|trim');

    if ($this->form_validation->run() == FALSE) {
        echo json_encode(['status' => 'error', 'message' => validation_errors()]);
    } else {
        $name = strtoupper($this->input->post('name', true));
        $exists = $this->settings_model->getVehicleBrandByName($name);

        if ($exists) {
            echo json_encode(['status' => 'error', 'message' => 'Record already exists!']);
        } else {
            $this->settings_model->saveVehicleBrand($name);
            echo json_encode(['status' => 'success', 'message' => 'New category added successfully!']);
        }
    }
}


public function updateVehicleBrand() {
    $this->require_permission('settings.vehiclebrands');
    // Load form validation
    $this->load->library('form_validation');
    $this->form_validation->set_rules(
        'name',
        'Brand Name',
        'required|trim|regex_match[/^[a-zA-Z0-9 ]+$/]',
        array('regex_match' => 'The %s field may only contain letters, numbers, and spaces.')
    );
    

    if ($this->form_validation->run() == FALSE) {
        // Validation failed
        echo json_encode(['status' => 'error', 'message' => validation_errors()]);
    } else {
        
        $recordId = $this->input->post('id');
        $name = strtoupper($this->input->post('name'));  // Convert to upper case

        $result = $this->settings_model->getVehicleBrandByName($name);

        if ($result && $result->vehicleCategoryId  != $recordId) {
            //name already exists
            echo json_encode(['status' => 'error', 'message' => 'Record already exists!']);
        } else {
            // Update in the database
            $data = [
                'vehicleBrandName' => $name,
                'updatedAt' => date('Y-m-d H:i:s')
            ];
            $this->db->where('vehicleBrandId', $recordId);
            $this->db->update('vehicle_brands', $data);

            echo json_encode(['status' => 'success', 'message' => 'Vehicle brand updated successfully!', 'data' => $data]);
        }
    }
}

public function deleteVehicleBrand() {
    $this->require_permission('settings.vehiclebrands');
    $recordId = $this->input->post('recordId');
    $result = $this->settings_model->loadVehicleBrand($recordId);  
    
    if ($result) {
        $this->db->where('vehicleBrandId', $recordId);
        $this->db->update('vehicle_brands', ['isDeleted' => 1]);
        echo json_encode(['status' => 'success', 'message' => 'Record deleted successfully!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Record not found.']);
    }  

}

public function undoDeleteVehicleBrand() {
    $this->require_permission('settings.vehiclebrands');
    $recordId = $this->input->post('recordId');
    $result = $this->settings_model->loadVehicleBrand($recordId);  
    
    if ($result) {
        $this->db->where('vehicleBrandId', $recordId);
        $this->db->update('vehicle_brands', ['isDeleted' => 0]);
        echo json_encode(['status' => 'success', 'message' => 'Record activated successfully!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Record not found.']);
    }  

}


}
?>