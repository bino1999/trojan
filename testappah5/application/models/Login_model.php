<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Login_model extends CI_Model {

    public function ensure_default_user()
    {
        $query = $this->db->get('users');
        if ($query->num_rows() === 0) {
            $default_password = '123';
            $encrypted_password = $this->encrypt->encode($default_password);

            $data = [
                'UserName'   => 'Admin',
                'FirstName'  => 'Sudath',
                'LastName'   => 'Bandara',
                'Email'      => 'usjpsudath@gmail.com',
                'PhoneNumber'   => '0711381628',
                'Password'   => $encrypted_password,
                'Role'       => 1,
                'EmployeeID'   => '0',
                'Department'   => 0,
                'HireDate'   => date('Y-m-d'),
                'IsActive'   => 1,
                'password_changed' => 1, // Default admin user doesn't need to change password
                'CreatedAt'  => date('Y-m-d H:i:s')
            ];
            $this->db->insert('users', $data);
        }
    }

    public function ensure_default_role()
    {
        $query = $this->db->get('system_roles');
        if ($query->num_rows() === 0) {

            $data = [
                'system_role_name'   => 'ADMIN',
                'is_deleted'   => 0,
                'created_at'  => date('Y-m-d H:i:s')
            ];
            $this->db->insert('system_roles', $data);
        }
    }

    public function ensure_permissions_exist()
    {
        $this->load->model('SystemPermissions_model');
        $result = $this->SystemPermissions_model->ensurePermissionsExist();
        return $result;
    }

    public function ensure_admin_has_all_permissions()
    {
        $this->load->model('SystemPermissions_model');
        $this->load->model('SystemRolePermissions_model');

        $allPermissions = $this->SystemPermissions_model->getAllPermissions();
        if (empty($allPermissions)) {
            return 0;
        }

        $allPermissionIds = array_map(function ($permission) {
            return (int)$permission->permission_id;
        }, $allPermissions);

        $existingIds = $this->SystemRolePermissions_model->getPermissionIdsByRole(1);
        $missingIds = array_diff($allPermissionIds, $existingIds);

        foreach ($missingIds as $permissionId) {
            $this->SystemRolePermissions_model->assignPermission(1, $permissionId);
        }

        return count($missingIds);
    }

    public function can_login($email, $password)
    {
        if (empty($email) || empty($password)) {
            return 'Email and password are required.';
        }

        // Query to find user by email
        $this->db->where('Email', $email);
        $this->db->or_where('UserName', $email);
        $query = $this->db->get('users');

        if ($query->num_rows() > 0) {
            $user = $query->row();

            // Check if account is active
            if ($user->IsActive == 0) {
                return 'Your account has been disabled. Please contact the system administrator.';
            }

            // Verify password. Fallback: if decode fails (different key in dump), accept raw match
            $decoded_password = $this->encrypt->decode($user->Password);
            if ($decoded_password === $password || $user->Password === $password) {
                // Store user ID in session
                $this->session->set_userdata('userid', $user->UserID);
                
                // Track first login if this is the first time
                $this->track_first_login($user->UserID);
                
                return '';  // Empty string indicates successful login
            } else {
                return 'Invalid email or password. Please try again.';
            }
        } else {
            return 'User not found. Please check your email.';
        }
    }

    public function get_user_details($UserID)
    {
        $this->db->select('*');
        $this->db->from('users');
        $this->db->where('UserID', $UserID);
        $query = $this->db->get();
        return $query->row();
    }

    public function find_userByName($UserName = null)
    {
        $this->db->select('*');
        $this->db->from('users');
        if ($UserName) {
            $this->db->where('UserName', $UserName);
        }
        $this->db->order_by('UserName', 'ASC');
        $query = $this->db->get();
        return $query->result();
    }

    public function changeMyPassword($UserID, $password)
    {
        $UserID = $this->session->userdata('userid');
        $password = $this->encrypt->encode($password);
        $data = array(
        'password'    => trim($password),
    );
        $this->db->where('UserID', $UserID);
        $this->db->update('users', $data);
        if ($this->db->affected_rows() == '1') {
        return 1;
    } else {
        return 0;
    }
    }

    public function track_first_login($userID)
    {
        // Check if this is the first login
        $this->db->where('UserID', $userID);
        $this->db->where('first_login_date IS NULL');
        $query = $this->db->get('users');
        
        if ($query->num_rows() > 0) {
            // This is the first login, record it
            $this->db->where('UserID', $userID);
            $this->db->update('users', array('first_login_date' => date('Y-m-d H:i:s')));
        }
        
        // Always update last login date
        $this->update_last_login($userID);
    }

    public function update_last_login($userID)
    {
        $this->db->where('UserID', $userID);
        $this->db->update('users', array('last_login_date' => date('Y-m-d H:i:s')));
    }

    public function needs_password_change($userID)
    {
        $this->db->select('password_changed');
        $this->db->where('UserID', $userID);
        $query = $this->db->get('users');
        
        if ($query->num_rows() > 0) {
            $user = $query->row();
            return $user->password_changed == 0; // Return true if password hasn't been changed
        }
        return false;
    }

    public function update_password_change_status($userID, $newPassword, $newUsername = null)
    {
        $data = array(
            'password_changed' => 1,
            'last_password_change' => date('Y-m-d H:i:s'),
            'password' => $this->encrypt->encode($newPassword)
        );
        
        // Optionally update username if provided
        if ($newUsername && !empty($newUsername)) {
            $data['UserName'] = $newUsername;
        }
        
        $this->db->where('UserID', $userID);
        $this->db->update('users', $data);
        
        return $this->db->affected_rows() > 0;
    }

    public function check_username_exists($username, $excludeUserID = null)
    {
        $this->db->where('UserName', $username);
        if ($excludeUserID) {
            $this->db->where('UserID !=', $excludeUserID);
        }
        $query = $this->db->get('users');
        return $query->num_rows() > 0;
    }    

}
?>