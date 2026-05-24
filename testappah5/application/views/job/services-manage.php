<?php
if ($url_status == 'all') {
    $status_name = 'All';
} else if ($url_status == '0') {
    $status_name = 'Pending';
} else if ($url_status == '1') {
    $status_name = 'Ongoing';
} else if ($url_status == '4') {
    $status_name = 'Completed';
} else if ($url_status == '2') {
    $status_name = 'Hold';
} else if ($url_status == '3') {
    $status_name = 'Cancelled';
} else if ($url_status == '5') {
    $status_name = 'Invoiced';
} else {
    $status_name = 'All';
}
?>

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Services Management</h4>

            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="<?php echo base_url(); ?>">Home</a></li>
                    <li class="breadcrumb-item active"><?= $status_name ?> Jobs</li>
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
                        <input type="date" name="sdate" id="sdate" class="form-control" value="">
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="edate" id="edate" class="form-control" value="">
                    </div>
                    <div class="col-md-2">
                        <select id="filter_service_type" name="type" class="form-select" onchange="loadServicesJobs()">
                            <option value="">All</option>
                            <option value="N">Normal</option>
                            <option value="M">Mechanical</option>
                            <option value="E">Estimate</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary" style="width: 100%;" onclick="loadServicesJobs();"><?= $status_name ?> Jobs</button>
                    </div>

                    <?php if ($url_status == '5') { ?>
                    <div class="col-md-2">
                        <button class="btn btn-info" style="width: 100%;" onclick="loadServicesJobs('credit');">Search Credit Invoices</button>
                    </div>
                    <?php }else{
                        echo '<div class="col-md-2"></div>';
                    } ?>

                    <?php if ($url_status == '0') { ?>
                        <div class="col-md-2">
                            <button class="btn btn-success waves-effect waves-light" style="width: 100%;" onclick="openAddJobModal()"><i class="fa fa-plus"></i> New Jobs</button>
                        </div>
                    <?php } ?>
                </div>
            </div>

            <div class="card-body">
                <div id="servicesTable"></div>
            </div>
        </div>
    </div>
    <!--end col-->
</div>


<!-- Add Service Modal -->
<div class="modal fade" id="addJobModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog  modal-xl zoomIn">
        <form id="serviceJobForm">
            <div class="modal-content">
                <div class="modal-header pb-2" style="border-bottom: 1px solid #f1f1f1;">
                    <h5 class="modal-title">Create Service Job</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">

                    <div class="row">

                        <!-- Left Column -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="existing_vehicle_id">Search Existing Vehicle</label>
                                <select id="existing_vehicle_id" name="vehicle_id" class="form-control">
                                    <option value="" selected>Search Existing Vehicle</option>
                                    <?php foreach ($vehicles as $vehicle) : ?>
                                        <?php
                                        // Format vehicle number (add space if no space between alphabet and numbers)
                                        $formatted_vehicle_no = preg_replace('/([A-Za-z]+)(\d+)/', '$1 $2', $vehicle->vehicle_no);
                                        ?>
                                        <option value="<?= $vehicle->vehicle_id ?>"
                                            data-category_id="<?= $vehicle->category_id ?>"
                                            data-vehicleCategoryName="<?= $vehicle->vehicleCategoryName ?>"
                                            data-brand_id="<?= $vehicle->brand_id ?>"
                                            data-vehicleBrandName="<?= $vehicle->vehicleBrandName ?>"
                                            data-customer_id="<?= $vehicle->customer_id ?>"
                                            data-customer_name="<?= $vehicle->customer_name ?>"
                                            data-customer_mobile="<?= $vehicle->customer_mobile ?>"
                                            data-salutation="<?= $vehicle->salutation ?>"
                                            data-vehicle_no="<?= $formatted_vehicle_no ?>">
                                            <?= $formatted_vehicle_no ?> &nbsp; - &nbsp; <?= $vehicle->customer_name ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Existing / New Customer Radio Button -->
                            <div class="row mb-3 mt-4">
                                <div class="btn-group" role="group" aria-label="Basic radio toggle button group">
                                    <input type="radio" class="btn-check" name="btnradio" id="existing_customer" autocomplete="off" checked="">
                                    <label class="btn btn-outline-secondary shadow-none" for="existing_customer">EXISTING CUSTOMER</label>

                                    <input type="radio" class="btn-check" name="btnradio" id="new_customer" autocomplete="off">
                                    <label class="btn btn-outline-secondary shadow-none" for="new_customer">NEW CUSTOMER</label>
                                </div>
                            </div>

                            <!-- Customer Info -->
                            <div class="mb-3">
                                <label for="customer_id">Customer</label>
                                <select id="customer_id" name="customer_id" class="form-select">
                                    <option value="">Select Customer</option>
                                    <?php foreach ($customers as $customer) : ?>
                                        <option value="<?= $customer->customers_id ?>"
                                            data-mobile="<?= $customer->mobile ?>"
                                            data-category_id=""
                                            data-vehicleCategoryName=""
                                            data-brand_id=""
                                            data-vehicleBrandName=""
                                            data-salutation="<?= $customer->salutation ?>"
                                            data-customer_name="<?= $customer->name ?>"><?= $customer->name ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!--new customer form-->
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <label for="new_salutation" class="form-label">Salutation *</label>
                                    <select class="form-select" id="new_salutation" name="new_salutation">
                                        <option value="">Select Salutation</option>
                                        <option value="MR">Mr.</option>
                                        <option value="MRS">Mrs.</option>
                                        <option value="MS">Ms.</option>
                                        <option value="DR">Dr.</option>
                                    </select>
                                </div>
                                <div class="col-md-9">
                                    <label for="new_customer_name">Customer Name</label>
                                    <input type="text" id="new_customer_name" name="new_customer_name" class="form-control" placeholder="Customer Name" style="text-transform: capitalize;">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="new_mobile">Contact Number</label>
                                    <input type="number" id="new_mobile" name="new_mobile" class="form-control" placeholder="Enter Contact Number" maxlength="10" pattern="\d{10}">
                                </div>

                                <div class="col-md-6">
                                    <label for="new_vehicle_no">Vehicle Number</label>
                                    <input type="text" id="new_vehicle_no" name="new_vehicle_no" class="form-control" placeholder="Vehicle Number" maxlength="8" pattern="[A-Za-z0-9 ]+" required style="text-transform: uppercase;">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="new_category_id">Vehicle Category</label>
                                    <select id="new_category_id" name="new_category_id" class="form-control">
                                        <option value="">Select Vehicle Category</option>
                                        <?php foreach ($vehicleCategories as $category) : ?>
                                            <option value="<?= $category->vehicleCategoryId ?>"><?= $category->vehicleCategoryName ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="new_brand_id">Vehicle Brand</label>
                                    <select id="new_brand_id" name="new_brand_id" class="form-control">
                                        <option value="">Select Vehicle Brand</option>
                                        <?php foreach ($vehicleBrands as $brand) : ?>
                                            <option value="<?= $brand->vehicleBrandId ?>"><?= $brand->vehicleBrandName ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <input type="hidden" id="use_vehicle_id" name="use_vehicle_id" value=""> <!-- Hidden field for vehicle ID -->
                                <input type="hidden" id="use_customer_id" name="use_customer_id" value=""> <!-- Hidden field for customer ID -->
                            </div>
                        </div><!-- End Left Column -->


                        <!-- Right Column -->
                        <div class="col-md-6" style="border-left:rgb(28, 5, 111) solid 1px;">

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="current_mileage">Current Mileage</label>
                                    <input type="number" id="current_mileage" name="current_mileage" placeholder="Current Mileage" class="form-control">
                                </div>

                                <div class="col-md-4">
                                    <label for="next_service_after">Next Service After (km)</label>
                                    <input type="number" id="next_service_after" name="next_service_after" placeholder="e.g., 5000" class="form-control">

                                </div>

                                <div class="col-md-4 d-none">
                                    <label for="next_service_mileage">Next Service Mileage</label>
                                    <input type="number" id="next_service_mileage" name="next_service_mileage" placeholder="Next Service Mileage" class="form-control">
                                </div>

                                <div class="col-md-4">
                                    <label for="next_service_date">Next Service Date</label>
                                    <input type="date" id="next_service_date" name="next_service_date" class="form-control">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div id="next_service_mileage_display" style="color: rgb(28, 5, 111);"></div>
                            </div>

                            <script>
                                function calculateNextServiceMileage() {
                                    const currentMileage = parseInt(document.getElementById('current_mileage').value) || 0;
                                    const serviceAfter = parseInt(document.getElementById('next_service_after').value) || 0;
                                    const nextMileage = currentMileage + serviceAfter;

                                    document.getElementById('next_service_mileage_display').innerHTML =
                                        nextMileage > 0 ? 'Next Service Mileage : ' + nextMileage : '';

                                    document.getElementById('next_service_mileage').value =
                                        nextMileage > 0 ? nextMileage : '';
                                }


                                document.getElementById('current_mileage').addEventListener('input', calculateNextServiceMileage);
                                document.getElementById('next_service_after').addEventListener('input', calculateNextServiceMileage);
                            </script>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="service_type">Service Type</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="service_types[]" value="N" id="service_type_normal" checked>
                                        <label class="form-check-label" for="service_type_normal">
                                            Normal
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="service_types[]" value="M" id="service_type_mechanical">
                                        <label class="form-check-label" for="service_type_mechanical">
                                            Mechanical
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="service_types[]" value="E" id="service_type_estimate">
                                        <label class="form-check-label" for="service_type_estimate">
                                            Estimate
                                        </label>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label for="status">Status</label>
                                    <select id="status" name="status" class="form-select">
                                        <option value="">Select Status</option>"
                                        <option value="0" selected>Pending</option>
                                        <option value="1" disabled>Ongoing</option>
                                        <option value="2" disabled>Hold</option>
                                        <option value="3" disabled>Cancelled</option>
                                        <option value="4" disabled>Completed</option>
                                    </select>
                                </div>
                            </div>


                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label for="service_type">Description</label>
                                    <textarea rows="4" id="description" name="description" class="form-control" placeholder="Description"></textarea>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <div id="errorDiv" class="alert alert-danger" style="display: none;"></div>
                                </div>
                            </div>

                        </div><!-- End Right Column -->

                    </div>


                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success" id="saveServiceJobBtn">Save Service Job</button>
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>



<script>
    loadServicesJobs();

    function openAddJobModal() {
        $('#addJobModal').modal('show');
        $('#addJobModal select').select2({
            dropdownParent: $('#addJobModal')
        });
    }

    function loadServicesJobs(invoicePayType = null) {
        let sdate = $('#sdate').val();
        let edate = $('#edate').val();
        let filter_service_type = $('#filter_service_type').val();

        // Show a loading spinner or message
        $('#servicesTable').html('<p>Loading...</p>');

        $.ajax({
            url: "<?php echo site_url('job/loadServicesJobs') ?>",
            type: "POST",
            data: {
                sdate: sdate,
                edate: edate,
                filter_service_type: filter_service_type,
                invoicePayType: invoicePayType
            },
            success: function(data) {
                $('#servicesTable').html(data); // Populate the table with data
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText);
                $('#servicesTable').html('<p>Error loading data.</p>');
            }
        });
    }

    $(document).ready(function() {

        // Handle form submission
        $('#serviceJobForm').submit(function(e) {
            e.preventDefault();

            // Optional: Force sync for all Select2 elements
            $(this).find('.select2').trigger('change');

            // Validate service types
            const selectedServiceTypes = $('input[name="service_types[]"]:checked');
            if (selectedServiceTypes.length === 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'Please select at least one service type.'
                });
                return;
            }

            // Prepare form data
            let formData = new FormData(this);
            formData.append('btnradio', $('input[name="btnradio"]:checked').attr('id'));

            // AJAX submission
            $.ajax({
                url: "<?= site_url('job/saveServiceJob') ?>",
                method: "POST",
                data: formData,
                dataType: 'json',
                processData: false,
                contentType: false,
                beforeSend: function() {
                    // Disable submit button to prevent duplicate submissions
                    $('#saveServiceJobBtn').prop('disabled', true);
                },
                complete: function() {
                    $('#saveServiceJobBtn').prop('disabled', false);
                },
                success: function(response) {
                    if (response.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message,
                            timer: 2000
                        });
                        $('#addJobModal').modal('hide');
                        $('#serviceJobForm')[0].reset();
                        loadServicesJobs();
                        // Reset Select2 fields if needed
                        // $('.select2').val(null).trigger('change');
                    } else {
                        let errors = Array.isArray(response.messages) ?
                            response.messages : [response.message];

                        $('#errorDiv').html(
                            '<ul><li>' + errors.join('</li><li>') + '</li></ul>'
                        ).show().delay(10000).fadeOut();
                    }
                },
                error: function(xhr) {
                    Swal.fire('Error', xhr.responseText || 'An error occurred', 'error');
                }
            });
        });





        // When an existing vehicle is selected
        $('#existing_vehicle_id').change(function() {
            setExistingVehicleData(this);
        });

        // Set Existing Vehicle Data based on selection
        function setExistingVehicleData(selectElement) {

            const vehicleId = $(selectElement).val();
            const selectedOption = $(selectElement).find(":selected");
            const vehicleNumber = selectedOption.data('vehicle_no');
            const customerId = selectedOption.data('customer_id');
            const salutation = selectedOption.data('salutation');
            const customer_name = selectedOption.data('customer_name');
            const customer_mobile = selectedOption.data('customer_mobile');

            //set hiidden fields
            $('#use_vehicle_id').val(vehicleId);
            $('#use_customer_id').val(customerId);

            // For Select2 fields, we need to both set the value and trigger the change
            $('#new_category_id').val(selectedOption.data('category_id')).trigger('change.select2');
            $('#new_brand_id').val(selectedOption.data('brand_id')).trigger('change.select2');

            // For regular input field (vehicle number)
            // Most reliable approach (combines methods)
            const $vehicleInput = $('#new_vehicle_no');
            $vehicleInput.val(vehicleNumber);
            $vehicleInput[0].dispatchEvent(new Event('input', {
                bubbles: true
            }));
            $vehicleInput.trigger('change');

            $('#new_mobile').val(customer_mobile).trigger('input');
            $('#new_customer_name').val(customer_name).trigger('input');

            // Set customer mode and details
            $('#existing_customer').prop('checked', true).trigger('change');

            // For Select2 customer dropdown
            $('#customer_id').val(customerId).trigger('change.select2');

            // For Select2 salutation dropdown (if applicable)
            $('#new_salutation').val(salutation).trigger('change.select2');

            // Disable fields
            $('#new_vehicle_no').prop('disabled', true);
            $('#new_category_id, #new_brand_id').prop('disabled', true);

            // Special handling if any of these are Select2 elements
            if ($('#new_category_id').hasClass('select2-hidden-accessible')) {
                $('#new_category_id').select2('enable', false);
            }
            if ($('#new_brand_id').hasClass('select2-hidden-accessible')) {
                $('#new_brand_id').select2('enable', false);
            }
        }

        // Handle radio button change (New/Existing Customer)
        $('input[name="btnradio"]').change(function() {
            const isNewCustomer = $(this).attr('id') === 'new_customer';

            // Enable/disable fields based on selection
            $('#customer_id').prop('disabled', isNewCustomer);
            $('#new_customer_name').prop('disabled', !isNewCustomer);
            $('#new_vehicle_no').prop('disabled', !isNewCustomer);
            $('#new_category_id').prop('disabled', !isNewCustomer);
            $('#new_brand_id').prop('disabled', !isNewCustomer);

            if (isNewCustomer) {
                resetNewCustomerForm();
            } else {
                // For existing customer, enable vehicle fields if they were disabled
                $('#new_vehicle_no, #new_category_id, #new_brand_id').prop('disabled', false);
            }
        });

        // Reset form for New Customer
        function resetNewCustomerForm() {
            $('#existing_vehicle_id').val('');
            $('#customer_id').val('').trigger('change');
            $('#new_salutation').val('').trigger('change');
            $('#new_vehicle_no, #new_customer_name, #new_mobile').val('');
            $('#new_category_id, #new_brand_id').val('').trigger('change');
            $('#new_salutation').val('');
            $('#use_vehicle_id').val('');
            $('#use_customer_id').val('');
        }

        // When an existing customer is selected
        $('#customer_id').change(function() {
            const selectedOption = $(this).find(":selected");
            const customerId = selectedOption.val();

            if (customerId) {
                const salutation = selectedOption.data('salutation');
                const customerName = selectedOption.text();
                const mobile = selectedOption.data('mobile');

                $('#new_customer_name').val(customerName);
                $('#new_mobile').val(mobile);
                $('#new_salutation').val(salutation).trigger('change');
                $('#new_vehicle_no').val('');
                $('#use_customer_id').val(customerId);
                $('#use_vehicle_id').val('');

                // Keep vehicle fields enabled when selecting from customer dropdown
                $('#new_vehicle_no, #new_category_id, #new_brand_id').prop('disabled', false);
                $('#new_customer_name').prop('disabled', true);
            } else {
                // When clearing customer selection
                $('#new_customer_name, #new_mobile').val('');
                $('#new_salutation').val('MR');
            }
        });
    });


    function openAddItemModal(job_id, vehicle_id, customer_id, customer_name, vehicle_no, service_date, job_type) {
        $('#servicesItemForm')[0].reset();

        $('#job_id').val(job_id);
        $('#vehicle_id').val(vehicle_id);
        $('#customer_id').val(customer_id);
        $('#customer_name_div').text('Customer : ' + customer_name);
        $('#vehicle_no_div').text('Vehicle No : ' + vehicle_no);

        $('#addItemModal select').select2({
            dropdownParent: $('#addItemModal')
        });

        // Set the PO ID in the modal title
        //$('#purchaseOrderNoText').text('Add Item to Purchase Order No: ' + job_id);
        $('#serviceOrderNoText').text('Add Item to Service Job No: ' + job_id);



        // Handle multiple service types
        let job_type_text = '';
        if (job_type) {
            const service_types = job_type.split(',');
            const type_labels = [];
            service_types.forEach(type => {
                switch(type.trim()) {
            case 'N':
                        type_labels.push('Normal');
                break;
            case 'M':
                        type_labels.push('Mechanical');
                break;
            case 'E':
                        type_labels.push('Estimate');
                break;
            default:
                        type_labels.push('Unknown');
                }
            });
            job_type_text = type_labels.join(', ');
        } else {
                job_type_text = 'Unknown';
        }
        $('#serviceOrderTypeText').text('Job Type: ' + job_type_text);

        // Set the checkboxes for editing service types
        if (job_type) {
            const service_types = job_type.split(',');
            $('#edit_service_type_normal').prop('checked', service_types.includes('N'));
            $('#edit_service_type_mechanical').prop('checked', service_types.includes('M'));
            $('#edit_service_type_estimate').prop('checked', service_types.includes('E'));
            
            // Show the service type editing section for pending, ongoing, or hold jobs
            const url_status = "<?php echo $this->session->userdata('filter_job_status'); ?>";
            if (url_status == '0' || url_status == '1' || url_status == '2') {
                $('#serviceTypeEditSection').show();
            }
        }



        $('#addItemModal').modal('show');
        servicePackagesTableToDatatable();
        serviceItemsTableToDatatable();
        productsTableToDatatable();
        loadJobItems();
        setPanelViible();
    }

    function setPanelViible() {

        var url_status = "<?php echo $this->session->userdata('filter_job_status'); ?>";
        if (url_status == 5) {
            //add d-none class to leftPanel div
            $('#leftPanel').addClass('d-none');
            $('#rightPanel').addClass('col-md-12');
        }
    }

    function closeAddItemModal() {
        $('#addItemModal').modal('hide');
    }

    function updateServiceType() {
        const selectedServiceTypes = $('input[name="edit_service_types[]"]:checked');
        if (selectedServiceTypes.length === 0) {
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'Please select at least one service type.'
            });
            return;
        }

        const jobId = $('#job_id').val();
        const serviceTypes = Array.from(selectedServiceTypes).map(cb => cb.value);

        console.log('Sending update request for job:', jobId, 'with service types:', serviceTypes);
        
        $.ajax({
            url: "<?php echo site_url('job/updateServiceType'); ?>",
            type: "POST",
            data: {
                jobId: jobId,
                serviceTypes: serviceTypes
            },
            success: function(response) {
                console.log('Raw response:', response);
                console.log('Response type:', typeof response);
                console.log('Response length:', response ? response.length : 'undefined');
                
                // Try to parse response if it's a string
                let parsedResponse = response;
                if (typeof response === 'string') {
                    try {
                        parsedResponse = JSON.parse(response);
                        console.log('Parsed response:', parsedResponse);
                    } catch (e) {
                        console.log('Failed to parse response as JSON:', e);
                    }
                }
                
                if (parsedResponse && parsedResponse.status === 'success') {
                    console.log('Success! Updating UI...');
                    Toastify({
                        text: parsedResponse.message,
                        className: "toastify-success",
                        duration: 5000,
                        close: true,
                        gravity: "top",
                        position: "right"
                    }).showToast();
                    
                    // Update the job type text in the header
                    const serviceTypeText = serviceTypes.map(type => {
                        switch(type) {
                            case 'N': return 'Normal';
                            case 'M': return 'Mechanical';
                            case 'E': return 'Estimate';
                            default: return 'Unknown';
                        }
                    }).join(', ');
                    $('#serviceOrderTypeText').text('Job Type: ' + serviceTypeText);
                } else {
                    console.log('Response indicates failure:', parsedResponse);
                    Toastify({
                        text: (parsedResponse && parsedResponse.message) || 'Failed to update service type',
                        className: "toastify-error",
                        duration: 5000,
                        close: true,
                        gravity: "top",
                        position: "right"
                    }).showToast();
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log('AJAX Error triggered');
                console.log('Status:', textStatus);
                console.log('Error:', errorThrown);
                console.log('Response Text:', jqXHR.responseText);
                console.log('Response Status:', jqXHR.status);
                console.log('Response Headers:', jqXHR.getAllResponseHeaders());
                
                // Try to parse the response as JSON in case it's actually a success
                try {
                    const response = JSON.parse(jqXHR.responseText);
                    console.log('Parsed error response:', response);
                    if (response.status === 'success') {
                        console.log('Actually a success response!');
                        Toastify({
                            text: response.message,
                            className: "toastify-success",
                            duration: 5000,
                            close: true,
                            gravity: "top",
                            position: "right"
                        }).showToast();
                        
                        // Update the job type text in the header
                        const serviceTypeText = serviceTypes.map(type => {
                            switch(type) {
                                case 'N': return 'Normal';
                                case 'M': return 'Mechanical';
                                case 'E': return 'Estimate';
                                default: return 'Unknown';
                            }
                        }).join(', ');
                        $('#serviceOrderTypeText').text('Job Type: ' + serviceTypeText);
                        return;
                    }
                } catch (e) {
                    console.log('Failed to parse error response as JSON:', e);
                }
                
                Toastify({
                    text: 'Failed to update service type',
                    className: "toastify-error",
                    duration: 5000,
                    close: true,
                    gravity: "top",
                    position: "right"
                }).showToast();
            }
        });
    }
</script>



<div class="modal fade" id="addItemModal" tabindex="-1" aria-labelledby="addItemModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen modal-xl">
        <div class="modal-content">
            <div class="modal-body">

                <div class="row">
                    <div class="col-md-5" id="leftPanel">
                        <div class="card">
                            <div class="card-header" style="padding: 3px 5px;">
                                <div class="col-lg-12" id="customer_name_div" style="font-weight: bold; font-size: 1.3em; color:rgb(207, 85, 14);"></div>
                                <div class="col-lg-12" id="vehicle_no_div" style="font-weight: bold; font-size: 1.3em; color:rgb(207, 85, 14);"></div>
                                
                                <!-- Service Type Editing Section -->
                                <div class="row mt-2" id="serviceTypeEditSection" style="display: none;">
                                    <div class="col-12">
                                        <div class="d-flex align-items-center">
                                            <label class="me-2 mb-0" style="font-weight: 600; color: rgb(64, 46, 231);">Edit Service Type:</label>
                                            <div class="form-check me-3">
                                                <input class="form-check-input" type="checkbox" name="edit_service_types[]" value="N" id="edit_service_type_normal">
                                                <label class="form-check-label" for="edit_service_type_normal">Normal</label>
                                            </div>
                                            <div class="form-check me-3">
                                                <input class="form-check-input" type="checkbox" name="edit_service_types[]" value="M" id="edit_service_type_mechanical">
                                                <label class="form-check-label" for="edit_service_type_mechanical">Mechanical</label>
                                            </div>
                                            <div class="form-check me-3">
                                                <input class="form-check-input" type="checkbox" name="edit_service_types[]" value="E" id="edit_service_type_estimate">
                                                <label class="form-check-label" for="edit_service_type_estimate">Estimate</label>
                                            </div>
                                            <button type="button" class="btn btn-primary btn-sm" onclick="updateServiceType()">Update</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-body">
                            <form id="servicesItemForm">
                                <input type="hidden" name="job_id" id="job_id">
                                <input type="hidden" name="vehicle_id" id="vehicle_id">
                                <input type="hidden" name="customer_id" id="customer_id">

                                <div class="row g-3">


                                    <ul class="nav nav-pills arrow-navtabs nav-success bg-light" role="tablist">
                                        <?php if(!(isset($is_manager) && $is_manager)){ ?>
                                        <li class="nav-item">
                                            <a class="nav-link active" data-bs-toggle="tab" href="#service-packages" role="tab">
                                                <span class="d-block d-sm-none"><i class="mdi mdi-home-variant"></i></span>
                                                <span class="d-none d-sm-block">SERVICE PACKAGES</span>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" data-bs-toggle="tab" href="#service-items" role="tab">
                                                <span class="d-block d-sm-none"><i class="mdi mdi-account"></i></span>
                                                <span class="d-none d-sm-block">SERVICE ITEMS</span>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" data-bs-toggle="tab" href="#product-items" role="tab">
                                                <span class="d-block d-sm-none"><i class="mdi mdi-account"></i></span>
                                                <span class="d-none d-sm-block">PRODUCTS / SPARE PARTS</span>
                                            </a>
                                        </li>
                                        <?php } ?>
                                        <li class="nav-item">
                                            <a class="nav-link <?php if(isset($is_manager) && $is_manager){ echo 'active'; } ?>" data-bs-toggle="tab" href="#other-items" role="tab">
                                                <span class="d-block d-sm-none"><i class="mdi mdi-account"></i></span>
                                                <span class="d-none d-sm-block">OTHERS</span>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" data-bs-toggle="tab" href="#outsource-parts" role="tab">
                                                <span class="d-block d-sm-none"><i class="mdi mdi-tools"></i></span>
                                                <span class="d-none d-sm-block">OUTSOURCE PARTS</span>
                                            </a>
                                        </li>
                                    </ul>


                                    <div class="card border card-border-primary">

                                        <div class="card-body">

                                            <!-- Tab panes -->
                                            <div class="tab-content text-muted">
                                                <!-- SERVICE PACKAGES (inactive/hidden for manager) -->
                                                <div class="tab-pane<?php if(!(isset($is_manager) && $is_manager)){ echo ' active'; } ?>" id="service-packages" role="tabpanel" <?php if(isset($is_manager) && $is_manager){ echo 'style=\"display:none\"'; } ?>>

                                                    <div class="row">
                                                        <div class="col-12">
                                                            <table id="servicePackagesTable" class="table table-striped table-sm table-responsive">
                                                                <thead class="table-info">
                                                                    <tr>
                                                                        <th>Package Item Name</th>
                                                                        <th class="text-end">Price</th>
                                                                        <th class="text-end" width="15%">Add</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody id="servicePackagesBody">
                                                                    <?php foreach ($servicePackages as $package) : ?>
                                                                        <tr class="package-row">
                                                                            <td class="f12"><?= $package->package_name ?></td>
                                                                            <td class="f12 text-end"><?= number_format($package->total_price, 2) ?></td>
                                                                            <td class="text-end">
                                                                                <button type="button" class="btn btn-success btn-sm" onclick="showServicePackageItems(<?= $package->id ?>, '<?= $package->package_name ?>')">
                                                                                    <i class="fa fa-eye"></i>
                                                                                </button>
                                                                                <button type="button" class="btn btn-info btn-sm" onclick="addServicePackageToJob(<?= $package->id ?>)">
                                                                                    <i class="fa fa-plus"></i>
                                                                                </button>
                                                                            </td>
                                                                        </tr>
                                                                    <?php endforeach; ?>
                                                                </tbody>
                                                            </table>


                                                        </div>
                                                    </div>
                                                </div>




                                                <!-- SERVICE ITEMS -->
                                                <div class="tab-pane" id="service-items" role="tabpanel" <?php if(isset($is_manager) && $is_manager){ echo 'style="display:none"'; } ?>>


                                                    <div class="row mb-2">
                                                        <div class="col-md-6">
                                                            <select id="servicePackageCategory" class="form-select" onchange="loadServiceItemsByCategory(this.value)">
                                                                <option value="">Select Package Category</option>
                                                                <?php foreach ($serviceItemCategory as $category) : ?>
                                                                    <option value="<?= $category->sic_id ?>"><?= $category->sci_name ?></option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <select id="serviceSection" class="form-select" onchange="loadServiceItemsByCategory(this.value)">
                                                                <option value="">Select Section</option>
                                                                <?php foreach ($employeeSections as $sec) : ?>
                                                                    <option value="<?= $sec->employeeSectionId ?>"><?= $sec->employeeSectionName ?></option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <div class="col-12" id="showServiceItemsDiv">
                                                            <table id="serviceItemsTable" class="table table-striped table-sm table-responsive">
                                                                <thead class="table-info">
                                                                    <tr>
                                                                        <th>Package Name</th>
                                                                        <th class="text-end">Price</th>
                                                                        <th class="text-end" width="10%">Add</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody id="serviceItemsBody">
                                                                    <?php foreach ($serviceItems as $item) : ?>
                                                                        <tr class="item-row">
                                                                            <td class="f12"><?= $item->name ?></td>
                                                                            <td class="f12 text-end"><?= number_format($item->price, 2) ?></td>
                                                                            <td class="text-end">
                                                                                <button type="button" class="btn btn-info btn-sm" onclick="addServiceItemToJob(<?= $item->id ?>)">
                                                                                    <i class="fa fa-plus"></i>
                                                                                </button>

                                                                            </td>
                                                                        </tr>
                                                                    <?php endforeach; ?>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- PRODUCT ITEMS -->
                                                <div class="tab-pane" id="product-items" role="tabpanel" <?php if(isset($is_manager) && $is_manager){ echo 'style="display:none"'; } ?>>

                                                    <div class="row mb-2">
                                                        <div class="col-md-4">
                                                            <select id="serviceProductBrand" class="form-select" onchange="loadServiceItemsByProductFilter(this.value)">
                                                                <option value="">Select Product Brand</option>
                                                                <?php foreach ($itemBrands as $brand) : ?>
                                                                    <option value="<?= $brand->itemBrandId ?>"><?= $brand->itemBrandName ?></option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>

                                                        <div class="col-md-4">
                                                            <select id="serviceProductCategory" class="form-select" onchange="loadServiceItemsByProductFilter(this.value)">
                                                                <option value="">Select Product Category</option>
                                                                <?php foreach ($itemCategories as $brand) : ?>
                                                                    <option value="<?= $brand->itemCategoryId ?>"><?= $brand->itemCategoryName ?></option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>

                                                        <div class="col-md-4">
                                                            <select id="serviceProductSupplier" class="form-select" onchange="loadServiceItemsByProductFilter(this.value)">
                                                                <option value="">Select Supplier</option>
                                                                <?php foreach ($suppliers as $supplier) : ?>
                                                                    <option value="<?= $supplier->supplier_id ?>"><?= $supplier->name ?></option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                    </div>


                                                    <div class="row">
                                                        <div class="col-12" id="showProductsDiv">
                                                            <!-- table responsive div-->
                                                            <div class="table-responsive">
                                                                <table id="productsTable" class="table table-striped table-sm">
                                                                    <thead class="table-info">
                                                                        <tr>
                                                                            <th>Product</th>
                                                                            <th class="text-end">Price</th>
                                                                            <th>Available</th>
                                                                            <th>Brand</th>
                                                                            <th class="text-center" width="10%">Add</th>
                                                                            <th>Unit</th>
                                                                            <th>Barcode</th>
                                                                            <th>Category</th>
                                                                            <th>Supplier</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody id="productsBody">
                                                                        <?php foreach ($products as $products) : ?>
                                                                            <tr class="products-row">
                                                                                <td class="f12">
                                                                                    <?= $products->product_name ?><br>
                                                                                   <span class="text-muted"> (<?= $products->sku ?>)</span>
                                                                                </td>
                                                                                <td class="f12 text-end"><?= number_format($products->sale_price, 2) ?></td>
                                                                                <td class="f12 text-end"><?= $products->available_stock ?></td>
                                                                                <td class="f12"><?= $products->brand_name ?></td>

                                                                                <td class="text-center">
                                                                                    <button type="button" class="btn btn-info btn-sm" onclick="addProductsToJob(<?= $products->po_item_id  ?>, <?= $products->product_id  ?>, '<?= htmlspecialchars($products->product_name, ENT_QUOTES, 'UTF-8') ?>', '<?= htmlspecialchars($products->measurement_unit, ENT_QUOTES, 'UTF-8') ?>')">
                                                                                        <i class="fa fa-plus"></i>
                                                                                    </button>
                                                                                </td>
                                                                                <td class="f12"><?= $products->measurement_unit ?></td>
                                                                                <td class="f12"><?= $products->barcode ?></td>
                                                                                <td class="f12"><?= $products->category_name ?></td>
                                                                                <td class="f12"><?= $products->supplier_name ?></td>
                                                                            </tr>
                                                                        <?php endforeach; ?>
                                                                    </tbody>
                                                                </table>
                                                            </div><!-- end table responsive div-->
                                                        </div>
                                                    </div>
                                                </div>



                                                <!-- OTHER ITEMS -->
                                                <div class="tab-pane<?php if(isset($is_manager) && $is_manager){ echo ' active'; } ?>" id="other-items" role="tabpanel">
                                                    <div class="row">
                                                        <div class="col-12" id="showProductsDiv">
                                                            <!-- table responsive div-->
                                                            <div class="table-responsive">
                                                                <table id="productsTable" class="table table-striped table-sm table-responsive">
                                                                    <thead class="table-info">
                                                                        <tr>
                                                                            <th>Description</th>
                                                                            <th class="text-center" width="10%">Add</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody id="otherBody">
                                                                            <tr class="products-row">
                                                                                <td class="f12">Labour Charges</td>
                                                                                <td class="text-center">
                                                                                    <button type="button" class="btn btn-info btn-sm" onclick="addOthersToJob('Labour')">
                                                                                        <i class="fa fa-plus"></i>
                                                                                    </button>
                                                                                </td>
                                                                            </tr>

                                                                            <tr class="products-row">
                                                                                <td class="f12">Mechanical Labour Charges</td>
                                                                                <td class="text-center">
                                                                                    <button type="button" class="btn btn-info btn-sm" onclick="addOthersToJob('Mechanical')">
                                                                                        <i class="fa fa-plus"></i>
                                                                                    </button>
                                                                                </td>
                                                                            </tr>

                                                                            <tr class="products-row">
                                                                                <td class="f12">Other Charges</td>
                                                                                <td class="text-center">
                                                                                    <button type="button" class="btn btn-info btn-sm" onclick="addOthersToJob('Other')">
                                                                                        <i class="fa fa-plus"></i>
                                                                                    </button>
                                                                                </td>
                                                                            </tr>

                                                                    </tbody>
                                                                </table>
                                                            </div><!-- end table responsive div-->
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- OUTSOURCE PARTS -->
                                                <div class="tab-pane" id="outsource-parts" role="tabpanel">
                                                    <div class="row">
                                                        <div class="col-12">
                                                            <div class="card border card-border-info">
                                                                <div class="card-header">
                                                                    <h6 class="card-title mb-0">Add Outsource Part</h6>
                                                                </div>
                                                                <div class="card-body">
                                                                    <button type="button" class="btn btn-primary" onclick="addOutsourcePartToJob()">
                                                                        <i class="fa fa-plus"></i> Add New Outsource Part
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="row mt-3">
                                                        <div class="col-12">
                                                            <div class="card border card-border-success">
                                                                <div class="card-header">
                                                                    <h6 class="card-title mb-0">Outsource Parts List</h6>
                                                                </div>
                                                                <div class="card-body">
                                                                    <div id="outsourcePartsList">
                                                                        <!-- Outsource parts will be loaded here -->
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                        </div><!-- end card-body -->
                                    </div><!-- end card -->
                                </div>

                        </div>
                        </form>
                    </div>
                    <!-- end left col -->


                    <div class="col-md-7" id="rightPanel">
                        <div class="card">
                            <div class="card-header">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6 class="card-title mb-0" id="serviceOrderNoText">Add Item</h6>
                                    </div>
                                    <div class="col-md-6 text-end">
                                        <h6 class="card-title mb-0" id="serviceOrderTypeText" style="font-weight: 600;color: rgb(64, 46, 231);">Job Type</h6>
                                    </div>
                                </div>
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


<style>
    .f12 {
        font-size: 14px !important;
        color: rgb(0, 0, 0) !important;
    }



    .swal-popup-custom {
        width: 80% !important;
        /* Adjust the percentage as needed */
        max-width: 1000px;
        /* Optional: Set a maximum width */
    }

    .form-check {
        margin-bottom: 8px;
    }

    .form-check-input:checked {
        background-color: #0d6efd;
        border-color: #0d6efd;
    }

    .form-check-label {
        font-weight: 500;
        cursor: pointer;
    }
</style>

<script>
    function servicePackagesTableToDatatable() {
        // Destroy existing DataTable if it exists
        if ($.fn.DataTable.isDataTable('#servicePackagesTable')) {
            $('#servicePackagesTable').DataTable().destroy();
        }

        // Initialize new DataTable
        $('#servicePackagesTable').DataTable({
            "lengthMenu": [
                [10, 25, 50, -1],
                [10, 25, 50, "All"]
            ],
            "searching": true,
            "responsive": true
        });
    }

    function serviceItemsTableToDatatable() {

        if ($.fn.DataTable.isDataTable('#serviceItemsTable')) {
            $('#serviceItemsTable').DataTable().destroy();
        }

        $('#serviceItemsTable').DataTable({
            "lengthMenu": [
                [10, 25, 50, -1],
                [10, 25, 50, "All"]
            ],
            "searching": true,
            "responsive": true
        });
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

    function showServicePackageItems(packageId, package_name) {
        var detailDiv = $('#div-' + packageId);
        if (packageId > 0) {
            $.ajax({
                url: "<?php echo site_url('job/showServicePackageItems') ?>",
                type: "POST",
                data: "packageId=" + packageId,
                success: function(data) {
                    Swal.fire({
                        title: package_name,
                        html: data,
                        customClass: {
                            popup: 'swal-popup-custom'
                        },
                        // You can also add other options like buttons, timer, etc.
                        showCloseButton: true
                    });
                },
                error: function(jXHR, textStatus, errorThrown) {
                    console.log(jqXHR.responseText);
                }
            });
        }
    }

    function addServicePackageToJob(packageId) {

        var button = $('button[onclick="addServicePackageToJob(' + packageId + ')"]');
        button.prop('disabled', true);
        button.html('<i class="fa fa-spinner fa-spin"></i>');

        var jobId = $('#job_id').val();
        $.ajax({
            url: "<?php echo site_url('job/addServicePackageToJob') ?>",
            type: "POST",
            data: {
                jobId: jobId,
                packageId: packageId
            },
            success: function(data) {
                loadJobItems();
                Toastify({
                    text: 'Service Package Added Successfully',
                    className: "toastify-success",
                    duration: 5000,
                    close: true,
                    gravity: "top",
                    position: "right"
                }).showToast();
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText);
            },
            complete: function() {
                setTimeout(function() {
                    button.prop('disabled', false);
                    button.html('<i class="fa fa-plus"></i>');
                }, 1000);
            }
        });
    }


    function addServiceItemToJob(itemID) {

        var button = $('button[onclick="addServiceItemToJob(' + itemID + ')"]');
        button.prop('disabled', true);
        button.html('<i class="fa fa-spinner fa-spin"></i>');
        var jobId = $('#job_id').val(); // Get job ID from input

        // Check if job ID is available
        if (!jobId) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Job ID is not available.'
            })
            button.prop('disabled', false);
            return;
        }

        $.ajax({
            url: "<?php echo site_url('job/addServiceItemToJob') ?>", // AJAX URL
            type: "POST",
            data: {
                jobId: jobId,
                itemID: itemID
            },
            success: function(data) {
                loadJobItems(); // Reload job items
                Toastify({
                    text: 'Service Item Added Successfully',
                    className: "toastify-success",
                    duration: 5000,
                    close: true,
                    gravity: "top",
                    position: "right"
                }).showToast();
            },
            error: function(jqXHR, textStatus, error) {
                console.log(jqXHR.responseText);
                Toastify({
                    text: 'Failed to add Service Item',
                    className: "toastify-error",
                    duration: 5000,
                    close: true,
                    gravity: "top",
                    position: "right"
                }).showToast();
            },
            complete: function() {
                setTimeout(function() {
                    button.prop('disabled', false);
                    button.html('<i class="fa fa-plus"></i>'); // Reset button text
                }, 1000);
            }
        });
    }




    let selectedProductId = null;
    let selectedPoItemId = null;
    // Function to open the modal for adding a product
    function addProductsToJob(po_item_id, productID, product_name, unit) {
        selectedProductId = productID;
        selectedPoItemId = po_item_id;
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


        if (quantity <= 0) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Quantity must be greater than 0.'
            });
            return;
        }

        var jobId = $('#job_id').val();

        // Check if job ID is available
        if (!jobId) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Job ID is not available.'
            })
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

        // Proceed with adding the product to the job
        $.ajax({
            url: "<?php echo site_url('job/addProductToJob') ?>", // AJAX URL
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
                loadJobItems();
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


    function addOthersToJob(model) {
    const jobId = $('#job_id').val();

    if (!jobId) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Job ID is not available.'
        });
        return;
    }

    // Set defaults based on model
    $('#others_model_type').val(model);

    if (model === 'Labour') {
        $('#others_description').val('Labour Cost').prop('readonly', true);
    } else if (model === 'Mechanical') {
        $('#others_description').val('Mechanical Labour Cost').prop('readonly', true);
    } else {
        $('#others_description').val('').prop('readonly', false);
    }

    $('#others_cost').val('');
    $('#othersModal').modal('show');
}


function submitOthersItem() {
    const jobId = $('#job_id').val();
    const model = $('#others_model_type').val();
    const description = $('#others_description').val().trim();
    const cost = parseFloat($('#others_cost').val());

    if (!description) {
        Swal.fire('Error', 'Description is required.', 'error');
        return;
    }

    if (isNaN(cost) || cost <= 0) {
        Swal.fire('Error', 'Valid cost is required.', 'error');
        return;
    }

    $.ajax({
        url: "<?php echo site_url('job/addOthersItemToJob'); ?>",
        type: "POST",
        data: {
            jobId: jobId,
            model: model,
            description: description,
            amount: cost
        },
        success: function (response) {
            $('#othersModal').modal('hide');
            loadJobItems();
            Toastify({
                text: 'Service Item Added Successfully',
                className: "toastify-success",
                duration: 5000,
                close: true,
                gravity: "top",
                position: "right"
            }).showToast();
        },
        error: function (jqXHR) {
            Toastify({
                text: 'Failed to add Service Item',
                className: "toastify-error",
                duration: 5000,
                close: true,
                gravity: "top",
                position: "right"
            }).showToast();
        }
    });
}





    function loadJobItems() {
        var jobId = $('#job_id').val();

        $.ajax({
            url: "<?php echo site_url('job/loadJobItems') ?>",
            type: "POST",
            data: {
                jobId: jobId
            },
            success: function(data) {
                $('#itemList').html(data);
                // Also load outsource parts
                loadOutsourceParts(jobId);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText);
            }
        });
    }

    function loadServiceItemsByCategory(categoryId) {
        var sectionId = $('#serviceSection').val();
        var categoryId = $('#servicePackageCategory').val();

        $.ajax({
            url: "<?php echo site_url('job/loadServiceItemsFilterListByCategory') ?>",
            type: "POST",
            data: {
                categoryId: categoryId,
                sectionId: sectionId
            },
            success: function(data) {
                $('#showServiceItemsDiv').html(data);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText);
            }
        });
    }

    function loadServiceItemsByProductFilter() {
        var categoryId = $('#serviceProductCategory').val();
        var brandId = $('#serviceProductBrand').val();
        var supplierId = $('#serviceProductSupplier').val();

        $.ajax({
            url: "<?php echo site_url('job/loadServiceProductsFilterListByProduct') ?>",
            type: "POST",
            data: {
                categoryId: categoryId,
                brandId: brandId,
                supplierId: supplierId
            },
            success: function(data) {
                $('#showProductsDiv').html(data);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText);
            }
        });
    }


    function deleteJobItem(id, package_id, job_id) {
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
                    url: '<?= base_url('job/deleteJobItem') ?>',
                    type: 'POST',
                    data: {
                        id: id,
                        package_id: package_id,
                        job_id: job_id
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            loadJobItems();
                            Toastify({
                                text: response.message,
                                className: "toastify-success",
                                duration: 5000,
                                close: true,
                                gravity: "top",
                                position: "right"
                            }).showToast();
                        } else {
                            Swal.fire(
                                'Error!',
                                response.message,
                                'error'
                            );
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error(error);
                        Swal.fire('Error!', 'Failed to delete item.', 'error');
                    }
                });
            }
        });
    }

    // Outsource Parts Functions
    function loadOutsourceParts(jobId) {
        if (!jobId) return;
        
        $.ajax({
            url: '<?= base_url('job/loadOutsourceParts') ?>',
            type: 'POST',
            data: { jobId: jobId },
            success: function(data) {
                $('#outsourcePartsList').html(data);
            },
            error: function(xhr, status, error) {
                console.error('Error loading outsource parts:', error);
            }
        });
    }

    function deleteOutsourcePart(partId) {
        var jobId = $('#job_id').val();
        
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
                    url: '<?= base_url('job/deleteOutsourcePart') ?>',
                    type: 'POST',
                    data: {
                        partId: partId,
                        jobId: jobId
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            loadOutsourceParts(jobId);
                            loadJobItems();
                            Toastify({
                                text: response.message,
                                className: "toastify-success",
                                duration: 5000,
                                close: true,
                                gravity: "top",
                                position: "right"
                            }).showToast();
                        } else {
                            Swal.fire(
                                'Error!',
                                response.message,
                                'error'
                            );
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error(error);
                        Swal.fire('Error!', 'Failed to delete outsource part.', 'error');
                    }
                });
            }
        });
    }

    // Outsource Parts Functions - Modal Approach
    function addOutsourcePartToJob() {
        const jobId = $('#job_id').val();

        if (!jobId) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Job ID is not available.'
            });
            return;
        }

        // Clear the form
        $('#outsource_item_name').val('');
        $('#outsource_purchased_from').val('');
        $('#outsource_purchased_price').val('');
        $('#outsource_invoice_price').val('');
        
        $('#outsourcePartModal').modal('show');
    }

    function submitOutsourcePart() {
        const jobId = $('#job_id').val();
        const itemName = $('#outsource_item_name').val().trim();
        const purchasedFrom = $('#outsource_purchased_from').val().trim();
        const purchasedPrice = parseFloat($('#outsource_purchased_price').val());
        const invoicePrice = parseFloat($('#outsource_invoice_price').val());

        if (!itemName) {
            Swal.fire('Error', 'Item name is required.', 'error');
            return;
        }

        if (!purchasedFrom) {
            Swal.fire('Error', 'Purchased from is required.', 'error');
            return;
        }

        if (isNaN(purchasedPrice) || purchasedPrice <= 0) {
            Swal.fire('Error', 'Valid purchased price is required.', 'error');
            return;
        }

        if (isNaN(invoicePrice) || invoicePrice <= 0) {
            Swal.fire('Error', 'Valid invoice price is required.', 'error');
            return;
        }

        $.ajax({
            url: '<?= base_url('job/addOutsourcePartToJob') ?>',
            type: 'POST',
            data: {
                jobId: jobId,
                itemName: itemName,
                purchasedFrom: purchasedFrom,
                purchasedPrice: purchasedPrice,
                invoicePrice: invoicePrice
            },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#outsourcePartModal').modal('hide');
                    loadOutsourceParts(jobId);
                    loadJobItems();
                    
                    Toastify({
                        text: response.message,
                        className: "toastify-success",
                        duration: 5000,
                        close: true,
                        gravity: "top",
                        position: "right"
                    }).showToast();
                } else {
                    Swal.fire(
                        'Error!',
                        response.message,
                        'error'
                    );
                }
            },
            error: function(xhr, status, error) {
                console.error(error);
                Swal.fire('Error!', 'Failed to add outsource part.', 'error');
            }
        });
    }
</script>


<!-- Others Modal -->
<div class="modal fade" id="othersModal" tabindex="-1" aria-labelledby="othersModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="othersModalLabel">Add Service Item</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="others_model_type">
        
        <div class="mb-3">
          <label for="others_description" class="form-label">Description</label>
          <input type="text" class="form-control" id="others_description" placeholder="Enter description">
        </div>

        <div class="mb-3">
          <label for="others_cost" class="form-label">Cost</label>
          <input type="number" class="form-control" id="others_cost" placeholder="Enter cost" min="0" step="0.01">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" id="submitOthersBtn" class="btn btn-primary" onclick="submitOthersItem()">Add Item</button>
      </div>
    </div>
  </div>
</div>

<!-- Outsource Parts Modal -->
<div class="modal fade" id="outsourcePartModal" tabindex="-1" aria-labelledby="outsourcePartModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="outsourcePartModalLabel">Add Outsource Part</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="outsource_item_name" class="form-label">Item Name *</label>
                    <input type="text" class="form-control" id="outsource_item_name" placeholder="Enter item name" required>
                </div>
                
                <div class="mb-3">
                    <label for="outsource_purchased_from" class="form-label">Purchased From *</label>
                    <input type="text" class="form-control" id="outsource_purchased_from" placeholder="Enter supplier name" required>
                </div>
                
                <div class="mb-3">
                    <label for="outsource_purchased_price" class="form-label">Purchased Price *</label>
                    <input type="number" step="0.01" class="form-control" id="outsource_purchased_price" placeholder="Enter purchased price" required>
                </div>
                
                <div class="mb-3">
                    <label for="outsource_invoice_price" class="form-label">Invoice Price *</label>
                    <input type="number" step="0.01" class="form-control" id="outsource_invoice_price" placeholder="Enter invoice price" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="submitOutsourcePart()">Add Outsource Part</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>





<!-- Modal for Quantity Input (Product) -->
<div class="modal fade" id="quantityModal" tabindex="-1" aria-labelledby="quantityModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered flip">
        <div class="modal-content" style="background-color:rgb(194, 194, 194);">
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