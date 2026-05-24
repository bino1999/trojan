<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Supplier extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('general_model');
    }

    public function manageSupplier()
    {
        $this->require_permission('supplier.view');
        $data['mainMenuName'] = 'Supplier';
        $data['subMenuName'] = 'Supplier';

        $data['suppliers'] = $this->general_model->loadSupplier();
        $data['provinces'] = $this->general_model->loadProvince();
        $data['districts'] = $this->general_model->loadDistrict();

        $this->load->view('layout/header');
        $this->load->view('layout/top_navbar');
        $this->load->view('layout/left_sidebar', $data);
        $this->load->view('masters/supplier-manage', $data);
        $this->load->view('layout/footer');
    }

    public function get_districts()
    {
        $province_id = $this->input->post('province_id');
        if ($province_id) {
            $districts = $this->general_model->getDistrictsByProvince($province_id);
            echo json_encode($districts);
        }
    }

    public function get_cities()
    {
        $district_id = $this->input->post('district_id');
        if ($district_id) {
            $cities = $this->general_model->getCitiesByDistrict($district_id);
            echo json_encode($cities);
        }
    }

    public function saveSupplier()
    {
        $this->require_permission('supplier.create');
        $this->form_validation->set_rules('salutation', 'Salutation', 'required');
        $this->form_validation->set_rules('name', 'Name', 'required');
        $this->form_validation->set_rules('mobile', 'Mobile', 'required|numeric');
        $this->form_validation->set_rules('province', 'Province', 'required|greater_than[0]', ['greater_than' => 'Please select a valid province.']);
        $this->form_validation->set_rules('city', 'City', 'required|greater_than[0]', ['greater_than' => 'Please select a valid city.']);


        if ($this->form_validation->run() == FALSE) {
            echo json_encode(['status' => 'error', 'message' => validation_errors()]);
            return;
        }

        $data = [
            'salutation'   => $this->input->post('salutation'),
            'name'         => $this->input->post('name'),
            'nic'          => $this->input->post('nic'),
            'email'        => $this->input->post('email'),
            'mobile'       => $this->input->post('mobile'),
            'mobile_2'      => $this->input->post('mobile2'),
            'address_no'   => $this->input->post('address_no'),
            'street'       => $this->input->post('street'),
            'province_id'     => $this->input->post('province'),
            'district_id'     => $this->input->post('district'),
            'city_id'         => $this->input->post('city'),
            'postal_code'  => $this->input->post('postal_code'),
            'description'  => $this->input->post('description'),
            'created_at'   => date('Y-m-d H:i:s'),
            'created_by'   => $this->session->userdata('userid')
        ];

        $insert = $this->general_model->insertSupplier($data);

        if ($insert) {
            echo json_encode(['status' => 'success', 'message' => 'Supplier saved successfully.']);
            $this->load->model('logs_model');
            $this->logs_model->log_activity('Supplier Create', 'Supplier Name: ' . $this->input->post('name'));
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to save supplier.']);
        }
    }

    public function getSupplierById()
    {
        $supplier_id = $this->input->post('supplier_id');
        $Supplier = $this->general_model->loadSupplier($supplier_id);
        echo json_encode($Supplier);
    }

    public function updateSupplier()
    {
        $this->require_permission('supplier.edit');
        $this->form_validation->set_rules('supplier_id', 'Supplier ID', 'required|numeric');
        $this->form_validation->set_rules('name', 'Name', 'required');
        $this->form_validation->set_rules('mobile', 'Mobile', 'required|numeric');
        $this->form_validation->set_rules('edit_province', 'Province', 'required|greater_than[0]', ['greater_than' => 'Please select a valid province.']);
        $this->form_validation->set_rules('edit_city', 'City', 'required|greater_than[0]', ['greater_than' => 'Please select a valid city.']);

        if ($this->form_validation->run() == FALSE) {
            echo json_encode(['status' => 'error', 'message' => validation_errors()]);
            return;
        }
        

        // Collect data for update
        $data = [
            'salutation'  => $this->input->post('salutation'),
            'name'        => $this->input->post('name'),
            'nic'         => $this->input->post('nic'),
            'email'       => $this->input->post('email'),
            'mobile'      => $this->input->post('mobile'),
            'mobile_2'     => $this->input->post('mobile2'),
            'address_no'  => $this->input->post('address_no'),
            'street'      => $this->input->post('street'),
            'province_id'    => $this->input->post('edit_province'),
            'district_id'    => $this->input->post('edit_district'),
            'city_id'        => $this->input->post('edit_city'),
            'postal_code' => $this->input->post('edit_postal_code'),
            'description' => $this->input->post('description'),
            'updated_at'   => date('Y-m-d H:i:s'),
            'updated_by'   => $this->session->userdata('userid')
        ];

        $this->db->where('supplier_id', $this->input->post('supplier_id'));
        if ($this->db->update('suppliers', $data)) {
            echo json_encode(['status' => 'success', 'message' => 'Supplier updated successfully!']);
            $this->load->model('logs_model');
            $this->logs_model->log_activity('Supplier Edit', 'Supplier ID: ' . $this->input->post('supplier_id'));
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update supplier.']);
        }
    }

    public function getDistrictsByProvince()
{
    $provinceId = $this->input->post('province_id');
    $districts = $this->db->get_where('districts', ['province_id' => $provinceId])->result();
    echo json_encode($districts);
}

public function getCitiesByDistrict()
{
    $districtId = $this->input->post('district_id');
    $cities = $this->db->get_where('cities', ['district_id' => $districtId])->result();
    echo json_encode($cities);
}





}
?>
