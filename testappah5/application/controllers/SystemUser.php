<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class SystemUser extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('settings_model');
    }


    public function manageSystemUser()
    {
        $this->require_permission('admin.users');
        $data['mainMenuName'] = 'employee-create';
        $data['subMenuName'] = 'system-user-manage'; 

        
        $data['results'] = $this->settings_model->loadSystemUsers();

        $data['roles'] = $this->settings_model->loadSystemRole();    
        $data['sections'] = $this->settings_model->loadEmployeeSection();    
        $this->load->view('layout/header');
        $this->load->view('layout/top_navbar'); 
        $this->load->view('layout/left_sidebar', $data);
        $this->load->view('masters/system-user-manage', $data);
        $this->load->view('layout/footer');
    }

    public function employeeList()
    {
        $data['mainMenuName'] = 'Members';
        $data['subMenuName'] = 'employee-list'; 

        $data['results'] = $this->settings_model->loadSystemUsers();
        
        $this->load->view('layout/header');
        $this->load->view('layout/top_navbar'); 
        $this->load->view('layout/left_sidebar', $data);
        $this->load->view('masters/employee-list', $data);
        $this->load->view('layout/footer');
    }

    public function saveSystemUser()
{
    $this->require_permission('admin.users');
    $this->load->library('encrypt');

    // Validate fields
    $this->form_validation->set_rules('userName', 'User Name', 'required|alpha_numeric');
    $this->form_validation->set_rules('firstName', 'First Name', 'required|alpha_numeric_spaces');
    $this->form_validation->set_rules('lastName', 'Last Name', 'required|alpha_numeric_spaces');
    $this->form_validation->set_rules('email', 'Email', 'valid_email');
    $this->form_validation->set_rules('phone', 'Phone Number', 'required|numeric');
    $this->form_validation->set_rules('password', 'Password', 'required|min_length[6]');
    $this->form_validation->set_rules('password2', 'Confirm Password', 'required|matches[password]');
    $this->form_validation->set_rules('role', 'Role', 'required|numeric|greater_than[0]',['greater_than' => 'Please select a valid Role.']);
    $this->form_validation->set_rules('section', 'Department', 'required|numeric|greater_than[0]',['greater_than' => 'Please select a valid Department.']);
    $this->form_validation->set_rules('hireDate', 'Hire Date', 'required');
    $this->form_validation->set_rules('isActive', 'Is Active', 'required|in_list[0,1]');

    if ($this->form_validation->run() == FALSE) {
        echo json_encode(['status' => 'error', 'message' => validation_errors()]);
        return;
    }

    // Check if UserName already exists
    $userName = ucfirst(strtolower(trim($this->input->post('userName'))));
    $email = trim($this->input->post('email'));

    $this->db->where('UserName', $userName);
    $userExist = $this->db->get('users')->row();

    if ($userExist) {
        echo json_encode(['status' => 'error', 'message' => 'User Name already exists.']);
        return;
    }

    // Check if Email already exists (only if email is provided)
    if (!empty($email)) {
        $this->db->where('Email', $email);
        $emailExist = $this->db->get('users')->row();

        if ($emailExist) {
            echo json_encode(['status' => 'error', 'message' => 'Email already exists.']);
            return;
        }
    }

    $encrypted_password = $this->encrypt->encode($this->input->post('password'));
    $data = [
        'UserName' => ucfirst(strtolower(trim($this->input->post('userName')))),
        'FirstName' => $this->input->post('firstName'),
        'LastName' => $this->input->post('lastName'),
        'Email' => !empty($email) ? $email : null,
        'PhoneNumber' => $this->input->post('phone'),
        'Password' => $encrypted_password,
        'Role' => $this->input->post('role'),
        'EmployeeID' => $this->input->post('employeeID'),
        'Department' => $this->input->post('section'),
        'HireDate' => $this->input->post('hireDate'),
        'IsActive' => $this->input->post('isActive'),
        'salary' => $this->input->post('salary') ? $this->input->post('salary') : null,
        'password_changed' => 0, // New users must change password on first login
        'CreatedAt' => date('Y-m-d H:i:s'),
        'UpdatedAt' => date('Y-m-d H:i:s')
    ];

    // Save to database
    if ($this->db->insert('users', $data)) {
        echo json_encode(['status' => 'success', 'message' => 'User successfully created.']);
        $this->load->model('logs_model');
        $logMessage = 'User created: ' . $userName;
        if (!empty($email)) {
            $logMessage .= ' (Email: ' . $email . ')';
        }
        $this->logs_model->log_activity('User Create', $logMessage);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to save user. Please try again.']);
    }
}



public function updateSystemUser() {
    $this->require_permission('admin.users');
    // Get the POST data
    $userId = $this->input->post('userId');
    $userName = $this->input->post('userName');
    $firstName = $this->input->post('firstName');
    $lastName = $this->input->post('lastName');
    $email = $this->input->post('email');
    $phone = $this->input->post('phone');
    $employeeID = $this->input->post('employeeID');
    $role = $this->input->post('role');
    $section = $this->input->post('section');
    $hireDate = $this->input->post('hireDate');
    $isActive = $this->input->post('isActive');

    // Validate required fields (Optional: you can add more validation rules here)
    if (!$userName || !$firstName || !$lastName || !$email || !$phone) {
        echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
        return;
    }

    if ($this->settings_model->checkUserEmailExists($email, $userId)) {
        echo json_encode(['status' => 'error', 'message' => 'The email address is already in use by another user.']);
        return;
    }
   
    $data = [
        'FirstName' => $firstName,
        'LastName' => $lastName,
        'Email' => $email,
        'PhoneNumber' => $phone,
        'EmployeeID' => $employeeID,
        'Role' => $role,
        'Department' => $section,
        'HireDate' => $hireDate,
        'IsActive' => $isActive,
        'salary' => $this->input->post('salary') ? $this->input->post('salary') : null,
        'UpdatedAt' => date('Y-m-d H:i:s')  // Set the update timestamp
    ];

    $this->db->where('UserID', $userId);
    $updateStatus = $this->db->update('users', $data);

    if ($updateStatus) {
        echo json_encode(['status' => 'success', 'message' => 'User details updated successfully.']);
        $this->load->model('logs_model');
        $this->logs_model->log_activity('User Update', 'User Email: '.$email);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update user. Please try again.']);
    }
}



    public function setTemporaryPassword()
    {
        $this->load->library('encrypt');
        $userId = $this->input->post('user_id');
        $password = $this->input->post('password');
        $confirm = $this->input->post('confirm_password');
        $forceChange = $this->input->post('force_change');

        if (empty($userId) || empty($password) || empty($confirm)) {
            echo json_encode(['status' => 'error', 'message' => 'User ID and passwords are required.']);
            return;
        }
        if ($password !== $confirm) {
            echo json_encode(['status' => 'error', 'message' => 'Passwords do not match.']);
            return;
        }
        if (strlen($password) < 6) {
            echo json_encode(['status' => 'error', 'message' => 'Password must be at least 6 characters.']);
            return;
        }

        $encrypted_password = $this->encrypt->encode($password);
        $data = [
            'Password' => $encrypted_password,
            'UpdatedAt' => date('Y-m-d H:i:s')
        ];

        // By default, temp password should force change on next login
        $force = ($forceChange === null || $forceChange === '' || $forceChange === '1' || $forceChange === 1);
        $data['password_changed'] = $force ? 0 : 1;
        if (!$force) {
            $data['last_password_change'] = date('Y-m-d H:i:s');
        }

        $this->db->where('UserID', $userId);
        $ok = $this->db->update('users', $data);
        if ($ok) {
            $this->load->model('logs_model');
            $this->logs_model->log_activity('Admin Password Reset', 'UserID: '.$userId);
            echo json_encode(['status' => 'success', 'message' => 'Temporary password set successfully.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to set temporary password.']);
        }
    }

    public function deleteSystemUser()
    {
        $this->require_permission('admin.users');
        $userId = $this->input->post('userId');
        
        if (empty($userId)) {
            echo json_encode(['status' => 'error', 'message' => 'User ID is required.']);
            return;
        }

        // Prevent deleting yourself
        $currentUserId = $this->session->userdata('userid');
        if ($userId == $currentUserId) {
            echo json_encode(['status' => 'error', 'message' => 'You cannot delete your own account.']);
            return;
        }

        // Check if user exists
        $this->db->where('UserID', $userId);
        $user = $this->db->get('users')->row();
        
        if (!$user) {
            echo json_encode(['status' => 'error', 'message' => 'User not found.']);
            return;
        }

        // Start transaction
        $this->db->trans_start();
        
        try {
            // Delete the user
            $this->db->where('UserID', $userId);
            $this->db->delete('users');
            
            $this->db->trans_complete();
            
            if ($this->db->trans_status() === FALSE) {
                throw new Exception('Error deleting user');
            }
            
            $this->load->model('logs_model');
            $logMessage = 'User permanently deleted: ' . $user->UserName;
            if (!empty($user->Email)) {
                $logMessage .= ' (Email: ' . $user->Email . ')';
            }
            $this->logs_model->log_activity('User Permanently Deleted', $logMessage);
            
            echo json_encode(['status' => 'success', 'message' => 'User has been permanently deleted!']);
        } catch (Exception $e) {
            $this->db->trans_rollback();
            log_message('error', 'Error deleting user: ' . $e->getMessage());
            echo json_encode(['status' => 'error', 'message' => 'Failed to delete user. Please try again.']);
        }
    }

    public function toggleUserStatus()
    {
        $this->require_permission('admin.users');
        $userId = $this->input->post('userId');
        
        if (empty($userId)) {
            echo json_encode(['status' => 'error', 'message' => 'User ID is required.']);
            return;
        }

        // Prevent disabling yourself
        $currentUserId = $this->session->userdata('userid');
        if ($userId == $currentUserId) {
            echo json_encode(['status' => 'error', 'message' => 'You cannot disable your own account.']);
            return;
        }

        // Get current user status
        $this->db->where('UserID', $userId);
        $user = $this->db->get('users')->row();
        
        if (!$user) {
            echo json_encode(['status' => 'error', 'message' => 'User not found.']);
            return;
        }

        // Toggle status
        $newStatus = $user->IsActive == 1 ? 0 : 1;
        $statusText = $newStatus == 1 ? 'enabled' : 'disabled';
        
        $data = [
            'IsActive' => $newStatus,
            'UpdatedAt' => date('Y-m-d H:i:s')
        ];
        
        $this->db->where('UserID', $userId);
        $updateStatus = $this->db->update('users', $data);
        
        if ($updateStatus) {
            $this->load->model('logs_model');
            $logMessage = 'User ' . $statusText . ': ' . $user->UserName;
            if (!empty($user->Email)) {
                $logMessage .= ' (Email: ' . $user->Email . ')';
            }
            $this->logs_model->log_activity('User ' . ucfirst($statusText), $logMessage);
            
            echo json_encode([
                'status' => 'success', 
                'message' => 'User has been ' . $statusText . '!',
                'newStatus' => $newStatus
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update user status. Please try again.']);
        }
    }

    public function profile()
    {
        $data['mainMenuName'] = 'Profile';
        $data['subMenuName'] = 'profile';

        // Load minimal current user info from session and DB
        $userId = $this->session->userdata('userid');
        $data['user'] = null;
        if ($userId) {
            $this->db->where('UserID', $userId);
            $data['user'] = $this->db->get('users')->row();
        }

        $this->load->view('layout/header');
        $this->load->view('layout/top_navbar');
        $this->load->view('layout/left_sidebar', $data);
        $this->load->view('profile/index', $data);
        $this->load->view('layout/footer');
    }

}
?>