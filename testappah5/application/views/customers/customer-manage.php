<div class="row">
<div class="col-12">
<div class="page-title-box d-sm-flex align-items-center justify-content-between">
<h4 class="mb-sm-0">Customers</h4>

<div class="page-title-right">
<ol class="breadcrumb m-0">
<li class="breadcrumb-item"><a href="<?php echo base_url(); ?>">Home</a></li>
<li class="breadcrumb-item active">Customers</li>
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
            <input type="date" name="sdate" id="sdate" class="form-control" value="<?= date('Y-m-d', strtotime('-2 month')); ?>">
                </div>
                <div class="col-md-2">
                    <input type="date" name="edate" id="edate" class="form-control" value="<?= date('Y-m-d'); ?>">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary" onclick="loadCustomers();" style="width: 100%;">Load Customers</button>
                </div>

                <div class="col-md-4">
                    <input type="text" name="searchFilter" id="searchFilter" class="form-control" placeholder="Customer name, mobile, email" oninput="filterCustomers();">
                </div>

                <div class="col-md-2">
<button type="button" class="btn btn-success btn-label waves-effect waves-light" style="width: 100%;" data-bs-toggle="modal" data-bs-target="#exampleModalgrid"><i class="fa fa-plus label-icon align-middle fs-16 me-2"></i> New Customers</button>
                </div>
            </div>   
</div>   
            
<div class="card-body">

<?php if ($this->session->flashdata('success')): ?>
<div class="alert alert-success"><?php echo $this->session->flashdata('success'); ?></div>
<?php endif; ?>

<div id="customersTable"></div>
</div>
</div>
</div>
<!--end col-->
</div>





<div class="modal fade zoomIn" id="exampleModalgrid" data-bs-backdrop="static" tabindex="-1" aria-labelledby="exampleModalgridLabel" aria-modal="true">
<div class="modal-dialog modal-lg modal-dialog-centered">
<div class="modal-content">
<div class="modal-header">
<h5 class="modal-title" id="exampleModalgridLabel">Create New Customer</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
    <form id="createCustomerForm">
        <div class="alert alert-danger" id="createError" style="display:none;"></div>
        <div class="row g-3">

            <!-- Salutation -->
            <div class="col-md-2">
                <label for="salutation" class="form-label">Salutation *</label>
                <select class="form-select" id="salutation" name="salutation" required>
                    <option value="MR">Mr.</option>
                    <option value="MRS">Mrs.</option>
                    <option value="MS">Ms.</option>
                    <option value="DR">Dr.</option>
                </select>
            </div>

            <!-- Name -->
            <div class="col-md-7">
                <label for="name" class="form-label">Name *</label>
                <input type="text" class="form-control" id="name" name="name" placeholder="Enter Name" required>
            </div>

            <!-- NIC -->
            <div class="col-md-3">
                <label for="nic" class="form-label">NIC</label>
                <input type="text" class="form-control" id="nic" name="nic" placeholder="Enter NIC" maxlength="12" pattern="[0-9]{9}[V]">
            </div>

            <!-- Email -->
            <div class="col-md-4">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" placeholder="Enter Email" maxlength="50" pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}">
            </div>

            <!-- Mobile Number -->
            <div class="col-md-4">
                <label for="mobile" class="form-label">Mobile *</label>
                <input type="text" class="form-control" id="mobile" name="mobile" placeholder="Enter Mobile Number" maxlength="10" required>
            </div>

            <!-- Home Number -->
            <div class="col-md-4">
                <label for="home" class="form-label">Extra Contact Number</label>
                <input type="text" class="form-control" id="mobile2" name="mobile2" maxlength="10" placeholder="Enter Extra Contact Number">
            </div>

            <!-- Address: No & Street -->
            <div class="col-md-3">
                <label for="address_no" class="form-label">No</label>
                <input type="text" class="form-control" id="address_no" name="address_no" placeholder="House No">
            </div>
            <div class="col-md-9">
                <label for="street" class="form-label">Street</label>
                <input type="text" class="form-control" id="street" name="street" placeholder="Enter Street">
            </div>

            <!-- Province -->
<div class="col-md-4">
    <label for="province" class="form-label">Province</label>
    <select class="form-select" id="province" name="province">
        <option value="">Select Province</option>
        <?php
        foreach ($provinces as $province) {
            echo '<option value="' . $province->id . '">' . $province->name_en . ' - ' . $province->name_si . '</option>';
        }
        ?>
    </select>
</div>

<!-- District -->
<div class="col-md-4">
    <label for="district" class="form-label">District</label>
    <select class="form-select" id="district" name="district">
        <option value="">Select District</option>
    </select>
</div>

<!-- City -->
<div class="col-md-4">
    <label for="city" class="form-label">City</label>
    <select class="form-select" id="city" name="city">
        <option value="">Select City</option>
    </select>
</div>


            <div class="col-md-3">
                <label for="postal_code" class="form-label">Postal Code</label>
                <input type="text" class="form-control" id="postal_code" name="postal_code" placeholder="Enter Postal Code">
            </div>
        </div>

        <div class="row mt-2">
            <div class="col-md-12">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" placeholder="Additional details"></textarea>
            </div>

            <div class="col-lg-12 mt-5">
                <div class="hstack gap-2 justify-content-end">
                    <button type="button" class="btn btn-link link-danger fw-medium shadow-none" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-success" id="saveCustomerBtn">Save Customer</button>
                </div>
            </div>

        </div>
    </form>
</div>


</div>
</div>
</div>



<div class="modal fade zoomIn" id="editCustomerModal" data-bs-backdrop="static" tabindex="-1" aria-labelledby="editCustomerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editCustomerModalLabel">Edit Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editCustomerForm">
                    <input type="hidden" id="edit_customer_id" name="customer_id">
                    <div class="alert alert-danger" id="updateError" style="display:none;"></div>
                    
                    <div class="row g-3">
                        <!-- Salutation -->
                        <div class="col-md-2">
                            <label for="edit_salutation" class="form-label">Salutation *</label>
                            <select class="form-select" id="edit_salutation" name="salutation" required>
                                <option value="MR">Mr.</option>
                                <option value="MRS">Mrs.</option>
                                <option value="MS">Ms.</option>
                                <option value="DR">Dr.</option>
                            </select>
                        </div>

                        <!-- Name -->
                        <div class="col-md-7">
                            <label for="edit_name" class="form-label">Name *</label>
                            <input type="text" class="form-control" id="edit_name" name="name" required>
                        </div>

                        <!-- NIC -->
                        <div class="col-md-3">
                            <label for="edit_nic" class="form-label">NIC</label>
                            <input type="text" class="form-control" id="edit_nic" name="nic" maxlength="12" pattern="[0-9]{9}[V]">
                        </div>

                        <!-- Email -->
                        <div class="col-md-4">
                            <label for="edit_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="edit_email" name="email" maxlength="50" pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}">
                        </div>

                        <!-- Mobile Number -->
                        <div class="col-md-4">
                            <label for="edit_mobile" class="form-label">Mobile *</label>
                            <input type="text" class="form-control" id="edit_mobile" name="mobile" maxlength="10" pattern="[0-9]{10}" required>
                        </div>

                        <!-- Extra Contact Number -->
                        <div class="col-md-4">
                            <label for="edit_mobile2" class="form-label">Extra Contact Number</label>
                            <input type="text" class="form-control" id="edit_mobile2" name="mobile2" maxlength="10" pattern="[0-9]{10}">
                        </div>

                        <!-- Address Fields -->
                        <div class="col-md-3">
                            <label for="edit_address_no" class="form-label">No</label>
                            <input type="text" class="form-control" id="edit_address_no" name="address_no">
                        </div>

                        <div class="col-md-9">
                            <label for="edit_street" class="form-label">Street</label>
                            <input type="text" class="form-control" id="edit_street" name="street">
                        </div>

                        <!-- Province -->
                        <div class="col-md-4">
                            <label for="edit_province" class="form-label">Province</label>
                            <select class="form-select" id="edit_province" name="province" required>
                                <option value="">Select Province</option>
                                <?php foreach ($provinces as $province) : ?>
                                    <option value="<?= $province->id ?>"><?= $province->name_en ?> - <?= $province->name_si ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- District -->
                        <div class="col-md-4">
                            <label for="edit_district" class="form-label">District</label>
                            <select class="form-select" id="edit_district" name="district" required>
                                <option value="">Select District</option>
                            </select>
                        </div>

                        <!-- City -->
                        <div class="col-md-4">
                            <label for="edit_city" class="form-label">City</label>
                            <select class="form-select" id="edit_city" name="city" required>
                                <option value="">Select City</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                <label for="postal_code" class="form-label">Postal Code</label>
                <input type="text" class="form-control" id="edit_postal_code" name="edit_postal_code" placeholder="Enter Postal Code">
            </div>

                    </div>

                    <div class="modal-footer mt-3">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-success" id="updateCustomerBtn">Update Customer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

</div>
</div>
</div>
<!--end row-->


<script type="text/javascript">

$('#table1').DataTable({
    "lengthMenu": [
        [150, 250, 500, -1],
        [150, 250, 500, "All"]
    ],
    "searching": true,
    "responsive": true
});

function loadCustomers(){
        let sdate = $('#sdate').val();
        let edate = $('#edate').val();
        $.ajax({
            url: "<?php echo site_url('customer/loadCustomers') ?>",
            type: "POST",
            data: "sdate=" + sdate + "& edate=" + edate,
            success: function(data) {
                $('#customersTable').html(data);
            },
            error: function(jXHR, textStatus, errorThrown) {
                console.log(jXHR.responseText);
            }
        });
    }

function filterCustomers() {
    let searchFilter = $('#searchFilter').val().toLowerCase();
    $.ajax({
            url: "<?php echo site_url('customer/filterCustomers') ?>",
            type: "POST",
            data: "searchFilter=" + searchFilter,
            success: function(data) {
                $('#customersTable').html(data);
            },
            error: function(jXHR, textStatus, errorThrown) {
                console.log(jXHR.responseText);
            }
        });
}  

$(document).ready(function () {
    loadCustomers();

    $('#exampleModalgrid select').select2({
            dropdownParent: $('#exampleModalgrid') // ensures it displays properly inside Bootstrap modal
        });

        $('#editCustomerModal select').select2({
            dropdownParent: $('#editCustomerModal') // ensures it displays properly inside Bootstrap modal
        });
    
    // Load Districts when Province is selected
    $('#province').change(function () {
        var provinceId = $(this).val();
        $('#district').html('<option value="">Loading...</option>'); // Show loading
        $('#city').html('<option value="">Select City</option>'); // Reset city
        if (provinceId !== "") {
            $.ajax({
                url: "<?= base_url('customer/get_districts'); ?>",
                type: "POST",
                data: { province_id: provinceId },
                dataType: "json",
                success: function (response) {
                    $('#district').html('<option value="">Select District</option>'); // Reset options
                    $.each(response, function (index, district) {
                        $('#district').append('<option value="' + district.id + '">' + district.name_en + ' - ' + district.name_si + '</option>');
                    });
                }
            });
        } else {
            $('#district').html('<option value="">Select District</option>');
        }
    });

    // Load Cities when District is selected
    $('#district').change(function () {
        var districtId = $(this).val();
        $('#city').html('<option value="">Loading...</option>'); // Show loading

        if (districtId !== "") {
            $.ajax({
                url: "<?= base_url('customer/get_cities'); ?>",
                type: "POST",
                data: { district_id: districtId },
                dataType: "json",
                success: function (response) {
                    $('#city').html('<option value="">Select City</option>'); // Reset options
                    $.each(response, function (index, city) {
                        $('#city').append('<option value="' + city.id + '">' + city.name_en + ' - ' + city.name_si + '</option>');
                    });
                }
            });
        } else {
            $('#city').html('<option value="">Select City</option>');
        }
    });


    // Load Districts when Edit Modal Province is selected
    $('#edit_province').change(function () {
        var provinceId = $(this).val();
        $('#edit_district').html('<option value="">Loading...</option>'); // Show loading
        $('#edit_city').html('<option value="">Select City</option>'); // Reset city

        if (provinceId !== "") {
            $.ajax({
                url: "<?= base_url('customer/get_districts'); ?>",
                type: "POST",
                data: { province_id: provinceId },
                dataType: "json",
                success: function (response) {
                    $('#edit_district').html('<option value="">Select District</option>'); // Reset options
                    $.each(response, function (index, district) {
                        $('#edit_district').append('<option value="' + district.id + '">' + district.name_en + ' - ' + district.name_si + '</option>');
                    });
                }
            });
        } else {
            $('#edit_district').html('<option value="">Select District</option>');
        }
    });

    // Load cities when edit modal district select
    $('#edit_district').change(function () {
        var districtId = $(this).val();
        $('#edit_city').html('<option value="">Loading...</option>'); // Show loading

        if (districtId !== "") {
            $.ajax({
                url: "<?= base_url('customer/get_cities'); ?>",
                type: "POST",
                data: { district_id: districtId },
                dataType: "json",
                success: function (response) {
                    $('#edit_city').html('<option value="">Select City</option>'); // Reset options
                    $.each(response, function (index, city) {
                        $('#edit_city').append('<option value="' + city.id + '">' + city.name_en + ' - ' + city.name_si + '</option>');
                    });
                }
            });
        } else {
            $('#edit_city').html('<option value="">Select City</option>');
        }
    });
});


$(document).ready(function () {
    $('#saveCustomerBtn').click(function (e) {
        e.preventDefault();
        
        $.ajax({
            url: "<?= base_url('saveCustomer'); ?>",
            method: "POST",
            data: $('#createCustomerForm').serialize(),
            dataType: 'json',
            success: function (response) {
                if (response.status === 'success') {
                    $('#exampleModalgrid').modal('hide');
                    loadCustomers();
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
    text: 'Found some errors, Please see error logs',
    className: "toastify-error",
    duration: 5000,
    close: true,
    gravity: "top",
    position: "right"
}).showToast();

                }
            },
            error: function () {
                Swal.fire('Error!', 'Something went wrong. Please try again.', 'error');
            }
        });
    });
});


$(document).ready(function () {
    
    $(document).on('click', '.edit-customer-btn', function () {
    let customerId = $(this).data('id');

    $('#edit_customer_id').val(customerId);
    $('#edit_salutation').val($(this).data('salutation'));
    $('#edit_name').val($(this).data('name'));
    $('#edit_nic').val($(this).data('nic'));
    $('#edit_email').val($(this).data('email'));
    $('#edit_mobile').val($(this).data('mobile'));
    $('#edit_mobile2').val($(this).data('mobile2'));
    $('#edit_address_no').val($(this).data('address_no'));
    $('#edit_street').val($(this).data('street'));
    $('#edit_postal_code').val($(this).data('postal_code'));
    $('#edit_description').val($(this).data('description'));


    let provinceId = $(this).data('province');
    $('#edit_province').val(provinceId).trigger('change');

    
    loadDistricts(provinceId, $(this).data('district'));

    setTimeout(() => {
        loadCities($(this).data('district'), $(this).data('city'));
    }, 500);
});



$('#updateCustomerBtn').click(function (e) {
    e.preventDefault();
    $.ajax({
        url: "<?php echo base_url('updateCustomer'); ?>",
        type: "POST",
        data: $('#editCustomerForm').serialize(),
        dataType: "json",
        success: function (response) {
            if (response.status === 'success') {
                $('#editCustomerModal').modal('hide');
                loadCustomers();
                Toastify({
                    text: response.message,
                    className: "toastify-success",
                    duration: 5000,
                    close: true,
                    gravity: "top",
                    position: "right"
                }).showToast();
                $('#updateError').hide();
            } else {
                $('#updateError').html(response.message).show();
                Toastify({
                    text: 'Found some errors, please see above.',
                    className: "toastify-error",
                    duration: 5000,
                    close: true,
                    gravity: "top",
                    position: "right"
                }).showToast();
            }
        },
        error: function (xhr) {
            console.log(xhr.responseText);
            Swal.fire('Error!', 'Something went wrong. Please try again.', 'error');
        }
    });
});


});


function loadDistricts(provinceId, selectedDistrict = null) {
    $.ajax({
        url: "<?php echo base_url('customer/getDistrictsByProvince'); ?>",
        type: "POST",
        data: { province_id: provinceId },
        dataType: "json",
        success: function (response) {
            let districtDropdown = $('#edit_district');
            districtDropdown.empty().append('<option value="">Select District</option>');

            $.each(response, function (index, district) {
                let selected = (district.id == selectedDistrict) ? 'selected' : '';
                districtDropdown.append('<option value="' + district.id + '" ' + selected + '>' + district.name_en + '</option>');
            });

            if (selectedDistrict) {
                districtDropdown.trigger('change');
            }
        }
    });
}

function loadCities(districtId, selectedCity = null) {
    $.ajax({
        url: "<?php echo base_url('customer/getCitiesByDistrict'); ?>",
        type: "POST",
        data: { district_id: districtId },
        dataType: "json",
        success: function (response) {
            let cityDropdown = $('#edit_city');
            cityDropdown.empty().append('<option value="">Select City</option>');

            $.each(response, function (index, city) {
                let selected = (city.id == selectedCity) ? 'selected' : '';
                cityDropdown.append('<option value="' + city.id + '" ' + selected + '>' + city.name_en + '</option>');
            });
        }
    });
}

$('#edit_province').change(function () {
    let provinceId = $(this).val();
    loadDistricts(provinceId);
});

$('#edit_district').change(function () {
    let districtId = $(this).val();
    loadCities(districtId);
});

</script>