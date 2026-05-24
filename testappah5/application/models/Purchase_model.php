<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Purchase_model extends CI_Model
{

    public function loadActiveProducts($product_id = null)
    {
        $this->db->select('products.*, brands.itemBrandName as brand_name, categories.itemCategoryName as category_name');
        $this->db->from('products');
        $this->db->join('item_brands brands', 'brands.itemBrandId = products.item_brand_id', 'left');
        $this->db->join('item_categories categories', 'categories.itemCategoryId = products.item_category_id', 'left');
        if ($product_id > 0) {
            $this->db->where('products.product_id', $product_id);
        }
        $this->db->where('is_active', 1);
        $this->db->order_by('product_name', 'ASC');
        $query = $this->db->get();
        return $query->result();
    }

    public function loadActiveProductsByCategoryBrand($category_id = null, $brand_id = null)
    {
        $this->db->select('products.*, brands.itemBrandName as brand_name, categories.itemCategoryName as category_name');
        $this->db->from('products');
        $this->db->join('item_brands brands', 'brands.itemBrandId = products.item_brand_id', 'left');
        $this->db->join('item_categories categories', 'categories.itemCategoryId = products.item_category_id', 'left');
        $this->db->where('is_active', 1);

        if ($category_id > 0) {
            $this->db->where('products.item_category_id', $category_id);
        }
        if ($brand_id > 0) {
            $this->db->where('products.item_brand_id', $brand_id);
        }

        $this->db->order_by('product_name', 'ASC');
        $query = $this->db->get();
        return $query->result();
    }

    public function loadProductCategories($category_id = null)
    {
        $this->db->select('*');
        $this->db->from('item_categories');
        if ($category_id > 0) {
            $this->db->where('itemCategoryId', $category_id);
        }
        $this->db->where('isDeleted', 0);
        $this->db->order_by('itemCategoryName', 'ASC');
        $query = $this->db->get();
        return $query->result();
    }

    public function loadProductBrands($brand_id = null)
    {
        $this->db->select('*');
        $this->db->from('item_brands');
        if ($brand_id > 0) {
            $this->db->where('itemBrandId', $brand_id);
        }
        $this->db->where('isDeleted', 0);
        $this->db->order_by('itemBrandName', 'ASC');
        $query = $this->db->get();
        return $query->result();
    }

    public function loadActiveSuppliers($supplier_id = null)
    {
        $this->db->select('*');
        $this->db->from('suppliers');

        if ($supplier_id > 0) {
            $this->db->where('supplier_id', $supplier_id);
        }

        $this->db->order_by('name', 'ASC');
        $query = $this->db->get();
        return $query->result();
    }

    public function loadPurchases($sdate = null, $edate = null, $supplier_id = null)
    {
        $this->db->select('po.*, s.name as supplier_name, u.UserName as created_by_name');
        $this->db->from('purchase_orders po');
        $this->db->join('suppliers s', 's.supplier_id = po.supplier_id', 'left');
        $this->db->join('users u', 'u.UserID = po.created_by', 'left');
        // Exclude any POs that have been moved to history as a final safeguard
        $this->db->join('purchase_item_history pih', 'pih.po_id = po.po_id', 'left');
        $this->db->where('po.is_deleted', 0);
        // Hide archived/finished POs from the active list
        $this->db->where_not_in('po.status', ['Archived', 'Finished']);
        $this->db->where('pih.po_id IS NULL', null, false);
        
        if ($sdate && $edate) {
            $this->db->where('po.bill_date >=', $sdate);
            $this->db->where('po.bill_date <=', $edate);
        }
        
        if ($supplier_id) {
            $this->db->where('po.supplier_id', $supplier_id);
        }
        
        $this->db->order_by('po.po_id', 'DESC');
        $query = $this->db->get();
        return $query->result();
    }

    public function addPurchaseOrderItem($data)
    {
        return $this->db->insert('purchase_order_items', $data);
    }

    public function getPurchaseOrder($po_id)
    {
        $this->db->select('*');
        $this->db->from('purchase_orders');
        $this->db->where('po_id', $po_id);
        $query = $this->db->get();
        return $query->row();
    }

    public function adjustStockForSupplierChange($po_id, $old_supplier_id, $new_supplier_id)
    {
        // This method would handle stock adjustment when supplier changes
        // For now, we'll just update the supplier_id in purchase_order_items
        $this->db->where('po_id', $po_id);
        $this->db->update('purchase_order_items', ['supplier_id' => $new_supplier_id]);
    }

    public function reduceStockForItem($po_item_id, $quantity)
    {
        // Reduce the available stock for an item
        $this->db->set('available_stock', 'GREATEST(available_stock - ' . (float)$quantity . ', 0)', false);
        $this->db->where('po_item_id', $po_item_id);
        $this->db->update('purchase_order_items');
    }

    public function getPoItem($po_item_id)
    {
        $this->db->select('*');
        $this->db->from('purchase_order_items');
        $this->db->where('po_item_id', $po_item_id);
        $query = $this->db->get();
        return $query->row();
    }

    public function getPurchaseOrderItems($po_id)
    {
        $this->db->select('*');
        $this->db->from('purchase_order_items');
        $this->db->where('po_id', $po_id);
        $this->db->order_by('po_item_id', 'ASC');
        $query = $this->db->get();
        return $query->result();
    }

    public function adjustStockForItemChange($po_item_id, $quantity_diff)
    {
        // Adjust available stock based on quantity difference
        if ($quantity_diff > 0) {
            // Quantity increased, add to stock
            $this->db->set('available_stock', 'available_stock + ' . (float)$quantity_diff, false);
        } else {
            // Quantity decreased, reduce from stock
            $this->db->set('available_stock', 'GREATEST(available_stock + ' . (float)$quantity_diff . ', 0)', false);
        }
        
        $this->db->where('po_item_id', $po_item_id);
        $this->db->update('purchase_order_items');
    }

    public function loadPurchaseOrderItems($po_id = null)
    {
        $this->db->select('poi.*, p.product_name, p.barcode, b.itemBrandName as brand_name, c.itemCategoryName as category_name, u.UserName as created_by, po.vat_type, po.vat_percent');
        $this->db->from('purchase_order_items poi');
        $this->db->join('products p', 'p.product_id = poi.product_id', 'left');
        $this->db->join('item_brands b', 'b.itemBrandId = p.item_brand_id', 'left');
        $this->db->join('item_categories c', 'c.itemCategoryId = p.item_category_id', 'left');
        $this->db->join('users u', 'u.UserID = poi.created_by', 'left');
        $this->db->join('purchase_orders po', 'po.po_id = poi.po_id', 'left');

        if ($po_id > 0) {
            $this->db->where('poi.po_id', $po_id);
        }
        $this->db->order_by('poi.po_item_id', 'ASC');
        $query = $this->db->get();
        return $query->result();
    }
    public function deletePurchaseOrderItem($po_item_id)
{
    $this->db->where('po_item_id', $po_item_id);
    return $this->db->delete('purchase_order_items');
}

    public function updateAvailableStockForItem($po_item_id, $new_quantity)
{
    // Update available_stock to match the new quantity
    $this->db->where('po_item_id', $po_item_id);
    $update_result = $this->db->update('purchase_order_items', [
        'available_stock' => $new_quantity,
        'available_stock_at' => date('Y-m-d H:i:s')
    ]);
    
    // Debug logging
    error_log("updateAvailableStockForItem - po_item_id: $po_item_id, new_quantity: $new_quantity, update_result: " . ($update_result ? 'success' : 'failed'));
    
    return $update_result;
}

    public function updateAvailableStockForCompletedPO($po_id)
    {
        // Set available_stock = quantity for all items in the PO
        $this->db->set('available_stock', 'quantity', false);
        $this->db->set('available_stock_at', date('Y-m-d H:i:s'));
        $this->db->where('po_id', $po_id);
        $this->db->update('purchase_order_items');
    }

    // =====================================================
    // PAYMENT SYSTEM METHODS
    // =====================================================

    public function getPoPayments($po_id)
    {
        $this->db->select('*');
        $this->db->from('purchase_order_payments');
        $this->db->where('po_id', $po_id);
        $this->db->order_by('payment_date', 'ASC');
        $query = $this->db->get();
        return $query->result();
    }

    public function getPurchaseHistory($supplierId = null, $dateFrom = null, $dateTo = null, $billNumber = null)
    {
        $this->db->select('pih.*, s.name as supplier_name, u.UserName as moved_by_name');
        $this->db->from('purchase_item_history pih');
        $this->db->join('suppliers s', 's.supplier_id = pih.supplier_id', 'left');
        $this->db->join('users u', 'u.UserID = pih.moved_by', 'left');
        
        // Apply filters
        if ($supplierId) {
            $this->db->where('pih.supplier_id', $supplierId);
        }
        if ($dateFrom) {
            $this->db->where('DATE(pih.bill_date) >=', $dateFrom);
        }
        if ($dateTo) {
            $this->db->where('DATE(pih.bill_date) <=', $dateTo);
        }
        if ($billNumber) {
            $this->db->like('pih.bill_no', $billNumber);
        }
        
        $this->db->order_by('pih.moved_at', 'DESC');
        $query = $this->db->get();
        return $query->result();
    }

    public function getPurchaseHistoryDetails($history_id)
    {
        // Get history header
        $this->db->select('pih.*, s.name as supplier_name, s.mobile as contact_no, s.email, u.UserName as moved_by_name');
        $this->db->from('purchase_item_history pih');
        $this->db->join('suppliers s', 's.supplier_id = pih.supplier_id', 'left');
        $this->db->join('users u', 'u.UserID = pih.moved_by', 'left');
        $this->db->where('pih.history_id', $history_id);
        $history = $this->db->get()->row();

        if (!$history) return null;

        // Get history items
        $this->db->select('phi.*, p.product_name, p.barcode, b.itemBrandName as brand_name, c.itemCategoryName as category_name');
        $this->db->from('purchase_history_items phi');
        $this->db->join('products p', 'p.product_id = phi.product_id', 'left');
        $this->db->join('item_brands b', 'b.itemBrandId = p.item_brand_id', 'left');
        $this->db->join('item_categories c', 'c.itemCategoryId = p.item_category_id', 'left');
        $this->db->where('phi.history_id', $history_id);
        $this->db->order_by('phi.id', 'ASC');
        $items = $this->db->get()->result();

        // Get history payments
        $this->db->select('*');
        $this->db->from('purchase_history_payments');
        $this->db->where('history_id', $history_id);
        $this->db->order_by('payment_date', 'ASC');
        $payments = $this->db->get()->result();

        return [
            'history' => $history,
            'items' => $items,
            'payments' => $payments
        ];
    }

    public function getSupplierById($supplier_id)
    {
        $this->db->select('supplier_id, name as supplier_name, mobile as contact_no, email, address_no, street, city_id, district_id, province_id, postal_code');
        $this->db->from('suppliers');
        $this->db->where('supplier_id', $supplier_id);
        $query = $this->db->get();
        return $query->row();
    }



    public function loadPurchasesProductsList($sdate = null, $edate = null, $category_id = null, $brand_id = null, $supplier_id = null, $product_id = null) 
    {
        $this->db->select('poi.*, po.bill_no, po.bill_date, po.vat_type, p.product_name, p.barcode, b.itemBrandName as brand_name, c.itemCategoryName as category_name');
        $this->db->from('purchase_order_items poi');
        $this->db->join('products p', 'p.product_id = poi.product_id', 'left');
        $this->db->join('item_brands b', 'b.itemBrandId = p.item_brand_id', 'left');
        $this->db->join('item_categories c', 'c.itemCategoryId = p.item_category_id', 'left');
        $this->db->join('purchase_orders po', 'po.po_id = poi.po_id', 'left');

        $this->db->where('po.status', 'Completed');

        if ($supplier_id > 0) {
            $this->db->where('poi.supplier_id', $supplier_id);
        }

        if($product_id > 0) {
            $this->db->where('poi.product_id', $product_id);
        }

        if (!empty($sdate) && !empty($edate)) {
            $this->db->where('poi.created_at >=', $sdate . ' 00:00:00');
            $this->db->where('poi.created_at <=', $edate . ' 23:59:59');
        }
        $this->db->order_by('poi.po_item_id', 'DESC');
        $query = $this->db->get();
        return $query->result();
    }


    public function loadPurchaseProductsAsGroup($category_id = null, $brand_id = null, $supplier_id = null, $product_id = null) 
    {
        $this->db->select('poi.*, po.bill_no, po.bill_date, po.vat_type, p.product_name, p.sku, b.itemBrandName as brand_name, c.itemCategoryName as category_name, s.name as supplier_name');
        $this->db->from('purchase_order_items poi');
        $this->db->join('products p', 'p.product_id = poi.product_id', 'left');
        $this->db->join('item_brands b', 'b.itemBrandId = p.item_brand_id', 'left');
        $this->db->join('item_categories c', 'c.itemCategoryId = p.item_category_id', 'left');
        $this->db->join('purchase_orders po', 'po.po_id = poi.po_id', 'left');
        $this->db->join('suppliers s', 's.supplier_id = poi.supplier_id', 'left');

        $this->db->where('po.status', 'Completed');

        if ($supplier_id > 0) {
            $this->db->where('poi.supplier_id', $supplier_id);
        }

        if($category_id > 0) {
            $this->db->where('p.item_category_id', $category_id);
        }   

        if($brand_id > 0) {
            $this->db->where('p.item_brand_id', $brand_id);
        }

        if($product_id > 0) {
            $this->db->where('poi.product_id', $product_id);
        }
        $this->db->group_by('poi.product_id');
        $this->db->order_by('poi.po_item_id', 'DESC');
        $query = $this->db->get();
        return $query->result();
    }

    public function getOpenBalance($product_id, $sdate)
{
    // Total purchased before sdate
    $this->db->select_sum('quantity');
    $this->db->from('purchase_order_items');
    $this->db->where('product_id', $product_id);
    $this->db->where('purchase_date <', $sdate);
    $purchased = $this->db->get()->row()->quantity ?? 0;

    // Issued in jobs
    $this->db->select_sum('quantity');
    $this->db->from('services_job_items');
    $this->db->where('item_type', 'product');
    $this->db->where('item_id', $product_id);
    $this->db->where('confirmed_by >', 0);
    $this->db->where('DATE(created_at) <', $sdate);
    $job_issued = $this->db->get()->row()->quantity ?? 0;

    // Issued in quick bill (handle returns)
    $this->db->select('SUM(quantity - IFNULL(return_quantity, 0)) as issued');
    $this->db->from('quick_bill_items');
    $this->db->where('item_id', $product_id);
    $this->db->where('DATE(created_at) <', $sdate);
    $quick_issued = $this->db->get()->row()->issued ?? 0;

    $open_balance = $purchased - ($job_issued + $quick_issued);
    //return max($open_balance, 0); // no negative stock
    return $open_balance;
}

public function getStockInOutSummary($product_ids, $sdate, $edate)
{
    $summary = [];

    // === STOCK IN ===
    $this->db->select('product_id, SUM(quantity) as stock_in');
    $this->db->from('purchase_order_items');
    $this->db->where_in('product_id', $product_ids);
    $this->db->where('purchase_date >=', $sdate);
    $this->db->where('purchase_date <=', $edate);
    $this->db->group_by('product_id');
    $stock_in_result = $this->db->get()->result();
    foreach ($stock_in_result as $row) {
        $summary[$row->product_id]['stock_in'] = $row->stock_in;
    }

    // === STOCK OUT FROM JOB ===
    $this->db->select('item_id as product_id, SUM(quantity) as job_out');
    $this->db->from('services_job_items');
    $this->db->where('item_type', 'product');
    $this->db->where_in('item_id', $product_ids);
    $this->db->where('confirmed_by >', 0);
    $this->db->where('DATE(created_at) >=', $sdate);
    $this->db->where('DATE(created_at) <=', $edate);
    $this->db->group_by('item_id');
    $job_result = $this->db->get()->result();
    foreach ($job_result as $row) {
        $summary[$row->product_id]['job_out'] = $row->job_out;
    }

    // === STOCK OUT FROM QUICK BILL ===
    $this->db->select('item_id as product_id, SUM(quantity - IFNULL(return_quantity, 0)) as bill_out');
    $this->db->from('quick_bill_items');
    $this->db->where_in('item_id', $product_ids);
    $this->db->where('DATE(created_at) >=', $sdate);
    $this->db->where('DATE(created_at) <=', $edate);
    $this->db->group_by('item_id');
    $bill_result = $this->db->get()->result();
    foreach ($bill_result as $row) {
        $summary[$row->product_id]['bill_out'] = $row->bill_out;
    }

    return $summary;
}


public function updateAvailableStock()
{
    // Get all purchase items
    $this->db->select('po_item_id, quantity');
    $purchase_items = $this->db->get('purchase_order_items')->result();

    foreach ($purchase_items as $item) {
        $po_item_id = $item->po_item_id;
        $qty = $item->quantity;

        // Get issued from jobs
        $this->db->select_sum('quantity');
        $this->db->from('services_job_items');
        $this->db->where('po_item_id', $po_item_id);
        $this->db->where('item_type', 'product');
        $job_issued = $this->db->get()->row()->quantity ?? 0;

        // Get issued from quick bill (minus returns)
        $this->db->select('SUM(quantity - IFNULL(return_quantity, 0)) as issued');
        $this->db->from('quick_bill_items');
        $this->db->where('po_item_id', $po_item_id);
        $quick_issued = $this->db->get()->row()->issued ?? 0;

        // Calculate available stock
        $available = $qty - ($job_issued + $quick_issued);

        // Update
        $this->db->where('po_item_id', $po_item_id);
        $this->db->update('purchase_order_items', ['available_stock' => $available, 'available_stock_at' => date('Y-m-d H:i:s')]);
    }

    echo "Available stock updated successfully.";
}

public function getPurchasedItemHistory($supplierId = null, $dateFrom = null, $dateTo = null, $itemName = null)
{
    $this->db->select('
        poi.po_item_id,
        poi.po_id,
        poi.product_id,
        poi.quantity,
        poi.company_price,
        poi.sale_price,
        p.product_name as item_name,
        p.sku,
        s.name as supplier_name,
        po.bill_date as purchase_date,
        po.bill_no,
        po.created_at as po_created_at
    ');
    
    $this->db->from('purchase_order_items poi');
    $this->db->join('products p', 'p.product_id = poi.product_id', 'left');
    $this->db->join('purchase_orders po', 'po.po_id = poi.po_id', 'left');
    $this->db->join('suppliers s', 's.supplier_id = po.supplier_id', 'left');
    
    // Apply filters
    if ($supplierId) {
        $this->db->where('po.supplier_id', $supplierId);
    }
    
    if ($dateFrom) {
        $this->db->where('po.bill_date >=', $dateFrom);
    }
    
    if ($dateTo) {
        $this->db->where('po.bill_date <=', $dateTo);
    }
    
    if ($itemName) {
        $this->db->like('p.product_name', $itemName);
    }
    
    // Show all purchase order items (regardless of status for now)
    // You can add status filtering later if needed
    $this->db->where('po.is_deleted', 0);
    
    // Order by most recent purchases first (by PO creation date, then by item ID)
    $this->db->order_by('po.created_at', 'DESC');
    $this->db->order_by('poi.po_item_id', 'DESC');
    
    $query = $this->db->get();
    return $query->result();
}

 
}
