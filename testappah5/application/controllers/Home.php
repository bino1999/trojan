<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends MY_Controller {
    public function __construct()
    {
        parent::__construct();
        $this->load->model('dashboard_model');
    }

    public function index()
{
    $this->require_permission('dashboard.view');
    
    $data['mainMenuName'] = 'dashboard';
    $data['subMenuName'] = 'dashboard'; 

    // Load basic dashboard data
    try {
        $dashboardData = $this->dashboard_model->loadDashboardData();
        $data = array_merge($data, $dashboardData);
    } catch (Exception $e) {
        // If there's an error, set default values
        $data['vehicle_count'] = 0;
        $data['customer_count'] = 0;
        $data['job_count'] = 0;
        $data['total_revenue'] = 0;
    }

$this->load->view('layout/header');
$this->load->view('layout/top_navbar');
$this->load->view('layout/left_sidebar', $data);
$this->load->view('dashboard', $data);  
$this->load->view('layout/footer');

}

}
?>