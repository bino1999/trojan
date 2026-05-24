<?php
defined('BASEPATH') or exit('No direct script access allowed');

if (!function_exists('get_ci_instance')) {
	function get_ci_instance()
	{
		$CI = &get_instance();
		return $CI;
	}
}

if (!function_exists('resolve_permission_id')) {
	function resolve_permission_id($permission)
	{
		if (is_numeric($permission)) {
			return (int)$permission;
		}
		$CI = get_ci_instance();
		$CI->load->database();
		$CI->db->select('permission_id');
		$CI->db->from('system_permissions');
		$CI->db->where('permission_name', $permission);
		$query = $CI->db->get();
		$row = $query->row();
		return $row ? (int)$row->permission_id : null;
	}
}

if (!function_exists('has_permission')) {
	function has_permission($permission)
	{
		$CI = get_ci_instance();
		$sessionPermissions = $CI->session->userdata('permissions');
		if (!is_array($sessionPermissions)) {
			return false;
		}
		if (is_array($permission)) {
			foreach ($permission as $perm) {
				$permId = resolve_permission_id($perm);
				if ($permId !== null && in_array($permId, $sessionPermissions, false)) {
					return true;
				}
			}
			return false;
		}
		$permId = resolve_permission_id($permission);
		return $permId !== null && in_array($permId, $sessionPermissions, false);
	}
}

if (!function_exists('require_permission')) {
	function require_permission($permission)
	{
		if (has_permission($permission)) {
			return true;
		}
		$CI = get_ci_instance();
		
		// Get permission description for better error message
		$permissionName = is_array($permission) ? implode(' or ', $permission) : $permission;
		$permissionDesc = '';
		if (!is_array($permission)) {
			$CI->load->database();
			$CI->db->select('description');
			$CI->db->from('system_permissions');
			$CI->db->where('permission_name', $permission);
			$query = $CI->db->get();
			$row = $query->row();
			if ($row) {
				$permissionDesc = $row->description;
			}
		}
		
		// Render 403 page with permission info
		$CI->output->set_status_header(403);
		$data['permission'] = $permissionName;
		$data['permission_desc'] = $permissionDesc;
		$CI->load->view('errors/403', $data);
		$CI->output->_display();
		exit;
	}
}


