<?php
defined('BASEPATH') or exit('No direct script access allowed');

class General_model extends CI_Model
{

    public function loadVehicleCategory($vehicleCategoryId = null)
    {
        $this->db->select('*');
        $this->db->from('vehicle_categories');
        if ($vehicleCategoryId > 0) {
            $this->db->where('vehicleCategoryId', $vehicleCategoryId);
        }
        $query = $this->db->get();
        return $query->result();
    }

    public function loadVehicleBrand($vehicleBrandId = null)
    {
        $this->db->select('*');
        $this->db->from('vehicle_brands');
        if ($vehicleBrandId > 0) {
            $this->db->where('vehicleBrandId', $vehicleBrandId);
        }
        $query = $this->db->get();
        return $query->result();
    }

    public function insertVehicle($data)
    {
        return $this->db->insert('vehicles', $data);
    }

    public function loadVehicle($vehicle_id = null)
{
    $this->db->select('
        vehicles.*,
        customers.name AS owner_name,
        vehicle_categories.vehicleCategoryName AS vehicle_category,
        vehicle_brands.vehicleBrandName AS vehicle_brand
    ');
    $this->db->from('vehicles');
    $this->db->join('customers', 'customers.customers_id = vehicles.owner_id', 'left');
    $this->db->join('vehicle_categories', 'vehicle_categories.vehicleCategoryId = vehicles.category_id', 'left');
    $this->db->join('vehicle_brands', 'vehicle_brands.vehicleBrandId = vehicles.brand_id', 'left');

    if (!is_null($vehicle_id)) {
        $this->db->where('vehicles.vehicle_id', $vehicle_id);
        $query = $this->db->get();
        return $query->row();
    }

    $this->db->order_by('vehicles.vehicle_id', 'DESC');
    $query = $this->db->get();
    return $query->result();
}

public function loadVehicleByDates($sdate = null, $edate = null)
{
    $this->db->select('
        vehicles.*,
        customers.name AS owner_name,
        vehicle_categories.vehicleCategoryName AS vehicle_category,
        vehicle_brands.vehicleBrandName AS vehicle_brand
    ');
    $this->db->from('vehicles');
    $this->db->join('customers', 'customers.customers_id = vehicles.owner_id', 'left');
    $this->db->join('vehicle_categories', 'vehicle_categories.vehicleCategoryId = vehicles.category_id', 'left');
    $this->db->join('vehicle_brands', 'vehicle_brands.vehicleBrandId = vehicles.brand_id', 'left');

    // Ensure valid date filtering
    if (!is_null($sdate) && !is_null($edate)) {
        $this->db->where('vehicles.created_at >=', $sdate . ' 00:00:00');
        $this->db->where('vehicles.created_at <=', $edate . ' 23:59:59');
    }

    $this->db->order_by('vehicles.vehicle_id', 'DESC');
    $query = $this->db->get();

    return $query->result(); // Always return an array of results
}


public function loadvehicle2($vehicle_id = null)
{
    $this->db->select('*');
    $this->db->from('vehicles');
    if (!is_null($vehicle_id)) {
        $this->db->where('vehicles.vehicle_id', $vehicle_id);
        $query = $this->db->get();
        return $query->row();
    }

    $this->db->order_by('vehicles.vehicle_id', 'DESC');
    $query = $this->db->get();
    return $query->result();
}

    public function insertSupplier($data)
    {
        return $this->db->insert('suppliers', $data);
    }

    public function loadSupplier($supplier_id = null)
    {
        $this->db->select('
        suppliers.*, 
        provinces.name_en AS province_name, 
        districts.name_en AS district_name, 
        cities.name_en AS city_name
        ');
        $this->db->from('suppliers');

        // Join with province, district, and city tables
        $this->db->join('provinces', 'provinces.id = suppliers.province_id', 'left');
        $this->db->join('districts', 'districts.id = suppliers.district_id', 'left');
        $this->db->join('cities', 'cities.id = suppliers.city_id', 'left');

        if (!is_null($supplier_id)) {
            $this->db->where('suppliers.supplier_id', $supplier_id);
            $query = $this->db->get();
            return $query->row(); // Return single suppliers
        }

        $this->db->order_by('suppliers.supplier_id', 'DESC');
        $query = $this->db->get();
        return $query->result(); // Return all suppliers
    }

    public function insertCustomer($data)
    {
        return $this->db->insert('customers', $data);
    }

    public function loadCustomer($customerId = null)
    {
        $this->db->select('
        customers.*, 
        provinces.name_en AS province_name, 
        districts.name_en AS district_name, 
        cities.name_en AS city_name
    ');
        $this->db->from('customers');

        // Join with province, district, and city tables
        $this->db->join('provinces', 'provinces.id = customers.province_id', 'left');
        $this->db->join('districts', 'districts.id = customers.district_id', 'left');
        $this->db->join('cities', 'cities.id = customers.city_id', 'left');

        if (!is_null($customerId)) {
            $this->db->where('customers.customers_id', $customerId);
            $query = $this->db->get();
            return $query->row(); // Return single customer
        }

        $this->db->order_by('customers.customers_id', 'DESC');
        $query = $this->db->get();
        return $query->result(); // Return all customers
    }

    public function loadCustomerByDates($sdate = null, $edate = null)
    {
        $this->db->select('
        customers.*, 
        provinces.name_en AS province_name, 
        districts.name_en AS district_name, 
        cities.name_en AS city_name
    ');
        $this->db->from('customers');

        if (!is_null($sdate) && !is_null($edate)) {
            $this->db->where('customers.created_at >=', $sdate . ' 00:00:00');
            $this->db->where('customers.created_at <=', $edate . ' 23:59:59');
        }

        // Join with province, district, and city tables
        $this->db->join('provinces', 'provinces.id = customers.province_id', 'left');
        $this->db->join('districts', 'districts.id = customers.district_id', 'left');
        $this->db->join('cities', 'cities.id = customers.city_id', 'left');

        $this->db->order_by('customers.customers_id', 'DESC');
        $query = $this->db->get();
        return $query->result(); // Return all customers
    }

    public function filterCustomers($searchFilter)
    {
        $this->db->select('
        customers.*, 
        provinces.name_en AS province_name, 
        districts.name_en AS district_name, 
        cities.name_en AS city_name
    ');
        $this->db->from('customers');
        $this->db->like('customers.name', $searchFilter);
        $this->db->or_like('customers.mobile', $searchFilter);
        $this->db->or_like('customers.email', $searchFilter);
        $this->db->or_like('customers.nic', $searchFilter);

        // Join with province, district, and city tables
        $this->db->join('provinces', 'provinces.id = customers.province_id', 'left');
        $this->db->join('districts', 'districts.id = customers.district_id', 'left');
        $this->db->join('cities', 'cities.id = customers.city_id', 'left');

        $this->db->order_by('customers.customers_id', 'DESC');
        $query = $this->db->get();
        return $query->result(); // Return all customers
    }

    public function loadCustomers($customers_id = null)
    {
        $this->db->select('*');
        $this->db->from('customers');

        if (!is_null($customers_id)) {
            $this->db->where('customers_id', $customers_id);
            $query = $this->db->get();
            return $query->row();
        }

        $this->db->order_by('name', 'ASC');
        $query = $this->db->get();
        return $query->result();
    }


    public function loadProvince($id = null)
    {
        $this->db->select('*');
        $this->db->from('provinces');

        if (!is_null($id)) {
            $this->db->where('id', $id);
            $query = $this->db->get();
            return $query->row();
        }

        $this->db->order_by('name_en', 'ASC');
        $query = $this->db->get();
        return $query->result();
    }

    public function loadDistrict($id = null)
    {
        $this->db->select('*');
        $this->db->from('districts');

        if (!is_null($id)) {
            $this->db->where('id', $id);
            $query = $this->db->get();
            return $query->row();
        }

        $this->db->order_by('name_en', 'ASC');
        $query = $this->db->get();
        return $query->result();
    }

    public function getDistrictsByProvince($province_id)
    {
        return $this->db->where('province_id', $province_id)->get('districts')->result_array();
    }

    public function getCitiesByDistrict($district_id)
    {
        return $this->db->where('district_id', $district_id)->get('cities')->result_array();
    }

    public function loadUserById($user_id) {
        $this->db->select('*');
        $this->db->from('users');
        $this->db->where('UserID', $user_id);
        $query = $this->db->get();
        return $query->row();
    }

    
}
