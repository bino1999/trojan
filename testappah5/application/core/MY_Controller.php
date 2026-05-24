<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MY_Controller extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		if (!$this->session->userdata('userid')) {
			redirect('login');
		}
		$this->load->helper('auth');
	}

	protected function require_permission($permission)
	{
		return require_permission($permission);
	}
}


