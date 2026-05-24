<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Advance_model extends CI_Model
{
    public function getUsersWithAdvanceData($year = null, $month = null)
    {
        try {
            // Use current year/month if not provided
            if (!$year) $year = date('Y');
            if (!$month) $month = date('n');

            $this->db->select('u.UserID, u.FirstName, u.LastName, u.UserName, u.salary, 
                              sr.system_role_name, es.employeeSectionName');
            $this->db->from('users u');
            $this->db->join('system_roles sr', 'sr.system_role_id = u.Role', 'left');
            $this->db->join('employee_sections es', 'es.employeeSectionId = u.Department', 'left');
            $this->db->where('u.IsActive', 1);
            $this->db->where('u.salary IS NOT NULL');
            $this->db->where('u.salary >', 0);
            $this->db->order_by('u.FirstName', 'ASC');
            
            $users = $this->db->get()->result();
            
            $result = [];
            foreach ($users as $user) {
                $advanceData = $this->calculateAdvanceForUser($user, $year, $month);
                $result[] = (object) array_merge((array) $user, $advanceData);
            }
            
            return $result;
        } catch (Exception $e) {
            error_log("Advance_model getUsersWithAdvanceData error: " . $e->getMessage());
            return [];
        }
    }

    public function calculateAdvanceForUser($user, $year, $month)
    {
        try {
            // Get total days in month
            $totalDaysInMonth = date('t', strtotime($year . '-' . $month . '-01'));
            
            // Get working days (excluding off days)
            $workingDays = $this->getWorkingDaysForUser($user->UserID, $year, $month);
            
            // Calculate maximum allowed advance (40% of salary)
            $maxAllowedAdvance = $user->salary * 0.4;
            
            // Calculate per day salary based on working days
            $perDaySalary = $workingDays > 0 ? $maxAllowedAdvance / $workingDays : 0;
            
            // Get present days for the month
            $presentDays = $this->getPresentDaysForUser($user->UserID, $year, $month);
            
            // Calculate actual allowed advance
            $actualAllowedAdvance = $perDaySalary * $presentDays;
            
            // Get total advance taken (if advance table exists)
            $totalAdvanceTaken = $this->getTotalAdvanceTaken($user->UserID, $year, $month);
            
            // Calculate remaining advance
            $remainingAdvance = max(0, $actualAllowedAdvance - $totalAdvanceTaken);
            
            // Get off days count for display
            $offDays = $this->getOffDaysForUser($user->UserID, $year, $month);
            $offDaysCount = count($offDays);
            
            return [
                'max_allowed_advance' => round($maxAllowedAdvance, 2),
                'per_day_salary' => round($perDaySalary, 2),
                'present_days' => $presentDays,
                'total_days_in_month' => $totalDaysInMonth,
                'working_days' => $workingDays,
                'off_days' => $offDaysCount,
                'actual_allowed_advance' => round($actualAllowedAdvance, 2),
                'total_advance_taken' => round($totalAdvanceTaken, 2),
                'remaining_advance' => round($remainingAdvance, 2),
                'attendance_percentage' => $workingDays > 0 ? round(($presentDays / $workingDays) * 100, 2) : 0
            ];
        } catch (Exception $e) {
            error_log("Advance_model calculateAdvanceForUser error: " . $e->getMessage());
            return [
                'max_allowed_advance' => 0,
                'per_day_salary' => 0,
                'present_days' => 0,
                'total_days_in_month' => 0,
                'working_days' => 0,
                'off_days' => 0,
                'actual_allowed_advance' => 0,
                'total_advance_taken' => 0,
                'remaining_advance' => 0,
                'attendance_percentage' => 0
            ];
        }
    }

    public function getPresentDaysForUser($userId, $year, $month)
    {
        try {
            // Check if attendance table exists
            if (!$this->db->table_exists('attendance')) {
                return 0;
            }
            
            $this->db->select('COUNT(*) as present_days');
            $this->db->from('attendance');
            $this->db->where('user_id', $userId);
            $this->db->where('YEAR(attendance_date)', $year);
            $this->db->where('MONTH(attendance_date)', $month);
            $this->db->where('is_present', 1);
            
            $result = $this->db->get()->row();
            return $result ? $result->present_days : 0;
        } catch (Exception $e) {
            error_log("Advance_model getPresentDaysForUser error: " . $e->getMessage());
            return 0;
        }
    }

    public function getTotalAdvanceTaken($userId, $year, $month)
    {
        // Check if advance_transactions table exists
        if (!$this->db->table_exists('advance_transactions')) {
            return 0;
        }
        
        $this->db->select('COALESCE(SUM(amount), 0) as total_advance');
        $this->db->from('advance_transactions');
        $this->db->where('user_id', $userId);
        $this->db->where('YEAR(transaction_date)', $year);
        $this->db->where('MONTH(transaction_date)', $month);
        $this->db->where('transaction_type', 'advance'); // Assuming there's a type field
        
        $result = $this->db->get()->row();
        return $result ? $result->total_advance : 0;
    }

    public function getWorkingDaysForUser($userId, $year, $month)
    {
        try {
            // Get total days in month
            $totalDaysInMonth = date('t', strtotime($year . '-' . $month . '-01'));
            
            // Get off days for this user and month
            $offDays = $this->getOffDaysForUser($userId, $year, $month);
            $offDaysCount = count($offDays);
            
            // Return working days (total days - off days)
            return $totalDaysInMonth - $offDaysCount;
        } catch (Exception $e) {
            error_log("Advance_model getWorkingDaysForUser error: " . $e->getMessage());
            return date('t', strtotime($year . '-' . $month . '-01')); // Return total days if error
        }
    }

    public function getOffDaysForUser($userId, $year, $month)
    {
        try {
            // Check if off_days tables exist
            if (!$this->db->table_exists('off_days') && !$this->db->table_exists('off_days_by_user')) {
                return [];
            }

            $offDays = [];

            // Get company-wide off days
            if ($this->db->table_exists('off_days')) {
                $this->db->select('*');
                $this->db->from('off_days');
                $this->db->where('YEAR(date)', $year);
                $this->db->where('MONTH(date)', $month);
                $companyOffDays = $this->db->get()->result();
                $offDays = array_merge($offDays, $companyOffDays);
            }

            // Get user-specific off days
            if ($this->db->table_exists('off_days_by_user')) {
                $this->db->select('*');
                $this->db->from('off_days_by_user');
                $this->db->where('user_id', $userId);
                $this->db->where('YEAR(date)', $year);
                $this->db->where('MONTH(date)', $month);
                $userOffDays = $this->db->get()->result();
                $offDays = array_merge($offDays, $userOffDays);
            }

            return $offDays;
        } catch (Exception $e) {
            error_log("Advance_model getOffDaysForUser error: " . $e->getMessage());
            return [];
        }
    }

    public function getAdvanceSummary($year = null, $month = null)
    {
        if (!$year) $year = date('Y');
        if (!$month) $month = date('n');

        $users = $this->getUsersWithAdvanceData($year, $month);
        
        $totalMaxAllowed = 0;
        $totalActualAllowed = 0;
        $totalAdvanceTaken = 0;
        $totalRemaining = 0;
        
        foreach ($users as $user) {
            $totalMaxAllowed += $user->max_allowed_advance;
            $totalActualAllowed += $user->actual_allowed_advance;
            $totalAdvanceTaken += $user->total_advance_taken;
            $totalRemaining += $user->remaining_advance;
        }
        
        return [
            'total_users' => count($users),
            'total_max_allowed' => round($totalMaxAllowed, 2),
            'total_actual_allowed' => round($totalActualAllowed, 2),
            'total_advance_taken' => round($totalAdvanceTaken, 2),
            'total_remaining' => round($totalRemaining, 2)
        ];
    }
}
