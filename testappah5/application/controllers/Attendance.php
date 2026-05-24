<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Attendance extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('attendance_model');
    }

    public function index()
    {
        $this->require_permission('employee.attendance');
        $data['mainMenuName'] = 'Members';
        $data['subMenuName'] = 'attendance';
        
        $data['users'] = $this->attendance_model->getUsers();
        
        $this->load->view('layout/header');
        $this->load->view('layout/top_navbar'); 
        $this->load->view('layout/left_sidebar', $data);
        // Load the new bulk attendance grid by default
        $this->load->view('masters/attendance_bulk', $data);
        $this->load->view('layout/footer');
    }

    public function bulk()
    {
        $this->require_permission('employee.attendance');
        $data['mainMenuName'] = 'Members';
        $data['subMenuName'] = 'attendance';
        $data['users'] = $this->attendance_model->getUsers();

        $this->load->view('layout/header');
        $this->load->view('layout/top_navbar'); 
        $this->load->view('layout/left_sidebar', $data);
        $this->load->view('masters/attendance_bulk', $data);
        $this->load->view('layout/footer');
    }

    public function getAttendanceData()
    {
        $this->require_permission('employee.attendance');
        $userId = $this->input->post('user_id');
        $year = $this->input->post('year');
        $month = $this->input->post('month');

        if (!$userId || !$year || !$month) {
            echo json_encode(['status' => 'error', 'message' => 'Missing required parameters']);
            return;
        }

        $attendance = $this->attendance_model->getAttendanceByUserAndMonth($userId, $year, $month);
        $summary = $this->attendance_model->getAttendanceSummaryWithOffDays($userId, $year, $month);
        $presentDates = $this->attendance_model->getPresentDates($userId, $year, $month);
        $offDays = $this->attendance_model->getAllOffDays($userId, $year, $month);

        // Get user details
        $this->db->select('FirstName, LastName, UserName');
        $this->db->from('users');
        $this->db->where('UserID', $userId);
        $user = $this->db->get()->row();

        echo json_encode([
            'status' => 'success',
            'attendance' => $attendance,
            'summary' => $summary,
            'presentDates' => $presentDates,
            'offDays' => $offDays,
            'user' => $user
        ]);
    }

    public function saveAttendance()
    {
        $this->require_permission('employee.attendance');
        $userId = $this->input->post('user_id');
        $date = $this->input->post('date');
        $isPresent = $this->input->post('is_present');

        if (!$userId || !$date) {
            echo json_encode(['status' => 'error', 'message' => 'Missing required parameters']);
            return;
        }

        $result = $this->attendance_model->saveAttendance($userId, $date, $isPresent);

        if ($result) {
            echo json_encode(['status' => 'success', 'message' => 'Attendance saved successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to save attendance']);
        }
    }

    public function getDaysInMonth()
    {
        $this->require_permission('employee.attendance');
        $year = $this->input->post('year');
        $month = $this->input->post('month');

        if (empty($year) || empty($month)) {
            echo json_encode(['status' => 'error', 'message' => 'Missing year or month']);
            return;
        }

        $year = (int)$year;
        $month = (int)$month;

        // Simple validation
        if ($month < 1 || $month > 12) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid month']);
            return;
        }

        // Get days in month using a simple approach
        $daysInMonth = date('t', strtotime($year . '-' . $month . '-01'));
        
        echo json_encode([
            'status' => 'success',
            'days' => (int)$daysInMonth
        ]);
    }

    public function getMonthlyGrid()
    {
        $this->require_permission('employee.attendance');
        $year = (int)$this->input->post('year');
        $month = (int)$this->input->post('month');

        if (!$year || !$month) {
            echo json_encode(['status' => 'error', 'message' => 'Missing year or month']);
            return;
        }

        $data = $this->attendance_model->getMonthlyAttendanceForAll($year, $month);
        $daysInMonth = (int)date('t', strtotime($year . '-' . $month . '-01'));

        echo json_encode([
            'status' => 'success',
            'daysInMonth' => $daysInMonth,
            'data' => $data
        ]);
    }

    public function saveMonthlyGrid()
    {
        $this->require_permission('employee.attendance');
        $payload = $this->input->post('records');
        if (!$payload) {
            echo json_encode(['status' => 'error', 'message' => 'No records provided']);
            return;
        }
        $records = json_decode($payload, true);
        if (!is_array($records)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid records payload']);
            return;
        }

        $ok = $this->attendance_model->saveAttendanceBatch($records);
        if ($ok) {
            echo json_encode(['status' => 'success', 'message' => 'Attendance updated']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to save attendance']);
        }
    }

    // Off Days Management Methods
    public function addOffDay()
    {
        $this->require_permission('employee.attendance');
        $date = $this->input->post('date');
        $description = $this->input->post('description');
        $isCompanyHoliday = $this->input->post('is_company_holiday') == '1';
        $userId = $this->input->post('user_id');

        if (!$date) {
            echo json_encode(['status' => 'error', 'message' => 'Date is required']);
            return;
        }

        if (!$isCompanyHoliday && !$userId) {
            echo json_encode(['status' => 'error', 'message' => 'User ID is required for personal off days']);
            return;
        }

        $result = $this->attendance_model->addOffDay($date, $description, $isCompanyHoliday, $userId);

        if ($result) {
            echo json_encode(['status' => 'success', 'message' => 'Off day added successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to add off day']);
        }
    }

    public function removeOffDay()
    {
        $this->require_permission('employee.attendance');
        $date = $this->input->post('date');
        $userId = $this->input->post('user_id');

        if (!$date) {
            echo json_encode(['status' => 'error', 'message' => 'Date is required']);
            return;
        }

        $result = $this->attendance_model->removeOffDay($date, $userId);

        if ($result) {
            echo json_encode(['status' => 'success', 'message' => 'Off day removed successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to remove off day']);
        }
    }

    public function getOffDays()
    {
        $this->require_permission('employee.attendance');
        $year = $this->input->post('year');
        $month = $this->input->post('month');
        $userId = $this->input->post('user_id');

        if (!$year || !$month) {
            echo json_encode(['status' => 'error', 'message' => 'Year and month are required']);
            return;
        }

        $offDays = $this->attendance_model->getAllOffDays($userId, $year, $month);

        echo json_encode([
            'status' => 'success',
            'offDays' => $offDays
        ]);
    }

    // Test method to verify controller is working
    public function test()
    {
        $this->require_permission('employee.attendance');
        echo json_encode(['status' => 'success', 'message' => 'Controller is working']);
    }
}
