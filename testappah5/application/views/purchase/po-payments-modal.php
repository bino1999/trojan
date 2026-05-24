<!-- PO Payments Modal -->
<div class="modal fade" id="poPaymentsModal" tabindex="-1" aria-labelledby="poPaymentsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="poPaymentsModalLabel">
                    <i class="fa fa-credit-card"></i> Purchase Order Payments
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- PO Information -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Bill No:</strong> <?= $po->bill_no ?><br>
                        <strong>Supplier:</strong> <?= $po->supplier_name ?><br>
                        <strong>Bill Date:</strong> <?= date('d/m/Y', strtotime($po->bill_date)) ?>
                    </div>
                    <div class="col-md-6 text-end">
                        <strong>Total Amount:</strong> Rs. <?= number_format($po->total_amount, 2) ?><br>
                        <strong>Actually Paid:</strong> Rs. <span id="totalPaidAmount"><?= number_format($po->total_paid ?? 0, 2) ?></span><br>
                        <strong>Outstanding Balance:</strong> Rs. <span id="remainingBalance"><?= number_format(($po->total_amount - ($po->total_paid ?? 0)), 2) ?></span>
                    </div>
                </div>

                <!-- Add Payment Form -->
                <div class="card border-primary mb-3" id="addPaymentCard">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0"><i class="fa fa-plus"></i> Add Payment</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
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
                                <input type="number" class="form-control" id="paidAmount" value="<?= ($po->total_amount - ($po->total_paid ?? 0)) ?>" step="0.01" min="0.01" max="<?= ($po->total_amount - ($po->total_paid ?? 0)) ?>">
                            </div>
                            <div class="col-md-3">
                                <label>&nbsp;</label>
                                <button type="button" class="btn btn-primary" onclick="addPoPayment()" style="width: 100%;">
                                    <i class="fa fa-plus"></i> Add Payment
                                </button>
                            </div>
                        </div>

                        <!-- Additional Payment Fields -->
                        <div class="row mt-3" id="additionalPaymentFields" style="display: none;">
                            <div class="col-md-4">
                                <label for="cardOrChequeNo" id="cardOrChequeNoLabel">Receipt/Cheque No:</label>
                                <input type="text" class="form-control" id="cardOrChequeNo" placeholder="">
                            </div>
                            <div class="col-md-4" id="chequeDateGroup" style="display: none;">
                                <label for="chequeDate">Cheque Date:</label>
                                <input type="date" class="form-control" id="chequeDate">
                            </div>
                            <div class="col-md-4">
                                <label for="bankName" id="bankNameLabel">Bank Name:</label>
                                <input type="text" class="form-control" id="bankName" placeholder="">
                            </div>
                        </div>

                        <!-- Credit Fields - Removed for PO payments as we are the ones paying -->
                    </div>
                </div>

                <!-- Payment Methods List removed as requested -->

                <!-- Existing Payments -->
                <?php if (!empty($payments)): ?>
                <div class="card border-success">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0"><i class="fa fa-list"></i> Existing Payments</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Payment Method</th>
                                        <th>Amount</th>
                                        <th>Details</th>
                                        <th>Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $paymentsTotal = 0; 
                                    $creditTotal = 0;
                                    $actualPaymentsTotal = 0;
                                    foreach ($payments as $index => $payment): 
                                        $paymentsTotal += (float)$payment->paid_amount;
                                        if (strtolower($payment->payment_method) === 'credit') {
                                            $creditTotal += (float)$payment->paid_amount;
                                        } else {
                                            $actualPaymentsTotal += (float)$payment->paid_amount;
                                        }
                                    ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td>
                                            <?= $payment->payment_method ?>
                                            <?php if (strtolower($payment->payment_method) === 'credit'): ?>
                                                <span class="badge bg-warning text-dark ms-1">Credit</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>Rs. <?= number_format($payment->paid_amount, 2) ?></td>
                                        <td><?= getPaymentDetails($payment) ?></td>
                                        <td><?= date('d/m/Y H:i', strtotime($payment->payment_date)) ?></td>
                                        <td>
                                            <button class="btn btn-danger btn-sm" onclick="deletePoPayment(<?= $payment->id ?>, <?= $po->po_id ?>)">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="row mt-2 align-items-center">
                            <div class="col-md-6">
                                <strong>Total Bill Amount: Rs. <?= number_format($po->total_amount, 2) ?></strong>
                            </div>
                            <div class="col-md-6 text-end">
                                <strong>Actually Paid: Rs. <?= number_format($actualPaymentsTotal, 2) ?></strong><br>
                                <?php if ($creditTotal > 0): ?>
                                    <strong>Credit Balance: Rs. <?= number_format($creditTotal, 2) ?></strong><br>
                                <?php endif; ?>
                                <strong>Outstanding Balance: Rs. <?= number_format(max(($po->total_amount - $actualPaymentsTotal), 0), 2) ?></strong>
                            </div>
                        </div>
                        <?php $isCompleted = (isset($po->payment_status) && strtolower($po->payment_status) === 'completed'); ?>
                        <div class="text-end mt-3">
                            <?php
                                $canConfirm = (!$isCompleted && ($actualPaymentsTotal >= (float)$po->total_amount));
                                $btnDisabled = $canConfirm ? '' : 'disabled';
                                $btnText = $isCompleted ? 'Payments Confirmed' : 'Confirm Payments';
                                $btnClass = $isCompleted ? 'btn-secondary' : 'btn-info';
                            ?>
                            <button class="btn <?= $btnClass ?>" id="confirmPaymentsBtn" onclick="confirmPayments()" <?= $btnDisabled ?>>
                                <i class="fa fa-check"></i> <?= $btnText ?>
                            </button>
                            <?php if ($creditTotal > 0): ?>
                                <div class="mt-2">
                                    <small class="text-warning">
                                        <i class="fa fa-exclamation-triangle"></i> 
                                        This PO has credit balance of Rs. <?= number_format($creditTotal, 2) ?>. 
                                        Credit must be paid through Accounts > Credits before confirming.
                                    </small>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?php
function getPaymentDetails($payment) {
    $details = [];
    
    if ($payment->card_or_cheque_no) {
        $details[] = 'No: ' . $payment->card_or_cheque_no;
    }
    if ($payment->cheque_date) {
        $details[] = 'Date: ' . date('d/m/Y', strtotime($payment->cheque_date));
    }
    if ($payment->bank_name) {
        $details[] = 'Bank: ' . $payment->bank_name;
    }
    if ($payment->note) {
        $details[] = 'Note: ' . $payment->note;
    }
    
    return !empty($details) ? implode(' | ', $details) : '-';
}
?>

<style>
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
    border-radius: 4px;
    background-color: white;
    cursor: pointer;
    min-height: 38px;
}

.qb-dropdown-toggle:hover {
    border-color: #80bdff;
}

.dropdown-menu {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #ced4da;
    border-radius: 4px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    z-index: 1000;
    max-height: 200px;
    overflow-y: auto;
    display: none;
}

.dropdown-options {
    padding: 0;
}

.option {
    padding: 8px 12px;
    cursor: pointer;
    border-bottom: 1px solid #f8f9fa;
}

.option:hover {
    background-color: #f8f9fa;
}

.option:last-child {
    border-bottom: none;
}
</style>

<script id="poPaymentsModalScript">
// Global variables
window.currentPoId = Number(<?= isset($po->po_id) ? (int)$po->po_id : 0 ?>);

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
        credit.slideUp(); // Don't show credit fields for PO payments
    } else if (method === 'Card' || method === 'Bank Transfer') {
        $('#cardOrChequeNoLabel').text('Receipt / Transaction ID');
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

// Function to toggle payment dropdown
function togglePaymentDropdown() {
    const dropdown = document.getElementById('paymentDropdown');
    dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
}

// Function to select payment method
function selectPaymentMethod(element, value) {
    $('#selectedPaymentText').text(value);
    $('#paymentMethod').val(value);
    document.getElementById('paymentDropdown').style.display = 'none';
    applyPaymentMethodUI();
}

// Function to add a new payment
    function addPoPayment() {
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
                validationError = 'Please enter Receipt/Transaction ID for ' + method + ' payment.';
            } else if (!$('#bankName').val().trim()) {
                validationError = 'Please enter Bank Name for ' + method + ' payment.';
            }
        } else if (method === 'Cheque') {
            if (!$('#cardOrChequeNo').val().trim()) {
                validationError = 'Please enter Cheque Number for Cheque payment.';
            } else if (!$('#chequeDate').val()) {
                validationError = 'Please enter Cheque Date for Cheque payment.';
            } else if (!$('#bankName').val().trim()) {
                validationError = 'Please enter Bank Name for Cheque payment.';
            }
        } else if (method === 'Credit') {
            // No validation needed for credit payments in PO as we are the ones paying
        }
        
        if (validationError) {
            Swal.fire('Error', validationError, 'error');
            return;
        }
        
        // Submit payment to server
        submitPaymentToServer(method, amount);
    }
    
    function submitPaymentToServer(method, amount) {
        const paymentData = {
            po_id: currentPoId,
            payment_method: method,
            paid_amount: amount,
            card_or_cheque_no: $('#cardOrChequeNo').val() || '',
            cheque_date: $('#chequeDate').val() || '',
            bank_name: $('#bankName').val() || '',
            note: '',
            credit_person_name: '', // Not needed for PO payments
            credit_person_phone: '' // Not needed for PO payments
        };
        
        // Send payment to server
        $.ajax({
            url: "<?php echo site_url('purchase/addPoPayment'); ?>",
            type: "POST",
            data: paymentData,
            success: function(response) {
                try {
                    const result = JSON.parse(response);
                    if (result.status === 'success') {
                        Swal.fire('Success', result.message, 'success');
                        
                        // Clear the form after successful submission
                        $('#paymentMethod').val('');
                        $('#selectedPaymentText').text('Select Payment Method');
                        $('#paidAmount').val('');
                        $('#cardOrChequeNo').val('');
                        $('#chequeDate').val('');
                        $('#bankName').val('');
                        // Credit fields removed for PO payments
                        $('#additionalPaymentFields').hide();
                        $('#creditFields').hide();
                        
                        // Refresh the payments modal content without full reload
                        $.ajax({
                            url: "<?php echo site_url('purchase/loadPoPayments'); ?>",
                            type: "POST",
                            data: { po_id: currentPoId },
                            success: function(html) {
                                // Clean up any existing modal/backdrop to avoid dark overlay / blocked UI
                                $('#poPaymentsModal').remove();
                                $('.modal-backdrop').remove();
                                $('body').removeClass('modal-open').css('overflow', '');

                                var $fragment = $(html);
                                $('body').append($fragment);
                                $fragment.filter('script').each(function() {
                                    var code = this.text || this.textContent || this.innerHTML || '';
                                    if (code.trim()) { $.globalEval(code); }
                                });
                                $('#poPaymentsModal').modal('show');
                            }
                        });
                    } else {
                        Swal.fire('Error', result.message, 'error');
                    }
                } catch (e) {
                    Swal.fire('Error', 'Invalid response from server', 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'Failed to add payment', 'error');
            }
        });
    }

function confirmPayments() {
    $.ajax({
        url: "<?php echo site_url('purchase/confirmPoPayments'); ?>",
        type: "POST",
        data: { po_id: currentPoId },
        success: function(response) {
            try {
                const res = JSON.parse(response);
                if (res.status === 'success') {
                    Swal.fire('Confirmed', res.message, 'success');
                    // Refresh modal content to reflect locked state
                    $.ajax({
                        url: "<?php echo site_url('purchase/loadPoPayments'); ?>",
                        type: "POST",
                        data: { po_id: currentPoId },
                        success: function(html) {
                            // Clean up any existing modal/backdrop
                            $('#poPaymentsModal').remove();
                            $('.modal-backdrop').remove();
                            $('body').removeClass('modal-open').css('overflow', '');
                            var $fragment = $(html);
                            $('body').append($fragment);
                            $fragment.filter('script').each(function() {
                                var code = this.text || this.textContent || this.innerHTML || '';
                                if (code.trim()) { $.globalEval(code); }
                            });
                            $('#poPaymentsModal').modal('show');
                            if (typeof loadPurchases === 'function') { loadPurchases(); }
                        }
                    });
                } else {
                    Swal.fire('Error', res.message, 'error');
                }
            } catch (e) {
                Swal.fire('Error', 'Invalid response from server', 'error');
            }
        },
        error: function() {
            Swal.fire('Error', 'Failed to confirm payments', 'error');
        }
    });
}

// Helper to format details for dynamic UI (kept for potential inline use)
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

// Removed multiple-payments UI helpers as requested

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    const dropdown = document.getElementById('paymentDropdown');
    const toggle = document.querySelector('.qb-dropdown-toggle');
    
    if (!dropdown.contains(event.target) && !toggle.contains(event.target)) {
        dropdown.style.display = 'none';
    }
});

// Removed auto-initialization of multiple-payments section
</script>
