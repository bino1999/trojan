<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Services extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('products_model');
        $this->load->model('service_model');
        $this->load->model('services_model');
    }

    public function serviceItemsManage() {
        $this->require_permission('serviceitems.view');
        $data['mainMenuName'] = 'Service Packages';
        $data['subMenuName'] = 'services-items-manage';
        $data['sections'] = $this->products_model->loadSections();
        $data['serviceItemCategory'] = $this->products_model->loadServiceItemsCategory();
        
        $this->load->view('layout/header');
        $this->load->view('layout/top_navbar');
        $this->load->view('layout/left_sidebar', $data);
        $this->load->view('general/service-items-manage', $data);
        $this->load->view('layout/footer');
    }
    
    public function loadServiceItems() {
        $this->require_permission('serviceitems.view');
        $section_id = $this->input->post('section_id');
        $category_id = $this->input->post('category_id');
        $data['items'] = $this->service_model->get_service_items($section_id, $category_id);
        $this->load->view('general/service-items-list', $data);
    }
    
    public function saveServiceItem()
{
    $item_id = $this->input->post('service_item_id');
    if ($item_id) {
        $this->require_permission('serviceitems.edit');
    } else {
        $this->require_permission('serviceitems.create');
    }
    $this->form_validation->set_rules('name', 'Service Name', 'required');
    $this->form_validation->set_rules('price', 'Price', 'required|numeric');
    $this->form_validation->set_rules('price', 'Price', 'required|numeric');
    $this->form_validation->set_rules('service_category', 'Service Category', 'required');

    if ($this->form_validation->run() == FALSE) {
        echo json_encode([
            'status' => 'error',
            'message' => validation_errors()
        ]);
        return;
    }

    $data = [
        'section_id' => $this->input->post('section'),
        'service_category_id' => $this->input->post('service_category'),
        'name' => $this->input->post('name'),
        'price' => $this->input->post('price'),
        'description' => $this->input->post('description'),
        'status' => $this->input->post('status'),
        'created_by' => $this->session->userdata('userid'),
        'created_at' => date('Y-m-d H:i:s')
    ];

    if ($item_id) {
        // Update existing item
        $data['updated_by'] = $this->session->userdata('userid');
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->service_model->update_service_item($item_id, $data);
        $message = 'Service item updated successfully';

        $this->load->model('logs_model');
        $this->logs_model->log_activity('Service Item Updated', $this->input->post('name'));
    } else {
        // Create new item
        $item_id = $this->service_model->add_service_item($data);
        $message = 'Service item added successfully';
        $this->load->model('logs_model');
        $this->logs_model->log_activity('Service Item Added', $this->input->post('name'));
    }

    echo json_encode([
        'status' => 'success',
        'message' => $message,
        'item_id' => $item_id
    ]);
}

    
    public function getServiceItem() {
        $this->require_permission('serviceitems.view');
        $item_id = $this->input->post('item_id');
        $item = $this->service_model->get_service_item($item_id);
        
        if ($item) {
            echo json_encode([
                'status' => 'success',
                'data' => $item
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Service item not found'
            ]);
        }
    }
    
    public function deleteServiceItem() {
        $this->require_permission('serviceitems.delete');
        $item_id = $this->input->post('item_id');
        $result = $this->service_model->delete_service_item($item_id);
        
        if ($result) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Service item deleted successfully'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to delete service item'
            ]);
        }
    }

    public function servicePackagesManage()
{
    $this->require_permission('servicepackages.view');
    $data['mainMenuName'] = 'Service Packages';
    $data['subMenuName'] = 'service-packages-manage';
    $data['items'] = $this->service_model->loadServiceItems();
    $data['sections'] = $this->products_model->loadSections();
    $data['serviceItemCategory'] = $this->products_model->loadServiceItemsCategory();
    // for Items tab filters
    $data['itemBrands'] = $this->services_model->loadItemBrands();
    $data['itemCategories'] = $this->services_model->loadItemCategories();

    $this->load->view('layout/header');
    $this->load->view('layout/top_navbar');
    $this->load->view('layout/left_sidebar', $data);
    $this->load->view('general/service-packages-manage', $data);
    $this->load->view('layout/footer');
}

    public function loadServiceItemsForPackage() {
        $section_id = $this->input->post('section_id');
        $category_id = $this->input->post('category_id');
        $data['items'] = $this->service_model->get_service_items($section_id, $category_id);
        $this->load->view('general/service-package-items-table', $data);
    }

public function saveServicePackage()
{
    $this->require_permission('servicepackages.create');
    $packageName = $this->input->post('package_name');
    $items = $this->input->post('items'); // This now contains item_id, discount_type, and discount_value for each item
    $packageDiscountType = $this->input->post('package_discount_type') ?? 'none';
    $packageDiscountValue = $this->input->post('package_discount_value') ?? 0;
    $availabilityStart = $this->input->post('availability_start') ?: null;
    $availabilityEnd = $this->input->post('availability_end') ?: null;

    if (empty($packageName) || empty($items)) {
        echo json_encode(['status' => 'error', 'message' => 'Package name and items are required.']);
        return;
    }

    // Calculate subtotal with item discounts applied
    $subtotal = 0;
    $itemIds = [];
    
    // First get all item prices
    $this->db->select('id, price');
    $this->db->where_in('id', array_column($items, 'item_id'));
    $serviceItems = $this->db->get('service_items')->result_array();
    $itemPrices = array_column($serviceItems, 'price', 'id');

    // Calculate subtotal with item discounts
    foreach ($items as $item) {
        $itemId = $item['item_id'];
        $originalPrice = $itemPrices[$itemId] ?? 0;
        $discountType = $item['discount_type'] ?? 'none';
        $discountValue = $item['discount_value'] ?? 0;
        
        // Calculate discounted price
        $discountedPrice = $originalPrice;
        if ($discountType === 'percentage' && $discountValue > 0) {
            $discountedPrice = $originalPrice - ($originalPrice * $discountValue / 100);
        } elseif ($discountType === 'fixed' && $discountValue > 0) {
            $discountedPrice = $originalPrice - $discountValue;
            if ($discountedPrice < 0) $discountedPrice = 0;
        }
        
        $subtotal += $discountedPrice;
        $itemIds[] = $itemId;
    }

    // Apply package discount
    $packageDiscountAmount = 0;
    if ($packageDiscountType === 'percentage' && $packageDiscountValue > 0) {
        $packageDiscountAmount = ($subtotal * $packageDiscountValue) / 100;
    } elseif ($packageDiscountType === 'fixed' && $packageDiscountValue > 0) {
        $packageDiscountAmount = $packageDiscountValue;
    }
    
    $totalPrice = $subtotal - $packageDiscountAmount;
    if ($totalPrice < 0) $totalPrice = 0;

    // Save package
    $packageData = [
        'package_name' => $packageName,
        'total_price' => $totalPrice,
        'package_discount_type' => $packageDiscountType,
        'package_discount_value' => $packageDiscountValue,
        'availability_start' => $availabilityStart,
        'availability_end' => $availabilityEnd,
        'created_by' => $this->session->userdata('userid'),
        'created_at' => date('Y-m-d H:i:s')
    ];
    $this->db->insert('service_packages', $packageData);
    $packageId = $this->db->insert_id();

    // Save package items with discounts
    foreach ($items as $item) {
        $itemId = $item['item_id'];
        $originalPrice = $itemPrices[$itemId] ?? 0;
        $discountType = $item['discount_type'] ?? 'none';
        $discountValue = $item['discount_value'] ?? 0;
        $itemType = $item['item_type'] ?? 'service';
        
        // Calculate discounted price
        $discountedPrice = $originalPrice;
        if ($discountType === 'percentage' && $discountValue > 0) {
            $discountedPrice = $originalPrice - ($originalPrice * $discountValue / 100);
        } elseif ($discountType === 'fixed' && $discountValue > 0) {
            $discountedPrice = $originalPrice - $discountValue;
            if ($discountedPrice < 0) $discountedPrice = 0;
        }

        $this->db->insert('service_package_items', [
            'package_id' => $packageId,
            'item_id' => $itemId,
            'item_type' => $itemType,
            'discount_type' => $discountType,
            'discount_value' => $discountValue,
            'final_price' => $discountedPrice
        ]);
    }

    echo json_encode(['status' => 'success', 'message' => 'Service Package saved successfully.']);
}


public function updateServicePackage()
{
    $this->require_permission('servicepackages.edit');
    // Get the data from the form (using POST)
    $packageId = $this->input->post('package_id');
    $packageName = $this->input->post('package_name');
    $items = $this->input->post('items'); // This now contains item_id, discount_type, and discount_value for each item
    $packageDiscountType = $this->input->post('package_discount_type') ?? 'none';
    $packageDiscountValue = $this->input->post('package_discount_value') ?? 0;
    $availabilityStart = $this->input->post('availability_start') ?: null;
    $availabilityEnd = $this->input->post('availability_end') ?: null;

    // Validate required data
    if (empty($packageId) || empty($packageName) || empty($items)) {
        echo json_encode(['status' => 'error', 'message' => 'Package ID, name and items are required.']);
        return;
    }



    // Start a transaction to ensure data consistency
    $this->db->trans_start();

    // Calculate subtotal with item discounts applied
    $subtotal = 0;
    $itemIds = [];
    
    // First get all item prices
    $this->db->select('id, price');
    $this->db->where_in('id', array_column($items, 'item_id'));
    $serviceItems = $this->db->get('service_items')->result_array();
    $itemPrices = array_column($serviceItems, 'price', 'id');

    // Calculate subtotal with item discounts
    foreach ($items as $item) {
        $itemId = $item['item_id'];
        $originalPrice = $itemPrices[$itemId] ?? 0;
        $discountType = $item['discount_type'] ?? 'none';
        $discountValue = $item['discount_value'] ?? 0;
        
        // Calculate discounted price
        $discountedPrice = $originalPrice;
        if ($discountType === 'percentage' && $discountValue > 0) {
            $discountedPrice = $originalPrice - ($originalPrice * $discountValue / 100);
        } elseif ($discountType === 'fixed' && $discountValue > 0) {
            $discountedPrice = $originalPrice - $discountValue;
            if ($discountedPrice < 0) $discountedPrice = 0;
        }
        
        $subtotal += $discountedPrice;
        $itemIds[] = $itemId;
    }

    // Apply package discount
    $packageDiscountAmount = 0;
    if ($packageDiscountType === 'percentage' && $packageDiscountValue > 0) {
        $packageDiscountAmount = ($subtotal * $packageDiscountValue) / 100;
    } elseif ($packageDiscountType === 'fixed' && $packageDiscountValue > 0) {
        $packageDiscountAmount = $packageDiscountValue;
    }
    
    $totalPrice = $subtotal - $packageDiscountAmount;
    if ($totalPrice < 0) $totalPrice = 0;

    // Update the package in the 'service_packages' table
    $this->db->set('package_name', $packageName);
    $this->db->set('total_price', $totalPrice);
    $this->db->set('package_discount_type', $packageDiscountType);
    $this->db->set('package_discount_value', $packageDiscountValue);
    $this->db->set('availability_start', $availabilityStart);
    $this->db->set('availability_end', $availabilityEnd);
    $this->db->set('updated_by', $this->session->userdata('userid'));
    $this->db->set('updated_at', date('Y-m-d H:i:s'));
    $this->db->where('id', $packageId);
    $this->db->update('service_packages');

    // Delete the existing items associated with this package
    $this->db->where('package_id', $packageId);
    $this->db->delete('service_package_items');

    // Save package items with discounts
    foreach ($items as $item) {
        $itemId = $item['item_id'];
        $originalPrice = $itemPrices[$itemId] ?? 0;
        $discountType = $item['discount_type'] ?? 'none';
        $discountValue = $item['discount_value'] ?? 0;
        $itemType = $item['item_type'] ?? 'service';
        
        // Calculate discounted price
        $discountedPrice = $originalPrice;
        if ($discountType === 'percentage' && $discountValue > 0) {
            $discountedPrice = $originalPrice - ($originalPrice * $discountValue / 100);
        } elseif ($discountType === 'fixed' && $discountValue > 0) {
            $discountedPrice = $originalPrice - $discountValue;
            if ($discountedPrice < 0) $discountedPrice = 0;
        }

        // Insert the new items into the pivot table with discount details
        $this->db->insert('service_package_items', [
            'package_id' => $packageId,
            'item_id' => $itemId,
            'item_type' => $itemType,
            'discount_type' => $discountType,
            'discount_value' => $discountValue,
            'final_price' => $discountedPrice
        ]);
    }

    // Complete the transaction
    $this->db->trans_complete();

    // Check if the transaction was successful
    if ($this->db->trans_status() === FALSE) {
        // Rollback transaction if there was an error
        $this->db->trans_rollback();
        echo json_encode(['status' => 'error', 'message' => 'Failed to update service package']);
    } else {
        // Commit transaction if successful
        $this->db->trans_commit();
        echo json_encode(['status' => 'success', 'message' => 'Service package updated successfully']);
    }
}



public function loadServicePackages()
{
    $this->require_permission('servicepackages.view');
    $packages = $this->service_model->loadServicePackages();
    $data['packages'] = $packages;
    $this->load->view('general/service-packages-list', $data);
}

public function getServicePackage($package_id = null)
{
    $this->require_permission('servicepackages.view');
    if (is_null($package_id)) {
        show_404();
    }
    
    $package = $this->service_model->getServicePackage($package_id);
    
    if (!$package) {
        show_404();
    }
    
    echo json_encode($package);
}

public function deleteServicePackage()
{
    $this->require_permission('servicepackages.delete');
    $packageId = $this->input->post('package_id');

    $this->db->where('id', $packageId)->delete('service_packages');
    $this->db->where('package_id', $packageId)->delete('service_package_items');

    echo json_encode(['status' => 'success', 'message' => 'Service Package deleted successfully.']);
}

public function getServicePackageItem($packageId)
{
    // Get package basic info
    $package = $this->db->get_where('service_packages', ['id' => $packageId])->row();
    
    if (!$package) {
        echo json_encode(['error' => 'Package not found']);
        return;
    }

    // Get package items with discount details
    $this->db->select('si.id as item_id, si.name, si.price, 
                      spi.discount_type, spi.discount_value, spi.final_price, spi.item_type');
    $this->db->from('service_package_items spi');
    $this->db->join('service_items si', 'si.id = spi.item_id');
    $this->db->where('spi.package_id', $packageId);
    $items = $this->db->get()->result();

    $response = [
        'package_name' => $package->package_name,
        'total_price' => $package->total_price,
        'package_discount_type' => $package->package_discount_type ?? 'none',
        'package_discount_value' => $package->package_discount_value ?? 0,
        'availability_start' => $package->availability_start ?? null,
        'availability_end' => $package->availability_end ?? null,
        'items' => $items
    ];

    echo json_encode($response);
}


public function toggleServicePackage()
{
    $this->require_permission('servicepackages.edit');
    $packageId = $this->input->post('package_id');
    $newStatus = $this->input->post('status');

    // Validate input
    if (empty($packageId) || !is_numeric($newStatus)) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid request parameters'
        ]);
        return;
    }

    $userId = $this->session->userdata('userid');
    // Update status
    $this->db->where('id', $packageId)
             ->update('service_packages', ['active' => $newStatus, 'updated_by' => $userId, 'updated_at' => date('Y-m-d H:i:s')]);

    if ($this->db->affected_rows() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Status updated successfully'
        ]);
    } else {
        // Check if package exists
        $exists = $this->db->where('id', $packageId)
                          ->count_all_results('service_packages') > 0;
        
        echo json_encode([
            'success' => false,
            'message' => $exists ? 'No changes made' : 'Package not found'
        ]);
    }
}

    public function loadPackageProducts() {
        $brand_id = $this->input->post('brand_id');
        $category_id = $this->input->post('category_id');
        
        $products = $this->services_model->loadProductsByFilters($category_id, $brand_id, null);
        
        $data['products'] = $products;
        $this->load->view('general/service-package-products-table', $data);
    }


}
?>
