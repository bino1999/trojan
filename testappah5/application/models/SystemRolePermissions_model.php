<?php
defined('BASEPATH') or exit('No direct script access allowed');

class SystemRolePermissions_model extends CI_Model
{

    public function getPermissionsByRole($role_id)
    {
        $this->db->select('*');
        $this->db->where('role_id', $role_id);
        return $this->db->get('system_role_permissions')->result();
    }

    public function getAllPermissions()
    {
        return $this->db->get('system_permissions')->result();
    }

    public function getPermissionIdsByRole($role_id)
    {
        $this->db->select('permission_id');
        $this->db->where('role_id', $role_id);
        $result = $this->db->get('system_role_permissions')->result_array();
        return array_map(function ($row) {
            return (int)$row['permission_id'];
        }, $result);
    }

    public function removeRolePermissions($role_id)
    {
        $this->db->where('role_id', $role_id);
        $this->db->delete('system_role_permissions');
    }

    public function assignPermission($role_id, $permissionKey)
    {
        $data = [
            'role_id' => $role_id,
            'permission_id' => $permissionKey
        ];
        // Prevent duplicate entries
        $this->db->where($data);
        $exists = $this->db->get('system_role_permissions')->row();
        if ($exists) {
            return;
        }
        $this->db->insert('system_role_permissions', $data);
    }
}
