<?php if (empty($cart)) : ?>
    <p>No items in the cart.</p>
<?php else : ?>
<style>
.customer-search-container {
    position: relative;
}

.custom-dropdown {
    position: relative;
    width: 100%;
}

.qb-dropdown-toggle {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 12px;
    border: 1px solid #ced4da;
    border-radius: 0.375rem;
    background: white;
    cursor: pointer;
    transition: border-color 0.15s ease-in-out;
    /* Hide default browser arrow */
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
}

.qb-dropdown-toggle:hover {
    border-color: #2196F3;
}

.qb-dropdown-toggle i {
    transition: transform 0.2s ease;
}

.qb-dropdown-toggle.active i {
    transform: rotate(180deg);
}

.dropdown-menu {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #ced4da;
    border-top: none;
    border-radius: 0 0 0.375rem 0.375rem;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    z-index: 1000;
    max-height: 300px;
    overflow-y: auto;
    display: none;
}

.dropdown-menu.show {
    display: block;
}

.search-container {
    padding: 10px;
    border-bottom: 1px solid #e9ecef;
    background: #f8f9fa;
}

.search-container input {
    border: 1px solid #ced4da;
    border-radius: 0.375rem;
    font-size: 14px;
}

.search-container input:focus {
    border-color: #2196F3;
    box-shadow: 0 0 0 0.2rem rgba(33, 150, 243, 0.25);
    outline: none;
}

.dropdown-options {
    max-height: 250px;
    overflow-y: auto;
}

.option {
    padding: 8px 12px;
    cursor: pointer;
    border-bottom: 1px solid #f1f3f4;
    transition: background-color 0.15s ease;
}

.option:hover {
    background-color: #e3f2fd;
}

.option.selected {
    background-color: #2196F3;
    color: white;
}

.option.hidden {
    display: none;
}

.payment-method-card {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    margin-bottom: 10px;
}

.payment-method-card .card-header {
    background: #e9ecef;
    padding: 8px 12px;
    border-bottom: 1px solid #dee2e6;
}

.payment-method-card .card-body {
    padding: 12px;
}

.remove-payment {
    cursor: pointer;
    color: #dc3545;
}

.remove-payment:hover {
    color: #c82333;
}
</style>

<div class="container-fluid">
    <!-- Cart Items Display -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fa fa-shopping-cart"></i> Internal Bill Items</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Product Name</th>
                                    <th>Quantity</th>
                                    <th>Unit Price</th>
                                    <th>Total Price</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cart as $index => $item) : ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td><?= htmlspecialchars($item['product_name']) ?></td>
                                    <td><?= $item['quantity'] ?></td>
                                    <td>Rs. <?= number_format($item['unit_price'], 2) ?></td>
                                    <td>Rs. <?= number_format($item['total_price'], 2) ?></td>
                                    <td>
                                        <button type="button" class="btn btn-danger btn-sm" onclick="deleteCartItem(<?= $index ?>)">
                                            <i class="fa fa-trash"></i> Remove
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Employee Selection Section -->
    <div class="row mt-3">
        <div class="col-md-12">
            <div class="card border-primary">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="fa fa-user"></i> Select Employee</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <label for="customer">Employee:</label>
                            <div class="position-relative">
                                <div class="custom-dropdown">
                                    <div class="qb-dropdown-toggle" onclick="toggleCustomerDropdown()">
                                        <span id="selectedCustomerText">Select Employee</span>
                                        <i class="fa fa-chevron-down"></i>
                                    </div>
                                    <div class="dropdown-menu" id="customerDropdown">
                                        <div class="search-container">
                                            <input type="text" id="customerSearch" placeholder="Search employees..." oninput="filterCustomerOptions(this.value)">
                                        </div>
                                        <div class="dropdown-options">
                                            <?php foreach ($employees as $employee) : ?>
                                            <div class="option" data-value="<?= $employee->UserID ?>" onclick="selectCustomer(this, '<?= $employee->UserID ?>', '<?= $employee->FirstName . ' ' . $employee->LastName ?>')">
                                                <?= $employee->FirstName . ' ' . $employee->LastName ?>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    <input type="hidden" id="customer" value="">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>





    <!-- Note Section -->
    <div class="row mt-3">
        <div class="col-md-12">
            <div class="card border-secondary">
                <div class="card-header bg-secondary text-white">
                    <h6 class="mb-0"><i class="fa fa-sticky-note"></i> Note</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <label for="note">Note:</label>
                            <input type="text" class="form-control" id="note" placeholder="Enter note (optional)">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Error Display -->
    <div id="errorDiv" class="mt-3"></div>

    <!-- Complete Bill Button -->
    <div class="row mt-3">
        <div class="col-12 text-center">
            <button type="button" class="btn btn-success btn-lg" onclick="completeBill()" style="min-width: 200px;">
                <i class="fa fa-check-circle"></i> Complete Internal Bill
            </button>
        </div>
    </div>
</div>

<script>
// Customer dropdown functionality
function toggleCustomerDropdown() {
    const dropdown = document.getElementById('customerDropdown');
    const toggle = document.querySelector('.qb-dropdown-toggle');
    
    if (dropdown.classList.contains('show')) {
        dropdown.classList.remove('show');
        toggle.classList.remove('active');
    } else {
        // Close other dropdowns first
        closeAllDropdowns();
        dropdown.classList.add('show');
        toggle.classList.add('active');
        
        // Position dropdown above if there's not enough space below
        positionDropdown(dropdown);
    }
}

function selectCustomer(element, value, text) {
    document.getElementById('customer').value = value;
    document.getElementById('selectedCustomerText').textContent = text;
    
    // Close dropdown
    document.getElementById('customerDropdown').classList.remove('show');
    document.querySelector('.qb-dropdown-toggle').classList.remove('active');
    
    // Remove selection from other options
    document.querySelectorAll('#customerDropdown .option').forEach(opt => opt.classList.remove('selected'));
    element.classList.add('selected');
    
    // Handle customer selection change
    handleCustomerSelection();
}

function filterCustomerOptions(searchTerm) {
    const options = document.querySelectorAll('#customerDropdown .option');
    options.forEach(option => {
        const text = option.textContent.toLowerCase();
        if (text.includes(searchTerm.toLowerCase())) {
            option.classList.remove('hidden');
        } else {
            option.classList.add('hidden');
        }
    });
}

// Handle customer selection change
function handleCustomerSelection() {
    // No special handling needed for internal bills
}

// Utility functions
function closeAllDropdowns() {
    document.querySelectorAll('.dropdown-menu').forEach(dropdown => {
        dropdown.classList.remove('show');
    });
    document.querySelectorAll('.qb-dropdown-toggle').forEach(toggle => {
        toggle.classList.remove('active');
    });
}

function positionDropdown(dropdown) {
    const rect = dropdown.getBoundingClientRect();
    const viewportHeight = window.innerHeight;
    
    if (rect.bottom > viewportHeight && rect.top > 100) {
        dropdown.classList.add('show-above');
    } else {
        dropdown.classList.remove('show-above');
    }
}

// Close dropdowns when clicking outside
document.addEventListener('click', function(event) {
    if (!event.target.closest('.custom-dropdown')) {
        closeAllDropdowns();
    }
});



// Function to complete the bill
function completeBill() {
    const customerId = document.getElementById('customer').value;
    const note = $('#note').val();

    // Basic validation
    if (!customerId || customerId.trim() === '') {
        Swal.fire('Error', 'Please select an employee.', 'error');
        return;
    }

    // Show loading
    Swal.fire({
        title: 'Saving Internal Bill...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // Make AJAX call to save the bill
    $.ajax({
        url: "<?php echo site_url('internalBill/saveCompletedBill'); ?>",
        type: "POST",
        dataType: "json",
        data: {
            customerId,
            note
        },
        success: function(res) {
            if (res.status === 'success') {
                const base_url = "<?= base_url(); ?>";
                const receiptUrl = base_url + 'internal_bill/print_receipt/' + res.bill_id;
                window.open(receiptUrl, '_blank');

                // Then show the success dialog (after opening window)
                Swal.fire({
                    icon: 'success',
                    title: 'Saved!',
                    text: 'Internal bill saved successfully. Receipt opened in new tab.',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire('Error', res.message || 'Something went wrong', 'error');
                document.getElementById('errorDiv').innerHTML = '<div class="alert alert-danger">' + (res.message || 'Something went wrong') + '</div>';
            }
        },
        error: function(xhr, status, error) {
            console.error(error);
            Swal.fire('Error', 'An unexpected error occurred.', 'error');
        }
    });
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    // Close dropdowns on escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeAllDropdowns();
        }
    });
});
</script>
<?php endif; ?>
