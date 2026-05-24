<?php
defined('BASEPATH') or exit('No direct script access allowed');

class InternalBill_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get_all_internal_bills($sdate = null, $edate = null)
    {
        $this->db->select('ib.*, u.UserName as created_by_name, emp.FirstName as customer_name, emp.LastName as customer_last_name');
        $this->db->from('internal_bills ib');
        $this->db->join('users u', 'u.UserID = ib.created_by', 'left');
        $this->db->join('users emp', 'emp.UserID = ib.customer_id', 'left');

        if (!empty($sdate) && !empty($edate)) {
            $this->db->where('DATE(ib.created_at) >=', $sdate);
            $this->db->where('DATE(ib.created_at) <=', $edate);
        }

        $this->db->order_by('ib.created_at', 'DESC');
        $query = $this->db->get();
        return $query->result();
    }

    public function get_internal_bill_details($bill_id)
    {
        $this->db->select('ib.*, u.UserName as created_by_name, emp.FirstName as employee_name, emp.LastName as employee_last_name, CONCAT(emp.FirstName, " ", emp.LastName) as full_employee_name');
        $this->db->from('internal_bills ib');
        $this->db->join('users u', 'u.UserID = ib.created_by', 'left');
        $this->db->join('users emp', 'emp.UserID = ib.customer_id', 'left');
        $this->db->where('ib.id', $bill_id);
        $query = $this->db->get();
        
        // Debug: Check if query failed
        if ($query === false) {
            error_log("Database query failed in get_internal_bill_details for bill_id: " . $bill_id);
            error_log("Last query: " . $this->db->last_query());
            return [];
        }
        
        return $query->result();
    }

    public function get_internal_bill_items($bill_id)
    {
        // Use a simpler query without the problematic purchase_order_items JOIN
        $this->db->select('ibi.*, p.product_name, p.barcode, p.sku');
        $this->db->from('internal_bill_items ibi');
        $this->db->join('products p', 'p.product_id = ibi.item_id', 'left');
        $this->db->where('ibi.internal_bill_id', $bill_id);
        $query = $this->db->get();
        
        // Debug: Check if query failed
        if ($query === false) {
            error_log("Database query failed in get_internal_bill_items for bill_id: " . $bill_id);
            error_log("Last query: " . $this->db->last_query());
            return [];
        }
        
        return $query->result();
    }

    public function create_internal_bill($billData)
    {
        $this->db->insert('internal_bills', $billData);
        return $this->db->insert_id();
    }

    public function add_internal_bill_item($itemData)
    {
        return $this->db->insert('internal_bill_items', $itemData);
    }

    public function update_internal_bill($bill_id, $data)
    {
        $this->db->where('id', $bill_id);
        return $this->db->update('internal_bills', $data);
    }

    public function delete_internal_bill($bill_id)
    {
        // Start transaction
        $this->db->trans_start();

        // Get bill items to restore stock
        $items = $this->get_internal_bill_items($bill_id);
        foreach ($items as $item) {
            // Restore stock
            $this->db->set('available_stock', 'available_stock + ' . (float)$item->quantity, false);
            $this->db->where('po_item_id', $item->po_item_id);
            $this->db->update('purchase_order_items');
        }

        // Delete bill items
        $this->db->where('internal_bill_id', $bill_id);
        $this->db->delete('internal_bill_items');

        // Delete bill
        $this->db->where('id', $bill_id);
        $this->db->delete('internal_bills');

        $this->db->trans_complete();
        return $this->db->trans_status();
    }
}
