<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ServiceItemCategory extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('products_model');
    }

    public function manageServiceItemCategory()
    {
        $this->require_permission('settings.servicecategories');
        $data['mainMenuName'] = 'settings';
        $data['subMenuName'] = 'service-item-category-manage'; 

        $data['serviceItemCategories'] = $this->products_model->loadServiceItemsCategory();
        $this->load->view('layout/header');
        $this->load->view('layout/top_navbar');
        $this->load->view('layout/left_sidebar', $data);
        $this->load->view('masters/service-item-category-manage', $data);
        $this->load->view('layout/footer');
    }

    public function saveServiceItemCategory()
    {
        $this->require_permission('settings.servicecategories');
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
            $categoryName = strtoupper($this->input->post('name', true));
            $exists = $this->checkServiceItemCategoryExists($categoryName);

            if ($exists) {
                echo json_encode(['status' => 'error', 'message' => 'Service Item Category already exists!']);
            } else {
                $this->saveServiceItemCategoryToDb($categoryName);
                echo json_encode(['status' => 'success', 'message' => 'Service Item Category added successfully!']);
            }
        }
    }

    public function updateServiceItemCategory() {
        $this->require_permission('settings.servicecategories');
        $this->load->library('form_validation');
        $this->form_validation->set_rules('name', 'Category Name', 'required|trim');

        if ($this->form_validation->run() == FALSE) {
            echo json_encode(['status' => 'error', 'message' => validation_errors()]);
        } else {
            $categoryId = $this->input->post('id');
            $categoryName = strtoupper($this->input->post('name'));

            $category = $this->getServiceItemCategoryByName($categoryName);

            if ($category && $category->sic_id != $categoryId) {
                echo json_encode(['status' => 'error', 'message' => 'Service Item Category already exists!']);
            } else {
                $data = [
                    'sci_name' => $categoryName
                ];
                $this->db->where('sic_id', $categoryId);
                $this->db->update('service_items_category', $data);

                echo json_encode(['status' => 'success', 'message' => 'Service Item Category updated successfully!', 'data' => $data]);
            }
        }
    }

    public function deleteServiceItemCategory()
    {
        $this->require_permission('settings.servicecategories');
        $categoryId = $this->input->post('categoryId');
        $category = $this->getServiceItemCategoryById($categoryId);

        if ($category) {
            $this->db->where('sic_id', $categoryId);
            $this->db->update('service_items_category', ['isDeleted' => 1]);

            echo json_encode(['status' => 'success', 'message' => 'Service Item Category successfully marked as deleted.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Service Item Category not found.']);
        }
    }

    public function undoDeleteServiceItemCategory() {
        $this->require_permission('settings.servicecategories');
        $categoryId = $this->input->post('categoryId');
        $category = $this->getServiceItemCategoryById($categoryId);

        if ($category) {
            $this->db->where('sic_id', $categoryId);
            $this->db->update('service_items_category', ['isDeleted' => 0]);

            echo json_encode([
                'status' => 'success',
                'message' => 'Service Item Category successfully restored.'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Service Item Category not found.'
            ]);
        }
    }

    // Helper methods
    private function checkServiceItemCategoryExists($categoryName) {
        $this->db->where('sci_name', $categoryName);
        $this->db->where('isDeleted', 0);
        return $this->db->get('service_items_category')->row();
    }

    private function saveServiceItemCategoryToDb($categoryName) {
        $data = [
            'sci_name' => $categoryName,
            'isDeleted' => 0
        ];
        $this->db->insert('service_items_category', $data);
    }

    private function getServiceItemCategoryByName($categoryName) {
        $this->db->where('sci_name', $categoryName);
        $this->db->where('isDeleted', 0);
        return $this->db->get('service_items_category')->row();
    }

    private function getServiceItemCategoryById($categoryId) {
        $this->db->where('sic_id', $categoryId);
        return $this->db->get('service_items_category')->row();
    }
}
?>
