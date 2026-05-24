<div class="row">
<div class="col-12">
<div class="page-title-box d-sm-flex align-items-center justify-content-between">
<h4 class="mb-sm-0">Suppliers</h4>

<div class="page-title-right">
<ol class="breadcrumb m-0">
<li class="breadcrumb-item"><a href="<?php echo base_url(); ?>">Home</a></li>
<li class="breadcrumb-item active">Suppliers</li>
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
<div class="card-header align-items-center d-flex">
<h4 class="card-title mb-0 flex-grow-1">All Suppliers</h4>
<div class="flex-shrink-0">
<button type="button" class="btn btn-success btn-label waves-effect waves-light" data-bs-toggle="modal" data-bs-target="#exampleModalgrid"><i class="fa fa-plus label-icon align-middle fs-16 me-2"></i> Create New Supplier</button>
</div>
</div><!-- end card header -->
<div class="card-body">

<table id="table1" class="table table-striped table-responsive">
    <thead class="table-info">
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>NIC</th>
            <th>Contacts</th>
            <th>Address</th>
            <th>Registered</th>
            <th>Updated</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($suppliers as $key => $supplier) { ?>
            <tr>
                <td><?= $supplier->supplier_id; ?></td>
                <td><?= $supplier->salutation; ?>. <?= $supplier->name; ?></td>
                <td><?= $supplier->nic ? $supplier->nic : '-'; ?></td>
                <td>
                    <?= $supplier->email ? $supplier->email . '<br>' : ''; ?>
                    <?= $supplier->mobile; ?>
                    <?= $supplier->mobile_2 ? '<br>' . $supplier->mobile_2 : ''; ?>
                </td>
                <td>
                    <?= $supplier->address_no . ', ' . $supplier->street; ?><br>
                    <?= $supplier->city_name . ', ' . $supplier->district_name; ?><br>
                    <?= $supplier->province_name . ' (' . $supplier->postal_code . ')'; ?>
                </td>
                <td><?= date('Y-m-d', strtotime($supplier->created_at)); ?></td>
                <td><?php if($supplier->updated_at){ 
                    echo date('Y-m-d', strtotime($supplier->updated_at)); 
                }
                ?></td>
                <td>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-secondary edit-supplier-btn"
                            data-id="<?= $supplier->supplier_id; ?>"
                            data-salutation="<?= $supplier->salutation; ?>"
                            data-name="<?= $supplier->name; ?>"
                            data-nic="<?= $supplier->nic; ?>"
                            data-email="<?= $supplier->email; ?>"
                            data-mobile="<?= $supplier->mobile; ?>"
                            data-mobile2="<?= $supplier->mobile_2; ?>"
                            data-address_no="<?= $supplier->address_no; ?>"
                            data-street="<?= $supplier->street; ?>"
                            data-province="<?= $supplier->province_id; ?>"
                            data-district="<?= $supplier->district_id; ?>"
                            data-city="<?= $supplier->city_id; ?>"
                            data-postal_code="<?= $supplier->postal_code; ?>"
                            data-description="<?= $supplier->description; ?>"
                            data-bs-toggle="modal"
                            data-bs-target="#editSupplierModal">
                            <i class="fa fa-pencil"></i> Edit
                        </button>
                    </div>
                </td>
            </tr>
        <?php } ?>
    </tbody>
</table>

</div>
</div>
</div>
<!--end col-->
</div>





<div class="modal fade zoomIn" id="exampleModalgrid" tabindex="-1" data-bs-backdrop="static" aria-labelledby="exampleModalgridLabel" aria-modal="true">
<div class="modal-dialog modal-lg modal-dialog-centered">
<div class="modal-content">
<div class="modal-header">
<h5 class="modal-title" id="exampleModalgridLabel">Create New Supplier</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
    <div id="createModalBody"></div>

    
    <form id="createSupplierForm">
        <div class="alert alert-danger" id="createError" style="display:none;"></div>
        <div class="row g-3">

            <!-- Salutation -->
            <div class="col-md-2">
                <label for="salutation" class="form-label">Salutation *</label>
                <select class="form-select" id="salutation" name="salutation" required>
                    <option value="MR">Mr.</option>
                    <option value="MRS">Mrs.</option>
                    <option value="MS">Ms.</option>
                    <option value="CMP">Company</option>
                    <option value="SYST">System.</option>
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
                <input type="text" class="form-control" id="nic" name="nic" placeholder="Enter NIC">
            </div>

            <!-- Email -->
            <div class="col-md-4">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" placeholder="Enter Email">
            </div>

            <!-- Mobile Number -->
            <div class="col-md-4">
                <label for="mobile" class="form-label">Mobile *</label>
                <input type="text" class="form-control" id="mobile" name="mobile" placeholder="Enter Mobile Number" required>
            </div>

            <!-- Home Number -->
            <div class="col-md-4">
                <label for="home" class="form-label">Extra Contact Number</label>
                <input type="text" class="form-control" id="mobile2" name="mobile2" placeholder="Enter Extra Contact Number">
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
    <select class="form-select select2" id="province" name="province">
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
    <select class="form-select select2" id="district" name="district">
        <option value="">Select District</option>
    </select>
</div>

<!-- City -->
<div class="col-md-4">
    <label for="city" class="form-label">City</label>
    <select class="form-select select2" id="city" name="city">
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
                    <button type="button" class="btn btn-success" id="saveSupplierBtn">Save Supplier</button>
                </div>
            </div>

        </div>
    </form>
</div>


</div>
</div>
</div>



<div class="modal fade zoomIn" id="editSupplierModal" tabindex="-1" data-bs-backdrop="static" aria-labelledby="editSupplierModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editSupplierModalLabel">Edit Supplier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editSupplierForm">
                    <input type="hidden" id="edit_supplier_id" name="supplier_id">
                    <div class="alert alert-danger" id="updateError" style="display:none;"></div>
                    
                    <div class="row g-3">
                        <!-- Salutation -->
                        <div class="col-md-2">
                            <label for="edit_salutation" class="form-label">Salutation *</label>
                            <select class="form-select" id="edit_salutation" name="salutation" required>
                                <option value="MR">Mr.</option>
                                <option value="MRS">Mrs.</option>
                                <option value="MS">Ms.</option>
                                <option value="CMP">Company</option>
                                <option value="SYST">System.</option>
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
                            <input type="text" class="form-control" id="edit_nic" name="nic">
                        </div>

                        <!-- Email -->
                        <div class="col-md-4">
                            <label for="edit_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="edit_email" name="email">
                        </div>

                        <!-- Mobile Number -->
                        <div class="col-md-4">
                            <label for="edit_mobile" class="form-label">Mobile *</label>
                            <input type="text" class="form-control" id="edit_mobile" name="mobile" required>
                        </div>

                        <!-- Extra Contact Number -->
                        <div class="col-md-4">
                            <label for="edit_mobile2" class="form-label">Extra Contact Number</label>
                            <input type="text" class="form-control" id="edit_mobile2" name="mobile2">
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
                            <select class="form-select" id="edit_province" name="edit_province" required>
                                <option value="">Select Province</option>
                                <?php foreach ($provinces as $province) : ?>
                                    <option value="<?= $province->id ?>"><?= $province->name_en ?> - <?= $province->name_si ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- District -->
                        <div class="col-md-4">
                            <label for="edit_district" class="form-label">District</label>
                            <select class="form-select" id="edit_district" name="edit_district" required>
                                <option value="">Select District</option>
                            </select>
                        </div>

                        <!-- City -->
                        <div class="col-md-4">
                            <label for="edit_city" class="form-label">City</label>
                            <select class="form-select" id="edit_city" name="edit_city" required>
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
                        <button type="button" class="btn btn-success" id="updateSupplierBtn">Update Supplier</button>
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
searching: true,
responsive: true
});


$(document).ready(function () {    

    $('#province').change(function () {
        var provinceId = $(this).val();
        $('#district').html('<option value="">Loading...</option>');
        $('#city').html('<option value="">Select City</option>');
        if (provinceId !== "") {
            $.ajax({
                url: "<?= base_url('supplier/get_districts'); ?>",
                type: "POST",
                data: { province_id: provinceId },
                dataType: "json",
                success: function (response) {
                    $('#district').html('<option value="">Select District</option>');
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
                url: "<?= base_url('supplier/get_cities'); ?>",
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
                url: "<?= base_url('supplier/get_districts'); ?>",
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
                url: "<?= base_url('supplier/get_cities'); ?>",
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

    $('#saveSupplierBtn').click(function (e) {
        e.preventDefault();
        $.ajax({
            url: "<?= base_url('saveSupplier'); ?>",
            method: "POST",
            data: $('#createSupplierForm').serialize(),
            dataType: 'json',
            success: function (response) {
                if (response.status === 'success') {
                    Swal.fire('Saved!', response.message, 'success').then(() => {
                        location.reload();
                    });
                } else {
                    $('#createError').html(response.message).show();
                }
            },
            error: function () {
                Swal.fire('Error!', 'Something went wrong. Please try again.', 'error');
            }
        });
    });

    
    
    $(document).on('click', '.edit-supplier-btn', function () {
    let supplierId = $(this).data('id');
    // Set basic form fields
    $('#edit_supplier_id').val(supplierId);
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
    $('#edit_description').val($(this).data('description'));
    let provinceId = $(this).data('province');
    let districtId = $(this).data('district');
    let cityId = $(this).data('city');

    // Initialize Select2 when modal is shown
    $('#editSupplierModal').on('shown.bs.modal', function () {
        // Initialize Select2 for all dropdowns
        $('#edit_salutation, #edit_province, #edit_district, #edit_city').select2({
            dropdownParent: $('#editSupplierModal') // Ensure dropdown appears inside modal
        });

        // Set province and trigger district/city load
        $('#edit_province').val(provinceId).trigger('change');
        // Load districts and cities with a slight delay (if needed)
        setTimeout(() => {
            loadDistricts(provinceId, districtId);
            loadCities(districtId, cityId);
        }, 100);

        setTimeout(() => {
            $('#edit_city').val(cityId).trigger('change');
        }, 950);

    });

    // Manually show the modal (if not using data-bs-toggle)
    $('#editSupplierModal').modal('show');
});



    $('#updateSupplierBtn').click(function (e) {
        var editCity = $('#edit_city').val();
    e.preventDefault();
    $.ajax({
        url: "<?php echo base_url('updateSupplier'); ?>",
        type: "POST",
        data: $('#editSupplierForm').serialize(),
        dataType: "json",
        success: function (response) {
            console.log(response); // Check the response in console
            if (response.status === 'success') {
                Swal.fire('Updated!', response.message, 'success').then(() => {
                    location.reload();
                });
            } else {
                $('#updateError').html(response.message).show();
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
        url: "<?php echo base_url('supplier/getDistrictsByProvince'); ?>",
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
        url: "<?php echo base_url('supplier/getCitiesByDistrict'); ?>",
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