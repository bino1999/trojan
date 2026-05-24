<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Bill</h4>

            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="<?php echo base_url(); ?>">Home</a></li>
                    <li class="breadcrumb-item active">Bill</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Left Column for Internal Bill Form -->
    <div class="col-lg-6">
        <div class="card mainCard">
            <div class="card-header">
                <div class="row mb-2">
                    <div class="col-md-4">
                        <select id="serviceProductBrand" class="form-select" onchange="loadServiceItemsByProductFilter()">
                            <option value="">Select Product Brand</option>
                            <?php foreach ($itemBrands as $brand) : ?>
                                <option value="<?= $brand->itemBrandId ?>"><?= $brand->itemBrandName ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <select id="serviceProductCategory" class="form-select" onchange="loadServiceItemsByProductFilter()">
                            <option value="">Select Product Category</option>
                            <?php foreach ($itemCategories as $brand) : ?>
                                <option value="<?= $brand->itemCategoryId ?>"><?= $brand->itemCategoryName ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <select id="serviceProductSupplier" class="form-select" onchange="loadServiceItemsByProductFilter()">
                            <option value="">Select Supplier</option>
                            <?php foreach ($suppliers as $supplier) : ?>
                                <option value="<?= $supplier->supplier_id ?>"><?= $supplier->name ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-12 text-center">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="clearFilters()">Clear Filters</button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12" id="showProductsDiv">
                        <!-- table responsive div-->
                        <div class="table-responsive">
                            <table id="productsTable" class="table table-sm table-striped">
                                <thead class="table-info">
                                    <tr>
                                        <th>Product</th>
                                        <th>SKU</th>
                                        <th>Supplier</th>
                                        <th>Brand</th>
                                        <th>Category</th>
                                        <th class="text-end">Price</th>
                                        <th>Unit</th>
                                        <th>Available</th>
                                        <th class="text-center" width="10%">Add</th>
                                    </tr>
                                </thead>
                                <tbody id="productsBody">
                                    <?php foreach ($products as $products) : ?>
                                        <tr class="products-row">
                                            <td class="f12"><?= $products->product_name ?></td>
                                            <td class="f12"><?= $products->sku ?></td>
                                            <td class="f12"><?= $products->supplier_name ?></td>
                                            <td class="f12"><?= $products->brand_name ?></td>
                                            <td class="f12"><?= $products->category_name ?></td>
                                            <td class="f12 text-end"><?= number_format($products->sale_price, 2) ?></td>
                                            <td class="f12"><?= $products->measurement_unit ?></td>
                                            <td class="f12 text-end"><?= $products->available_stock ?></td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-primary btn-sm" onclick="addProductsToJob(<?= $products->po_item_id  ?>, <?= $products->product_id  ?>, '<?= htmlspecialchars($products->product_name, ENT_QUOTES, 'UTF-8') ?>', '<?= $products->measurement_unit ?>', '<?= $products->discount_percent ?>', '<?= $products->discount_amount ?>')">
                                                    <i class="fa fa-plus"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div><!-- end table responsive div-->
                    </div>
                </div>
            </div><!-- end card body -->
        </div>
    </div>

    <!-- Right Column for Bill Summary -->
    <div class="col-lg-6">
        <div class="card mainCard">
            <div class="card-header">
                <h4 class="card-title ">Bill Summary</h4>
            </div>
            <div class="card-body" id="itemList">
                <!-- Bill items will be loaded here via AJAX -->
                <div class="text-center">
                    <p class="text-muted">No items added yet.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    loadBillItems();
    productsTableToDatatable();

    window.addEventListener('DOMContentLoaded', function() {
        const hamburgerBtn = document.getElementById('topnav-hamburger-icon');
        if (hamburgerBtn) {
           // hamburgerBtn.click();
        }
    });

    let selectedProductId = null;
    let selectedPoItemId = null;

    function addProductsToJob(po_item_id, productID, product_name, unit) {
        selectedProductId = productID;
        selectedPoItemId = po_item_id;

        // Reset quantity input to empty/0
        $('#quantityInput').val('');

        $('#quantityModal').modal('show');

        if (unit == 'p') {
            unit = 'piece';
        }

        // Populate product name and unit
        $('#productInfo').html(`
        <p><strong>Product Name:</strong> ${product_name}</p>
    `);

        $('#unitInput').val(unit);
        $('#unitInput').prop('disabled', true);
    }

    function confirmProductQuantity() {
        const quantity = $('#quantityInput').val();
        const unit = $('#unitInput').val();
        const jobId = 0;

        if (quantity <= 0) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Quantity must be greater than 0.'
            });
            return;
        }

        //check quantity is decimal and unit is piece
        if (unit == 'piece' && quantity % 1 != 0) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Quantity must be a whole number when unit is piece.'
            });
            return;
        }

        $.ajax({
            url: "<?php echo site_url('internalBill/addProductToBill') ?>", // AJAX URL
            type: "POST",
            dataType: 'json',
            data: {
                jobId: jobId,
                productID: selectedProductId,
                po_item_id: selectedPoItemId,
                quantity: quantity
            },
            success: function(res) {
                if (res && res.status === 'success') {
                    loadBillItems();
                    Toastify({
                        text: 'Product Added Successfully',
                        className: "toastify-success",
                        duration: 5000,
                        close: true,
                        gravity: "top",
                        position: "right"
                    }).showToast();
                    $('#quantityModal').modal('hide');
                } else {
                    const msg = (res && res.message) ? res.message : 'Failed to add product';
                    Toastify({
                        text: msg,
                        className: "toastify-error",
                        duration: 5000,
                        close: true,
                        gravity: "top",
                        position: "right"
                    }).showToast();
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText);
                Toastify({
                    text: 'Failed to add product',
                    className: "toastify-error",
                    duration: 5000,
                    close: true,
                    gravity: "top",
                    position: "right"
                }).showToast();
            },
            complete: function() {}
        });
    }

    function loadBillItems() {
        $.ajax({
            url: "<?php echo site_url('internalBill/loadBillItems') ?>",
            type: "POST",
            success: function(data) {
                $('#itemList').html(data);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText);
            }
        });
    }

    function deleteCartItem(index) {
        $.ajax({
            url: "<?= site_url('internalBill/deleteCartItem') ?>",
            method: "POST",
            data: {
                index: index
            },
            success: function() {
                Toastify({
                    text: 'Item deleted successfully',
                    className: "toastify-success",
                    duration: 5000,
                    close: true,
                    gravity: "top",
                    position: "right"
                }).showToast();
                loadBillItems();
            },
            error: function(xhr) {
                console.error("Delete failed:", xhr.responseText);
            }
        });
    }

    function completeBill() {
        const customerId = $('#customer').val();
        const paidAmount = parseFloat($('#paidAmount').val()) || 0;
        const paymentMethod = $('#paymentMethod').val();
        const cardOrChequeNo = $('#cardOrChequeNo').val();
        const chequeDate = $('#chequeDate').val();
        const bankName = $('#bankName').val();
        let note = $('#note').val();
        if ((paymentMethod || '').toLowerCase() === 'credit') {
            const cpName = ($('#creditPersonName').val() || '').trim();
            const cpPhone = ($('#creditPersonPhone').val() || '').trim();
            if (cpName || cpPhone) {
                note = (note ? note + ' | ' : '') + 'Credit to: ' + (cpName || '-') + (cpPhone ? ' (' + cpPhone + ')' : '');
            }
        }

        if (paymentMethod !== 'Credit' && paidAmount <= 0) {
            document.getElementById('errorDiv').innerHTML = '<div class="alert alert-danger">Please enter a valid paid amount.</div>';
            Toastify({
                text: 'Please enter a valid paid amount.',
                className: "toastify-error",
                duration: 5000,
                close: true,
                gravity: "top",
                position: "right"
            }).showToast();
            return;
        }

        $.ajax({
            url: "<?php echo site_url('internalBill/saveCompletedBill') ?>",
            type: "POST",
            dataType: "json",
            data: {
                customerId,
                paymentMethod,
                paidAmount,
                cardOrChequeNo,
                chequeDate,
                bankName,
                note
            },
            success: function(res) {
                if (res.status === 'success') {
                    const base_url = "<?= base_url(); ?>";
                    const receiptUrl = base_url + 'internal_bill/print_receipt/' + res.bill_id;
                    window.open(receiptUrl, '_blank');

                    // Then show the success dialog (after opening window)
                    Swal.fire({
                        icon: 'success',
                        title: 'Saved!',
                        text: 'Internal bill saved successfully. Receipt opened in new tab.',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Error', res.message || 'Something went wrong', 'error');
                    document.getElementById('errorDiv').innerHTML = '<div class="alert alert-danger">' + (res.message || 'Something went wrong') + '</div>';
                }
            },
            error: function(xhr, status, error) {
                console.error(error);
                Swal.fire('Error', 'An unexpected error occurred.', 'error');
            }
        });
    }

    function loadServiceItemsByProductFilter() {
        var categoryId = $('#serviceProductCategory').val();
        var brandId = $('#serviceProductBrand').val();
        var supplierId = $('#serviceProductSupplier').val();

        console.log('Filtering with:', { categoryId, brandId, supplierId });

        $.ajax({
            url: "<?php echo site_url('internalBill/loadServiceProductsFilterListByProduct') ?>",
            type: "POST",
            data: {
                categoryId: categoryId,
                brandId: brandId,
                supplierId: supplierId
            },
            success: function(data) {
                $('#showProductsDiv').html(data);
                // Reinitialize DataTable after content update
                productsTableToDatatable();
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText);
            }
        });
    }

    function clearFilters() {
        $('#serviceProductBrand').val('');
        $('#serviceProductCategory').val('');
        $('#serviceProductSupplier').val('');
        loadServiceItemsByProductFilter();
    }

    function productsTableToDatatable() {
        if ($.fn.DataTable.isDataTable('#productsTable')) {
            $('#productsTable').DataTable().destroy();
        }

        $('#productsTable').DataTable({
            "lengthMenu": [
                [10, 25, 50, -1],
                [10, 25, 50, "All"]
            ],
            "searching": true,
            "responsive": true
        });
    }


</script>

<!-- Modal for Quantity Input (Product) -->
<div class="modal fade" id="quantityModal" tabindex="-1" aria-labelledby="quantityModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered flip">
        <div class="modal-content" style="background-color:rgb(217, 232, 245);">
            <div class="modal-header">
                <h5 class="modal-title" id="quantityModalLabel">Enter Quantity for Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="productInfo"></div>

                <div class="row">
                    <div class="col-md-8 mt-2">
                        <label for="productInput">Quantity:</label>
                        <input type="number" class="form-control" id="quantityInput" min="1" value="1">
                    </div>
                    <div class="col-md-4 mt-2">
                        <label for="unitInput">Unit:</label>
                        <input type="text" class="form-control" id="unitInput" disabled>
                    </div>
                </div>


            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" id="confirmQuantity" onclick="confirmProductQuantity()">Confirm</button>
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>
