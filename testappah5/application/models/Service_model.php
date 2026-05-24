<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Service_model extends CI_Model {
    public function get_service_items($section_id = null, $category_id = null) {
        $this->db->select('si.*, es.employeeSectionName as section_name, users.UserName as created_by_name, 
                           sic.sci_name as category_name');
        $this->db->from('service_items si');
        $this->db->join('employee_sections es', 'es.employeeSectionId = si.section_id', 'left');
        $this->db->join('service_items_category sic', 'sic.sic_id  = si.service_category_id', 'left');
        $this->db->join('users users', 'users.UserID = si.created_by', 'left');
        
        if ($section_id > 0) {
            $this->db->where('si.section_id', $section_id);
        }
        
        if ($category_id > 0) {
            $this->db->where('si.service_category_id', $category_id);
        }
        
        $this->db->order_by('si.name', 'ASC');
        return $this->db->get()->result();
    }
    
    public function get_service_item($item_id) {
        return $this->db->get_where('service_items', ['id' => $item_id])->row();
    }
    
    public function add_service_item($data) {
        $this->db->insert('service_items', $data);
        return $this->db->insert_id();
    }
    
    public function update_service_item($item_id, $data) {
        $this->db->where('id', $item_id);
        return $this->db->update('service_items', $data);
    }
    
    public function delete_service_item($item_id) {
        $this->db->where('id', $item_id);
        return $this->db->delete('service_items');
    }

    public function loadServiceItems()
{
    return $this->db->select('id, name, price')->where('status', 1)->get('service_items')->result();
}

public function loadServicePackagesOld()
{
    return $this->db->select('sp.*, GROUP_CONCAT(CONCAT(si.name, ":", si.price)) as items')
        ->from('service_packages sp')
        ->join('service_package_items spi', 'spi.package_id = sp.id')
        ->join('service_items si', 'si.id = spi.item_id')
        ->group_by('sp.id')
        ->get()
        ->result();
}

public function loadServicePackages()
{
    // Get all active packages with their items
    $this->db->select('sp.*, 
        GROUP_CONCAT(si.name SEPARATOR "|||") as item_names,
        GROUP_CONCAT(si.price SEPARATOR "|||") as item_prices,
        GROUP_CONCAT(spi.discount_type SEPARATOR "|||") as discount_types,
        GROUP_CONCAT(spi.discount_value SEPARATOR "|||") as discount_values,
        GROUP_CONCAT(spi.final_price SEPARATOR "|||") as final_prices');
    $this->db->from('service_packages sp');
    $this->db->join('service_package_items spi', 'spi.package_id = sp.id', 'left');
    $this->db->join('service_items si', 'si.id = spi.item_id', 'left');
   // $this->db->where('sp.active', 1);
    $this->db->group_by('sp.id');
    $this->db->order_by('sp.id', 'DESC');
    $packages = $this->db->get()->result();

    // Process the concatenated results
    foreach ($packages as $package) {
        $package->items = [];
        
        if (!empty($package->item_names)) {
            $names = explode('|||', $package->item_names);
            $prices = explode('|||', $package->item_prices);
            $discount_types = explode('|||', $package->discount_types);
            $discount_values = explode('|||', $package->discount_values);
            $final_prices = explode('|||', $package->final_prices);

            for ($i = 0; $i < count($names); $i++) {
                $package->items[] = (object)[
                    'name' => $names[$i] ?? 'Unknown',
                    'price' => $prices[$i] ?? 0,
                    'discount_type' => $discount_types[$i] ?? 'none',
                    'discount_value' => $discount_values[$i] ?? 0,
                    'final_price' => $final_prices[$i] ?? $prices[$i] ?? 0
                ];
            }
        }
    }

    return $packages;
}

public function getServicePackage($package_id)
{
    // Get package info
    $this->db->where('id', $package_id);
    $package = $this->db->get('service_packages')->row();

    if ($package) {
        // Get package items with discounts
        $this->db->select('si.id as item_id, si.name, si.price, 
                          spi.discount_type, spi.discount_value, spi.final_price');
        $this->db->from('service_package_items spi');
        $this->db->join('service_items si', 'si.id = spi.item_id');
        $this->db->where('spi.package_id', $package_id);
        $package->items = $this->db->get()->result();
    }

    return $package;
}









}


?>