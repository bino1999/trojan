<div class="row">
<div class="col-12">
    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
        <h4 class="mb-sm-0">Item Categories</h4>

        <div class="page-title-right">
            <ol class="breadcrumb m-0">
                <li class="breadcrumb-item"><a href="<?php echo base_url(); ?>" zoomIn>Home</a></li>
                <li class="breadcrumb-item active">Item Categories</li>
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
        <h4 class="card-title mb-0 flex-grow-1">All Item Categories</h4>
        <div class="flex-shrink-0">
            <button type="button" class="btn btn-success btn-label waves-effect waves-light" data-bs-toggle="modal" data-bs-target="#exampleModalgrid"><i class="fa fa-plus label-icon align-middle fs-16 me-2"></i> Create New Category</button>
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
<?php foreach ($itemCategories as $key => $category) { ?>
    <tr id="category-<?= $category->itemCategoryId; ?>" class="<?= $category->isDeleted ? 'deleted' : ''; ?>">
        <th scope="row"><?php echo $category->itemCategoryId; ?></th>
        <td><?php echo $category->itemCategoryName; ?></td>
        <td><?php echo $category->createdAt; ?></td>
        <td><?php echo $category->updatedAt; ?></td>
        <td>
            <div class="d-flex gap-2">
                <?php if ($category->isDeleted): ?>
                    <button class="btn btn-sm btn-warning waves-effect waves-light undo-delete-btn" 
                        data-id="<?php echo $category->itemCategoryId; ?>"
                        onclick="undoDelete(<?php echo $category->itemCategoryId; ?>)">
                        <i class="fa fa-undo"></i> Undo
                    </button>
                <?php else: ?>
                    <button class="btn btn-sm btn-secondary waves-effect waves-light edit-category-btn" 
                            data-id="<?php echo $category->itemCategoryId; ?>" 
                            data-name="<?php echo $category->itemCategoryName; ?>"
                            data-bs-toggle="modal" 
                            data-bs-target="#editCategoryModal">
                        <i class="fa fa-pencil"></i> Update
                    </button>

                    <button class="btn btn-sm btn-danger waves-effect waves-light remove-item-btn" 
                            onclick="deleteRecord(<?php echo $category->itemCategoryId; ?>)">
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
            <h5 class="modal-title" id="exampleModalgridLabel">Create New Category</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">

        <form id="createCategoryForm">
    <div class="alert alert-danger" id="createCategoryError" style="display:none;"></div>
    <div class="row g-3">
        <div class="col-12">
            <label for="Name" class="form-label">Category Name</label>
            <input type="text" class="form-control" id="name" name="name" placeholder="Enter Name" required>
        </div>
        <div class="col-lg-12">
            <div class="hstack gap-2 justify-content-end">
                <button type="button" class="btn btn-link link-danger fw-medium shadow-none" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success" id="saveCategoryBtn">Save Category</button>
            </div>
        </div>
    </div>
</form>

        </div>
    </div>
</div>
</div>


<!-- Update Category Modal -->
<div class="modal fade zoomIn" id="editCategoryModal" tabindex="-1" aria-labelledby="editCategoryModalLabel" aria-modal="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editCategoryModalLabel">Edit Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editCategoryForm">
                    <input type="hidden" id="editCategoryId" name="id">
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="editCategoryName" class="form-label">Category Name</label>
                            <input type="text" class="form-control" id="editCategoryName" name="name" required>
                        </div>
                        <div class="col-lg-12">
                            <div class="hstack gap-2 justify-content-end">
                                <button type="button" class="btn btn-link link-danger fw-medium shadow-none" data-bs-dismiss="modal">Close</button>
                                <button type="button" class="btn btn-warning" id="updateCategoryBtn">Update Category</button>
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
</div>


<script type="text/javascript">
    // Initialize DataTable
    $('#table1').DataTable({
        "lengthMenu": [[150, 250, 500, -1], [150, 250, 500, "All"]],
        searching: true,
        responsive: true
    });

    // Save New Category with AJAX
    $('#saveCategoryBtn').click(function (e) {
        e.preventDefault();
        
        // Serialize the form data
        $.ajax({
            url: "<?php echo base_url('saveCategory'); ?>",
            method: "POST",
            data: $('#createCategoryForm').serialize(),  // Sending the form data
            dataType: 'json',
            success: function (response) {
                if (response.status === 'error') {
                    $('#createCategoryError').html(response.message).show();  // Show error if any
                } else {
                    $('#exampleModalgrid').modal('hide');  // Close modal
                    Swal.fire('Success', response.message, 'success').then(() => {
                        location.reload();
                    });
                }
            }
        });
    });


$('#updateCategoryBtn').click(function (e) {
    e.preventDefault();
    
    // Serialize the form data
    $.ajax({
        url: "<?php echo base_url('updateCategory'); ?>", // Ensure this is correct
        method: "POST",
        data: $('#editCategoryForm').serialize(), // Send serialized form data
        dataType: 'json',
        success: function (response) {
            if (response.status === 'error') {
                // Show error if status is 'error'
                $('#editCategoryError').html(response.message).show();  // Display error message
                Swal.fire('Error', response.message, 'error');  // Optionally show a SweetAlert
            } else {
                $('#editCategoryModal').modal('hide'); 
                Swal.fire('Updated!', 'Category updated successfully!', 'success').then(() => {
                    location.reload();
                });
            }
        }
    });
});



function deleteRecord(categoryId) {
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
                url: "<?php echo site_url('itemCategory/deleteCategory') ?>",
                type: "POST",
                data: { categoryId: categoryId },
                dataType: 'json',  // Ensure you're expecting a JSON response
                success: function(response) {
                    if (response.status === 'success') {
                        Swal.fire(
                            'Deleted!',
                            response.message,  // Use the message returned by the controller
                            'success'
                        ).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire(
                            'Error!',
                            response.message,  // Use the error message returned by the controller
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

function undoDelete(categoryId) {
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
                url: "<?php echo site_url('itemCategory/undoDeleteCategory') ?>",
                type: "POST",
                data: { categoryId: categoryId },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        Swal.fire(
                            'Restored!',
                            'Category has been restored.',
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


$('#editCategoryModal').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget); // Button that triggered the modal
    var categoryId = button.data('id');
    var categoryName = button.data('name');

    // Set the values in the form
    $('#editCategoryId').val(categoryId);
    $('#editCategoryName').val(categoryName);
});

</script>
