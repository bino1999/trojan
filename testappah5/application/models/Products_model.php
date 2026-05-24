<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Products_model extends CI_Model
{

    public function getAllCustomers() {
        $this->db->select('*');
        $this->db->from('customers');
        $this->db->order_by('name', 'ASC');
        $query = $this->db->get();
        return $query->result();
    }

public function checkDuplicateProduct($name, $brandId, $categoryId) {
    $this->db->where('product_name', $name);
    $this->db->where('item_brand_id', $brandId);
    $this->db->where('item_category_id', $categoryId);
    //$this->db->where('is_deleted', 0);
    return $this->db->get('products')->row();
}

public function checkBarcodeExists($barcode) {
    $this->db->where('barcode', $barcode);
    //$this->db->where('is_deleted', 0);
    return $this->db->get('products')->row();
}

public function addProduct($data) {
    $productData = array(
        'product_name' => $data['product_name'],
        'sku' => $data['sku'],
        'barcode' => $data['barcode'],
        'item_brand_id' => $data['item_brand_id'],
        'item_category_id' => $data['item_category_id'],
        'measurement_unit' => $data['measurement_unit'],
        'sale_price' => $data['sale_price'],
        'reorder_level' => $data['reorder_level'],
        'description' => $data['description'],
        'inventory_type' => isset($data['inventory_type']) ? strtolower($data['inventory_type']) : 'sale',
        'is_active' => $data['is_active'],
        'created_at' => $data['created_at'],
        'updated_at' => $data['updated_at'],
        'created_by' => $data['created_by'],
        'is_deleted' => 0
    );

    $this->db->insert('products', $productData);
    return $this->db->affected_rows() > 0 ? $this->db->insert_id() : false;
}

public function updateProduct($product_id, $data) {
    $this->db->where('product_id', $product_id);
    $this->db->update('products', $data);
    
    return $this->db->affected_rows() > 0;
}

public function updateProductStatus($product_id, $status) {
    $this->db->where('product_id', $product_id);
    $this->db->update('products', [
        'is_active' => $status,
        'updated_at' => date('Y-m-d H:i:s')
    ]);
    
    return $this->db->affected_rows() > 0;
}

public function loadProductByFilter($filterBrand = null, $filterCategory = null) {
    $this->db->select('
        products.*,
        brands.itemBrandName as brand_name,
        categories.itemCategoryName as category_name
    ');
    $this->db->from('products');

    $this->db->join('item_brands brands', 'brands.itemBrandId = products.item_brand_id', 'left');
    
    $this->db->join('item_categories categories', 'categories.itemCategoryId = products.item_category_id', 'left');
    
    if ($filterBrand > 0) {
        $this->db->where('products.item_brand_id', $filterBrand);
    }

    if ($filterCategory > 0) {
        $this->db->where('products.item_category_id', $filterCategory);
    }
    
    //$this->db->where('products.is_deleted', 0);
    $this->db->order_by('products.product_name', 'ASC');
    
    $query = $this->db->get();
    return $query->result();
}

public function loadProduct($product_id = null) {
    $this->db->select('
        products.*,
        brands.itemBrandName as brand_name,
        categories.itemCategoryName as category_name
    ');
    $this->db->from('products');

    $this->db->join('item_brands brands', 'brands.itemBrandId = products.item_brand_id', 'left');
    
    $this->db->join('item_categories categories', 'categories.itemCategoryId = products.item_category_id', 'left');
    
    if ($product_id > 0) {
        $this->db->where('products.product_id', $product_id);
    }
    
    //$this->db->where('products.is_deleted', 0);
    $this->db->order_by('products.product_name', 'ASC');
    
    $query = $this->db->get();
    return $query->result();
}

public function getProductWithDetails($product_id) {
    $this->db->select('
        products.*,
        brands.itemBrandName as brand_name,
        categories.itemCategoryName as category_name
    ');
    $this->db->from('products');
    $this->db->join('item_brands brands', 'brands.itemBrandId = products.item_brand_id', 'left');
    $this->db->join('item_categories categories', 'categories.itemCategoryId = products.item_category_id', 'left');
    $this->db->where('products.product_id', $product_id);
    $this->db->where('products.is_deleted', 0);
    
    $query = $this->db->get();
    return $query->row();
}

    public function loadItemBrand($itemBrandId = null)
    {
        $this->db->select('*');
        $this->db->from('item_brands');
        if ($itemBrandId > 0) {
            $this->db->where('itemBrandId', $itemBrandId);
        }
        $this->db->where('isDeleted', 0);
        $query = $this->db->get();
        return $query->result();
    }

    public function loadItemCategory($itemCategoryId = null)
    {
        $this->db->select('*');
        $this->db->from('item_categories');

        if ($itemCategoryId) {
            $this->db->like('itemCategoryId', $itemCategoryId);
        }
        $this->db->where('isDeleted', 0);
        $this->db->order_by('itemCategoryId', 'ASC');
        $query = $this->db->get();
        return $query->result();
    }

    public function loadSections($employeeSectionId = null) {
        $this->db->select('employee_sections.*');
        $this->db->from('employee_sections');        
        if ($employeeSectionId > 0) {
            $this->db->where('employee_sections.employeeSectionId', $employeeSectionId);
        }        
        $this->db->order_by('employee_sections.employeeSectionName', 'ASC');
        $query = $this->db->get();
        return $query->result();
    }

    public function loadServiceItemsCategory($sic_id = null) {
        $this->db->select('*');
        $this->db->from('service_items_category');        
        if ($sic_id > 0) {
            $this->db->where('service_items_category.sic_id', $sic_id);
        }        
        $this->db->order_by('service_items_category.sci_name', 'ASC');
        $query = $this->db->get();
        return $query->result();
    }
    

}