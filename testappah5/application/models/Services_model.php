<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Services_model extends CI_Model
{

    public function loadServicesJobs($sdate = null, $edate = null, $filter_service_type= null, $is_credit = null)
{

    $currentStatus = $this->session->userdata('filter_job_status');

    $this->db->select('
        services.*,
        vehicles.vehicle_no,
        vehicle_brands.vehicleBrandName AS vehicleBrandName, 
        vehicle_categories.vehicleCategoryName AS vehicleCategoryName, 
        customers.customers_id AS customer_id, 
        customers.salutation AS salutation, 
        customers.name AS customer_name,
        customers.mobile AS customer_mobile
    ');
    $this->db->from('services_job as services');

    if($filter_service_type) {
        $this->db->where('services.service_type', $filter_service_type);
    }

    if(!is_null($currentStatus) && $currentStatus != 'all') {
        $this->db->where('services.status', $currentStatus);
    }

    if($currentStatus == 'all') {
        $this->db->where('services.status !=',5);
    }

    if($is_credit=='credit') {
        $this->db->where('services.is_credit', 1);
    }

    // Apply date filters if available
    if (!empty($sdate) && !empty($edate)) {
        $this->db->where('services.service_date >=', $sdate);
        $this->db->where('services.service_date <=', $edate);
    }

    $this->db->join('customers', 'customers.customers_id = services.customer_id', 'left');
    $this->db->join('vehicles', 'vehicles.vehicle_id = services.vehicle_id', 'left');
    $this->db->join('vehicle_brands', 'vehicle_brands.vehicleBrandId = vehicles.brand_id', 'left');
    $this->db->join('vehicle_categories', 'vehicle_categories.vehicleCategoryId = vehicles.category_id', 'left');
    
    $this->db->order_by('services.job_id', 'DESC');
    
    $query = $this->db->get();
    return $query->result(); // Return all services
}

    public function loadCustomer($customerId = null)
    {
        $this->db->select('*');
        $this->db->from('customers');
        if (!is_null($customerId)) {
            $this->db->where('customers.customers_id', $customerId);
            $query = $this->db->get();
            return $query->row(); // Return single customer
        }

        $this->db->order_by('customers.customers_id', 'DESC');
        $query = $this->db->get();
        return $query->result(); // Return all customers
    }

    public function loadvehicles($vehicle_id = null)
{
    $this->db->select('
        vehicles.*, 
        vehicle_categories.vehicleCategoryName AS vehicleCategoryName, 
        vehicle_brands.vehicleBrandName AS vehicleBrandName, 
        customers.customers_id AS customer_id, 
        customers.salutation AS salutation, 
        customers.name AS customer_name,
        customers.mobile AS customer_mobile,
    ');
    $this->db->from('vehicles');
    if (!is_null($vehicle_id)) {
        $this->db->where('vehicles.vehicle_id', $vehicle_id);
        $query = $this->db->get();
        return $query->row();
    }
    $this->db->join('vehicle_categories', 'vehicle_categories.vehicleCategoryId = vehicles.category_id', 'left');
    $this->db->join('vehicle_brands', 'vehicle_brands.vehicleBrandId = vehicles.brand_id', 'left');
    $this->db->join('customers', 'customers.customers_id = vehicles.owner_id', 'left');
    $this->db->order_by('vehicles.vehicle_id', 'DESC');
    $query = $this->db->get();
    return $query->result();
}

    public function loadvehicleBrands($brand_id = null)
    {
        if (!is_null($brand_id)) {
            $this->db->where('vehicleBrandId', $brand_id);
            $query = $this->db->get('vehicle_brands');
            return $query->row();
        }
        $this->db->order_by('vehicleBrandName', 'ASC');
        $query = $this->db->get('vehicle_brands');
        return $query->result();
    }

    public function loadvehicleCategories($category_id = null)
    {
        if (!is_null($category_id)) {
            $this->db->where('vehicleCategoryId', $category_id);
            $query = $this->db->get('vehicle_categories');
            return $query->row();
        }
        $this->db->order_by('vehicleCategoryName', 'ASC');
        $query = $this->db->get('vehicle_categories');
        return $query->result();
    }

    public function loadServicesPackages($package_id = null)
    {
        if (!is_null($package_id)) {
            $this->db->where('id', $package_id);
            $query = $this->db->get('service_packages');
            return $query->row();
        }
        $this->db->where('active', 1);
        $this->db->order_by('id', 'ASC');
        $query = $this->db->get('service_packages');
        return $query->result();
    }

    public function loadServicesItems($item_id = null)
    {
        if (!is_null($item_id)) {
            $this->db->where('id', $item_id);
            $query = $this->db->get('service_items');
            return $query->row();
        }
        $this->db->where('status', 1);
        $this->db->order_by('name', 'ASC');
        $query = $this->db->get('service_items');
        return $query->result();
    }


    public function loadServicesItemsByCategory($categoryId = null, $sectionId = null)
    {

        $this->db->select('service_items.*, service_items_category.*');
        $this->db->from('service_items');
        $this->db->join('service_items_category', 'service_items_category.sic_id = service_items.service_category_id', 'left');
        
        if($categoryId>0) {
            $this->db->where('service_items.service_category_id', $categoryId);
        }

        if($sectionId>0) {
            $this->db->where('service_items.section_id', $sectionId);
        }

        $query = $this->db->get();
        return $query->result();
    }

    public function showServicePackageItems($packageId)
    {
        $this->db->select('service_items.*, service_package_items.discount_type, service_package_items.discount_value');
        $this->db->from('service_package_items');
        $this->db->join('service_items', 'service_items.id = service_package_items.item_id', 'left');
        $this->db->where('service_package_items.package_id', $packageId);
        $query = $this->db->get();
        return $query->result();
    }

    public function loadItemBrands($brand_id = null)
    {
        if (!is_null($brand_id)) {
            $this->db->where('itemBrandId', $brand_id);
            $query = $this->db->get('item_brands');
            return $query->row();
        }
        $this->db->order_by('itemBrandName', 'ASC');
        $query = $this->db->get('item_brands');
        return $query->result();
    }

    public function loadItemCategories($category_id = null)
    {
        if (!is_null($category_id)) {
            $this->db->where('itemCategoryId', $category_id);
            $query = $this->db->get('item_categories');
            return $query->row();
        }
        $this->db->order_by('itemCategoryName', 'ASC');
        $query = $this->db->get('item_categories');
        return $query->result();
    }


    public function loadSuppliers($supplier_id = null)
    {
        if (!is_null($supplier_id)) {
            $this->db->where('supplier_id', $supplier_id);
            $query = $this->db->get('suppliers');
            return $query->row();
        }
        $this->db->order_by('name', 'ASC');
        $query = $this->db->get('suppliers');
        return $query->result();
    }

    public function loadJobItems($job_id = null)
{
    $this->db->select('
        services_job_items.*, 
        service_items.name AS item_name, 
        service_packages.package_name AS package_name, 
        products.product_name AS product_name,
        products.sku
    ');
    $this->db->from('services_job_items');
    
    // Join service_items table
    $this->db->join('service_items', 'service_items.id = services_job_items.item_id', 'left');
    
    // Join service_packages table
    $this->db->join('service_packages', 'service_packages.id = services_job_items.item_id', 'left');
    
    // Join products table only when item_type is 'product'
    $this->db->join('products', 'products.product_id = services_job_items.item_id AND services_job_items.item_type = "product"', 'left');
    
    // Filter by service_job_id
    if (!is_null($job_id)) {
        $this->db->where('services_job_items.service_job_id', $job_id);
    }

    // Apply the order_by condition
    $this->db->order_by('services_job_items.id', 'ASC');
    
    // Execute the query
    $query = $this->db->get();
    return $query->result();
}


public function loadJobData($job_id = null)
{
    $this->db->select('
        services_job.*, 
        vehicles.vehicle_no, 
        customers.salutation AS salutation, 
        customers.name AS customer_name,
        customers.mobile AS customer_mobile
    ');
    $this->db->from('services_job');

    // If job_id is provided, fetch the specific job's details
    if (!is_null($job_id)) {
        // Join customers and vehicles only if you are looking for a specific job
        $this->db->join('customers', 'customers.customers_id = services_job.customer_id', 'left');
        $this->db->join('vehicles', 'vehicles.vehicle_id = services_job.vehicle_id', 'left');
        
        // Fetch the single job data
        $this->db->where('job_id', $job_id);
        $query = $this->db->get();
        return $query->row(); // Return a single row for a specific job
    }

    // If no job_id, fetch all job data
    // Make sure to join customers and vehicles here for all jobs
    $this->db->join('customers', 'customers.customers_id = services_job.customer_id', 'left');
    $this->db->join('vehicles', 'vehicles.vehicle_id = services_job.vehicle_id', 'left');
    
    // Fetch all jobs
    $query = $this->db->get();
    return $query->result(); // Return an array of jobs if no job_id is provided
}


public function loadJobTotalPayment($job_id = null) {
    
    if (is_null($job_id)) {
        return (object) ['total_payment' => 0];
    }
    
    $this->db->select('SUM(paid_amount) as total_payment');
    $this->db->from('services_job_payments');
    $this->db->where('service_job_id', $job_id);
    $this->db->where('deleted_by IS NULL', null, false); // Only include non-deleted payments
    $this->db->where('cheque_return_by IS NULL', null, false); // Only include non-returned cheques
    $this->db->where('payment_method !=', 'credit'); // Exclude credit payments from total paid
    
    $query = $this->db->get();
    $result = $query->row();
    
    // Ensure we return a valid object with total_payment
    if (!$result || is_null($result->total_payment)) {
        return (object) ['total_payment' => 0];
    }
    
    return $result;
}

public function getJobLastPayment($job_id = null) {
    
    if (is_null($job_id)) {
        return (object) ['total_payment' => 0];
    }
    
    $this->db->select('*');
    $this->db->from('services_job_payments');
    $this->db->where('deleted_by', null);
    $this->db->where('service_job_id', $job_id);
    $this->db->order_by('id', 'DESC');
    $this->db->limit(1);
    
    $query = $this->db->get();
    return $query->row();
}

public function loadJobPayment($job_id) {
    $this->db->select('services_job_payments.*, users.UserName');
    $this->db->from('services_job_payments');
    $this->db->join('users', 'users.UserID = services_job_payments.created_by', 'left');
    $this->db->where('service_job_id', $job_id);
    $this->db->where('deleted_by IS NULL', null, false); // Only include non-deleted payments
    $this->db->order_by('id', 'DESC');    
    $query = $this->db->get();
    return $query->result(); 
}



    


    public function getServiceItemById($item_id)
    {
        $this->db->where('id', $item_id);
        $query = $this->db->get('service_items');
        return $query->row();
    }

    public function loadProducts($product_id = null)
{
    $this->db->select('
        products.*, 
        brands.itemBrandName as brand_name, 
        categories.itemCategoryName as category_name,
        products.sale_price
    ');
    $this->db->from('products');
    $this->db->join('item_brands brands', 'brands.itemBrandId = products.item_brand_id', 'left');
    $this->db->join('item_categories categories', 'categories.itemCategoryId = products.item_category_id', 'left');
    
    // If product_id is provided and valid, filter by it
    if (!is_null($product_id) && $product_id > 0) {
        $this->db->where('products.product_id', $product_id);
    }
    
    $this->db->where('products.is_active', 1);
    $this->db->order_by('products.product_name', 'ASC');
    
    $query = $this->db->get();
    
    // If a specific product_id was requested, return a single row
    if (!is_null($product_id) && $product_id > 0) {
        return $query->row();
    }
    // Otherwise return all active products
    return $query->result();
}

public function loadAvailablePurchaseProducts($product_id = null, $supplier_id = null, $brand_id = null, $category_id = null){
    $this->db->select('
        poi.*, 
        p.product_name, 
        p.sku, 
        p.measurement_unit,
        p.barcode,
        p.sku,
        ib.itemBrandName AS brand_name, 
        ic.itemCategoryName AS category_name,
        po.po_id,
        s.name AS supplier_name,
        ');
    $this->db->from('purchase_order_items as poi');
    $this->db->join('products as p', 'p.product_id = poi.product_id', 'left');
    $this->db->join('item_brands as ib', 'ib.itemBrandId = p.item_brand_id', 'left');
    $this->db->join('item_categories as ic', 'ic.itemCategoryId = p.item_category_id', 'left');

    //join purchase_orders
    $this->db->join('purchase_orders as po', 'po.po_id = poi.po_id', 'left');
    //join suppliers
    $this->db->join('suppliers as s', 's.supplier_id = po.supplier_id', 'left');

    //get only active purchase orders
    $this->db->where('po.completed_by>0');

    $this->db->where('poi.available_stock>0');

    // Only show items marked for sale or both (from product definition)
    $this->db->where('(p.inventory_type = "sale" OR p.inventory_type = "both")');  

    // Supplier comes from the purchase order, not the PO item
    if ($supplier_id > 0) {
        $this->db->where('po.supplier_id', $supplier_id);
        error_log("Filtering by supplier: $supplier_id");
    }

    if ($brand_id > 0) {
        $this->db->where('p.item_brand_id', $brand_id);
        error_log("Filtering by brand: $brand_id");
    }

    if ($category_id > 0) {
        $this->db->where('p.item_category_id', $category_id);
        error_log("Filtering by category: $category_id");
    }

    if ($product_id > 0) {
        $this->db->where('poi.product_id', $product_id);
    }
 
    $query = $this->db->get();
    error_log("Final SQL query: " . $this->db->last_query());
    return $query->result();
}
public function loadPurchaseProduct($po_item_id = null){
    $this->db->select('
        purchase_order_items.*, 
        products.product_name');
    $this->db->from('purchase_order_items');
    //join products table
    $this->db->join('products', 'products.product_id = purchase_order_items.product_id', 'left');

    if (!is_null($po_item_id) && $po_item_id > 0) {
        $this->db->where('po_item_id', $po_item_id);
        $query = $this->db->get();
        return $query->row(); 
    }
    $this->db->order_by('po_item_id', 'DESC');
    $query = $this->db->get();
    return $query->result();
}


public function loadProductsByFilters($categoryId = null, $brandId = null, $supplierId = null)
{
    $this->db->select('
        products.*, 
        brands.itemBrandName as brand_name, 
        categories.itemCategoryName as category_name,
        poi.sale_price,
        poi.available_stock
    ');
    $this->db->from('products');
    $this->db->join('item_brands brands', 'brands.itemBrandId = products.item_brand_id', 'left');
    $this->db->join('item_categories categories', 'categories.itemCategoryId = products.item_category_id', 'left');
    $this->db->join('purchase_order_items poi', 'poi.product_id = products.product_id', 'inner');
    
    // Only show products with available stock and product-level inventory type
    $this->db->where('poi.available_stock >', 0);
    $this->db->where('(products.inventory_type = "sale" OR products.inventory_type = "both")');
    
    if (!is_null($categoryId) && $categoryId > 0) {
        $this->db->where('products.item_category_id', $categoryId);
    }

    if (!is_null($brandId) && $brandId > 0) {
        $this->db->where('products.item_brand_id', $brandId);
    }
    
    $this->db->where('products.is_active', 1);
    $this->db->order_by('products.product_name', 'ASC');
    
    $query = $this->db->get();
    return $query->result();
}


public function loadProductSalePrice($product_id = null)
{
    $this->db->select('*');
    $this->db->from('products');
    
    if ($product_id > 0) {
        $this->db->where('product_id', $product_id);
    }
    
    $query = $this->db->get();
    return $query->result();
}

public function loadServiceItemsCategory($sic_id = null) {
        $this->db->select('*');
        $this->db->from('service_items_category');        
        if ($sic_id > 0) {
            $this->db->where('service_items_category.sic_id', $sic_id);
        }        
        $this->db->where('service_items_category.isDeleted', 0);
        $this->db->order_by('service_items_category.sci_name', 'ASC');
        $query = $this->db->get();
        return $query->result();
    }

    public function loadEmployeeSections($section_id = null) {
        $this->db->select('*');
        $this->db->from('employee_sections');        
        if ($section_id > 0) {
            $this->db->where('employeeSectionId', $section_id);
        }        
        $this->db->where('isDeleted', 0);
        $this->db->order_by('employeeSectionName', 'ASC');
        $query = $this->db->get();
        return $query->result();
    }


    // Outsource Parts Methods
    public function loadOutsourceParts($jobId = null) {
        $this->db->select('*');
        $this->db->from('outsource_parts');
        
        if (!is_null($jobId) && $jobId > 0) {
            $this->db->where('service_job_id', $jobId);
        }
        
        $this->db->order_by('created_at', 'DESC');
        $query = $this->db->get();
        return $query->result();
    }

    public function addOutsourcePart($data) {
        return $this->db->insert('outsource_parts', $data);
    }

    public function deleteOutsourcePart($id) {
        $this->db->where('id', $id);
        return $this->db->delete('outsource_parts');
    }

    public function getOutsourcePart($id) {
        $this->db->select('*');
        $this->db->from('outsource_parts');
        $this->db->where('id', $id);
        $query = $this->db->get();
        return $query->row();
    }

    public function updateOutsourcePart($id, $data) {
        $this->db->where('id', $id);
        return $this->db->update('outsource_parts', $data);
    }

    public function loadAvailableInternalProducts($product_id = null, $supplier_id = null, $brand_id = null, $category_id = null){
        $this->db->select('
            poi.*, 
            p.product_name, 
            p.sku, 
            p.measurement_unit,
            p.barcode,
            p.sku,
            ib.itemBrandName AS brand_name, 
            ic.itemCategoryName AS category_name,
            po.po_id,
            s.name AS supplier_name,
            ');
        $this->db->from('purchase_order_items as poi');
        $this->db->join('products as p', 'p.product_id = poi.product_id', 'left');
        $this->db->join('item_brands as ib', 'ib.itemBrandId = p.item_brand_id', 'left');
        $this->db->join('item_categories as ic', 'ic.itemCategoryId = p.item_category_id', 'left');

        //join purchase_orders
        $this->db->join('purchase_orders as po', 'po.po_id = poi.po_id', 'left');
        //join suppliers
        $this->db->join('suppliers as s', 's.supplier_id = po.supplier_id', 'left');

        //get only active purchase orders
        $this->db->where('po.completed_by>0');

        // Only show items marked for internal use or both (from product definition)
        $this->db->where('(p.inventory_type = "internal" OR p.inventory_type = "both")');

        $this->db->where('poi.available_stock>0');  

        // Supplier comes from the purchase order, not the PO item
        if ($supplier_id > 0) {
            $this->db->where('po.supplier_id', $supplier_id);
        }

        if ($brand_id > 0) {
            $this->db->where('p.item_brand_id', $brand_id);
        }

        if ($category_id > 0) {
            $this->db->where('p.item_category_id', $category_id);
        }

        if ($product_id > 0) {
            $this->db->where('poi.product_id', $product_id);
        }
     
        $query = $this->db->get();
        return $query->result();
    }
}