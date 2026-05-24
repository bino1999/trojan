<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Login extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('encrypt');
        $this->load->model('login_model');
        $this->login_model->ensure_default_user();
        $this->login_model->ensure_default_role();
        $this->login_model->ensure_permissions_exist();
        $this->login_model->ensure_admin_has_all_permissions();
    }

    public function index()
    {
        // If already logged in, redirect to dashboard
        if ($this->session->userdata('userid')) {
            redirect('home');
        }
        $this->load->view('auth/login');
    }

    public function login_validation()
    {
        // Set validation rules
        $this->form_validation->set_rules('email', 'Email', 'trim|required');
        $this->form_validation->set_rules('password', 'Password', 'trim|required');

        if ($this->form_validation->run() == FALSE) {
            // Validation failed
            $this->session->set_flashdata('error', validation_errors());
            redirect('login');
        } else {
            // Attempt login
            $email = $this->input->post('email');
            $password = $this->input->post('password');

            $login_result = $this->login_model->can_login($email, $password);

            if ($login_result === '') {
                // Successful login
                $user = $this->login_model->get_user_details($this->session->userdata('userid'));

                // Check if user needs to change password
                if ($this->login_model->needs_password_change($user->UserID)) {
                    // Update last login even if password change is required
                    $this->login_model->update_last_login($user->UserID);
                    // Redirect to password change page
                    $this->session->set_userdata('userid', $user->UserID);
                    $this->session->set_userdata('temp_login', TRUE);
                    redirect('login/change_password');
                    return;
                }

                $this->load->model('systemRolePermissions_model');
                // Get assigned permissions for the role
                $assignedPermissions = $this->systemRolePermissions_model->getPermissionsByRole($user->Role);
                // Extract only permission ID
                $permissions = array_column($assignedPermissions, 'permission_id');

                // Set user session data
                $session_data = [
                    'userid' => $user->UserID,
                    'email' => $user->Email,
                    'uname' => $user->UserName,
                    'fname' => $user->FirstName,
                    'name' => $user->FirstName . ' ' . $user->LastName,
                    'role' => $user->Role,
                    'department' => $user->Department,
                    'isActive' => $user->IsActive,
                    'logged_in' => TRUE,
                    'permissions' => $permissions
                ];
                $this->session->set_userdata($session_data);

                $this->load->model('logs_model');
                $this->logs_model->log_activity('Login', 'Loged In');
                redirect('home');
            } else {
                // Login failed
                $this->session->set_flashdata('error', $login_result);
                redirect('login');
            }
        }
    }

    public function logout()
    {
        // Destroy session
        $this->session->sess_destroy();

        $this->load->model('logs_model');
        $this->logs_model->log_activity('Signout', 'Loged Out');
        redirect('login');
    }

    public function change_password()
    {
        // Check if user is in temp login state
        if (!$this->session->userdata('userid') || !$this->session->userdata('temp_login')) {
            redirect('login');
            return;
        }

        $this->load->view('auth/change_password');
    }

    public function process_password_change()
    {
        // Check if user is in temp login state
        if (!$this->session->userdata('userid') || !$this->session->userdata('temp_login')) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid session. Please login again.']);
            return;
        }

        $userID = $this->session->userdata('userid');
        
        // Set validation rules
        $this->form_validation->set_rules('new_password', 'New Password', 'required|min_length[6]');
        $this->form_validation->set_rules('confirm_password', 'Confirm Password', 'required|matches[new_password]');
        $this->form_validation->set_rules('new_username', 'New Username', 'alpha_numeric|min_length[3]');

        if ($this->form_validation->run() == FALSE) {
            echo json_encode(['status' => 'error', 'message' => validation_errors()]);
            return;
        }

        $newPassword = $this->input->post('new_password');
        $newUsername = $this->input->post('new_username');

        // Check if username is provided and not empty
        if (!empty($newUsername)) {
            // Check if username already exists
            if ($this->login_model->check_username_exists($newUsername, $userID)) {
                echo json_encode(['status' => 'error', 'message' => 'Username already exists. Please choose a different username.']);
                return;
            }
        }

        // Update password and optionally username
        if ($this->login_model->update_password_change_status($userID, $newPassword, $newUsername)) {
            // Clear temp login session
            $this->session->unset_userdata('temp_login');
            
            // Complete the login process
            $user = $this->login_model->get_user_details($userID);
            
            $this->load->model('systemRolePermissions_model');
            $assignedPermissions = $this->systemRolePermissions_model->getPermissionsByRole($user->Role);
            $permissions = array_column($assignedPermissions, 'permission_id');

            // Set full user session data
            $session_data = [
                'userid' => $user->UserID,
                'email' => $user->Email,
                'uname' => $user->UserName,
                'fname' => $user->FirstName,
                'name' => $user->FirstName . ' ' . $user->LastName,
                'role' => $user->Role,
                'department' => $user->Department,
                'isActive' => $user->IsActive,
                'logged_in' => TRUE,
                'permissions' => $permissions
            ];
            $this->session->set_userdata($session_data);

            $this->load->model('logs_model');
            $this->logs_model->log_activity('Password Change', 'Changed password on first login');
            
            echo json_encode(['status' => 'success', 'message' => 'Password changed successfully. Redirecting to dashboard...']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update password. Please try again.']);
        }
    }


}
