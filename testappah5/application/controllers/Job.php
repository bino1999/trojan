<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Job extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('purchase_model');
        $this->load->model('services_model');
        $this->load->model('wallet_model');
    }

    public function servicesManage()
    {
        $this->require_permission('job.view');

        // Get the status parameter from the URL
        $url_status = $this->input->get('status');
        $url_manager = $this->input->get('manager');

        if ($url_status === null) {
            $url_status = 'all';
        }

        $this->session->set_userdata('filter_job_status', $url_status);
        $this->session->set_userdata('filter_job_status_is_invoices', 0);
        $this->session->set_userdata('filter_job_status_is_manager', ($url_status == 4 && $url_manager === 'yes') ? 1 : 0);


        if ($url_status == 5) {
            $data['mainMenuName'] = 'Invoices';
        } else {
            $data['mainMenuName'] = 'Services Manager';
        }


        if ($url_status == 4 && $url_manager == 'yes') {
            $data['mainMenuName'] = 'Manager Approval';
        }

        $data['subMenuName'] = $url_status;
        $data['url_status'] = $url_status;
        $data['is_manager'] = $this->session->userdata('filter_job_status_is_manager');

        $data['customers'] = $this->services_model->loadCustomer();
        $data['vehicles'] = $this->services_model->loadvehicles();
        $data['vehicleBrands'] = $this->services_model->loadvehicleBrands();
        $data['vehicleCategories'] = $this->services_model->loadvehicleCategories();
        $data['serviceItemCategory'] = $this->services_model->loadServiceItemsCategory();
        $data['employeeSections'] = $this->services_model->loadEmployeeSections();
        $data['itemBrands'] = $this->services_model->loadItemBrands();
        $data['itemCategories'] = $this->services_model->loadItemCategories();
        $data['suppliers'] = $this->services_model->loadSuppliers();

        $data['servicePackages'] = $this->services_model->loadServicesPackages();
        $data['serviceItems'] = $this->services_model->loadServicesItems();

        
        $data['products'] = $this->services_model->loadAvailablePurchaseProducts();


        $this->load->view('layout/header');
        $this->load->view('layout/top_navbar');
        $this->load->view('layout/left_sidebar', $data);
        $this->load->view('job/services-manage', $data);
        $this->load->view('layout/footer');
    }

    public function loadServiceItemsFilterListByCategory()
    {
        $categoryId = $this->input->post('categoryId');
        $sectionId = $this->input->post('sectionId');
        $data['serviceItems'] = $this->services_model->loadServicesItemsByCategory($categoryId, $sectionId);
        $this->load->view('job/service-items-filter-list', $data);
    }

    public function loadServiceProductsFilterListByProduct()
    {
        $categoryId = $this->input->post('categoryId');
        $brandId = $this->input->post('brandId');
        $supplierId = $this->input->post('supplierId');
        $data['products'] = $this->services_model->loadAvailablePurchaseProducts(null, $supplierId, $brandId, $categoryId);
        $this->load->view('job/product-items-filter-list', $data);
    }

    public function invoices()
    {
        $this->require_permission('job.invoice');
        $this->session->set_userdata('filter_job_status', 5);
        $this->session->set_userdata('filter_job_status_is_invoices', 1);

        $data['mainMenuName'] = 'Invoices';
        $data['subMenuName'] = 'Invoices';
        $data['url_status'] = 5;

        $this->load->view('layout/header');
        $this->load->view('layout/top_navbar');
        $this->load->view('layout/left_sidebar', $data);
        $this->load->view('job/invoices', $data);
        $this->load->view('layout/footer');
    }

    public function saveServiceJob()
    {
        $this->require_permission('job.create');
        $errors = [];
        $isNewCustomer = ($this->input->post('btnradio') === 'new_customer');

        // Only validate new customer fields if it's a new customer
        if ($isNewCustomer) {
            if (!$this->input->post('new_salutation')) {
                $errors[] = 'Salutation is required for new customer.';
            }

            if (!$this->input->post('new_customer_name')) {
                $errors[] = 'Customer name is required for new customer.';
            }

            if (!$this->input->post('new_vehicle_no')) {
                $errors[] = 'Vehicle number is required for new customer.';
            } else {
                // Additional validation if needed
                $vehicleNo = $this->input->post('new_vehicle_no');
                if (strlen(trim($vehicleNo)) === 0) {
                    $errors[] = 'Vehicle number cannot be empty.';
                }
            }
        }

        // Common validations (for both new and existing customers)
        if (!$this->input->post('new_mobile')) {
            $errors[] = 'Mobile number is required for new customer.';
        }

        //check mobile number has 10 digit and is numeric
        if (!preg_match('/^[0-9]{10}$/', $this->input->post('new_mobile'))) {
            $errors[] = 'Mobile number must be 10 digits and numeric.';
        }

        if (!$this->input->post('current_mileage')) {
            $errors[] = 'Current mileage is required.';
        }

        if (!$this->input->post('next_service_mileage')) {
            $errors[] = 'Next service mileage is required.';
        }

        //check both millage are same
        if ($this->input->post('current_mileage') >= $this->input->post('next_service_mileage')) {
            $errors[] = 'Next service mileage must be greater than current mileage.';
        }

        //if not select status then set default value as "Pending"
        $job_status = $this->input->post('status');
        if (!$this->input->post('status')) {
            $job_status = 0;
        }

        if ($errors) {
            echo json_encode(['status' => 'error', 'messages' => $errors]);
            return;
        }

        // Process form data
        $service_types = $this->input->post('service_types');
        if (empty($service_types)) {
            $errors[] = 'At least one service type must be selected.';
        }
        
        if ($errors) {
            echo json_encode(['status' => 'error', 'messages' => $errors]);
            return;
        }
        
        // Convert array to comma-separated string
        $service_type_string = implode(',', $service_types);
        
        $data = [
            'job_contact_no' => $this->input->post('new_mobile'),
            'current_mileage' => $this->input->post('current_mileage'),
            'next_service_mileage' => $this->input->post('next_service_mileage'),
            'next_service_date' => $this->input->post('next_service_date'),
            'service_type' => $service_type_string,
            'status' => $job_status,
            'description' => $this->input->post('description'),
            'service_date' => date('Y-m-d'),
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => $this->session->userdata('userid'),
        ];

        // Handle customer type
        if ($this->input->post('btnradio') === 'new_customer') {
            // Insert new customer
            $customer_data = [
                'salutation' => $this->input->post('new_salutation'),
                'name' => $this->input->post('new_customer_name'),
                'mobile' => $this->input->post('new_mobile'),
                'created_at' => date('Y-m-d H:i:s'),
                'created_by' => $this->session->userdata('userid'),
            ];
            $this->db->insert('customers', $customer_data);
            $customer_id = $this->db->insert_id();

            // Insert new vehicle
            $vehicle_data = [
                'owner_id' => $customer_id,
                'vehicle_no' => $this->formatVehicleNumber($this->input->post('new_vehicle_no')),
                'category_id' => $this->input->post('new_category_id'),
                'brand_id' => $this->input->post('new_brand_id'),
                'created_at' => date('Y-m-d H:i:s'),
                'created_by' => $this->session->userdata('userid'),
            ];
            $this->db->insert('vehicles', $vehicle_data);
            $data['vehicle_id'] = $this->db->insert_id();
            $data['customer_id'] = $customer_id;
        } else {
            // Existing customer
            $data['vehicle_id'] = $this->input->post('use_vehicle_id');
            $data['customer_id'] = $this->input->post('use_customer_id');
        }

        // Save service job
        if ($this->db->insert('services_job', $data)) {
            echo json_encode(['status' => 'success', 'message' => 'Service Job created successfully!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to create Service Job. Please try again.']);
        }
    }

    protected function formatVehicleNumber($vehicleNo)
    {
        if (empty($vehicleNo)) return '';

        // Remove all special characters (keep only letters and numbers)
        $cleaned = preg_replace('/[^A-Za-z0-9]/', '', $vehicleNo);

        // Add space between letters and numbers (e.g. "ABC123" → "ABC 123")
        $formatted = preg_replace('/([A-Za-z]+)([0-9]+)/i', '$1 $2', $cleaned);

        return strtoupper($formatted);
    }

    public function loadServicesJobs()
    {

        $sdate = $this->input->post('sdate');
        $edate = $this->input->post('edate');
        $filter_service_type = $this->input->post('filter_service_type');
        $is_credit = $this->input->post('invoicePayType');

        $data['jobs'] = $this->services_model->loadServicesJobs($sdate, $edate, $filter_service_type, $is_credit);
        $this->load->view('job/services-manage-list', $data);
    }

    public function loadInvoices()
    {

        $sdate = $this->input->post('sdate');
        $edate = $this->input->post('edate');

        $data['jobs'] = $this->services_model->loadServicesJobs($sdate, $edate);
        $this->load->view('job/invoices-list', $data);
    }

    public function showServicePackageItems()
    {
        $packageId = $this->input->post('packageId');

        $data['packageItems'] = $this->services_model->showServicePackageItems($packageId);

        if (!empty($data['packageItems'])) {
            // Start the table
            $html = '<table class="table table-sm table-normal">';
            $html .= '<thead class="table-info">';
            $html .= '<tr>';
            $html .= '<th>Item Name</th>';
            $html .= '<th>Original Price</th>';
            $html .= '<th>Discount</th>';
            $html .= '<th>Final Amount</th>';
            $html .= '</tr>';
            $html .= '</thead>';
            $html .= '<tbody>';

            $totalPrice = 0;
            foreach ($data['packageItems'] as $item) {
                // Calculate final amount based on discount type
                $finalAmount = $item->price;
                $discountDisplay = '-';

                if ($item->discount_type == 'percentage' && $item->discount_value > 0) {
                    $discountAmount = $item->price * ($item->discount_value / 100);
                    $finalAmount = $item->price - $discountAmount;
                    $discountDisplay = $item->discount_value . '% (LKR ' . number_format($discountAmount, 2) . ')';
                } elseif ($item->discount_type == 'fixed' && $item->discount_value > 0) {
                    $finalAmount = $item->price - $item->discount_value;
                    $discountDisplay = 'LKR ' . number_format($item->discount_value, 2);
                }

                // Ensure final amount doesn't go below 0
                $finalAmount = max(0, $finalAmount);
                $totalPrice += $finalAmount;

                $html .= '<tr>';
                $html .= '<td style="text-align: left;">' . htmlspecialchars($item->name) . '</td>';
                $html .= '<td>LKR ' . number_format($item->price, 2) . '</td>';
                $html .= '<td>' . $discountDisplay . '</td>';
                $html .= '<td>LKR ' . number_format($finalAmount, 2) . '</td>';
                $html .= '</tr>';
            }

            $html .= '</tbody>';
            $html .= '<tfoot>';
            $html .= '<tr>';
            $html .= '<td colspan="3" style="text-align: left;"><strong>Total:</strong></td>';
            $html .= '<td><strong>LKR ' . number_format($totalPrice, 2) . '</strong></td>';
            $html .= '</tr>';
            $html .= '</tfoot>';
            $html .= '</table>';

            echo $html;
        } else {
            echo '<div class="alert alert-info">No items found for this package.</div>';
        }
    }


    public function addServicePackageToJob()
    {
        $jobId = $this->input->post('jobId');
        $packageId = $this->input->post('packageId');

        // Get package items
        $packageItems = $this->services_model->showServicePackageItems($packageId);

        // Start transaction for data integrity
        $this->db->trans_start();

        foreach ($packageItems as $item) {
            $quantity = 1;
            $price = $item->price;
            $discount_type = $item->discount_type;
            $discount_value = $item->discount_value;

            // Initialize discount variables
            $discountPercent = 0;
            $discountAmount = 0;

            // Calculate discounts based on type
            if ($discount_type == 'percentage' && $discount_value > 0) {
                $discountPercent = $discount_value;
                $discountAmount = $price * ($discount_value / 100);
            } elseif ($discount_type == 'fixed' && $discount_value > 0) {
                $discountAmount = $discount_value;
            }

            // Ensure discount doesn't make price negative
            $discountAmount = min($discountAmount, $price);

            // Prepare data for insertion
            $data = [
                'service_job_id' => $jobId,
                'item_id' => $item->id,
                'item_type' => 'service',
                'package_id' => $packageId,
                'quantity' => $quantity,
                'price' => $price,
                'discount_percent' => $discountPercent,
                'discount_amount' => $discountAmount,
                // total_price will be calculated automatically by the database
                'created_at' => date('Y-m-d H:i:s'),
                'created_by' => $this->session->userdata('userid')
            ];

            $this->db->insert('services_job_items', $data);
        }

        // Complete transaction
        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            // Log error or handle transaction failure
            log_message('error', 'Failed to add package to job: ' . print_r($this->db->error(), TRUE));
            return false;
        }

        return true;
    }

    public function addServiceItemToJob()
    {
        $jobId = $this->input->post('jobId');
        $itemId = $this->input->post('itemID');

        if (empty($jobId) || empty($itemId)) {
            echo json_encode(['status' => 'error', 'message' => 'Job ID or Item ID is missing']);
            return;
        }

        $itemDetails = $this->services_model->loadServicesItems($itemId);
        if (empty($itemDetails)) {
            echo json_encode(['status' => 'error', 'message' => 'Item not found']);
            return;
        }

        $itemPrice = $itemDetails->price;
        $itemName = $itemDetails->name;

        $data = [
            'service_job_id' => $jobId,
            'item_id' => $itemId,
            'item_type' => 'service',
            'package_id' => null,
            'quantity' => 1,  // Default quantity for now
            'price' => $itemPrice,
            'discount_percent' => 0,
            'discount_amount' => 0,
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => $this->session->userdata('userid')
        ];

        // Insert into the database
        if ($this->db->insert('services_job_items', $data)) {
            echo json_encode(['status' => 'success', 'message' => 'Service item added successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to add service item']);
        }
    }

    public function addProductToJob()
    {
        $jobId = $this->input->post('jobId');
        $productId = $this->input->post('productID');
        $po_item_id = $this->input->post('po_item_id');
        $quantity = $this->input->post('quantity');

        if (empty($jobId) || empty($productId) || empty($po_item_id) || empty($quantity)) {
            echo json_encode(['status' => 'error', 'message' => 'Job ID, Product ID or Quantity is missing']);
            return;
        }

        //$productDetails = $this->services_model->loadProducts($productId);
        $productDetails = $this->services_model->loadPurchaseProduct($po_item_id);
        if (empty($productDetails)) {
            echo json_encode(['status' => 'error', 'message' => 'Product not found']);
            return;
        }

        // Check if product price is available
        $productPrice = isset($productDetails->sale_price) ? $productDetails->sale_price : 0; // Set default to 0 if no price
        if ($productPrice <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Product price is invalid ']);
            return;
        }

        $productName = $productDetails->product_name;
        $discount_percent = 0;
        $discount_amount = 0;
        $totalPrice = $productPrice * $quantity;

        // Validate available stock on the purchase item
        $availableStock = isset($productDetails->available_stock) ? floatval($productDetails->available_stock) : 0;
        if (floatval($quantity) > $availableStock) {
            echo json_encode(['status' => 'error', 'message' => 'Quantity exceeds available stock. Available: ' . number_format($availableStock, 2)]);
            return;
        }

        $data = [
            'service_job_id' => $jobId,
            'item_id' => $productId,
            'po_item_id' => $po_item_id,
            'item_type' => 'product',
            'package_id' => null,
            'quantity' => $quantity,
            'price' => $productPrice,
            'discount_percent' => $discount_percent,
            'discount_amount' => $discount_amount,
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => $this->session->userdata('userid')
        ];

        if ($this->db->insert('services_job_items', $data)) {
            // If job is currently ongoing, immediately deduct stock and mark confirmed
            $job = $this->db->get_where('services_job', ['job_id' => $jobId])->row();
            if ($job && intval($job->status) === 1) {
                $this->db->set('available_stock', 'GREATEST(available_stock - ' . (float)$quantity . ', 0)', false);
                $this->db->where('po_item_id', $po_item_id);
                $this->db->update('purchase_order_items');

                $insertedId = $this->db->insert_id();
                $this->db->where('id', $insertedId);
                $this->db->update('services_job_items', ['confirmed_by' => $this->session->userdata('userid'), 'confirmed_at' => date('Y-m-d H:i:s')]);
            }
            echo json_encode(['status' => 'success', 'message' => 'Product added successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to add product']);
        }
    }

    public function addOthersItemToJob()
    {
        $jobId = $this->input->post('jobId');
        $model = $this->input->post('model');
        $description = $this->input->post('description');
        $amount = $this->input->post('amount');

        if (empty($jobId)) {
            echo json_encode(['status' => 'error', 'message' => 'Job ID is missing']);
            return;
        }

        if (empty($description) || empty($amount)) {
            echo json_encode(['status' => 'error', 'message' => 'Description or Amount is missing']);
            return;
        }

        $model = strtolower($model);

        if($model == 'labour') {
            $description = 'Labour charges';
        } elseif($model == 'mechanical') {
            $description = 'Mechanical labour charges';
            $model = 'labour'; // Use labour type for mechanical charges
        }

        $data = [
            'service_job_id' => $jobId,
            'item_id' => 0,
            'item_type' => $model,
            'package_id' => null,
            'description' => $description,
            'quantity' => 1,  // Default quantity for now
            'price' => $amount,
            'discount_percent' => 0,
            'discount_amount' => 0,
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => $this->session->userdata('userid')
        ];

        // Insert into the database
        if ($this->db->insert('services_job_items', $data)) {
            echo json_encode(['status' => 'success', 'message' => 'Record added successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to add item']);
        }
    }

    public function loadJobItems()
    {
        $jobId = $this->input->post('jobId');
        $data['jobData'] = $this->services_model->loadJobData($jobId);
        $data['jobPayment'] = $this->services_model->loadJobTotalPayment($jobId);
        $data['jobItems'] = $this->services_model->loadJobItems($jobId);
        $data['paymentHistory'] = $this->services_model->loadJobPayment($jobId);
        $this->load->view('job/services-job-items-list', $data);
    }

    public function deleteJobItem()
    {
        $this->require_permission('job.edit');
        $itemId = $this->input->post('id');
        $package_id = $this->input->post('package_id');
        $job_id = $this->input->post('job_id');


        if (!$package_id > 0) {
            // Make sure you check for a valid item ID
            if (!is_null($itemId)) {
                $this->db->where('id', $itemId);
                if ($this->db->delete('services_job_items')) {
                    echo json_encode(['status' => 'success', 'message' => 'Item deleted successfully!']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Failed to delete item.']);
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Invalid item ID.']);
            }
        }


        //if package_id > 0 then delete the all package item
        if ($package_id > 0) {
            $this->db->where('service_job_id', $job_id);
            $this->db->where('package_id', $package_id);
            if ($this->db->delete('services_job_items')) {
                echo json_encode(['status' => 'success', 'message' => 'Item deleted successfully!']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to delete item.']);
            }
        }
    }

    public function addDiscountToJob()
    {
        $jobId = $this->input->post('jobId');
        $discountRate = $this->input->post('discountRate');
        $discountAmount = $this->input->post('discountAmount');

        // Validate inputs
        if (empty($jobId)) {
            echo json_encode(['status' => 'error', 'message' => 'Job ID is missing']);
            return;
        }

        // Check if either discountRate or discountAmount is numeric
        if ((!is_numeric($discountRate) || $discountRate < 0) && (!is_numeric($discountAmount) || $discountAmount < 0)) {
            echo json_encode(['status' => 'error', 'message' => 'Discount Rate and Amount must be numeric and non-negative']);
            return;
        }

        // Validate only one of them is entered
        if ($discountRate > 0 && $discountAmount > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Please enter either a discount rate or amount, not both']);
            return;
        }

        if ($discountRate > 0) {
            $discountAmount = 0;
        } else {
            $discountRate = 0;
        }

        // Update the services_job table with the discount rate and amount
        $this->db->where('job_id', $jobId);
        $this->db->update('services_job', [
            'discount_percent' => $discountRate,
            'discount_amount' => $discountAmount
        ]);

        if ($this->db->affected_rows() > 0) {
            echo json_encode(['status' => 'success', 'message' => 'Discount added successfully!']);
            $this->load->model('logs_model');
            $this->logs_model->log_activity('Discount added to job', 'Job ID: ' . $jobId . ', Discount Rate: ' . $discountRate . ', Discount Amount: ' . $discountAmount);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to add discount.']);
        }
    }

    public function addPaymentToJob()
    {
        $this->require_permission('payments.create');
        $jobId = $this->input->post('jobId');
        $paymentMethod = $this->input->post('paymentMethod');
        $paidAmount = $this->input->post('paidAmount');
        $bankName = $this->input->post('bankName');
        $chequeNumber = $this->input->post('chequeNumber');
        $chequeDate = $this->input->post('chequeDate');
        $creditPersonName = $this->input->post('creditPersonName');
        $creditRemark = $this->input->post('creditRemark');
        $creditPayDate = $this->input->post('creditPayDate');

        // Validate required inputs
if (empty($jobId) || empty($paymentMethod)) {
    echo json_encode(['status' => 'error', 'message' => 'Job ID and Payment Method are required']);
    return;
}

// Validate paid amount based on payment method
if ($paymentMethod !== 'credit') {
    if (!is_numeric($paidAmount) || $paidAmount <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Paid Amount must be a positive number']);
        return;
    }
} else {
    // For credit method, allow 0 or null paidAmount
    if (!is_numeric($paidAmount)) {
        $paidAmount = 0.00; // Optional normalization
    }
}

        if ($paymentMethod === 'credit' && empty($creditPersonName)) {
            echo json_encode(['status' => 'error', 'message' => 'Credit Person Name is required when payment method is credit']);
            return;
        }

        // Make expected credit payment date mandatory for credit
        if ($paymentMethod === 'credit') {
            if (empty($creditPayDate)) {
                echo json_encode(['status' => 'error', 'message' => 'Expected Payment Date is required for credit payments']);
                return;
            }
            // validate format YYYY-MM-DD and not in past
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $creditPayDate)) {
                echo json_encode(['status' => 'error', 'message' => 'Invalid Expected Payment Date format']);
                return;
            }
        }

        // Make creditRemark optional
        if (empty($creditRemark)) {
            $creditRemark = null;
        }

        //credit pay date should be today or future date (only if provided)
        if ($paymentMethod === 'credit' && !empty($creditPayDate) && $creditPayDate < date('Y-m-d')) {
            echo json_encode(['status' => 'error', 'message' => 'Credit Pay Date should be today or future date']);
            return;
        }

        // If payment method is cheque, ensure that the cheque number is provided
        if ($paymentMethod === 'cheque' && empty($chequeNumber)) {
            echo json_encode(['status' => 'error', 'message' => 'Cheque number is required when payment method is cheque']);
            return;
        }

        if ($paymentMethod === 'cheque' && empty($chequeDate)) {
            echo json_encode(['status' => 'error', 'message' => 'Cheque date is required when payment method is cheque']);
            return;
        }

        //validate cheque date, its should be today or future date
        if ($paymentMethod === 'cheque' && $chequeDate < date('Y-m-d')) {
            echo json_encode(['status' => 'error', 'message' => 'Cheque date should be today or future date']);
            return;
        }

        // If payment method is bank_transfer, ensure that the transaction ID and bank name are provided
        if ($paymentMethod === 'bank_transfer' && (empty($chequeNumber) || empty($bankName))) {
            echo json_encode(['status' => 'error', 'message' => 'Transaction ID and bank name are required when payment method is bank transfer']);
            return;
        }

        $paymentDate = date('Y-m-d H:i:s');
        $createdBy = $this->session->userdata('userid');
        $createdAt = date('Y-m-d H:i:s');

        // Prepare data to insert
        $data = [
            'service_job_id' => $jobId,
            'payment_method' => $paymentMethod,
            'paid_amount' => $paidAmount,
            'bank_name' => $bankName,
            'cheque_number' => $chequeNumber,
            'cheque_date' => $chequeDate,
            'payment_date' => $paymentDate,
            'created_at' => $createdAt,
            'created_by' => $createdBy,
            'deleted_by' => null,
            'deleted_reason' => null,
        ];

        // If payment method is credit, update services_job table with credit data
        if ($paymentMethod === 'credit') {
            $creditData = [
                'is_credit'     => 1,
                'credit_person' => $creditPersonName,
                'credit_remark' => $creditRemark,
                'credit_pay_date'   => $creditPayDate
            ];

            $this->db->where('job_id', $jobId);
            $this->db->update('services_job', $creditData);
            
            // Create credit transaction in account_transactions
            $jobData = $this->services_model->loadJobData($jobId);
            if ($jobData) {
                // For credit payments, the credit amount should be the actual credit payment amount
                $creditAmount = $paidAmount;
                
                $creditTransactionData = [
                    'account_slug' => 'credits',
                    'description' => 'Job Invoice Credit - ' . $creditPersonName . ' - Job #' . $jobId,
                    'amount' => $creditAmount,
                    'txn_type' => 'credit',
                    'reference_type' => 'service_job',
                    'reference_id' => $jobId,
                    'customer_id' => $jobData->customer_id,
                    'created_by' => $createdBy,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                
                $this->db->insert('account_transactions', $creditTransactionData);
                
                // Update credits account balance
                $this->db->set('balance', 'balance + ' . $creditAmount, FALSE);
                $this->db->where('slug', 'credits');
                $this->db->update('accounts');
            }
        }

        if ($paidAmount > 0) {
            // Process wallet payment (only for non-credit payments)
            if ($paymentMethod !== 'credit') {
                $paymentData = [
                    'payment_method' => $paymentMethod,
                    'amount' => floatval($paidAmount),
                    'description' => $this->generateInvoicePaymentDescription($paymentMethod, $paidAmount, $jobId, $bankName, $chequeNumber),
                    'reference_type' => 'service_job',
                    'reference_id' => $jobId,
                    'customer_id' => null, // Will be determined from job data if needed
                    'created_by' => $createdBy
                ];

                $walletResult = $this->wallet_model->processPayment($paymentData);
                
                if ($walletResult['status'] !== 'success') {
                    echo json_encode(['status' => 'error', 'message' => 'Wallet processing failed: ' . $walletResult['message']]);
                    return;
                }
            }

            // Insert payment record
            if ($this->db->insert('services_job_payments', $data)) {
                // Update job totals
                if ($paymentMethod === 'credit') {
                    // For credit payments, do not change totals; balance is based on actual payments only
                    // No-op for totals on credit
                } else {
                    // For actual payments, update total_paid and reduce balance
                    $this->db->set('total_paid', 'total_paid + ' . $paidAmount, FALSE);
                    $this->db->set('total_balance', 'total_balance - ' . $paidAmount, FALSE);
                }
                $this->db->set('updated_at', date('Y-m-d H:i:s'));
                $this->db->where('job_id', $jobId);
                $this->db->update('services_job');
                
                echo json_encode([
                    'status' => 'success', 
                    'message' => 'Payment added successfully! Wallet balances updated.',
                    'wallet_processed' => ($paymentMethod !== 'credit') ? ($walletResult['total_processed'] ?? $paidAmount) : 0
                ]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to add payment.']);
            }
        } else {
            echo json_encode(['status' => 'success', 'message' => 'Credit recorded without payment.']);
        }
    }

    public function printInvoice($encodedJobId)
    {
        $this->require_permission('job.invoice');
        $jobId = base64_decode(urldecode($encodedJobId));

        // Get the job details from the model using the job ID
        $data['jobData'] = $this->services_model->loadJobData($jobId);
        $data['jobLastPayment'] = $this->services_model->getJobLastPayment($jobId);
        $data['jobItems'] = $this->services_model->loadJobItems($jobId);

        // Check if the job data exists
        if (empty($data['jobData'])) {
            show_404(); // If no job found, show a 404 error
        }

        // Load the view to generate the invoice
        $this->load->view('job/print_invoice', $data);
    }

    public function printMiniInvoice($encodedJobId)
    {
        $jobId = base64_decode(urldecode($encodedJobId));

        // Get the job details from the model using the job ID
        $data['jobData'] = $this->services_model->loadJobData($jobId);
        $data['jobLastPayment'] = $this->services_model->getJobLastPayment($jobId);
        $data['jobItems'] = $this->services_model->loadJobItems($jobId);

        // Check if the job data exists
        if (empty($data['jobData'])) {
            show_404(); // If no job found, show a 404 error
        }

        // Load the view to generate the invoice
        $this->load->view('job/print_mini_invoice', $data);
    }

    public function updateJobStatus()
    {
        $this->require_permission(['job.edit', 'job.progress']);
        $jobId = $this->input->post('jobId');
        $status = $this->input->post('jobStatus');
        $userId = $this->session->userdata('userid');

        if (!$jobId > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Job ID is missing']);
            return;
        }

        if ($status == '') {
            echo json_encode(['status' => 'error', 'message' => 'Status must be a number']);
            return;
        }

        // Prepare data for update
        $updateData = [
            'status' => $status,
            'confirmed_by' => 0,
            'confirmed_at' => null,
            'print_access_at' => null,
            'print_access_by' => 0
        ];

        $updateData2 = [
            'confirmed_at' => null,
            'confirmed_by' => 0
        ];

        $this->db->where('job_id', $jobId);
        $this->db->update('services_job', $updateData);

        if ($this->db->affected_rows() > 0) {
            //update job item table
            $this->db->where('service_job_id', $jobId);
            $this->db->update('services_job_items', $updateData2);

            // If moving to Ongoing (1): deduct stock for all product items not yet confirmed
            if (intval($status) === 1) {
                $items = $this->db->get_where('services_job_items', ['service_job_id' => $jobId, 'item_type' => 'product'])->result();
                foreach ($items as $it) {
                    if (intval($it->confirmed_by) <= 0) {
                        $this->db->set('available_stock', 'GREATEST(available_stock - ' . (float)$it->quantity . ', 0)', false);
                        $this->db->where('po_item_id', $it->po_item_id);
                        $this->db->update('purchase_order_items');
                        $this->db->where('id', $it->id);
                        $this->db->update('services_job_items', ['confirmed_by' => $userId, 'confirmed_at' => date('Y-m-d H:i:s')]);
                    }
                }
            }

            echo json_encode(['status' => 'success', 'message' => 'Status updated successfully!']);
            $this->load->model('logs_model');
            $this->logs_model->log_activity('Job status updated', 'Job ID: ' . $jobId . ', Status: ' . $status);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update status.']);
        }
    }

    public function confirmJobItem()
    {
        $jobItemId = $this->input->post('jobItemId');
        $status = $this->input->post('status');

        if (!$jobItemId > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Job Item ID is missing']);
            return;
        }

        $userId = $this->session->userdata('userid');
        // Update the job item status in the database
        $item = $this->db->get_where('services_job_items', ['id' => $jobItemId])->row();
        if (!$item) {
            echo json_encode(['status' => 'error', 'message' => 'Job item not found']);
            return;
        }

        if ($status == 1) {
            // confirm and deduct stock for products
            if ($item->item_type === 'product') {
                $this->db->set('available_stock', 'GREATEST(available_stock - ' . (float)$item->quantity . ', 0)', false);
                $this->db->where('po_item_id', $item->po_item_id);
                $this->db->update('purchase_order_items');
            }
            $this->db->where('id', $jobItemId);
            $this->db->update('services_job_items', ['confirmed_by' => $userId, 'confirmed_at' => date('Y-m-d H:i:s')]);
        } else {
            // unconfirm: return stock for products
            if ($item->item_type === 'product') {
                $this->db->set('available_stock', 'available_stock + ' . (float)$item->quantity, false);
                $this->db->where('po_item_id', $item->po_item_id);
                $this->db->update('purchase_order_items');
            }
            $this->db->where('id', $jobItemId);
            $this->db->update('services_job_items', ['confirmed_by' => 0, 'confirmed_at' => date('Y-m-d H:i:s')]);
        }


        if ($this->db->affected_rows() > 0) {
            echo json_encode(['status' => 'success', 'message' => 'Status updated successfully!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to confirm job item..']);
        }
    }

    public function confirmJob()
    {
        $this->require_permission('job.approve');
        $jobId = $this->input->post('jobId');
        $userId = $this->session->userdata('userid');

        if (!$jobId > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Job ID is missing']);
            return;
        }

        // Update the job status in the database
        $this->db->where('job_id', $jobId);
        $this->db->update('services_job', ['status' => 5, 'confirmed_by' => $userId, 'confirmed_at' => date('Y-m-d H:i:s'), 'print_access_by' => $userId, 'print_access_at' => date('Y-m-d H:i:s')]);

        if ($this->db->affected_rows() > 0) {
            echo json_encode(['status' => 'success', 'message' => 'Job confirmation updated successfully!']);
            //log set
            $this->load->model('logs_model');
            $this->logs_model->log_activity('Job confirmed', 'Job ID: ' . $jobId);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update job confirmation.']);
        }
    }

    public function addPrintAccess()
    {
        $jobId = $this->input->post('jobId');
        $userId = $this->session->userdata('userid');

        if (!$jobId > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Job ID is missing']);
            return;
        }

        // Update the job status in the database
        $this->db->where('job_id', $jobId);
        $this->db->update('services_job', ['print_access_by' => $userId, 'print_access_at' => date('Y-m-d H:i:s')]);

        if ($this->db->affected_rows() > 0) {
            echo json_encode(['status' => 'success', 'message' => 'Print Access updated successfully!']);
            //log set
            $this->load->model('logs_model');
            $this->logs_model->log_activity('Job Print Access updated', 'Job ID: ' . $jobId);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update Print Access.']);
        }
    }

    public function updateItemDiscount()
    {

        // Get input data
        $itemId = $this->input->post('item_id');
        $discount = $this->input->post('discount');
        $discount_amount = $this->input->post('discount_amount');

        // Validate input
        if (!is_numeric($itemId)) {
            echo json_encode(['success' => false, 'message' => 'Invalid item ID']);
            return;
        }

        if (!is_numeric($discount)) {
            echo json_encode(['success' => false, 'message' => 'Discount must be a number']);
            return;
        }

        if (!is_numeric($discount_amount)) {
            echo json_encode(['success' => false, 'message' => 'Discount amount must be a number']);
            return;
        }


        $discount = floatval($discount);
        if ($discount < 0 || $discount > 100) {
            echo json_encode(['success' => false, 'message' => 'Discount must be between 0-100%']);
            return;
        }

        //check both discount and discount_amount are same
        if ($discount > 0 && $discount_amount > 0) {
            echo json_encode(['success' => false, 'message' => 'Please enter either a discount rate or amount, not both']);
            return;
        }


        $data = [
            'discount_percent' => $discount,
            'discount_amount' => $discount_amount,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        $this->db->where('id', $itemId);
        $this->db->update('services_job_items', $data);

        if ($this->db->affected_rows() > 0) {
            echo json_encode(['success' => true, 'message' => 'Discount updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No changes made or item not found']);
        }
    }

    public function updateServiceType()
    {
        // Log the incoming data
        log_message('debug', 'updateServiceType called with jobId: ' . $this->input->post('jobId') . ', serviceTypes: ' . json_encode($this->input->post('serviceTypes')));
        
        $jobId = $this->input->post('jobId');
        $serviceTypes = $this->input->post('serviceTypes');

        if (empty($jobId)) {
            log_message('error', 'Job ID is missing in updateServiceType');
            echo json_encode(['status' => 'error', 'message' => 'Job ID is missing']);
            return;
        }

        if (empty($serviceTypes)) {
            log_message('error', 'Service types are missing in updateServiceType');
            echo json_encode(['status' => 'error', 'message' => 'Service types are missing']);
            return;
        }

        // Check if job exists and is in ongoing or hold status
        $job = $this->db->get_where('services_job', ['job_id' => $jobId])->row();
        if (!$job) {
            log_message('error', 'Job not found in updateServiceType: ' . $jobId);
            echo json_encode(['status' => 'error', 'message' => 'Job not found']);
            return;
        }

        if ($job->status != 0 && $job->status != 1 && $job->status != 2) {
            log_message('error', 'Service type update attempted for job with invalid status: ' . $job->status);
            echo json_encode(['status' => 'error', 'message' => 'Service type can only be updated for pending, ongoing, or hold jobs']);
            return;
        }

        // Convert array to comma-separated string
        $service_type_string = implode(',', $serviceTypes);
        log_message('debug', 'Updating service type to: ' . $service_type_string);

        // Update the service type
        $this->db->where('job_id', $jobId);
        $updateData = [
            'service_type' => $service_type_string,
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => $this->session->userdata('userid')
        ];
        
        $result = $this->db->update('services_job', $updateData);
        log_message('debug', 'Update result: ' . ($result ? 'true' : 'false') . ', affected rows: ' . $this->db->affected_rows());

        // Check if update was successful (either affected rows > 0 or no database errors)
        if ($result && ($this->db->affected_rows() > 0 || $this->db->last_query())) {
            log_message('info', 'Service type updated successfully for job: ' . $jobId);
            echo json_encode(['status' => 'success', 'message' => 'Service type updated successfully']);
        } else {
            // Check if the current service type is already the same
            $currentJob = $this->db->get_where('services_job', ['job_id' => $jobId])->row();
            if ($currentJob && $currentJob->service_type === $service_type_string) {
                log_message('info', 'Service type already up to date for job: ' . $jobId);
                echo json_encode(['status' => 'success', 'message' => 'Service type is already up to date']);
            } else {
                log_message('error', 'Failed to update service type for job: ' . $jobId);
                echo json_encode(['status' => 'error', 'message' => 'Failed to update service type']);
            }
        }
    }

    // Outsource Parts Methods
    public function addOutsourcePartToJob()
    {
        $jobId = $this->input->post('jobId');
        $itemName = $this->input->post('itemName');
        $purchasedFrom = $this->input->post('purchasedFrom');
        $purchasedPrice = $this->input->post('purchasedPrice');
        $invoicePrice = $this->input->post('invoicePrice');

        // Validate inputs
        if (empty($jobId)) {
            echo json_encode(['status' => 'error', 'message' => 'Job ID is missing']);
            return;
        }

        if (empty($itemName)) {
            echo json_encode(['status' => 'error', 'message' => 'Item name is required']);
            return;
        }

        if (empty($purchasedFrom)) {
            echo json_encode(['status' => 'error', 'message' => 'Purchased from is required']);
            return;
        }

        if (!is_numeric($purchasedPrice) || $purchasedPrice < 0) {
            echo json_encode(['status' => 'error', 'message' => 'Purchased price must be a valid number']);
            return;
        }

        if (!is_numeric($invoicePrice) || $invoicePrice < 0) {
            echo json_encode(['status' => 'error', 'message' => 'Invoice price must be a valid number']);
            return;
        }

        // Prepare data for insertion
        $data = [
            'service_job_id' => $jobId,
            'item_name' => $itemName,
            'purchased_from' => $purchasedFrom,
            'purchased_price' => $purchasedPrice,
            'invoice_price' => $invoicePrice,
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => $this->session->userdata('userid')
        ];

        // Insert into outsource_parts table
        if ($this->services_model->addOutsourcePart($data)) {
            // Also add to services_job_items table for billing
            $jobItemData = [
                'service_job_id' => $jobId,
                'item_id' => 0,
                'item_type' => 'outsource',
                'package_id' => null,
                'description' => $itemName,
                'quantity' => 1,
                'price' => $invoicePrice,
                'discount_percent' => 0,
                'discount_amount' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'created_by' => $this->session->userdata('userid')
            ];

            $this->db->insert('services_job_items', $jobItemData);

            echo json_encode(['status' => 'success', 'message' => 'Outsource part added successfully!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to add outsource part']);
        }
    }

    public function loadOutsourceParts()
    {
        $jobId = $this->input->post('jobId');
        $data['outsourceParts'] = $this->services_model->loadOutsourceParts($jobId);
        $this->load->view('job/outsource-parts-list', $data);
    }

    public function deleteOutsourcePart()
    {
        $partId = $this->input->post('partId');
        $jobId = $this->input->post('jobId');

        if (empty($partId)) {
            echo json_encode(['status' => 'error', 'message' => 'Part ID is missing']);
            return;
        }

        // Get the part details to find the corresponding job item
        $part = $this->services_model->getOutsourcePart($partId);
        if (!$part) {
            echo json_encode(['status' => 'error', 'message' => 'Part not found']);
            return;
        }

        // Delete from outsource_parts table
        if ($this->services_model->deleteOutsourcePart($partId)) {
            // Also delete the corresponding job item
            $this->db->where('service_job_id', $jobId);
            $this->db->where('item_type', 'outsource');
            $this->db->where('description', $part->item_name);
            $this->db->delete('services_job_items');

            echo json_encode(['status' => 'success', 'message' => 'Outsource part deleted successfully!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to delete outsource part']);
        }
    }

    public function getJobBalance()
    {
        $jobId = $this->input->post('jobId');
        
        if (empty($jobId)) {
            echo json_encode(['status' => 'error', 'message' => 'Job ID is missing']);
            return;
        }

        // Get job data
        $jobData = $this->services_model->loadJobData($jobId);
        if (!$jobData) {
            echo json_encode(['status' => 'error', 'message' => 'Job not found']);
            return;
        }

        // Get total payments
        $jobPayment = $this->services_model->loadJobTotalPayment($jobId);
        $totalPaid = isset($jobPayment->total_payment) ? $jobPayment->total_payment : 0;
        
        // Calculate balance
        $balance = $jobData->final_bill_amount - $totalPaid;
        
        echo json_encode([
            'status' => 'success',
            'balance' => $balance,
            'totalPaid' => $totalPaid,
            'finalAmount' => $jobData->final_bill_amount
        ]);
    }

    public function confirmInvoice()
    {
        $jobId = $this->input->post('jobId');
        
        if (empty($jobId)) {
            echo json_encode(['status' => 'error', 'message' => 'Job ID is missing']);
            return;
        }

        // Get job data
        $jobData = $this->services_model->loadJobData($jobId);
        if (!$jobData) {
            echo json_encode(['status' => 'error', 'message' => 'Job not found']);
            return;
        }

        // Check if payments are complete
        $jobPayment = $this->services_model->loadJobTotalPayment($jobId);
        $totalPaid = isset($jobPayment->total_payment) ? $jobPayment->total_payment : 0;
        
        if ($totalPaid < $jobData->final_bill_amount) {
            echo json_encode(['status' => 'error', 'message' => 'Cannot confirm invoice: payments are not complete']);
            return;
        }

        // Update the job to mark it as confirmed
        $this->db->where('job_id', $jobId);
        $updateData = [
            'confirmed_by' => $this->session->userdata('userid'),
            'confirmed_at' => date('Y-m-d H:i:s')
        ];
        
        if ($this->db->update('services_job', $updateData)) {
            echo json_encode(['status' => 'success', 'message' => 'Invoice confirmed successfully!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to confirm invoice']);
        }
    }

    public function deletePaymentFromJob()
    {
        $this->require_permission('payments.delete');
        $jobId = $this->input->post('jobId');
        $paymentId = $this->input->post('paymentId');
        
        if (empty($jobId) || empty($paymentId)) {
            echo json_encode(['status' => 'error', 'message' => 'Job ID or Payment ID is missing']);
            return;
        }

        // Check if invoice is already confirmed
        $jobData = $this->services_model->loadJobData($jobId);
        if (!$jobData) {
            echo json_encode(['status' => 'error', 'message' => 'Job not found']);
            return;
        }

        if ($jobData->confirmed_by > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Cannot delete payment: invoice is already confirmed']);
            return;
        }

        // Get payment details
        $payment = $this->db->get_where('services_job_payments', ['id' => $paymentId, 'service_job_id' => $jobId])->row();
        if (!$payment) {
            echo json_encode(['status' => 'error', 'message' => 'Payment not found']);
            return;
        }

        // Process wallet reversal if payment amount > 0
        if ($payment->paid_amount > 0) {
            $reversalData = [
                'payment_method' => $payment->payment_method,
                'amount' => floatval($payment->paid_amount),
                'description' => "REVERSAL: " . $this->generateInvoicePaymentDescription($payment->payment_method, $payment->paid_amount, $jobId, $payment->bank_name, $payment->cheque_number),
                'reference_type' => 'service_job',
                'reference_id' => $jobId,
                'customer_id' => null,
                'created_by' => $this->session->userdata('userid')
            ];

            // Process reversal (debit from account)
            $reversalResult = $this->wallet_model->processPaymentReversal($reversalData);
            
            if ($reversalResult['status'] !== 'success') {
                echo json_encode(['status' => 'error', 'message' => 'Wallet reversal failed: ' . $reversalResult['message']]);
                return;
            }
        }

        // Delete the payment
        $this->db->where('id', $paymentId);
        $this->db->where('service_job_id', $jobId);
        
        if ($this->db->delete('services_job_payments')) {
            echo json_encode(['status' => 'success', 'message' => 'Payment deleted successfully! Wallet balances updated.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to delete payment']);
        }
    }

    public function loadPaymentHistory()
    {
        $jobId = $this->input->post('jobId');
        
        if (empty($jobId)) {
            echo json_encode(['status' => 'error', 'message' => 'Job ID is missing']);
            return;
        }

        // Get job data
        $jobData = $this->services_model->loadJobData($jobId);
        if (!$jobData) {
            echo json_encode(['status' => 'error', 'message' => 'Job not found']);
            return;
        }

        // Get total payments
        $jobPayment = $this->services_model->loadJobTotalPayment($jobId);
        $totalPaid = isset($jobPayment->total_payment) ? $jobPayment->total_payment : 0;
        
        // Calculate balance
        $balance = $jobData->final_bill_amount - $totalPaid;
        
        // Get payment history
        $paymentHistory = $this->services_model->loadJobPayment($jobId);
        
        // Load the payment history view
        $data['jobData'] = $jobData;
        $data['paymentHistory'] = $paymentHistory;
        $data['totalPaid'] = $totalPaid;
        $data['balance'] = $balance;
        $data['final_total_amount'] = $jobData->final_bill_amount;
        
        $this->load->view('job/payment-history-table', $data);
    }

    public function getJobPaymentSummary()
    {
        $jobId = $this->input->post('jobId');
        
        if (empty($jobId)) {
            echo json_encode(['status' => 'error', 'message' => 'Job ID is missing']);
            return;
        }

        // Get job data
        $jobData = $this->services_model->loadJobData($jobId);
        if (!$jobData) {
            echo json_encode(['status' => 'error', 'message' => 'Job not found']);
            return;
        }

        // Get total payments
        $jobPayment = $this->services_model->loadJobTotalPayment($jobId);
        $totalPaid = isset($jobPayment->total_payment) ? $jobPayment->total_payment : 0;
        
        // Calculate balance
        $balance = $jobData->final_bill_amount - $totalPaid;
        
        // Get payment history
        $this->db->select('sjp.*, u.name as UserName');
        $this->db->from('services_job_payments sjp');
        $this->db->join('users u', 'u.userid = sjp.created_by', 'left');
        $this->db->where('sjp.service_job_id', $jobId);
        $this->db->where('sjp.deleted_by IS NULL');
        $this->db->order_by('sjp.payment_date', 'ASC');
        $paymentHistory = $this->db->get()->result();
        
        $response = [
            'status' => 'success',
            'balance' => $balance,
            'totalPaid' => $totalPaid,
            'finalAmount' => $jobData->final_bill_amount,
            'paymentHistory' => $paymentHistory
        ];
        
        // Log for debugging
        error_log("Payment summary for job $jobId: " . json_encode($response));
        
        echo json_encode($response);
    }

    /**
     * Generate payment description for invoice payments
     */
    private function generateInvoicePaymentDescription($paymentMethod, $amount, $jobId, $bankName = null, $chequeNumber = null)
    {
        $method = ucfirst($paymentMethod);
        $amountFormatted = number_format($amount, 2);
        
        $description = "{$method} payment of LKR {$amountFormatted} for Service Job #{$jobId}";
        
        // Add additional details if available
        if (!empty($chequeNumber)) {
            $description .= " (Ref: {$chequeNumber})";
        }
        
        if (!empty($bankName)) {
            $description .= " - {$bankName}";
        }

        return $description;
    }

}
