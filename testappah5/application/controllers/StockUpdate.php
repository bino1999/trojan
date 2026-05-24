<?php
defined('BASEPATH') or exit('No direct script access allowed');

class StockUpdate extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('products_model');
        $this->load->model('purchase_model');
    }

    public function index()
    {
        $this->require_permission('stock.manage');
        $data['mainMenuName'] = 'Stock Update';
        $data['subMenuName'] = 'Stock Update';

        // Load all products (no pagination)
        $data['products'] = $this->getProductsWithStock();
        $data['brands'] = $this->products_model->loadItemBrand();
        $data['categories'] = $this->products_model->loadItemCategory();
        $data['purchase_orders'] = $this->getCompletedPurchaseOrders();
        
        // Pagination data (single page)
        $data['current_page'] = 1;
        $data['per_page'] = null;
        $data['total_products'] = count($data['products']);
        $data['total_pages'] = 1;

        $this->load->view('layout/header');
        $this->load->view('layout/top_navbar');
        $this->load->view('layout/left_sidebar', $data);
        $this->load->view('stock/stock-update', $data);
        $this->load->view('layout/footer');
    }

    /**
     * Get products with their current stock information from purchase orders
     */
    private function getProductsWithStock($limit = null, $offset = 0)
    {
        $this->db->select('
            p.product_id,
            p.product_name,
            p.sku,
            p.barcode,
            p.measurement_unit,
            COALESCE(MAX(poi.sale_price), p.sale_price) as sale_price,
            COALESCE(MAX(poi.company_price), p.cost_price) as cost_price,
            p.stock_quantity,
            p.reorder_level,
            p.inventory_type,
            p.is_active,
            ib.itemBrandName as brand_name,
            ic.itemCategoryName as category_name,
            COALESCE(SUM(poi.available_stock), 0) as total_available_stock,
            COUNT(poi.po_item_id) as po_item_count,
            GROUP_CONCAT(DISTINCT poi.po_id) as existing_po_ids
        ');
        $this->db->from('products p');
        $this->db->join('item_brands ib', 'ib.itemBrandId = p.item_brand_id', 'left');
        $this->db->join('item_categories ic', 'ic.itemCategoryId = p.item_category_id', 'left');
        $this->db->join('purchase_order_items poi', 'poi.product_id = p.product_id', 'left');
        $this->db->join('purchase_orders po', 'po.po_id = poi.po_id', 'left');
        $this->db->where('p.is_deleted', 0);
        $this->db->where('p.is_active', 1);
        $this->db->group_by('p.product_id');
        $this->db->order_by('p.product_name', 'ASC');
        if ($limit !== null) {
            $this->db->limit($limit, $offset);
        }
        
        $query = $this->db->get();
        return $query->result();
    }

    /**
     * Get total products count for pagination
     */
    private function getTotalProductsCount()
    {
        $this->db->select('COUNT(DISTINCT p.product_id) as total');
        $this->db->from('products p');
        $this->db->where('p.is_deleted', 0);
        $this->db->where('p.is_active', 1);
        
        $query = $this->db->get();
        return $query->row()->total;
    }

    /**
     * Get completed purchase orders for stock updates
     */
    private function getCompletedPurchaseOrders()
    {
        $this->db->select('po_id, bill_no, bill_date, supplier_id');
        $this->db->from('purchase_orders');
        $this->db->where('is_deleted', 0);
        $this->db->where('status', 'Completed');
        $this->db->order_by('bill_date', 'DESC');
        $this->db->limit(50); // Limit to recent 50 POs
        
        $query = $this->db->get();
        return $query->result();
    }


    /**
     * Save bulk updates for products and stock
     */
    public function saveBulkUpdates()
    {
        $this->require_permission('stock.manage');
        if (!$this->input->is_ajax_request() || $this->input->method() !== 'post') {
            $this->output->set_status_header(400);
            echo json_encode(['status' => 'error', 'message' => 'Bad request']);
            return;
        }

        $this->db->trans_start();

        try {
            $updated_count = 0;
            $errors = [];

            // Get all form data
            $sale_prices = $this->input->post('sale_price');
            $company_prices = $this->input->post('company_price');
            $total_stocks = $this->input->post('total_stock');
            $inventory_types = $this->input->post('inventory_type');
            $units = $this->input->post('unit');
            $is_active = $this->input->post('is_active');
            $po_ids = $this->input->post('po_id');

            if ($sale_prices) {
                foreach ($sale_prices as $product_id => $sale_price) {
                    $sale_price = (float)$sale_price;
                    $company_price = (float)($company_prices[$product_id] ?? 0);
                    
                    // Validate that sale price is not lower than company price
                    if ($sale_price > 0 && $company_price > 0 && $sale_price < $company_price) {
                        $errors[] = "Product ID {$product_id}: Sale price ({$sale_price}) cannot be lower than company price ({$company_price})";
                        continue;
                    }
                    
                    // Update product information
                    $product_data = [
                        'sale_price' => $sale_price,
                        'cost_price' => $company_price,
                        'measurement_unit' => $units[$product_id] ?? 'p',
                        'inventory_type' => $inventory_types[$product_id] ?? 'sale',
                        'is_active' => (int)($is_active[$product_id] ?? 1),
                        'updated_at' => date('Y-m-d H:i:s')
                    ];

                    $this->db->where('product_id', $product_id);
                    $this->db->update('products', $product_data);

                    // Update stock if provided
                    if (isset($total_stocks[$product_id]) && $total_stocks[$product_id] !== '') {
                        $new_stock = (float)$total_stocks[$product_id];
                        $selected_po_id = isset($po_ids[$product_id]) ? $po_ids[$product_id] : null;
                        
                        if ($selected_po_id && $selected_po_id !== '') {
                            // Check if PO item already exists for this product and PO
                            $this->db->select('po_item_id');
                            $this->db->from('purchase_order_items');
                            $this->db->where('product_id', $product_id);
                            $this->db->where('po_id', $selected_po_id);
                            $existing_po_item = $this->db->get()->row();
                            
                            if ($existing_po_item) {
                                // Update existing PO item
                                $this->db->where('po_item_id', $existing_po_item->po_item_id);
                                $this->db->update('purchase_order_items', [
                                    'available_stock' => $new_stock,
                                    'available_stock_at' => date('Y-m-d H:i:s')
                                ]);
                            } else {
                                // Create new PO item entry
                                $this->createNewPoItem($product_id, $selected_po_id, $new_stock, $product_data);
                            }
                        } else {
                            // No PO selected, update existing stock if available
                            $this->db->select('po_item_id');
                            $this->db->from('purchase_order_items');
                            $this->db->where('product_id', $product_id);
                            $this->db->limit(1);
                            $po_item = $this->db->get()->row();
                            
                            if ($po_item) {
                                $this->db->where('po_item_id', $po_item->po_item_id);
                                $this->db->update('purchase_order_items', [
                                    'available_stock' => $new_stock,
                                    'available_stock_at' => date('Y-m-d H:i:s')
                                ]);
                            }
                        }
                    }

                    $updated_count++;
                }
            }

                $this->db->trans_complete();

                if ($this->db->trans_status() === FALSE) {
                    throw new Exception('Database transaction failed');
                }

                // Check for validation errors
                if (!empty($errors)) {
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'Validation errors: ' . implode('; ', $errors)
                    ]);
                    return;
                }

                echo json_encode([
                    'status' => 'success',
                    'message' => "Successfully updated {$updated_count} products"
                ]);

        } catch (Exception $e) {
            $this->db->trans_rollback();
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to update products: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Create new PO item entry
     */
    private function createNewPoItem($product_id, $po_id, $stock_quantity, $product_data)
    {
        // Get PO details
        $this->db->select('supplier_id, bill_date');
        $this->db->from('purchase_orders');
        $this->db->where('po_id', $po_id);
        $po = $this->db->get()->row();

        if (!$po) {
            return false;
        }

        // Get product details
        $this->db->select('*');
        $this->db->from('products');
        $this->db->where('product_id', $product_id);
        $product = $this->db->get()->row();

        if (!$product) {
            return false;
        }

        // Insert new purchase order item
        $insertData = [
            'po_id' => $po_id,
            'supplier_id' => $po->supplier_id,
            'product_id' => $product_id,
            'brand' => $product_data['brand_name'] ?? '',
            'category' => $product_data['category_name'] ?? '',
            'purchase_date' => $po->bill_date,
            'genuine' => 1,
            'uom' => $product_data['measurement_unit'] ?? 'p',
            'inventory_type' => $product_data['inventory_type'] ?? 'sale',
            'quantity' => $stock_quantity,
            'discount_percent' => 0.00,
            'discount_amount' => 0.00,
            'company_price' => $product_data['cost_price'] ?? $product->cost_price ?? 0,
            'sale_price' => $product_data['sale_price'],
            'rack_no' => '',
            'bin_no' => '',
            'reorder_level' => $product->reorder_level ?? 0,
            'note' => 'Added via Stock Update Tool',
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => $this->session->userdata('userid'),
            'available_stock' => $stock_quantity,
            'available_stock_at' => date('Y-m-d H:i:s')
        ];

        return $this->db->insert('purchase_order_items', $insertData);
    }

    /**
     * Filter products by brand and category
     */
    public function filterProducts()
    {
        $this->require_permission('stock.manage');
        $filterBrand = $this->input->post('filterBrand');
        $filterCategory = $this->input->post('filterCategory');
        $searchTerm = $this->input->post('searchTerm');
        $this->db->select('
            p.product_id,
            p.product_name,
            p.sku,
            p.barcode,
            p.measurement_unit,
            COALESCE(MAX(poi.sale_price), p.sale_price) as sale_price,
            COALESCE(MAX(poi.company_price), p.cost_price) as cost_price,
            p.stock_quantity,
            p.reorder_level,
            p.inventory_type,
            p.is_active,
            ib.itemBrandName as brand_name,
            ic.itemCategoryName as category_name,
            COALESCE(SUM(poi.available_stock), 0) as total_available_stock,
            COUNT(poi.po_item_id) as po_item_count,
            GROUP_CONCAT(DISTINCT poi.po_id) as existing_po_ids
        ');
        $this->db->from('products p');
        $this->db->join('item_brands ib', 'ib.itemBrandId = p.item_brand_id', 'left');
        $this->db->join('item_categories ic', 'ic.itemCategoryId = p.item_category_id', 'left');
        $this->db->join('purchase_order_items poi', 'poi.product_id = p.product_id', 'left');
        $this->db->where('p.is_deleted', 0);
        $this->db->where('p.is_active', 1);

        if ($filterBrand > 0) {
            $this->db->where('p.item_brand_id', $filterBrand);
        }

        if ($filterCategory > 0) {
            $this->db->where('p.item_category_id', $filterCategory);
        }

        // Add search functionality
        if (!empty($searchTerm)) {
            $this->db->group_start();
            $this->db->like('p.product_name', $searchTerm);
            $this->db->or_like('p.sku', $searchTerm);
            $this->db->or_like('p.barcode', $searchTerm);
            $this->db->group_end();
        }

        $this->db->group_by('p.product_id');
        $this->db->order_by('p.product_name', 'ASC');
        $query = $this->db->get();
        $products = $query->result();

        $data['products'] = $products;
        $data['purchase_orders'] = $this->getCompletedPurchaseOrders();
        $data['current_page'] = 1;
        $data['per_page'] = null;
        $data['total_products'] = count($products);
        $data['total_pages'] = 1;
        $this->load->view('stock/products-list', $data);
    }

    /**
     * Get total count for filtered products
     */
    private function getFilteredProductsCount($filterBrand = null, $filterCategory = null, $searchTerm = null)
    {
        $this->db->select('COUNT(DISTINCT p.product_id) as total');
        $this->db->from('products p');
        $this->db->join('item_brands ib', 'ib.itemBrandId = p.item_brand_id', 'left');
        $this->db->join('item_categories ic', 'ic.itemCategoryId = p.item_category_id', 'left');
        $this->db->join('purchase_order_items poi', 'poi.product_id = p.product_id', 'left');
        $this->db->where('p.is_deleted', 0);
        $this->db->where('p.is_active', 1);

        if ($filterBrand > 0) {
            $this->db->where('p.item_brand_id', $filterBrand);
        }

        if ($filterCategory > 0) {
            $this->db->where('p.item_category_id', $filterCategory);
        }

        // Add search functionality
        if (!empty($searchTerm)) {
            $this->db->group_start();
            $this->db->like('p.product_name', $searchTerm);
            $this->db->or_like('p.sku', $searchTerm);
            $this->db->or_like('p.barcode', $searchTerm);
            $this->db->group_end();
        }

        $query = $this->db->get();
        return $query->row()->total;
    }
}
