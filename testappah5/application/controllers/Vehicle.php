<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Vehicle extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('general_model');
    }


    public function manageVehicle()
    {
        $this->require_permission('vehicles.view');
        $data['mainMenuName'] = 'Vehicle';
        $data['subMenuName'] = 'Vehicle';

        $data['vehicles'] = $this->general_model->loadVehicle();
        
        $this->load->view('layout/header');
        $this->load->view('layout/top_navbar');
        $this->load->view('layout/left_sidebar', $data);
        $this->load->view('general/vehicle-manage', $data);
        $this->load->view('layout/footer');
    }

    public function loadVehicles(){
        $this->require_permission('vehicles.view');

        $sdate = $this->input->post('sdate');
        $edate = $this->input->post('edate');

        $data['vehicles'] = $this->general_model->loadVehicleByDates($sdate, $edate);
        $this->load->view('general/vehicle-list', $data);
    }

    public function openVehicleCreateModal(){
        $this->require_permission('vehicles.create');
        $data['customers'] = $this->general_model->loadCustomers();
        $data['categories'] = $this->general_model->loadVehicleCategory();
        $data['brands'] = $this->general_model->loadVehicleBrand();
        $this->load->view('general/vehicle-create', $data);
    }

    public function saveVehicleDetails()
{
    $this->require_permission('vehicles.create');
    // Set custom error messages
    $this->form_validation->set_message('greater_than', '{field} selection required.');
    $this->form_validation->set_message('required', '{field} is required.');
    $this->form_validation->set_message('is_unique', 'This {field} is already registered.');
    
    // Custom validation for vehicle_no
    $this->form_validation->set_rules('vehicle_no', 'Vehicle No', 'required|is_unique[vehicles.vehicle_no]|callback_vehicle_no_validation');

    $this->form_validation->set_rules('owner_id', 'Owner', 'required|greater_than[0]');
    $this->form_validation->set_rules('color', 'Color', 'required');
    $this->form_validation->set_rules('category_id', 'Category', 'required|greater_than[0]');
    $this->form_validation->set_rules('brand_id', 'Brand', 'required|greater_than[0]');
    $this->form_validation->set_rules('model', 'Model', 'required');
    $this->form_validation->set_rules('service_mileage', 'Service Mileage', 'required|integer');
    $this->form_validation->set_rules('next_service_date', 'Next Service Date', 'required');

    if ($this->form_validation->run() == FALSE) {
        echo json_encode(['status' => 'error', 'message' => validation_errors()]);
        return;
    }

    // Get form data
    $vehicle_no = $this->input->post('vehicle_no');

    // Format vehicle_no: add space between letters and numbers, convert letters to uppercase
    $vehicle_no = strtoupper(preg_replace('/([A-Za-z]+)(\d+)/', '$1 $2', $vehicle_no));

    // Prepare data to be saved
    $data = [
        'owner_id'            => $this->input->post('owner_id'),
        'vehicle_no'          => $vehicle_no, // Use the formatted vehicle_no
        'chassis_no'          => $this->input->post('chassis_no'),
        'engine_no'           => $this->input->post('engine_no'),
        'color'               => $this->input->post('color'),
        'battery_no'          => $this->input->post('battery_no'),
        'year_of_manufacture' => $this->input->post('year_of_manufacture'),
        'category_id'         => $this->input->post('category_id'),
        'brand_id'            => $this->input->post('brand_id'),
        'model'               => $this->input->post('model'),
        'service_mileage'     => $this->input->post('service_mileage'),
        'next_service_date'   => $this->input->post('next_service_date'),
        'created_at'          => date('Y-m-d H:i:s'),
        'created_by'          => $this->session->userdata('userid')
    ];

    // Insert the data into the database
    if ($this->db->insert('vehicles', $data)) {
        echo json_encode(['status' => 'success', 'message' => 'Vehicle saved successfully!']);
        $this->load->model('logs_model');
        $this->logs_model->log_activity('Vehicle Create', 'Vehicle No: ' . $vehicle_no);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $this->db->error()['message']]);
    }
}

// Custom validation callback for vehicle_no
public function vehicle_no_validation($vehicle_no)
{
    // Check if the vehicle number contains only letters and numbers (no special characters)
    if (!preg_match('/^[A-Za-z]+\d+$/', $vehicle_no)) {
        $this->form_validation->set_message('vehicle_no_validation', 'The {field} should only contain letters and numbers, without any special characters.');
        return FALSE;
    }
    return TRUE;
}

    
    public function openVehicleEditModal(){
        $this->require_permission('vehicles.edit');
        $vehicle_id = $this->input->post('vehicle_id');
        $data['vehicle'] = $this->general_model->loadvehicle2($vehicle_id);
        $data['customers'] = $this->general_model->loadCustomers();
        $data['categories'] = $this->general_model->loadVehicleCategory();
        $data['brands'] = $this->general_model->loadVehicleBrand();

        $this->load->view('general/vehicle-edit', $data);
    }



public function updateXXXXXX()
{
    $this->require_permission('vehicles.edit');
    $vehicle_id = $this->input->post('vehicle_id');
    $data = $this->input->post();
    unset($data['vehicle_id']);

    $this->db->where('vehicle_id', $vehicle_id);
    if ($this->db->update('vehicles', $data)) {
        echo json_encode(['status' => 'success', 'message' => 'Vehicle updated successfully!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update vehicle.']);
    }
}

public function updateVehicle()
    {
        $this->require_permission('vehicles.edit');

        // Set custom error messages
        $this->form_validation->set_message('greater_than', '{field} selection required.');
        $this->form_validation->set_message('required', '{field} is required.');
        $this->form_validation->set_message('is_unique', 'This {field} is already registered.');

        $this->form_validation->set_rules('owner_id', 'Owner', 'required|greater_than[0]');
        //$this->form_validation->set_rules('vehicle_no', 'Vehicle No', 'required|is_unique[vehicles.vehicle_no]');
        $this->form_validation->set_rules('color', 'Color', 'required');
        $this->form_validation->set_rules('category_id', 'Category', 'required|greater_than[0]');
        $this->form_validation->set_rules('brand_id', 'Brand', 'required|greater_than[0]');
        $this->form_validation->set_rules('model', 'Model', 'required');
        $this->form_validation->set_rules('service_mileage', 'Service Mileage', 'required|integer');
        $this->form_validation->set_rules('next_service_date', 'Next Service Date', 'required');
    
        if ($this->form_validation->run() == FALSE) {
            echo json_encode(['status' => 'error', 'message' => validation_errors()]);
            return;
        }

        $vehicle_id = $this->input->post('vehicle_id');
        if (!$vehicle_id>0) {
            echo json_encode(['status' => 'error', 'message' => 'Server error..!']);
            return;
        }
    
        
        // Get form data
        $data = [
            'owner_id'            => $this->input->post('owner_id'),
            'chassis_no'          => $this->input->post('chassis_no'),
            'engine_no'           => $this->input->post('engine_no'),
            'color'               => $this->input->post('color'),
            'battery_no'          => $this->input->post('battery_no'),
            'year_of_manufacture' => $this->input->post('year_of_manufacture'),
            'category_id'         => $this->input->post('category_id'),
            'brand_id'            => $this->input->post('brand_id'),
            'model'               => $this->input->post('model'),
            'service_mileage'     => $this->input->post('service_mileage'),
            'next_service_date'   => $this->input->post('next_service_date'),
            'updated_at'          => date('Y-m-d H:i:s'),
            'updated_by'          => $this->session->userdata('userid')
        ];
    
        $this->db->where('vehicle_id', $vehicle_id);
    if ($this->db->update('vehicles', $data)) {
        echo json_encode(['status' => 'success', 'message' => 'Vehicle updated successfully!']);
        $this->load->model('logs_model');
            $this->logs_model->log_activity('Vehicle Updated', 'Vehicle No: ' . $this->input->post('vehicle_no'));
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update vehicle.']);
    }

    }


    public function showVehicleDetails($vehicle_id=null) {
        $this->require_permission('vehicles.view');
        $vehicle_id = $this->input->post('vehicle_id');
        $data['vehicle'] = $this->general_model->loadvehicle($vehicle_id);
        $this->load->view('general/vehicle-details', $data);
    }



}
?>
