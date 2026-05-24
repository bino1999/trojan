<?php
defined('BASEPATH') or exit('No direct script access allowed');

class QuickBill extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('purchase_model');
        $this->load->model('services_model');
        $this->load->model('products_model');
        $this->load->model('general_model');
        $this->load->model('wallet_model');
    }

    public function quickBill()
    {
        $this->require_permission('quickbill.create');
        
        $data['mainMenuName'] = 'Quick Bill';
        $data['subMenuName'] = 'quick-bill';

        $data['itemBrands'] = $this->services_model->loadItemBrands();
        $data['itemCategories'] = $this->services_model->loadItemCategories();
        $data['suppliers'] = $this->services_model->loadSuppliers();
        //$data['products'] = $this->services_model->loadProducts();
        $data['products'] = $this->services_model->loadAvailablePurchaseProducts();

        $this->load->view('layout/header');
        $this->load->view('layout/top_navbar');
        $this->load->view('layout/left_sidebar', $data);
        $this->load->view('quick-bill/quick-bill', $data);
        $this->load->view('layout/footer');
    }

    public function quickBillList()
    {
        $this->require_permission('quickbill.view');
        
        $data['mainMenuName'] = 'Quick Bill';
        $data['subMenuName'] = 'quick-bill-list';

        $this->load->view('layout/header');
        $this->load->view('layout/top_navbar');
        $this->load->view('layout/left_sidebar', $data);
        $this->load->view('quick-bill/quick-bill-invoice', $data);
        $this->load->view('layout/footer');
    }

    public function loadQuickBillList()
    {
        $this->require_permission('quickbill.view');
        
        $sdate = $this->input->post('sdate');
        $edate = $this->input->post('edate');

        // Load all quick bills within date range
        $this->load->model('QuickBill_model');
        $data['quickBills'] = $this->QuickBill_model->get_all_quick_bills($sdate, $edate);
        $this->load->view('quick-bill/quick-bill-invoice-list', $data);
    }   

    public function loadServiceProductsFilterListByProduct()
    {
        $this->require_permission('quickbill.create');
        
        $categoryId = $this->input->post('categoryId');
        $brandId = $this->input->post('brandId');
        $supplierId = $this->input->post('supplierId');
        
        // Debug logging
        error_log("Filter values - Category: $categoryId, Brand: $brandId, Supplier: $supplierId");
        
        $data['products'] = $this->services_model->loadAvailablePurchaseProducts(null, $supplierId, $brandId, $categoryId);
        
        // Debug logging
        error_log("Products returned: " . count($data['products']));
        
        $this->load->view('quick-bill/product-items-filter-list', $data);
    }

    public function addProductToBill()
    {
        $this->require_permission('quickbill.create');
        
        $jobId = $this->input->post('jobId');
        $productId = $this->input->post('productID');
        $po_item_id = $this->input->post('po_item_id');
        $quantity = $this->input->post('quantity');
        $discountPercent = $this->input->post('discountPercent');
        $discountAmount = $this->input->post('discountAmount');

        //$productDetails = $this->services_model->loadProducts($productId);
        $productDetails = $this->services_model->loadPurchaseProduct($po_item_id);
        if (empty($productDetails)) {
            echo json_encode(['status' => 'error', 'message' => 'Product not found']);
            return;
        }

        $productPrice = isset($productDetails->sale_price) ? $productDetails->sale_price : 0;
        if ($productPrice <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Product price is invalid']);
            return;
        }

        $discountPercent = floatval($discountPercent);
        $discountAmount = floatval($discountAmount);

        // Apply discount logic: only one type allowed
        if ($discountPercent > 0 && $discountAmount > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Cannot apply both discount percent and discount amount']);
            return;
        }

        // Enforce business rule: prevent free items
        if ($discountPercent >= 100) {
            echo json_encode(['status' => 'error', 'message' => 'Discount percentage must be less than 100%.']);
            return;
        }

        // Calculate total before discount
        $totalBeforeDiscount = $productPrice * $quantity;

        if ($discountPercent > 0) {
            $discountAmount = ($totalBeforeDiscount * $discountPercent) / 100;
        } elseif ($discountAmount > 0) {
            $discountPercent = 0;
        } else {
            $discountAmount = 0;
            $discountPercent = 0;
        }

        if ($discountAmount >= $totalBeforeDiscount && $totalBeforeDiscount > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Discount amount must be less than the line total.']);
            return;
        }

        $product_name = htmlspecialchars($productDetails->product_name, ENT_QUOTES, 'UTF-8');

        // Validate available stock for this PO item considering existing cart quantities
        $availableStock = isset($productDetails->available_stock) ? floatval($productDetails->available_stock) : 0;
        $cart = $this->session->userdata('pos_cart');
        if (!is_array($cart)) {
            $cart = [];
        }

        $existingQtyInCart = 0;
        foreach ($cart as $ci) {
            if (isset($ci['po_item_id']) && $ci['po_item_id'] == $po_item_id) {
                $existingQtyInCart += floatval($ci['quantity']);
            }
        }

        if (($existingQtyInCart + floatval($quantity)) > $availableStock) {
            echo json_encode(['status' => 'error', 'message' => 'Quantity exceeds available stock. Available: ' . number_format($availableStock - $existingQtyInCart, 2)]);
            return;
        }

        // Calculate total with proper discount logic
        $totalBeforeDiscount = $productPrice * $quantity;
        if ($discountPercent > 0) {
            $total = $totalBeforeDiscount - ($totalBeforeDiscount * $discountPercent / 100);
        } else {
            $total = $totalBeforeDiscount - $discountAmount;
        }

        $productItem = [
            'po_item_id'       => $po_item_id,
            'product_id'       => $productId,
            'product_name'     => $product_name,
            'quantity'         => $quantity,
            'unit_price'       => $productPrice,
            'discount_percent' => $discountPercent,
            'discount_amount'  => $discountAmount,
            'total'            => $total
        ];

        // Retrieve existing cart from session
        $cart = $this->session->userdata('pos_cart');
        if (!is_array($cart)) {
            $cart = [];
        }

        $found = false;

        // Check if product already exists in cart with same price and discount
        foreach ($cart as &$item) {
            if (
                $item['po_item_id'] == $po_item_id &&
                $item['product_id'] == $productId &&
                $item['unit_price'] == $productPrice &&
                $item['discount_percent'] == $discountPercent &&
                $item['discount_amount'] == $discountAmount
            ) {
                $item['quantity'] += $quantity;
                // Recalculate total with proper discount logic
                $newTotalBeforeDiscount = $item['unit_price'] * $item['quantity'];
                if ($item['discount_percent'] > 0) {
                    $item['total'] = $newTotalBeforeDiscount - ($newTotalBeforeDiscount * $item['discount_percent'] / 100);
                } else {
                    $item['total'] = $newTotalBeforeDiscount - $item['discount_amount'];
                }
                $found = true;
                break;
            }
        }
        unset($item);

        // If not found, add as new item
        if (!$found) {
            $cart[] = [
                'po_item_id'       => $po_item_id,
                'product_id'       => $productId,
                'product_name'     => $productDetails->product_name,
                'quantity'         => $quantity,
                'unit_price'       => $productPrice,
                'discount_percent' => $discountPercent,
                'discount_amount'  => $discountAmount,
                'total'            => $total
            ];
        }

        // Save updated cart to session
        $this->session->set_userdata('pos_cart', $cart);
        echo json_encode(['status' => 'success', 'message' => 'Product added to cart successfully']);
    }

    public function loadBillItems()
    {
        $this->require_permission('quickbill.create');
        
        $cart = $this->session->userdata('pos_cart');
        if (!is_array($cart)) {
            $cart = [];
        }

        // Calculate cart total
        $total = 0;
        foreach ($cart as $item) {
            $total += $item['total'];
        }

        $data = [
            'cart' => $cart,
            'total' => $total,
            'customers' => $this->products_model->getAllCustomers(),
            'payment_methods' => ['Cash', 'Card', 'Cheque', 'Credit', 'Bank Transfer'],
        ];

        $this->load->view('quick-bill/quick-bill-items-list', $data);
    }


    public function deleteCartItem()
    {
        $this->require_permission('quickbill.create');
        
        $index = $this->input->post('index');
        $cart = $this->session->userdata('pos_cart');

        if (!is_array($cart) || !isset($cart[$index])) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid cart item index']);
            return;
        }

        unset($cart[$index]);
        // Reindex the array
        $cart = array_values($cart);
        $this->session->set_userdata('pos_cart', $cart);

        echo json_encode(['status' => 'success', 'message' => 'Item removed']);
    }

    public function saveCompletedBill()
    {
        $this->require_permission('quickbill.create');
        
        $customerId      = $this->input->post('customerId');
        $paymentMethods  = $this->input->post('paymentMethods');
        $note            = $this->input->post('note');
        $billDiscountPercent = floatval($this->input->post('billDiscountPercent'));
        $billDiscountAmount  = floatval($this->input->post('billDiscountAmount'));
        $cart            = $this->session->userdata('pos_cart');
        
        // Parse payment methods JSON
        $paymentMethodsArray = json_decode($paymentMethods, true);
        if (!$paymentMethodsArray) {
            $paymentMethodsArray = [];
        }

        if (empty($cart)) {
            echo json_encode(['status' => 'error', 'message' => 'Cart is empty.']);
            return;
        }

        // Validate payment methods
        $totalPaid = 0;
        $totalCredit = 0;
        foreach ($paymentMethodsArray as $payment) {
            if (strtolower($payment['method']) !== 'credit') {
                $totalPaid += floatval($payment['amount']);
            } else {
                $totalCredit += floatval($payment['amount']);
            }
            
            // Validate required fields for each payment method
            if (strtolower($payment['method']) == 'card' && (empty($payment['cardOrChequeNo']) || empty($payment['bankName']))) {
                echo json_encode(['status' => 'error', 'message' => 'Card details & bank required for card payments.']);
                return;
            }
            
            if (strtolower($payment['method']) == 'cheque' && (empty($payment['cardOrChequeNo']) || empty($payment['chequeDate']) || empty($payment['bankName']))) {
                echo json_encode(['status' => 'error', 'message' => 'Cheque details required for cheque payments.']);
                return;
            }
            
            if (strtolower($payment['method']) == 'bank transfer' && (empty($payment['cardOrChequeNo']) || empty($payment['bankName']))) {
                echo json_encode(['status' => 'error', 'message' => 'Transaction ID and bank name required for bank transfer payments.']);
                return;
            }
            
            // Check if customer is required for credit
            if (strtolower($payment['method']) == 'credit' && !$customerId > 0) {
                echo json_encode(['status' => 'error', 'message' => 'Please select a customer for credit bill.']);
                return;
            }

            // Validate required credit due date when method is Credit
            if (strtolower($payment['method']) == 'credit') {
                if (empty($payment['creditDueDate'])) {
                    echo json_encode(['status' => 'error', 'message' => 'Credit Date (due) is required for credit payments.']);
                    return;
                }
                // Basic date format check YYYY-MM-DD
                if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $payment['creditDueDate'])) {
                    echo json_encode(['status' => 'error', 'message' => 'Invalid Credit Date format.']);
                    return;
                }
            }
        }

        // Calculate totals
        $billAmount = 0;
        foreach ($cart as $item) {
            $billAmount += $item['total'];
        }

        // Compute bill-level discount (only one type allowed)
        if ($billDiscountPercent > 0 && $billDiscountAmount > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Cannot apply both total discount percent and amount']);
            return;
        }
        if ($billDiscountPercent < 0 || $billDiscountPercent >= 100) {
            if ($billDiscountPercent > 0) {
                echo json_encode(['status' => 'error', 'message' => 'Bill discount percent must be between 0 and 100']);
                return;
            }
            $billDiscountPercent = 0;
        }
        if ($billDiscountAmount < 0) {
            $billDiscountAmount = 0;
        }

        $computedDiscountAmount = 0;
        if ($billDiscountPercent > 0) {
            $computedDiscountAmount = ($billAmount * $billDiscountPercent) / 100.0;
        } elseif ($billDiscountAmount > 0) {
            $computedDiscountAmount = $billDiscountAmount;
        }
        // Clamp to bill amount
        if ($computedDiscountAmount > $billAmount) {
            $computedDiscountAmount = $billAmount;
        }
        $finalBillAmount = $billAmount - $computedDiscountAmount;

        // Calculate remaining balance (positive = amount owed, negative = overpaid)
        $remainingBalance = $finalBillAmount - $totalPaid;
        
        // Save bill
        $billData = [
            'customer_id'       => $customerId,
            'description'       => $note,
            'bill_amount'       => $billAmount,
            'discount_percent'  => $billDiscountPercent,
            'discount_amount'   => $computedDiscountAmount,
            'final_bill_amount' => $finalBillAmount,
            'total_paid'        => $totalPaid, // Only actual payments, not credit
            'total_balance'     => $remainingBalance, // This will include credit amount as balance
            'created_at'        => date('Y-m-d H:i:s'),
            'created_by'        => $this->session->userdata('userid'),
        ];

        // Attach credit person details if any payment method is Credit
        $creditPersonName = null;
        $creditPersonPhone = null;
        $creditDueDate = null;
        foreach ($paymentMethodsArray as $payment) {
            if (strtolower($payment['method']) === 'credit') {
                $creditPersonName = $payment['creditPersonName'] ?: null;
                $creditPersonPhone = $payment['creditPersonPhone'] ?: null;
                $creditDueDate = $payment['creditDueDate'] ?: null;
                break;
            }
        }
        if ($creditPersonName || $creditPersonPhone) {
            $billData['credit_person_name'] = $creditPersonName;
            $billData['credit_person_phone'] = $creditPersonPhone;
        }
        if ($creditDueDate) {
            $billData['credit_due_date'] = $creditDueDate;
        }

        $this->db->insert('quick_bill', $billData);
        $billId = $this->db->insert_id();

        foreach ($cart as $item) {
            $itemData = [
                'quick_bill_id'     => $billId,
                'item_id'           => $item['product_id'],
                'po_item_id'        => $item['po_item_id'],
                'quantity'          => $item['quantity'],
                'price'             => $item['unit_price'],
                'discount_percent'  => $item['discount_percent'],
                'discount_amount'   => $item['discount_amount'],
                //'total_price'       => $item['total'],
                'created_at'        => date('Y-m-d H:i:s'),
                'created_by'        => $this->session->userdata('userid'),
            ];
            $this->db->insert('quick_bill_items', $itemData);

            // Deduct stock from purchase_order_items.available_stock
            $this->db->set('available_stock', 'GREATEST(available_stock - ' . (float)$item['quantity'] . ', 0)', false);
            $this->db->where('po_item_id', $item['po_item_id']);
            $this->db->update('purchase_order_items');
        }

        // Process wallet payments
        $walletResult = $this->wallet_model->processMultiplePayments(
            $paymentMethodsArray,
            'quick_bill',
            $billId,
            $customerId,
            $this->session->userdata('userid')
        );

        if ($walletResult['status'] !== 'success') {
            echo json_encode(['status' => 'error', 'message' => 'Wallet processing failed: ' . $walletResult['message']]);
            return;
        }

        // Save multiple payment info
        foreach ($paymentMethodsArray as $payment) {
            $paymentData = [
                'quick_bill_id'      => $billId,
                'paid_amount'        => $payment['amount'],
                'payment_date'       => date('Y-m-d H:i:s'),
                'payment_method'     => $payment['method'],
                'card_or_cheque_no'  => $payment['cardOrChequeNo'] ?: null,
                'cheque_date'        => $payment['chequeDate'] ?: null,
                'bank_name'          => $payment['bankName'] ?: null,
                'note'               => $note ?: null,
                'created_by'         => $this->session->userdata('userid'),
                'created_at'         => date('Y-m-d H:i:s'),
            ];
            if (strtolower($payment['method']) === 'credit') {
                $paymentData['credit_date'] = isset($payment['creditDueDate']) ? $payment['creditDueDate'] : null;
            }

            $this->db->insert('quick_bill_payments', $paymentData);
        }

        // Clear session cart
        $this->session->unset_userdata('pos_cart');
        
        // Generate appropriate success message based on payment status
        $paymentStatus = '';
        if ($remainingBalance > 0) {
            $paymentStatus = "Partial payment received. Balance remaining: LKR " . number_format($remainingBalance, 2);
        } elseif ($remainingBalance < 0) {
            $paymentStatus = "Overpaid by LKR " . number_format(abs($remainingBalance), 2);
        } else {
            $paymentStatus = "Payment completed successfully";
        }
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Bill saved successfully. ' . $paymentStatus . '. Wallet balances updated.',
            'bill_id' => $billId,
            'wallet_processed' => $walletResult['total_processed'],
            'remaining_balance' => $remainingBalance
        ]);
    }


    public function print_receipt($bill_id)
    {
        $this->require_permission('quickbill.view');
        
        $this->load->model('QuickBill_model');
        $data['bill'] = $this->QuickBill_model->get_bill_by_id($bill_id);
        $data['items'] = $this->QuickBill_model->get_bill_items($bill_id);
        $data['payments'] = $this->QuickBill_model->get_bill_payments($bill_id);

        if (empty($data['bill'])) {
            show_404();
        }

        $data['quick_bill_id'] = $bill_id;
        $data['logo_url'] = base_url('assets/images/logo.png');
        $data['shop_name'] = 'TROJA AUTO HUB';
        $data['shop_address'] = "No 664/1, Yatihena, Malwana";
        $data['shop_phone'] = 'Hotline: 075 500 500 9  |  Tel: 011 299 68 68';


        $this->load->view('quick-bill/print_receipt', $data);
    }

    public function printA4Receipt($bill_id)
    {
        $this->require_permission('quickbill.view');
        $this->load->model('QuickBill_model');
        $data['bill'] = $this->QuickBill_model->get_bill_by_id($bill_id);
        $data['items'] = $this->QuickBill_model->get_bill_items($bill_id);
        $data['payments'] = $this->QuickBill_model->get_bill_payments($bill_id);

        if (empty($data['bill'])) {
            show_404();
        }

        $data['quick_bill_id'] = $bill_id;
        $data['logo_url'] = base_url('assets/images/logo.png');
        $data['shop_name'] = 'TROJA AUTO HUB';
        $data['shop_address'] = "No 664/1, Yatihena, Malwana";
        $data['shop_phone'] = 'Hotline: 075 500 500 9  |  Tel: 011 299 68 68';

        $this->load->view('quick-bill/print_a4_receipt', $data);
    }

    public function saveWalkInCustomer()
    {
        $this->require_permission('quickbill.create');
        $this->form_validation->set_rules('salutation', 'Salutation', 'required');
        $this->form_validation->set_rules('name', 'Name', 'required');
        $this->form_validation->set_rules('mobile', 'Mobile', 'required|regex_match[/^[0-9]{10}$/]', ['regex_match' => 'Mobile must be exactly 10 digits.']);

        if ($this->form_validation->run() == FALSE) {
            echo json_encode(['status' => 'error', 'message' => validation_errors()]);
            return;
        }

        $data = [
            'salutation'   => $this->input->post('salutation'),
            'name'         => $this->input->post('name'),
            'mobile'       => $this->input->post('mobile'),
            'created_at'   => date('Y-m-d H:i:s'),
            'created_by'   => $this->session->userdata('userid')
        ];

        $insert = $this->general_model->insertCustomer($data);

        if ($insert) {
            $customerId = $this->db->insert_id();
            $customer = $this->general_model->loadCustomer($customerId);
            echo json_encode([
                'status' => 'success', 
                'message' => 'Customer saved successfully.',
                'customer' => $customer
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to save customer.']);
        }
    }

    public function deleteQuickBill() {
        $this->require_permission('quickbill.delete');
        $billId = $this->input->post('bill_id');
        $deletionReason = $this->input->post('deletion_reason');
        
        // Validate required fields
        if (empty($billId)) {
            echo json_encode(['status' => 'error', 'message' => 'Bill ID is required']);
            return;
        }
        
        if (empty($deletionReason) || trim($deletionReason) === '') {
            echo json_encode(['status' => 'error', 'message' => 'Deletion reason is mandatory']);
            return;
        }
        
        $this->load->model('QuickBill_model');
        
        // Get bill details before deletion
        $billDetails = $this->QuickBill_model->get_bill_by_id($billId);
        if (empty($billDetails)) {
            echo json_encode(['status' => 'error', 'message' => 'Bill not found']);
            return;
        }
        
        $billData = $billDetails[0];
        
        // Delete the bill and return items to stock
        $result = $this->QuickBill_model->deleteQuickBillWithReason($billId, $deletionReason, $this->session->userdata('userid'));
        
        if ($result['status'] === 'success') {
            echo json_encode([
                'status' => 'success', 
                'message' => 'Quick bill deleted successfully. Items have been returned to stock.'
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => $result['message']]);
        }
    }

    public function getQuickBillForEdit()
    {
        $this->require_permission('quickbill.edit');
        try {
            $bill_id = $this->input->post('bill_id');
            
            if (empty($bill_id)) {
                echo json_encode(['status' => 'error', 'message' => 'Bill ID is required']);
                return;
            }
            
            error_log("getQuickBillForEdit called with bill_id: " . $bill_id);
            
            $this->load->model('QuickBill_model');
            
            $data['bill'] = $this->QuickBill_model->get_bill_by_id($bill_id);
            $data['items'] = $this->QuickBill_model->get_bill_items($bill_id);
            
            error_log("Bill data retrieved: " . print_r($data['bill'], true));
            error_log("Items data retrieved: " . print_r($data['items'], true));
            
            // Get customers from the database directly to avoid model issues
            $data['customers'] = $this->db->get('customers')->result();
            $data['payment_methods'] = ['Cash', 'Card', 'Cheque', 'Credit', 'Bank Transfer'];
            
            // Get payment information
            $data['payments'] = $this->db->get_where('quick_bill_payments', ['quick_bill_id' => $bill_id])->result();

            if (empty($data['bill'])) {
                echo json_encode(['status' => 'error', 'message' => 'Bill not found']);
                return;
            }

            // Load the edit view and capture the HTML
            $html = $this->load->view('quick-bill/edit-quick-bill', $data, true);
            
            error_log("View loaded successfully, HTML length: " . strlen($html));
            
            echo json_encode([
                'status' => 'success',
                'html' => $html
            ]);
            
        } catch (Exception $e) {
            error_log("Error in getQuickBillForEdit: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            echo json_encode(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
        }
    }

    public function updateQuickBill()
    {
        $this->require_permission('quickbill.edit');
        try {
            $billId = $this->input->post('bill_id');
            $customerId = $this->input->post('customerId');
            $paymentMethod = $this->input->post('paymentMethod');
            $paidAmount = $this->input->post('paidAmount');
            $cardOrChequeNo = $this->input->post('cardOrChequeNo');
            $chequeDate = $this->input->post('chequeDate');
            $bankName = $this->input->post('bankName');
            $note = $this->input->post('note');
            $creditPersonName = $this->input->post('creditPersonName');
            $creditPersonPhone = $this->input->post('creditPersonPhone');
            $items = json_decode($this->input->post('items'), true);

            // Debug logging
            error_log("Update Quick Bill - Bill ID: " . $billId);
            error_log("Update Quick Bill - Items: " . print_r($items, true));

            if (empty($billId)) {
                echo json_encode(['status' => 'error', 'message' => 'Bill ID is required']);
                return;
            }

            if (empty($items) || !is_array($items)) {
                echo json_encode(['status' => 'error', 'message' => 'Items data is required and must be an array']);
                return;
            }

            $this->load->model('QuickBill_model');
            $result = $this->QuickBill_model->updateQuickBill($billId, $customerId, $paymentMethod, $paidAmount, $cardOrChequeNo, $chequeDate, $bankName, $note, $creditPersonName, $creditPersonPhone, $items, $this->session->userdata('userid'));

            // Debug the result
            error_log("Update Quick Bill Result: " . print_r($result, true));
            
            echo json_encode($result);
        } catch (Exception $e) {
            error_log("Error in updateQuickBill controller: " . $e->getMessage());
            echo json_encode(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
        }
    }

    public function getBillPaymentHistory()
    {
        $this->require_permission('quickbill.view');
        $billId = $this->input->post('bill_id');
        
        if (empty($billId)) {
            echo json_encode(['status' => 'error', 'message' => 'Bill ID is required']);
            return;
        }
        
        $this->load->model('QuickBill_model');
        $payments = $this->QuickBill_model->getBillPaymentHistory($billId);
        
        echo json_encode([
            'status' => 'success',
            'payments' => $payments
        ]);
    }

    public function getPaymentDetails()
    {
        $this->require_permission('quickbill.view');
        $paymentId = $this->input->post('payment_id');
        
        if (empty($paymentId)) {
            echo json_encode(['status' => 'error', 'message' => 'Payment ID is required']);
            return;
        }
        
        $this->load->model('QuickBill_model');
        $payment = $this->QuickBill_model->getPaymentById($paymentId);
        
        if (!$payment) {
            echo json_encode(['status' => 'error', 'message' => 'Payment not found']);
            return;
        }
        
        echo json_encode([
            'status' => 'success',
            'payment' => $payment
        ]);
    }

    public function updatePayment()
    {
        $this->require_permission('quickbill.edit');
        $paymentId = $this->input->post('payment_id');
        $paymentMethod = $this->input->post('payment_method');
        $paidAmount = $this->input->post('paid_amount');
        $cardOrChequeNo = $this->input->post('card_or_cheque_no');
        $bankName = $this->input->post('bank_name');
        $chequeDate = $this->input->post('cheque_date');
        $note = $this->input->post('note');
        
        if (empty($paymentId) || empty($paymentMethod) || empty($paidAmount)) {
            echo json_encode(['status' => 'error', 'message' => 'Payment ID, method, and amount are required']);
            return;
        }
        
        $this->load->model('QuickBill_model');
        $result = $this->QuickBill_model->updatePayment($paymentId, $paymentMethod, $paidAmount, $cardOrChequeNo, $bankName, $chequeDate, $note, $this->session->userdata('userid'));
        
        echo json_encode($result);
    }

    public function deletePayment()
    {
        $this->require_permission('quickbill.edit');
        $paymentId = $this->input->post('payment_id');
        
        if (empty($paymentId)) {
            echo json_encode(['status' => 'error', 'message' => 'Payment ID is required']);
            return;
        }
        
        $this->load->model('QuickBill_model');
        $result = $this->QuickBill_model->deletePayment($paymentId, $this->session->userdata('userid'));
        
        echo json_encode($result);
    }

    public function createPayment()
    {
        $this->require_permission('quickbill.edit');
        $billId = $this->input->post('bill_id');
        $paymentMethod = $this->input->post('payment_method');
        $paidAmount = $this->input->post('paid_amount');
        $cardOrChequeNo = $this->input->post('card_or_cheque_no');
        $bankName = $this->input->post('bank_name');
        $chequeDate = $this->input->post('cheque_date');
        $note = $this->input->post('note');
        
        if (empty($billId) || empty($paymentMethod) || empty($paidAmount)) {
            echo json_encode(['status' => 'error', 'message' => 'Bill ID, payment method, and amount are required']);
            return;
        }
        
        $this->load->model('QuickBill_model');
        $result = $this->QuickBill_model->createPayment($billId, $paymentMethod, $paidAmount, $cardOrChequeNo, $bankName, $chequeDate, $note, $this->session->userdata('userid'));
        
        echo json_encode($result);
    }

    // Return functionality methods
    public function getReturnData() {
        $this->require_permission('quickbill.edit');
        $billId = $this->input->post('bill_id');
        
        // Debug logging
        error_log("getReturnData called with bill_id: " . $billId);
        
        $this->load->model('QuickBill_model');
        $bill = $this->QuickBill_model->get_bill_by_id($billId);
        $items = $this->QuickBill_model->get_bill_items($billId);
        
        // Debug logging
        error_log("Bill data: " . print_r($bill, true));
        error_log("Items data: " . print_r($items, true));
        
        if (empty($bill)) {
            error_log("Bill not found for ID: " . $billId);
            echo json_encode(['status' => 'error', 'message' => 'Bill not found']);
            return;
        }
        
        $billData = $bill[0];
        
        // Calculate return quantities for each item
        foreach ($items as &$item) {
            // Get already returned quantity for this item
            $alreadyReturned = $this->QuickBill_model->getAlreadyReturnedQuantity($item->id);
            $item->already_returned_qty = $alreadyReturned;
            
            // Calculate available quantity for return
            $item->available_for_return_qty = $item->quantity - $alreadyReturned;
            if ($item->available_for_return_qty < 0) {
                $item->available_for_return_qty = 0;
            }
        }
        
        // Format bill details
        $billDetails = "
            <p><strong>Bill Amount:</strong> Rs. " . number_format($billData->final_bill_amount, 2) . "</p>
            <p><strong>Date:</strong> " . date('Y-m-d H:i', strtotime($billData->created_at)) . "</p>
            <p><strong>Status:</strong> " . ($billData->total_balance > 0 ? 'Due' : 'Paid') . "</p>
        ";
        
        // Format customer details
        $customerDetails = "
            <p><strong>Name:</strong> " . ($billData->customer_id == 0 ? 'Walk-in Customer' : htmlspecialchars($billData->customer_name)) . "</p>
            <p><strong>Mobile:</strong> " . htmlspecialchars($billData->customer_mobile ?? 'N/A') . "</p>
        ";
        
        $response = [
            'status' => 'success',
            'billDetails' => $billDetails,
            'customerDetails' => $customerDetails,
            'items' => $items
        ];
        
        error_log("Response being sent: " . json_encode($response));
        echo json_encode($response);
    }

    public function submitReturnRequest()
    {
        $this->require_permission('quickbill.edit');
        $billId = $this->input->post('bill_id');
        $generalReason = $this->input->post('general_reason');
        $returnItems = json_decode($this->input->post('return_items'), true);
        
        if (empty($returnItems)) {
            echo json_encode(['status' => 'error', 'message' => 'No items selected for return']);
            return;
        }
        
        $this->load->model('QuickBill_model');
        
        // Calculate total return amount
        $totalAmount = 0;
        foreach ($returnItems as $item) {
            $totalAmount += $item['return_amount'];
        }
        
        // Create return request
        $requestData = [
            'quick_bill_id' => $billId,
            'general_reason' => $generalReason,
            'total_amount' => $totalAmount,
            'created_by' => $this->session->userdata('userid')
        ];
        
        $requestId = $this->QuickBill_model->createReturnRequest($requestData);
        
        if ($requestId) {
            // Create return items
            foreach ($returnItems as $item) {
                $itemData = [
                    'return_request_id' => $requestId,
                    'quick_bill_item_id' => $item['item_id'],
                    'item_id' => $item['item_id'],
                    'po_item_id' => 0, // You might want to get this from the original item
                    'return_quantity' => $item['return_quantity'],
                    'return_amount' => $item['return_amount'],
                    'reason' => $item['reason']
                ];
                
                $this->QuickBill_model->createReturnItem($itemData);
            }
            
            echo json_encode(['status' => 'success', 'message' => 'Return request submitted successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to submit return request']);
        }
    }

    public function getReturnApprovals()
    {
        $this->require_permission('quickbill.view');
        $billId = $this->input->post('bill_id');
        
        $this->load->model('QuickBill_model');
        $approvals = $this->QuickBill_model->getReturnApprovals($billId);
        
        echo json_encode([
            'status' => 'success',
            'approvals' => $approvals
        ]);
    }

    public function approveReturns() {
        $this->require_permission('quickbill.edit');
        $returnIds = $this->input->post('return_ids');
        
        if (empty($returnIds)) {
            echo json_encode(['status' => 'error', 'message' => 'No return IDs provided']);
            return;
        }
        
        $this->load->model('QuickBill_model');
        $successCount = 0;
        
        foreach ($returnIds as $returnId) {
            if ($this->QuickBill_model->approveReturn($returnId, $this->session->userdata('userid'))) {
                $successCount++;
            }
        }
        
        if ($successCount > 0) {
            echo json_encode(['status' => 'success', 'message' => $successCount . ' return(s) approved successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to approve any returns']);
        }
    }

    public function testUpdate()
    {
        // Simple test endpoint to debug the update issue
        $billId = $this->input->post('bill_id');
        $items = json_decode($this->input->post('items'), true);
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Test endpoint working',
            'bill_id' => $billId,
            'items_count' => count($items),
            'items' => $items
        ]);
    }
}
