<?php
defined('BASEPATH') or exit('No direct script access allowed');

class CronController extends CI_Controller
{
    public function update_stock()
    {
        $this->load->model('purchase_model');
        $this->purchase_model->updateAvailableStock();
        echo "Available stock updated.";
    }
}


?>