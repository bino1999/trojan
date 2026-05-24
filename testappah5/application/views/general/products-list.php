<table id="productTable" class="table table-striped table-responsive">
    <thead class="table-info">
        <tr>
            <th>#</th>
            <th>Product Name</th>
            <th>SKU</th>
            <th>Brand</th>
            <th>Category</th>
            <th>Unit</th>
            <th>Inventory Type</th>
            <th>Reorder Level</th>
            <th>Created At</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php $counter = 1; ?>
        <?php foreach ($products as $product): ?>
            <tr>
                <td><?= $counter++; ?></td>
                <td><?= htmlspecialchars($product->product_name); ?></td>
                <td><?= htmlspecialchars($product->sku); ?></td>
                <td><?= htmlspecialchars($product->brand_name ?? 'N/A'); ?></td>
                <td><?= htmlspecialchars($product->category_name ?? 'N/A'); ?></td>
                <td>
                    <?php
                    $unit = htmlspecialchars($product->measurement_unit);
                    if ($unit === 'kg') {
                        echo 'Kilogram';
                    } elseif ($unit === 'g') {
                        echo 'Gram';
                    } elseif ($unit === 'l') {
                        echo 'Liter';
                    } elseif ($unit === 'ml') {
                        echo 'Milliliter';
                    } elseif ($unit === 'p') {
                        echo 'Piece';
                    } else {
                        echo $unit; // Fallback for any unexpected values
                    }
                    ?>
                </td>
                <td>
                    <?php 
                        $itype = strtolower($product->inventory_type ?? 'sale');
                        echo $itype === 'internal' ? 'Internal Use' : ($itype === 'both' ? 'Both' : 'Sale');
                    ?>
                </td>
                <td><?= $product->reorder_level; ?></td>
                <td><?= date('M d, Y', strtotime($product->created_at)); ?></td>
                <td>
                    <span class="badge bg-<?= $product->is_active ? 'success' : 'warning' ?>">
                        <?= $product->is_active ? 'Active' : 'Inactive' ?>
                    </span>
                </td>
                <td>
                    <div class="btn-group" role="group">
                        <!-- Edit Button -->
                        <button class="btn btn-sm btn-info edit-product-btn"
                            data-id="<?= $product->product_id; ?>"
                            data-bs-toggle="modal"
                            data-bs-target="#editProductModal"
                            title="Edit">
                            <i class="fa fa-edit"></i>
                        </button>

                        <!-- Status Toggle Button -->
                        <?php if ($product->is_active): ?>
                            <!-- Deactivate Button (shown when product is active) -->
                            <button class="btn btn-sm btn-warning toggle-status-btn"
                                data-id="<?= $product->product_id; ?>"
                                data-action="deactivate"
                                title="Deactivate">
                                <i class="fa fa-undo"></i>
                            </button>
                        <?php else: ?>
                            <!-- Activate Button (shown when product is inactive) -->
                            <button class="btn btn-sm btn-success toggle-status-btn"
                                data-id="<?= $product->product_id; ?>"
                                data-action="activate"
                                title="Activate">
                                <i class="fa fa-undo"></i>
                            </button>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<script>
    $(document).ready(function() {
        $('#productTable').DataTable({
            "lengthMenu": [
                [150, 250, 500, -1],
                [150, 250, 500, "All"]
            ],
            "searching": true,
            "responsive": true
        });


    });


    $('.edit-product-btn').click(function() {
        var productId = $(this).data('id');

        $.ajax({
            url: '<?= base_url('products/getProductById') ?>',
            method: 'POST',
            data: {
                product_id: productId
            },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    // Populate form fields
                    $('#edit_product_id').val(response.data.product_id);
                    $('#edit_product_name').val(response.data.product_name);
                    $('#edit_sku').val(response.data.sku);
                    $('#edit_barcode').val(response.data.barcode);
                    $('#edit_brand').val(response.data.item_brand_id);
                    $('#edit_category').val(response.data.item_category_id);
                    $('#edit_unit').val(response.data.measurement_unit);
                    $('#edit_inventory_type').val((response.data.inventory_type || 'sale').toLowerCase());
                    $('#edit_sale_price').val(response.data.sale_price);
                    $('#edit_stock_quantity').val(response.data.stock_quantity);
                    $('#edit_reorderLevel').val(response.data.reorder_level);
                    $('#edit_description').val(response.data.description);
                    $('#edit_is_active').prop('checked', response.data.is_active == 1);

                    $('#editProductModal select').select2({
                        dropdownParent: $('#editProductModal')
                    });

                    $('#editProductModal').modal('show');
                } else {
                    Swal.fire('Error!', response.message, 'error');
                }
            },
            error: function() {
                Swal.fire('Error!', 'Failed to load product data', 'error');
            }
        });
    });


    $('#updateProductBtn').click(function(e) {
        e.preventDefault();

        // Get form data
        var formData = $('#editProductForm').serialize();

        // Show loading state
        var $btn = $(this);
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Updating...');

        $.ajax({
            url: '<?= base_url('products/updateProduct') ?>',
            method: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    
                    $('#editProductModal').modal('hide');
                    Toastify({
    text: response.message,
    className: "toastify-warning",
    duration: 5000,
    close: true,
    gravity: "top",
    position: "right"
}).showToast();
loadProducts();


                } else {
                    $('#updateError').html(response.message).show();
                }
            },
            error: function(xhr) {
                var errorMessage = xhr.responseJSON && xhr.responseJSON.message ?
                    xhr.responseJSON.message :
                    'Failed to update product. Please try again.';
                Swal.fire('Error!', errorMessage, 'error');
            },
            complete: function() {
                $btn.prop('disabled', false).html('Update Product');
            }
        });
    });


    $('.toggle-status-btn').click(function() {
        var productId = $(this).data('id');
        var action = $(this).data('action');
        var $btn = $(this);

        var actionText = action === 'activate' ? 'activate' : 'deactivate';

        Swal.fire({
            title: 'Are you sure?',
            text: "You are about to " + actionText + " this product.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, ' + actionText + ' it!'
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading state
                $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

                $.ajax({
                    url: '<?= base_url('products/toggleProductStatus') ?>',
                    method: 'POST',
                    data: {
                        product_id: productId,
                        action: action,
                        <?= $this->security->get_csrf_token_name() ?>: '<?= $this->security->get_csrf_hash() ?>'
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            Toastify({
    text: response.message,
    className: "toastify-success",
    duration: 5000,
    close: true,
    gravity: "top",
    position: "right"
}).showToast();
loadProducts();
                        } else {
                            Swal.fire('Error!', response.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error!', 'Failed to update product status', 'error');
                    },
                    complete: function() {
                        $btn.prop('disabled', false);
                        $btn.html(action === 'activate' ? '<i class="fa fa-eye"></i>' : '<i class="fa fa-eye-slash"></i>');
                    }
                });
            }
        });
    });
</script>



<!-- Edit Product Modal -->
<div class="modal fade" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editProductModalLabel">Edit Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editProductForm">
                    <input type="hidden" id="edit_product_id" name="product_id">
                    <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
                    <div class="row">
                        <!-- First Row -->
                        <div class="col-md-12 mb-3">
                            <label for="edit_product_name" class="form-label">Product Name*</label>
                            <input type="text" class="form-control" id="edit_product_name" name="product_name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_sku" class="form-label">SKU*</label>
                            <input type="text" class="form-control" id="edit_sku" name="sku" required>
                        </div>

                        <!-- Second Row -->
                        <div class="col-md-6 mb-3">
                            <label for="edit_barcode" class="form-label">Barcode</label>
                            <input type="text" class="form-control" id="edit_barcode" name="barcode">
                        </div>

                        <!-- Fourth Row -->
                        <div class="col-md-6 mb-3">
                            <label for="edit_brand" class="form-label">Brand*</label>
                            <select id="edit_brand" name="brand" class="form-control" required>
                                <option value="">Select Brand</option>
                                <?php foreach ($brands as $brand): ?>
                                    <option value="<?= $brand->itemBrandId; ?>"><?= $brand->itemBrandName; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="edit_category" class="form-label">Category*</label>
                            <select id="edit_category" name="category" class="form-control" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= $category->itemCategoryId; ?>"><?= $category->itemCategoryName; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Fifth Row -->
                        <div class="col-md-6 mb-3">
                            <label for="edit_unit" class="form-label">Unit*</label>
                            <select class="form-control" id="edit_unit" name="unit" required>
                                <option value="">Select Unit</option>
                                <option value="p">Pieces</option>
                                <option value="ml">Milliliter (mL)</option>
                                <option value="g">Gram (g)</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_inventory_type" class="form-label">Inventory Type*</label>
                            <select class="form-control" id="edit_inventory_type" name="inventory_type" required>
                                <option value="sale">Sale</option>
                                <option value="internal">Internal Use</option>
                                <option value="both">Both</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_reorderLevel" class="form-label">Re Order Level</label>
                            <input type="number" class="form-control" id="edit_reorderLevel" name="reorderLevel" step="0.001" min="0">
                        </div>

                        <div class="col-md-6 mb-3 d-none">
                <label for="stock_quantity" class="form-label">Sale Price</label>
                <input type="number" class="form-control" id="edit_sale_price" name="edit_sale_price">
            </div>

                        <!-- Sixth Row - Full width -->
                        <div class="col-12 mb-3">
                            <label for="edit_description" class="form-label">Description</label>
                            <textarea class="form-control" id="edit_description" name="description" rows="2"></textarea>
                        </div>

                        <!-- Seventh Row - Checkboxes -->
                        <div class="col-md-6 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="edit_is_active" name="is_active">
                                <label class="form-check-label" for="edit_is_active">Active Product</label>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-12">
                        <div class="hstack gap-2 justify-content-end">
                            <button type="button" class="btn btn-primary" id="updateProductBtn">Update Product</button>
                            <button type="button" class="btn btn-danger fw-medium shadow-none" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </form>
                <div id="updateError" class="mt-3 text-danger errorDiv" style="display:none;"></div>
            </div>
        </div>
    </div>
</div>