<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Notifications extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Notification_model');
        
        // Check if user is logged in
        if (!$this->session->userdata('user_id')) {
            redirect('login');
        }
    }

    /**
     * Get notifications via AJAX
     */
    public function get_notifications() {
        try {
            // Check if notifications table exists by trying to get notifications
            $notifications = $this->Notification_model->get_notifications();
            $unread_count = $this->Notification_model->get_unread_count();
            
            $response = array(
                'success' => true,
                'notifications' => $notifications ? $notifications : array(),
                'unread_count' => $unread_count ? $unread_count : 0
            );
        } catch (Exception $e) {
            // If there's an error (like table doesn't exist), return empty state
            $response = array(
                'success' => true,
                'notifications' => array(),
                'unread_count' => 0,
                'error' => 'Notifications table not found or database error'
            );
        }
        
        header('Content-Type: application/json');
        echo json_encode($response);
    }

    /**
     * Mark notification as read
     */
    public function mark_as_read() {
        $notification_id = $this->input->post('notification_id');
        
        if ($notification_id) {
            $result = $this->Notification_model->mark_as_read($notification_id);
            
            if ($result) {
                $unread_count = $this->Notification_model->get_unread_count();
                echo json_encode(array('success' => true, 'unread_count' => $unread_count));
            } else {
                echo json_encode(array('success' => false, 'message' => 'Failed to mark notification as read'));
            }
        } else {
            echo json_encode(array('success' => false, 'message' => 'Invalid notification ID'));
        }
    }

    /**
     * Mark all notifications as read
     */
    public function mark_all_as_read() {
        $result = $this->Notification_model->mark_all_as_read();
        
        if ($result) {
            echo json_encode(array('success' => true, 'unread_count' => 0));
        } else {
            echo json_encode(array('success' => false, 'message' => 'Failed to mark all notifications as read'));
        }
    }

    /**
     * Delete notification
     */
    public function delete_notification() {
        $notification_id = $this->input->post('notification_id');
        
        if ($notification_id) {
            $result = $this->Notification_model->delete_notification($notification_id);
            
            if ($result) {
                $unread_count = $this->Notification_model->get_unread_count();
                echo json_encode(array('success' => true, 'unread_count' => $unread_count));
            } else {
                echo json_encode(array('success' => false, 'message' => 'Failed to delete notification'));
            }
        } else {
            echo json_encode(array('success' => false, 'message' => 'Invalid notification ID'));
        }
    }

    /**
     * Delete all notifications
     */
    public function delete_all_notifications() {
        $result = $this->Notification_model->delete_all_notifications();
        
        if ($result) {
            echo json_encode(array('success' => true, 'unread_count' => 0));
        } else {
            echo json_encode(array('success' => false, 'message' => 'Failed to delete all notifications'));
        }
    }
}
