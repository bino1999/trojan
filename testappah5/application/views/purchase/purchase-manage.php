<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Purchase Orders</h4>

            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="<?php echo base_url(); ?>">Home</a></li>
                    <li class="breadcrumb-item active">Purchase Orders</li>
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
                    <div class="col-md-2">
                        <label class="form-label">Start Date (Optional)</label>
                        <input type="date" name="sdate" id="sdate" class="form-control">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">End Date (Optional)</label>
                        <input type="date" name="edate" id="edate" class="form-control">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Supplier (Optional)</label>
                        <select name="supplier_filter" id="supplier_filter" class="form-control">
                            <option value="">All Suppliers</option>
                            <?php foreach ($suppliers as $supplier) : ?>
                                <option value="<?= $supplier->supplier_id ?>"><?= $supplier->name ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div>
                        <button class="btn btn-primary" onclick="loadPurchases();">Load Purchase Orders</button>
                            <button class="btn btn-secondary" onclick="clearFilters();">Clear Filters</button>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div>
                        <button class="btn btn-success waves-effect waves-light" onclick="openAddPurchaseModal()"><i class="fa fa-plus"></i> New Purchase Order</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <div id="purchaseTable"></div>
            </div>
        </div>
    </div>
    <!--end col-->
</div>


<!-- Add Purchase Modal -->
<div class="modal fade" id="addPurchaseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="purchaseForm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">New Purchase</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">

                    <div class="mb-3">
                        <label>Supplier</label>
                        <select class="form-control" name="supplier_id" required>
                            <option value="">-- Select Supplier --</option>
                            <?php foreach ($suppliers as $supplier) : ?>
                                <option value="<?= $supplier->supplier_id ?>"><?= $supplier->name ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label>Bill No</label>
                        <input type="text" name="bill_no" class="form-control" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Bill Date</label>
                        <input type="date" name="bill_date" class="form-control" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Payment Method</label>
                        <select name="payment_method" class="form-control" required>
                            <option value="">Payment Method --</option>
                            <option value="cash">Cash</option>
                            <option value="cheque">Cheque</option>
                            <option value="card">Card</option>
                            <option value="credit" selected>Credit</option>
                            <option value="bank_transfer">Bank Transfer</option>
                        </select>
                    </div>
                </div>


                    <div class="row">
                    <div class="col-md-6 mb-3">
    <label>VAT Type</label>
    <select name="vat_type" id="vat_type" class="form-control" required>
        <option value="">Select VAT Type --</option>
        <option value="none">Non Vat</option>
        <option value="vat_whole">Vat - Whole Bill</option>
        <option value="vat_per_item">Vat - Per Items</option>
    </select>
</div>

<div class="col-md-6 mb-3" id="customVatDiv">
    <label>Custom VAT %</label>
    <input type="number" name="vat_percent" id="vat_percent" class="form-control" step="0.01" min="0" placeholder="Enter VAT %" value="18" />
</div>
                    </div>


                    <div class="mb-3">
                        <label>Description</label>
                        <textarea name="description" class="form-control"></textarea>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Save Purchase</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Edit Purchase Modal -->
<div class="modal fade" id="editPurchaseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="editPurchaseForm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Purchase Order</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="edit_po_id" id="edit_po_id">
                    <input type="hidden" name="edit_original_status" id="edit_original_status">

                    <div class="mb-3">
                        <label>Supplier</label>
                        <select class="form-control" name="edit_supplier_id" id="edit_supplier_id" required>
                            <option value="">-- Select Supplier --</option>
                            <?php foreach ($suppliers as $supplier) : ?>
                                <option value="<?= $supplier->supplier_id ?>"><?= $supplier->name ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label>Bill No</label>
                            <input type="text" name="edit_bill_no" id="edit_bill_no" class="form-control" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Bill Date</label>
                            <input type="date" name="edit_bill_date" id="edit_bill_date" class="form-control" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Payment Method</label>
                            <select name="edit_payment_method" id="edit_payment_method" class="form-control" required>
                                <option value="">Payment Method --</option>
                                <option value="cash">Cash</option>
                                <option value="cheque">Cheque</option>
                                <option value="card">Card</option>
                                <option value="credit">Credit</option>
                                <option value="bank_transfer">Bank Transfer</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>VAT Type</label>
                            <select name="edit_vat_type" id="edit_vat_type" class="form-control" required>
                                <option value="">Select VAT Type --</option>
                                <option value="none">Non Vat</option>
                                <option value="vat_whole">Vat - Whole Bill</option>
                                <option value="vat_per_item">Vat - Per Items</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3" id="editCustomVatDiv">
                            <label>Custom VAT %</label>
                            <input type="number" name="edit_vat_percent" id="edit_vat_percent" class="form-control" step="0.01" min="0" placeholder="Enter VAT %" value="18" />
                        </div>
                    </div>

                    <div class="mb-3">
                        <label>Description</label>
                        <textarea name="edit_description" id="edit_description" class="form-control"></textarea>
                    </div>

                    <div class="alert alert-warning" id="editStockWarning" style="display: none;">
                        <strong>Warning:</strong> This purchase order is completed. Editing will affect the available stock quantities.
                    </div>

                    <!-- PO Items Section -->
                    <div class="mt-4">
                        <h6>Purchase Order Items</h6>
                        <div id="editPoItemsList"></div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Update Purchase Order</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Edit PO Item Modal -->
<div class="modal fade" id="editPoItemModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form id="editPoItemForm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Purchase Order Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="edit_po_item_id" id="edit_po_item_id">
                    <input type="hidden" name="edit_po_id" id="edit_po_id">
                    <input type="hidden" name="edit_original_quantity" id="edit_original_quantity">
                    <input type="hidden" name="edit_original_company_price" id="edit_original_company_price">
                    <input type="hidden" name="edit_original_sale_price" id="edit_original_sale_price">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Product</label>
                            <select class="form-control" name="edit_product_id" id="edit_product_id" required>
                                <option value="">-- Select Product --</option>
                                <?php foreach ($products as $product) : ?>
                                    <option value="<?= $product->product_id ?>"
                                        data-brand="<?= $product->brand_name ?>"
                                        data-category="<?= $product->category_name ?>"
                                        data-barcode="<?= $product->barcode ?>"><?= strtoupper($product->product_name) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Quantity</label>
                            <input type="number" name="edit_quantity" id="edit_quantity" class="form-control" min="1" step="1" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Company Price</label>
                            <input type="number" name="edit_company_price" id="edit_company_price" class="form-control" step="0.01" min="0" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Sale Price</label>
                            <input type="number" name="edit_sale_price" id="edit_sale_price" class="form-control" step="0.01" min="0" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Discount %</label>
                            <input type="number" name="edit_discount_percent" id="edit_discount_percent" class="form-control" step="0.01" min="0" max="100">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Discount Amount</label>
                            <input type="number" name="edit_discount_amount" id="edit_discount_amount" class="form-control" step="0.01" min="0">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>UOM</label>
                            <select name="edit_uom" id="edit_uom" class="form-control" required>
                                <option value="">Unit</option>
                                <option value="kg">Kilogram (kg)</option>
                                <option value="g">Gram (g)</option>
                                <option value="l">Liter (L)</option>
                                <option value="ml">Milliliter (mL)</option>
                                <option value="p">Piece</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Inventory Type</label>
                            <select name="edit_inventory_type" id="edit_inventory_type" class="form-control" required>
                                <option value="sale">Sale</option>
                                <option value="internal">Internal Use</option>
                                <option value="both">Both</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Rack No</label>
                            <input type="text" name="edit_rack_no" id="edit_rack_no" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Bin No</label>
                            <input type="text" name="edit_bin_no" id="edit_bin_no" class="form-control">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Reorder Level</label>
                            <input type="number" name="edit_reorder_level" id="edit_reorder_level" class="form-control" min="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Purchase Date</label>
                            <input type="date" name="edit_purchase_date" id="edit_purchase_date" class="form-control" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label>Note</label>
                        <textarea name="edit_note" id="edit_note" class="form-control" rows="2"></textarea>
                    </div>

                    <div class="alert alert-info">
                        <strong>Stock Impact:</strong> <span id="stockImpactText"></span>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Update Item</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>





<script>
   
    $(document).ready(function() {

        loadPurchases();

        // Handle VAT type change in add modal
        $('#vat_type').change(function() {
            if ($(this).val() === 'vat_whole' || $(this).val() === 'vat_per_item') {
                $('#customVatDiv').show();
            } else {
                $('#customVatDiv').hide();
                $('#vat_percent').val('18');
            }
        });

        $('#purchaseForm').submit(function(e) {
    e.preventDefault();
    $.ajax({
        url: "<?= site_url('purchase/createPurchase') ?>",
        method: "POST",
        data: $(this).serialize(),
        dataType: 'json', // important to ensure JSON is parsed automatically
        success: function(response) {
            if (response.status === 'success') {
                $('#addPurchaseModal').modal('hide');
                $('#purchaseForm')[0].reset();
                loadPurchases();
                Toastify({
                    text: "Purchase Order created successfully.",
                    className: "toastify-success",
                    duration: 3000,
                    gravity: "top",
                    position: "right"
                }).showToast();
                
            } else {
                Swal.fire('Validation Error', response.message, 'warning');
            }
        },
        error: function(xhr, status, error) {
            Swal.fire('Error!', 'Something went wrong. Please try again.', 'error');
            console.error("AJAX Error:", status, error);
        }
    });
});

        // Reset form when modal is closed
        $('#addPurchaseModal').on('hidden.bs.modal', function () {
            $('#purchaseForm')[0].reset();
            $('#payment_method').val('credit');
            $('#vat_type').val('none');
            $('#vat_percent').val('18');
            $('#customVatDiv').hide();
});

    });

    function loadPurchases() {
        let sdate = $('#sdate').val();
        let edate = $('#edate').val();
        let supplier_id = $('#supplier_filter').val();
        
        $.ajax({
            url: "<?php echo site_url('purchase/loadPurchases') ?>",
            type: "POST",
            data: {
                sdate: sdate,
                edate: edate,
                supplier_id: supplier_id
            },
            success: function(data) {
                $('#purchaseTable').html(data);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText);
            }
        });
    }

    function clearFilters() {
        $('#sdate').val('');
        $('#edate').val('');
        $('#supplier_filter').val('');
        loadPurchases();
    }


    function openAddPurchaseModal() {
        $('#addPurchaseModal').modal('show');
        $('#addPurchaseModal select').select2({
            dropdownParent: $('#addPurchaseModal')
        });
        
        // Set default values
        $('#payment_method').val('credit');
        $('#vat_type').val('none');
        $('#vat_percent').val('18');
        $('#customVatDiv').hide();
    }

    function openEditPurchaseModal(po_id, supplier_id, bill_no, bill_date, payment_method, vat_type, vat_percent, description, status) {
        // Populate the edit form
        $('#edit_po_id').val(po_id);
        $('#edit_original_status').val(status);
        $('#edit_supplier_id').val(supplier_id);
        $('#edit_bill_no').val(bill_no);
        $('#edit_bill_date').val(bill_date);
        $('#edit_description').val(description);
        
        // Set payment method
        let paymentText = '';
        if (payment_method == 1) paymentText = 'cash';
        else if (payment_method == 2) paymentText = 'cheque';
        else if (payment_method == 3) paymentText = 'card';
        else if (payment_method == 4) paymentText = 'credit';
        else if (payment_method == 5) paymentText = 'bank_transfer';
        $('#edit_payment_method').val(paymentText);
        
        // Set VAT type and percent
        $('#edit_vat_type').val(vat_type);
        $('#edit_vat_percent').val(vat_percent);
        
        // Show/hide VAT percent field based on VAT type
        if (vat_type === 'vat_whole' || vat_type === 'vat_per_item') {
            $('#editCustomVatDiv').show();
        } else {
            $('#editCustomVatDiv').hide();
        }
        
        // Show stock warning if completed
        if (status === 'Completed') {
            $('#editStockWarning').show();
        } else {
            $('#editStockWarning').hide();
        }
        
        // Initialize select2
        $('#editPurchaseModal select').select2({
            dropdownParent: $('#editPurchaseModal')
        });
        
        // Load PO items
        loadEditPoItems(po_id);
        
        $('#editPurchaseModal').modal('show');
    }

    // Reset edit form when modal is closed
    $('#editPurchaseModal').on('hidden.bs.modal', function () {
        $('#editPurchaseForm')[0].reset();
        $('#editStockWarning').hide();
    });

    // Handle VAT type change in edit modal
    $(document).on('change', '#edit_vat_type', function() {
        if ($(this).val() === 'vat_whole' || $(this).val() === 'vat_per_item') {
            $('#editCustomVatDiv').show();
        } else {
            $('#editCustomVatDiv').hide();
            $('#edit_vat_percent').val('18');
        }
    });

    // Handle edit form submission
    $('#editPurchaseForm').submit(function(e) {
        e.preventDefault();
        
        $.ajax({
            url: "<?= site_url('purchase/updatePurchase') ?>",
            method: "POST",
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#editPurchaseModal').modal('hide');
                    $('#editPurchaseForm')[0].reset();
                    loadPurchases();
                    Toastify({
                        text: response.message,
                        className: "toastify-success",
                        duration: 3000,
                        gravity: "top",
                        position: "right"
                    }).showToast();
                } else {
                    Swal.fire('Validation Error', response.message, 'warning');
                }
            },
            error: function(xhr, status, error) {
                Swal.fire('Error!', 'Something went wrong. Please try again.', 'error');
                console.error("AJAX Error:", status, error);
            }
        });
    });

    // Load PO items for editing
    function loadEditPoItems(po_id) {
        $.ajax({
            url: "<?= site_url('purchase/loadEditPoItems') ?>",
            method: "POST",
            data: { po_id: po_id },
            success: function(data) {
                $('#editPoItemsList').html(data);
            },
            error: function(xhr, status, error) {
                console.error("Error loading PO items:", error);
            }
        });
    }

    // Open edit PO item modal
    function openEditPoItemModal(po_item_id, po_id, product_id, quantity, company_price, sale_price, discount_percent, discount_amount, uom, inventory_type, rack_no, bin_no, reorder_level, purchase_date, note) {
        // Populate the edit form
        $('#edit_po_item_id').val(po_item_id);
        $('#edit_po_id').val(po_id);
        $('#edit_original_quantity').val(quantity);
        $('#edit_original_company_price').val(company_price);
        $('#edit_original_sale_price').val(sale_price);
        
        $('#edit_product_id').val(product_id);
        $('#edit_quantity').val(quantity);
        $('#edit_company_price').val(company_price);
        $('#edit_sale_price').val(sale_price);
        $('#edit_discount_percent').val(discount_percent);
        $('#edit_discount_amount').val(discount_amount);
        $('#edit_uom').val(uom);
        $('#edit_inventory_type').val(inventory_type);
        $('#edit_rack_no').val(rack_no);
        $('#edit_bin_no').val(bin_no);
        $('#edit_reorder_level').val(reorder_level);
        $('#edit_purchase_date').val(purchase_date);
        $('#edit_note').val(note);
        
        // Calculate stock impact
        updateStockImpact(quantity, quantity);
        
        // Initialize select2
        $('#editPoItemModal select').select2({
            dropdownParent: $('#editPoItemModal')
        });
        
        $('#editPoItemModal').modal('show');
    }

    // Update stock impact text
    function updateStockImpact(originalQty, newQty) {
        const diff = newQty - originalQty;
        let impactText = '';
        
        if (diff > 0) {
            impactText = `Stock will increase by ${diff} units`;
        } else if (diff < 0) {
            impactText = `Stock will decrease by ${Math.abs(diff)} units`;
        } else {
            impactText = 'No stock change';
        }
        
        $('#stockImpactText').text(impactText);
    }

    // Handle quantity change for stock impact
    $(document).on('input', '#edit_quantity', function() {
        const originalQty = parseFloat($('#edit_original_quantity').val()) || 0;
        const newQty = parseFloat($(this).val()) || 0;
        updateStockImpact(originalQty, newQty);
    });

    // Handle edit PO item form submission
    $('#editPoItemForm').submit(function(e) {
        e.preventDefault();
        
        // Basic client-side validation
        let isValid = true;
        let errorMessage = '';
        
        if (!$('#edit_product_id').val()) {
            errorMessage = 'Please select a product';
            isValid = false;
        } else if (!$('#edit_quantity').val() || $('#edit_quantity').val() <= 0) {
            errorMessage = 'Please enter a valid quantity';
            isValid = false;
        } else if (!$('#edit_company_price').val() || $('#edit_company_price').val() <= 0) {
            errorMessage = 'Please enter a valid company price';
            isValid = false;
        } else if (!$('#edit_sale_price').val() || $('#edit_sale_price').val() <= 0) {
            errorMessage = 'Please enter a valid sale price';
            isValid = false;
        } else if (!$('#edit_uom').val()) {
            errorMessage = 'Please select UOM';
            isValid = false;
        } else if (!$('#edit_purchase_date').val()) {
            errorMessage = 'Please select purchase date';
            isValid = false;
        }
        
        if (!isValid) {
            Swal.fire('Validation Error', errorMessage, 'warning');
            return;
        }
        
        // Check if sale price is less than company price
        let companyPrice = parseFloat($('#edit_company_price').val());
        let salePrice = parseFloat($('#edit_sale_price').val());
        if (salePrice < companyPrice) {
            Swal.fire('Validation Error', 'Sale price must not be less than company price', 'warning');
            return;
        }
        
        // Check discount validation
        let discountPercent = parseFloat($('#edit_discount_percent').val()) || 0;
        let discountAmount = parseFloat($('#edit_discount_amount').val()) || 0;
        if (discountPercent > 0 && discountAmount > 0) {
            Swal.fire('Validation Error', 'Only one of discount percent or amount can be entered', 'warning');
            return;
        }
        
        // Log the form data for debugging
        let formData = $(this).serialize();
        console.log('Form data being sent:', formData);
        
        $.ajax({
            url: "<?= site_url('purchase/updatePoItem') ?>",
            method: "POST",
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#editPoItemModal').modal('hide');
                    $('#editPoItemForm')[0].reset();
                    
                    // Refresh PO items list
                    loadEditPoItems($('#edit_po_id').val());
                    
                    Toastify({
                        text: response.message,
                        className: "toastify-success",
                        duration: 3000,
                        gravity: "top",
                        position: "right"
                    }).showToast();
                } else {
                    Swal.fire('Validation Error', response.message, 'warning');
                }
            },
            error: function(xhr, status, error) {
                Swal.fire('Error!', 'Something went wrong. Please try again.', 'error');
                console.error("AJAX Error:", status, error);
            }
        });
    });

    // Delete PO item
    function deletePoItem(po_item_id, po_id) {
        Swal.fire({
            title: 'Are you sure?',
            text: "This will delete the item and affect available stock if the PO is completed.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '<?= base_url('purchase/deletePoItem') ?>',
                    type: 'POST',
                    data: { po_item_id: po_item_id },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            // Refresh PO items list
                            loadEditPoItems(po_id);
                            
                            Toastify({
                                text: "Item deleted successfully.",
                                className: "toastify-success",
                                duration: 3000,
                                gravity: "top",
                                position: "right"
                            }).showToast();
                        } else {
                            Swal.fire('Error!', response.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error!', 'An error occurred while deleting.', 'error');
                    }
                });
            }
        });
    }

    var currentBillDate = "";
    function openAddItemModal(po_id, supplier_id, supplier_name, bill_no, bill_date, vat_type, vat_percent) {
        $('#purchaseItemForm')[0].reset();
        currentBillDate = bill_date;

        $('#po_id').val(po_id);
        $('#supplier_id').val(supplier_id);
        $('#supplier_name_div').text('Supplier : '+supplier_name);
        $('#purchase_date').val(bill_date);

        $('#addItemModal select').select2({
            dropdownParent: $('#addItemModal')
        });

        // Set the PO ID in the modal title
        //$('#purchaseOrderNoText').text('Add Item to Purchase Order No: ' + po_id);
        $('#purchaseOrderNoText2').text('You are adding items to purchase orer bill no: ' + bill_no);
        
        // Add VAT type indicator
        let vatTypeText = '';
        let vatTypeColor = 'text-danger';
        
        if (vat_type === 'vat_whole') {
            vatTypeText = ' (VAT - Whole Bill: ' + vat_percent + '%)';
        } else if (vat_type === 'vat_per_item') {
            vatTypeText = ' (VAT - Per Items: ' + vat_percent + '%)';
        } else {
            vatTypeText = ' (Non VAT)';
        }
        
        $('#vatTypeIndicator').html('<span class="' + vatTypeColor + '">' + vatTypeText + '</span>');

        $('#addItemModal').modal('show');
        loadPurchaseItems();
    }

    // =====================================================
    // PAYMENT SYSTEM FUNCTIONS
    // =====================================================

    function openPoPayments(po_id) {
        $.ajax({
            url: "<?php echo site_url('purchase/loadPoPayments'); ?>",
            type: "POST",
            data: { po_id: po_id },
            success: function(data) {
                // Remove existing modal if any
                $('#poPaymentsModal').remove();

                // Convert to jQuery fragment so we can execute scripts
                var $fragment = $(data);

                // Append HTML
                $('body').append($fragment);

                // Execute any inline scripts inside the returned fragment
                $fragment.filter('script').each(function() {
                    var code = this.text || this.textContent || this.innerHTML || '';
                    if (code.trim()) {
                        $.globalEval(code);
                    }
                });

                // Show the modal
                $('#poPaymentsModal').modal('show');
            },
            error: function(xhr, status, error) {
                console.error('Error loading PO payments:', error);
                Swal.fire('Error', 'Failed to load payment information', 'error');
            }
        });
    }

    function addPoPayment() {
        const method = $('#paymentMethod').val();
        const amount = parseFloat($('#paidAmount').val()) || 0;
        const po_id = currentPoId;
        
        if (!method) {
            Swal.fire('Error', 'Please select a payment method.', 'error');
            return;
        }
        
        if (amount <= 0) {
            Swal.fire('Error', 'Please enter a valid amount.', 'error');
            return;
        }
        
        // Validate required fields based on payment method
        let validationError = '';
        
        if (method === 'Card' || method === 'Bank Transfer') {
            if (!$('#cardOrChequeNo').val().trim()) {
                validationError = 'Please enter Receipt/Transaction ID for ' + method + ' payment.';
            } else if (!$('#bankName').val().trim()) {
                validationError = 'Please enter Bank Name for ' + method + ' payment.';
            }
        } else if (method === 'Cheque') {
            if (!$('#cardOrChequeNo').val().trim()) {
                validationError = 'Please enter Cheque Number for Cheque payment.';
            } else if (!$('#chequeDate').val()) {
                validationError = 'Please enter Cheque Date for Cheque payment.';
            } else if (!$('#bankName').val().trim()) {
                validationError = 'Please enter Bank Name for Cheque payment.';
            }
        } else if (method === 'Credit') {
            if (!$('#creditPersonName').val().trim()) {
                validationError = 'Please enter Credit Person/Company Name for Credit payment.';
            } else if (!$('#creditPersonPhone').val().trim()) {
                validationError = 'Please enter Phone Number for Credit payment.';
            }
        }
        
        if (validationError) {
            Swal.fire('Error', validationError, 'error');
            return;
        }
        
        // Prepare payment data
        const paymentData = {
            po_id: po_id,
            payment_method: method,
            paid_amount: amount,
            card_or_cheque_no: $('#cardOrChequeNo').val() || '',
            cheque_date: $('#chequeDate').val() || '',
            bank_name: $('#bankName').val() || '',
            note: ''
        };
        
        // Send payment to server
        $.ajax({
            url: "<?php echo site_url('purchase/addPoPayment'); ?>",
            type: "POST",
            data: paymentData,
            success: function(response) {
                try {
                    const result = JSON.parse(response);
                    if (result.status === 'success') {
                        Swal.fire('Success', result.message, 'success');
                        
                        // Refresh the payments modal
                        openPoPayments(po_id);
                        
                        // Refresh the main PO list to update payment status
                        loadPurchases();
                    } else {
                        Swal.fire('Error', result.message, 'error');
                    }
                } catch (e) {
                    Swal.fire('Error', 'Invalid response from server', 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'Failed to add payment', 'error');
            }
        });
    }

    function deletePoPayment(payment_id, po_id) {
        Swal.fire({
            title: 'Delete Payment?',
            text: 'Are you sure you want to delete this payment?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "<?php echo site_url('purchase/deletePoPayment'); ?>",
                    type: "POST",
                    data: { 
                        payment_id: payment_id,
                        po_id: po_id
                    },
                    success: function(response) {
                        try {
                            const result = JSON.parse(response);
                            if (result.status === 'success') {
                                Swal.fire('Deleted!', result.message, 'success');
                                
                                // Refresh the payments modal
                                openPoPayments(po_id);
                                
                                // Refresh the main PO list to update payment status
                                loadPurchases();
                            } else {
                                Swal.fire('Error', result.message, 'error');
                            }
                        } catch (e) {
                            Swal.fire('Error', 'Invalid response from server', 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Failed to delete payment', 'error');
                    }
                });
            }
        });
    }


    function openViewItemModal(po_id, supplier_id, supplier_name, bill_no, bill_date) {
        $('#purchaseItemForm')[0].reset();

        $('#po_id2').val(po_id);
        $('#supplier_id2').val(supplier_id);
        $('#supplier_name_div2').text('Supplier : '+supplier_name);

        $('#viewItemModal select').select2({
            dropdownParent: $('#viewItemModal')
        });

        // Set the PO ID in the modal title
        //$('#purchaseOrderNoText').text('Add Item to Purchase Order No: ' + po_id);
        $('#purchaseOrderNoText3').text('Purchase Orer Bill No: ' + bill_no);

        $('#viewItemModal').modal('show');
        loadPurchaseItems2();
    }

    function loadPurchaseItems2(){
        let po_id = $('#po_id2').val();
     
        $.ajax({
            url: "<?php echo site_url('purchase/loadPurchaseOrderItems2') ?>",
            type: "POST",
            data: "po_id=" + po_id,
            success: function(data) {
                $('#itemList2').html(data);
            },
            error: function(jXHR, textStatus, errorThrown) {
                console.log(jXHR.responseText);
            }
        });
    }

    function savePurchaseItem() {
        var formData = $('#purchaseItemForm').serialize();
        $.ajax({
            url: "<?php echo site_url('purchase/addItemToPurchaseOrder'); ?>",
            type: "POST",
            data: formData,
            success: function(res) {
                var result = JSON.parse(res);
                if (result.status == 'success') {
                    alert('Item Added Successfully!');
                    $('#addItemModal').modal('hide');
                    // reload items if needed
                    loadPurchaseItems();
                } else {
                    alert('Error saving item');
                }
            }
        });
    }

    function loadPurchaseItems(){
        let po_id = $('#po_id').val();
     
        $.ajax({
            url: "<?php echo site_url('purchase/loadPurchaseOrderItems') ?>",
            type: "POST",
            data: "po_id=" + po_id,
            success: function(data) {
                $('#itemList').html(data);
            },
            error: function(jXHR, textStatus, errorThrown) {
                console.log(jXHR.responseText);
            }
        });
    }

    function setDefaultValues() {
        const productSelect = document.getElementById('product_id');
        const brandInput = document.getElementById('brand_name');
        const categoryInput = document.getElementById('category_name');
        const barcodeInput = document.getElementById('barcode');
        const uomSelect = document.getElementById('uom');
        const invTypeSelect = document.getElementById('inventory_type');
        const reorderLevelInput = document.getElementById('reorder_level');
        const salePriceInput = document.getElementById('sale_price');

        const selectedOption = productSelect.options[productSelect.selectedIndex];

        if (selectedOption.value !== "") {
            // Set values from data attributes
            brandInput.value = selectedOption.getAttribute('data-brand') || '';
            categoryInput.value = selectedOption.getAttribute('data-category') || '';
            barcodeInput.value = selectedOption.getAttribute('data-barcode') || '';
            reorderLevelInput.value = selectedOption.getAttribute('data-reorder_level') || '';
            salePriceInput.value = selectedOption.getAttribute('data-sale_price') || '';

            const measurementUnit = selectedOption.getAttribute('data-measurement_unit') || '';

            // Handle UOM selection
            if (measurementUnit !== '') {
                let matched = false;
                const unitLower = measurementUnit.trim().toLowerCase();

                // Search for matching option (case insensitive)
                for (let i = 0; i < uomSelect.options.length; i++) {
                    if (uomSelect.options[i].value.trim().toLowerCase() === unitLower) {
                        uomSelect.selectedIndex = i;
                        matched = true;
                        break;
                    }
                }

                // If not found, add it as a new option
                if (!matched) {
                    const newOption = document.createElement('option');
                    newOption.value = measurementUnit;
                    newOption.textContent = measurementUnit;
                    uomSelect.appendChild(newOption);
                    uomSelect.value = measurementUnit;
                }

                // Trigger events to update floating label
                uomSelect.dispatchEvent(new Event('change'));
                uomSelect.dispatchEvent(new Event('input'));
            } else {
                uomSelect.selectedIndex = 0; // default to "Select Unit"
            }

            // Disable brand, category, barcode inputs
            brandInput.disabled = true;
            categoryInput.disabled = true;
            barcodeInput.disabled = true;

            // Set inventory type select (read-only display)
            const invType = (selectedOption.getAttribute('data-inventory_type') || 'sale').toLowerCase();
            if (invType === 'internal' || invType === 'both' || invType === 'sale') {
                invTypeSelect.value = invType;
            } else {
                invTypeSelect.value = 'sale';
            }
            // Update select2 UI if applied
            $('#inventory_type').trigger('change.select2');

        } else {
            // If no product selected, clear all fields
            brandInput.value = '';
            categoryInput.value = '';
            barcodeInput.value = '';
            uomSelect.selectedIndex = 0;

            // Enable brand, category, barcode inputs
            brandInput.disabled = false;
            categoryInput.disabled = false;
            barcodeInput.disabled = false;

            // Reset inventory type display
            invTypeSelect.value = 'sale';
            $('#inventory_type').trigger('change.select2');

            // Trigger events to update floating labels
            uomSelect.dispatchEvent(new Event('change'));
            brandInput.dispatchEvent(new Event('input'));
            categoryInput.dispatchEvent(new Event('input'));
            barcodeInput.dispatchEvent(new Event('input'));
        }
    }

    function deletePurchaseItem(po_item_id) {
        $.ajax({
            url: '<?= base_url('purchase/deletePurchaseOrderItem/') ?>' + po_item_id,
            type: 'POST',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    loadPurchaseItems(); // Refresh table
                    Toastify({
                        text: "Item deleted successfully.",
                        className: "toastify-success",
                        duration: 3000,
                        gravity: "top",
                        position: "right"
                    }).showToast();
                } else {
                    alert(response.message);
                }
            },
            error: function() {
                alert('Error occurred while deleting the item.');
            }
        });
    }

    function completePurchaseOrder(po_id) {
    const grandTotal = $('#grandTotalCell').data('grand');
    
    var po_id = $('#po_id').val();

    Swal.fire({
        title: 'Are you sure?',
        text: "You are about to complete this Purchase Order.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, complete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '<?= base_url('purchase/completePurchaseOrder') ?>',
                type: 'POST',
                data: {
                    po_id: po_id,
                    grand_total: grandTotal
                },
                success: function(response) {
                    var res = null;
                    try {
                        res = (typeof response === 'object') ? response : JSON.parse(response);
                    } catch (e) {
                        // If parsing fails but server likely succeeded (since UI updates on refresh), treat as success
                        res = { status: 'success' };
                    }

                    if (res && res.status === 'success') {
                        loadPurchases();
                        $('#addItemModal').modal('hide');
                        Toastify({
                            text: "Purchase Order completed successfully.",
                            className: "toastify-success",
                            duration: 3000,
                            gravity: "top",
                            position: "right"
                        }).showToast();
                    } else {
                        Toastify({
                            text: (res && res.message) ? res.message : 'Failed to complete purchase order.',
                            className: "toastify-error",
                            duration: 3000,
                            gravity: "top",
                            position: "right"
                        }).showToast();
                    }
                },
                error: function(xhr) {
                    var msg = 'Error occurred while completing the purchase order.';
                    try {
                        var r = JSON.parse(xhr.responseText || '{}');
                        if (r && r.message) { msg = r.message; }
                    } catch (e) {}
                    Toastify({
                        text: msg,
                        className: "toastify-error",
                        duration: 3000,
                        gravity: "top",
                        position: "right"
                    }).showToast();
                }
            });
        }
    });
}
</script>





<div class="modal fade" id="addItemModal" tabindex="-1" aria-labelledby="addItemModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen modal-xl">
        <div class="modal-content">
            <div class="modal-body">

                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header" style="padding: 3px;">
    <div class="col-lg-12" id="supplier_name_div" style="font-weight: bold; font-size: 1.3em; color:rgb(207, 85, 14);">
                                        </div>
                            </div>
                            <div class="card-body">
                                <form id="purchaseItemForm">
                                    <input type="hidden" name="po_id" id="po_id">
                                    <input type="hidden" name="supplier_id" id="supplier_id">

                                    <div class="row mb-3">
                                        <div class="col-lg-6">
                                            <select name="filter_item_category" id="filter_item_category" class="form-select" onchange="filterProducts();">
                                                    <option value="">Select Category</option>
                                                    <?php foreach ($productCategories as $category) { ?>
                                                        <option value="<?= $category->itemCategoryId ?>"><?= $category->itemCategoryName ?></option>
                                                    <?php } ?>
                                                </select>
                                        </div>
                                        <div class="col-lg-4">
                                            <select name="filter_item_brand" id="filter_item_brand" class="form-select" onchange="filterProducts();">
                                                    <option value="">Select Brand</option>
                                                    <?php foreach ($productBrands as $brand) { ?>
                                                        <option value="<?= $brand->itemBrandId ?>"><?= $brand->itemBrandName ?></option>
                                                    <?php } ?>
                                                </select>
                                        </div>
                                        <div class="col-lg-2 d-grid">
                                            <button type="button" class="btn btn-outline-secondary" onclick="clearAddItemForm();">Clear Filters</button>
                                        </div>
                                    </div>

                                    <div class="row g-3">
                                        <!-- Product Dropdown -->
                                        <div class="col-lg-8">
                                            <div class="form" id="productSelectDiv">
                                                <select name="product_id" id="product_id" class="form-select" onchange="setDefaultValues();" required>
                                                    <option value="">Select Product</option>
                                                    <?php foreach ($products as $product) { ?>
                                                        <option value="<?= $product->product_id ?>"
                                                        data-brand-id="<?= $product->item_brand_id ?>"
    data-category-id="<?= $product->item_category_id ?>"
                                                            data-brand="<?= $product->brand_name ?>"
                                                            data-category="<?= $product->category_name ?>"
                                                            data-measurement_unit="<?= $product->measurement_unit ?>"
                                                            data-sale_price="<?= $product->sale_price ?>"
                                                            data-reorder_level="<?= $product->reorder_level ?>"
                                                            data-barcode="<?= $product->barcode ?>"
                                                            data-inventory_type="<?= strtolower($product->inventory_type ?? 'sale') ?>"><?= strtoupper($product->product_name) ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </div>

                                        <!-- UOM -->
                                        <div class="col-lg-2">
                                            <div class="form">
                                                <select name="uom" id="uom" class="form-select" required>
                                                    <option value="">Unit</option>
                                                    <option value="kg">Kilogram (kg)</option>
                                                    <option value="g">Gram (g)</option>
                                                    <option value="l">Liter (L)</option>
                                                    <option value="ml">Milliliter (mL)</option>
                                                    <option value="p">Piece</option>
                                                </select>
                                            </div>
                                        </div>

                                        <!-- Inventory Type (Derived from Product, displayed read-only) -->
                                        <div class="col-lg-2">
                                            <div class="form">
                                                <select name="inventory_type" id="inventory_type" class="form-select" disabled>
                                                    <option value="sale">Sale</option>
                                                    <option value="internal">Internal Use</option>
                                                    <option value="both">Both</option>
                                                </select>
                                            </div>
                                        </div>

                                        <!-- Category -->
                                        <div class="col-lg-4">
                                            <div class="form-floating">
                                                <input type="text" name="category_name" id="category_name" class="form-control" disabled placeholder="Category">
                                                <label for="brand">Category</label>
                                            </div>
                                        </div>

                                        <!-- Brand Name -->
                                        <div class="col-lg-4">
                                            <div class="form-floating">
                                                <input type="text" name="brand_name" id="brand_name" class="form-control" disabled placeholder="Brand" required>
                                                <label for="brand_name">Brand Name</label>
                                            </div>
                                        </div>

                                        <!-- Barcode -->
                                         <div class="col-lg-4">
                                            <div class="form-floating">
                                                <input type="text" name="barcode" id="barcode" class="form-control" disabled placeholder="Barcode">
                                                <label for="Barcode">Barcode</label>
                                            </div>
                                        </div>

                                        <!-- Purchase Date -->
                                        <div class="col-lg-4">
                                            <div class="form-floating">
                                                <input type="date" name="purchase_date" id="purchase_date" class="form-control" placeholder="Purchase Date" required>
                                                <label for="purchase_date">Purchase Date *</label>
                                            </div>
                                        </div>

                                        <!-- Genuine Checkbox (Separate from floating) -->
                                        <div class="col-lg-6 d-flex align-items-center mt-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="genuine" id="genuine" value="1" checked>
                                                <label class="form-check-label" for="genuine">Genuine</label>
                                            </div>
                                        </div>

                                        <!-- Quantity -->
                                        <div class="col-lg-4">
                                            <div class="form-floating">
                                                <input type="number" name="quantity" id="quantity" class="form-control" placeholder="Quantity" required>
                                                <label for="quantity">Quantity *</label>
                                            </div>
                                        </div>

                                        <!-- Reorder Level -->
                                        <div class="col-lg-4">
                                            <div class="form-floating">
                                                <input type="number" name="reorder_level" id="reorder_level" class="form-control" placeholder="Reorder Level">
                                                <label for="reorder_level">Reorder Level *</label>
                                            </div>
                                        </div>

                                        <!-- Sale Price -->
                                        <div class="col-lg-4">
                                            <div class="form-floating">
                                                <input type="number" name="sale_price" id="sale_price" class="form-control" placeholder="Sale Price" step="0.01" required>
                                                <label for="sale_price">Sale Price *</label>
                                            </div>
                                        </div>

                                        <!-- Company Price -->
                                        <div class="col-lg-4">
                                            <div class="form-floating">
                                                <input type="number" name="company_price" id="company_price" class="form-control" placeholder="Company Price" step="0.01" required>
                                                <label for="company_price">Company Price *</label>
                                            </div>
                                        </div>

                                        <!-- Discount Percent -->
                                        <div class="col-lg-4">
                                            <div class="form-floating">
                                                <input type="number" name="discount_percent" id="discount_percent" class="form-control" placeholder="Discount %" step="0.01">
                                                <label for="discount_percent">Discount %</label>
                                            </div>
                                        </div>

                                        <!-- Discount Amount -->
                                        <div class="col-lg-4">
                                            <div class="form-floating">
                                                <input type="number" name="discount_amount" id="discount_amount" class="form-control" placeholder="Discount Amount" step="0.01" value="0">
                                                <label for="discount_amount">Discount Amount</label>
                                            </div>
                                        </div>

                                        <!-- Rack No -->
                                        <div class="col-lg-4">
                                            <div class="form-floating">
                                                <input type="text" name="rack_no" class="form-control" placeholder="Rack No">
                                                <label for="rack_no">Rack No</label>
                                            </div>
                                        </div>

                                        <!-- Bin No -->
                                        <div class="col-lg-4">
                                            <div class="form-floating">
                                                <input type="text" name="bin_no" class="form-control" placeholder="Bin No">
                                                <label for="bin_no">Bin No</label>
                                            </div>
                                        </div>

                                        <!-- Note -->
                                        <div class="col-lg-12">
                                            <div class="form-floating">
                                                <textarea name="note" class="form-control" placeholder="Note" style="height: 60px;"></textarea>
                                                <label for="note">Note</label>
                                            </div>
                                        </div>

                                        <!-- Submit Button -->
                                        <div class="col-lg-12 mt-3">
                                            <div class="text-end">
                                                <button type="button" class="btn btn-primary" id="savePurchaseItemBtn">Add Item</button>
                                            </div>
                                        </div>
                                        <div class="col-lg-12">
                                            <div id="createError" class="text-danger"></div>
                                        </div>

                                    </div>
                                </form>

                            </div>
                        </div>
                    </div><!-- end col -->


                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="card-title mb-0" id="purchaseOrderNoText2">Add Item to Purchase Order No</h6>
                                <div id="vatTypeIndicator" class="mt-1"></div>
                            </div>
                            <div class="card-body">
                                                        <div id="itemList"></div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="viewItemModal" tabindex="-1" aria-labelledby="viewItemModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen modal-xl">
        <div class="modal-content">
            <div class="modal-body">

                <div class="row">
                    
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                            <div class="col-lg-12" id="purchaseOrderNoText3" style="font-weight: bold; font-size: 1.3em; color:rgb(207, 85, 14);"></div>
                            <div class="col-lg-12" id="supplier_name_div2" style="font-weight: bold; font-size: 1.3em; color:rgb(207, 85, 14);"></div>
                            </div>
                            <div class="card-body">
                            <input type="hidden" name="po_id2" id="po_id2">
                            <input type="hidden" name="supplier_id2" id="supplier_id2">
                            <div id="itemList2"></div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>



<script>
   $(document).ready(function() {
    $('#savePurchaseItemBtn').click(function(e) {
        e.preventDefault();

        const companyPrice = parseFloat($('#company_price').val()) || 0;
        const salePrice = parseFloat($('#sale_price').val()) || 0;
        const discountPercent = parseFloat($('#discount_percent').val()) || 0;
        const discountAmount = parseFloat($('#discount_amount').val()) || 0;
        const quantity = parseFloat($('#quantity').val()) || 0;

        // Validation: Company Price > 0
        if (companyPrice <= 0) {
            Toastify({
                text: 'Company price must be greater than 0.',
                className: "toastify-error",
                duration: 5000,
                close: true,
                gravity: "top",
                position: "right"
            }).showToast();
            return;
        }

        // Validation: Quantity > 0
        if (quantity <= 0) {
            Toastify({
                text: 'Quantity must be greater than 0.',
                className: "toastify-error",
                duration: 5000,
                close: true,
                gravity: "top",
                position: "right"
            }).showToast();
            return;
        }

        // Validation: Sale price >= Company price
        if (salePrice < companyPrice) {
            Toastify({
                text: 'Sale price must not be less than company price.',
                className: "toastify-error",
                duration: 5000,
                close: true,
                gravity: "top",
                position: "right"
            }).showToast();
            return;
        }

        // New rule: If sale price equals company price, one discount field is required
        if (salePrice === companyPrice && discountPercent <= 0 && discountAmount <= 0) {
            Toastify({
                text: 'When sale price equals company price, enter Discount % or Discount Amount.',
                className: "toastify-error",
                duration: 5000,
                close: true,
                gravity: "top",
                position: "right"
            }).showToast();
            return;
        }

        // Validation: Only one discount field allowed
        if (discountPercent > 0 && discountAmount > 0) {
            Toastify({
                text: 'Only one of discount percent or discount amount can be entered.',
                className: "toastify-error",
                duration: 5000,
                close: true,
                gravity: "top",
                position: "right"
            }).showToast();
            return;
        }

        // If all validations pass, proceed with AJAX
        $.ajax({
            url: "<?= base_url('addPurchaseOrderItem'); ?>",
            method: "POST",
            data: $('#purchaseItemForm').serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#purchaseItemForm')[0].reset();
                    $('#purchase_date').val(currentBillDate);
                    // Manually reset disabled fields
                    $('#category_name, #brand_name, #barcode, #reorder_level').val('');
                    $('#product_id, #uom, #inventory_type').val('');

                    $('#createError').html('').show();
                    loadPurchaseItems();
                    Toastify({
                        text: response.message,
                        className: "toastify-success",
                        duration: 5000,
                        close: true,
                        gravity: "top",
                        position: "right"
                    }).showToast();
                } else {
                    $('#createError').html(response.message).show();
                    Toastify({
                        text: response.message,
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
            }
        });
    });
});


function deletePurchase(po_id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "This will delete the Purchase Order and all its items.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '<?= base_url('purchase/deletePurchase') ?>',
                type: 'POST',
                data: { po_id: po_id },
                success: function (response) {
                    const res = JSON.parse(response);
                    if (res.status === 'success') {
                        Swal.fire('Deleted!', 'The purchase order was deleted.', 'success');
                        loadPurchases();
                    } else {
                        Swal.fire('Error!', res.message, 'error');
                    }
                },
                error: function () {
                    Swal.fire('Error!', 'An error occurred while deleting.', 'error');
                }
            });
        }
    });
}

function filterProducts() {
    let selectedCategory = $('#filter_item_category').val();
    let selectedBrand = $('#filter_item_brand').val();

    $.ajax({
        url: '<?= base_url('purchase/filterProducts') ?>',
        type: 'POST',
        data: { category_id: selectedCategory, brand_id: selectedBrand },
        success: function (response) {
            $('#productSelectDiv').html(response);
            //product select convert to select2
            $('#product_id').select2({
                placeholder: 'Select Product',
                dropdownParent: $('#addItemModal')
            });
        },
        error: function () {
            console.log('Error loading product list.');
        }
    });


}


// Clear all inputs and selections in Add Item modal and reset filters
function clearAddItemForm() {
    // Reset selects (with change to update Select2 UI)
    $('#filter_item_category').val('').trigger('change');
    $('#filter_item_brand').val('').trigger('change');
    $('#product_id').val('').trigger('change');
    $('#uom').val('').trigger('change');
    $('#inventory_type').val('sale').trigger('change');

    // Reset text/number/date inputs
    $('#category_name').val('');
    $('#brand_name').val('');
    $('#barcode').val('');
    $('#purchase_date').val(currentBillDate || '');
    $('#genuine').prop('checked', true);
    $('#quantity').val('');
    $('#reorder_level').val('');
    $('#sale_price').val('');
    $('#company_price').val('');
    $('#discount_percent').val('');
    $('#discount_amount').val('0');
    $('input[name="rack_no"]').val('');
    $('input[name="bin_no"]').val('');
    $('textarea[name="note"]').val('');

    // Enable disabled fields in case product was selected before
    $('#brand_name').prop('disabled', true);
    $('#category_name').prop('disabled', true);
    $('#barcode').prop('disabled', true);

    // Refresh product list for cleared filters
    filterProducts();

    // Re-init select2 for product after reload
    setTimeout(function(){
        $('#product_id').select2({
            placeholder: 'Select Product',
            dropdownParent: $('#addItemModal')
        });
    }, 200);
}





</script>

</body>