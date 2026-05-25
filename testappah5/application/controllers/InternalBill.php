<?php
defined('BASEPATH') or exit('No direct script access allowed');

class InternalBill extends MY_Controller
{
    public $InternalBill_model; // Declare the property

    public function __construct()
    {
        parent::__construct();
        $this->load->model('purchase_model');
        $this->load->model('services_model');
        $this->load->model('products_model');
        $this->load->model('general_model');
        $this->load->model('expenses_model'); // For loading employees
    }

    public function internalBill()
    {
        $this->require_permission('internalbill.create');
        $data['mainMenuName'] = 'Internal Bill';
        $data['subMenuName'] = 'internal-bill';

        $data['itemBrands'] = $this->services_model->loadItemBrands();
        $data['itemCategories'] = $this->services_model->loadItemCategories();
        $data['suppliers'] = $this->services_model->loadSuppliers();
        $data['products'] = $this->services_model->loadAvailableInternalProducts();

        $this->load->view('layout/header');
        $this->load->view('layout/top_navbar');
        $this->load->view('layout/left_sidebar', $data);
        $this->load->view('internal-bill/internal-bill', $data);
        $this->load->view('layout/footer');
    }

    public function internalBillList()
    {
        $this->require_permission('internalbill.view');
        $data['mainMenuName'] = 'Internal Bill';
        $data['subMenuName'] = 'internal-bill-list';

        $this->load->view('layout/header');
        $this->load->view('layout/top_navbar');
        $this->load->view('layout/left_sidebar', $data);
        $this->load->view('internal-bill/internal-bill-invoice', $data);
        $this->load->view('layout/footer');
    }

    public function loadInternalBillList()
    {
        $this->require_permission('internalbill.view');
        $sdate = $this->input->post('sdate');
        $edate = $this->input->post('edate');

        // Load all internal bills within date range
        $this->load->model('InternalBill_model');
        $data['internalBills'] = $this->InternalBill_model->get_all_internal_bills($sdate, $edate);
        $this->load->view('internal-bill/internal-bill-invoice-list', $data);
    }   

    public function loadServiceProductsFilterListByProduct()
    {
        $categoryId = $this->input->post('categoryId');
        $brandId = $this->input->post('brandId');
        $supplierId = $this->input->post('supplierId');
        
        // Debug logging
        error_log("Filter values - Category: $categoryId, Brand: $brandId, Supplier: $supplierId");
        
        $data['products'] = $this->services_model->loadAvailableInternalProducts(null, $supplierId, $brandId, $categoryId);
        
        // Debug logging
        error_log("Products returned: " . count($data['products']));
        
        $this->load->view('internal-bill/product-items-filter-list', $data);
    }

    public function addProductToBill()
    {
        $productId = $this->input->post('productID');
        $quantity = $this->input->post('quantity');
        $poItemId = $this->input->post('po_item_id');

        // Validate inputs
        if (!$productId || !$quantity || !$poItemId) {
            echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
            return;
        }

        // Get PO item details to get unit price
        $poItem = $this->purchase_model->getPoItem($poItemId);
        if (!$poItem) {
            echo json_encode(['status' => 'error', 'message' => 'PO item not found']);
            return;
        }

        // Validate requested quantity against available stock (BUG-10)
        $availableStock = floatval($poItem->available_stock ?? 0);
        if (floatval($quantity) > $availableStock) {
            echo json_encode(['status' => 'error', 'message' => 'Quantity exceeds available stock. Available: ' . number_format($availableStock, 2)]);
            return;
        }

        // Get product details
        $product = $this->services_model->loadProducts($productId);
        if (!$product) {
            echo json_encode(['status' => 'error', 'message' => 'Product not found']);
            return;
        }

        // Check if product is already in cart
        $cart = $this->session->userdata('internal_cart') ?: [];
        foreach ($cart as $item) {
            if ($item['product_id'] == $productId) {
                echo json_encode(['status' => 'error', 'message' => 'Product already in cart']);
                return;
            }
        }

        // Calculate total price (no discounts for internal use)
        $unitPrice = 0;
        if (isset($poItem->company_price) && (float)$poItem->company_price > 0) {
            $unitPrice = (float)$poItem->company_price;
        } elseif (isset($poItem->sale_price) && (float)$poItem->sale_price > 0) {
            $unitPrice = (float)$poItem->sale_price;
        } elseif (isset($poItem->price) && (float)$poItem->price > 0) {
            $unitPrice = (float)$poItem->price;
        } elseif (isset($product->sale_price) && (float)$product->sale_price > 0) {
            $unitPrice = (float)$product->sale_price;
        }
        $totalPrice = ((float)$quantity) * $unitPrice;

        // Add to cart
        $cartItem = [
            'product_id' => $productId,
            'product_name' => $product->product_name,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'discount_percent' => 0,
            'discount_amount' => 0,
            'total_price' => $totalPrice,
            'po_item_id' => $poItemId
        ];

        $cart[] = $cartItem;
        $this->session->set_userdata('internal_cart', $cart);

        echo json_encode(['status' => 'success', 'message' => 'Product added to cart']);
    }

    public function removeProductFromBill()
    {
        $index = $this->input->post('index');
        $cart = $this->session->userdata('internal_cart');

        if (!is_array($cart) || !isset($cart[$index])) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid cart item index']);
            return;
        }

        unset($cart[$index]);
        // Reindex the array
        $cart = array_values($cart);
        $this->session->set_userdata('internal_cart', $cart);

        echo json_encode(['status' => 'success', 'message' => 'Item removed']);
    }

    public function createInternalBill()
    {
        $this->require_permission('internalbill.create');
        try {
            $this->db->trans_start();

            $customerId = $this->input->post('customerId');
            $paymentMethods = json_decode($this->input->post('paymentMethods'), true);
            $note = $this->input->post('note');

            // Validate inputs
            if (!$customerId) {
                echo json_encode(['status' => 'error', 'message' => 'Please select a customer/employee']);
                return;
            }

            if (empty($paymentMethods)) {
                echo json_encode(['status' => 'error', 'message' => 'Please add at least one payment method']);
                return;
            }

            // Validate payment methods
            $totalPaid = 0;
            foreach ($paymentMethods as $payment) {
                $totalPaid += $payment['amount'];
                
                // Validate required fields based on payment method
                if (strtolower($payment['method']) === 'card' || strtolower($payment['method']) === 'bank transfer') {
                    if (empty($payment['cardOrChequeNo']) || empty($payment['bankName'])) {
                        echo json_encode(['status' => 'error', 'message' => 'Please fill all required fields for ' . $payment['method'] . ' payment']);
                        return;
                    }
                } elseif (strtolower($payment['method']) === 'cheque') {
                    if (empty($payment['cardOrChequeNo']) || empty($payment['chequeDate']) || empty($payment['bankName'])) {
                        echo json_encode(['status' => 'error', 'message' => 'Please fill all required fields for Cheque payment']);
                        return;
                    }
                } elseif (strtolower($payment['method']) === 'credit') {
                    if (empty($payment['creditPersonName']) || empty($payment['creditPersonPhone'])) {
                        echo json_encode(['status' => 'error', 'message' => 'Please fill all required fields for Credit payment']);
                        return;
                    }
                }
            }

            $cart = $this->session->userdata('internal_cart');
            if (empty($cart)) {
                echo json_encode(['status' => 'error', 'message' => 'No items in cart']);
                return;
            }

            // Calculate bill total
            $billTotal = array_sum(array_column($cart, 'total_price'));

            // Check if total paid matches bill total
            if ($totalPaid < $billTotal) {
                echo json_encode(['status' => 'error', 'message' => 'Total paid amount is less than bill total']);
                return;
            }

            // Create internal bill
            $billData = [
                'bill_date' => date('Y-m-d H:i:s'),
                'customer_id' => $customerId,
                'payment_method' => $paymentMethods[0]['method'], // Primary payment method
                'paid_amount' => $totalPaid,
                'card_or_cheque_no' => $paymentMethods[0]['cardOrChequeNo'] ?? null,
                'cheque_date' => $paymentMethods[0]['chequeDate'] ?? null,
                'bank_name' => $paymentMethods[0]['bankName'] ?? null,
                'note' => $note,
                'grand_total' => $billTotal,
                'created_by' => $this->session->userdata('userid'),
                'created_at' => date('Y-m-d H:i:s')
            ];

            $this->db->insert('internal_bills', $billData);
            $billId = $this->db->insert_id();

            // Add bill items and update stock
            foreach ($cart as $item) {
                $itemData = [
                    'internal_bill_id' => $billId,
                    'item_id' => $item['product_id'],
                    'po_item_id' => $item['po_item_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['unit_price'],
                    'discount_percent' => $item['discount_percent'],
                    'discount_amount' => $item['discount_amount'],
                    'total_price' => $item['total_price'],
                    'created_at' => date('Y-m-d H:i:s'),
                    'created_by' => $this->session->userdata('userid')
                ];

                $this->db->insert('internal_bill_items', $itemData);

                // Update available stock in purchase_order_items
                $this->purchase_model->reduceStockForItem($item['po_item_id'], $item['quantity']);
            }

            // Clear cart
            unset($_SESSION['internal_cart']);

            $this->db->trans_complete();

            if ($this->db->trans_status()) {
                echo json_encode(['status' => 'success', 'message' => 'Internal bill created successfully', 'bill_id' => $billId]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to create internal bill']);
            }

        } catch (Exception $e) {
            $this->db->trans_rollback();
            echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    public function getCartItems()
    {
        $this->require_permission('internalbill.create');
        if (isset($_SESSION['internal_cart'])) {
            echo json_encode($_SESSION['internal_cart']);
        } else {
            echo json_encode([]);
        }
    }

    public function clearCart()
    {
        $this->require_permission('internalbill.create');
        unset($_SESSION['internal_cart']);
        echo json_encode(['status' => 'success', 'message' => 'Cart cleared']);
    }

    public function loadBillItems()
    {
        $this->require_permission('internalbill.create');
        if (!isset($_SESSION['internal_cart']) || empty($_SESSION['internal_cart'])) {
            $this->load->view('internal-bill/internal-bill-items-list', ['cart' => [], 'total' => 0, 'employees' => []]);
            return;
        }

        $cart = $_SESSION['internal_cart'];
        $total = array_sum(array_column($cart, 'total_price'));

        // Load employees (like in expenses) instead of customers
        $employees = $this->expenses_model->loadUsers();

        $data = [
            'cart' => $cart,
            'total' => $total,
            'employees' => $employees
        ];

        $this->load->view('internal-bill/internal-bill-items-list', $data);
    }

    public function deleteCartItem()
    {
        $this->require_permission('internalbill.create');
        $index = $this->input->post('index');
        $cart = $this->session->userdata('internal_cart');

        if (!is_array($cart) || !isset($cart[$index])) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid cart item index']);
            return;
        }

        unset($cart[$index]);
        // Reindex the array
        $cart = array_values($cart);
        $this->session->set_userdata('internal_cart', $cart);

        echo json_encode(['status' => 'success', 'message' => 'Item removed']);
    }

    public function saveCompletedBill()
    {
        $this->require_permission('internalbill.create');
        try {
            $this->db->trans_start();

            $customerId = $this->input->post('customerId');
            $note = $this->input->post('note');

            // Validate inputs
            if (!$customerId) {
                echo json_encode(['status' => 'error', 'message' => 'Please select an employee']);
                return;
            }

            $cart = $this->session->userdata('internal_cart');
            if (empty($cart)) {
                echo json_encode(['status' => 'error', 'message' => 'No items in cart']);
                return;
            }

            // Calculate bill total
            $billTotal = array_sum(array_column($cart, 'total_price'));

            // Create internal bill
            $billData = [
                'bill_date' => date('Y-m-d H:i:s'),
                'customer_id' => $customerId,
                'payment_method' => 'Internal Use',
                'paid_amount' => $billTotal,
                'card_or_cheque_no' => null,
                'cheque_date' => null,
                'bank_name' => null,
                'note' => $note,
                'grand_total' => $billTotal,
                'created_by' => $this->session->userdata('userid'),
                'created_at' => date('Y-m-d H:i:s')
            ];

            $this->db->insert('internal_bills', $billData);
            $billId = $this->db->insert_id();

            // Add bill items and update stock
            foreach ($cart as $item) {
                $itemData = [
                    'internal_bill_id' => $billId,
                    'item_id' => $item['product_id'],
                    'po_item_id' => $item['po_item_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['unit_price'],
                    'discount_percent' => $item['discount_percent'],
                    'discount_amount' => $item['discount_amount'],
                    'total_price' => $item['total_price'],
                    'created_at' => date('Y-m-d H:i:s'),
                    'created_by' => $this->session->userdata('userid')
                ];

                $this->db->insert('internal_bill_items', $itemData);

                // Update available stock in purchase_order_items
                $this->purchase_model->reduceStockForItem($item['po_item_id'], $item['quantity']);
            }

            // Clear cart
            unset($_SESSION['internal_cart']);

            $this->db->trans_complete();

            if ($this->db->trans_status()) {
                echo json_encode(['status' => 'success', 'message' => 'Internal bill created successfully', 'bill_id' => $billId]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to create internal bill']);
            }

        } catch (Exception $e) {
            $this->db->trans_rollback();
            echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    public function getBillDetails()
    {
        $this->require_permission('internalbill.view');
        $billId = $this->input->post('bill_id');
        
        if (!$billId) {
            echo json_encode(['status' => 'error', 'message' => 'Bill ID required']);
            return;
        }

        $this->load->model('InternalBill_model');
        $bill = $this->InternalBill_model->get_internal_bill_details($billId);
        $items = $this->InternalBill_model->get_internal_bill_items($billId);

        if (!$bill) {
            echo json_encode(['status' => 'error', 'message' => 'Bill not found']);
            return;
        }

        echo json_encode([
            'status' => 'success',
            'bill' => $bill,
            'items' => $items
        ]);
    }

    public function deleteBill()
    {
        $this->require_permission('internalbill.delete');
        $billId = $this->input->post('bill_id');
        
        if (!$billId) {
            echo json_encode(['status' => 'error', 'message' => 'Bill ID required']);
            return;
        }

        try {
            $this->db->trans_start();

            // Get bill items to restore stock
            $this->load->model('InternalBill_model');
            $items = $this->InternalBill_model->get_internal_bill_items($billId);

            // Restore stock for each item: ADD back the quantity (F-04 fix)
            // updateAvailableStockForItem() SETS stock = qty which is wrong here;
            // use reduceStockForItem with negative qty equivalent = increment
            foreach ($items as $item) {
                if (!empty($item->po_item_id) && $item->po_item_id > 0) {
                    $this->db->set('available_stock', 'available_stock + ' . (float)$item->quantity, false);
                    $this->db->where('po_item_id', $item->po_item_id);
                    $this->db->update('purchase_order_items');
                }
            }

            // Delete bill items
            $this->db->where('internal_bill_id', $billId);
            $this->db->delete('internal_bill_items');

            // Delete bill
            $this->db->where('id', $billId);
            $this->db->delete('internal_bills');

            $this->db->trans_complete();

            if ($this->db->trans_status()) {
                echo json_encode(['status' => 'success', 'message' => 'Bill deleted successfully']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to delete bill']);
            }

        } catch (Exception $e) {
            $this->db->trans_rollback();
            echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    public function print_receipt($bill_id)
    {
        $this->require_permission('internalbill.view');
        try {
            $this->load->model('InternalBill_model');
            $data['bill'] = $this->InternalBill_model->get_internal_bill_details($bill_id);
            $data['items'] = $this->InternalBill_model->get_internal_bill_items($bill_id);

            // Check if data exists
            if (empty($data['bill'])) {
                show_404();
                return;
            }

            $data['internal_bill_id'] = $bill_id;
            $data['logo_url'] = base_url('assets/images/logo.png');
            $data['shop_name'] = 'TROJA AUTO HUB';
            $data['shop_address'] = "No 664/1, Yatihena, Malwana";
            $data['shop_phone'] = 'Hotline: 075 500 500 9  |  Tel: 011 299 68 68';

            $this->load->view('internal-bill/print_receipt', $data);
        } catch (Exception $e) {
            log_message('error', 'Internal Bill Print Error: ' . $e->getMessage());
            show_404();
        }
    }
}
