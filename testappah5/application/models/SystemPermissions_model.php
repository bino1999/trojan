<?php
defined('BASEPATH') or exit('No direct script access allowed');

class SystemPermissions_model extends CI_Model
{
    public function getAllPermissions()
    {
        return $this->db->get('system_permissions')->result();
    }

    public function getPermissionById($permission_id)
    {
        $this->db->where('permission_id', $permission_id);
        return $this->db->get('system_permissions')->row();
    }

    public function getPermissionByName($permission_name)
    {
        $this->db->where('permission_name', $permission_name);
        return $this->db->get('system_permissions')->row();
    }

    private function permissionDefinitions()
    {
        return [
            ['permission_name' => 'dashboard.view', 'permission_description' => 'View dashboard', 'category' => 'Dashboard'],
            ['permission_name' => 'profile.view', 'permission_description' => 'View own profile', 'category' => 'Dashboard'],
            ['permission_name' => 'profile.edit', 'permission_description' => 'Edit own profile', 'category' => 'Dashboard'],
            ['permission_name' => 'quickbill.view', 'permission_description' => 'View quick bills', 'category' => 'Quick Bills'],
            ['permission_name' => 'quickbill.create', 'permission_description' => 'Create new quick bills', 'category' => 'Quick Bills'],
            ['permission_name' => 'quickbill.edit', 'permission_description' => 'Edit existing quick bills', 'category' => 'Quick Bills'],
            ['permission_name' => 'quickbill.delete', 'permission_description' => 'Delete quick bills', 'category' => 'Quick Bills'],
            ['permission_name' => 'quickbill.list', 'permission_description' => 'View quick bill list', 'category' => 'Quick Bills'],
            ['permission_name' => 'internalbill.view', 'permission_description' => 'View internal bills', 'category' => 'Internal Bills'],
            ['permission_name' => 'internalbill.create', 'permission_description' => 'Create internal bills', 'category' => 'Internal Bills'],
            ['permission_name' => 'internalbill.edit', 'permission_description' => 'Edit internal bills', 'category' => 'Internal Bills'],
            ['permission_name' => 'internalbill.delete', 'permission_description' => 'Delete internal bills', 'category' => 'Internal Bills'],
            ['permission_name' => 'internalbill.list', 'permission_description' => 'View internal bill list', 'category' => 'Internal Bills'],
            ['permission_name' => 'job.view', 'permission_description' => 'View all jobs', 'category' => 'Jobs & Services'],
            ['permission_name' => 'job.create', 'permission_description' => 'Create new jobs', 'category' => 'Jobs & Services'],
            ['permission_name' => 'job.edit', 'permission_description' => 'Edit existing jobs', 'category' => 'Jobs & Services'],
            ['permission_name' => 'job.delete', 'permission_description' => 'Delete jobs', 'category' => 'Jobs & Services'],
            ['permission_name' => 'job.approve', 'permission_description' => 'Approve completed jobs (Manager function)', 'category' => 'Jobs & Services'],
            ['permission_name' => 'job.hold', 'permission_description' => 'Put jobs on hold', 'category' => 'Jobs & Services'],
            ['permission_name' => 'job.cancel', 'permission_description' => 'Cancel jobs', 'category' => 'Jobs & Services'],
            ['permission_name' => 'job.invoice', 'permission_description' => 'Generate job invoices', 'category' => 'Jobs & Services'],
            ['permission_name' => 'supplier.view', 'permission_description' => 'View suppliers', 'category' => 'Purchasing'],
            ['permission_name' => 'supplier.create', 'permission_description' => 'Add new suppliers', 'category' => 'Purchasing'],
            ['permission_name' => 'supplier.edit', 'permission_description' => 'Edit suppliers', 'category' => 'Purchasing'],
            ['permission_name' => 'supplier.delete', 'permission_description' => 'Delete suppliers', 'category' => 'Purchasing'],
            ['permission_name' => 'purchase.view', 'permission_description' => 'View purchase orders', 'category' => 'Purchasing'],
            ['permission_name' => 'purchase.create', 'permission_description' => 'Create purchase orders', 'category' => 'Purchasing'],
            ['permission_name' => 'purchase.edit', 'permission_description' => 'Edit purchase orders', 'category' => 'Purchasing'],
            ['permission_name' => 'purchase.delete', 'permission_description' => 'Delete purchase orders', 'category' => 'Purchasing'],
            ['permission_name' => 'stock.view', 'permission_description' => 'View stock levels', 'category' => 'Purchasing'],
            ['permission_name' => 'stock.manage', 'permission_description' => 'Manage stock (add/remove items)', 'category' => 'Purchasing'],
            ['permission_name' => 'products.view', 'permission_description' => 'View products', 'category' => 'Products'],
            ['permission_name' => 'products.create', 'permission_description' => 'Add new products', 'category' => 'Products'],
            ['permission_name' => 'products.edit', 'permission_description' => 'Edit products', 'category' => 'Products'],
            ['permission_name' => 'products.delete', 'permission_description' => 'Delete products', 'category' => 'Products'],
            ['permission_name' => 'payments.view', 'permission_description' => 'View payments', 'category' => 'Finance'],
            ['permission_name' => 'payments.create', 'permission_description' => 'Record payments', 'category' => 'Finance'],
            ['permission_name' => 'payments.edit', 'permission_description' => 'Edit payments', 'category' => 'Finance'],
            ['permission_name' => 'payments.delete', 'permission_description' => 'Delete payments', 'category' => 'Finance'],
            ['permission_name' => 'accounts.inhand', 'permission_description' => 'Manage in-hand cash', 'category' => 'Finance'],
            ['permission_name' => 'accounts.bank', 'permission_description' => 'Manage bank accounts', 'category' => 'Finance'],
            ['permission_name' => 'accounts.banktransfer', 'permission_description' => 'Bank transfers', 'category' => 'Finance'],
            ['permission_name' => 'accounts.cardmachine', 'permission_description' => 'Card machine transactions', 'category' => 'Finance'],
            ['permission_name' => 'accounts.cheque', 'permission_description' => 'Manage cheque deposits', 'category' => 'Finance'],
            ['permission_name' => 'accounts.credits', 'permission_description' => 'Manage credits', 'category' => 'Finance'],
            ['permission_name' => 'accounts.loans', 'permission_description' => 'Manage loans', 'category' => 'Finance'],
            ['permission_name' => 'expenses.view', 'permission_description' => 'View expenses', 'category' => 'Finance'],
            ['permission_name' => 'expenses.create', 'permission_description' => 'Add expenses', 'category' => 'Finance'],
            ['permission_name' => 'expenses.edit', 'permission_description' => 'Edit expenses', 'category' => 'Finance'],
            ['permission_name' => 'expenses.delete', 'permission_description' => 'Delete expenses', 'category' => 'Finance'],
            ['permission_name' => 'serviceitems.view', 'permission_description' => 'View service items', 'category' => 'Service Packages'],
            ['permission_name' => 'serviceitems.create', 'permission_description' => 'Add service items', 'category' => 'Service Packages'],
            ['permission_name' => 'serviceitems.edit', 'permission_description' => 'Edit service items', 'category' => 'Service Packages'],
            ['permission_name' => 'serviceitems.delete', 'permission_description' => 'Delete service items', 'category' => 'Service Packages'],
            ['permission_name' => 'servicepackages.view', 'permission_description' => 'View service packages', 'category' => 'Service Packages'],
            ['permission_name' => 'servicepackages.create', 'permission_description' => 'Create service packages', 'category' => 'Service Packages'],
            ['permission_name' => 'servicepackages.edit', 'permission_description' => 'Edit service packages', 'category' => 'Service Packages'],
            ['permission_name' => 'servicepackages.delete', 'permission_description' => 'Delete service packages', 'category' => 'Service Packages'],
            ['permission_name' => 'employee.view', 'permission_description' => 'View employee list', 'category' => 'Employees'],
            ['permission_name' => 'employee.create', 'permission_description' => 'Add new employees', 'category' => 'Employees'],
            ['permission_name' => 'employee.edit', 'permission_description' => 'Edit employee details', 'category' => 'Employees'],
            ['permission_name' => 'employee.delete', 'permission_description' => 'Delete employees', 'category' => 'Employees'],
            ['permission_name' => 'employee.attendance', 'permission_description' => 'Manage attendance', 'category' => 'Employees'],
            ['permission_name' => 'employee.advance', 'permission_description' => 'Manage employee advances', 'category' => 'Employees'],
            ['permission_name' => 'settings.categories', 'permission_description' => 'Manage item categories', 'category' => 'Settings'],
            ['permission_name' => 'settings.servicecategories', 'permission_description' => 'Manage service item categories', 'category' => 'Settings'],
            ['permission_name' => 'settings.brands', 'permission_description' => 'Manage item brands', 'category' => 'Settings'],
            ['permission_name' => 'settings.vehiclecategories', 'permission_description' => 'Manage vehicle categories', 'category' => 'Settings'],
            ['permission_name' => 'settings.vehiclebrands', 'permission_description' => 'Manage vehicle brands', 'category' => 'Settings'],
            ['permission_name' => 'settings.expensecategories', 'permission_description' => 'Manage expense categories', 'category' => 'Settings'],
            ['permission_name' => 'settings.employeesections', 'permission_description' => 'Manage employee sections', 'category' => 'Settings'],
            ['permission_name' => 'settings.roles', 'permission_description' => 'Manage system roles and permissions', 'category' => 'Settings'],
            ['permission_name' => 'customers.view', 'permission_description' => 'View customers', 'category' => 'CRM'],
            ['permission_name' => 'customers.create', 'permission_description' => 'Add new customers', 'category' => 'CRM'],
            ['permission_name' => 'customers.edit', 'permission_description' => 'Edit customer details', 'category' => 'CRM'],
            ['permission_name' => 'customers.delete', 'permission_description' => 'Delete customers', 'category' => 'CRM'],
            ['permission_name' => 'vehicles.view', 'permission_description' => 'View vehicles', 'category' => 'CRM'],
            ['permission_name' => 'vehicles.create', 'permission_description' => 'Add new vehicles', 'category' => 'CRM'],
            ['permission_name' => 'vehicles.edit', 'permission_description' => 'Edit vehicle details', 'category' => 'CRM'],
            ['permission_name' => 'vehicles.delete', 'permission_description' => 'Delete vehicles', 'category' => 'CRM'],
            ['permission_name' => 'reports.view', 'permission_description' => 'View all reports', 'category' => 'Reports'],
            ['permission_name' => 'reports.financial', 'permission_description' => 'View financial reports', 'category' => 'Reports'],
            ['permission_name' => 'reports.jobs', 'permission_description' => 'View job reports', 'category' => 'Reports'],
            ['permission_name' => 'reports.inventory', 'permission_description' => 'View inventory reports', 'category' => 'Reports'],
            ['permission_name' => 'admin.users', 'permission_description' => 'Manage system users', 'category' => 'Administration'],
            ['permission_name' => 'admin.roles', 'permission_description' => 'Manage roles and permissions', 'category' => 'Administration'],
            ['permission_name' => 'admin.settings', 'permission_description' => 'System-wide settings', 'category' => 'Administration'],
            ['permission_name' => 'admin.logs', 'permission_description' => 'View system logs', 'category' => 'Administration'],
        ];
    }

    private function normalizePermission($definition)
    {
        return [
            'permission_name' => $definition['permission_name'],
            'permission_key' => isset($definition['permission_key']) ? $definition['permission_key'] : $definition['permission_name'],
            'description' => $definition['permission_description'] ?? ($definition['description'] ?? ''),
            'category' => $definition['category'] ?? 'General',
        ];
    }

    public function seedPermissions()
    {
        $definitions = $this->permissionDefinitions();
        $existing = $this->db->get('system_permissions')->result_array();
        $existingMap = [];
        foreach ($existing as $row) {
            $existingMap[$row['permission_name']] = $row;
        }

        $inserted = 0;
        $updated = 0;
        $now = date('Y-m-d H:i:s');

        foreach ($definitions as $definition) {
            $data = $this->normalizePermission($definition);
            if (isset($existingMap[$data['permission_name']])) {
                $this->db->where('permission_id', $existingMap[$data['permission_name']]['permission_id']);
                $data['updated_at'] = $now;
                $this->db->update('system_permissions', $data);
                if ($this->db->affected_rows() > 0) {
                    $updated++;
                }
            } else {
                $data['created_at'] = $now;
                $data['updated_at'] = $now;
                if ($this->db->insert('system_permissions', $data)) {
                    $inserted++;
                }
            }
        }

        return [
            'inserted' => $inserted,
            'updated' => $updated,
            'total' => count($definitions),
        ];
    }

    public function ensurePermissionsExist()
    {
        return $this->seedPermissions();
    }
}
