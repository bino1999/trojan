<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Products extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('products_model');
    }


    public function index()
    {
        $this->require_permission('products.view');
        $data['mainMenuName'] = 'Products';
        $data['subMenuName'] = 'Products';

        $data['products'] = $this->products_model->loadProduct();
        $data['brands'] = $this->products_model->loadItemBrand();
        $data['categories'] = $this->products_model->loadItemCategory();

        $this->load->view('layout/header');
        $this->load->view('layout/top_navbar');
        $this->load->view('layout/left_sidebar', $data);
        $this->load->view('general/products-manage', $data);
        $this->load->view('layout/footer');
    }

    public function loadProductsList()
    {
        $this->require_permission('products.view');
        $filterCategory = $this->input->post('filterCategory');
        $filterBrand = $this->input->post('filterBrand');
        $data['products'] = $this->products_model->loadProductByFilter($filterBrand, $filterCategory);
        $data['brands'] = $this->products_model->loadItemBrand();
        $data['categories'] = $this->products_model->loadItemCategory();

        $this->load->view('general/products-list', $data);
    }

    public function productSave()
    {
        $this->require_permission('products.create');
        if ($this->input->method() === 'post') {

            // Set validation rules
            $this->form_validation->set_rules('product_name', 'Product Name', 'trim|required');
            $this->form_validation->set_rules('brand', 'Brand', 'trim|required');
            $this->form_validation->set_rules('category', 'Category', 'trim|required');
            $this->form_validation->set_rules('unit', 'Unit', 'trim|required');
            $this->form_validation->set_rules('inventory_type', 'Inventory Type', 'trim|required|in_list[sale,internal,both]');

            if ($this->form_validation->run() == FALSE) {
                echo json_encode([
                    'status' => 'error',
                    'message' => validation_errors()
                ]);
                return;
            }

            // Check for duplicate (name + brand + category)
            $duplicateCheck = $this->products_model->checkDuplicateProduct(
                $this->input->post('product_name'),
                $this->input->post('brand'),
                $this->input->post('category')
            );

            if ($duplicateCheck) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Product with same name, brand and category already exists!'
                ]);
                return;
            }

            $barcode = $this->input->post('barcode');
            if (empty($barcode)) {
                $barcode = $this->generateBarcode();
                while ($this->products_model->checkBarcodeExists($barcode)) {
                    $barcode = $this->generateBarcode();
                }
            } else {
                if ($this->products_model->checkBarcodeExists($barcode)) {
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'Barcode already exists!'
                    ]);
                    return;
                }
            }


            $data = [
                'product_name' => $this->input->post('product_name'),
                'sku' => $this->generateSKU($this->input->post('product_name')),
                'barcode' => $barcode,
                'item_brand_id' => $this->input->post('brand'),
                'item_category_id' => $this->input->post('category'),
                'measurement_unit' => $this->input->post('unit'),
                'sale_price' => $this->input->post('salePrice') ?? 0,
                'reorder_level' => $this->input->post('reorderLevel') ?? 0,
                'description' => $this->input->post('description'),
                'inventory_type' => strtolower($this->input->post('inventory_type')),
                'is_active' => $this->input->post('is_active') ? 1 : 0,
                'created_by' => $this->session->userdata('userid'),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $insertId = $this->products_model->addProduct($data);
            if ($insertId) {
                $response = [
                    'status' => 'success',
                    'message' => 'Product added successfully.',
                    'barcode' => $barcode
                ];
            } else {
                $response = [
                    'status' => 'error',
                    'message' => 'Failed to add product. Please try again.'
                ];
            }

            echo json_encode($response);
        }
    }

    function generateSKU($productName)
    {
        $slug = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $productName), 0, 4));
        $random = rand(10, 9999);
        return $slug . '-' . $random;
    }

    private function generateBarcode()
    {
        $prefix = '20'; // Your company prefix (2 digits)
        $random = str_pad(mt_rand(0, 999999999), 9, '0', STR_PAD_LEFT); // 9 random digits
        $barcode = $prefix . $random; // Now 11 digits total

        // Calculate EAN-13 check digit
        $sum = 0;
        for ($i = 0; $i < 11; $i++) { // Loop through first 11 digits
            // Multiply by 1 or 3 based on position (EAN-13 standard)
            $sum += ($i % 2 == 0) ? $barcode[$i] * 1 : $barcode[$i] * 3;
        }
        $checkDigit = (10 - ($sum % 10)) % 10;
        return $barcode . $checkDigit;
    }

    public function getProductById()
    {
        // Check if this is an AJAX request
        if (!$this->input->is_ajax_request() || $this->input->method() !== 'post') {
            $this->output->set_status_header(400);
            echo json_encode(['status' => 'error', 'message' => 'Bad request']);
            return;
        }

        $product_id = $this->input->post('product_id');

        if (empty($product_id)) {
            $this->output->set_status_header(400);
            echo json_encode(['status' => 'error', 'message' => 'Product ID is required']);
            return;
        }

        // Load the product model
        $this->load->model('products_model');

        // Get product data with brand and category info
        $product = $this->products_model->getProductWithDetails($product_id);

        if ($product) {
            $response = [
                'status' => 'success',
                'data' => [
                    'product_id' => $product->product_id,
                    'product_name' => $product->product_name,
                    'sku' => $product->sku,
                    'barcode' => $product->barcode,
                    'item_brand_id' => $product->item_brand_id,
                    'brand_name' => $product->brand_name ?? '',
                    'item_category_id' => $product->item_category_id,
                    'category_name' => $product->category_name ?? '',
                    'measurement_unit' => $product->measurement_unit,
                    'sale_price' => $product->sale_price,
                    'stock_quantity' => $product->stock_quantity,
                    'reorder_level' => $product->reorder_level,
                    'description' => $product->description,
                    'is_active' => $product->is_active,
                    'created_at' => $product->created_at
                ]
            ];
        } else {
            $response = [
                'status' => 'error',
                'message' => 'Product not found'
            ];
            $this->output->set_status_header(404);
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    public function updateProduct() {
        $this->require_permission('products.edit');
        // Check if this is an AJAX request
        if (!$this->input->is_ajax_request() || $this->input->method() !== 'post') {
            $this->output->set_status_header(400);
            echo json_encode(['status' => 'error', 'message' => 'Bad request']);
            return;
        }
    
        // Load form validation library
        $this->load->library('form_validation');
    
        // Set validation rules
        $this->form_validation->set_rules('product_id', 'Product ID', 'trim|required|numeric');
        $this->form_validation->set_rules('product_name', 'Product Name', 'trim|required|max_length[255]');
        $this->form_validation->set_rules('sku', 'SKU', 'trim|required|max_length[50]');
        $this->form_validation->set_rules('brand', 'Brand', 'trim|required|numeric');
        $this->form_validation->set_rules('category', 'Category', 'trim|required|numeric');
        $this->form_validation->set_rules('unit', 'Unit', 'trim|required|max_length[5]');
        $this->form_validation->set_rules('stock_quantity', 'Stock Quantity', 'trim|numeric');
        $this->form_validation->set_rules('inventory_type', 'Inventory Type', 'trim|required|in_list[sale,internal,both]');
    
        // Run validation
        if ($this->form_validation->run() == FALSE) {
            $response = [
                'status' => 'error',
                'message' => validation_errors('<li>', '</li>')
            ];
            $this->output->set_status_header(400);
            echo json_encode($response);
            return;
        }
    
        // Prepare update data
        $updateData = [
            'product_name' => $this->input->post('product_name'),
            'sku' => $this->input->post('sku'),
            'barcode' => $this->input->post('barcode'),
            'item_brand_id' => $this->input->post('brand'),
            'item_category_id' => $this->input->post('category'),
            'measurement_unit' => $this->input->post('unit'),
            'sale_price' => $this->input->post('edit_sale_price'),
            'stock_quantity' => $this->input->post('stock_quantity'),
            'reorder_level' => $this->input->post('reorderLevel'),
            'description' => $this->input->post('description'),
            'inventory_type' => strtolower($this->input->post('inventory_type')),
            'is_active' => $this->input->post('is_active') ? 1 : 0,
            'updated_at' => date('Y-m-d H:i:s')
        ];
    
        $updated = $this->products_model->updateProduct($this->input->post('product_id'), $updateData);
    
        if ($updated) {
            $response = [
                'status' => 'success',
                'message' => 'Product updated successfully'
            ];
        } else {
            $response = [
                'status' => 'error',
                'message' => 'No changes made or failed to update product'
            ];
            $this->output->set_status_header(500);
        }
    
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    public function toggleProductStatus() {
        $this->require_permission('products.edit');
        if (!$this->input->is_ajax_request() || $this->input->method() !== 'post') {
            $this->output->set_status_header(400);
            echo json_encode(['status' => 'error', 'message' => 'Bad request']);
            return;
        }
    
        $product_id = $this->input->post('product_id');
        $action = $this->input->post('action');
        
        if (empty($product_id) || !in_array($action, ['activate', 'deactivate'])) {
            $this->output->set_status_header(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid parameters']);
            return;
        }
    
        $this->load->model('products_model');
        
        $new_status = ($action === 'activate') ? 1 : 0;
        $updated = $this->products_model->updateProductStatus($product_id, $new_status);
    
        if ($updated) {
            $response = [
                'status' => 'success',
                'message' => 'Product ' . $action . 'd successfully'
            ];
        } else {
            $response = [
                'status' => 'error',
                'message' => 'Failed to ' . $action . ' product'
            ];
            $this->output->set_status_header(500);
        }
    
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }


}
