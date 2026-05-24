<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Vehicles</h4>

            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="<?php echo base_url(); ?>">Home</a></li>
                    <li class="breadcrumb-item active">Vehicles</li>
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
            <input type="date" name="sdate" id="sdate" class="form-control" value="<?= date('Y-m-d', strtotime('-1 month')); ?>">
                </div>
                <div class="col-md-2">
                    <input type="date" name="edate" id="edate" class="form-control" value="<?= date('Y-m-d'); ?>">
                </div>
                <div class="col-md-6">
                    <button class="btn btn-primary" onclick="loadVehicles();">Load vehicle</button>
                </div>

                <div class="col-md-2 flex-shrink-0">
                <button type="button" class="btn btn-success btn-label waves-effect waves-light" onclick="openVehicleModal()">
                        <i class="fa fa-plus label-icon align-middle fs-16 me-2"></i> Create New Vehicle
                    </button>
                </div>
            </div>       

            </div><!-- end card header -->
            <div class="card-body" id="vehicleList">

            </div>
        </div>
    </div>
    <!--end col-->
</div>





<!-- Vehicle Modal -->
<div class="modal fade zoomIn" id="vehicleModal" data-bs-backdrop="static" tabindex="-1" aria-labelledby="vehicleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="vehicleModalLabel">Create Vehicle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="vehicleModalBody">

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-secondary" onclick="saveVehicleDetails()">Save</button>
            </div>

        </div>
    </div>
</div>

<!-- Vehicle Edit Modal -->
<div class="modal fade zoomIn" id="vehicleEditModal" data-bs-backdrop="static" tabindex="-1" aria-labelledby="vehicleEditModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="vehicleEditModalLabel">Create Vehicle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="vehicleEditModalBody">

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-secondary" onclick="updateVehicleDetails()">Update</button>
            </div>

        </div>
    </div>
</div>


<!-- Vehicle details Modal -->
<div class="modal fade zoomIn" id="vehicleDetailsModal"  tabindex="-1" aria-labelledby="vehicleDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="vehicleDetailsModalLabel">Vehicle Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="vehicleDetailsModalBody">

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
            </div>

        </div>
    </div>
</div>


</div>
</div>
</div>
<!--end row-->

<script type="text/javascript">
    $(document).ready(function() {

        loadVehicles();

        $('#table1').DataTable({
            "lengthMenu": [
                [150, 250, 500, -1],
                [150, 250, 500, "All"]
            ],
            "searching": true,
            "responsive": true
        });


        // Initialize when modal opens
        $('#vehicleModal').on('shown.bs.modal', function() {
            $('#vehicleModal select').select2({
            dropdownParent: $('#vehicleModal')
        });
        });

        // Cleanup when modal closes
        $('#vehicleModal').on('hidden.bs.modal', function() {
            $('.select2').select2('destroy');
            $(document).off('select2:open');
        });
    });

    function loadVehicles(){
        let sdate = $('#sdate').val();
        let edate = $('#edate').val();
        
        $.ajax({
            url: "<?php echo site_url('vehicle/loadVehicles') ?>",
            type: "POST",
            data: "sdate=" + sdate + "& edate=" + edate,
            success: function(data) {
                $('#vehicleList').html(data);
            },
            error: function(jXHR, textStatus, errorThrown) {
                console.log(jXHR.responseText);
            }
        });
    }

    function openVehicleModal() {
        $.ajax({
            url: "<?php echo site_url('vehicle/openVehicleCreateModal') ?>",
            type: "POST",
            //data: "loan_id=" + loan_id + "& recId=" + recId + "&",
            success: function(data) {
                $('#vehicleModalBody').html(data);
                $('#vehicleModal').modal('show');
            },
            error: function(jXHR, textStatus, errorThrown) {
                console.log(jXHR.responseText);
            }
        });
    }

    function openVehicleEditModal(vehicle_id) {
    $.ajax({
        url: "<?php echo site_url('vehicle/openVehicleEditModal') ?>",
        type: "POST",
        data: "vehicle_id=" + vehicle_id,
        success: function(data) {
            $('#vehicleEditModalBody').html(data);
            $('#vehicleEditModal').modal('show');

            $('#vehicleEditModal select').select2({
            dropdownParent: $('#vehicleEditModal')
        });

        
        },
        error: function(jXHR, textStatus, errorThrown) {
            console.log(jXHR.responseText);
        }
    });
}

    function openVehicleDetailsModal(vehicle_id) {
        $.ajax({
            url: "<?php echo site_url('vehicle/showVehicleDetails') ?>",
            type: "POST",
            data: "vehicle_id=" + vehicle_id + "&",
            success: function(data) {
                $('#vehicleDetailsModalBody').html(data);
                $('#vehicleDetailsModal').modal('show');
            },
            error: function(jXHR, textStatus, errorThrown) {
                console.log(jXHR.responseText);
            }
        });
    }



    function saveVehicleDetails() {
        Swal.fire({
            title: 'Are you sure?',
            text: "Do you want to save this vehicle?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#f06548',
            confirmButtonText: 'Yes, save it!'
        }).then((result) => {
            if (result.isConfirmed) {
                var formData = $("#vehicleForm").serialize(); // Serialize form data

                $.ajax({
                    url: "<?php echo site_url('vehicle/saveVehicleDetails') ?>", // Update with your correct endpoint
                    type: "POST",
                    data: formData,
                    dataType: "json",
                    success: function(response) {
                        if (response.status === "success") {

                            Swal.fire({
                                icon: 'success',
                                text: 'Vehicle details saved successfully!',
                                showConfirmButton: true,
                                confirmButtonText: 'OK',
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    $("#vehicleForm")[0].reset();
                                    $(".select2").val(null).trigger("change");
                                    $("#vehicleModal").modal('hide');
                                    window.location.reload();
                                }
                            });

                        } else {
                            $('#createError').html(response.message).show();

                            Toastify({
                                text: "Something went wrong! Please check your inputs.",
                                className: "error",
                                duration: 5000,
                                close: true,
                                gravity: "top",
                                position: "right",
                                backgroundColor: "#cc563d",
                            }).showToast();

                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("AJAX Error:", xhr.responseText);
                        Swal.fire({
                            icon: 'error',
                            text: 'An error occurred while saving: ' + xhr.responseText,
                        });
                    }

                });
            }
        });
    }

    function updateVehicleDetails() {
        Swal.fire({
            title: 'Are you sure?',
            text: "Do you want to update this vehicle?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#f06548',
            confirmButtonText: 'Yes, save it!'
        }).then((result) => {
            if (result.isConfirmed) {
                var formData = $("#vehicleForm").serialize(); // Serialize form data

                $.ajax({
                    url: "<?php echo site_url('vehicle/updateVehicle') ?>", // Update with your correct endpoint
                    type: "POST",
                    data: formData,
                    dataType: "json",
                    success: function(response) {
                        if (response.status === "success") {

                            Swal.fire({
                                icon: 'success',
                                text: 'Vehicle details saved successfully!',
                                showConfirmButton: true,
                                confirmButtonText: 'OK',
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    $("#vehicleForm")[0].reset();
                                    $(".select2").val(null).trigger("change");
                                    $("#vehicleModal").modal('hide');
                                    window.location.reload();
                                }
                            });

                        } else {
                            $('#createError').html(response.message).show();

                            Toastify({
                                text: "Something went wrong! Please check your inputs.",
                                className: "error",
                                duration: 5000,
                                close: true,
                                gravity: "top",
                                position: "right",
                                backgroundColor: "#cc563d",
                            }).showToast();

                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("AJAX Error:", xhr.responseText);
                        Swal.fire({
                            icon: 'error',
                            text: 'An error occurred while saving: ' + xhr.responseText,
                        });
                    }

                });
            }
        });
    }
</script>