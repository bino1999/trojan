<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Settings_model extends CI_Model
{

    public function loadItemCategory($itemCategoryId = null)
    {
        $this->db->select('*');
        $this->db->from('item_categories');

        if ($itemCategoryId) {
            $this->db->like('itemCategoryId', $itemCategoryId);
        }

        $this->db->order_by('itemCategoryId', 'ASC');
        $query = $this->db->get();
        return $query->result();
    }

    public function checkCategoryExists($name, $excludeId = null)
    {
        $this->db->where('itemCategoryName', $name);
        if ($excludeId) {
            $this->db->where('itemCategoryId !=', $excludeId);
        }
        return $this->db->get('item_categories')->num_rows() > 0;
    }

    public function saveCategory($name)
    {
        $this->db->insert('item_categories', [
            'itemCategoryName' => $name,
            'createdAt' => date('Y-m-d H:i:s')
        ]);
    }

    public function updateCategory($id, $name)
    {
        $this->db->where('itemCategoryId', $id);
        $this->db->update('item_categories', [
            'itemCategoryName' => $name,
            'updatedAt' => date('Y-m-d H:i:s')
        ]);
    }

    public function getCategoryByName($categoryName)
    {
        $this->db->where('itemCategoryName', $categoryName);
        $query = $this->db->get('item_categories');
        return $query->row();
    }

    public function loadItemBrand($itemBrandId = null)
    {
        $this->db->select('*');
        $this->db->from('item_brands');
        if ($itemBrandId > 0) {
            $this->db->where('itemBrandId', $itemBrandId);
        }
        $query = $this->db->get();
        return $query->result();
    }

    public function checkBrandExists($name, $excludeId = null)
    {
        $this->db->where('itemBrandName', $name);
        if ($excludeId) {
            $this->db->where('itemBrandId !=', $excludeId);
        }
        return $this->db->get('item_brands')->num_rows() > 0;
    }

    public function saveBrand($name)
    {
        $this->db->insert('item_brands', [
            'itemBrandName' => $name,
            'createdAt' => date('Y-m-d H:i:s')
        ]);
    }

    public function getBrandByName($brandName)
    {
        $this->db->where('itemBrandName', $brandName);
        $query = $this->db->get('item_brands');
        return $query->row();
    }

    public function loadVehicleCategory($vehicleCategoryId = null)
    {
        $this->db->select('*');
        $this->db->from('vehicle_categories');
        if ($vehicleCategoryId > 0) {
            $this->db->where('vehicleCategoryId', $vehicleCategoryId);
        }
        $query = $this->db->get();
        return $query->result();
    }

    public function checkVehicleCategoryExists($name, $excludeId = null)
    {
        $this->db->where('vehicleCategoryName', $name);
        if ($excludeId) {
            $this->db->where('vehicleCategoryId !=', $excludeId);
        }
        return $this->db->get('vehicle_categories')->num_rows() > 0;
    }

    public function saveVehicleCategory($name)
    {
        $this->db->insert('vehicle_categories', [
            'vehicleCategoryName' => $name,
            'createdAt' => date('Y-m-d H:i:s')
        ]);
    }

    public function loadVehicleBrand($vehicleBrandId = null)
    {
        $this->db->select('*');
        $this->db->from('vehicle_brands');
        if ($vehicleBrandId > 0) {
            $this->db->where('vehicleBrandId', $vehicleBrandId);
        }
        $query = $this->db->get();
        return $query->result();
    }

    public function saveVehicleBrand($name)
    {
        $this->db->insert('vehicle_brands', [
            'vehicleBrandName' => $name,
            'createdAt' => date('Y-m-d H:i:s')
        ]);
    }

    public function getVehicleBrandByName($brandName)
    {
        $this->db->where('vehicleBrandName', $brandName);
        $query = $this->db->get('vehicle_brands');
        return $query->row();
    }

    public function loadExpensesCategory($expensesCategoryId = null)
    {
        $this->db->select('*');
        $this->db->from('expenses_categories');
        if ($expensesCategoryId > 0) {
            $this->db->where('expensesCategoryId', $expensesCategoryId);
        }
        $query = $this->db->get();
        return $query->result();
    }

    public function saveExpensesCategory($name)
    {
        $this->db->insert('expenses_categories', [
            'expensesCategoryName' => $name,
            'createdAt' => date('Y-m-d H:i:s')
        ]);
    }

    public function getExpensesCategoryByName($name)
    {
        $this->db->where('expensesCategoryName', $name);
        $query = $this->db->get('expenses_categories');
        return $query->row();
    }

    public function loadEmployeeSection($employeeSectionId = null)
    {
        $this->db->select('*');
        $this->db->from('employee_sections');
        if ($employeeSectionId > 0) {
            $this->db->where('employeeSectionId', $employeeSectionId);
        }
        $query = $this->db->get();
        return $query->result();
    }

    public function saveEmployeeSection($name)
    {
        $this->db->insert('employee_sections', [
            'employeeSectionName' => $name,
            'createdAt' => date('Y-m-d H:i:s')
        ]);
    }

    public function getEmployeeSectionByName($name)
    {
        $this->db->where('employeeSectionName', $name);
        $query = $this->db->get('employee_sections');
        return $query->row();
    }

    public function loadSystemRole($system_role_id = null)
    {
        $this->db->select('*');
        $this->db->from('system_roles');
        if ($system_role_id > 0) {
            $this->db->where('system_role_id', $system_role_id);
        }
        $query = $this->db->get();
        return $query->result();
    }

    public function saveSystemRole($name)
    {
        $this->db->insert('system_roles', [
            'system_role_name' => $name,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function getSystemRoleByName($name)
    {
        $this->db->where('system_role_name', $name);
        $query = $this->db->get('system_roles');
        return $query->row();
    }

    public function loadSystemUsers($UserID = null)
    {
        $this->db->select('users.*, system_roles.system_role_name, employee_sections.employeeSectionName');
        $this->db->from('users');
        $this->db->join('system_roles', 'system_roles.system_role_id = users.Role', 'left');
        $this->db->join('employee_sections', 'employee_sections.employeeSectionId = users.Department', 'left');

        if ($UserID > 0) {
            $this->db->where('users.UserID', $UserID);
        }
        // Removed the filter that only showed admin users - now shows all users

        $this->db->order_by('users.CreatedAt', 'DESC');
        $query = $this->db->get();
        return $query->result();
    }

    public function checkUserEmailExists($email, $userId)
    {
        $this->db->select('UserID');
        $this->db->from('users');
        $this->db->where('Email', $email);
        $this->db->where('UserID !=', $userId);  // Exclude current user
        $query = $this->db->get();

        return $query->num_rows() > 0;  // Return true if email exists, false otherwise
    }
}
