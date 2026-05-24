<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Service Packages Management</h4>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card mainCard">
            <div class="card-header d-flex justify-content-between">
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#packageModal">
                    <i class="fa fa-plus"></i> Add New Package
                </button>
            </div>
            <div class="card-body">
                <div id="packagesTable"></div>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<!-- Modal -->
<div class="modal fade" id="packageModal" tabindex="-1" aria-labelledby="packageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <form id="packageForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="packageModalLabel">Add Service Package</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger" id="packageError" style="display:none;"></div>
                    <input type="hidden" id="package_id" name="package_id" value="">

                    <div class="row g-4">
                        <!-- Left panel: Details, discount, amounts, selected -->
                        <div class="col-12 col-lg-4">
                            <div class="mb-3">
                                <label class="form-label">Package Name *</label>
                                <input type="text" class="form-control" id="package_name" name="package_name" required>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Availability Start (optional)</label>
                                    <input type="date" class="form-control" id="availability_start" name="availability_start">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Availability End (optional)</label>
                                    <input type="date" class="form-control" id="availability_end" name="availability_end">
                                </div>
                            </div>

                            <div class="card mb-3">
                                <div class="card-body">
                                    <div class="row g-2">
                                        <div class="col-12">
                                            <label class="form-label">Subtotal:</label>
                                            <h5>LKR <span id="subtotalPrice">0.00</span></h5>
                                        </div>
                                        <div class="col-12">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label class="form-label">Package Discount Type:</label>
                                                    <select class="form-select" id="package_discount_type" name="package_discount_type">
                                                        <option value="none">No Discount</option>
                                                        <option value="percentage">Percentage</option>
                                                        <option value="fixed">Fixed Amount</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Package Discount Value:</label>
                                                    <input type="number" class="form-control" id="package_discount_value" name="package_discount_value" min="0" step="0.01" disabled>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label class="form-label">Discount Amount:</label>
                                                    <h6 class="text-danger">-LKR <span id="packageDiscountAmount">0.00</span></h6>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Final Total:</label>
                                                    <h4 class="text-success">LKR <span id="totalPrice">0.00</span></h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Selected Items</label>
                                <div id="selectedItemsPanel" class="border rounded p-2 small text-muted">No items selected</div>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary flex-fill" id="savePackageBtn">Save Package</button>
                                <button type="submit" class="btn btn-success flex-fill d-none" id="updatePackageBtn">Update Package</button>
                            </div>
                        </div>

                        <!-- Right panel: Filters, tabs, pickers -->
                        <div class="col-12 col-lg-8">
                            

                            <style>
                                #packageTabs .nav-link#tab-services { background: #e8f4ff; color: #0d6efd; border: 1px solid #b6d6ff; }
                                #packageTabs .nav-link#tab-services.active { background: #0d6efd; color: #ffffff; }
                                #packageTabs .nav-link#tab-products { background: #fff3e6; color: #fd7e14; border: 1px solid #ffd1a6; }
                                #packageTabs .nav-link#tab-products.active { background: #fd7e14; color: #ffffff; }
                                #packageTabs .nav-link { margin-right: 6px; }
                                .picker-table { max-height: calc(100vh - 380px); overflow: auto; }
                            </style>
                            <ul class="nav nav-tabs mb-3" id="packageTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="tab-services" data-bs-toggle="tab" data-bs-target="#tabPaneServices" type="button" role="tab">Service Items</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="tab-products" data-bs-toggle="tab" data-bs-target="#tabPaneProducts" type="button" role="tab">Items</button>
                                </li>
                            </ul>

                            <div class="tab-content">
                                <div class="tab-pane fade show active" id="tabPaneServices" role="tabpanel">
                                    <div class="card mb-2">
                                        <div class="card-body">
                                            <div class="row g-3 align-items-end">
                                                <div class="col-md-4">
                                                    <label class="form-label">Section</label>
                                                    <select class="form-select" id="package_section_filter" name="package_section_filter">
                                                        <option value="">All Sections</option>
                                                        <?php foreach ($sections as $section): ?>
                                                            <option value="<?= $section->employeeSectionId ?>"><?= $section->employeeSectionName ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">Category</label>
                                                    <select class="form-select" id="package_category_filter" name="package_category_filter">
                                                        <option value="">All Categories</option>
                                                        <?php foreach ($serviceItemCategory as $category): ?>
                                                            <option value="<?= $category->sic_id ?>"><?= $category->sci_name ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="col-md-4">
                                                    <button type="button" class="btn btn-primary w-100" onclick="loadPackageServiceItems();">
                                                        <i class="fa fa-filter me-1"></i> Filter Service Items
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="picker-table">
                                        <table id="packageItemsTable" class="table table-striped table-responsive">
                                            <thead class="table-info">
                                                <tr>
                                                    <th scope="col">Service Items</th>
                                                    <th scope="col">Original Price</th>
                                                    <th scope="col">Discount Type</th>
                                                    <th scope="col">Discount Value</th>
                                                    <th scope="col">Final Price</th>
                                                </tr>
                                            </thead>
                                            <tbody id="packageItemsTableBody">
                                                <?php foreach ($items as $item): ?>
                                                    <tr class="item-row" data-item-id="<?= $item->id ?>">
                                                        <td>
                                                            <div class="form-check">
                                                                <input class="form-check-input item-checkbox" type="checkbox" value="<?= $item->id ?>" data-price="<?= $item->price ?>" id="item<?= $item->id ?>">
                                                                <label class="form-check-label" for="item<?= $item->id ?>"><?= htmlspecialchars($item->name) ?></label>
                                                            </div>
                                                        </td>
                                                        <td class="original-price">LKR <?= number_format($item->price, 2) ?></td>
                                                        <td>
                                                            <select class="form-select discount-type" disabled>
                                                                <option value="none">No Discount</option>
                                                                <option value="percentage">Percentage</option>
                                                                <option value="fixed">Fixed Amount</option>
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <input type="number" class="form-control discount-value" min="0" step="0.01" disabled>
                                                        </td>
                                                        <td class="final-price">LKR <?= number_format($item->price, 2) ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="tab-pane fade" id="tabPaneProducts" role="tabpanel">
                                    <div class="card mb-2">
                                        <div class="card-body">
                                            <div class="row g-3 align-items-end">
                                                <div class="col-md-4">
                                                    <label class="form-label">Brand</label>
                                                    <select id="package_product_brand" class="form-select">
                                                        <option value="">All Brands</option>
                                                        <?php if (!empty($itemBrands)): foreach ($itemBrands as $b): ?>
                                                            <option value="<?= $b->itemBrandId ?>"><?= $b->itemBrandName ?></option>
                                                        <?php endforeach; endif; ?>
                                                     </select>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">Category</label>
                                                    <select id="package_product_category" class="form-select">
                                                        <option value="">All Categories</option>
                                                        <?php if (!empty($itemCategories)): foreach ($itemCategories as $c): ?>
                                                            <option value="<?= $c->itemCategoryId ?>"><?= $c->itemCategoryName ?></option>
                                                        <?php endforeach; endif; ?>
                                                     </select>
                                                </div>
                                                <div class="col-md-4">
                                                    <button type="button" class="btn btn-primary w-100" id="btnFilterProducts">
                                                        <i class="fa fa-filter me-1"></i> Filter Items
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="picker-table">
                                        <table class="table table-striped">
                                            <thead class="table-info">
                                                <tr>
                                                    <th>Item</th>
                                                    <th>SKU</th>
                                                    <th>Brand</th>
                                                    <th>Category</th>
                                                    <th class="text-end">Sale Price</th>
                                                </tr>
                                            </thead>
                                            <tbody id="packageProductsTableBody">
                                                <tr><td colspan="5" class="text-center text-muted">Use filters to load items</td></tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>



<script>
// Global selection map accessible from all handlers (inside and outside document.ready)
var selectedItems = window.selectedItems || {};
window.selectedItems = selectedItems;
   $(document).ready(function() {
    // Persist selected items across filters
    selectedItems = window.selectedItems || selectedItems; // ensure we use the global instance

    function renderSelectedItemsPanel() {
        const $panel = $('#selectedItemsPanel');
        const ids = Object.keys(selectedItems);
        if (ids.length === 0) { $panel.text('No items selected'); return; }
        let html = '<ul class="mb-0">';
        ids.forEach(function(key){
            const it = selectedItems[key];
            html += '<li class="d-flex justify-content-between align-items-center">'
                 + '<span>' + (it.name || ('Item #' + it.item_id)) + ' - LKR ' + Number(it.final_price||it.price||0).toFixed(2) + '</span>'
                 + '<button type="button" class="btn btn-sm btn-link text-danger p-0 remove-selected" data-id="'+it.item_id+'" data-type="'+it.item_type+'">Remove</button>'
                 + '</li>';
        });
        html += '</ul>';
        $panel.html(html);
    }

    $(document).on('click','.remove-selected', function(){
        const id = $(this).data('id');
        const itemType = $(this).data('type') || 'service';
        const key = itemType + ':' + String(id);
        delete selectedItems[key];
        
        if (itemType === 'service') {
            const $row = $('tr[data-item-id="'+id+'"]');
            if ($row.length){
                $row.find('.item-checkbox').prop('checked', false);
                $row.find('.discount-type').val('none').prop('disabled', true);
                $row.find('.discount-value').val('').prop('disabled', true);
            }
        } else if (itemType === 'product') {
            const $row = $('tr[data-product-id="'+id+'"]');
            if ($row.length){
                $row.find('.product-checkbox').prop('checked', false);
            }
        }
        
        renderSelectedItemsPanel();
        calculateTotalPrice();
    });

    function upsertFromRow($row){
        const id = String($row.data('item-id'));
        const key = 'service:'+id;
        const name = $.trim($row.find('label.form-check-label').text()) || null;
        const price = parseFloat($row.find('.item-checkbox').data('price')) || 0;
        const type = $row.find('.discount-type').val() || 'none';
        const val = parseFloat($row.find('.discount-value').val()) || 0;
        let finalPrice = price;
        if (type==='percentage' && val>0) finalPrice = price - (price*val/100);
        else if (type==='fixed' && val>0) { finalPrice = price - val; if (finalPrice<0) finalPrice=0; }
        
        console.log('Upserting service item:', {id, key, name, price, type, val, finalPrice});
        
        selectedItems[key] = { item_id:id, name:name, price:price, discount_type:type, discount_value:val, final_price:finalPrice, item_type:'service' };
        console.log('Selected items after upsert:', selectedItems);
        renderSelectedItemsPanel();
        calculateTotalPrice();
    }

    function reapplySelections(){
        Object.keys(selectedItems).forEach(function(k){
            const it = selectedItems[k];
            if ((it.item_type||'service')==='service'){
                const $row = $('tr[data-item-id="'+it.item_id+'"]');
                if ($row.length){
                    $row.find('.item-checkbox').prop('checked', true);
                    $row.find('.discount-type').prop('disabled', false).val(it.discount_type||'none');
                    $row.find('.discount-value').prop('disabled', false).val(it.discount_value||'');
                    calculateItemPrice($row);
                }
            } else {
                const $prow = $('tr[data-product-id="'+it.item_id+'"]');
                if ($prow.length){
                    $prow.find('.product-checkbox').prop('checked', true);
                }
            }
        });
        renderSelectedItemsPanel();
        calculateTotalPrice();
    }

    // Load packages when page loads
    loadServicePackages();
    
    // Debug: Check service items data attributes
    console.log('Service items loaded:', $('.item-checkbox').length);
    $('.item-checkbox').each(function() {
        console.log('Service item:', $(this).val(), 'Price:', $(this).data('price'), 'Row data:', $(this).closest('tr').data('item-id'));
    });

    // Enable/disable discount fields when checkbox is toggled
    $(document).on('change', '.item-checkbox', function() {
        console.log('Service item checkbox changed:', $(this).val(), $(this).is(':checked'));
        const $row = $(this).closest('tr');
        const isChecked = $(this).is(':checked');
        
        // Toggle discount fields
        $row.find('.discount-type, .discount-value').prop('disabled', !isChecked);
        
        // Reset discount fields if unchecked
        if (!isChecked) {
            $row.find('.discount-type').val('none');
            $row.find('.discount-value').val('');
            // Remove from persisted selections
            const id = String($row.data('item-id'));
            const key = 'service:'+id;
            console.log('Removing service item:', key);
            delete selectedItems[key];
            renderSelectedItemsPanel();
        }
        
        // Recalculate prices
        calculateItemPrice($row);
        if (isChecked) { 
            console.log('Adding service item to selection');
            upsertFromRow($row); 
        }
        console.log('Selected items after change:', selectedItems);
        calculateTotalPrice();
    });


    $(document).on('click', '.toggle-package', function(e) {
        e.preventDefault();
        const packageId = $(this).data('id');
        const currentStatus = $(this).data('status');
        toggleServicePackage(packageId, currentStatus);
    });

    // Calculate price when discount fields change
    $(document).on('change', '.discount-type, .discount-value', function() {
        console.log('Discount field changed:', $(this).attr('class'), $(this).val());
        const $row = $(this).closest('tr');
        const $checkbox = $row.find('.item-checkbox');
        
        if ($checkbox.is(':checked')) {
            console.log('Recalculating price for checked item');
            calculateItemPrice($row);
            upsertFromRow($row);
            calculateTotalPrice();
        } else {
            console.log('Item not checked, skipping recalculation');
        }
    });

    // Handle package discount type changes
    $('#package_discount_type').change(function() {
        const discountType = $(this).val();
        const $discountValue = $('#package_discount_value');
        
        if (discountType === 'none') {
            $discountValue.prop('disabled', true).val('');
        } else {
            $discountValue.prop('disabled', false);
        }
        
        calculateTotalPrice();
    });

    // Handle package discount value changes
    $('#package_discount_value').on('input', function() {
        calculateTotalPrice();
    });

    // Calculate price for individual item
    function calculateItemPrice($row) {
        const originalPrice = parseFloat($row.find('.item-checkbox').data('price'));
        const discountType = $row.find('.discount-type').val();
        const discountValue = parseFloat($row.find('.discount-value').val()) || 0;
        
        let finalPrice = originalPrice;
        
        if (discountType === 'percentage' && discountValue > 0) {
            // Apply percentage discount
            finalPrice = originalPrice - (originalPrice * discountValue / 100);
        } else if (discountType === 'fixed' && discountValue > 0) {
            // Apply fixed amount discount
            finalPrice = originalPrice - discountValue;
            // Ensure price doesn't go negative
            if (finalPrice < 0) finalPrice = 0;
        }
        
        // Update displayed final price
        $row.find('.final-price').text('LKR ' + finalPrice.toFixed(2));
    }

    // Calculate total price of all selected items
    function calculateTotalPrice() {
        console.log('Calculating total price. Selected items:', selectedItems);
        let subtotal = 0;
        Object.keys(selectedItems).forEach(function(k){
            const item = selectedItems[k];
            console.log('Processing item:', k, item);
            subtotal += Number(item.final_price || 0);
        });
        
        console.log('Subtotal calculated:', subtotal);
        
        // Update subtotal
        $('#subtotalPrice').text(subtotal.toFixed(2));
        
        // Calculate package discount
        calculatePackageDiscount(subtotal);
    }
    
    // Calculate package discount
    function calculatePackageDiscount(subtotal) {
        const discountType = $('#package_discount_type').val();
        const discountValue = parseFloat($('#package_discount_value').val()) || 0;
        let discountAmount = 0;
        
        if (discountType === 'percentage' && discountValue > 0) {
            discountAmount = (subtotal * discountValue) / 100;
        } else if (discountType === 'fixed' && discountValue > 0) {
            discountAmount = discountValue;
        }
        
        // Update discount amount display
        $('#packageDiscountAmount').text(discountAmount.toFixed(2));
        
        // Calculate final total
        const finalTotal = subtotal - discountAmount;
        $('#totalPrice').text(finalTotal.toFixed(2));
    }

    // Form submission for saving package
    $('#packageForm').submit(function(e) {
        e.preventDefault();

        const packageId = $('#package_id').val();
        
        // Validate at least one item is selected
        if (Object.keys(selectedItems || {}).length === 0) {
            $('#packageError').html('Please select at least one service item.').show();
            return;
        }

        // Validate discount values
        let valid = true;
        Object.keys(selectedItems).forEach(function(k){
            const si = selectedItems[k];
            const discountType = si.discount_type || 'none';
            const discountValue = Number(si.discount_value || 0);
            const isService = (si.item_type||'service')==='service';
            const $row = isService ? $('tr[data-item-id="'+si.item_id+'"]') : $('tr[data-product-id="'+si.item_id+'"]');

            // reset visible state
            if ($row.length) { $row.find('.discount-value').removeClass('is-invalid'); }

            if (discountType !== 'none' && discountValue <= 0) {
                valid = false;
                if ($row.length) { $row.find('.discount-value').addClass('is-invalid'); }
            }
            if (discountType === 'percentage' && discountValue > 100) {
                valid = false;
                if ($row.length) { $row.find('.discount-value').addClass('is-invalid'); }
            }
        });
         
        if (!valid) {
            $('#packageError').html('Please enter valid discount values.').show();
            return;
        }

        // If product selections exist (item_type product), no per-item discount UI exists for them.

        // Collect item data with discounts
        const items = Object.keys(selectedItems).map(function(k){
            return {
                item_id: selectedItems[k].item_id,
                discount_type: selectedItems[k].discount_type,
                discount_value: selectedItems[k].discount_value,
                item_type: selectedItems[k].item_type || 'service'
            };
        });

        // Prepare data for AJAX
        const formData = {
            package_name: $('#package_name').val(),
            items: items,
            package_discount_type: $('#package_discount_type').val(),
            package_discount_value: $('#package_discount_value').val() || 0,
            availability_start: $('#availability_start').val() || '',
            availability_end: $('#availability_end').val() || ''
        };

        // Include package ID if editing
        if (packageId) {
            formData.package_id = packageId;
        }

        // Determine which button was clicked
        const isUpdate = $('#updatePackageBtn').is(':visible');
        const url = isUpdate ? "<?= site_url('services/updateServicePackage') ?>" : "<?= site_url('/saveServicePackage') ?>";
        const buttonId = isUpdate ? '#updatePackageBtn' : '#savePackageBtn';

        // Submit via AJAX
        $.ajax({
            url: url,
            type: "POST",
            data: formData,
            dataType: "json",
            beforeSend: function() {
                $(buttonId).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');
            },
            success: function(response) {
                if (response.status === 'success') {
                    $('#packageModal').modal('hide');
                    loadServicePackages();
                    showToast(response.message, 'success');
                } else {
                    $('#packageError').html(response.message).show();
                }
            },
            error: function(xhr, status, error) {
                $('#packageError').html('An error occurred. Please try again.').show();
            },
            complete: function() {
                $(buttonId).prop('disabled', false).html(isUpdate ? 'Update Package' : 'Save Package');
            }
        });
    });

    // Reset modal when closed
    $('#packageModal').on('hidden.bs.modal', function() {
        $('#packageForm')[0].reset();
        $('#packageError').hide();
        $('.item-checkbox').prop('checked', false);
        $('.discount-type').val('none').prop('disabled', true);
        $('.discount-value').val('').prop('disabled', true);
        $('.final-price').each(function() {
            const originalPrice = $(this).data('original');
            $(this).text('LKR ' + parseFloat(originalPrice).toFixed(2));
        });
        $('#totalPrice').text('0.00');
        $('#savePackageBtn').removeClass('d-none');
        $('#updatePackageBtn').addClass('d-none');
        $('#packageModalLabel').text('Add Service Package');
        // Reset filters
        $('#package_section_filter').val('');
        $('#package_category_filter').val('');
        // Reset package discount
        $('#package_discount_type').val('none');
        $('#package_discount_value').val('').prop('disabled', true);
        $('#subtotalPrice').text('0.00');
        $('#packageDiscountAmount').text('0.00');
        selectedItems = {};
        renderSelectedItemsPanel();
        // Reset availability
        $('#availability_start').val('');
        $('#availability_end').val('');
    });

    // Load service packages into table
    function loadServicePackages() {
        $.ajax({
            url: "<?= site_url('services/loadServicePackages') ?>",
            type: "GET",
            success: function(data) {
                $('#packagesTable').html(data);
            },
            error: function() {
                showToast('Error loading packages', 'error');
            }
        });
    }

    // Edit package
    $(document).on('click', '.edit-package', function() {
        const packageId = $(this).data('id');
        editServicePackage(packageId);
    });

    function editServicePackage(packageId) {
        $.ajax({
            url: "<?= site_url('services/getServicePackageItem/') ?>" + packageId,
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.error) {
                    showToast(response.error, 'error');
                    return;
                }

                // Set package info
                $('#package_id').val(packageId);
                $('#package_name').val(response.package_name);

                // Clear all selections first (UI)
                $('.item-checkbox').prop('checked', false);
                $('.discount-type').val('none').prop('disabled', true);
                $('.discount-value').val('').prop('disabled', true);

                // Reset selection map
                selectedItems = {};

                // Set selected items and their discounts
                response.items.forEach(function(item) {
                    const key = (item.item_type||'service')+':'+String(item.item_id);
                    selectedItems[key] = {
                        item_id: String(item.item_id),
                        name: item.name,
                        price: Number(item.price || 0),
                        discount_type: item.discount_type || 'none',
                        discount_value: Number(item.discount_value || 0),
                        final_price: Number(item.final_price || item.price || 0),
                        item_type: item.item_type || 'service'
                    };
                });

                // Populate package discount fields
                $('#package_discount_type').val(response.package_discount_type || 'none');
                $('#package_discount_value').val(response.package_discount_value || '');
                
                // Hydrate availability fields
                $('#availability_start').val(response.availability_start || '');
                $('#availability_end').val(response.availability_end || '');

                // Enable/disable discount value field based on type
                if ((response.package_discount_type || 'none') === 'none') {
                    $('#package_discount_value').prop('disabled', true);
                } else {
                    $('#package_discount_value').prop('disabled', false);
                }

                // Populate availability dates
                $('#availability_start').val(response.availability_start || '');
                $('#availability_end').val(response.availability_end || '');

                // Reflect selections in UI and totals immediately
                renderSelectedItemsPanel();
                reapplySelections();
                calculateTotalPrice();

                // Toggle buttons and update modal title
                $('#savePackageBtn').addClass('d-none');
                $('#updatePackageBtn').removeClass('d-none');
                $('#packageModalLabel').text('Edit Service Package');
                
                // Show modal
                $('#packageModal').modal('show');
            },
            error: function(xhr, status, error) {
                showToast('Error loading package details', 'error');
            }
        });
    }

    // Delete package
    $(document).on('click', '.delete-package', function() {
        const packageId = $(this).data('id');
        deleteServicePackage(packageId);
    });



    // Open modal for new package
    $(document).on('click', '#addNewPackage', function() {
        openNewPackageModal();
    });

    function openNewPackageModal() {
        $('#packageForm')[0].reset();
        $('#package_id').val('');
        $('.item-checkbox').prop('checked', false);
        $('.discount-type').val('none').prop('disabled', true);
        $('.discount-value').val('').prop('disabled', true);
        $('.final-price').each(function() {
            const originalPrice = $(this).data('original');
            $(this).text('LKR ' + parseFloat(originalPrice).toFixed(2));
        });
        $('#totalPrice').text('0.00');
        $('#packageError').hide();
        $('#savePackageBtn').removeClass('d-none');
        $('#updatePackageBtn').addClass('d-none');
        $('#packageModalLabel').text('Add Service Package');
        // Reset filters
        $('#package_section_filter').val('');
        $('#package_category_filter').val('');
        // Reset package discount
        $('#package_discount_type').val('none');
        $('#package_discount_value').val('').prop('disabled', true);
        $('#subtotalPrice').text('0.00');
        $('#packageDiscountAmount').text('0.00');
        selectedItems = {};
        renderSelectedItemsPanel();
        $('#packageModal').modal('show');
    }

    // Helper function to show toast messages
    function showToast(message, type = 'success') {
        const className = type === 'success' ? 'toastify-success' : 
                         type === 'error' ? 'toastify-error' : 'toastify-info';
        
        Toastify({
            text: message,
            className: className,
            duration: 3000
        }).showToast();
    }

    // Expose functions globally for handlers outside document.ready
    window.renderSelectedItemsPanel = renderSelectedItemsPanel;
    window.calculateTotalPrice = calculateTotalPrice;
    window.reapplySelections = reapplySelections;
    window.upsertFromRow = upsertFromRow;
    window.calculateItemPrice = calculateItemPrice;
});

// Load filtered service items for package (moved outside document ready for global access)
function loadPackageServiceItems() {
    const sectionId = $('#package_section_filter').val();
    const categoryId = $('#package_category_filter').val();
    
    $.ajax({
        url: "<?= site_url('services/loadServiceItemsForPackage') ?>",
        type: "POST",
        data: { 
            section_id: sectionId,
            category_id: categoryId
        },
        beforeSend: function() {
            $('#packageItemsTableBody').html('<tr><td colspan="5" class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading items...</td></tr>');
        },
        success: function(data) {
            $('#packageItemsTableBody').html(data);
            // Reapply persisted selections to the new rows
            reapplySelections();
        },
        error: function() {
            $('#packageItemsTableBody').html('<tr><td colspan="5" class="text-center text-danger">Failed to load service items. Please try again.</td></tr>');
        }
    });
}

// Products tab filter and selection
$('#btnFilterProducts').on('click', function(){
    const brandId = $('#package_product_brand').val();
    const categoryId = $('#package_product_category').val();
    $('#packageProductsTableBody').html('<tr><td colspan="5" class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading...</td></tr>');
    $.post('<?= site_url('services/loadPackageProducts') ?>', { brand_id: brandId, category_id: categoryId }, function(html){
        $('#packageProductsTableBody').html(html);
        reapplySelections();
    });
});

// Auto-load products when Items tab is opened
$('#tab-products').on('shown.bs.tab', function() {
    if ($('#packageProductsTableBody tr').length === 1 && $('#packageProductsTableBody tr td').text().includes('Use filters')) {
        $('#packageProductsTableBody').html('<tr><td colspan="5" class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading all available products...</td></tr>');
        $.post('<?= site_url('services/loadPackageProducts') ?>', { brand_id: '', category_id: '' }, function(html){
            $('#packageProductsTableBody').html(html);
            reapplySelections();
        });
    }
});

$(document).on('change','.product-checkbox', function(){
    const $row = $(this).closest('tr');
    const id = String($row.data('product-id'));
    const key = 'product:'+id;
    
    if ($(this).is(':checked')){
        const name = $.trim($row.find('label.form-check-label').text()) || $row.find('td').first().text();
        const price = parseFloat($(this).data('price')) || 0;
        selectedItems[key] = { item_id:id, name:name, price:price, discount_type:'none', discount_value:0, final_price:price, item_type:'product' };
    } else {
        delete selectedItems[key];
    }
    renderSelectedItemsPanel();
    calculateTotalPrice();
});
</script>