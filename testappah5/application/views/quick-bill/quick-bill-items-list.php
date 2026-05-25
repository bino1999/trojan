<?php if (empty($cart)) : ?>
    <p>No items in the cart.</p>
<?php else : ?>

<!-- Customer Information Section - Moved to Top -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card border-primary">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0"><i class="fa fa-user"></i> Customer Information</h6>
            </div>
            <div class="card-body">
                <!-- Customer Selection -->
                <div class="row mb-3">
                    <div class="col-md-12">
                        <label for="customer">Select Customer:</label>
                        <div class="position-relative customer-search-container">
                            <div class="custom-dropdown">
                                <div class="qb-dropdown-toggle" onclick="toggleCustomerDropdown()">
                                    <span id="selectedCustomerText">Select Customer</span>
                                    <i class="fa fa-chevron-down"></i>
                                </div>
                                <div class="dropdown-menu" id="customerDropdown">
                                    <div class="search-container">
                                        <input type="text" class="form-control" id="customerSearch" placeholder="Search customers..." oninput="filterCustomerOptions(this.value)">
                                    </div>
                                    <div class="dropdown-options">
                                        <div class="option" data-value="0" onclick="selectCustomer(this, '0', 'Walk-in Customer')">
                                            <i class="fa fa-user-plus text-primary"></i> Walk-in Customer
                                        </div>
                                        <?php if (empty($customers)) : ?>
                                            <div class="option disabled">No customers available</div>
                                        <?php endif; ?>
                                        <?php foreach ($customers as $cust) : ?>
                                            <div class="option" data-value="<?= $cust->customers_id ?>" onclick="selectCustomer(this, '<?= $cust->customers_id ?>', '<?= ucfirst($cust->name) ?> - <?= $cust->mobile ?>')">
                                                <?= ucfirst($cust->name) ?> - <?= $cust->mobile ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <input type="hidden" id="customer" value="">
                                <input type="hidden" id="selectedCustomerNameHidden" value="">
                                <input type="hidden" id="selectedCustomerMobileHidden" value="">
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Walk-in Customer Registration Fields -->
                <div class="row" id="walkInCustomerFields" style="display: none;">
                    <div class="col-md-12">
                        <div class="card border-info walk-in-customer-card">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0"><i class="fa fa-user-plus"></i> Walk-in Customer Registration</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-2">
                                        <label for="salutation" class="form-label">Salutation *</label>
                                        <select class="form-select" id="salutation" name="salutation" required>
                                            <option value="MR">Mr.</option>
                                            <option value="MRS">Mrs.</option>
                                            <option value="MS">Ms.</option>
                                            <option value="DR">Dr.</option>
                                        </select>
                                    </div>
                                    <div class="col-md-5">
                                        <label for="customerName" class="form-label">Customer Name *</label>
                                        <input type="text" class="form-control" id="customerName" name="customerName" placeholder="Enter customer name" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="customerMobile" class="form-label">Mobile Number *</label>
                                        <input type="text" class="form-control" id="customerMobile" name="customerMobile" placeholder="Enter mobile number" maxlength="10" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">&nbsp;</label>
                                        <button type="button" class="btn btn-primary btn-sm" onclick="saveWalkInCustomer()" style="width: 100%;">
                                            <i class="fa fa-save"></i> Save
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
</div>

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

/* Required field styling */
.text-danger {
    color: #dc3545 !important;
}

.form-control:required,
.form-select:required {
    border-left: 3px solid #dc3545;
}

.form-control:required:valid,
.form-select:required:valid {
    border-left: 3px solid #28a745;
}

.is-valid {
    border-color: #28a745 !important;
    box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25) !important;
}

.is-invalid {
    border-color: #dc3545 !important;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
}

.option.disabled {
    color: #6c757d;
    cursor: not-allowed;
    background-color: #f8f9fa;
}

.option.hidden {
    display: none;
}

/* Custom scrollbar for dropdown */
.dropdown-options::-webkit-scrollbar {
    width: 6px;
}

.dropdown-options::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.dropdown-options::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}

.dropdown-options::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* Animation for dropdown */
.dropdown-menu {
    opacity: 0;
    transform: translateY(-10px);
    transition: opacity 0.2s ease, transform 0.2s ease;
}

.dropdown-menu.show {
    opacity: 1;
    transform: translateY(0);
}

/* Smart positioning - show above when needed */
.dropdown-menu.show-above {
    top: auto;
    bottom: 100%;
    border-top: 1px solid #ced4da;
    border-bottom: none;
    border-radius: 0.375rem 0.375rem 0 0;
    box-shadow: 0 -4px 6px rgba(0,0,0,0.1);
}

.walk-in-customer-card {
    border: 2px solid #e3f2fd;
    border-radius: 0.5rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.walk-in-customer-card .card-header {
    background: linear-gradient(135deg, #2196F3, #1976D2);
    border-bottom: none;
}

.form-label {
    font-weight: 500;
    color: #495057;
}

.btn-primary {
    background: linear-gradient(135deg, #2196F3, #1976D2);
    border: none;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(33, 150, 243, 0.3);
}

.form-control:focus {
    border-color: #2196F3;
    box-shadow: 0 0 0 0.2rem rgba(33, 150, 243, 0.25);
}

.form-select:focus {
    border-color: #2196F3;
    box-shadow: 0 0 0 0.2rem rgba(33, 150, 243, 0.25);
}
</style>

<div class="table-responsive">
    <table id="productsTable" class="table table-striped">
        <thead class="table-info">
            <tr>
                <th>#</th>
                <th>Product</th>
                <th>Qty</th>
                <th>Unit Price</th>
                <th>Discount</th>
                <th>Total</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($cart as $index => $item) : ?>
                <tr>
                    <td><?= $index + 1 ?></td>
                    <td><?= ucfirst($item['product_name']) ?></td>
                    <td><?= $item['quantity'] ?></td>
                    <td><?= number_format($item['unit_price'], 2) ?></td>
                    <td>
                        <?php if ($item['discount_percent'] > 0): ?>
                            <?= $item['discount_percent'] . '%' ?>
                        <?php elseif ($item['discount_amount'] > 0): ?>
                            <?= number_format($item['discount_amount'], 2) ?>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                    <td><?= number_format($item['total'], 2) ?></td>
                    <td>
                        <button class="btn btn-sm btn-danger" onclick="deleteCartItem(<?= $index ?>)">
                            <i class="fa fa-trash"></i>
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="5" class="text-end">Subtotal</th>
                <th colspan="2">Rs. <?= number_format($total, 2) ?></th>
            </tr>
            <tr>
                <th colspan="5" class="text-end">Bill Discount</th>
                <th colspan="2">
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="input-group input-group-sm">
                                <input type="number" min="0" max="100" step="0.01" class="form-control" id="totalDiscountPercent" placeholder="0.00">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">Rs.</span>
                                <input type="number" min="0" step="0.01" class="form-control" id="totalDiscountAmount" placeholder="0.00">
                            </div>
                        </div>
                    </div>
                </th>
            </tr>
            <tr>
                <th colspan="5" class="text-end">Grand Total</th>
                <th colspan="2">Rs. <span id="grandTotalDisplay"><?= number_format($total, 2) ?></span></th>
            </tr>
        </tfoot>
    </table>

    <!-- Subtotal for JS reinit after AJAX reload -->
    <input type="hidden" id="currentSubtotal" value="<?= number_format($total, 2, '.', '') ?>">

    <!-- Payment Information Section -->
    <div class="row mt-3">
        <div class="col-md-5">
            <label for="paymentMethod">Payment Method:</label>
            <div class="position-relative">
                <div class="custom-dropdown">
                    <div class="qb-dropdown-toggle" onclick="togglePaymentDropdown()">
                        <span id="selectedPaymentText">Select Payment Method</span>
                        <i class="fa fa-chevron-down"></i>
                    </div>
                    <div class="dropdown-menu" id="paymentDropdown">
                        <div class="dropdown-options">
                            <?php foreach ($payment_methods as $method) : ?>
                                <div class="option" data-value="<?= $method ?>" onclick="selectPaymentMethod(this, '<?= $method ?>')">
                                    <?= $method ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <input type="hidden" id="paymentMethod" value="">
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <label for="paidAmount">Paid Amount:</label>
            <input type="number" class="form-control" id="paidAmount" value="<?= $total ?>" name="paidAmount" step="0.01">
        </div>
        <div class="col-md-3">
            <label>&nbsp;</label>
            <button type="button" class="btn btn-primary" onclick="addPaymentMethod()" style="width: 100%;">
                <i class="fa fa-plus"></i> Add Payment
            </button>
        </div>
    </div>

    <!-- Hidden fields shown only when payment is Card/Cheque/Bank Transfer -->
    <div class="row mt-3" id="additionalPaymentFields" style="display: none;">
        <div class="col-md-4">
            <label id="cardOrChequeNoLabel" for="cardOrChequeNo">Card / Cheque No: <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="cardOrChequeNo" name="cardOrChequeNo" required>
        </div>

        <div class="col-md-4" id="chequeDateGroup">
            <label for="chequeDate">Cheque Date: <span class="text-danger">*</span></label>
            <input type="date" class="form-control" id="chequeDate" name="chequeDate" required>
        </div>

        <div class="col-md-4">
            <label id="bankNameLabel" for="bankName">Bank Name: <span class="text-danger">*</span></label>
            <select class="form-select" id="bankName" name="bankName" required>
                <option value="">Select Bank</option>
                <option value="Amana Bank PLC">Amana Bank PLC</option>
                <option value="Bank of Ceylon">Bank of Ceylon</option>
                <option value="Bank of China (Colombo Branch)">Bank of China (Colombo Branch)</option>
                <option value="Cargills Bank PLC">Cargills Bank PLC</option>
                <option value="Citibank N.A.">Citibank N.A.</option>
                <option value="Commercial Bank of Ceylon PLC">Commercial Bank of Ceylon PLC</option>
                <option value="Deutsche Bank AG">Deutsche Bank AG</option>
                <option value="DFCC Bank PLC">DFCC Bank PLC</option>
                <option value="Habib Bank Limited">Habib Bank Limited</option>
                <option value="Hatton National Bank PLC">Hatton National Bank PLC</option>
                <option value="Hongkong and Shanghai Banking Corporation (HSBC)">Hongkong and Shanghai Banking Corporation (HSBC)</option>
                <option value="ICICI Bank Ltd.">ICICI Bank Ltd.</option>
                <option value="Indian Bank">Indian Bank</option>
                <option value="Indian Overseas Bank">Indian Overseas Bank</option>
                <option value="MCB Bank Limited">MCB Bank Limited</option>
                <option value="National Development Bank PLC">National Development Bank PLC</option>
                <option value="Nations Trust Bank PLC">Nations Trust Bank PLC</option>
                <option value="Pan Asia Banking Corporation PLC">Pan Asia Banking Corporation PLC</option>
                <option value="People's Bank">People's Bank</option>
                <option value="Public Bank Berhad">Public Bank Berhad</option>
                <option value="Sampath Bank PLC">Sampath Bank PLC</option>
                <option value="Seylan Bank PLC">Seylan Bank PLC</option>
                <option value="Standard Chartered Bank">Standard Chartered Bank</option>
                <option value="State Bank of India">State Bank of India</option>
                <option value="Union Bank of Colombo PLC">Union Bank of Colombo PLC</option>
            </select>
        </div>
    </div>

    <!-- Credit specific fields -->
    <div class="row mt-3" id="creditFields" style="display: none;">
        <div class="col-md-5">
            <label for="creditPersonName">Credit Person / Company Name: <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="creditPersonName" placeholder="Enter name" required>
        </div>
        <div class="col-md-4">
            <label for="creditPersonPhone">Phone Number: <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="creditPersonPhone" placeholder="Enter phone" required>
        </div>
        <div class="col-md-3 d-flex align-items-end">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="creditSameAsCustomer">
                <label class="form-check-label" for="creditSameAsCustomer">Same as customer</label>
            </div>
        </div>
        <div class="col-md-4 mt-3">
            <label for="creditDueDate">Credit Date (Due): <span class="text-danger">*</span></label>
            <input type="date" class="form-control" id="creditDueDate" required>
        </div>
    </div>

    <!-- Multiple Payment Methods Section -->
    <div class="row mt-3" id="multiplePaymentsSection" style="display: none;">
        <div class="col-md-12">
            <div class="card border-info">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="fa fa-credit-card"></i> Payment Methods</h6>
                </div>
                <div class="card-body">
                    <div id="paymentMethodsList">
                        <!-- Payment methods will be added here dynamically -->
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-12">
                            <strong>Total Bill Amount: Rs. <span id="totalBillAmount"><?= number_format($total, 2) ?></span></strong>
                        </div>
                    </div>
                    <div class="row mt-1">
                        <div class="col-md-12">
                            <strong>Total Paid: Rs. <span id="totalPaidAmount">0.00</span></strong>
                        </div>
                    </div>
                    <div class="row mt-1">
                        <div class="col-md-12 d-flex align-items-center">
                            <strong>Balance: Rs. <span id="remainingBalance"><?= number_format($total, 2) ?></span></strong>
                            <span id="paymentStatus" class="badge bg-warning ms-2">Incomplete</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden fields shown only when payment is Card/Cheque/Bank Transfer -->
    <div class="row mt-3" id="additionalPaymentFields" style="display: none;">
        <div class="col-md-4">
            <label id="cardOrChequeNoLabel" for="cardOrChequeNo">Card / Cheque No: <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="cardOrChequeNo" name="cardOrChequeNo" required>
        </div>

        <div class="col-md-4" id="chequeDateGroup">
            <label for="chequeDate">Cheque Date: <span class="text-danger">*</span></label>
            <input type="date" class="form-control" id="chequeDate" name="chequeDate" required>
        </div>

        <div class="col-md-4">
            <label id="bankNameLabel" for="bankName">Bank Name: <span class="text-danger">*</span></label>
            <select class="form-select" id="bankName" name="bankName" required>
                <option value="">Select Bank</option>
                <option value="Amana Bank PLC">Amana Bank PLC</option>
                <option value="Bank of Ceylon">Bank of Ceylon</option>
                <option value="Bank of China (Colombo Branch)">Bank of China (Colombo Branch)</option>
                <option value="Cargills Bank PLC">Cargills Bank PLC</option>
                <option value="Citibank N.A.">Citibank N.A.</option>
                <option value="Commercial Bank of Ceylon PLC">Commercial Bank of Ceylon PLC</option>
                <option value="Deutsche Bank AG">Deutsche Bank AG</option>
                <option value="DFCC Bank PLC">DFCC Bank PLC</option>
                <option value="Habib Bank Limited">Habib Bank Limited</option>
                <option value="Hatton National Bank PLC">Hatton National Bank PLC</option>
                <option value="Hongkong and Shanghai Banking Corporation (HSBC)">Hongkong and Shanghai Banking Corporation (HSBC)</option>
                <option value="ICICI Bank Ltd.">ICICI Bank Ltd.</option>
                <option value="Indian Bank">Indian Bank</option>
                <option value="Indian Overseas Bank">Indian Overseas Bank</option>
                <option value="MCB Bank Limited">MCB Bank Limited</option>
                <option value="National Development Bank PLC">National Development Bank PLC</option>
                <option value="Nations Trust Bank PLC">Nations Trust Bank PLC</option>
                <option value="Pan Asia Banking Corporation PLC">Pan Asia Banking Corporation PLC</option>
                <option value="People's Bank">People's Bank</option>
                <option value="Public Bank Berhad">Public Bank Berhad</option>
                <option value="Sampath Bank PLC">Sampath Bank PLC</option>
                <option value="Seylan Bank PLC">Seylan Bank PLC</option>
                <option value="Standard Chartered Bank">Standard Chartered Bank</option>
                <option value="State Bank of India">State Bank of India</option>
                <option value="Union Bank of Colombo PLC">Union Bank of Colombo PLC</option>
            </select>
        </div>
    </div>

    <!-- Credit specific fields -->
    <div class="row mt-3" id="creditFields" style="display: none;">
        <div class="col-md-5">
            <label for="creditPersonName">Credit Person / Company Name: <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="creditPersonName" placeholder="Enter name" required>
        </div>
        <div class="col-md-4">
            <label for="creditPersonPhone">Phone Number: <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="creditPersonPhone" placeholder="Enter phone" required>
        </div>
        <div class="col-md-3 d-flex align-items-end">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="creditSameAsCustomer">
                <label class="form-check-label" for="creditSameAsCustomer">Same as customer</label>
            </div>
        </div>
    </div>

    <div class="row mt-3" id="noteFieldRow">
        <div class="col-md-12">
            <label for="note">Note:</label>
            <input type="text" class="form-control" id="note" name="note">
        </div>
    </div>

    <!-- Error Display Area -->
    <div class="row mt-3 mx-1" id="errorDiv"></div>

    <div class="text-end mt-3">
        <button class="btn btn-success" onclick="completeBill()">Complete Bill</button>
    </div>
<?php endif; ?>



<script>
    // Global variables for multiple payments
    let paymentMethods = [];
    let totalBillAmount = <?= $total ?>;
    let billDiscountPercent = 0;
    let billDiscountAmount = 0;
    
    // Global function for payment method UI
    function applyPaymentMethodUI() {
        const method = ($('#paymentMethod').val() || '');
        const addl = $('#additionalPaymentFields');
        const credit = $('#creditFields');

        if (method === 'Cash') {
            addl.slideUp();
            credit.slideUp();
            $('#cardOrChequeNo').val('');
            $('#chequeDate').val('');
            $('#bankName').val('');
        } else if (method === 'Credit') {
            addl.slideUp();
            credit.slideDown();
        } else if (method === 'Card' || method === 'Bank Transfer') {
            $('#cardOrChequeNoLabel').text('Receipt / Cheque No');
            $('#bankNameLabel').text('Bank Name');
            $('#chequeDateGroup').hide();
            credit.slideUp();
            addl.slideDown();
        } else if (method === 'Cheque') {
            $('#cardOrChequeNoLabel').text('Cheque No');
            $('#bankNameLabel').text('Bank Name');
            $('#chequeDateGroup').show();
            credit.slideUp();
            addl.slideDown();
        } else {
            addl.slideUp();
            credit.slideUp();
        }
    }

    $(document).ready(function() {
        // Initialize payment method UI on page load
        applyPaymentMethodUI();
        
        // Initialize payment status and balance
        updatePaymentStatus();
        bindBillDiscountInputs();
        
        // Add real-time validation feedback
        $('input, select').on('input change', function() {
            const field = $(this);
            if (field.prop('required')) {
                if (field.val().trim()) {
                    field.removeClass('is-invalid').addClass('is-valid');
                } else {
                    field.removeClass('is-valid').addClass('is-invalid');
                }
            }
        });

        $('#creditSameAsCustomer').on('change', function() {
            if (this.checked) {
                // Prefer hidden fields captured on selection for reliability
                const savedName = ($('#selectedCustomerNameHidden').val() || '').trim();
                const savedMobile = ($('#selectedCustomerMobileHidden').val() || '').trim();
                const selectedText = ($('#selectedCustomerText').text() || '').trim();
                const isSpecificCustomer = selectedText && selectedText.toLowerCase() !== 'select customer' && selectedText.toLowerCase() !== 'walk-in customer';

                if (isSpecificCustomer && savedName) {
                    $('#creditPersonName').val(savedName);
                    if (savedMobile) $('#creditPersonPhone').val(savedMobile);
                } else {
                    const walkinVisible = $('#walkInCustomerFields').is(':visible');
                    if (walkinVisible) {
                        $('#creditPersonName').val($('#customerName').val());
                        $('#creditPersonPhone').val($('#customerMobile').val());
                    }
                }
            } else {
                // Clear when unchecked
                $('#creditPersonName').val('');
                $('#creditPersonPhone').val('');
            }
        });
    });

    // Toggle customer dropdown
    function toggleCustomerDropdown() {
        const dropdown = document.getElementById('customerDropdown');
        const toggle = document.querySelector('.qb-dropdown-toggle');

        const isOpen = dropdown.dataset.open === '1';
        if (isOpen) {
            dropdown.dataset.open = '0';
            dropdown.classList.remove('show', 'show-above');
            toggle.classList.remove('active');
            dropdown.style.position = '';
            dropdown.style.top = '';
            dropdown.style.left = '';
            dropdown.style.width = '';
            document.body.removeChild(dropdown);
            document.querySelector('.custom-dropdown').appendChild(dropdown);
            return;
        }

        // Compute fixed position so it is not clipped by bill summary scroll
        const rect = toggle.getBoundingClientRect();
        const dropdownHeight = 300;
        const spaceBelow = window.innerHeight - rect.bottom;
        const showAbove = spaceBelow < dropdownHeight && rect.top > dropdownHeight;

        // Move to body for overlay
        document.body.appendChild(dropdown);
        dropdown.style.position = 'fixed';
        dropdown.style.left = rect.left + 'px';
        dropdown.style.width = rect.width + 'px';
        dropdown.style.top = (showAbove ? rect.top - dropdownHeight : rect.bottom) + 'px';
        dropdown.classList.remove('show-above');
        dropdown.classList.add('show');
        toggle.classList.add('active');
        dropdown.dataset.open = '1';

        setTimeout(() => {
            const input = document.getElementById('customerSearch');
            if (input) input.focus();
        }, 50);

        const closeHandler = (e) => {
            if (!dropdown.contains(e.target) && !toggle.contains(e.target)) {
                dropdown.dataset.open = '0';
                dropdown.classList.remove('show');
                toggle.classList.remove('active');
                document.body.removeEventListener('click', closeHandler, true);
                document.body.removeChild(dropdown);
                document.querySelector('.custom-dropdown').appendChild(dropdown);
            }
        };
        setTimeout(() => document.body.addEventListener('click', closeHandler, true), 0);
    }

    // Toggle payment method dropdown
    function togglePaymentDropdown() {
        const dropdown = document.getElementById('paymentDropdown');
        const toggle = dropdown.previousElementSibling;

        const isOpen = dropdown.dataset.open === '1';
        if (isOpen) {
            dropdown.dataset.open = '0';
            dropdown.classList.remove('show', 'show-above');
            toggle.classList.remove('active');
            return;
        }

        // Close any other open dropdowns
        document.querySelectorAll('.dropdown-menu').forEach(dd => {
            if (dd !== dropdown) {
                dd.dataset.open = '0';
                dd.classList.remove('show', 'show-above');
                dd.previousElementSibling.classList.remove('active');
            }
        });

        dropdown.classList.add('show');
        toggle.classList.add('active');
        dropdown.dataset.open = '1';

        const closeHandler = (e) => {
            if (!dropdown.contains(e.target) && !toggle.contains(e.target)) {
                dropdown.dataset.open = '0';
                dropdown.classList.remove('show');
                toggle.classList.remove('active');
                document.removeEventListener('click', closeHandler);
            }
        };
        setTimeout(() => document.addEventListener('click', closeHandler), 0);
    }

    // Select customer from dropdown
    function selectCustomer(element, value, text) {
        // Update hidden input
        document.getElementById('customer').value = value;
        
        // Update display text
        document.getElementById('selectedCustomerText').textContent = text;
        // Persist parsed name and mobile for reliable reuse
        try {
            const parts = String(text || '').split(' - ');
            const namePart = (parts[0] || '').trim();
            const mobilePart = (parts[1] || '').trim();
            document.getElementById('selectedCustomerNameHidden').value = namePart;
            document.getElementById('selectedCustomerMobileHidden').value = mobilePart;
        } catch (e) {}
        
        // Update selected state
        document.querySelectorAll('.option').forEach(opt => opt.classList.remove('selected'));
        element.classList.add('selected');
        
        // Close dropdown
        const dd = document.getElementById('customerDropdown');
        if (dd) {
            dd.classList.remove('show', 'show-above');
            dd.dataset.open = '0';
            // If dropdown was moved to body as overlay, move it back to container
            try {
                if (dd.parentNode === document.body) {
                    const container = document.querySelector('.custom-dropdown');
                    if (container) container.appendChild(dd);
                }
            } catch (e) {}
        }
        const toggleEl = document.querySelector('.qb-dropdown-toggle');
        if (toggleEl) toggleEl.classList.remove('active');
        
        // Handle customer selection immediately
        const selectedId = String(value);
        if (selectedId === '0') {
            $('#walkInCustomerFields').stop(true, true).slideDown();
        } else if (selectedId) {
            $('#walkInCustomerFields').stop(true, true).slideUp();
            $('#salutation').val('MR');
            $('#customerName').val('');
            $('#customerMobile').val('');
        } else {
            $('#walkInCustomerFields').stop(true, true).slideUp();
        }
    }

    // Select payment method from dropdown
    function selectPaymentMethod(element, value) {
        // Update hidden input
        document.getElementById('paymentMethod').value = value;
        
        // Update display text
        document.getElementById('selectedPaymentText').textContent = value;
        
        // Update selected state
        document.querySelectorAll('#paymentDropdown .option').forEach(opt => opt.classList.remove('selected'));
        element.classList.add('selected');
        
        // Close dropdown
        const dd = document.getElementById('paymentDropdown');
        if (dd) {
            dd.classList.remove('show', 'show-above');
            dd.dataset.open = '0';
        }
        const toggleEl = dd.previousElementSibling;
        if (toggleEl) toggleEl.classList.remove('active');
        
        // Trigger payment method change event
        applyPaymentMethodUI();
    }

    // Handle customer selection change
    function handleCustomerSelection() {
        const customerInput = document.getElementById('customer');
        if (!customerInput) return;
        const customerId = String(customerInput.value).trim();

        if (customerId === '0' || customerId === 0) {
            // Walk-in Customer selected
            $('#walkInCustomerFields').stop(true, true).slideDown();
        } else if (customerId) {
            // Existing customer selected
            $('#walkInCustomerFields').stop(true, true).slideUp();
            // Clear walk-in customer fields
            $('#salutation').val('MR');
            $('#customerName').val('');
            $('#customerMobile').val('');
        } else {
            // Nothing selected
            $('#walkInCustomerFields').stop(true, true).slideUp();
        }
    }

    // Filter customer options in real-time
    function filterCustomerOptions(searchTerm) {
        const searchLower = searchTerm.toLowerCase().trim();
        const options = document.querySelectorAll('.dropdown-options .option');
        
        options.forEach(option => {
            const optionText = option.textContent.toLowerCase();
            
            if (searchLower.length < 2 || optionText.includes(searchLower)) {
                option.classList.remove('hidden');
            } else {
                option.classList.add('hidden');
            }
        });
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        const dropdown = document.querySelector('.custom-dropdown');
        if (!dropdown.contains(e.target)) {
            document.getElementById('customerDropdown').classList.remove('show', 'show-above');
            document.querySelector('.qb-dropdown-toggle').classList.remove('active');
        }
    });

    // Save walk-in customer
    function saveWalkInCustomer() {
        const salutation = $('#salutation').val();
        const name = $('#customerName').val().trim();
        const mobile = $('#customerMobile').val().trim();

        // Basic validation
        if (!salutation || !name || !mobile) {
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'Please fill in all required fields (Salutation, Name, and Mobile Number).'
            });
            return;
        }

        if (mobile.length !== 10 || !/^\d+$/.test(mobile)) {
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'Mobile number must be exactly 10 digits.'
            });
            return;
        }

        // Check if name is too short
        if (name.length < 2) {
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'Customer name must be at least 2 characters long.'
            });
            return;
        }

        // Check if mobile number already exists in the dropdown
        let mobileExists = false;
        $('#customer option').each(function() {
            const optionText = $(this).text();
            if (optionText.includes(mobile) && $(this).val() !== '0' && $(this).val() !== '') {
                mobileExists = true;
                return false; // break the loop
            }
        });

        if (mobileExists) {
            Swal.fire({
                icon: 'warning',
                title: 'Mobile Number Exists',
                text: 'A customer with this mobile number already exists. Please select the existing customer instead.'
            });
            return;
        }

        // Show loading
        Swal.fire({
            title: 'Saving Customer...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Make AJAX call to save customer
        $.ajax({
            url: '<?= base_url("quickbill/saveWalkInCustomer") ?>',
            type: 'POST',
            data: {
                salutation: salutation,
                name: name,
                mobile: mobile
            },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message
                    }).then(() => {
                        // Add the new customer to the dropdown
                        const newOption = document.createElement('div');
                        newOption.className = 'option';
                        newOption.setAttribute('data-value', response.customer.customers_id);
                        newOption.onclick = function() {
                            selectCustomer(this, response.customer.customers_id, name + ' - ' + mobile);
                        };
                        newOption.textContent = name + ' - ' + mobile;
                        
                        // Insert after Walk-in Customer option
                        const walkInOption = document.querySelector('.option[data-value="0"]');
                        walkInOption.parentNode.insertBefore(newOption, walkInOption.nextSibling);
                        
                        // Select the new customer
                        selectCustomer(newOption, response.customer.customers_id, name + ' - ' + mobile);
                        
                        // Hide walk-in customer fields
                        $('#walkInCustomerFields').slideUp();
                        
                        // Clear the fields
                        $('#salutation').val('MR');
                        $('#customerName').val('');
                        $('#customerMobile').val('');
                        
                        // Clear search input
                        document.getElementById('customerSearch').value = '';
                        filterCustomerOptions(''); // Reset filter
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while saving the customer. Please try again.'
                });
            }
        });
    }

    // Add input validation for mobile number
    $(document).ready(function() {
        $('#customerMobile').on('input', function() {
            const value = $(this).val();
            // Remove any non-digit characters
            const cleanValue = value.replace(/\D/g, '');
            // Limit to 10 digits
            if (cleanValue.length > 10) {
                $(this).val(cleanValue.substring(0, 10));
            } else {
                $(this).val(cleanValue);
            }
        });
    });

    function bindBillDiscountInputs() {
        const $dp = $('#totalDiscountPercent');
        const $da = $('#totalDiscountAmount');
        // reset
        $dp.val('');
        $da.val('');

        function recomputeFromPercent() {
            const p = parseFloat($dp.val()) || 0;
            if (p > 0) { $da.val(''); }
            billDiscountPercent = Math.max(0, Math.min(100, p));
            billDiscountAmount = 0;
            recalcGrandTotal();
        }

        function recomputeFromAmount() {
            const a = parseFloat($da.val()) || 0;
            if (a > 0) { $dp.val(''); }
            billDiscountAmount = Math.max(0, a);
            billDiscountPercent = 0;
            recalcGrandTotal();
        }

        $dp.on('input', recomputeFromPercent);
        $da.on('input', recomputeFromAmount);
        recalcGrandTotal();
    }

    function recalcGrandTotal() {
        const subtotal = <?= $total ?>;
        let discountValue = 0;
        if (billDiscountPercent > 0) {
            discountValue = (subtotal * billDiscountPercent) / 100.0;
        } else if (billDiscountAmount > 0) {
            discountValue = billDiscountAmount;
        }
        discountValue = Math.min(discountValue, subtotal);
        const grand = subtotal - discountValue;
        totalBillAmount = grand;
        $('#grandTotalDisplay').text(grand.toFixed(2));
        $('#totalBillAmount').text(grand.toFixed(2));
        $('#remainingBalance').text((grand - paymentMethods.reduce((s,p)=> s + parseFloat(p.amount||0), 0)).toFixed(2));
        // Auto-fill paid amount with grand total when no payments have been added yet
        if (paymentMethods.length === 0) {
            $('#paidAmount').val(grand.toFixed(2));
        }
        updatePaymentStatus();
    }

    // Function to complete the bill
    function completeBill() {
        const customerId = document.getElementById('customer').value;
        const note = $('#note').val();

        // Basic validation
        if (!customerId) {
            Swal.fire('Error', 'Please select a customer or register as a walk-in customer.', 'error');
            return;
        }

        // Check if any payments have been added
        if (paymentMethods.length === 0) {
            Swal.fire('Error', 'Please add at least one payment method before completing the bill.', 'error');
            return;
        }
        
        // Validate total payments match bill total
        const totalPaid = paymentMethods.reduce((sum, payment) => sum + parseFloat(payment.amount), 0);
        const balance = totalBillAmount - totalPaid;
        
        // Allow partial payments but show appropriate warnings
        if (balance > 0) {
            // Partial payment - show warning but allow continuation
            Swal.fire({
                title: 'Partial Payment',
                text: `Total paid amount (Rs. ${totalPaid.toFixed(2)}) is less than bill total (Rs. ${totalBillAmount.toFixed(2)}). Balance remaining: Rs. ${balance.toFixed(2)}. Do you want to continue with partial payment?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Continue',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    proceedWithBillCompletion();
                }
            });
            return;
        }
        
        if (balance < 0) {
            // Overpaid - show warning but allow continuation
            Swal.fire({
                title: 'Overpaid Amount',
                text: `Total paid amount (Rs. ${totalPaid.toFixed(2)}) is more than bill total (Rs. ${totalBillAmount.toFixed(2)}). Overpaid amount: Rs. ${Math.abs(balance).toFixed(2)}. Do you want to continue?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Continue',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    proceedWithBillCompletion();
                }
            });
            return;
        }
        
        // Exact payment - proceed directly
        proceedWithBillCompletion();
    }
    
    function proceedWithBillCompletion() {
        const customerId = document.getElementById('customer').value;
        const note = $('#note').val();

        // Show loading
        Swal.fire({
            title: 'Saving Bill...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Make AJAX call to save the bill
        $.ajax({
            url: "<?php echo site_url('quickBill/saveCompletedBill'); ?>",
            type: "POST",
            dataType: "json",
            data: {
                customerId,
                paymentMethods: JSON.stringify(paymentMethods),
                billDiscountPercent: billDiscountPercent,
                billDiscountAmount: billDiscountAmount,
                note
            },
            success: function(res) {
                if (res.status === 'success') {
                    const base_url = "<?= base_url(); ?>";
                    const receiptUrl = base_url + 'quick_bill/print_receipt/' + res.bill_id;
                    window.open(receiptUrl, '_blank');

                    // Then show the success dialog (after opening window)
                    Swal.fire({
                        icon: 'success',
                        title: 'Saved!',
                        text: 'Quick bill saved successfully. Receipt opened in new tab.',
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

    // Function to add a new payment method
    function addPaymentMethod() {
        const method = $('#paymentMethod').val();
        const amount = parseFloat($('#paidAmount').val()) || 0;
        
        if (!method) {
            Swal.fire('Error', 'Please select a payment method.', 'error');
            return;
        }
        
        if (amount <= 0) {
            Swal.fire('Error', 'Please enter a valid amount.', 'error');
            return;
        }
        
        // Validate required fields based on payment method
        let validationError = '';
        
        if (method === 'Card' || method === 'Bank Transfer') {
            if (!$('#cardOrChequeNo').val().trim()) {
                validationError = 'Please enter Receipt/Cheque Number for ' + method + ' payment.';
            } else if (!$('#bankName').val()) {
                validationError = 'Please select Bank Name for ' + method + ' payment.';
            }
        } else if (method === 'Cheque') {
            if (!$('#cardOrChequeNo').val().trim()) {
                validationError = 'Please enter Cheque Number for Cheque payment.';
            } else if (!$('#chequeDate').val()) {
                validationError = 'Please enter Cheque Date for Cheque payment.';
            } else if (!$('#bankName').val()) {
                validationError = 'Please select Bank Name for Cheque payment.';
            }
        } else if (method === 'Credit') {
            if (!$('#creditPersonName').val().trim()) {
                validationError = 'Please enter Credit Person/Company Name for Credit payment.';
            } else if (!$('#creditPersonPhone').val().trim()) {
                validationError = 'Please enter Phone Number for Credit payment.';
            } else if (!$('#creditDueDate').val()) {
                validationError = 'Please select Credit Date (Due) for Credit payment.';
            }
        }
        
        if (validationError) {
            Swal.fire('Error', validationError, 'error');
            return;
        }
        
        // Check if this payment method already exists
        const existingIndex = paymentMethods.findIndex(p => p.method === method);
        if (existingIndex !== -1) {
            Swal.fire('Error', 'This payment method has already been added.', 'error');
            return;
        }
        
        // Add to payment methods array
        paymentMethods.push({
            method: method,
            amount: amount,
            cardOrChequeNo: $('#cardOrChequeNo').val() || '',
            chequeDate: $('#chequeDate').val() || '',
            bankName: $('#bankName').val() || '',
            creditPersonName: $('#creditPersonName').val() || '',
            creditPersonPhone: $('#creditPersonPhone').val() || '',
            creditDueDate: $('#creditDueDate').val() || ''
        });
        
        // Show multiple payments section
        $('#multiplePaymentsSection').show();
        
        // Update payment methods list
        updatePaymentMethodsList();
        
        // Clear the form
        $('#paymentMethod').val('');
        $('#cardOrChequeNo').val('');
        $('#chequeDate').val('');
        $('#bankName').val('');
        $('#creditPersonName').val('');
        $('#creditPersonPhone').val('');
        $('#additionalPaymentFields').hide();
        $('#creditFields').hide();
        $('#creditDueDate').val('');
        // Show remaining balance as next payment amount
        const totalAlreadyPaid = paymentMethods.reduce((s,p) => s + parseFloat(p.amount||0), 0);
        const remainingAmt = Math.max(0, totalBillAmount - totalAlreadyPaid);
        $('#paidAmount').val(remainingAmt > 0 ? remainingAmt.toFixed(2) : '');
        
        // Reset required field styling
        $('.form-control:required, .form-select:required').removeClass('is-valid is-invalid');
        
        // Update payment status
        updatePaymentStatus();
    }
    
    // Function to update payment methods list display
    function updatePaymentMethodsList() {
        const container = $('#paymentMethodsList');
        container.empty();
        
        paymentMethods.forEach((payment, index) => {
            const paymentHtml = `
                <div class="row mb-2 payment-method-item" data-index="${index}">
                    <div class="col-md-3">
                        <strong>${payment.method}</strong>
                    </div>
                    <div class="col-md-3">
                        Rs. ${payment.amount.toFixed(2)}
                    </div>
                    <div class="col-md-4">
                        ${getPaymentDetails(payment)}
                    </div>
                    <div class="col-md-2 text-end">
                        <button type="button" class="btn btn-danger btn-sm" onclick="removePaymentMethod(${index})">
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>
                </div>
            `;
            container.append(paymentHtml);
        });
    }
    
    // Function to get payment details for display
    function getPaymentDetails(payment) {
        let details = '';
        
        if (payment.method.toLowerCase() === 'card' || payment.method.toLowerCase() === 'bank transfer') {
            if (payment.cardOrChequeNo) details += `Receipt: ${payment.cardOrChequeNo}`;
            if (payment.bankName) details += details ? ` | Bank: ${payment.bankName}` : `Bank: ${payment.bankName}`;
        } else if (payment.method.toLowerCase() === 'cheque') {
            if (payment.cardOrChequeNo) details += `Cheque: ${payment.cardOrChequeNo}`;
            if (payment.chequeDate) details += details ? ` | Date: ${payment.chequeDate}` : `Date: ${payment.chequeDate}`;
            if (payment.bankName) details += details ? ` | Bank: ${payment.bankName}` : `Bank: ${payment.bankName}`;
        } else if (payment.method.toLowerCase() === 'credit') {
            if (payment.creditPersonName) details += `To: ${payment.creditPersonName}`;
            if (payment.creditPersonPhone) details += details ? ` | Phone: ${payment.creditPersonPhone}` : `Phone: ${payment.creditPersonPhone}`;
        }
        
        return details || '-';
    }
    
    // Function to remove a payment method
    function removePaymentMethod(index) {
        paymentMethods.splice(index, 1);
        updatePaymentMethodsList();
        updatePaymentStatus();

        if (paymentMethods.length === 0) {
            $('#multiplePaymentsSection').hide();
            $('#paidAmount').val(totalBillAmount.toFixed(2));
        } else {
            const totalAlreadyPaid = paymentMethods.reduce((s,p) => s + parseFloat(p.amount||0), 0);
            const remainingAmt = Math.max(0, totalBillAmount - totalAlreadyPaid);
            $('#paidAmount').val(remainingAmt > 0 ? remainingAmt.toFixed(2) : '');
        }
    }
    
    // Function to update payment status
    function updatePaymentStatus() {
        const totalPaid = paymentMethods.reduce((sum, payment) => sum + parseFloat(payment.amount), 0);
        const balance = totalBillAmount - totalPaid;
        
        console.log('Debug - totalBillAmount:', totalBillAmount);
        console.log('Debug - paymentMethods:', paymentMethods);
        console.log('Debug - totalPaid:', totalPaid);
        console.log('Debug - balance:', balance);
        
        $('#totalPaidAmount').text(totalPaid.toFixed(2));
        $('#remainingBalance').text(balance.toFixed(2));
        
        const statusElement = $('#paymentStatus');
        if (Math.abs(balance) < 0.01) { // Account for floating point precision
            statusElement.text('Complete').removeClass('bg-warning bg-danger bg-info').addClass('bg-success');
        } else if (balance < 0) {
            statusElement.text('Overpaid').removeClass('bg-warning bg-success bg-info').addClass('bg-danger');
        } else if (balance > 0 && balance < totalBillAmount) {
            statusElement.text('Partial').removeClass('bg-warning bg-success bg-danger').addClass('bg-info');
        } else {
            statusElement.text('Incomplete').removeClass('bg-success bg-info bg-danger').addClass('bg-warning');
        }
    }
</script>