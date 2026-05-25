<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Quick Bill</h4>

            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="<?php echo base_url(); ?>">Home</a></li>
                    <li class="breadcrumb-item active">Quick Bill</li>
                </ol>
            </div>

        </div>
    </div>
</div>

<div class="row">
    <!-- Left Column for Quick Bill Form -->
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
                                    <?php foreach ($products as $product) : ?>
                                        <tr class="products-row">
                                            <td class="f12"><?= $product->product_name ?></td>
                                            <td class="f12"><?= $product->sku ?></td>
                                            <td class="f12"><?= $product->supplier_name ?></td>
                                            <td class="f12"><?= $product->brand_name ?></td>
                                            <td class="f12"><?= $product->category_name ?></td>
                                            <td class="f12 text-end"><?= number_format($product->sale_price, 2) ?></td>
                                            <td class="f12"><?= $product->measurement_unit ?></td>
                                            <td class="f12 text-end"><?= $product->available_stock ?></td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-primary btn-sm" onclick="addProductsToJob(<?= $product->po_item_id  ?>, <?= $product->product_id  ?>, '<?= htmlspecialchars($product->product_name, ENT_QUOTES, 'UTF-8') ?>', '<?= $product->measurement_unit ?>', '<?= $product->discount_percent ?>', '<?= $product->discount_amount ?>', <?= json_encode((float)$product->sale_price) ?>, <?= json_encode((float)$product->company_price) ?>)">
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
    let modalCompanyPrice = 0;
    let modalSalePrice = 0;

    function renderPriceInfo() {
        const dp = parseFloat($('#discountPercent').val() || 0);
        const da = parseFloat($('#discountAmount').val() || 0);
        let effective = modalSalePrice;
        if (dp > 0) {
            effective = modalSalePrice - (modalSalePrice * (dp/100));
        } else if (da > 0) {
            effective = modalSalePrice - da;
        }
        if (isNaN(effective)) { effective = modalSalePrice; }
        const belowCost = effective < modalCompanyPrice;
        const html = `
            <div class="mt-2" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px; padding:8px 10px;">
                <div style="display:flex; align-items:center; justify-content:space-between; gap:8px;">
                    <div style="font-size:0.92rem; color:#64748b;">Company Price</div>
                    <div style="font-size:1rem; font-weight:600; color:#0369a1;">Rs. ${modalCompanyPrice.toFixed(2)}</div>
                </div>
                <div style="display:flex; align-items:center; justify-content:space-between; gap:8px; margin-top:6px;">
                    <div style="font-size:0.92rem; color:#64748b;">Sell (after discount)</div>
                    <div style="font-size:1.05rem; font-weight:700; color:#065f46;">Rs. ${Math.max(effective,0).toFixed(2)}</div>
                </div>
                ${belowCost ? `<div style="margin-top:6px; font-size:0.9rem; color:#dc2626;">
                    <i class="fa fa-exclamation-triangle"></i> Warning: discounted sell price is below company price.
                </div>` : ''}
            </div>`;
        $('#priceInfo').html(html);
    }

    function addProductsToJob(po_item_id, productID, product_name, unit, discount_percent, discount_amount, sale_price, company_price) {
        selectedProductId = productID;
        selectedPoItemId = po_item_id;
        modalCompanyPrice = parseFloat(company_price || 0);
        modalSalePrice = parseFloat(sale_price || 0);
        // Initialize discount fields to zero for Quick Bill
        $('#discountPercent').val('0');
        $('#discountAmount').val('0');

        // Reset quantity input to empty/0
        $('#quantityInput').val('');

        $('#quantityModal').modal('show');

        if (unit == 'p') {
            unit = 'piece';
        }

        // Populate product name and unit + price info
        $('#productInfo').html(`
            <p><strong>Product Name:</strong> ${product_name}</p>
            <div id="priceInfo"></div>
        `);
        renderPriceInfo();

        $('#unitInput').val(unit);
        $('#unitInput').prop('disabled', true);
    }

    function confirmProductQuantity() {
        const quantity = $('#quantityInput').val();
        const unit = $('#unitInput').val();
        const discountPercent = $('#discountPercent').val();
        const discountAmount = $('#discountAmount').val();
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

        //validate discount percentage and amount , cannot have both
        if (discountPercent > 0 && discountAmount > 0) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'You cannot apply both discount percentage and amount at the same time.'
            });
            return;
        }

        // Additional validations: prevent free items
        const dpNum = parseFloat(discountPercent || 0);
        const daNum = parseFloat(discountAmount || 0);
        const qtyNum = parseFloat(quantity || 0);
        const lineTotalBefore = (isNaN(modalSalePrice) ? 0 : modalSalePrice) * (isNaN(qtyNum) ? 0 : qtyNum);

        if (dpNum >= 100) {
            Swal.fire({
                icon: 'error',
                title: 'Invalid Discount %',
                text: 'Discount percentage must be less than 100%.'
            });
            return;
        }

        if (daNum >= lineTotalBefore && lineTotalBefore > 0) {
            Swal.fire({
                icon: 'error',
                title: 'Invalid Discount Amount',
                text: 'Discount amount must be less than the line total (price × quantity).'
            });
            return;
        }

        // Update sell price info preview when confirming (recompute)
        // Note: purely visual, not stored
        try {
            const sp = parseFloat($('.products-row button.btn-primary.btn-sm').closest('tr').find('td.f12.text-end').text().replace(/,/g,'') || '0');
            let effective = sp;
            const dp = parseFloat(discountPercent || 0);
            const da = parseFloat(discountAmount || 0);
            if (dp > 0) { effective = sp - (sp * (dp/100)); }
            else if (da > 0) { effective = sp - da; }
            // no UI element here; info shown on open
            if (!isNaN(effective) && effective < (modalCompanyPrice || 0)) {
                Toastify({
                    text: 'Warning: discounted sell price is below company price.',
                    className: 'toastify-warning',
                    duration: 4000,
                    close: true,
                    gravity: 'top',
                    position: 'right'
                }).showToast();
            }
        } catch(e) {}

        $.ajax({
            url: "<?php echo site_url('quickBill/addProductToBill') ?>", // AJAX URL
            type: "POST",
            dataType: 'json',
            data: {
                jobId: jobId,
                productID: selectedProductId,
                discountPercent: discountPercent,
                discountAmount: discountAmount,
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
            url: "<?php echo site_url('quickBill/loadBillItems') ?>",
            type: "POST",
            cache: false,
            success: function(data) {
                $('#itemList').html(data);
                // Reinitialize bill state using fresh subtotal from DOM to avoid stale cached values
                try { reinitBillStateFromDom(); } catch (e) { console.warn('Reinit bill state failed', e); }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText);
            }
        });
    }

    function deleteCartItem(index) {
        $.ajax({
            url: "<?= site_url('quickBill/deleteCartItem') ?>",
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
            url: "<?php echo site_url('quickBill/saveCompletedBill'); ?>",
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
                    const receiptUrl = base_url + 'quick_bill/print_receipt/' + res.bill_id;
                    window.open(receiptUrl, '_blank');

                    // Then show the success dialog (after opening window)
                    Swal.fire({
                        icon: 'success',
                        title: 'Saved!',
                        text: 'Quick bill saved successfully. Receipt opened in new tab.',
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
            url: "<?php echo site_url('quickBill/loadServiceProductsFilterListByProduct') ?>",
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

    // Handle discount input to ensure only one type is used
    function handleDiscountInput(type) {
        if (type === 'percent') {
            const percentValue = parseFloat($('#discountPercent').val()) || 0;
            if (percentValue > 0) {
                $('#discountAmount').val('0');
            }
        } else if (type === 'amount') {
            const amountValue = parseFloat($('#discountAmount').val()) || 0;
            if (amountValue > 0) {
                $('#discountPercent').val('0');
            }
        }
        // Live update price info
        renderPriceInfo();
    }

    // Reinitialize totals and payment state after items HTML reload
    function reinitBillStateFromDom() {
        try {
            const subtotalField = document.getElementById('currentSubtotal');
            const subtotalVal = subtotalField ? parseFloat(subtotalField.value || '0') : 0;
            // Reset global state used by payment UI
            if (typeof window.paymentMethods === 'undefined') { window.paymentMethods = []; } else { window.paymentMethods.length = 0; }
            window.totalBillAmount = isNaN(subtotalVal) ? 0 : subtotalVal;
            window.billDiscountPercent = 0;
            window.billDiscountAmount = 0;
            // Update UI fields
            const grandDisp = document.getElementById('grandTotalDisplay');
            const totalBillDisp = document.getElementById('totalBillAmount');
            const totalPaidDisp = document.getElementById('totalPaidAmount');
            const remainDisp = document.getElementById('remainingBalance');
            if (grandDisp) grandDisp.textContent = window.totalBillAmount.toFixed(2);
            if (totalBillDisp) totalBillDisp.textContent = window.totalBillAmount.toFixed(2);
            if (totalPaidDisp) totalPaidDisp.textContent = '0.00';
            if (remainDisp) remainDisp.textContent = window.totalBillAmount.toFixed(2);
            // Clear payment methods UI list and hide section
            if (typeof updatePaymentMethodsList === 'function') { updatePaymentMethodsList(); }
            const mpSection = document.getElementById('multiplePaymentsSection');
            if (mpSection) { mpSection.style.display = 'none'; }
            // Reset payment inputs and labels
            const pmSel = document.getElementById('paymentMethod');
            if (pmSel) pmSel.value = '';
            // paidAmount is set by recalcGrandTotal() called below via bindBillDiscountInputs()
            const receiptNo = document.getElementById('cardOrChequeNo');
            if (receiptNo) receiptNo.value = '';
            const chequeDate = document.getElementById('chequeDate');
            if (chequeDate) chequeDate.value = '';
            const bankName = document.getElementById('bankName');
            if (bankName) bankName.value = '';
            const creditName = document.getElementById('creditPersonName');
            if (creditName) creditName.value = '';
            const creditPhone = document.getElementById('creditPersonPhone');
            if (creditPhone) creditPhone.value = '';
            const creditDueDate = document.getElementById('creditDueDate');
            if (creditDueDate) creditDueDate.value = '';
            const addlFields = document.getElementById('additionalPaymentFields');
            if (addlFields) addlFields.style.display = 'none';
            const creditFields = document.getElementById('creditFields');
            if (creditFields) creditFields.style.display = 'none';
            const selPayTxt = document.getElementById('selectedPaymentText');
            if (selPayTxt) selPayTxt.textContent = 'Select Payment Method';
            const statusBadge = document.getElementById('paymentStatus');
            if (statusBadge) { statusBadge.textContent = 'Incomplete'; statusBadge.className = 'badge bg-warning ms-2'; }
            if (typeof updatePaymentStatus === 'function') { updatePaymentStatus(); }
            // Rebind discount inputs to recalc using new subtotal
            if (typeof bindBillDiscountInputs === 'function') { bindBillDiscountInputs(); }
        } catch (e) {
            console.warn('Failed to reinit bill state', e);
        }
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

                <div class="row">
                    <!-- Discount Percentage -->
                    <div class="col-md-4 mt-2">
                        <label for="discountPercent">Discount % :</label>
                        <input type="number" class="form-control" id="discountPercent" min="0" max="100" step="0.01" placeholder="0.00" value="0" oninput="handleDiscountInput('percent')">
                    </div>

                    <!-- Discount Amount -->
                    <div class="col-md-4 mt-2">
                        <label for="discountAmount">Discount Amount :</label>
                        <input type="number" class="form-control" id="discountAmount" min="0" step="0.01" placeholder="0.00" value="0" oninput="handleDiscountInput('amount')">
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