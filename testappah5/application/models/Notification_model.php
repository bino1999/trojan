<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Notification_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Get notifications for a user
     */
    public function get_notifications($user_id = null, $limit = 10) {
        if ($user_id === null) {
            $user_id = $this->session->userdata('user_id');
        }
        
        $this->db->where('user_id', $user_id);
        $this->db->where('is_read', 0);
        $this->db->order_by('created_at', 'DESC');
        $this->db->limit($limit);
        
        return $this->db->get('notifications')->result();
    }

    /**
     * Get unread notification count
     */
    public function get_unread_count($user_id = null) {
        if ($user_id === null) {
            $user_id = $this->session->userdata('user_id');
        }
        
        $this->db->where('user_id', $user_id);
        $this->db->where('is_read', 0);
        
        return $this->db->count_all_results('notifications');
    }

    /**
     * Mark notification as read
     */
    public function mark_as_read($notification_id, $user_id = null) {
        if ($user_id === null) {
            $user_id = $this->session->userdata('user_id');
        }
        
        $this->db->where('id', $notification_id);
        $this->db->where('user_id', $user_id);
        
        return $this->db->update('notifications', array('is_read' => 1));
    }

    /**
     * Mark all notifications as read for a user
     */
    public function mark_all_as_read($user_id = null) {
        if ($user_id === null) {
            $user_id = $this->session->userdata('user_id');
        }
        
        $this->db->where('user_id', $user_id);
        $this->db->where('is_read', 0);
        
        return $this->db->update('notifications', array('is_read' => 1));
    }

    /**
     * Add a new notification
     */
    public function add_notification($user_id, $title, $message, $type = 'info', $link = null) {
        $data = array(
            'user_id' => $user_id,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'link' => $link,
            'is_read' => 0,
            'created_at' => date('Y-m-d H:i:s')
        );
        
        return $this->db->insert('notifications', $data);
    }

    /**
     * Delete a notification
     */
    public function delete_notification($notification_id, $user_id = null) {
        if ($user_id === null) {
            $user_id = $this->session->userdata('user_id');
        }
        
        $this->db->where('id', $notification_id);
        $this->db->where('user_id', $user_id);
        
        return $this->db->delete('notifications');
    }

    /**
     * Delete all notifications for a user
     */
    public function delete_all_notifications($user_id = null) {
        if ($user_id === null) {
            $user_id = $this->session->userdata('user_id');
        }
        
        $this->db->where('user_id', $user_id);
        
        return $this->db->delete('notifications');
    }
}
