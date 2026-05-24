<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Customer extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('general_model');
    }


    public function manageCustomer()
    {
        $this->require_permission('customers.view');
        $data['mainMenuName'] = 'Customers';
        $data['subMenuName'] = 'system-user-manage';


        //$data['customers'] = $this->general_model->loadCustomer();
        $data['provinces'] = $this->general_model->loadProvince();
        $data['districts'] = $this->general_model->loadDistrict();

        $this->load->view('layout/header');
        $this->load->view('layout/top_navbar');
        $this->load->view('layout/left_sidebar', $data);
        $this->load->view('customers/customer-manage', $data);
        $this->load->view('layout/footer');
    }

    public function loadCustomers(){

        $sdate = $this->input->post('sdate');
        $edate = $this->input->post('edate');
        $data['customers'] = $this->general_model->loadCustomerByDates($sdate, $edate);
        $this->load->view('customers/customer-list', $data);
    }

    public function filterCustomers(){

        $searchFilter = $this->input->post('searchFilter');

        $data['customers'] = $this->general_model->filterCustomers($searchFilter);
        $this->load->view('customers/customer-list', $data);
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

    public function saveCustomer()
    {
        $this->require_permission('customers.create');
    $this->form_validation->set_rules('salutation', 'Salutation', 'required');
    $this->form_validation->set_rules('name', 'Name', 'required');

    // Mobile: exactly 10 digits, numeric
    $this->form_validation->set_rules('mobile', 'Mobile', 'required|regex_match[/^[0-9]{10}$/]', ['regex_match' => 'Mobile must be exactly 10 digits.']);

    // Optional mobile_2: if provided, must be 10 digits
    if (!empty($this->input->post('mobile2'))) {
        $this->form_validation->set_rules('mobile2', 'Mobile 2', 'regex_match[/^[0-9]{10}$/]', ['regex_match' => 'Mobile 2 must be exactly 10 digits.']);
    }

    // NIC: 9 digits + optional V/v/X/x or 12 digits (old or new format)
    if (!empty($this->input->post('nic'))) {
        $this->form_validation->set_rules('nic', 'NIC', 'regex_match[/^([0-9]{9}[vVxX]|[0-9]{12})$/]', ['regex_match' => 'NIC must be 9 digits with V/X or 12 digits.']);
    }

    // Email: optional but must be valid if provided
    if (!empty($this->input->post('email'))) {
        $this->form_validation->set_rules('email', 'Email', 'valid_email');
    }

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
        'mobile_2'     => $this->input->post('mobile2'),
        'address_no'   => $this->input->post('address_no'),
        'street'       => $this->input->post('street'),
        'province_id'  => $this->input->post('province'),
        'district_id'  => $this->input->post('district'),
        'city_id'      => $this->input->post('city'),
        'postal_code'  => $this->input->post('postal_code'),
        'description'  => $this->input->post('description'),
        'created_at'   => date('Y-m-d H:i:s'),
        'created_by'   => $this->session->userdata('userid')
    ];

    $insert = $this->general_model->insertCustomer($data);

    if ($insert) {
        echo json_encode(['status' => 'success', 'message' => 'Customer saved successfully.']);
        $this->load->model('logs_model');
        $this->logs_model->log_activity('Customer Create', 'Customer Name: ' . $this->input->post('name'));
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to save customer.']);
    }
}


    public function getCustomerById()
    {
        $customer_id = $this->input->post('customer_id');
        $customer = $this->general_model->loadCustomer($customer_id);
        echo json_encode($customer);
    }

    public function updateCustomer()
    {
        $this->require_permission('customers.edit');
    $this->form_validation->set_rules('customer_id', 'Customer ID', 'required|numeric');
    $this->form_validation->set_rules('salutation', 'Salutation', 'required');
    $this->form_validation->set_rules('name', 'Name', 'required');
    $this->form_validation->set_rules('mobile', 'Mobile', 'required|regex_match[/^[0-9]{10}$/]', ['regex_match' => 'Mobile must be exactly 10 digits.']);

    if (!empty($this->input->post('mobile2'))) {
        $this->form_validation->set_rules('mobile2', 'Mobile 2', 'regex_match[/^[0-9]{10}$/]', ['regex_match' => 'Mobile 2 must be exactly 10 digits.']);
    }

    if (!empty($this->input->post('email'))) {
        $this->form_validation->set_rules('email', 'Email', 'valid_email');
    }

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
        'mobile_2'     => $this->input->post('mobile2'),
        'address_no'   => $this->input->post('address_no'),
        'street'       => $this->input->post('street'),
        'province_id'  => $this->input->post('province'),
        'district_id'  => $this->input->post('district'),
        'city_id'      => $this->input->post('city'),
        'postal_code'  => $this->input->post('edit_postal_code'),
        'description'  => $this->input->post('description'),
        'updated_at'   => date('Y-m-d H:i:s'),
        'updated_by'   => $this->session->userdata('userid')
    ];

    $this->db->where('customers_id', $this->input->post('customer_id'));
    if ($this->db->update('customers', $data)) {
        echo json_encode(['status' => 'success', 'message' => 'Customer updated successfully!']);
        $this->load->model('logs_model');
        $this->logs_model->log_activity('Customer Edit', 'Customer ID: ' . $this->input->post('customer_id'));
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update customer.']);
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
