<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Advance extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('advance_model');
    }

    public function index()
    {
        $this->require_permission('employee.advance');
        $data['mainMenuName'] = 'Members';
        $data['subMenuName'] = 'advance';
        
        // Get current year and month
        $year = $this->input->get('year') ?: date('Y');
        $month = $this->input->get('month') ?: date('n');
        
        $data['year'] = $year;
        $data['month'] = $month;
        $data['users'] = $this->advance_model->getUsersWithAdvanceData($year, $month);
        $data['summary'] = $this->advance_model->getAdvanceSummary($year, $month);
        
        $this->load->view('layout/header');
        $this->load->view('layout/top_navbar'); 
        $this->load->view('layout/left_sidebar', $data);
        $this->load->view('masters/advance', $data);
        $this->load->view('layout/footer');
    }

    public function getAdvanceData()
    {
        $this->require_permission('employee.advance');
        $year = $this->input->post('year');
        $month = $this->input->post('month');

        if (!$year || !$month) {
            echo json_encode(['status' => 'error', 'message' => 'Missing year or month']);
            return;
        }

        $users = $this->advance_model->getUsersWithAdvanceData($year, $month);
        $summary = $this->advance_model->getAdvanceSummary($year, $month);

        echo json_encode([
            'status' => 'success',
            'users' => $users,
            'summary' => $summary
        ]);
    }

    // Test method to debug issues
    public function test()
    {
        $this->require_permission('employee.advance');
        try {
            echo "Advance controller is working<br>";
            
            // Test database connection
            $this->db->select('COUNT(*) as count');
            $this->db->from('users');
            $result = $this->db->get()->row();
            echo "Users table accessible: " . ($result ? $result->count : 'Error') . "<br>";
            
            // Test if salary column exists
            $this->db->select('salary');
            $this->db->from('users');
            $this->db->limit(1);
            $salaryTest = $this->db->get();
            echo "Salary column exists: " . ($salaryTest ? 'Yes' : 'No') . "<br>";
            
            // Test attendance table
            if ($this->db->table_exists('attendance')) {
                echo "Attendance table exists: Yes<br>";
            } else {
                echo "Attendance table exists: No<br>";
            }
            
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }
}
