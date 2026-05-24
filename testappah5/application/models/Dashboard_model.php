<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Dashboard_model extends CI_Model
{

    public function loadVehicleCount()
    {
        try {
            if (!$this->db->table_exists('vehicles')) {
                return 0;
            }
        $this->db->select('COUNT(*) AS vehicle_count');
        $this->db->from('vehicles');
        
        $query = $this->db->get();
        $result = $query->row();
    
            return $result ? $result->vehicle_count : 0;
        } catch (Exception $e) {
            return 0;
        }
    }

public function loadCustomerCount($customers_id = null)
{
        try {
            if (!$this->db->table_exists('customers')) {
                return 0;
            }
    $this->db->from('customers');
    
    if ($customers_id > 0) {
        $this->db->where('customers_id', $customers_id);
    }

    return $this->db->count_all_results(); // Returns the row count
        } catch (Exception $e) {
            return 0;
        }
    }

    public function loadJobCount()
    {
        try {
            if (!$this->db->table_exists('services_job')) {
                return 0;
            }
            $this->db->from('services_job');
            return $this->db->count_all_results();
        } catch (Exception $e) {
            return 0;
        }
    }

    public function loadTodayJobCount()
    {
        if (!$this->db->table_exists('services_job')) {
            return 0;
        }
        $this->db->from('services_job');
        $this->db->where('DATE(created_at)', date('Y-m-d'));
        return $this->db->count_all_results();
    }

    public function loadTotalRevenue()
    {
        try {
            if (!$this->db->table_exists('services_job_payments')) {
                return 0;
            }
            
            // Try different column names that might exist
            $this->db->select('COALESCE(SUM(amount), 0) as total_revenue');
            $this->db->from('services_job_payments');
            
            // Try different ways to check for deleted records
            $this->db->where('(deleted_by IS NULL OR deleted_by = 0)');
            
            $query = $this->db->get();
            $result = $query->row();
            return $result ? $result->total_revenue : 0;
        } catch (Exception $e) {
            // If the query fails, try a simpler version
            try {
                $this->db->select('SUM(amount) as total_revenue');
                $this->db->from('services_job_payments');
                $query = $this->db->get();
                $result = $query->row();
                return $result && $result->total_revenue ? $result->total_revenue : 0;
            } catch (Exception $e2) {
                return 0;
            }
        }
    }

    public function loadTodayRevenue()
    {
        $this->db->select('COALESCE(SUM(amount), 0) as today_revenue');
        $this->db->from('services_job_payments');
        $this->db->where('DATE(created_at)', date('Y-m-d'));
        $this->db->where('deleted_by IS NULL');
        $query = $this->db->get();
        $result = $query->row();
        return $result->today_revenue;
    }

    public function loadMonthlyRevenue()
    {
        $this->db->select('COALESCE(SUM(amount), 0) as monthly_revenue');
        $this->db->from('services_job_payments');
        $this->db->where('MONTH(created_at)', date('n'));
        $this->db->where('YEAR(created_at)', date('Y'));
        $this->db->where('deleted_by IS NULL');
        $query = $this->db->get();
        $result = $query->row();
        return $result->monthly_revenue;
    }

    public function loadTotalExpenses()
    {
        $this->db->select('COALESCE(SUM(amount), 0) as total_expenses');
        $this->db->from('expenses');
        $query = $this->db->get();
        $result = $query->row();
        return $result->total_expenses;
    }

    public function loadTodayExpenses()
    {
        $this->db->select('COALESCE(SUM(amount), 0) as today_expenses');
        $this->db->from('expenses');
        $this->db->where('DATE(expense_date)', date('Y-m-d'));
        $query = $this->db->get();
        $result = $query->row();
        return $result->today_expenses;
    }

    public function loadMonthlyExpenses()
    {
        $this->db->select('COALESCE(SUM(amount), 0) as monthly_expenses');
        $this->db->from('expenses');
        $this->db->where('MONTH(expense_date)', date('n'));
        $this->db->where('YEAR(expense_date)', date('Y'));
        $query = $this->db->get();
        $result = $query->row();
        return $result->monthly_expenses;
    }

    public function loadActiveUsers()
    {
        $this->db->from('users');
        $this->db->where('IsActive', 1);
        return $this->db->count_all_results();
    }

    public function loadRecentJobs($limit = 5)
    {
        $this->db->select('sj.*, c.customerName, c.customerPhone');
        $this->db->from('services_job sj');
        $this->db->join('customers c', 'c.customers_id = sj.customer_id', 'left');
        $this->db->order_by('sj.created_at', 'DESC');
        $this->db->limit($limit);
        return $this->db->get()->result();
    }

    public function loadRecentPayments($limit = 5)
    {
        $this->db->select('sjp.*, sj.job_number, c.customerName');
        $this->db->from('services_job_payments sjp');
        $this->db->join('services_job sj', 'sj.id = sjp.service_job_id', 'left');
        $this->db->join('customers c', 'c.customers_id = sj.customer_id', 'left');
        $this->db->where('sjp.deleted_by IS NULL');
        $this->db->order_by('sjp.created_at', 'DESC');
        $this->db->limit($limit);
        return $this->db->get()->result();
    }

    public function loadRecentExpenses($limit = 5)
    {
        $this->db->select('e.*, ec.expensesCategoryName, u.UserName');
        $this->db->from('expenses e');
        $this->db->join('expenses_categories ec', 'ec.expensesCategoryId = e.category_id', 'left');
        $this->db->join('users u', 'u.UserID = e.created_by', 'left');
        $this->db->order_by('e.expense_date', 'DESC');
        $this->db->limit($limit);
        return $this->db->get()->result();
    }

    public function loadRevenueChartData($days = 30)
    {
        $this->db->select('DATE(created_at) as date, SUM(amount) as revenue');
        $this->db->from('services_job_payments');
        $this->db->where('created_at >=', date('Y-m-d', strtotime("-{$days} days")));
        $this->db->where('deleted_by IS NULL');
        $this->db->group_by('DATE(created_at)');
        $this->db->order_by('date', 'ASC');
        return $this->db->get()->result();
    }

    public function loadExpenseChartData($days = 30)
    {
        $this->db->select('DATE(expense_date) as date, SUM(amount) as expenses');
        $this->db->from('expenses');
        $this->db->where('expense_date >=', date('Y-m-d', strtotime("-{$days} days")));
        $this->db->group_by('DATE(expense_date)');
        $this->db->order_by('date', 'ASC');
        return $this->db->get()->result();
    }

    public function loadPaymentMethodStats()
    {
        $this->db->select('payment_method, COUNT(*) as count, SUM(amount) as total');
        $this->db->from('services_job_payments');
        $this->db->where('deleted_by IS NULL');
        $this->db->where('created_at >=', date('Y-m-d', strtotime('-30 days')));
        $this->db->group_by('payment_method');
        return $this->db->get()->result();
    }

    public function loadJobStatusStats()
    {
        $this->db->select('status, COUNT(*) as count');
        $this->db->from('services_job');
        $this->db->group_by('status');
        return $this->db->get()->result();
    }

    public function loadWalletBalances()
    {
        $this->db->select('*');
        $this->db->from('accounts');
        $this->db->order_by('name', 'ASC');
        return $this->db->get()->result();
    }

    public function loadAttendanceStats()
    {
        $today = date('Y-m-d');
        $this->db->select('COUNT(*) as total_users');
        $this->db->from('users');
        $this->db->where('IsActive', 1);
        $totalUsers = $this->db->get()->row()->total_users;

        $this->db->select('COUNT(*) as present_today');
        $this->db->from('attendance');
        $this->db->where('attendance_date', $today);
        $this->db->where('is_present', 1);
        $presentToday = $this->db->get()->row()->present_today;

        return [
            'total_users' => $totalUsers,
            'present_today' => $presentToday,
            'absent_today' => $totalUsers - $presentToday,
            'attendance_percentage' => $totalUsers > 0 ? round(($presentToday / $totalUsers) * 100, 2) : 0
        ];
    }

    public function loadAdvanceSummary()
    {
        $this->load->model('Advance_model');
        return $this->Advance_model->getAdvanceSummary();
    }

    public function loadDashboardData()
    {
        // Back to working version - skip revenue for now
        return [
            'vehicle_count' => $this->loadVehicleCount(),
            'customer_count' => $this->loadCustomerCount(),
            'job_count' => $this->loadJobCount(),
            'total_revenue' => 0 // Keep static until we fix the revenue method
        ];
    }
}