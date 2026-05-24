<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class SystemRole extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('settings_model');
        $this->load->model('systemRolePermissions_model');
    }


    public function manageSystemRole()
    {
        $this->require_permission('admin.roles');
        $data['mainMenuName'] = 'settings';
        $data['subMenuName'] = 'system-role-manage'; 

        $data['results'] = $this->settings_model->loadSystemRole();    
        $this->load->view('layout/header');
        $this->load->view('layout/top_navbar'); 
        $this->load->view('layout/left_sidebar', $data);
        $this->load->view('masters/system-role-manage', $data);
        $this->load->view('layout/footer');
    }

    public function saveSystemRole()
{
    $this->require_permission('admin.roles');
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
        $exists = $this->settings_model->getSystemRoleByName($name);

        if ($exists) {
            echo json_encode(['status' => 'error', 'message' => 'Record already exists!']);
        } else {
            $this->settings_model->saveSystemRole($name);
            echo json_encode(['status' => 'success', 'message' => 'New category added successfully!']);
        }
    }
}


public function updateSystemRole() {
    $this->require_permission('admin.roles');
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

        $result = $this->settings_model->getSystemRoleByName($name);

        if ($result && $result->vehicleCategoryId  != $recordId) {
            //name already exists
            echo json_encode(['status' => 'error', 'message' => 'Record already exists!']);
        } else {
            // Update in the database
            $data = [
                'system_role_name' => $name,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            $this->db->where('system_role_id', $recordId);
            $this->db->update('system_roles', $data);

            echo json_encode(['status' => 'success', 'message' => 'Record updated successfully!', 'data' => $data]);
        }
    }
}

public function deleteSystemRole() {
    $this->require_permission('admin.roles');
    $recordId = $this->input->post('recordId');
    $result = $this->settings_model->loadSystemRole($recordId);  
    
    if ($result) {
        // Start transaction to ensure data consistency
        $this->db->trans_start();
        
        try {
            // First delete related permissions
            $this->db->where('role_id', $recordId);
            $this->db->delete('system_role_permissions');
            
            // Then delete the role
            $this->db->where('system_role_id', $recordId);
            $this->db->delete('system_roles');
            
            $this->db->trans_complete();
            
            if ($this->db->trans_status() === FALSE) {
                throw new Exception('Error deleting role');
            }
            
            $this->load->model('logs_model');
            $this->logs_model->log_activity('Role Permanently Deleted', 'Role ID: '.$recordId);
            
            echo json_encode(['status' => 'success', 'message' => 'Role has been permanently deleted!']);
        } catch (Exception $e) {
            $this->db->trans_rollback();
            log_message('error', 'Error deleting role: ' . $e->getMessage());
            echo json_encode(['status' => 'error', 'message' => 'Failed to delete role. Please try again.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Role not found.']);
    }  
}



public function loadPermissionsList() {
    $role_id = $this->input->post('recordId');

    // First ensure all permissions exist by seeding them directly
    $this->seedPermissionsDirectly();

    $assignedPermissions = $this->systemRolePermissions_model->getPermissionsByRole($role_id);
    $allPermissions = $this->systemRolePermissions_model->getAllPermissions();
    
    // Group permissions by category
    $categorizedPermissions = [];
    foreach ($allPermissions as $permission) {
        $category = $this->getPermissionCategory($permission->permission_name);
        if (!isset($categorizedPermissions[$category])) {
            $categorizedPermissions[$category] = [];
        }
        $categorizedPermissions[$category][] = $permission;
    }
    
    // Sort categories
    ksort($categorizedPermissions);
    
    $data['assignedPermissions'] = $assignedPermissions;
    $data['allPermissions'] = $allPermissions;
    $data['categorizedPermissions'] = $categorizedPermissions;

    $this->load->view('masters/system-role-permissions-list', $data);
}

private function getPermissionCategory($permissionName) {
    $categoryMap = [
        'dashboard' => 'Dashboard',
        'profile' => 'Dashboard',
        'quickbill' => 'Quick Bills',
        'internalbill' => 'Internal Bills',
        'job' => 'Jobs & Services',
        'supplier' => 'Purchasing & Inventory',
        'purchase' => 'Purchasing & Inventory',
        'stock' => 'Purchasing & Inventory',
        'stockupdate' => 'Purchasing & Inventory',
        'products' => 'Products',
        'payments' => 'Financial',
        'accounts' => 'Financial',
        'expenses' => 'Financial',
        'serviceitems' => 'Service Items',
        'servicepackages' => 'Service Packages',
        'employee' => 'Employees',
        'customers' => 'Customers & Vehicles',
        'vehicles' => 'Customers & Vehicles',
        'reports' => 'Reports',
        'admin' => 'System Administration',
        'attendance' => 'Employees',
        'advance' => 'Employees',
        'sms' => 'System Administration',
        'notifications' => 'System Administration',
        'cron' => 'System Administration'
    ];
    
    $prefix = explode('.', $permissionName)[0];
    return isset($categoryMap[$prefix]) ? $categoryMap[$prefix] : 'General';
}

private function seedPermissionsDirectly() {
    // First, ensure the system_permissions table exists
    $this->createPermissionsTable();
    
    // Check if permissions already exist
    $count = $this->db->count_all('system_permissions');
    if ($count > 0) {
        return; // Permissions already exist
    }

    // Define all permissions
    $permissions = [
        // Dashboard & General Access
        ['permission_name' => 'dashboard.view', 'permission_description' => 'View dashboard', 'category' => 'Dashboard'],
        ['permission_name' => 'profile.view', 'permission_description' => 'View own profile', 'category' => 'Dashboard'],
        ['permission_name' => 'profile.edit', 'permission_description' => 'Edit own profile', 'category' => 'Dashboard'],

        // Quick Bill System
        ['permission_name' => 'quickbill.view', 'permission_description' => 'View quick bills', 'category' => 'Quick Bills'],
        ['permission_name' => 'quickbill.create', 'permission_description' => 'Create new quick bills', 'category' => 'Quick Bills'],
        ['permission_name' => 'quickbill.edit', 'permission_description' => 'Edit existing quick bills', 'category' => 'Quick Bills'],
        ['permission_name' => 'quickbill.delete', 'permission_description' => 'Delete quick bills', 'category' => 'Quick Bills'],
        ['permission_name' => 'quickbill.list', 'permission_description' => 'View quick bill list', 'category' => 'Quick Bills'],

        // Internal Bill System
        ['permission_name' => 'internalbill.view', 'permission_description' => 'View internal bills', 'category' => 'Internal Bills'],
        ['permission_name' => 'internalbill.create', 'permission_description' => 'Create internal bills', 'category' => 'Internal Bills'],
        ['permission_name' => 'internalbill.edit', 'permission_description' => 'Edit internal bills', 'category' => 'Internal Bills'],
        ['permission_name' => 'internalbill.delete', 'permission_description' => 'Delete internal bills', 'category' => 'Internal Bills'],
        ['permission_name' => 'internalbill.list', 'permission_description' => 'View internal bill list', 'category' => 'Internal Bills'],

        // Services/Jobs Management
        ['permission_name' => 'job.view', 'permission_description' => 'View all jobs', 'category' => 'Jobs & Services'],
        ['permission_name' => 'job.create', 'permission_description' => 'Create new jobs', 'category' => 'Jobs & Services'],
        ['permission_name' => 'job.edit', 'permission_description' => 'Edit existing jobs', 'category' => 'Jobs & Services'],
        ['permission_name' => 'job.delete', 'permission_description' => 'Delete jobs', 'category' => 'Jobs & Services'],
        ['permission_name' => 'job.approve', 'permission_description' => 'Approve completed jobs (Manager function)', 'category' => 'Jobs & Services'],
        ['permission_name' => 'job.hold', 'permission_description' => 'Put jobs on hold', 'category' => 'Jobs & Services'],
        ['permission_name' => 'job.cancel', 'permission_description' => 'Cancel jobs', 'category' => 'Jobs & Services'],
        ['permission_name' => 'job.invoice', 'permission_description' => 'Generate job invoices', 'category' => 'Jobs & Services'],

        // Purchasing System
        ['permission_name' => 'supplier.view', 'permission_description' => 'View suppliers', 'category' => 'Purchasing & Inventory'],
        ['permission_name' => 'supplier.create', 'permission_description' => 'Add new suppliers', 'category' => 'Purchasing & Inventory'],
        ['permission_name' => 'supplier.edit', 'permission_description' => 'Edit suppliers', 'category' => 'Purchasing & Inventory'],
        ['permission_name' => 'supplier.delete', 'permission_description' => 'Delete suppliers', 'category' => 'Purchasing & Inventory'],
        ['permission_name' => 'purchase.view', 'permission_description' => 'View purchase orders', 'category' => 'Purchasing & Inventory'],
        ['permission_name' => 'purchase.create', 'permission_description' => 'Create purchase orders', 'category' => 'Purchasing & Inventory'],
        ['permission_name' => 'purchase.edit', 'permission_description' => 'Edit purchase orders', 'category' => 'Purchasing & Inventory'],
        ['permission_name' => 'purchase.delete', 'permission_description' => 'Delete purchase orders', 'category' => 'Purchasing & Inventory'],
        ['permission_name' => 'stock.view', 'permission_description' => 'View stock levels', 'category' => 'Purchasing & Inventory'],
        ['permission_name' => 'stock.manage', 'permission_description' => 'Manage stock (add/remove items)', 'category' => 'Purchasing & Inventory'],

        // Products Management
        ['permission_name' => 'products.view', 'permission_description' => 'View products', 'category' => 'Products'],
        ['permission_name' => 'products.create', 'permission_description' => 'Add new products', 'category' => 'Products'],
        ['permission_name' => 'products.edit', 'permission_description' => 'Edit products', 'category' => 'Products'],
        ['permission_name' => 'products.delete', 'permission_description' => 'Delete products', 'category' => 'Products'],

        // Financial/Wallet System
        ['permission_name' => 'payments.view', 'permission_description' => 'View payments'],
        ['permission_name' => 'payments.create', 'permission_description' => 'Record payments'],
        ['permission_name' => 'payments.edit', 'permission_description' => 'Edit payments'],
        ['permission_name' => 'payments.delete', 'permission_description' => 'Delete payments'],
        ['permission_name' => 'accounts.inhand', 'permission_description' => 'Manage in-hand cash'],
        ['permission_name' => 'accounts.bank', 'permission_description' => 'Manage bank accounts'],
        ['permission_name' => 'accounts.banktransfer', 'permission_description' => 'Bank transfers'],
        ['permission_name' => 'accounts.cardmachine', 'permission_description' => 'Card machine transactions'],
        ['permission_name' => 'accounts.credits', 'permission_description' => 'Manage credits'],
        ['permission_name' => 'accounts.loans', 'permission_description' => 'Manage loans'],
        ['permission_name' => 'expenses.view', 'permission_description' => 'View expenses'],
        ['permission_name' => 'expenses.create', 'permission_description' => 'Add expenses'],
        ['permission_name' => 'expenses.edit', 'permission_description' => 'Edit expenses'],
        ['permission_name' => 'expenses.delete', 'permission_description' => 'Delete expenses'],

        // Service Packages
        ['permission_name' => 'serviceitems.view', 'permission_description' => 'View service items'],
        ['permission_name' => 'serviceitems.create', 'permission_description' => 'Add service items'],
        ['permission_name' => 'serviceitems.edit', 'permission_description' => 'Edit service items'],
        ['permission_name' => 'serviceitems.delete', 'permission_description' => 'Delete service items'],
        ['permission_name' => 'servicepackages.view', 'permission_description' => 'View service packages'],
        ['permission_name' => 'servicepackages.create', 'permission_description' => 'Create service packages'],
        ['permission_name' => 'servicepackages.edit', 'permission_description' => 'Edit service packages'],
        ['permission_name' => 'servicepackages.delete', 'permission_description' => 'Delete service packages'],

        // Employee Management
        ['permission_name' => 'employee.view', 'permission_description' => 'View employee list'],
        ['permission_name' => 'employee.create', 'permission_description' => 'Add new employees'],
        ['permission_name' => 'employee.edit', 'permission_description' => 'Edit employee details'],
        ['permission_name' => 'employee.delete', 'permission_description' => 'Delete employees'],
        ['permission_name' => 'employee.attendance', 'permission_description' => 'Manage attendance'],
        ['permission_name' => 'employee.advance', 'permission_description' => 'Manage employee advances'],

        // System Settings
        ['permission_name' => 'settings.categories', 'permission_description' => 'Manage item categories'],
        ['permission_name' => 'settings.servicecategories', 'permission_description' => 'Manage service item categories'],
        ['permission_name' => 'settings.brands', 'permission_description' => 'Manage item brands'],
        ['permission_name' => 'settings.vehiclecategories', 'permission_description' => 'Manage vehicle categories'],
        ['permission_name' => 'settings.vehiclebrands', 'permission_description' => 'Manage vehicle brands'],
        ['permission_name' => 'settings.expensecategories', 'permission_description' => 'Manage expense categories'],
        ['permission_name' => 'settings.employeesections', 'permission_description' => 'Manage employee sections'],
        ['permission_name' => 'settings.roles', 'permission_description' => 'Manage system roles and permissions'],

        // Customer & Vehicle Management
        ['permission_name' => 'customers.view', 'permission_description' => 'View customers'],
        ['permission_name' => 'customers.create', 'permission_description' => 'Add new customers'],
        ['permission_name' => 'customers.edit', 'permission_description' => 'Edit customer details'],
        ['permission_name' => 'customers.delete', 'permission_description' => 'Delete customers'],
        ['permission_name' => 'vehicles.view', 'permission_description' => 'View vehicles'],
        ['permission_name' => 'vehicles.create', 'permission_description' => 'Add new vehicles'],
        ['permission_name' => 'vehicles.edit', 'permission_description' => 'Edit vehicle details'],
        ['permission_name' => 'vehicles.delete', 'permission_description' => 'Delete vehicles'],

        // Reports
        ['permission_name' => 'reports.view', 'permission_description' => 'View all reports'],
        ['permission_name' => 'reports.financial', 'permission_description' => 'View financial reports'],
        ['permission_name' => 'reports.jobs', 'permission_description' => 'View job reports'],
        ['permission_name' => 'reports.inventory', 'permission_description' => 'View inventory reports'],

        // System Administration
        ['permission_name' => 'admin.users', 'permission_description' => 'Manage system users'],
        ['permission_name' => 'admin.roles', 'permission_description' => 'Manage roles and permissions'],
        ['permission_name' => 'admin.settings', 'permission_description' => 'System-wide settings'],
        ['permission_name' => 'admin.logs', 'permission_description' => 'View system logs'],

        // Attendance Management
        ['permission_name' => 'attendance.view', 'permission_description' => 'View attendance records', 'category' => 'Employees'],
        ['permission_name' => 'attendance.manage', 'permission_description' => 'Mark/modify attendance', 'category' => 'Employees'],

        // Employee Advances
        ['permission_name' => 'advance.view', 'permission_description' => 'View employee advances', 'category' => 'Employees'],
        ['permission_name' => 'advance.manage', 'permission_description' => 'Manage employee advances', 'category' => 'Employees'],

        // SMS Management
        ['permission_name' => 'sms.view', 'permission_description' => 'View SMS logs', 'category' => 'System Administration'],
        ['permission_name' => 'sms.send', 'permission_description' => 'Send SMS notifications', 'category' => 'System Administration'],

        // Stock Update Management
        ['permission_name' => 'stockupdate.view', 'permission_description' => 'View stock update logs', 'category' => 'Purchasing & Inventory'],
        ['permission_name' => 'stockupdate.manage', 'permission_description' => 'Manage stock updates', 'category' => 'Purchasing & Inventory'],

        // Notifications Management
        ['permission_name' => 'notifications.view', 'permission_description' => 'View system notifications', 'category' => 'System Administration'],
        ['permission_name' => 'notifications.manage', 'permission_description' => 'Manage notifications', 'category' => 'System Administration'],

        // Cron/Background Jobs
        ['permission_name' => 'cron.view', 'permission_description' => 'View cron job status', 'category' => 'System Administration'],
        ['permission_name' => 'cron.manage', 'permission_description' => 'Manage cron jobs', 'category' => 'System Administration'],

        // Enhanced Financial Controls
        ['permission_name' => 'payments.cheque.return', 'permission_description' => 'Return cheques', 'category' => 'Financial'],
        ['permission_name' => 'payments.reconcile', 'permission_description' => 'Payment reconciliation', 'category' => 'Financial'],

        // Enhanced Job Management
        ['permission_name' => 'job.assign', 'permission_description' => 'Assign jobs to technicians', 'category' => 'Jobs & Services'],
        ['permission_name' => 'job.progress', 'permission_description' => 'Update job progress', 'category' => 'Jobs & Services'],
        ['permission_name' => 'job.complete', 'permission_description' => 'Mark jobs as complete', 'category' => 'Jobs & Services']
    ];

    // Insert all permissions with correct column names and categories
    foreach ($permissions as $permission) {
        $data = [
            'permission_name' => $permission['permission_name'],
            'permission_key' => str_replace('.', '_', $permission['permission_name']),
            'description' => $permission['permission_description'],
            'category' => isset($permission['category']) ? $permission['category'] : 'General',
            'created_at' => date('Y-m-d H:i:s')
        ];
        $this->db->insert('system_permissions', $data);
    }
}

private function createPermissionsTable() {
    // Check if table exists
    if ($this->db->table_exists('system_permissions')) {
        return; // Table already exists
    }
    
    // Create the system_permissions table with correct column names
    $sql = "CREATE TABLE `system_permissions` (
        `permission_id` int(11) NOT NULL AUTO_INCREMENT,
        `permission_name` varchar(155) NOT NULL,
        `permission_key` varchar(125) NOT NULL,
        `description` text,
        `created_at` timestamp NULL DEFAULT current_timestamp(),
        `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
        PRIMARY KEY (`permission_id`),
        UNIQUE KEY `permission_name` (`permission_name`),
        UNIQUE KEY `permission_key` (`permission_key`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    $this->db->query($sql);
}

public function saveRolePermissions() {
    $this->require_permission('admin.roles');
    $role_id = $this->input->post('role_id');
    $permissions = $this->input->post('permissions');

    if (!$role_id) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid Role ID.']);
        return;
    }

    // Remove existing permissions for this role
    $this->systemRolePermissions_model->removeRolePermissions($role_id);    
    // Insert new permissions
    if (!empty($permissions)) {
        foreach ($permissions as $permissionKey) {
            $this->systemRolePermissions_model->assignPermission($role_id, $permissionKey);
        }
    }

    // If the current user's role was updated, refresh their permissions in session
    $currentRoleId = $this->session->userdata('role');
    if ($currentRoleId && (string)$currentRoleId === (string)$role_id) {
        $assignedPermissions = $this->systemRolePermissions_model->getPermissionsByRole($role_id);
        $permissionIds = array_column($assignedPermissions, 'permission_id');
        $this->session->set_userdata('permissions', $permissionIds);
    }

    echo json_encode(['status' => 'success', 'message' => 'Permissions assigned successfully.']);
}

public function seedAllPermissions() {
    // Clear all existing permissions first
    $this->db->empty_table('system_permissions');
    
    $this->load->model('SystemPermissions_model');
    $result = $this->SystemPermissions_model->seedPermissions();
    
    echo json_encode([
        'status' => 'success', 
        'message' => "Permissions seeded successfully! Inserted: {$result['inserted']}, Skipped: {$result['skipped']}, Total: {$result['total']}"
    ]);
}


}
?>