<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Payments</h4>

            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="<?php echo base_url(); ?>">Home</a></li>
                    <li class="breadcrumb-item active">Payments</li>
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
            
            .voucher-readonly {
                background-color: #e9ecef !important;
                color: #6c757d !important;
                cursor: not-allowed !important;
                border: 1px solid #ced4da !important;
                opacity: 0.8;
                pointer-events: none;
            }
            
            .voucher-readonly:focus {
                box-shadow: none !important;
                border-color: #ced4da !important;
            }
        </style>


        <div class="card mainCard">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-2">
                        <input type="date" name="sdate" id="sdate" class="form-control" value="<?= date('Y-m-d', strtotime('-7 day')); ?>">
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="edate" id="edate" class="form-control" value="<?= date('Y-m-d'); ?>">
                    </div>
                    <div class="col-md-3">
                <select id="expenseCategory" class="form-select" onchange="onCategoryFilterChange()">
                    <option value="" selected>Select Category</option>
                    <?php
                    foreach ($expenseCategories as $category) {
                        echo '<option value="' . $category->expensesCategoryId . '">' . $category->expensesCategoryName . '</option>';
                    }
                    ?>
                </select>
            </div>

            <div class="col-md-3" id="employeeFilterWrapper" style="display:none;">
                <select id="expenseEmployeeFilter" class="form-select" onchange="loadExpenses()">
                    <option value="" selected>Select Employee</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?= $user->UserID ?>"><?= $user->FirstName . ' ' . $user->LastName ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-1">
                <button class="btn btn-primary" onclick="loadExpenses();"><i class="fa fa-search"></i></button>
            </div>


                    <div class="col-md-2 flex-shrink-0">
                <button type="button" class="btn btn-success btn-label waves-effect waves-light" onclick="openExpenseModal()">
                        <i class="fa fa-plus label-icon align-middle fs-16 me-2"></i> Create Expense
                    </button>
                </div>
                </div>
            </div>

            <div class="card-body">
                <div id="expensesTable"></div>
            </div>
        </div>
    </div>
    <!--end col-->
</div>



<script>
    loadExpenses();

    function loadExpenses() {
        let sdate = $('#sdate').val();
        let edate = $('#edate').val();
        let expenseCategory = $('#expenseCategory').val();
        let employeeId = $('#expenseEmployeeFilter').length ? $('#expenseEmployeeFilter').val() : '';

        if (!sdate || !edate) {
            Toastify({
    text: "Please select both start and end dates.",
    className: "toastify-success",
    duration: 5000,
    close: true,
    gravity: "top",
    position: "right"
}).showToast();
            return;
        }

        $('#expensesTable').html('<p>Loading...</p>');
        $.ajax({
            url: "<?php echo site_url('expenses/loadExpenses') ?>",
            type: "POST",
            data: {
                sdate: sdate,
                edate: edate,
                expenseCategory: expenseCategory,
                employeeId: employeeId
            },
            success: function(data) {
                $('#expensesTable').html(data);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText);
                $('#expensesTable').html('<p>Error loading data.</p>');
            }
        });
    }

    function openExpenseModal() {
        $('#addExpenseModal select').select2({
            dropdownParent: $('#addExpenseModal')
        });

        // Auto-generate voucher number when modal opens
        generateVoucherNumber();

        // Ensure voucher number field is truly readonly with multiple protections
        $('#reference')
            .attr('readonly', true)
            .attr('disabled', false) // Keep enabled for form submission
            .addClass('voucher-readonly')
            .off('keydown keyup keypress paste input change focus click')
            .on('keydown keyup keypress paste input change focus click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            })
            .on('mousedown mouseup mousemove', function(e) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            });

        $('#addExpenseModal').modal('show');
    }

    function generateVoucherNumber() {
        $.ajax({
            url: "<?= base_url('expenses/getNextVoucherNumber'); ?>",
            method: "GET",
            dataType: 'json',
            success: function(response) {
                $('#reference').val(response.voucher_number);
            },
            error: function() {
                console.log('Error generating voucher number');
            }
        });
    }

    function handleCategoryChange() {
        const categoryId = $('#expense_category').val();
        const salaryAdvanceId = 7; // SALARY ADVANCE category ID
        
        // Hide both employee fields initially
        $('#employeeField').hide();
        $('#employeeNameField').hide();
        
        // Clear selections and remove required attributes
        $('#expense_user').val('').prop('required', false);
        $('#expense_member').val('').prop('required', false);
        
        if (categoryId == salaryAdvanceId) {
            // Show employee dropdown for salary advance
            $('#employeeField').show();
            $('#expense_user').prop('required', true);
        }
        // For all other categories, no employee field is shown
    }

    function onCategoryFilterChange(){
        const categoryId = $('#expenseCategory').val();
        const salaryAdvanceId = 7;
        if (String(categoryId) === String(salaryAdvanceId)){
            $('#employeeFilterWrapper').show();
        } else {
            $('#employeeFilterWrapper').hide();
            if ($('#expenseEmployeeFilter').length) {
                $('#expenseEmployeeFilter').val('');
            }
        }
        loadExpenses();
    }

    $(document).on('click', '#saveExpenseBtn', function (e) {
    e.preventDefault();
    
    // Validate required fields based on category
    const categoryId = $('#expense_category').val();
    const salaryAdvanceId = 7;
    
    if (categoryId == salaryAdvanceId) {
        if (!$('#expense_user').val()) {
            Swal.fire('Error', 'Please select an employee for salary advance.', 'error');
            return;
        }
    }
    // For other categories, no employee validation needed
    
    // Validate person name and phone
    if (!$('#person_name').val().trim()) {
        Swal.fire('Error', 'Please enter the person name.', 'error');
        return;
    }
    
    if (!$('#person_phone').val().trim()) {
        Swal.fire('Error', 'Please enter the person phone number.', 'error');
        return;
    }
    
    $.ajax({
        url: "<?= base_url('expenses/saveExpense'); ?>",
        method: "POST",
        data: $('#createExpenseForm').serialize(),
        dataType: 'json',
        success: function (response) {
            if (response.status === 'success') {
                $('#addExpenseModal').modal('hide');
                Toastify({
                    text: response.message,
                    className: "toastify-success",
                    duration: 5000,
                    close: true,
                    gravity: "top",
                    position: "right"
                }).showToast();

                $('#createExpenseForm')[0].reset();
                $('#expenseError').hide();
                
                // Hide employee fields
                $('#employeeField').hide();
                $('#employeeNameField').hide();

                if (typeof loadExpenses === 'function') {
                    loadExpenses();
                }
            } else {
                $('#expenseError').html(response.message).show();
            }
        },
        error: function () {
            Swal.fire('Error!', 'Something went wrong. Please try again.', 'error');
        }
    });
});




$(document).on('click', '.delete-expense', function () {
    let id = $(this).data('id');
    Swal.fire({
        title: 'Are you sure?',
        text: "This expense will be permanently deleted.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post("<?= site_url('expenses/delete') ?>", { id: id }, function (res) {
                if (res.status === 'success') {
                    Toastify({
                        text: res.message,
                        className: "toastify-success",
                        duration: 3000
                    }).showToast();
                    loadExpenses(); // Refresh the table
                } else {
                    Swal.fire('Error', res.message, 'error');
                }
            }, 'json');
        }
    });
});

   </script>



<!-- Add Expense Modal -->
<div class="modal fade" id="addExpenseModal" tabindex="-1" aria-labelledby="addExpenseModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Expense</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="createExpenseForm">
                    <div class="row">
                        <!-- Category -->
                        <div class="col-md-12 mb-3">
                            <label for="expense_category" class="form-label">Expense Category*</label>
                            <select id="expense_category" name="expense_category" class="form-control" required onchange="handleCategoryChange()">
                                <option value="">Select Category</option>
                                <?php foreach ($expenseCategories as $cat): ?>
                                    <option value="<?= $cat->expensesCategoryId ?>"><?= $cat->expensesCategoryName ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Auto-generated Voucher Number -->
                        <div class="col-md-6 mb-3">
                            <label for="reference" class="form-label">Voucher Number</label>
                            <input type="text" class="form-control voucher-readonly" id="reference" name="reference" placeholder="Auto-generated" readonly>
                        </div>

                        <!-- Amount -->
                        <div class="col-md-6 mb-3">
                            <label for="expense_amount" class="form-label">Amount*</label>
                            <input type="number" class="form-control" id="expense_amount" name="expense_amount" step="0.01" min="0" required>
                        </div>

                        <!-- Date -->
                        <div class="col-md-6 mb-3">
                            <label for="expense_date" class="form-label">Date*</label>
                            <input type="date" class="form-control" id="expense_date" name="expense_date" value="<?= date('Y-m-d') ?>" required>
                        </div>

                        <!-- Payment Method -->
                        <div class="col-md-6 mb-3">
                            <label for="payment_method" class="form-label">Payment Method*</label>
                            <select class="form-control" id="payment_method" name="payment_method" onchange="if(this.value === 'cheque') { $('.cheque-fields').show(); } else { $('.cheque-fields').hide(); }" required>
                                <option value="">Select Method</option>
                                <option value="cash" selected>Cash</option>
                                <option value="card">Card</option>
                                <option value="cheque">Cheque</option>
                                <option value="credit">Credit</option>
                                <option value="bank_transfer">Bank Transfer</option>
                            </select>
                        </div>

                        <!-- Employee (only for Salary Advance) -->
                        <div class="col-md-6 mb-3" id="employeeField" style="display: none;">
                            <label for="expense_user" class="form-label">Employee*</label>
                            <select id="expense_user" name="expense_user" class="form-control">
                                <option value="">Select Employee</option>
                                <?php foreach ($users as $user): ?>
                                    <option value="<?= $user->UserID ?>"><?= $user->FirstName . ' ' . $user->LastName ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Cheque Date (hidden by default) -->
                        <div class="col-md-6 mb-3 cheque-fields" style="display:none;">
                            <label for="cheque_date" class="form-label">Cheque Date</label>
                            <input type="date" class="form-control" id="cheque_date" name="cheque_date" placeholder="Cheque Date">
                        </div>

                        <!-- Person Name -->
                        <div class="col-md-6 mb-3">
                            <label for="person_name" class="form-label">Person Name*</label>
                            <input type="text" class="form-control" id="person_name" name="person_name" placeholder="Enter person name" required>
                        </div>

                        <!-- Person Phone -->
                        <div class="col-md-6 mb-3">
                            <label for="person_phone" class="form-label">Person Phone*</label>
                            <input type="text" class="form-control" id="person_phone" name="person_phone" placeholder="Enter phone number" required>
                        </div>

                        <!-- Description -->
                        <div class="col-12 mb-3">
                            <label for="description" class="form-label">Remarks / Description</label>
                            <textarea class="form-control" id="description" name="description" rows="2"></textarea>
                        </div>
                    </div>

                    <!-- Footer Buttons -->
                    <div class="col-12">
                        <div class="hstack gap-2 justify-content-end">
                            <button type="button" class="btn btn-success" id="saveExpenseBtn">Save Expense</button>
                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </form>

                <div id="expenseError" class="mt-3 text-danger errorDiv" style="display:none;"></div>
            </div>
        </div>
    </div>
</div>
