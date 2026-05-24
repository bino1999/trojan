<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ItemBrand extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('settings_model');
    }


    public function manageBrand()
    {
        $this->require_permission('settings.brands');
        $data['mainMenuName'] = 'settings';
        $data['subMenuName'] = 'brand-manage'; 

        $data['itemBrands'] = $this->settings_model->loadItemBrand();    
        $this->load->view('layout/header');
        $this->load->view('layout/top_navbar'); 
        $this->load->view('layout/left_sidebar', $data);
        $this->load->view('masters/brand-manage');
        $this->load->view('layout/footer');
    }

    public function saveBrand()
{
    $this->require_permission('settings.brands');
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
        $exists = $this->settings_model->checkBrandExists($name);

        if ($exists) {
            echo json_encode(['status' => 'error', 'message' => 'Record already exists!']);
        } else {
            $this->settings_model->saveBrand($name);
            echo json_encode(['status' => 'success', 'message' => 'New brand added successfully!']);
        }
    }
}


public function updateBrand() {
    $this->require_permission('settings.brands');
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
        
        $brandId = $this->input->post('id');
        $name = strtoupper($this->input->post('name'));  // Convert to upper case

        $brand = $this->settings_model->getBrandByName($name);

        if ($brand && $brand->itemBrandId != $brandId) {
            //name already exists
            echo json_encode(['status' => 'error', 'message' => 'Brand already exists!']);
        } else {
            // Update in the database
            $data = [
                'itemBrandName' => $name,
                'updatedAt' => date('Y-m-d H:i:s')
            ];
            $this->db->where('itemBrandId', $brandId);
            $this->db->update('item_brands', $data);

            echo json_encode(['status' => 'success', 'message' => 'Brand updated successfully!', 'data' => $data]);
        }
    }
}

public function deleteBrand() {
    $this->require_permission('settings.brands');
    $recordId = $this->input->post('recordId');
    $brand = $this->settings_model->loadItemBrand($recordId);  
    
    if ($brand) {
        $this->db->where('itemBrandId', $recordId);
        $this->db->update('item_brands', ['isDeleted' => 1]);
        echo json_encode(['status' => 'success', 'message' => 'Brand deleted successfully!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Brand not found.']);
    }  

}

public function undoDeleteBrand() {
    $this->require_permission('settings.brands');
    $recordId = $this->input->post('recordId');
    $brand = $this->settings_model->loadItemBrand($recordId);  
    
    if ($brand) {
        $this->db->where('itemBrandId', $recordId);
        $this->db->update('item_brands', ['isDeleted' => 0]);
        echo json_encode(['status' => 'success', 'message' => 'Brand activated successfully!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Brand not found.']);
    }  

}


}
?>