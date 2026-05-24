<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Attendance_model extends CI_Model
{
    public function getUsers()
    {
        $this->db->select('UserID, FirstName, LastName, UserName');
        $this->db->from('users');
        $this->db->where('IsActive', 1);
        $this->db->order_by('FirstName', 'ASC');
        $query = $this->db->get();
        return $query->result();
    }

    public function getAttendanceByUserAndMonth($userId, $year, $month)
    {
        $this->db->select('*');
        $this->db->from('attendance');
        $this->db->where('user_id', $userId);
        $this->db->where('YEAR(attendance_date)', $year);
        $this->db->where('MONTH(attendance_date)', $month);
        $this->db->order_by('attendance_date', 'ASC');
        $query = $this->db->get();
        return $query->result();
    }

    public function saveAttendance($userId, $date, $isPresent)
    {
        $data = [
            'user_id' => $userId,
            'attendance_date' => $date,
            'is_present' => $isPresent ? 1 : 0
        ];

        // Use ON DUPLICATE KEY UPDATE to handle existing records
        $sql = "INSERT INTO attendance (user_id, attendance_date, is_present) VALUES (?, ?, ?) 
                ON DUPLICATE KEY UPDATE is_present = VALUES(is_present), updated_at = CURRENT_TIMESTAMP";
        
        return $this->db->query($sql, [$userId, $date, $isPresent ? 1 : 0]);
    }

    public function getAttendanceSummary($userId, $year, $month)
    {
        $this->db->select('COUNT(*) as total_days, SUM(is_present) as present_days');
        $this->db->from('attendance');
        $this->db->where('user_id', $userId);
        $this->db->where('YEAR(attendance_date)', $year);
        $this->db->where('MONTH(attendance_date)', $month);
        $query = $this->db->get();
        return $query->row();
    }

    public function getPresentDates($userId, $year, $month)
    {
        $this->db->select('DAY(attendance_date) as day');
        $this->db->from('attendance');
        $this->db->where('user_id', $userId);
        $this->db->where('YEAR(attendance_date)', $year);
        $this->db->where('MONTH(attendance_date)', $month);
        $this->db->where('is_present', 1);
        $this->db->order_by('attendance_date', 'ASC');
        $query = $this->db->get();
        return $query->result();
    }

    // Off Days Management Methods
    public function getOffDays($year, $month)
    {
        $this->db->select('*');
        $this->db->from('off_days');
        $this->db->where('YEAR(date)', $year);
        $this->db->where('MONTH(date)', $month);
        $this->db->order_by('date', 'ASC');
        $query = $this->db->get();
        return $query->result();
    }

    public function getUserOffDays($userId, $year, $month)
    {
        $this->db->select('*');
        $this->db->from('off_days_by_user');
        $this->db->where('user_id', $userId);
        $this->db->where('YEAR(date)', $year);
        $this->db->where('MONTH(date)', $month);
        $this->db->order_by('date', 'ASC');
        $query = $this->db->get();
        return $query->result();
    }

    public function getAllOffDays($userId, $year, $month)
    {
        // Get company-wide off days
        $companyOffDays = $this->getOffDays($year, $month);
        
        // Get user-specific off days
        $userOffDays = $this->getUserOffDays($userId, $year, $month);
        
        // Combine and return all off days
        return array_merge($companyOffDays, $userOffDays);
    }

    public function addOffDay($date, $description = '', $isCompanyHoliday = false, $userId = null)
    {
        if ($isCompanyHoliday) {
            // Add to company-wide off days
            $data = [
                'date' => $date,
                'description' => $description,
                'is_company_holiday' => 1
            ];
            return $this->db->insert('off_days', $data);
        } else {
            // Add to user-specific off days
            if (!$userId) {
                return false;
            }
            $data = [
                'user_id' => $userId,
                'date' => $date,
                'description' => $description
            ];
            return $this->db->insert('off_days_by_user', $data);
        }
    }

    public function removeOffDay($date, $userId = null)
    {
        if ($userId) {
            // Remove user-specific off day
            $this->db->where('user_id', $userId);
            $this->db->where('date', $date);
            return $this->db->delete('off_days_by_user');
        } else {
            // Remove company-wide off day
            $this->db->where('date', $date);
            return $this->db->delete('off_days');
        }
    }

    public function isOffDay($date, $userId = null)
    {
        // Check company-wide off days
        $this->db->select('id');
        $this->db->from('off_days');
        $this->db->where('date', $date);
        $companyOffDay = $this->db->get()->row();

        if ($companyOffDay) {
            return true;
        }

        // Check user-specific off days if userId provided
        if ($userId) {
            $this->db->select('id');
            $this->db->from('off_days_by_user');
            $this->db->where('user_id', $userId);
            $this->db->where('date', $date);
            $userOffDay = $this->db->get()->row();

            if ($userOffDay) {
                return true;
            }
        }

        return false;
    }

    public function getWorkingDaysInMonth($userId, $year, $month)
    {
        // Get total days in month
        $daysInMonth = date('t', strtotime($year . '-' . $month . '-01'));
        
        // Get all off days for this month
        $offDays = $this->getAllOffDays($userId, $year, $month);
        
        // Count off days
        $offDaysCount = count($offDays);
        
        // Return working days (total days - off days)
        return $daysInMonth - $offDaysCount;
    }

    public function getAttendanceSummaryWithOffDays($userId, $year, $month)
    {
        // Get basic attendance summary
        $summary = $this->getAttendanceSummary($userId, $year, $month);
        
        // Get working days in month
        $workingDays = $this->getWorkingDaysInMonth($userId, $year, $month);
        
        // Get off days count
        $offDays = $this->getAllOffDays($userId, $year, $month);
        $offDaysCount = count($offDays);
        
        // Update summary with off days information
        $summary->working_days = $workingDays;
        $summary->off_days = $offDaysCount;
        $summary->total_days_in_month = date('t', strtotime($year . '-' . $month . '-01'));
        
        return $summary;
    }

    public function getMonthlyAttendanceForAll($year, $month)
    {
        $this->db->select('UserID as user_id, FirstName, LastName, UserName');
        $this->db->from('users');
        $this->db->where('IsActive', 1);
        $this->db->order_by('FirstName', 'ASC');
        $employees = $this->db->get()->result();

        $this->db->select('user_id, DAY(attendance_date) AS day, is_present');
        $this->db->from('attendance');
        $this->db->where('YEAR(attendance_date)', (int)$year);
        $this->db->where('MONTH(attendance_date)', (int)$month);
        $rows = $this->db->get()->result();

        $attendanceMap = [];
        foreach ($rows as $r) {
            if (!isset($attendanceMap[$r->user_id])) $attendanceMap[$r->user_id] = [];
            $attendanceMap[$r->user_id][(int)$r->day] = (int)$r->is_present;
        }

        // Company off days for the month (keyed by day)
        $this->db->select('DAY(date) AS day, description, 1 as is_company_holiday');
        $this->db->from('off_days');
        $this->db->where('YEAR(date)', (int)$year);
        $this->db->where('MONTH(date)', (int)$month);
        $companyOffs = $this->db->get()->result();
        $companyOffDays = [];
        foreach ($companyOffs as $o) { $companyOffDays[(int)$o->day] = $o; }

        // User-specific off days
        $this->db->select('user_id, DAY(date) AS day, description');
        $this->db->from('off_days_by_user');
        $this->db->where('YEAR(date)', (int)$year);
        $this->db->where('MONTH(date)', (int)$month);
        $userOffs = $this->db->get()->result();
        $userOffMap = [];
        foreach ($userOffs as $o) {
            if (!isset($userOffMap[$o->user_id])) $userOffMap[$o->user_id] = [];
            $userOffMap[$o->user_id][(int)$o->day] = $o->description ?: '';
        }

        return [
            'employees' => $employees,
            'attendance' => $attendanceMap,
            'companyOffDays' => $companyOffDays,
            'userOffDays' => $userOffMap,
        ];
    }

    public function saveAttendanceBatch($records)
    {
        if (empty($records)) return true;

        $sql = "INSERT INTO attendance (user_id, attendance_date, is_present) VALUES ";
        $placeholders = [];
        $params = [];
        foreach ($records as $r) {
            $placeholders[] = '(?, ?, ?)';
            $params[] = (int)$r['user_id'];
            $params[] = $r['date'];
            $params[] = (int)$r['is_present'];
        }
        $sql .= implode(',', $placeholders) . " ON DUPLICATE KEY UPDATE is_present = VALUES(is_present), updated_at = CURRENT_TIMESTAMP";
        return $this->db->query($sql, $params);
    }
}
