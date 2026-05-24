<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ItemCategory extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('settings_model');
    }

public function manageCategory()
{
    $this->require_permission('settings.categories');
    $data['mainMenuName'] = 'settings';
    $data['subMenuName'] = 'category-manage'; 

    $data['itemCategories'] = $this->settings_model->loadItemCategory();
    $this->load->view('layout/header');
    $this->load->view('layout/top_navbar');
    $this->load->view('layout/left_sidebar', $data);
    $this->load->view('masters/category-manage');
    $this->load->view('layout/footer');
}

public function saveCategory()
{
    $this->require_permission('settings.categories');
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
        $exists = $this->settings_model->checkCategoryExists($categoryName);

        if ($exists) {
            echo json_encode(['status' => 'error', 'message' => 'Category already exists!']);
        } else {
            $this->settings_model->saveCategory($categoryName);
            echo json_encode(['status' => 'success', 'message' => 'Category added successfully!']);
        }
    }
}


public function updateCategory() {
    $this->require_permission('settings.categories');
    // Load form validation
    $this->load->library('form_validation');
    $this->form_validation->set_rules('name', 'Category Name', 'required|trim');

    if ($this->form_validation->run() == FALSE) {
        // Validation failed
        echo json_encode(['status' => 'error', 'message' => validation_errors()]);
    } else {
        // Validation passed, update the category
        $categoryId = $this->input->post('id');
        $categoryName = strtoupper($this->input->post('name'));  // Convert to upper case

        // Check if the category exists
        $this->load->model('settings_model');
        $category = $this->settings_model->getCategoryByName($categoryName);

        if ($category && $category->itemCategoryId != $categoryId) {
            // Category name already exists
            echo json_encode(['status' => 'error', 'message' => 'Category already exists!']);
        } else {
            // Update category in the database
            $data = [
                'itemCategoryName' => $categoryName,
                'updatedAt' => date('Y-m-d H:i:s')
            ];
            $this->db->where('itemCategoryId', $categoryId);
            $this->db->update('item_categories', $data);

            echo json_encode(['status' => 'success', 'message' => 'Category updated successfully!', 'data' => $data]);
        }
    }
}

public function deleteCategory($value = '')
{
    $this->require_permission('settings.categories');
    $categoryId = $this->input->post('categoryId');
    $category = $this->settings_model->loadItemCategory($categoryId);

    if ($category) {
        $this->db->where('itemCategoryId', $categoryId);
        $this->db->update('item_categories', ['isDeleted' => 1, 'updatedAt' => date('Y-m-d H:i:s')]);

        echo json_encode(['status' => 'success', 'message' => 'Category successfully marked as deleted.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Category not found.']);
    }
}

public function undoDeleteCategory() {
    $this->require_permission('settings.categories');
    $categoryId = $this->input->post('categoryId');
    $category = $this->settings_model->loadItemCategory($categoryId);

    if ($category) {
        // Restore the category by setting isDeleted to 0
        $this->db->where('itemCategoryId', $categoryId);
        $this->db->update('item_categories', ['isDeleted' => 0, 'updatedAt' => date('Y-m-d H:i:s')]);

        echo json_encode([
            'status' => 'success',
            'message' => 'Category successfully restored.'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Category not found.'
        ]);
    }
}







}
?>