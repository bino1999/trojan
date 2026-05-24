<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0"><?= htmlspecialchars($mainMenuName) ?></h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="<?php echo base_url(); ?>">Home</a></li>
                    <li class="breadcrumb-item active">Accounts</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Current Credit Balance</h5>
            </div>
            <div class="card-body text-center">
                <h1 class="display-5" style="color:#0ab39c; font-weight:800;">LKR <?= number_format($account->balance ?? 0, 2) ?></h1>
                <div class="text-muted">as of <?= date('Y-m-d H:i') ?></div>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Filters</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-2">
                        <label class="form-label">Transaction Type</label>
                        <select class="form-control" id="filterTransactionType">
                            <option value="">All Transactions</option>
                            <option value="credit" <?= (isset($filters['txn_type']) && $filters['txn_type'] == 'credit') ? 'selected' : '' ?>>Credit Only</option>
                            <option value="debit" <?= (isset($filters['txn_type']) && $filters['txn_type'] == 'debit') ? 'selected' : '' ?>>Debit Only</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Filter by Customer</label>
                        <select class="form-control" id="filterCustomer">
                            <option value="">All Customers</option>
                            <?php foreach($customers as $customer): ?>
                                <option value="<?= $customer->customers_id ?>" <?= (isset($filters['customer_id']) && $filters['customer_id'] == $customer->customers_id) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($customer->name) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Filter by Supplier</label>
                        <select class="form-control" id="filterSupplier">
                            <option value="">All Suppliers</option>
                            <?php foreach($suppliers as $supplier): ?>
                                <option value="<?= $supplier->supplier_id ?>" <?= (isset($filters['supplier_id']) && $filters['supplier_id'] == $supplier->supplier_id) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($supplier->name) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Date From</label>
                        <input type="date" class="form-control" id="filterDateFrom" value="<?= isset($filters['date_from']) ? $filters['date_from'] : '' ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Date To</label>
                        <input type="date" class="form-control" id="filterDateTo" value="<?= isset($filters['date_to']) ? $filters['date_to'] : '' ?>">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="button" class="btn btn-primary me-2" id="applyFilters">
                            <i class="fa fa-filter"></i> Apply Filters
                        </button>
                        <button type="button" class="btn btn-secondary" id="clearFilters">
                            <i class="fa fa-times"></i> Clear Filters
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Credit Transactions</h5>
            </div>
            <div class="card-body">
                <table id="creditsTable" class="table table-striped table-responsive table-sm">
                    <thead class="table-info">
                        <tr>
                            <th>#</th>
                            <th>Customer/Company</th>
                            <th>Description</th>
                            <th>Bill Number</th>
                            <th class="text-end">Total Bill Amount</th>
                            <th class="text-end">Credit Amount</th>
                            <th class="text-center">Credit Date</th>
                            <th class="text-center">Due Date</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i=1; foreach($transactions as $t): ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td>
                                <?php if (!empty($t->customer_name)): ?>
                                    <span class="badge bg-primary">Customer</span><br>
                                    <strong><?= htmlspecialchars($t->customer_name) ?></strong>
                                <?php elseif (!empty($t->supplier_name)): ?>
                                    <span class="badge bg-warning">Supplier</span><br>
                                    <strong><?= htmlspecialchars($t->supplier_name) ?></strong>
                                <?php elseif ($t->customer_id == 0): ?>
                                    <span class="badge bg-secondary">Walk-in Customer</span>
                                <?php else: ?>
                                    <span class="text-muted">Unknown</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($t->description) ?></td>
                            <td>
                                <?php if (!empty($t->quick_bill_id)): ?>
                                    <span class="badge bg-success">QB-<?= $t->quick_bill_id ?></span>
                                <?php elseif (!empty($t->po_id)): ?>
                                    <span class="badge bg-info">PO-<?= $t->po_id ?></span>
                                <?php elseif (!empty($t->job_id)): ?>
                                    <span class="badge bg-primary">JOB-<?= $t->job_id ?></span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <?php if (!empty($t->total_bill_amount)): ?>
                                    LKR <?= number_format($t->total_bill_amount, 2) ?>
                                <?php elseif (!empty($t->po_total_amount)): ?>
                                    LKR <?= number_format($t->po_total_amount, 2) ?>
                                <?php elseif (!empty($t->job_total_amount)): ?>
                                    LKR <?= number_format($t->job_total_amount, 2) ?>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <span class="badge <?= $t->txn_type=='credit' ? 'bg-success':'bg-danger' ?>">
                                    LKR <?= number_format($t->amount, 2) ?>
                                </span>
                            </td>
                            <td class="text-center"><?= date('Y-m-d H:i', strtotime($t->created_at)) ?></td>
                            <td class="text-center">
                                <?php 
                                    $due = $t->credit_due_date ?? $t->job_credit_due_date ?? null; 
                                ?>
                                <?php if (!empty($due)) : ?>
                                    <?= htmlspecialchars($due) ?>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="viewDetails(<?= $t->txn_id ?>, '<?= $t->reference_type ?>', <?= $t->reference_id ?: 'null' ?>, '<?= htmlspecialchars($t->customer_name ?: $t->supplier_name ?: 'Unknown') ?>', '<?= htmlspecialchars($t->credit_person_name ?? $t->job_credit_person ?? '') ?>', '<?= htmlspecialchars($t->credit_person_phone ?? '') ?>', '<?= htmlspecialchars($t->po_bill_no ?? '') ?>')">
                                        <i class="fa fa-eye"></i> View
                                    </button>
                                    <button type="button" class="btn btn-sm btn-success" onclick="makePayment(<?= $t->txn_id ?>, <?= $t->amount ?>, '<?= $t->reference_type ?>', <?= $t->reference_id ?: 'null' ?>, '<?= htmlspecialchars($t->customer_name ?: $t->supplier_name ?: 'Unknown') ?>')">
                                        <i class="fa fa-credit-card"></i> Pay
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; if (empty($transactions)): ?>
                        <tr>
                            <td>1</td>
                            <td class="text-muted">No credit transactions found</td>
                            <td class="text-muted">-</td>
                            <td class="text-muted">-</td>
                            <td class="text-end text-muted">-</td>
                            <td class="text-end text-muted">-</td>
                            <td class="text-center text-muted">-</td>
                            <td class="text-center text-muted">-</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<script>
    $(document).ready(function() {
        // Initialize DataTable
        if ($.fn.DataTable) {
            var hasRealRows = $('#creditsTable tbody tr').length > 0 && $('#creditsTable tbody tr td').eq(1).text().trim() !== 'No credit transactions found';
            if (hasRealRows) {
                if ($.fn.DataTable.isDataTable('#creditsTable')) $('#creditsTable').DataTable().destroy();
                $('#creditsTable').DataTable({
                    lengthMenu: [[10,25,50,-1],[10,25,50,'All']],
                    searching: true,
                    responsive: true,
                    order: [[6, 'desc']] // Sort by date column
                });
            }
        }

        // Apply filters
        $('#applyFilters').click(function() {
            var txnType = $('#filterTransactionType').val();
            var customerId = $('#filterCustomer').val();
            var supplierId = $('#filterSupplier').val();
            var dateFrom = $('#filterDateFrom').val();
            var dateTo = $('#filterDateTo').val();
            
            // Reload page with filters
            var url = new URL(window.location);
            if (txnType) url.searchParams.set('txn_type', txnType);
            if (customerId) url.searchParams.set('customer_id', customerId);
            if (supplierId) url.searchParams.set('supplier_id', supplierId);
            if (dateFrom) url.searchParams.set('date_from', dateFrom);
            if (dateTo) url.searchParams.set('date_to', dateTo);
            
            window.location.href = url.toString();
        });

        // Clear filters
        $('#clearFilters').click(function() {
            $('#filterTransactionType').val('');
            $('#filterCustomer').val('');
            $('#filterSupplier').val('');
            $('#filterDateFrom').val('');
            $('#filterDateTo').val('');
            window.location.href = window.location.pathname;
        });
    });

    // Handle payment method changes
    $(document).on('change', '#payment_method', function() {
        var method = $(this).val();
        var fieldsDiv = $('#card_cheque_fields');
        var cardChequeLabel = $('#card_cheque_label');
        var chequeDateLabel = $('#cheque_date_label');
        
        if (method === 'card') {
            fieldsDiv.show();
            cardChequeLabel.text('Card Number');
            chequeDateLabel.text('Card Date');
            $('#cheque_date').attr('type', 'date');
        } else if (method === 'cheque') {
            fieldsDiv.show();
            cardChequeLabel.text('Cheque Number');
            chequeDateLabel.text('Cheque Date');
            $('#cheque_date').attr('type', 'date');
        } else if (method === 'bank_transfer') {
            fieldsDiv.show();
            cardChequeLabel.text('Transaction ID');
            chequeDateLabel.text('Transaction Date');
            $('#cheque_date').attr('type', 'date');
        } else {
            fieldsDiv.hide();
        }
    });

    // Payment functionality
    function makePayment(txnId, creditAmount, referenceType, referenceId, customerName) {
        $('#paymentTxnId').val(txnId);
        $('#paymentReferenceType').val(referenceType);
        $('#paymentReferenceId').val(referenceId);
        $('#paymentCustomerName').val(customerName);
        $('#paymentCreditAmount').val('LKR ' + parseFloat(creditAmount).toFixed(2));
        $('#paymentAmount').attr('max', creditAmount);
        $('#paymentAmount').val(creditAmount);
        
        // Auto-populate transaction date with today's date
        var today = new Date().toISOString().split('T')[0];
        $('#paymentDate').val(today);
        
        // Reset form fields
        $('#payment_method').val('');
        $('#card_or_cheque_no').val('');
        $('#cheque_date').val('');
        $('#bank_name').val('');
        $('#payment_note').val('');
        $('#card_cheque_fields').hide();
        
        $('#creditPaymentModal').modal('show');
    }

    $(document).on('click', '#saveCreditPayment', function(e) {
        e.preventDefault();
        
        var txnId = $('#paymentTxnId').val();
        var paymentMethod = $('#payment_method').val();
        var amount = parseFloat($('#paymentAmount').val());
        var cardOrChequeNo = $('#card_or_cheque_no').val();
        var chequeDate = $('#cheque_date').val();
        var bankName = $('#bank_name').val();
        var note = $('#payment_note').val();
        var transactionDate = $('#paymentDate').val();
        
        if (!paymentMethod || !amount || amount <= 0) {
            Swal.fire('Error', 'Please fill all required fields', 'error');
            return;
        }
        
        // Validate payment method specific fields
        if (paymentMethod === 'card' && (!cardOrChequeNo || !bankName)) {
            Swal.fire('Error', 'Card number and bank name are required for card payments', 'error');
            return;
        }
        
        if (paymentMethod === 'cheque' && (!cardOrChequeNo || !chequeDate || !bankName)) {
            Swal.fire('Error', 'Cheque number, date and bank name are required for cheque payments', 'error');
            return;
        }
        
        if (paymentMethod === 'bank_transfer' && (!cardOrChequeNo || !bankName)) {
            Swal.fire('Error', 'Transaction ID and bank name are required for bank transfer payments', 'error');
            return;
        }
        
        $.post("<?= base_url('accounts/processCreditPayment'); ?>", {
            txn_id: txnId,
            payment_method: paymentMethod,
            amount: amount,
            card_or_cheque_no: cardOrChequeNo,
            cheque_date: chequeDate,
            bank_name: bankName,
            note: note,
            transaction_date: transactionDate
        }, function(res) {
            if (res.status === 'success') {
                Toastify({ text: 'Payment processed successfully', className: 'toastify-success', duration: 3000 }).showToast();
                $('#creditPaymentModal').modal('hide');
                location.reload();
            } else {
                Swal.fire('Error', res.message || 'Payment failed', 'error');
            }
        }, 'json').fail(function(jq) {
            Swal.fire('Error', 'Request failed', 'error');
        });
    });

    function viewDetails(txnId, referenceType, referenceId, customerName, creditPersonName, creditPersonPhone, billNumber) {
        var message = '<div class="text-start">';
        message += '<p><strong>Transaction ID:</strong> ' + txnId + '</p>';
        message += '<p><strong>Customer/Company:</strong> ' + customerName + '</p>';
        
        if (referenceType && referenceId) {
            message += '<p><strong>Reference:</strong> ' + referenceType + ' #' + referenceId + '</p>';
        }
        
        if (billNumber && referenceType === 'purchase_order') {
            message += '<p><strong>Bill Number:</strong> ' + billNumber + '</p>';
        }
        
        if (creditPersonName) {
            message += '<p><strong>Credit Person Name:</strong> ' + creditPersonName + '</p>';
        }
        
        if (creditPersonPhone) {
            message += '<p><strong>Credit Person Phone:</strong> ' + creditPersonPhone + '</p>';
        }
        
        message += '</div>';
        
        Swal.fire({
            title: 'Credit Transaction Details',
            html: message,
            icon: 'info',
            width: '500px'
        });
    }

</script>

<!-- Credit Payment Modal -->
<div class="modal fade" id="creditPaymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Process Credit Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Customer/Company</label>
                            <input type="text" class="form-control" id="paymentCustomerName" readonly style="background-color: #f8f9fa;">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Credit Amount</label>
                            <input type="text" class="form-control" id="paymentCreditAmount" readonly style="background-color: #f8f9fa;">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Transaction Date</label>
                            <input type="date" class="form-control" id="paymentDate" readonly style="background-color: #f8f9fa;">
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Payment Method *</label>
                            <select class="form-control" id="payment_method" required>
                                <option value="">Select Payment Method</option>
                                <option value="cash">Cash</option>
                                <option value="card">Card</option>
                                <option value="cheque">Cheque</option>
                                <option value="bank_transfer">Bank Transfer</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Payment Amount *</label>
                            <input type="number" min="0" step="0.01" class="form-control" id="paymentAmount" required>
                        </div>
                    </div>
                </div>
                
                <div class="row" id="card_cheque_fields" style="display: none;">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label" id="card_cheque_label">Card/Cheque Number</label>
                            <input type="text" class="form-control" id="card_or_cheque_no">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Bank Name</label>
                            <select class="form-control" id="bank_name">
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
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label" id="cheque_date_label">Cheque Date</label>
                            <input type="date" class="form-control" id="cheque_date">
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Note (Optional)</label>
                    <textarea class="form-control" id="payment_note" rows="2" placeholder="Additional notes..."></textarea>
                </div>
                
                <input type="hidden" id="paymentTxnId">
                <input type="hidden" id="paymentReferenceType">
                <input type="hidden" id="paymentReferenceId">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="saveCreditPayment">Process Payment</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>
