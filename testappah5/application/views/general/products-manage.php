<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Produts</h4>

            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="<?php echo base_url(); ?>">Home</a></li>
                    <li class="breadcrumb-item active">Produts</li>
                </ol>
            </div>

        </div>
    </div>
</div>



<div class="row">
    <div class="col-lg-12">

        <style>
            .mainCard {
                margin: -10px;
            }
        </style>

        <div class="card mainCard">
            <div class="card-header">

            <div class="row">
            <div class="col-md-3">
            <select id="filterBrand" name="filterBrand" class="form-select" required>
                    <option value="">Select Brand</option>
                    <?php foreach ($brands as $brand): ?>
                        <option value="<?= $brand->itemBrandId; ?>"><?= $brand->itemBrandName; ?></option>
                    <?php endforeach; ?>
                </select>
                </div>
                <div class="col-md-3">
                <select id="filterCategory" name="filterCategory" class="form-select" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= $category->itemCategoryId; ?>"><?= $category->itemCategoryName; ?></option>
                                <?php endforeach; ?>
                            </select>
                </div>
                <div class="col-md-4">
                    <button class="btn btn-primary" onclick="loadProducts();">Filter Produts</button>
                </div>

                <div class="col-md-2 flex-shrink-0">
                <button type="button" class="btn btn-success btn-label waves-effect waves-light" onclick="openProAddModal()">
                        <i class="fa fa-plus label-icon align-middle fs-16 me-2"></i> Create New Produt
                    </button>
                </div>
            </div>       

            </div><!-- end card header -->
            <div class="card-body" id="productList">

            </div>
        </div>
    </div>
    <!--end col-->
</div>




<!-- Add Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addProductModalLabel">Add Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
    <form id="createProductForm">
        <div class="row">
            <!-- First Row -->
            <div class="col-md-12 mb-3">
                <label for="product_name" class="form-label">Product Name*</label>
                <input type="text" class="form-control" id="product_name" name="product_name" required>
            </div>
            <div class="col-md-6 mb-3">
                <label for="sku" class="form-label">SKU*</label>
                <input type="text" class="form-control" id="sku" name="sku" required>
            </div>
            
            <!-- Second Row -->
            <div class="col-md-6 mb-3">
                <label for="barcode" class="form-label">Barcode</label>
                <input type="text" class="form-control" id="barcode" name="barcode">
            </div>
            
            <!-- Fourth Row -->
            <div class="col-md-6 mb-3">
                <label for="brand" class="form-label">Brand*</label>
                <select id="brand" name="brand" class="form-control" required>
                    <option value="">Select Brand</option>
                    <?php foreach ($brands as $brand): ?>
                        <option value="<?= $brand->itemBrandId; ?>"><?= $brand->itemBrandName; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label for="category" class="form-label">Category*</label>
                <select id="category" name="category" class="form-control" required>
                    <option value="">Select Category</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= $category->itemCategoryId; ?>"><?= $category->itemCategoryName; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- Fifth Row -->
            <div class="col-md-6 mb-3">
                <label for="unit" class="form-label">Unit*</label>
                <select class="form-control" id="unit" name="unit" required>
                    <option value="">Select Unit</option>
                    <option value="p">Pieces</option>
                    <option value="ml">Milliliter (mL)</option>
                    <option value="g">Gram (g)</option>
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label for="stock_quantity" class="form-label">Re Order Level</label>
                <input type="number" class="form-control" id="reorderLevel" name="reorderLevel" step="0.001" min="0" value="0">
            </div>

            <div class="col-md-6 mb-3">
                <label for="inventory_type" class="form-label">Inventory Type*</label>
                <select class="form-control" id="inventory_type" name="inventory_type" required>
                    <option value="sale" selected>Sale</option>
                    <option value="internal">Internal Use</option>
                    <option value="both">Both</option>
                </select>
            </div>

            <div class="col-md-6 mb-3 d-none">
                <label for="stock_quantity" class="form-label">Sale Price</label>
                <input type="number" class="form-control" id="salePrice" name="salePrice" value="0">
            </div>
            
            <!-- Sixth Row - Full width -->
            <div class="col-12 mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" rows="2"></textarea>
            </div>
            
            <!-- Seventh Row - Checkboxes -->
            <div class="col-md-6 mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" checked>
                    <label class="form-check-label" for="is_active">Active Product</label>
                </div>
            </div>
        </div>

        <div class="col-lg-12">
            <div class="hstack gap-2 justify-content-end">
                <button type="button" class="btn btn-success" id="saveProductBtn">Save Product</button>
                <button type="button" class="btn btn-danger fw-medium shadow-none" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </form>
    <div id="createError" class="mt-3 text-danger errorDiv" style="display:none;"></div>
</div>
        </div>
    </div>
</div>


<script type="text/javascript">
    $(document).ready(function() {

loadProducts();

$('#table1').DataTable({
    "lengthMenu": [
        [150, 250, 500, -1],
        [150, 250, 500, "All"]
    ],
    "searching": true,
    "responsive": true
});
});

function loadProducts(){
        let filterCategory = $('#filterCategory').val();
        let filterBrand = $('#filterBrand').val();
        
        $.ajax({
            url: "<?php echo site_url('products/loadProductsList') ?>",
            type: "POST",
            data: "filterCategory=" + filterCategory + "& filterBrand=" + filterBrand,
            success: function(data) {
                $('#productList').html(data);
            },
            error: function(jXHR, textStatus, errorThrown) {
                console.log(jXHR.responseText);
            }
        });
    }

    function openProAddModal() {
        $('#addProductModal select').select2({
            dropdownParent: $('#addProductModal')
        });

        $('#addProductModal').modal('show');
    }


    // Add Product Form submission
    $('#saveProductBtn').click(function (e) {
        e.preventDefault();
        $.ajax({
            url: "<?= base_url('products/productSave'); ?>",
            method: "POST",
            data: $('#createProductForm').serialize(),
            dataType: 'json',
            success: function (response) {
                if (response.status === 'success') {
                    $('#addProductModal').modal('hide');
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
                    $('#createError').html(response.message).show();
                }
            },
            error: function () {
                Swal.fire('Error!', 'Something went wrong. Please try again.', 'error');
            }
        });
    });


    // Update Product Form submission
    $('#updateProductBtn').click(function (e) {
        e.preventDefault();
        $.ajax({
            url: "<?= base_url('products/update'); ?>",
            method: "POST",
            data: $('#editProductForm').serialize(),
            dataType: 'json',
            success: function (response) {
                if (response.status === 'success') {
                    Swal.fire('Updated!', response.message, 'success').then(() => {
                        location.reload();
                    });
                } else {
                    $('#updateError').html(response.message).show();
                }
            },
            error: function () {
                Swal.fire('Error!', 'Something went wrong. Please try again.', 'error');
            }
        });
    });
    
</script>
