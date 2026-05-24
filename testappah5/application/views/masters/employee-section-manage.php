<div class="row">
<div class="col-12">
    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
        <h4 class="mb-sm-0">Employee Section</h4>

        <div class="page-title-right">
            <ol class="breadcrumb m-0">
                <li class="breadcrumb-item"><a href="<?php echo base_url(); ?>" zoomIn>Home</a></li>
                <li class="breadcrumb-item active">Employee Section</li>
            </ol>
        </div>

    </div>
</div>
</div>

<div class="row">
<div class="col-lg-12">

<style>
.mainCard{
    margin: -10px;
}
</style>

<div class="card mainCard">
    <div class="card-header align-items-center d-flex">
        <h4 class="card-title mb-0 flex-grow-1">All Employee Section</h4>
        <div class="flex-shrink-0">
            <button type="button" class="btn btn-success btn-label waves-effect waves-light" data-bs-toggle="modal" data-bs-target="#exampleModalgrid"><i class="fa fa-plus label-icon align-middle fs-16 me-2"></i> Create New Employee Section</button>
        </div>
    </div><!-- end card header -->
    <div class="card-body">

    <?php if ($this->session->flashdata('success')): ?>
    <div class="alert alert-success"><?php echo $this->session->flashdata('success'); ?></div>
<?php endif; ?>


        <!-- Tables Without Borders -->
<table id="table1" class="table table-striped table-responsive">
<thead class="table-info">
    <tr>
        <th>Id</th>
        <th>Name</th>
        <th>Create At</th>
        <th>Update At</th>
        <th>Action</th>
    </tr>
</thead>
<tbody>
<?php foreach ($results as $key => $result) { ?>
    <tr id="category-<?= $result->employeeSectionId; ?>" class="<?= $result->isDeleted ? 'deleted' : ''; ?>">
        <th scope="row"><?php echo $result->employeeSectionId; ?></th>
        <td><?php echo $result->employeeSectionName; ?></td>
        <td><?php echo $result->createdAt; ?></td>
        <td><?php echo $result->updatedAt; ?></td>
        <td>
            <div class="d-flex gap-2">
                <?php if ($result->isDeleted): ?>
                    <button class="btn btn-sm btn-warning waves-effect waves-light undo-delete-btn" 
                        data-id="<?php echo $result->employeeSectionId; ?>"
                        onclick="undoDelete(<?php echo $result->employeeSectionId; ?>)">
                        <i class="fa fa-undo"></i> Undo
                    </button>
                <?php else: ?>
                    <button class="btn btn-sm btn-secondary waves-effect waves-light edit-category-btn" 
                            data-id="<?php echo $result->employeeSectionId; ?>" 
                            data-name="<?php echo $result->employeeSectionName; ?>"
                            data-bs-toggle="modal" 
                            data-bs-target="#editModal">
                        <i class="fa fa-pencil"></i> Update
                    </button>

                    <button class="btn btn-sm btn-danger waves-effect waves-light remove-item-btn" 
                            onclick="deleteRecord(<?php echo $result->employeeSectionId; ?>)">
                        <i class="fa fa-trash"></i> Delete
                    </button>
                <?php endif; ?>
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





<div class="modal fade zoomIn" id="exampleModalgrid" tabindex="-1" aria-labelledby="exampleModalgridLabel" aria-modal="true">
<div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="exampleModalgridLabel">Create New Employee Section</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">

        <form id="createForm">
    <div class="alert alert-danger" id="createError" style="display:none;"></div>
    <div class="row g-3">
        <div class="col-12">
            <label for="Name" class="form-label">Employee Section Name</label>
            <input type="text" class="form-control" id="name" name="name" placeholder="Enter Name" required>
        </div>
        <div class="col-lg-12">
            <div class="hstack gap-2 justify-content-end">
                <button type="button" class="btn btn-link link-danger fw-medium shadow-none" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success" id="saveBtn">Save Employee Section  </button>
            </div>
        </div>
    </div>
</form>

        </div>
    </div>
</div>
</div>


<!-- Update Category Modal -->
<div class="modal fade zoomIn" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-modal="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Edit Employee Section</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editForm">
                    <input type="hidden" id="editRecordId" name="id">
                    <div class="alert alert-danger" id="editError" style="display:none;"></div>
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="editName" class="form-label">Employee Section Name</label>
                            <input type="text" class="form-control" id="editName" name="name" required>
                        </div>
                        <div class="col-lg-12">
                            <div class="hstack gap-2 justify-content-end">
                                <button type="button" class="btn btn-link link-danger fw-medium shadow-none" data-bs-dismiss="modal">Close</button>
                                <button type="button" class="btn btn-warning" id="updateBtn">Update Employee Section</button>
                            </div>
                        </div>
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
    // Initialize DataTable
    $('#table1').DataTable({
        "lengthMenu": [[150, 250, 500, -1], [150, 250, 500, "All"]],
        searching: true,
        responsive: true
    });

    $('#saveBtn').click(function (e) {
        e.preventDefault();
        
        $.ajax({
            url: "<?php echo base_url('saveEmployeeSection'); ?>",
            method: "POST",
            data: $('#createForm').serialize(),
            dataType: 'json',
            success: function (response) {
                if (response.status === 'error') {
                    $('#createError').html(response.message).show();
                } else {
                    $('#exampleModalgrid').modal('hide');  // Close modal
                    Swal.fire('Success', response.message, 'success').then(() => {
                        location.reload();
                    });
                }
            }
        });
    });


$('#updateBtn').click(function (e) {
    e.preventDefault();
    
    $.ajax({
        url: "<?php echo base_url('updateEmployeeSection'); ?>",
        method: "POST",
        data: $('#editForm').serialize(),
        dataType: 'json',
        success: function (response) {
            if (response.status === 'error') {
                // Show error if status is 'error'
                $('#editError').html(response.message).show();
                Swal.fire('', response.message, 'error');
            } else {
                $('#editModal').modal('hide'); 
                Swal.fire('Updated!', 'Record updated successfully!', 'success').then(() => {
                    location.reload();
                });
            }
        }
    });
});



function deleteRecord(recordId) {
    
    Swal.fire({
        title: 'Are you sure?',
        text: "Do you want delete this record?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "<?php echo site_url('employeeSection/deleteEmployeeSection') ?>",
                type: "POST",
                data: { recordId: recordId },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        Swal.fire(
                            'Deleted!',
                            response.message,
                            'success'
                        ).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire(
                            'Error!',
                            response.message,
                            'error'
                        );
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    Swal.fire(
                        'Error!',
                        'There was a problem with the server. Please try again later.',
                        'error'
                    );
                }
            });
        }
    });
}

function undoDelete(recordId) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You want to restore this category!",
        icon: 'info',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, restore it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "<?php echo site_url('employeeSection/undoDeleteEmployeeSection') ?>",
                type: "POST",
                data: { recordId: recordId },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        Swal.fire(
                            'Restored!',
                            'Record has been restored.',
                            'success'
                        ).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire(
                            'Error!',
                            response.message,
                            'error'
                        );
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    Swal.fire(
                        'Error!',
                        'There was a problem with the server. Please try again later.',
                        'error'
                    );
                }
            });
        }
    });
}


$('#editModal').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget); // Button that triggered the modal
    var categoryId = button.data('id');
    var categoryName = button.data('name');

    // Set the values in the form
    $('#editRecordId').val(categoryId);
    $('#editName').val(categoryName);
});

</script>
