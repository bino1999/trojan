<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Logs_model extends CI_Model
{

    public function log_activity($action, $description = '')
    {
        $user_id = $this->session->userdata('userid');
        $data = [
            'user_id'     => $user_id,
            'action'     => $action,
            'description' => $description,
            'ip_address' => $this->input->ip_address(),
            'user_agent'  => $this->input->user_agent(),
            'created_at'   => date('Y-m-d H:i:s')
        ];
        $this->db->insert('user_logs', $data);
    }
}
