<?php
// This view is loaded in the edit modal
// In CodeIgniter, variables passed via $data become available directly.
// Ensure we have safe locals and compute payment method from $payments.
$paymentMethod = '';
if (!empty($payments)) {
    $paymentMethod = $payments[0]->payment_method ?? '';
}
$billRow = !empty($bill) ? $bill[0] : null;
?>

<style>
.edit-form-container {
    max-height: none;
    overflow: visible;
}

.edit-items-table th {
    background-color: #f8f9fa;
    font-weight: 600;
    font-size: 0.9rem;
}

.edit-items-table td {
    vertical-align: middle;
}

.edit-items-table input {
    font-size: 0.9rem;
}

.edit-items-table .form-control {
    border: 1px solid #dee2e6;
}

.edit-items-table .form-control:focus {
    border-color: #2196F3;
    box-shadow: 0 0 0 0.2rem rgba(33, 150, 243, 0.25);
}

.summary-card {
    border: 1px solid #e3f2fd;
    border-radius: 0.5rem;
}

.summary-card .card-header {
    background: linear-gradient(135deg, #2196F3, #1976D2);
    color: white;
    border-radius: 0.5rem 0.5rem 0 0;
    padding: 0.75rem 1rem;
}

.summary-card .card-body {
    padding: 1rem;
}

.summary-row {
    padding: 0.5rem 0;
    border-bottom: 1px solid #f1f3f4;
}

.summary-row:last-child {
    border-bottom: none;
}

.payment-history-table {
    font-size: 0.85rem;
}

.payment-history-table th {
    background-color: #f8f9fa;
    font-weight: 600;
    font-size: 0.8rem;
    padding: 0.5rem;
}

.payment-history-table td {
    padding: 0.5rem;
    vertical-align: middle;
}

.payment-history-table .badge {
    font-size: 0.75rem;
}

.payment-history-table .btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
}
</style>

<div class="edit-form-container">
    <form id="editQuickBillForm">
        <input type="hidden" id="editBillId" value="<?= $billRow->quick_bill_id ?? 0 ?>">
        
        <!-- Customer Selection -->
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="editCustomer" class="form-label">Customer:</label>
                <select class="form-control" id="editCustomer" required disabled>
                    <option value="0" <?= ($billRow && $billRow->customer_id == 0) ? 'selected' : '' ?>>Walk-in Customer</option>
                    <?php foreach ($customers as $cust): ?>
                        <option value="<?= $cust->customers_id ?>" <?= ($billRow && $billRow->customer_id == $cust->customers_id) ? 'selected' : '' ?>>
                            <?= ucfirst($cust->name) ?> - <?= $cust->mobile ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if ($billRow): ?>
                <small class="text-muted">
                    <?php if ((int)$billRow->customer_id === 0): ?>
                        <span class="badge bg-secondary">W-C</span>
                    <?php endif; ?>
                    <?= htmlspecialchars($billRow->customer_name ?? '') ?>
                    <?php if (!empty($billRow->customer_mobile)): ?> - <?= htmlspecialchars($billRow->customer_mobile) ?><?php endif; ?>
                </small>
                <?php endif; ?>
            </div>
            <div class="col-md-6">
                <label for="editPaymentMethod" class="form-label">Payment Method:</label>
                <select class="form-control" id="editPaymentMethod" required disabled>
                    <?php foreach ($payment_methods as $method): ?>
                        <option value="<?= $method ?>" <?= ($paymentMethod == $method) ? 'selected' : '' ?>>
                            <?= $method ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Bill Items -->
        <div class="row mb-3">
            <div class="col-12">
                <h6>Bill Items</h6>
                <div class="table-responsive">
                                         <table class="table table-bordered edit-items-table" id="editItemsTable">
                        <thead class="table-light">
                            <tr>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Unit Price</th>
                                <th>Discount %</th>
                                <th>Discount Amount</th>
                                <th>Total</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="editItemsBody">
                            <?php foreach ($items as $index => $item): ?>
                                <tr data-index="<?= $index ?>">
                                    <td>
                                        <input type="text" class="form-control" value="<?= htmlspecialchars($item->item_name) ?>" readonly>
                                        <input type="hidden" name="items[<?= $index ?>][product_id]" value="<?= $item->item_id ?>">
                                        <input type="hidden" name="items[<?= $index ?>][po_item_id]" value="<?= $item->po_item_id ?>">
                                    </td>
                                    <td>
                                        <input type="number" class="form-control quantity-input" name="items[<?= $index ?>][quantity]" 
                                               value="<?= $item->quantity ?>" min="0" step="1" onchange="calculateItemTotal(<?= $index ?>)" disabled>
                                    </td>
                                    <td>
                                        <input type="number" class="form-control price-input" name="items[<?= $index ?>][unit_price]" 
                                               value="<?= $item->price ?>" min="0" step="0.01" onchange="calculateItemTotal(<?= $index ?>)" disabled>
                                    </td>
                                    <td>
                                        <input type="number" class="form-control discount-percent-input" name="items[<?= $index ?>][discount_percent]" 
                                               value="<?= $item->discount_percent ?>" min="0" max="100" step="0.01" onchange="calculateItemTotal(<?= $index ?>)" disabled>
                                    </td>
                                    <td>
                                        <input type="number" class="form-control discount-amount-input" name="items[<?= $index ?>][discount_amount]" 
                                               value="<?= $item->discount_amount ?>" min="0" step="0.01" onchange="calculateItemTotal(<?= $index ?>)" disabled>
                                    </td>
                                    <td>
                                        <?php
                                            $rowTotal = (float)$item->quantity * (float)$item->price;
                                            if ((float)$item->discount_percent > 0) {
                                                $rowTotal = $rowTotal - ($rowTotal * (float)$item->discount_percent / 100.0);
                                            } elseif ((float)$item->discount_amount > 0) {
                                                $rowTotal = $rowTotal - (float)$item->discount_amount;
                                            }
                                        ?>
                                        <input type="number" class="form-control total-input" name="items[<?= $index ?>][total]" 
                                               value="<?= number_format($rowTotal, 2, '.', '') ?>" readonly>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-danger btn-sm" onclick="removeEditItem(<?= $index ?>)" disabled>
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>


        <!-- Payment History Section -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="card summary-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Payment History</h6>
                        <div>
                            <button type="button" class="btn btn-sm btn-success me-2" onclick="addNewPayment()">
                                <i class="fa fa-plus"></i> Add Payment
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="refreshPaymentHistory()">
                                <i class="fa fa-refresh"></i> Refresh
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info mb-3">
                            <i class="fa fa-info-circle"></i>
                            <strong>Note:</strong> This shows all payment records for this bill. Multiple payments may exist for the same bill.
                        </div>
                        <div id="paymentHistoryContainer">
                            <div class="text-center text-muted">
                                <i class="fa fa-spinner fa-spin"></i> Loading payment history...
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="row">
            <div class="col-12 d-flex justify-content-between">
                <div>
                    <button type="button" id="enableEditBtn" class="btn btn-warning">Edit</button>
                </div>
                <div>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" id="updateBillBtn" class="btn btn-primary" disabled>Update Bill</button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
$(document).ready(function() {
    // Initialize totals
    calculateEditTotal();
    
    // Form submission
    $('#editQuickBillForm').on('submit', function(e) {
        e.preventDefault();
        updateQuickBill();
    });


    // Enable editing
    $('#enableEditBtn').on('click', function() {
        $('#editQuickBillForm .form-control').prop('disabled', false);
        $('#editQuickBillForm .btn-danger').prop('disabled', false);
        $('#editPaymentMethod').prop('disabled', false);
        $('#editCustomer').prop('disabled', false);
        $('#updateBillBtn').prop('disabled', false);
        
        // Enable payment edit/delete buttons
        $('.payment-edit-btn').prop('disabled', false);
        $('.payment-delete-btn').prop('disabled', false);
    });

    // Handle discount input changes to ensure only one type is used
    $(document).on('input', '.discount-percent-input', function() {
        const percentValue = parseFloat($(this).val()) || 0;
        if (percentValue > 0) {
            $(this).closest('tr').find('.discount-amount-input').val('0');
        }
    });

    $(document).on('input', '.discount-amount-input', function() {
        const amountValue = parseFloat($(this).val()) || 0;
        if (amountValue > 0) {
            $(this).closest('tr').find('.discount-percent-input').val('0');
        }
    });
});

// Calculate item total
function calculateItemTotal(index) {
    const row = $(`tr[data-index="${index}"]`);
    const quantity = parseFloat(row.find('.quantity-input').val()) || 0;
    const price = parseFloat(row.find('.price-input').val()) || 0;
    const discountPercent = parseFloat(row.find('.discount-percent-input').val()) || 0;
    const discountAmount = parseFloat(row.find('.discount-amount-input').val()) || 0;
    
    // Validate that only one type of discount is applied
    if (discountPercent > 0 && discountAmount > 0) {
        Swal.fire({
            icon: 'error',
            title: 'Invalid Discount',
            text: 'You cannot apply both discount percentage and amount at the same time.'
        });
        return;
    }
    
    let total = quantity * price;
    
    if (discountPercent > 0) {
        total = total - (total * discountPercent / 100);
    } else if (discountAmount > 0) {
        total = total - discountAmount;
    }
    
    row.find('.total-input').val(total.toFixed(2));
    calculateEditTotal();
}

// Calculate edit total
function calculateEditTotal() {
    let total = 0;
    $('.total-input').each(function() {
        total += parseFloat($(this).val()) || 0;
    });
    $('#editTotalAmount').text('Rs. ' + total.toFixed(2));
}


// Remove edit item
function removeEditItem(index) {
    $(`tr[data-index="${index}"]`).remove();
    calculateEditTotal();
}

// Update quick bill
function updateQuickBill() {
    const formData = new FormData();
    formData.append('bill_id', $('#editBillId').val());
    formData.append('customerId', $('#editCustomer').val());
    formData.append('paymentMethod', $('#editPaymentMethod').val());
    
    // Add required parameters (even if empty since we removed the sections)
    formData.append('paidAmount', '0'); // Not used in edit mode since payments are handled separately
    formData.append('cardOrChequeNo', '');
    formData.append('chequeDate', '');
    formData.append('bankName', '');
    formData.append('note', '');
    formData.append('creditPersonName', '');
    formData.append('creditPersonPhone', '');
    
    // Collect items data
    const items = [];
    $('#editItemsBody tr').each(function() {
        const index = $(this).data('index');
        items.push({
            product_id: $(this).find('input[name="items[' + index + '][product_id]"]').val(),
            po_item_id: $(this).find('input[name="items[' + index + '][po_item_id]"]').val(),
            quantity: $(this).find('input[name="items[' + index + '][quantity]"]').val(),
            unit_price: $(this).find('input[name="items[' + index + '][unit_price]"]').val(),
            discount_percent: $(this).find('input[name="items[' + index + '][discount_percent]"]').val(),
            discount_amount: $(this).find('input[name="items[' + index + '][discount_amount]"]').val(),
            total: $(this).find('input[name="items[' + index + '][total]"]').val()
        });
    });
    
    formData.append('items', JSON.stringify(items));
    
    // Debug logging
    console.log('Update Quick Bill - Bill ID:', $('#editBillId').val());
    console.log('Update Quick Bill - Items:', items);
    console.log('Update Quick Bill - Items JSON:', JSON.stringify(items));
    
    $.ajax({
        url: '<?= base_url("quickBill/updateQuickBill") ?>',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
            console.log('Update Quick Bill - Raw response:', response);
            console.log('Update Quick Bill - Response type:', typeof response);
            console.log('Update Quick Bill - Response status:', response.status);
            
            if (response.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: response.message
                }).then(() => {
                    // Refresh payment history before closing modal
                    loadPaymentHistory();
                    // Show updated payment history briefly
                    setTimeout(() => {
                        $('#editQuickBillModal').modal('hide');
                        location.reload();
                    }, 1000);
                });
            } else {
                Swal.fire('Error', response.message, 'error');
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', xhr.responseText);
            console.error('Status:', status);
            console.error('Error:', error);
            console.error('Response Status:', xhr.status);
            let errorMessage = 'An error occurred while updating the bill';
            
            try {
                const response = JSON.parse(xhr.responseText);
                if (response.message) {
                    errorMessage = response.message;
                }
            } catch (e) {
                // If response is not JSON, use the raw response text
                if (xhr.responseText) {
                    errorMessage = xhr.responseText;
                }
            }
            
            Swal.fire('Error', errorMessage, 'error');
        }
    });
}

// Load payment history
function loadPaymentHistory() {
    const billId = $('#editBillId').val();
    if (!billId) return;

    $.ajax({
        url: '<?= base_url("quickBill/getBillPaymentHistory") ?>',
        type: 'POST',
        data: { bill_id: billId },
        success: function(response) {
            try {
                let result;
                if (typeof response === 'string') {
                    result = JSON.parse(response);
                } else {
                    result = response;
                }
                
                if (result.status === 'success') {
                    displayPaymentHistory(result.payments);
                } else {
                    $('#paymentHistoryContainer').html('<div class="text-center text-danger">Error loading payment history</div>');
                }
            } catch (e) {
                console.error('JSON parse error:', e);
                $('#paymentHistoryContainer').html('<div class="text-center text-danger">Error processing response</div>');
            }
        },
        error: function() {
            $('#paymentHistoryContainer').html('<div class="text-center text-danger">Failed to load payment history</div>');
        }
    });
}

// Display payment history
function displayPaymentHistory(payments) {
    if (!payments || payments.length === 0) {
        $('#paymentHistoryContainer').html('<div class="text-center text-muted">No payment records found</div>');
        return;
    }

    let html = '<div class="table-responsive"><table class="table table-sm table-bordered payment-history-table">';
    html += '<thead class="table-light"><tr>';
    html += '<th>Date</th><th>Method</th><th>Amount</th><th>Details</th><th>Note</th><th>Actions</th>';
    html += '</tr></thead><tbody>';

    let totalAmount = 0;
    payments.forEach(function(payment) {
        const paymentDate = payment.payment_date ? new Date(payment.payment_date).toLocaleDateString() : 'N/A';
        const method = payment.payment_method || 'N/A';
        const amount = parseFloat(payment.paid_amount || 0);
        totalAmount += amount;
        
        let details = '';
        if (payment.card_or_cheque_no) {
            details += `No: ${payment.card_or_cheque_no}`;
        }
        if (payment.bank_name) {
            details += details ? ` | Bank: ${payment.bank_name}` : `Bank: ${payment.bank_name}`;
        }
        if (payment.cheque_date) {
            details += details ? ` | Date: ${new Date(payment.cheque_date).toLocaleDateString()}` : `Date: ${new Date(payment.cheque_date).toLocaleDateString()}`;
        }
        if (!details) details = 'N/A';

        const note = payment.note || 'N/A';
        
        html += '<tr>';
        html += `<td>${paymentDate}</td>`;
        html += `<td><span class="badge bg-primary">${method}</span></td>`;
        html += `<td class="text-end">Rs. ${amount.toFixed(2)}</td>`;
        html += `<td><small>${details}</small></td>`;
        html += `<td><small>${note}</small></td>`;
        html += '<td>';
        html += '<button type="button" class="btn btn-sm btn-outline-warning me-1 payment-edit-btn" onclick="editPayment(' + payment.id + ')" title="Edit Payment" disabled>';
        html += '<i class="fa fa-edit"></i>';
        html += '</button>';
        html += '<button type="button" class="btn btn-sm btn-outline-danger payment-delete-btn" onclick="deletePayment(' + payment.id + ')" title="Delete Payment" disabled>';
        html += '<i class="fa fa-trash"></i>';
        html += '</button>';
        html += '</td>';
        html += '</tr>';
    });

    html += '</tbody></table>';
    html += '<div class="mt-3 text-end"><strong>Total Payments: Rs. ' + totalAmount.toFixed(2) + '</strong></div>';
    html += '</div>';
    
    $('#paymentHistoryContainer').html(html);
}

// Refresh payment history
function refreshPaymentHistory() {
    loadPaymentHistory();
}

// Edit payment
function editPayment(paymentId) {
    // Get payment details from server
    $.ajax({
        url: '<?= base_url("quickBill/getPaymentDetails") ?>',
        type: 'POST',
        data: { payment_id: paymentId },
        success: function(response) {
            try {
                let result;
                if (typeof response === 'string') {
                    result = JSON.parse(response);
                } else {
                    result = response;
                }
                
                if (result.status === 'success') {
                    const payment = result.payment;
                    showEditPaymentModal(payment);
                } else {
                    Swal.fire('Error', result.message || 'Failed to load payment details', 'error');
                }
            } catch (e) {
                console.error('JSON parse error:', e);
                Swal.fire('Error', 'Error processing response', 'error');
            }
        },
        error: function() {
            Swal.fire('Error', 'Failed to load payment details', 'error');
        }
    });
}

// Show edit payment modal with proper fields based on payment method
function showEditPaymentModal(payment) {
    const method = payment.payment_method || '';
    const needsReference = (method.toLowerCase() === 'card' || method.toLowerCase() === 'cheque' || method.toLowerCase() === 'bank transfer');
    const needsBank = (method.toLowerCase() === 'cheque' || method.toLowerCase() === 'bank transfer');
    const needsChequeDate = (method.toLowerCase() === 'cheque');
    
    // Create custom modal HTML
    const modalHtml = `
        <div class="modal fade" id="editPaymentModal" tabindex="-1" aria-labelledby="editPaymentModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editPaymentModalLabel">Edit Payment</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
        <form id="editPaymentForm">
            <div class="mb-3">
                <label class="form-label">Payment Method:</label>
                <select class="form-control" id="editPaymentMethod" required>
                                    <option value="Cash" ${method === 'Cash' ? 'selected' : ''}>Cash</option>
                                    <option value="Card" ${method === 'Card' ? 'selected' : ''}>Card</option>
                                    <option value="Cheque" ${method === 'Cheque' ? 'selected' : ''}>Cheque</option>
                                    <option value="Bank Transfer" ${method === 'Bank Transfer' ? 'selected' : ''}>Bank Transfer</option>
                                    <option value="Credit" ${method === 'Credit' ? 'selected' : ''}>Credit</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Amount:</label>
                                <input type="number" class="form-control" id="editPaymentAmount" value="${payment.paid_amount || 0}" step="0.01" min="0" required>
            </div>
                            <div class="mb-3" id="referenceField" style="display: ${needsReference ? 'block' : 'none'};">
                <label class="form-label">Card/Cheque No:</label>
                                <input type="text" class="form-control" id="editPaymentReference" value="${payment.card_or_cheque_no || ''}" placeholder="Enter reference number">
            </div>
                            <div class="mb-3" id="bankField" style="display: ${needsBank ? 'block' : 'none'};">
                <label class="form-label">Bank Name:</label>
                                <select class="form-control" id="editPaymentBank">
                                    <option value="">Select Bank</option>
                                    <option value="Amana Bank PLC" ${payment.bank_name === 'Amana Bank PLC' ? 'selected' : ''}>Amana Bank PLC</option>
                                    <option value="Bank of Ceylon" ${payment.bank_name === 'Bank of Ceylon' ? 'selected' : ''}>Bank of Ceylon</option>
                                    <option value="Bank of China (Colombo Branch)" ${payment.bank_name === 'Bank of China (Colombo Branch)' ? 'selected' : ''}>Bank of China (Colombo Branch)</option>
                                    <option value="Cargills Bank PLC" ${payment.bank_name === 'Cargills Bank PLC' ? 'selected' : ''}>Cargills Bank PLC</option>
                                    <option value="Citibank N.A." ${payment.bank_name === 'Citibank N.A.' ? 'selected' : ''}>Citibank N.A.</option>
                                    <option value="Commercial Bank of Ceylon PLC" ${payment.bank_name === 'Commercial Bank of Ceylon PLC' ? 'selected' : ''}>Commercial Bank of Ceylon PLC</option>
                                    <option value="Deutsche Bank AG" ${payment.bank_name === 'Deutsche Bank AG' ? 'selected' : ''}>Deutsche Bank AG</option>
                                    <option value="DFCC Bank PLC" ${payment.bank_name === 'DFCC Bank PLC' ? 'selected' : ''}>DFCC Bank PLC</option>
                                    <option value="Habib Bank Limited" ${payment.bank_name === 'Habib Bank Limited' ? 'selected' : ''}>Habib Bank Limited</option>
                                    <option value="Hatton National Bank PLC" ${payment.bank_name === 'Hatton National Bank PLC' ? 'selected' : ''}>Hatton National Bank PLC</option>
                                    <option value="Hongkong and Shanghai Banking Corporation (HSBC)" ${payment.bank_name === 'Hongkong and Shanghai Banking Corporation (HSBC)' ? 'selected' : ''}>Hongkong and Shanghai Banking Corporation (HSBC)</option>
                                    <option value="ICICI Bank Ltd." ${payment.bank_name === 'ICICI Bank Ltd.' ? 'selected' : ''}>ICICI Bank Ltd.</option>
                                    <option value="Indian Bank" ${payment.bank_name === 'Indian Bank' ? 'selected' : ''}>Indian Bank</option>
                                    <option value="Indian Overseas Bank" ${payment.bank_name === 'Indian Overseas Bank' ? 'selected' : ''}>Indian Overseas Bank</option>
                                    <option value="MCB Bank Limited" ${payment.bank_name === 'MCB Bank Limited' ? 'selected' : ''}>MCB Bank Limited</option>
                                    <option value="National Development Bank PLC" ${payment.bank_name === 'National Development Bank PLC' ? 'selected' : ''}>National Development Bank PLC</option>
                                    <option value="Nations Trust Bank PLC" ${payment.bank_name === 'Nations Trust Bank PLC' ? 'selected' : ''}>Nations Trust Bank PLC</option>
                                    <option value="Pan Asia Banking Corporation PLC" ${payment.bank_name === 'Pan Asia Banking Corporation PLC' ? 'selected' : ''}>Pan Asia Banking Corporation PLC</option>
                                    <option value="People's Bank" ${payment.bank_name === 'People\'s Bank' ? 'selected' : ''}>People's Bank</option>
                                    <option value="Public Bank Berhad" ${payment.bank_name === 'Public Bank Berhad' ? 'selected' : ''}>Public Bank Berhad</option>
                                    <option value="Sampath Bank PLC" ${payment.bank_name === 'Sampath Bank PLC' ? 'selected' : ''}>Sampath Bank PLC</option>
                                    <option value="Seylan Bank PLC" ${payment.bank_name === 'Seylan Bank PLC' ? 'selected' : ''}>Seylan Bank PLC</option>
                                    <option value="Standard Chartered Bank" ${payment.bank_name === 'Standard Chartered Bank' ? 'selected' : ''}>Standard Chartered Bank</option>
                                    <option value="State Bank of India" ${payment.bank_name === 'State Bank of India' ? 'selected' : ''}>State Bank of India</option>
                                    <option value="Union Bank of Colombo PLC" ${payment.bank_name === 'Union Bank of Colombo PLC' ? 'selected' : ''}>Union Bank of Colombo PLC</option>
                                </select>
                            </div>
                            <div class="mb-3" id="chequeDateField" style="display: ${needsChequeDate ? 'block' : 'none'};">
                                <label class="form-label">Cheque Date:</label>
                                <input type="date" class="form-control" id="editPaymentChequeDate" value="${payment.cheque_date || ''}">
            </div>
            <div class="mb-3">
                <label class="form-label">Note:</label>
                                <textarea class="form-control" id="editPaymentNote" rows="2" placeholder="Enter payment note">${payment.note || ''}</textarea>
            </div>
        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" id="updatePaymentBtn">Update Payment</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    $('#editPaymentModal').remove();
    
    // Add modal to body
    $('body').append(modalHtml);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('editPaymentModal'));
    modal.show();
    
    // Add event listeners
    $('#editPaymentMethod').on('change', function() {
        toggleEditPaymentFields();
    });
    
    $('#updatePaymentBtn').on('click', function() {
        const method = $('#editPaymentMethod').val();
        const amount = $('#editPaymentAmount').val();
        const reference = $('#editPaymentReference').val();
        const bank = $('#editPaymentBank').val();
        const chequeDate = $('#editPaymentChequeDate').val();
        const note = $('#editPaymentNote').val();
            
            if (!method || !amount) {
            Swal.fire('Error', 'Payment method and amount are required', 'error');
            return;
            }
            
        const paymentData = {
                method: method,
                amount: amount,
                reference: reference,
                bank: bank,
            chequeDate: chequeDate,
                note: note
            };
        
        updatePayment(payment.id, paymentData);
        modal.hide();
    });
    
    // Clean up modal when hidden
    $('#editPaymentModal').on('hidden.bs.modal', function() {
        $(this).remove();
    });
}

// Toggle payment fields based on selected method (for edit modal)
function toggleEditPaymentFields() {
    const method = document.getElementById('editPaymentMethod').value.toLowerCase();
    const referenceField = document.getElementById('referenceField');
    const bankField = document.getElementById('bankField');
    const chequeDateField = document.getElementById('chequeDateField');
    
    const needsReference = (method === 'card' || method === 'cheque' || method === 'bank transfer');
    const needsBank = (method === 'cheque' || method === 'bank transfer');
    const needsChequeDate = (method === 'cheque');
    
    if (referenceField) referenceField.style.display = needsReference ? 'block' : 'none';
    if (bankField) bankField.style.display = needsBank ? 'block' : 'none';
    if (chequeDateField) chequeDateField.style.display = needsChequeDate ? 'block' : 'none';
}

// Toggle payment fields based on selected method (for add modal)
function togglePaymentFields() {
    const method = document.getElementById('editPaymentMethod').value.toLowerCase();
    const referenceField = document.getElementById('referenceField');
    const bankField = document.getElementById('bankField');
    const chequeDateField = document.getElementById('chequeDateField');
    
    const needsReference = (method === 'card' || method === 'cheque' || method === 'bank transfer');
    const needsBank = (method === 'cheque' || method === 'bank transfer');
    const needsChequeDate = (method === 'cheque');
    
    if (referenceField) referenceField.style.display = needsReference ? 'block' : 'none';
    if (bankField) bankField.style.display = needsBank ? 'block' : 'none';
    if (chequeDateField) chequeDateField.style.display = needsChequeDate ? 'block' : 'none';
}

// Update payment
function updatePayment(paymentId, paymentData) {
    $.ajax({
        url: '<?= base_url("quickBill/updatePayment") ?>',
        type: 'POST',
        data: {
            payment_id: paymentId,
            payment_method: paymentData.method,
            paid_amount: paymentData.amount,
            card_or_cheque_no: paymentData.reference,
            bank_name: paymentData.bank,
            cheque_date: paymentData.chequeDate,
            note: paymentData.note
        },
        success: function(response) {
            try {
                let result;
                if (typeof response === 'string') {
                    result = JSON.parse(response);
                } else {
                    result = response;
                }
                
                if (result.status === 'success') {
                    Swal.fire('Success!', 'Payment updated successfully', 'success');
                    // Refresh payment history
                    loadPaymentHistory();
                } else {
                    Swal.fire('Error', result.message || 'Failed to update payment', 'error');
                }
            } catch (e) {
                console.error('JSON parse error:', e);
                Swal.fire('Error', 'Error processing response', 'error');
            }
        },
        error: function(xhr, status, error) {
            console.error('Payment Update AJAX Error:', xhr.responseText);
            console.error('Status:', status);
            console.error('Error:', error);
            let errorMessage = 'Failed to update payment';
            
            try {
                const response = JSON.parse(xhr.responseText);
                if (response.message) {
                    errorMessage = response.message;
                }
            } catch (e) {
                if (xhr.responseText) {
                    errorMessage = xhr.responseText;
                }
            }
            
            Swal.fire('Error', errorMessage, 'error');
        }
    });
}

// Delete payment
function deletePayment(paymentId) {
    Swal.fire({
        title: 'Delete Payment',
        text: 'Are you sure you want to delete this payment? This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, Delete',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#d33'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '<?= base_url("quickBill/deletePayment") ?>',
                type: 'POST',
                data: { payment_id: paymentId },
                success: function(response) {
                    try {
                        let result;
                        if (typeof response === 'string') {
                            result = JSON.parse(response);
                        } else {
                            result = response;
                        }
                        
                        if (result.status === 'success') {
                            Swal.fire('Deleted!', 'Payment deleted successfully', 'success');
                            // Refresh payment history
                            loadPaymentHistory();
                        } else {
                            Swal.fire('Error', result.message || 'Failed to delete payment', 'error');
                        }
                    } catch (e) {
                        console.error('JSON parse error:', e);
                        Swal.fire('Error', 'Error processing response', 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Failed to delete payment', 'error');
                }
            });
        }
    });
}

// Add new payment
function addNewPayment() {
    const billId = $('#editBillId').val();
    if (!billId) {
        Swal.fire('Error', 'Bill ID not found', 'error');
        return;
    }

    // Create custom modal HTML
    const modalHtml = `
        <div class="modal fade" id="addPaymentModal" tabindex="-1" aria-labelledby="addPaymentModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addPaymentModalLabel">Add New Payment</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
        <form id="addPaymentForm">
            <div class="mb-3">
                <label class="form-label">Payment Method:</label>
                <select class="form-control" id="addPaymentMethod" required>
                    <option value="">Select Payment Method</option>
                    <option value="Cash">Cash</option>
                    <option value="Card">Card</option>
                    <option value="Cheque">Cheque</option>
                    <option value="Bank Transfer">Bank Transfer</option>
                    <option value="Credit">Credit</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Amount:</label>
                <input type="number" class="form-control" id="addPaymentAmount" step="0.01" min="0" required>
            </div>
                            <div class="mb-3" id="addReferenceField" style="display: none;">
                <label class="form-label">Card/Cheque No:</label>
                <input type="text" class="form-control" id="addPaymentReference" placeholder="Enter reference number">
            </div>
                            <div class="mb-3" id="addBankField" style="display: none;">
                <label class="form-label">Bank Name:</label>
                                <select class="form-control" id="addPaymentBank">
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
                            <div class="mb-3" id="addChequeDateField" style="display: none;">
                                <label class="form-label">Cheque Date:</label>
                                <input type="date" class="form-control" id="addPaymentChequeDate">
            </div>
            <div class="mb-3">
                <label class="form-label">Note:</label>
                <textarea class="form-control" id="addPaymentNote" rows="2" placeholder="Enter payment note"></textarea>
            </div>
        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" id="addPaymentBtn">Add Payment</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    $('#addPaymentModal').remove();
    
    // Add modal to body
    $('body').append(modalHtml);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('addPaymentModal'));
    modal.show();
    
    // Add event listeners
    $('#addPaymentMethod').on('change', function() {
        toggleAddPaymentFields();
    });
    
    $('#addPaymentBtn').on('click', function() {
        const method = $('#addPaymentMethod').val();
        const amount = $('#addPaymentAmount').val();
        const reference = $('#addPaymentReference').val();
        const bank = $('#addPaymentBank').val();
        const chequeDate = $('#addPaymentChequeDate').val();
        const note = $('#addPaymentNote').val();
            
            if (!method || !amount) {
            Swal.fire('Error', 'Payment method and amount are required', 'error');
            return;
            }
            
        const paymentData = {
                method: method,
                amount: amount,
                reference: reference,
                bank: bank,
            chequeDate: chequeDate,
                note: note
            };
        
        createNewPayment(billId, paymentData);
        modal.hide();
    });
    
    // Clean up modal when hidden
    $('#addPaymentModal').on('hidden.bs.modal', function() {
        $(this).remove();
    });
}

// Toggle add payment fields based on selected method
function toggleAddPaymentFields() {
    const method = document.getElementById('addPaymentMethod').value.toLowerCase();
    const referenceField = document.getElementById('addReferenceField');
    const bankField = document.getElementById('addBankField');
    const chequeDateField = document.getElementById('addChequeDateField');
    
    const needsReference = (method === 'card' || method === 'cheque' || method === 'bank transfer');
    const needsBank = (method === 'cheque' || method === 'bank transfer');
    const needsChequeDate = (method === 'cheque');
    
    if (referenceField) referenceField.style.display = needsReference ? 'block' : 'none';
    if (bankField) bankField.style.display = needsBank ? 'block' : 'none';
    if (chequeDateField) chequeDateField.style.display = needsChequeDate ? 'block' : 'none';
}

// Create new payment
function createNewPayment(billId, paymentData) {
    $.ajax({
        url: '<?= base_url("quickBill/createPayment") ?>',
        type: 'POST',
        data: {
            bill_id: billId,
            payment_method: paymentData.method,
            paid_amount: paymentData.amount,
            card_or_cheque_no: paymentData.reference,
            bank_name: paymentData.bank,
            cheque_date: paymentData.chequeDate,
            note: paymentData.note
        },
        success: function(response) {
            try {
                let result;
                if (typeof response === 'string') {
                    result = JSON.parse(response);
                } else {
                    result = response;
                }
                
                if (result.status === 'success') {
                    Swal.fire('Success!', 'Payment added successfully', 'success');
                    // Refresh payment history
                    loadPaymentHistory();
                } else {
                    Swal.fire('Error', result.message || 'Failed to add payment', 'error');
                }
            } catch (e) {
                console.error('JSON parse error:', e);
                Swal.fire('Error', 'Error processing response', 'error');
            }
        },
        error: function() {
            Swal.fire('Error', 'Failed to add payment', 'error');
        }
    });
}

// Load payment history when document is ready
$(document).ready(function() {
    loadPaymentHistory();
});
</script>
 