<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Service Items Management</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="<?php echo base_url(); ?>">Home</a></li>
                    <li class="breadcrumb-item active">Service Items</li>
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
                        <label class="form-label">Section</label>
                        <select class="form-select" id="section" name="section">
                            <option value="">All Sections</option>
                            <?php foreach ($sections as $section): ?>
                                <option value="<?= $section->employeeSectionId ?>"><?= $section->employeeSectionName ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Category</label>
                        <select class="form-select" id="category" name="category">
                            <option value="">All Categories</option>
                            <?php foreach ($serviceItemCategory as $category): ?>
                                <option value="<?= $category->sic_id ?>"><?= $category->sci_name ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <button class="btn btn-primary d-block" onclick="loadServiceItems();">
                            <i class="fa fa-refresh me-1"></i> Load Service Items
                        </button>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <button type="button" class="btn btn-success btn-label waves-effect waves-light d-block" style="width: 100%;" 
                                data-bs-toggle="modal" data-bs-target="#serviceItemModal">
                            <i class="fa fa-plus label-icon align-middle fs-16 me-2"></i> Add New Service Item
                        </button>
                    </div>
                </div>   
            </div>   
            <div class="card-body">
                <div id="serviceItemsTable"></div>
            </div>
        </div>
    </div>
</div>

<!-- Create/Edit Service Item Modal -->
<div class="modal fade zoomIn" id="serviceItemModal" data-bs-backdrop="static" tabindex="-1" aria-labelledby="serviceItemModalLabel" aria-modal="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="serviceItemModalLabel">Add New Service Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="serviceItemForm">
                    <input type="hidden" id="service_item_id" name="service_item_id">
                    <div class="alert alert-danger" id="serviceItemError" style="display:none;"></div>
                    
                    <div class="row g-3">
                        <!-- Section -->
                        <div class="col-md-6">
                            <label for="item_section" class="form-label">Section *</label>
                            <select class="form-select" id="item_section" name="section" required>
                                <option value="">Select Section</option>
                                <?php foreach ($sections as $section): ?>
                                    <option value="<?= $section->employeeSectionId ?>"><?= $section->employeeSectionName ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- Name -->
                        <div class="col-md-6">
                            <label for="item_name" class="form-label">Service Name *</label>
                            <input type="text" class="form-control" id="item_name" name="name" placeholder="Enter Service Name" required>
                        </div>

                        <div class="col-md-4">
                            <label for="service_category" class="form-label">Category *</label>
                            <select class="form-select" id="service_category" name="service_category" required>
                                <option value="">Select Service Category</option>
                                <?php foreach ($serviceItemCategory as $serviceCat): ?>
                                    <option value="<?= $serviceCat->sic_id ?>"><?= $serviceCat->sci_name ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- Price -->
                        <div class="col-md-4">
                            <label for="item_price" class="form-label">Price (LKR) *</label>
                            <input type="number" class="form-control" id="item_price" name="price" placeholder="Enter Price" step="0.01" min="0" required>
                        </div>
                        
                        <!-- Status -->
                        <div class="col-md-4">
                            <label for="item_status" class="form-label">Status *</label>
                            <select class="form-select" id="item_status" name="status" required>
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                        
                        <!-- Description -->
                        <div class="col-12">
                            <label for="item_description" class="form-label">Description</label>
                            <textarea class="form-control" id="item_description" name="description" rows="3" placeholder="Enter service description"></textarea>
                        </div>
                    </div>
                    
                    <div class="modal-footer mt-4">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" id="saveServiceItemBtn">Save Service Item</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    /* Toast Notifications */
    .toastify-success {
        background: #007625;
        background: linear-gradient(to right, #007625, #00a335);
        color: white;
        font-size: 14px;
        font-weight: 500;
        box-shadow: 0 2px 8px rgba(0, 118, 37, 0.3);
    }
    .toastify-error {
        background: #d32f2f;
        background: linear-gradient(to right, #d32f2f, #f44336);
        color: white;
        font-size: 14px;
        font-weight: 500;
        box-shadow: 0 2px 8px rgba(211, 47, 47, 0.3);
    }
</style>

<script type="text/javascript">
$(document).ready(function() {
    
    loadServiceItems();
    
    $('#serviceItemModal').on('shown.bs.modal', function () {
    $('#serviceItemModal select').select2({
        dropdownParent: $('#serviceItemModal')
    });
});


    $('#saveServiceItemBtn').click(function(e) {
        e.preventDefault();
        
        // Validate form
        if (!$('#serviceItemForm')[0].checkValidity()) {
            $('#serviceItemForm')[0].reportValidity();
            return;
        }
        
        $.ajax({
            url: "<?= site_url('/saveServiceItem') ?>",
            type: "POST",
            data: $('#serviceItemForm').serialize(),
            dataType: "json",
            beforeSend: function() {
                $('#saveServiceItemBtn').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');
            },
            success: function(response) {
                if (response.status === 'success') {
                    $('#serviceItemModal').modal('hide');
                    loadServiceItems();
                    Toastify({
                        text: response.message,
                        className: "toastify-success",
                        duration: 3000
                    }).showToast();
                } else {
                    $('#serviceItemError').html(response.message).show();
                    Toastify({
                        text: response.message,
                        className: "toastify-error",
                        duration: 3000
                    }).showToast();
                }
            },
            error: function(xhr) {
    console.log(xhr.responseText); 
    Toastify({
        text: 'An error occurred. Please try again.',
        className: "toastify-error",
        duration: 3000
    }).showToast();
},
            complete: function() {
                $('#saveServiceItemBtn').prop('disabled', false).html('Save Service Item');
            }
        });
    });

    // Reset form when modal is closed
    $('#serviceItemModal').on('hidden.bs.modal', function() {
        $('#serviceItemForm')[0].reset();
        $('#serviceItemError').hide();
        $('#service_item_id').val('');
        $('#serviceItemModalLabel').text('Add New Service Item');
    });
});

// Load service items
function loadServiceItems() {
    const sectionId = $('#section').val();
    const categoryId = $('#category').val();
    $.ajax({
        url: "<?= site_url('services/loadServiceItems') ?>",
        type: "POST",
        data: { 
            section_id: sectionId,
            category_id: categoryId
        },
        beforeSend: function() {
            $('#serviceItemsTable').html('<div class="text-center py-4"><i class="fa fa-spinner fa-spin fa-2x"></i><p>Loading service items...</p></div>');
        },
        success: function(data) {
            $('#serviceItemsTable').html(data);
        },
        error: function() {
            $('#serviceItemsTable').html('<div class="alert alert-danger">Failed to load service items. Please try again.</div>');
        }
    });
}

// Edit service item
function editServiceItem(itemId) {
    $.ajax({
        url: "<?= site_url('services/getServiceItem') ?>",
        type: "POST",
        data: { item_id: itemId },
        dataType: "json",
        success: function(response) {
            if (response.status === 'success') {
                const item = response.data;
                
                $('#service_item_id').val(item.id);
                $('#item_section').val(item.section_id).trigger('change');
                $('#service_category').val(item.service_category_id).trigger('change');
                $('#item_name').val(item.name);
                $('#item_price').val(item.price);
                $('#item_status').val(item.status);
                $('#item_description').val(item.description);
                
                $('#serviceItemModalLabel').text('Edit Service Item');
                $('#serviceItemModal').modal('show');
            } else {
                Toastify({
                    text: response.message,
                    className: "toastify-error",
                    duration: 3000
                }).showToast();
            }
        },
        error: function() {
            Toastify({
                text: 'Failed to load service item details',
                className: "toastify-error",
                duration: 3000
            }).showToast();
        }
    });
}

// Delete service item
function deleteServiceItem(itemId) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "<?= site_url('services/deleteServiceItem') ?>",
                type: "POST",
                data: { item_id: itemId },
                dataType: "json",
                success: function(response) {
                    if (response.status === 'success') {
                        loadServiceItems();
                        Toastify({
                            text: response.message,
                            className: "toastify-success",
                            duration: 3000
                        }).showToast();
                    } else {
                        Toastify({
                            text: response.message,
                            className: "toastify-error",
                            duration: 3000
                        }).showToast();
                    }
                },
                error: function() {
                    Toastify({
                        text: 'Failed to delete service item',
                        className: "toastify-error",
                        duration: 3000
                    }).showToast();
                }
            });
        }
    });
}
</script>