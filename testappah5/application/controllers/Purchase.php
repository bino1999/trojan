<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Purchase extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('purchase_model');
        $this->load->model('wallet_model');
    }

    public function purchaseManage() {
        $this->require_permission('purchase.view');
        $data['mainMenuName'] = 'Purchase Order';
        $data['subMenuName'] = 'purchase-manage';

        $data['suppliers'] = $this->purchase_model->loadActiveSuppliers();
        $data['products'] = $this->purchase_model->loadActiveProducts();
        $data['productCategories'] = $this->purchase_model->loadProductCategories();
        $data['productBrands'] = $this->purchase_model->loadProductBrands();
        $this->load->view('layout/header');
        $this->load->view('layout/top_navbar');
        $this->load->view('layout/left_sidebar', $data);
        $this->load->view('purchase/purchase-manage', $data);
        $this->load->view('layout/footer');
    }

    public function filterProducts() {
        $this->require_permission('purchase.view');
        $category_id = $this->input->post('category_id');
        $brand_id = $this->input->post('brand_id');

        $data['products'] = $this->purchase_model->loadActiveProductsByCategoryBrand($category_id, $brand_id);
        $this->load->view('purchase/product-filter-list', $data);
    }

    public function createPurchase()
{
    $this->require_permission('purchase.create');
    $supplier_id = $this->input->post('supplier_id');
    $bill_no = trim($this->input->post('bill_no'));
    $bill_date = $this->input->post('bill_date');
    $payment_method = $this->input->post('payment_method');
    $description = trim($this->input->post('description'));
    $vat_type = $this->input->post('vat_type');
    $vat_percent = $this->input->post('vat_percent');

    if (!$supplier_id || !$bill_no || !$bill_date) {
        echo json_encode(['status' => 'error', 'message' => 'Please fill all required fields.']);
        return;
    }

    if(!$payment_method){
        echo json_encode(['status' => 'error', 'message' => 'Please select a payment method.']);
        return;
    }

    if ($vat_type == 'vat_whole' || $vat_type == 'vat_per_item') {
        if ($vat_percent === '' || !is_numeric($vat_percent) || $vat_percent < 0 || $vat_percent > 100) {
            echo json_encode(['status' => 'error', 'message' => 'Please enter a valid VAT percent (0 - 100).']);
            return;
        }
    } else {
        $vat_percent = 0;
    }

    if($payment_method == 'cash'){
        $payment_method = 1;
    }else if($payment_method == 'cheque'){
        $payment_method = 2;
    }else if($payment_method == 'card'){
        $payment_method = 3;
    }else if($payment_method == 'credit'){
        $payment_method = 4;
    }else if($payment_method == 'bank_transfer'){
        $payment_method = 5;
    }else{
        $payment_method = 0;
    }

    $data = [
        'supplier_id' => $supplier_id,
        'bill_no' => $bill_no,
        'bill_date' => $bill_date,
        'intended_payment_method' => $payment_method,
        'vat_type' => $vat_type,
        'vat_percent' => $vat_percent,
        'description' => $description,
        'created_by' => $this->session->userdata('userid'),
        'created_at' => date('Y-m-d H:i:s')
    ];

    $this->db->insert('purchase_orders', $data);
    echo json_encode(['status' => 'success', 'message' => 'Purchase order created.']);
}

public function updatePurchase()
{
    $this->require_permission('purchase.edit');
    $po_id = $this->input->post('edit_po_id');
    $supplier_id = $this->input->post('edit_supplier_id');
    $bill_no = trim($this->input->post('edit_bill_no'));
    $bill_date = $this->input->post('edit_bill_date');
    $payment_method = $this->input->post('edit_payment_method');
    $description = trim($this->input->post('edit_description'));
    $vat_type = $this->input->post('edit_vat_type');
    $vat_percent = $this->input->post('edit_vat_percent');
    $original_status = $this->input->post('edit_original_status');

    if (!$po_id || !$supplier_id || !$bill_no || !$bill_date) {
        echo json_encode(['status' => 'error', 'message' => 'Please fill all required fields.']);
        return;
    }

    if(!$payment_method){
        echo json_encode(['status' => 'error', 'message' => 'Please select a payment method.']);
        return;
    }

    if ($vat_type == 'vat_whole' || $vat_type == 'vat_per_item') {
        if ($vat_percent === '' || !is_numeric($vat_percent) || $vat_percent < 0 || $vat_percent > 100) {
            echo json_encode(['status' => 'error', 'message' => 'Please enter a valid VAT percent (0 - 100).']);
            return;
        }
    } else {
        $vat_percent = 0;
    }

    if($payment_method == 'cash'){
        $payment_method = 1;
    }else if($payment_method == 'cheque'){
        $payment_method = 2;
    }else if($payment_method == 'card'){
        $payment_method = 3;
    }else if($payment_method == 'credit'){
        $payment_method = 4;
    }else if($payment_method == 'bank_transfer'){
        $payment_method = 5;
    }else{
        $payment_method = 0;
    }

    // Get current PO data for stock adjustment
    $current_po = $this->purchase_model->getPurchaseOrder($po_id);
    if (!$current_po) {
        echo json_encode(['status' => 'error', 'message' => 'Purchase order not found.']);
        return;
    }

    // If supplier changed and PO is completed, we need to adjust stock
    if ($original_status == 'Completed' && $current_po->supplier_id != $supplier_id) {
        // Remove stock from old supplier's items
        $this->purchase_model->adjustStockForSupplierChange($po_id, $current_po->supplier_id, $supplier_id);
    }

    $data = [
        'supplier_id' => $supplier_id,
        'bill_no' => $bill_no,
        'bill_date' => $bill_date,
        'intended_payment_method' => $payment_method,
        'vat_type' => $vat_type,
        'vat_percent' => $vat_percent,
        'description' => $description
    ];

    // Debug logging
    error_log("PO Update - Data: " . json_encode($data));
    
    $this->db->where('po_id', $po_id);
    $update = $this->db->update('purchase_orders', $data);

    if ($update) {
        echo json_encode(['status' => 'success', 'message' => 'Purchase order updated successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update purchase order.']);
    }
}

public function loadPurchases()
{
    $sdate = $this->input->post('sdate');
    $edate = $this->input->post('edate');
    $supplier_id = $this->input->post('supplier_id');

    $data['purchases'] = $this->purchase_model->loadPurchases($sdate, $edate, $supplier_id);
    $this->load->view('purchase/purchase-manage-list', $data);
}

public function loadEditPoItems()
{
    $po_id = $this->input->post('po_id');
    $data['po_items'] = $this->purchase_model->loadPurchaseOrderItems($po_id);
    $this->load->view('purchase/edit-po-items-list', $data);
}

public function updatePoItem()
{
    $po_item_id = $this->input->post('edit_po_item_id');
    $po_id = $this->input->post('edit_po_id');
    $product_id = $this->input->post('edit_product_id');
    $quantity = (float)$this->input->post('edit_quantity');
    $company_price = (float)$this->input->post('edit_company_price');
    $sale_price = (float)$this->input->post('edit_sale_price');
    $discount_percent = (float)$this->input->post('edit_discount_percent');
    $discount_amount = (float)$this->input->post('edit_discount_amount');
    $uom = $this->input->post('edit_uom');
    $inventory_type = $this->input->post('edit_inventory_type');
    $rack_no = $this->input->post('edit_rack_no');
    $bin_no = $this->input->post('edit_bin_no');
    $reorder_level = $this->input->post('edit_reorder_level');
    $purchase_date = $this->input->post('edit_purchase_date');
    $note = $this->input->post('edit_note');

    // Get original values for stock calculation
    $original_quantity = (float)$this->input->post('edit_original_quantity');
    $original_company_price = (float)$this->input->post('edit_original_company_price');
    $original_sale_price = (float)$this->input->post('edit_original_sale_price');

    // Validation
    if (!$po_item_id || !$product_id || $quantity <= 0 || $company_price <= 0 || $sale_price <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Please fill all required fields with valid values.']);
        return;
    }

    if ($sale_price < $company_price) {
        echo json_encode(['status' => 'error', 'message' => 'Sale price must not be less than company price.']);
        return;
    }

    if ($discount_percent > 0 && $discount_amount > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Only one of discount percent or amount can be entered.']);
        return;
    }

    // Check if PO is completed for stock adjustment
    $po_details = $this->purchase_model->getPurchaseOrder($po_id);
    $is_completed = ($po_details && $po_details->status == 'Completed');

    // Calculate stock adjustment
    $quantity_diff = $quantity - $original_quantity;

    $data = [
        'product_id' => $product_id,
        'quantity' => $quantity,
        'company_price' => $company_price,
        'sale_price' => $sale_price,
        'discount_percent' => $discount_percent,
        'discount_amount' => $discount_amount,
        'uom' => $uom,
        'inventory_type' => $inventory_type,
        'rack_no' => $rack_no,
        'bin_no' => $bin_no,
        'reorder_level' => $reorder_level,
        'purchase_date' => $purchase_date,
        'note' => $note
    ];

    // Remove empty values to avoid database errors
    $data = array_filter($data, function($value) {
        return $value !== '' && $value !== null;
    });

    // Update the PO item
    $this->db->where('po_item_id', $po_item_id);
    $update = $this->db->update('purchase_order_items', $data);

    if ($update) {
        // If PO is completed, adjust stock
        if ($is_completed && $quantity_diff != 0) {
            $this->purchase_model->adjustStockForItemChange($po_item_id, $quantity_diff);
        }

        echo json_encode(['status' => 'success', 'message' => 'Item updated successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update item.']);
    }
}

public function deletePoItem()
{
    $po_item_id = $this->input->post('po_item_id');
    
    if (!$po_item_id) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid item ID']);
        return;
    }

    // Get item details for stock adjustment
    $item_details = $this->purchase_model->getPoItem($po_item_id);
    if (!$item_details) {
        echo json_encode(['status' => 'error', 'message' => 'Item not found']);
        return;
    }

    $po_details = $this->purchase_model->getPurchaseOrder($item_details->po_id);
    $is_completed = ($po_details && $po_details->status == 'Completed');

    // Block deletion if the item has been used in any job, bill, or internal bill (BUG-06)
    if ($is_completed) {
        $job_usage      = $this->db->where('po_item_id', $po_item_id)->count_all_results('services_job_items');
        $bill_usage     = $this->db->where('po_item_id', $po_item_id)->count_all_results('quick_bill_items');
        $internal_usage = $this->db->where('po_item_id', $po_item_id)->count_all_results('internal_bill_items');
        if ($job_usage > 0 || $bill_usage > 0 || $internal_usage > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Cannot delete — this item has been used in existing jobs, bills, or internal bills.']);
            return;
        }
        $this->purchase_model->reduceStockForItem($po_item_id, $item_details->quantity);
    }

    $deleted = $this->purchase_model->deletePurchaseOrderItem($po_item_id);

    if ($deleted) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to delete item']);
    }
}

public function addPurchaseOrderItem()
{
    $company_price = (float)$this->input->post('company_price');
    $sale_price = (float)$this->input->post('sale_price');
    $discount_percent = (float)$this->input->post('discount_percent');
    $discount_amount = (float)$this->input->post('discount_amount');

    // Business Rule 1: Sale price must not be less than company price
    if ($sale_price < $company_price) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Sale price must not be less than company price.'
        ]);
        return;
    }

    // Business Rule 2a: If sale price equals company price, a discount is required
    if ($sale_price === $company_price && ($discount_percent <= 0 && $discount_amount <= 0)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'When sale price equals company price, enter Discount % or Discount Amount.'
        ]);
        return;
    }

    // Business Rule 2b: Only one discount method allowed
    if ($discount_percent > 0 && $discount_amount > 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Only one of discount percent or amount can be entered.'
        ]);
        return;
    }

    if (!$this->input->post('product_id') > 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Please select a product.'
        ]);
        return;
    }

    // Block adding items to a PO that is no longer in Draft status
    $po_check = $this->purchase_model->getPurchaseOrder($this->input->post('po_id'));
    if (!$po_check || !in_array($po_check->status, ['Draft', 'draft'])) {
        echo json_encode(['status' => 'error', 'message' => 'Cannot add items to a completed or archived purchase order.']);
        return;
    }

    $genuine = $this->input->post('genuine') !== null ? 1 : 0;

    // Derive inventory type from product definition (fallback to 'sale')
    $this->db->select('inventory_type');
    $this->db->from('products');
    $this->db->where('product_id', $this->input->post('product_id'));
    $productRow = $this->db->get()->row();
    $derivedInventoryType = isset($productRow->inventory_type) && in_array(strtolower($productRow->inventory_type), ['sale','internal','both'])
        ? strtolower($productRow->inventory_type)
        : 'sale';

    $insertData = [
        'po_id'            => $this->input->post('po_id'),
        'supplier_id'      => $this->input->post('supplier_id'),
        'product_id'       => $this->input->post('product_id'),
        'purchase_date'    => $this->input->post('purchase_date'),
        'genuine'          => $genuine,
        'uom'              => $this->input->post('uom'),
        // Inventory type is now derived from product; defaulting to 'sale' for all
        'inventory_type'   => $derivedInventoryType,
        'quantity'         => $this->input->post('quantity'),
        'discount_percent' => $discount_percent,
        'discount_amount'  => $discount_amount,
        'company_price'    => $company_price,
        'sale_price'       => $sale_price,
        'rack_no'          => $this->input->post('rack_no'),
        'bin_no'           => $this->input->post('bin_no'),
        'reorder_level'    => $this->input->post('reorder_level'),
        'note'             => $this->input->post('note'),
        'created_at'       => date('Y-m-d H:i:s'),
        'created_by'       => $this->session->userdata('userid'),
        'available_stock' => 0
    ];

    $this->load->model('purchase_model');
    $insertId = $this->purchase_model->addPurchaseOrderItem($insertData);

    if ($insertId) {
        // Optional: Log the activity
        $this->load->model('logs_model');
        $this->logs_model->log_activity('Inventory Item Added', 'Product ID: ' . $this->input->post('product_id'));

        echo json_encode(['status' => 'success', 'message' => 'Item added successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to add item.']);
    }
}

public function loadPurchaseOrderItems(){
    $po_id = $this->input->post('po_id');
   
      $data['purchases'] = $this->purchase_model->loadPurchaseOrderItems($po_id);
       $this->load->view('purchase/purchase-order-item-list', $data);
   }

   public function loadPurchaseOrderItems2(){

    $po_id = $this->input->post('po_id');
   
      $data['purchases'] = $this->purchase_model->loadPurchaseOrderItems($po_id);
       $this->load->view('purchase/purchase-order-item-list-2', $data);
   }

public function deletePurchaseOrderItem($po_item_id = null)
{
    // Unsafe URL-based route blocked — use deletePoItem() via POST instead
    show_error('Direct deletion not allowed. Use the POST route.');
}

public function completePurchaseOrder()
{
    $po_id = $this->input->post('po_id');
    $grand_total = $this->input->post('grand_total');

    if (!$po_id) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid Purchase Order ID']);
        return;
    }
    
    // Fallback: compute total on server if not provided or invalid
    if ($grand_total === null || $grand_total === '' || !is_numeric($grand_total) || (float)$grand_total <= 0) {
        $items = $this->purchase_model->loadPurchaseOrderItems($po_id);
        $computedTotal = 0.0;
        $vatAmount = 0.0;
        $vatType = !empty($items) ? ($items[0]->vat_type ?? 'none') : 'none';
        $vatPercent = 0.0;
        if (!empty($items)) {
            $vatPercent = isset($items[0]->vat_percent) && is_numeric($items[0]->vat_percent) ? (float)$items[0]->vat_percent : 0.0;
        }

        foreach ($items as $item) {
            $qty = (float)($item->quantity ?? 0);
            $price = (float)($item->company_price ?? 0);
            $discountPercent = (float)($item->discount_percent ?? 0);
            $discountAmount = (float)($item->discount_amount ?? 0);

            $lineDiscount = 0.0;
            if ($discountPercent > 0) {
                $lineDiscount = ($price * $discountPercent) / 100.0;
            } elseif ($discountAmount > 0) {
                $lineDiscount = $discountAmount;
            }

            $netPrice = max($price - $lineDiscount, 0);
            $lineTotal = $netPrice * $qty;
            $computedTotal += $lineTotal;

            if ($vatType === 'vat_per_item' && $vatPercent > 0) {
                $vatAmount += ($lineTotal * ($vatPercent / 100.0));
            }
        }

        if ($vatType === 'vat_whole' && $vatPercent > 0) {
            $vatAmount = $computedTotal * ($vatPercent / 100.0);
        }

        $grand_total = $computedTotal + $vatAmount;
    }

    $this->db->trans_start();

    $this->db->where('po_id', $po_id);
    $this->db->update('purchase_orders', [
        'total_amount' => (float)$grand_total,
        'status'       => 'Completed',
        'completed_at' => date('Y-m-d H:i:s'),
        'completed_by' => $this->session->userdata('userid'),
    ]);

    $this->purchase_model->updateAvailableStockForCompletedPO($po_id);

    $this->db->trans_complete();

    if ($this->db->trans_status() === false) {
        echo json_encode(['status' => 'error', 'message' => 'Failed to complete purchase order. Please try again.']);
    } else {
        echo json_encode(['status' => 'success', 'message' => 'Purchase order marked as complete.']);
    }
}

public function deletePurchase()
{
    $this->require_permission('purchase.delete');
    $po_id = $this->input->post('po_id');

    if (!$po_id) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid ID']);
        return;
    }

    $po_details = $this->purchase_model->getPurchaseOrder($po_id);
    if (!$po_details) {
        echo json_encode(['status' => 'error', 'message' => 'Purchase order not found']);
        return;
    }

    // Block deletion if any PO items have already been consumed in jobs or bills.
    // Deleting them would leave dangling po_item_id references and corrupt stock history.
    if ($po_details->status === 'Completed') {
        $po_item_ids = $this->db
            ->select('po_item_id')
            ->where('po_id', $po_id)
            ->get('purchase_order_items')
            ->result_array();
        $po_item_ids = array_column($po_item_ids, 'po_item_id');

        if (!empty($po_item_ids)) {
            $job_usage = $this->db
                ->where_in('po_item_id', $po_item_ids)
                ->count_all_results('services_job_items');

            $bill_usage = $this->db
                ->where_in('po_item_id', $po_item_ids)
                ->count_all_results('quick_bill_items');

            // BUG-06: also block if any items are used in internal bills
            $internal_usage = $this->db
                ->where_in('po_item_id', $po_item_ids)
                ->count_all_results('internal_bill_items');

            if ($job_usage > 0 || $bill_usage > 0 || $internal_usage > 0) {
                $parts = [];
                if ($job_usage > 0)      $parts[] = $job_usage . ' job(s)';
                if ($bill_usage > 0)     $parts[] = $bill_usage . ' quick bill(s)';
                if ($internal_usage > 0) $parts[] = $internal_usage . ' internal bill(s)';
                echo json_encode([
                    'status'  => 'error',
                    'message' => 'Cannot delete this purchase order — its items have already been used in ' . implode(', ', $parts) . '.',
                ]);
                return;
            }
        }
    }

    $this->db->trans_start();

    // Zero out available_stock for completed PO items before removing them
    if ($po_details->status === 'Completed') {
        $this->db->where('po_id', $po_id);
        $this->db->update('purchase_order_items', ['available_stock' => 0]);
    }

    $this->db->where('po_id', $po_id);
    $this->db->delete('purchase_order_items');

    $this->db->where('po_id', $po_id);
    $this->db->delete('purchase_orders');

    $this->db->trans_complete();

    if ($this->db->trans_status()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error occurred.']);
    }
}

public function purchaseHistoryManage() {
    $this->require_permission('purchase.view');
    $data['mainMenuName'] = 'Purchase Order';
    $data['subMenuName'] = 'purchase-history-manage';

    $data['suppliers'] = $this->purchase_model->loadActiveSuppliers();
    $data['products'] = $this->purchase_model->loadActiveProducts();
    
    $this->load->view('layout/header');
    $this->load->view('layout/top_navbar');
    $this->load->view('layout/left_sidebar', $data);
    $this->load->view('purchase/purchase-history-manage', $data);
    $this->load->view('layout/footer');
}


public function stockManage() {
        $this->require_permission('stock.view');
        $data['mainMenuName'] = 'Purchase Order';
        $data['subMenuName'] = 'item-stock';

 
        $data['suppliers'] = $this->purchase_model->loadActiveSuppliers();
        $data['products'] = $this->purchase_model->loadActiveProducts();
        $data['productCategories'] = $this->purchase_model->loadProductCategories();
        $data['productBrands'] = $this->purchase_model->loadProductBrands();
        $this->load->view('layout/header');
        $this->load->view('layout/top_navbar');
        $this->load->view('layout/left_sidebar', $data);
        $this->load->view('stock/stock-manage', $data);
        $this->load->view('layout/footer');
    }

    public function loadAvailableStock()
{
    $sdate       = $this->input->post('sdate');
    $edate       = $this->input->post('edate');
    $category_id = $this->input->post('category_id');
    $brand_id    = $this->input->post('brand_id');
    $supplier_id = $this->input->post('supplier_id');

    $has_date_filter = !empty($sdate) && !empty($edate);

    if ($has_date_filter) {
        if (!strtotime($sdate) || !strtotime($edate) || strtotime($sdate) > strtotime($edate)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid date range. Start date must be before end date.']);
            return;
        }
    }

    $stock = $this->purchase_model->loadPurchaseItemsPerBatch(
        $category_id,
        $brand_id,
        $supplier_id,
        $has_date_filter ? $sdate : null,
        $has_date_filter ? $edate : null
    );

    $data['stock']           = $stock;
    $data['has_date_filter'] = $has_date_filter;
    $data['filter_sdate']    = $has_date_filter ? $sdate : null;
    $this->load->view('stock/available-stock-list', $data);
}

    // =====================================================
    // PAYMENT SYSTEM METHODS
    // =====================================================

    public function loadPoPayments()
    {
        $po_id = $this->input->post('po_id');
        
        if (!$po_id) {
            echo json_encode(['status' => 'error', 'message' => 'PO ID is required']);
            return;
        }

        $payments = $this->purchase_model->getPoPayments($po_id);
        $po = $this->purchase_model->getPurchaseOrder($po_id);
        
        if (!$po) {
            echo json_encode(['status' => 'error', 'message' => 'Purchase order not found']);
            return;
        }

        // Get supplier name
        $supplier = $this->purchase_model->getSupplierById($po->supplier_id);
        $po->supplier_name = $supplier ? $supplier->supplier_name : 'Unknown Supplier';

        $data = [
            'payments' => $payments,
            'po' => $po,
            'payment_methods' => ['Cash', 'Card', 'Cheque', 'Credit', 'Bank Transfer']
        ];

        $this->load->view('purchase/po-payments-modal', $data);
    }

    public function addPoPayment()
    {
        $po_id = $this->input->post('po_id');
        $payment_method = $this->input->post('payment_method');
        $paid_amount = $this->input->post('paid_amount');
        $card_or_cheque_no = $this->input->post('card_or_cheque_no');
        $cheque_date = $this->input->post('cheque_date');
        $bank_name = $this->input->post('bank_name');
        $note = $this->input->post('note');
        $credit_person_name = $this->input->post('credit_person_name');
        $credit_person_phone = $this->input->post('credit_person_phone');

        if (!$po_id || !$payment_method || !$paid_amount) {
            echo json_encode(['status' => 'error', 'message' => 'Required fields are missing']);
            return;
        }

        // Block adding payments if already confirmed
        $po = $this->purchase_model->getPurchaseOrder($po_id);
        if ($po && isset($po->payment_status) && strtolower($po->payment_status) === 'completed') {
            echo json_encode(['status' => 'error', 'message' => 'Payments already confirmed. No further payments allowed.']);
            return;
        }

        // Validate payment method specific fields
        if (strtolower($payment_method) === 'card' || strtolower($payment_method) === 'bank transfer') {
            if (empty($card_or_cheque_no) || empty($bank_name)) {
                echo json_encode(['status' => 'error', 'message' => 'Receipt/Transaction ID and Bank Name are required']);
                return;
            }
        } elseif (strtolower($payment_method) === 'cheque') {
            if (empty($card_or_cheque_no) || empty($cheque_date) || empty($bank_name)) {
                echo json_encode(['status' => 'error', 'message' => 'Cheque Number, Date and Bank Name are required']);
                return;
            }
        } elseif (strtolower($payment_method) === 'credit') {
            // No validation needed for credit payments in PO as we are the ones paying the supplier
        }

        // Start transaction for credit payment processing
        $this->db->trans_start();

        try {
            $payment_data = [
                'po_id' => $po_id,
                'payment_method' => $payment_method,
                'paid_amount' => $paid_amount,
                'card_or_cheque_no' => $card_or_cheque_no ?: null,
                'cheque_date' => $cheque_date ?: null,
                'bank_name' => $bank_name ?: null,
                'note' => $note ?: null,
                'created_by' => $this->session->userdata('userid')
            ];

            $this->db->insert('purchase_order_payments', $payment_data);
            
            if ($this->db->affected_rows() <= 0) {
                throw new Exception('Failed to add payment record');
            }

            // Handle credit payment - create account transaction
            if (strtolower($payment_method) === 'credit') {
                // Get supplier information for the credit transaction
                $supplier = $this->purchase_model->getSupplierById($po->supplier_id);
                
                // Create credit transaction in account_transactions
                $creditTransactionData = [
                    'account_slug' => 'credits',
                    'txn_type' => 'credit',
                    'amount' => $paid_amount,
                    'description' => 'Purchase Order Credit - ' . $credit_person_name . ' - PO #' . $po_id,
                    'reference_type' => 'purchase_order',
                    'reference_id' => $po_id,
                    'customer_id' => null, // POs are for suppliers, not customers
                    'created_by' => $this->session->userdata('userid'),
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];

                $this->db->insert('account_transactions', $creditTransactionData);
                
                if ($this->db->affected_rows() <= 0) {
                    $dbError = $this->db->error();
                    error_log("Credit transaction insert failed. Error: " . print_r($dbError, true));
                    error_log("Credit transaction data: " . print_r($creditTransactionData, true));
                    throw new Exception('Failed to create credit transaction: ' . (isset($dbError['message']) ? $dbError['message'] : 'Unknown database error'));
                }

                // Update credits account balance
                $this->db->set('balance', 'balance + ' . $paid_amount, FALSE);
                $this->db->where('slug', 'credits');
                $this->db->update('accounts');

                // Update PO with credit person details
                $this->db->where('po_id', $po_id);
                $this->db->update('purchase_orders', [
                    'credit_person_name' => $credit_person_name,
                    'credit_person_phone' => $credit_person_phone,
                    'updated_at' => date('Y-m-d H:i:s'),
                    'updated_by' => $this->session->userdata('userid')
                ]);
            }

            // For non-credit payments (cash/card/bank transfer), record DEBIT from the relevant wallet account
            $methodLower = strtolower($payment_method);
            if (in_array($methodLower, ['cash', 'card', 'bank transfer', 'bank_transfer', 'cheque'])) {
                $desc = ucfirst($methodLower) . ' payment for Purchase Order #' . $po_id;
                $outgoing = [
                    'payment_method' => $payment_method,
                    'amount' => floatval($paid_amount),
                    'description' => $desc,
                    'reference_type' => 'purchase_order',
                    'reference_id' => $po_id,
                    'created_by' => $this->session->userdata('userid')
                ];
                // Cheque should not affect wallet; model handles that
                $walletResult = $this->wallet_model->processOutgoingPayment($outgoing);
                if ($walletResult['status'] !== 'success') {
                    throw new Exception($walletResult['message']);
                }
            }

            // Update PO payment status
            $this->updatePoPaymentStatus($po_id);

            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                throw new Exception('Transaction failed');
            }

            echo json_encode(['status' => 'success', 'message' => 'Payment added successfully']);

        } catch (Exception $e) {
            $this->db->trans_rollback();
            echo json_encode(['status' => 'error', 'message' => 'Failed to add payment: ' . $e->getMessage()]);
        }
    }

    public function confirmPoPayments()
    {
        $po_id = $this->input->post('po_id');
        if (!$po_id) {
            echo json_encode(['status' => 'error', 'message' => 'PO ID is required']);
            return;
        }

        // Get totals (excluding credit payments)
        $this->db->select('SUM(paid_amount) as total_paid');
        $this->db->from('purchase_order_payments');
        $this->db->where('po_id', $po_id);
        $this->db->where('payment_method !=', 'Credit');
        $result = $this->db->get()->row();
        $total_paid = $result ? (float)$result->total_paid : 0.0;

        $po = $this->purchase_model->getPurchaseOrder($po_id);
        if (!$po) {
            echo json_encode(['status' => 'error', 'message' => 'Purchase order not found']);
            return;
        }
        $po_total = (float)$po->total_amount;

        if ($total_paid < $po_total) {
            echo json_encode(['status' => 'error', 'message' => 'Cannot confirm. Balance remains.']);
            return;
        }

        // Mark as completed and archive so it won't appear in active list
        $this->db->where('po_id', $po_id);
        $this->db->update('purchase_orders', [
            'total_paid' => $total_paid,
            'payment_status' => 'Completed',
            'status' => 'Archived',
            'payment_completed_at' => date('Y-m-d H:i:s')
        ]);

        // Move to history now that it is confirmed
        $this->movePoToHistory($po_id);

        echo json_encode(['status' => 'success', 'message' => 'Payments confirmed']);
    }

    public function deletePoPayment()
    {
        $payment_id = $this->input->post('payment_id');
        $po_id      = $this->input->post('po_id');

        if (!$payment_id || !$po_id) {
            echo json_encode(['status' => 'error', 'message' => 'Payment ID and PO ID are required']);
            return;
        }

        // Fetch the payment BEFORE deleting so we can reverse its account entry
        $payment = $this->db->get_where('purchase_order_payments', ['id' => $payment_id])->row();
        if (!$payment) {
            echo json_encode(['status' => 'error', 'message' => 'Payment not found']);
            return;
        }

        $this->db->trans_start();

        $this->db->where('id', $payment_id);
        $this->db->delete('purchase_order_payments');

        $methodLower = strtolower($payment->payment_method);

        if ($methodLower === 'credit') {
            // Reverse the credit account balance increase created by addPoPayment()
            $this->db->set('balance', 'balance - ' . (float)$payment->paid_amount, false);
            $this->db->where('slug', 'credits');
            $this->db->update('accounts');

            // Remove the matching account_transaction for this PO credit payment
            $this->db->where('account_slug', 'credits');
            $this->db->where('reference_type', 'purchase_order');
            $this->db->where('reference_id', $po_id);
            $this->db->where('amount', (float)$payment->paid_amount);
            $this->db->where('txn_type', 'credit');
            $this->db->limit(1);
            $this->db->delete('account_transactions');
        } elseif (in_array($methodLower, ['cash', 'card', 'bank transfer', 'bank_transfer', 'cheque'])) {
            // Reverse the outgoing wallet deduction — add the amount back to the account
            $reversalData = [
                'payment_method' => $payment->payment_method,
                'amount'         => floatval($payment->paid_amount),
                'description'    => 'Reversal of ' . ucfirst($methodLower) . ' payment for Purchase Order #' . $po_id,
                'reference_type' => 'purchase_order',
                'reference_id'   => $po_id,
                'created_by'     => $this->session->userdata('userid'),
            ];
            $reversalResult = $this->wallet_model->processPaymentReversal($reversalData);
            if ($reversalResult['status'] !== 'success') {
                $this->db->trans_rollback();
                echo json_encode(['status' => 'error', 'message' => 'Failed to reverse account entry: ' . $reversalResult['message']]);
                return;
            }
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            echo json_encode(['status' => 'error', 'message' => 'Failed to delete payment']);
            return;
        }

        $this->updatePoPaymentStatus($po_id);
        echo json_encode(['status' => 'success', 'message' => 'Payment deleted successfully']);
    }

    private function updatePoPaymentStatus($po_id)
    {
        // Get total paid amount (excluding credit payments)
        $this->db->select('SUM(paid_amount) as total_paid');
        $this->db->from('purchase_order_payments');
        $this->db->where('po_id', $po_id);
        $this->db->where('payment_method !=', 'Credit');
        $result = $this->db->get()->row();
        
        $total_paid = $result ? (float)$result->total_paid : 0;
        
        // Get PO total amount
        $po = $this->purchase_model->getPurchaseOrder($po_id);
        if (!$po) return;
        
        $po_total = (float)$po->total_amount;
        
        // Determine payment status (do NOT auto-complete here)
        if ($total_paid > 0) {
            $payment_status = 'Partial';
        } else {
            $payment_status = 'Pending';
        }
        
        // Update PO payment status
        $this->db->where('po_id', $po_id);
        $this->db->update('purchase_orders', [
            'total_paid' => $total_paid,
            'payment_status' => $payment_status
        ]);
    }

    private function movePoToHistory($po_id)
    {
        $this->db->trans_start();
        
        try {
            // Get PO details
            $po = $this->purchase_model->getPurchaseOrder($po_id);
            if (!$po) return;
            
            // Insert into history
            $history_data = [
                'po_id' => $po->po_id,
                'supplier_id' => $po->supplier_id,
                'bill_no' => $po->bill_no,
                'bill_date' => $po->bill_date,
                'intended_payment_method' => $po->intended_payment_method,
                'vat_type' => $po->vat_type,
                'vat_percent' => $po->vat_percent,
                'description' => $po->description,
                'total_amount' => $po->total_amount,
                'total_paid' => $po->total_paid,
                'payment_completed_at' => date('Y-m-d H:i:s'),
                'moved_by' => $this->session->userdata('userid'),
                'moved_at' => date('Y-m-d H:i:s')
            ];
            
            $this->db->insert('purchase_item_history', $history_data);
            $history_id = $this->db->insert_id();
            
            // Copy PO items to history (map only existing columns and RETAIN po_item_id as required by schema)
            $po_items = $this->purchase_model->getPurchaseOrderItems($po_id);
            foreach ($po_items as $item) {
                $item_data = [
                    'history_id'         => $history_id,
                    'po_item_id'         => $item->po_item_id,
                    'po_id'              => $item->po_id,
                    'supplier_id'        => $item->supplier_id,
                    'product_id'         => $item->product_id,
                    'brand'              => isset($item->brand) ? $item->brand : null,
                    'category'           => isset($item->category) ? $item->category : null,
                    'purchase_date'      => isset($item->purchase_date) ? $item->purchase_date : null,
                    'genuine'            => isset($item->genuine) ? $item->genuine : 1,
                    'uom'                => isset($item->uom) ? $item->uom : null,
                    'inventory_type'     => isset($item->inventory_type) ? $item->inventory_type : null,
                    'quantity'           => isset($item->quantity) ? $item->quantity : 0,
                    'discount_percent'   => isset($item->discount_percent) ? $item->discount_percent : null,
                    'discount_amount'    => isset($item->discount_amount) ? $item->discount_amount : 0.00,
                    'company_price'      => isset($item->company_price) ? $item->company_price : 0.00,
                    'sale_price'         => isset($item->sale_price) ? $item->sale_price : 0.00,
                    'rack_no'            => isset($item->rack_no) ? $item->rack_no : null,
                    'bin_no'             => isset($item->bin_no) ? $item->bin_no : null,
                    'reorder_level'      => isset($item->reorder_level) ? $item->reorder_level : null,
                    'note'               => isset($item->note) ? $item->note : null,
                    'created_at'         => isset($item->created_at) ? $item->created_at : date('Y-m-d H:i:s'),
                    'created_by'         => isset($item->created_by) ? $item->created_by : $this->session->userdata('userid'),
                    'available_stock'    => isset($item->available_stock) ? $item->available_stock : 0,
                    'available_stock_at' => isset($item->available_stock_at) ? $item->available_stock_at : null,
                ];

                $this->db->insert('purchase_history_items', $item_data);
            }
            
            // Copy payments to history (map only existing columns)
            $payments = $this->purchase_model->getPoPayments($po_id);
            foreach ($payments as $payment) {
                $payment_data = [
                    'history_id'         => $history_id,
                    'po_id'              => $payment->po_id,
                    'payment_method'     => $payment->payment_method,
                    'paid_amount'        => $payment->paid_amount,
                    'card_or_cheque_no'  => isset($payment->card_or_cheque_no) ? $payment->card_or_cheque_no : null,
                    'cheque_date'        => isset($payment->cheque_date) ? $payment->cheque_date : null,
                    'bank_name'          => isset($payment->bank_name) ? $payment->bank_name : null,
                    'note'               => isset($payment->note) ? $payment->note : null,
                    'payment_date'       => isset($payment->payment_date) ? $payment->payment_date : date('Y-m-d H:i:s'),
                    'created_by'         => isset($payment->created_by) ? $payment->created_by : $this->session->userdata('userid'),
                ];

                $this->db->insert('purchase_history_payments', $payment_data);
            }
            
            // Status is already set to Archived by confirmPoPayments() before calling this method

            $this->db->trans_complete();

        } catch (Exception $e) {
            $this->db->trans_rollback();
        }
    }


    public function loadPurchaseHistory()
    {
        // Get filter parameters
        $supplierId = $this->input->get('supplier_id');
        $dateFrom = $this->input->get('date_from');
        $dateTo = $this->input->get('date_to');
        $billNumber = $this->input->get('bill_number');
        
        // Load suppliers for filter dropdown
        $data['suppliers'] = $this->purchase_model->loadActiveSuppliers();
        
        // Load purchase history with filters
        $data['purchases'] = $this->purchase_model->getPurchaseHistory($supplierId, $dateFrom, $dateTo, $billNumber);
        
        // Pass filter values back to view
        $data['filters'] = [
            'supplier_id' => $supplierId,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'bill_number' => $billNumber,
        ];
        
        // Debug output
        error_log("loadPurchaseHistory - Found " . count($data['purchases']) . " purchase history records");
        if (!empty($data['purchases'])) {
            $first = $data['purchases'][0];
            error_log("First record: " . print_r($first, true));
        }
        
        $this->load->view('purchase/purchase-history', $data);
    }

    public function getPurchaseHistoryDetails()
    {
        $po_id = $this->input->post('history_id');

        if (!$po_id) {
            echo json_encode(['status' => 'error', 'message' => 'PO ID is required']);
            return;
        }

        // First, try to get from history table (for archived POs)
        $this->db->select('*');
        $this->db->from('purchase_item_history');
        $this->db->where('po_id', $po_id);
        $history_po = $this->db->get()->row();

        if ($history_po) {
            $supplier = $this->purchase_model->getSupplierById($history_po->supplier_id);

            $moved_by_name = 'System';
            if (!empty($history_po->moved_by)) {
                $this->db->select('UserName');
                $this->db->from('users');
                $this->db->where('UserID', $history_po->moved_by);
                $user = $this->db->get()->row();
                if ($user) {
                    $moved_by_name = $user->UserName;
                }
            }

            $this->db->select('phi.*, p.product_name, p.barcode, b.itemBrandName as brand_name, c.itemCategoryName as category_name');
            $this->db->from('purchase_history_items phi');
            $this->db->join('products p', 'p.product_id = phi.product_id', 'left');
            $this->db->join('item_brands b', 'b.itemBrandId = p.item_brand_id', 'left');
            $this->db->join('item_categories c', 'c.itemCategoryId = p.item_category_id', 'left');
            $this->db->where('phi.history_id', $history_po->history_id);
            $this->db->order_by('phi.id', 'ASC');
            $items = $this->db->get()->result();

            $this->db->select('*');
            $this->db->from('purchase_history_payments');
            $this->db->where('history_id', $history_po->history_id);
            $this->db->order_by('payment_date', 'ASC');
            $payments = $this->db->get()->result();

            $data = [
                'history' => (object)[
                    'po_id'                => $history_po->po_id ?? 0,
                    'supplier_name'        => $supplier ? $supplier->supplier_name : 'Unknown',
                    'contact_no'           => $supplier ? ($supplier->contact_no ?? '') : '',
                    'email'                => $supplier ? ($supplier->email ?? '') : '',
                    'bill_no'              => $history_po->bill_no ?? '',
                    'bill_date'            => $history_po->bill_date ?? date('Y-m-d'),
                    'total_amount'         => $history_po->total_amount ?? 0,
                    'total_paid'           => $history_po->total_paid ?? 0,
                    'vat_type'             => $history_po->vat_type ?? 'none',
                    'vat_percent'          => $history_po->vat_percent ?? 0,
                    'description'          => $history_po->description ?? '',
                    'payment_completed_at' => $history_po->payment_completed_at ?? date('Y-m-d H:i:s'),
                    'moved_by_name'        => $moved_by_name,
                    'moved_at'             => $history_po->moved_at ?? date('Y-m-d H:i:s'),
                ],
                'items'    => $items    ?: [],
                'payments' => $payments ?: [],
            ];
        } else {
            // Fall back to active purchase_orders table
            $po = $this->purchase_model->getPurchaseOrder($po_id);
            if (!$po) {
                echo json_encode(['status' => 'error', 'message' => 'Purchase order not found']);
                return;
            }

            $supplier = $this->purchase_model->getSupplierById($po->supplier_id);
            $items    = $this->purchase_model->getPurchaseOrderItems($po_id);
            $payments = $this->purchase_model->getPoPayments($po_id);

            $data = [
                'history' => (object)[
                    'po_id'                => $po->po_id ?? 0,
                    'supplier_name'        => $supplier ? $supplier->supplier_name : 'Unknown',
                    'contact_no'           => $supplier ? ($supplier->contact_no ?? '') : '',
                    'email'                => $supplier ? ($supplier->email ?? '') : '',
                    'bill_no'              => $po->bill_no ?? '',
                    'bill_date'            => $po->bill_date ?? date('Y-m-d'),
                    'total_amount'         => $po->total_amount ?? 0,
                    'total_paid'           => $po->total_paid ?? 0,
                    'vat_type'             => $po->vat_type ?? 'none',
                    'vat_percent'          => $po->vat_percent ?? 0,
                    'description'          => $po->description ?? '',
                    'payment_completed_at' => $po->completed_at ?? date('Y-m-d H:i:s'),
                    'moved_by_name'        => 'Active PO',
                    'moved_at'             => $po->created_at ?? date('Y-m-d H:i:s'),
                ],
                'items'    => $items    ?: [],
                'payments' => $payments ?: [],
            ];
        }

        $this->load->view('purchase/purchase-history-details', $data);
    }

    public function downloadStockPDF()
    {
        $sdate = $this->input->post('sdate');
        $edate = $this->input->post('edate');
        $category_id = $this->input->post('category_id');
        $brand_id = $this->input->post('brand_id');
        $supplier_id = $this->input->post('supplier_id');

        // Check if dates are provided
        $has_date_filter = !empty($sdate) && !empty($edate);

        if ($has_date_filter) {
            if (!strtotime($sdate) || !strtotime($edate) || strtotime($sdate) > strtotime($edate)) {
                echo "Invalid date range. Start date must be before end date.";
                return;
            }
            $data['openBalanceSdate'] = date('Y-m-01', strtotime('-6 months', strtotime($sdate)));
            $data['openBalanceEdate'] = date('Y-m-d', strtotime($sdate . ' -1 day'));
        } else {
            $sdate = date('Y-m-01');
            $edate = date('Y-m-d');
            $data['openBalanceSdate'] = date('Y-m-01', strtotime('-6 months'));
            $data['openBalanceEdate'] = date('Y-m-d', strtotime('-1 day'));
        }

        // Step 1: Get filtered product purchase list
        $products = $this->purchase_model->loadPurchaseProductsAsGroup($category_id, $brand_id, $supplier_id);
        
        // If no products found with filters, return empty result
        if (empty($products)) {
            $data['stock'] = [];
        } else {
            $product_ids = array_column($products, 'product_id');

            // Step 2: Get stock summary for date range
            $stock_summary = $this->purchase_model->getStockInOutSummary($product_ids, $sdate, $edate);

            // Step 3: Loop and enrich each product row
            foreach ($products as &$p) {
                $pid = $p->product_id;

                // Open balance (as of sdate)
                $open_balance = $this->purchase_model->getOpenBalance($pid, $sdate);

                // Stock movements (include internal bill consumption — F-05)
                $stock_in     = $stock_summary[$pid]['stock_in']      ?? 0;
                $job_out      = $stock_summary[$pid]['job_out']       ?? 0;
                $bill_out     = $stock_summary[$pid]['bill_out']      ?? 0;
                $internal_out = $stock_summary[$pid]['internal_out']  ?? 0;
                $stock_out    = $job_out + $bill_out + $internal_out;

                // Assign to product object
                $p->open_balance    = $open_balance;
                $p->stock_in        = $stock_in;
                $p->stock_out       = $stock_out;
                $p->closing_balance = (float)$p->available_stock;
            }
            unset($p);

            $data['stock'] = $products;
        }

        // Add filter information for PDF header
        $data['filters'] = [
            'sdate' => $sdate,
            'edate' => $edate,
            'category_id' => $category_id,
            'brand_id' => $brand_id,
            'supplier_id' => $supplier_id,
            'has_date_filter' => $has_date_filter
        ];

        // Load PDF library
        $this->load->library('pdf');
        
        // Generate PDF
        $html = $this->load->view('stock/stock-pdf-template', $data, true);
        
        $this->pdf->loadHtml($html);
        $this->pdf->setPaper('A4', 'landscape');
        $this->pdf->render();
        
        $filename = 'Stock_Report_' . date('Y-m-d_H-i-s') . '.pdf';
        $this->pdf->stream($filename);
    }

    public function loadPurchasedItemHistory()
    {
        // Get filter parameters
        $supplierId = $this->input->get('supplier_id');
        $dateFrom = $this->input->get('date_from');
        $dateTo = $this->input->get('date_to');
        $itemName = $this->input->get('item_name');
        
        // Load suppliers for filter dropdown
        $data['suppliers'] = $this->purchase_model->loadActiveSuppliers();
        
        // Load purchased items history with filters
        $data['purchased_items'] = $this->purchase_model->getPurchasedItemHistory($supplierId, $dateFrom, $dateTo, $itemName);
        
        // Pass filter values back to view
        $data['filters'] = [
            'supplier_id' => $supplierId,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'item_name' => $itemName,
        ];
        
        $this->load->view('purchase/purchaseditem-history', $data);
    }

    public function writeOffProductStock()
    {
        $this->require_permission('stock.manage');
        $product_id = (int)$this->input->post('product_id');
        $reason     = trim($this->input->post('reason'));

        if (!$product_id) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid product ID']);
            return;
        }
        if (empty($reason)) {
            echo json_encode(['status' => 'error', 'message' => 'A reason is required for stock write-off']);
            return;
        }

        // Zero out available_stock across all completed PO batches for this product
        $this->db->join('purchase_orders po', 'po.po_id = purchase_order_items.po_id');
        $this->db->where('purchase_order_items.product_id', $product_id);
        $this->db->where('po.completed_by >', 0);
        $this->db->update('purchase_order_items', [
            'available_stock'    => 0,
            'available_stock_at' => date('Y-m-d H:i:s'),
        ]);

        $affected = $this->db->affected_rows();

        $this->load->model('logs_model');
        $this->logs_model->log_activity(
            'Stock Write-Off',
            'Product ID: ' . $product_id . ' | Reason: ' . $reason . ' | Batches zeroed: ' . $affected
        );

        echo json_encode([
            'status'  => 'success',
            'message' => 'Stock written off. ' . $affected . ' batch(es) zeroed.',
        ]);
    }

    public function writeOffBatchStock()
    {
        $this->require_permission('stock.manage');
        $po_item_id = (int)$this->input->post('po_item_id');
        $reason     = trim($this->input->post('reason'));

        if (!$po_item_id) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid batch ID']);
            return;
        }
        if (empty($reason)) {
            echo json_encode(['status' => 'error', 'message' => 'A reason is required for stock write-off']);
            return;
        }

        $batch = $this->purchase_model->getPoItem($po_item_id);
        if (!$batch) {
            echo json_encode(['status' => 'error', 'message' => 'Batch not found']);
            return;
        }

        $this->db->where('po_item_id', $po_item_id);
        $this->db->update('purchase_order_items', [
            'available_stock'    => 0,
            'available_stock_at' => date('Y-m-d H:i:s'),
        ]);

        $this->load->model('logs_model');
        $this->logs_model->log_activity(
            'Stock Write-Off (Batch)',
            'PO Item ID: ' . $po_item_id . ' | Product ID: ' . $batch->product_id . ' | Reason: ' . $reason
        );

        echo json_encode([
            'status'  => 'success',
            'message' => 'Batch stock written off successfully.',
        ]);
    }

    public function updateSellPrice()
    {
        $poItemId = $this->input->post('po_item_id');
        $salePrice = $this->input->post('sale_price');
        
        if (!$poItemId || !$salePrice) {
            echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
            return;
        }
        
        try {
            // Load the model
            $this->load->model('purchase_model');
            
            // Update the sell price
            $this->db->where('po_item_id', $poItemId);
            $result = $this->db->update('purchase_order_items', ['sale_price' => $salePrice]);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Sell price updated successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update sell price']);
            }
            
        } catch (Exception $e) {
            log_message('error', 'updateSellPrice Exception: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Database error occurred']);
        }
    }
}
