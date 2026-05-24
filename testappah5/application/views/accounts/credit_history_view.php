<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0"><?= htmlspecialchars($mainMenuName) ?></h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="<?php echo base_url(); ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo base_url('accounts/credits'); ?>">Credits</a></li>
                    <li class="breadcrumb-item active">Payment History</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">

        <!-- Filters Section -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Filters</h5>
            </div>
            <div class="card-body">
                <div class="row">
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
                        <label class="form-label">Payment Method</label>
                        <select class="form-control" id="filterPaymentMethod">
                            <option value="">All Methods</option>
                            <option value="cash" <?= (isset($filters['payment_method']) && $filters['payment_method'] == 'cash') ? 'selected' : '' ?>>Cash</option>
                            <option value="card" <?= (isset($filters['payment_method']) && $filters['payment_method'] == 'card') ? 'selected' : '' ?>>Card</option>
                            <option value="cheque" <?= (isset($filters['payment_method']) && $filters['payment_method'] == 'cheque') ? 'selected' : '' ?>>Cheque</option>
                            <option value="bank_transfer" <?= (isset($filters['payment_method']) && $filters['payment_method'] == 'bank_transfer') ? 'selected' : '' ?>>Bank Transfer</option>
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
                            <i class="fa fa-filter"></i> Apply
                        </button>
                        <button type="button" class="btn btn-secondary" id="clearFilters">
                            <i class="fa fa-times"></i> Clear
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Credit Transaction History</h5>
                <small class="text-muted">Showing all credit transactions (including paid and unpaid)</small>
            </div>
            <div class="card-body">
                <table id="creditHistoryTable" class="table table-striped table-responsive table-sm">
                    <thead class="table-info">
                        <tr>
                            <th>#</th>
                            <th>Customer/Company</th>
                            <th>Credit Person</th>
                            <th>Reference</th>
                            <th class="text-end">Total Bill Amount</th>
                            <th class="text-end">Credit Amount</th>
                            <th>Status</th>
                            <th>Description</th>
                            <th class="text-center">Credit Date</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i=1; foreach($payments as $payment): ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td>
                                <?php if (!empty($payment->customer_name)): ?>
                                    <span class="badge bg-primary">Customer</span><br>
                                    <strong><?= htmlspecialchars($payment->customer_name) ?></strong>
                                <?php elseif (!empty($payment->supplier_name)): ?>
                                    <span class="badge bg-warning">Supplier</span><br>
                                    <strong><?= htmlspecialchars($payment->supplier_name) ?></strong>
                                <?php else: ?>
                                    <span class="text-muted">Unknown</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($payment->credit_person_name)): ?>
                                    <strong><?= htmlspecialchars($payment->credit_person_name) ?></strong>
                                    <?php if (!empty($payment->credit_person_phone)): ?>
                                        <br><small class="text-muted"><?= htmlspecialchars($payment->credit_person_phone) ?></small>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($payment->reference_type === 'quick_bill'): ?>
                                    <span class="badge bg-success">QB-<?= $payment->quick_bill_id ?></span>
                                <?php elseif ($payment->reference_type === 'purchase_order'): ?>
                                    <span class="badge bg-info">PO-<?= $payment->po_id ?></span>
                                <?php elseif ($payment->reference_type === 'service_job'): ?>
                                    <span class="badge bg-primary">JOB-<?= $payment->job_id ?></span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                LKR <?= number_format($payment->total_bill_amount, 2) ?>
                            </td>
                            <td class="text-end">
                                <span class="badge bg-warning">
                                    LKR <?= number_format($payment->paid_amount, 2) ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-info">
                                    Credit Transaction
                                </span>
                            </td>
                            <td>
                                <?php if (!empty($payment->note)): ?>
                                    <small><?= htmlspecialchars($payment->note) ?></small>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center"><?= date('Y-m-d H:i', strtotime($payment->payment_date)) ?></td>
                            <td class="text-center">
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="viewPaymentDetails(<?= $payment->payment_id ?>, '<?= $payment->reference_type ?>', <?= $payment->quick_bill_id ?? $payment->po_id ?? $payment->job_id ?? 0 ?>, '<?= htmlspecialchars($payment->customer_name ?? $payment->supplier_name ?? 'Unknown') ?>', '<?= htmlspecialchars($payment->credit_person_name ?? $payment->job_credit_person ?? '') ?>', '<?= htmlspecialchars($payment->credit_person_phone ?? '') ?>', 'credit', <?= $payment->paid_amount ?>, '', '', '', '<?= htmlspecialchars($payment->note ?? '') ?>', '<?= htmlspecialchars($payment->po_bill_no ?? '') ?>')">
                                        <i class="fa fa-eye"></i> View
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; if (empty($payments)): ?>
                        <tr>
                            <td>1</td>
                            <td class="text-muted">No credit transactions found</td>
                            <td class="text-muted">-</td>
                            <td class="text-muted">-</td>
                            <td class="text-end text-muted">-</td>
                            <td class="text-end text-muted">-</td>
                            <td class="text-muted">-</td>
                            <td class="text-muted">-</td>
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
            var hasRealRows = $('#creditHistoryTable tbody tr').length > 0 && $('#creditHistoryTable tbody tr td').eq(1).text().trim() !== 'No credit payments found';
            if (hasRealRows) {
                if ($.fn.DataTable.isDataTable('#creditHistoryTable')) $('#creditHistoryTable').DataTable().destroy();
                $('#creditHistoryTable').DataTable({
                    lengthMenu: [[10,25,50,-1],[10,25,50,'All']],
                    searching: true,
                    responsive: true,
                    order: [[8, 'desc']] // Sort by payment date column
                });
            }
        }

        // Apply filters
        $('#applyFilters').click(function() {
            var customerId = $('#filterCustomer').val();
            var supplierId = $('#filterSupplier').val();
            var paymentMethod = $('#filterPaymentMethod').val();
            var dateFrom = $('#filterDateFrom').val();
            var dateTo = $('#filterDateTo').val();
            
            // Reload page with filters
            var url = new URL(window.location);
            if (customerId) url.searchParams.set('customer_id', customerId);
            if (supplierId) url.searchParams.set('supplier_id', supplierId);
            if (paymentMethod) url.searchParams.set('payment_method', paymentMethod);
            if (dateFrom) url.searchParams.set('date_from', dateFrom);
            if (dateTo) url.searchParams.set('date_to', dateTo);
            
            window.location.href = url.toString();
        });

        // Clear filters
        $('#clearFilters').click(function() {
            $('#filterCustomer').val('');
            $('#filterSupplier').val('');
            $('#filterPaymentMethod').val('');
            $('#filterDateFrom').val('');
            $('#filterDateTo').val('');
            window.location.href = window.location.pathname;
        });
    });

    function viewPaymentDetails(transactionId, referenceType, referenceId, customerName, creditPersonName, creditPersonPhone, transactionType, creditAmount, cardChequeNo, bankName, chequeDate, note, billNumber) {
        var message = '<div class="text-start">';
        message += '<p><strong>Transaction ID:</strong> ' + transactionId + '</p>';
        message += '<p><strong>Customer/Company:</strong> ' + customerName + '</p>';
        message += '<p><strong>Reference:</strong> ' + referenceType + ' #' + referenceId + '</p>';
        
        if (billNumber && referenceType === 'purchase_order') {
            message += '<p><strong>Bill Number:</strong> ' + billNumber + '</p>';
        }
        
        if (creditPersonName) {
            message += '<p><strong>Credit Person Name:</strong> ' + creditPersonName + '</p>';
        }
        
        if (creditPersonPhone) {
            message += '<p><strong>Credit Person Phone:</strong> ' + creditPersonPhone + '</p>';
        }
        
        message += '<p><strong>Transaction Type:</strong> Credit Transaction</p>';
        message += '<p><strong>Credit Amount:</strong> LKR ' + parseFloat(creditAmount).toFixed(2) + '</p>';
        
        if (note) {
            message += '<p><strong>Description:</strong> ' + note + '</p>';
        }
        
        message += '</div>';
        
        Swal.fire({
            title: 'Credit Transaction Details',
            html: message,
            icon: 'info',
            width: '600px'
        });
    }
</script>
