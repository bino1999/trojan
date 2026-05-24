<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0"><?= htmlspecialchars($mainMenuName) ?></h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="<?php echo base_url(); ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo base_url('accounts/bank'); ?>">Bank</a></li>
                    <li class="breadcrumb-item active">Cheque Payments</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">

        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="card-title mb-0">Filters
                    <?php if (!empty($alerts)): ?>
                        <span class="ms-2 badge bg-warning">Due Alerts: <?= count($alerts) ?></span>
                    <?php endif; ?>
                </h5>
                <div class="d-flex align-items-center gap-2">
                    <label class="me-2 mb-0">Status:</label>
                    <div style="min-width:160px;">
                        <select class="form-control" id="filterStatus">
                            <option value="uncleared" <?= (isset($filters['status']) && $filters['status'] === 'uncleared') ? 'selected' : '' ?>>Unreceived</option>
                            <option value="cleared" <?= (isset($filters['status']) && $filters['status'] === 'cleared') ? 'selected' : '' ?>>Received</option>
                            <option value="all" <?= (isset($filters['status']) && $filters['status'] === 'all') ? 'selected' : '' ?>>All</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <?php if (!empty($alerts)): ?>
                    <div class="mb-3">
                        <?php foreach($alerts as $a): ?>
                            <div class="alert alert-<?= $a['level'] == 'warning' ? 'warning' : 'info' ?> py-2 mb-2">
                                <i class="fa <?= $a['level'] == 'warning' ? 'fa-exclamation-triangle' : 'fa-info-circle' ?>"></i>
                                <?= htmlspecialchars($a['msg']) ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <div class="row">
                    <div class="col-md-3">
                        <label class="form-label">Customer</label>
                        <select class="form-control" id="filterCustomer">
                            <option value="">All Customers</option>
                            <?php foreach($customers as $customer): ?>
                                <option value="<?= $customer->customers_id ?>" <?= (isset($filters['customer_id']) && $filters['customer_id'] == $customer->customers_id) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($customer->name) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Supplier</label>
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
                <h5 class="card-title mb-0">Cheque Payments (Quick Bills, Service Jobs, Purchase Orders)</h5>
            </div>
            <div class="card-body">
                <table id="chequeTable" class="table table-striped table-responsive table-sm">
                    <thead class="table-info">
                        <tr>
                            <th>#</th>
                            <th>Source</th>
                            <th>Reference</th>
                            <th>Party</th>
                            <th class="text-end">Amount</th>
                            <th>Cheque No</th>
                            <th>Bank</th>
                            <th>Cheque Date</th>
                            <th>Payment Date</th>
                            <th>Note</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i=1; foreach($payments as $p): ?>
                        <tr data-cleared="<?= !empty($p->cleared) ? '1' : '0' ?>">
                            <td><?= $i++ ?></td>
                            <td><span class="badge bg-primary"><?= htmlspecialchars($p->source) ?></span></td>
                            <td>
                                <?php if ($p->source === 'Quick Bill'): ?>
                                    <span class="badge bg-success">QB-<?= $p->reference ?></span>
                                <?php elseif ($p->source === 'Service Job'): ?>
                                    <span class="badge bg-primary">JOB-<?= $p->reference ?></span>
                                <?php else: ?>
                                    <span class="badge bg-info">PO-<?= $p->reference ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($p->party) ?></td>
                            <td class="text-end">LKR <?= number_format($p->amount, 2) ?></td>
                            <td><?= htmlspecialchars($p->card_or_cheque_no) ?></td>
                            <td><?= htmlspecialchars($p->bank_name) ?></td>
                            <td><?= $p->cheque_date ? date('Y-m-d', strtotime($p->cheque_date)) : '-' ?></td>
                            <td><?= $p->payment_date ? date('Y-m-d H:i', strtotime($p->payment_date)) : '-' ?></td>
                            <td><small><?= htmlspecialchars($p->note) ?></small></td>
                            <td class="text-center">
                                <?php if (!empty($p->cleared)): ?>
                                    <button type="button" class="btn btn-sm btn-secondary" disabled>
                                        <i class="fa fa-check-double"></i> Received
                                    </button>
                                <?php else: ?>
                                    <button type="button" class="btn btn-sm btn-success" onclick="markChequeReceived('<?= strtolower($p->source) === 'quick bill' ? 'quick_bill' : (strtolower($p->source) === 'service job' ? 'service_job' : 'purchase_order') ?>', <?= (int)$p->payment_id ?>)">
                                        <i class="fa fa-check"></i> Mark Received
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; if (empty($payments)): ?>
                        <tr>
                            <td>1</td>
                            <td class="text-muted">No cheques found</td>
                            <td class="text-muted">-</td>
                            <td class="text-muted">-</td>
                            <td class="text-end text-muted">-</td>
                            <td class="text-muted">-</td>
                            <td class="text-muted">-</td>
                            <td class="text-muted">-</td>
                            <td class="text-muted">-</td>
                            <td class="text-muted">-</td>
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
        // Delay DataTable init until after initial status filter applies to avoid counting hidden rows
        setTimeout(function(){
            if ($.fn.DataTable) {
                var hasRealRows = $('#chequeTable tbody tr').length > 0 && $('#chequeTable tbody tr td').eq(1).text().trim() !== 'No cheques found';
                if (hasRealRows) {
                    if ($.fn.DataTable.isDataTable('#chequeTable')) $('#chequeTable').DataTable().destroy();
                    $('#chequeTable').DataTable({
                        lengthMenu: [[10,25,50,-1],[10,25,50,'All']],
                        searching: true,
                        responsive: true,
                        order: [[8, 'desc']]
                    });
                }
            }
        }, 50);

        // Live status toggle (no reload)
        $('#filterStatus').on('change', function(){
            applyStatusFilter();
        });

        // Apply filters (with reload for date/customer/supplier)
        $('#applyFilters').click(function() {
            var customerId = $('#filterCustomer').val();
            var supplierId = $('#filterSupplier').val();
            var dateFrom = $('#filterDateFrom').val();
            var dateTo = $('#filterDateTo').val();
            var status = $('#filterStatus').val();
            var url = new URL(window.location);
            if (customerId) url.searchParams.set('customer_id', customerId);
            if (supplierId) url.searchParams.set('supplier_id', supplierId);
            if (dateFrom) url.searchParams.set('date_from', dateFrom);
            if (dateTo) url.searchParams.set('date_to', dateTo);
            if (status) url.searchParams.set('status', status);
            window.location.href = url.toString();
        });

        // Clear filters
        $('#clearFilters').click(function() {
            $('#filterCustomer').val('');
            $('#filterSupplier').val('');
            $('#filterDateFrom').val('');
            $('#filterDateTo').val('');
            $('#filterStatus').val('uncleared');
            window.location.href = window.location.pathname;
        });

        // Initial client-side status filter pass (matches current selection)
        applyStatusFilter();
    });

    function applyStatusFilter(){
        var status = ($('#filterStatus').val() || 'uncleared');
        $('#chequeTable tbody tr').each(function(){
            var cleared = $(this).data('cleared');
            if (status === 'all') {
                $(this).show();
            } else if (status === 'cleared') {
                $(this).toggle(cleared === 1 || cleared === '1');
            } else { // uncleared
                $(this).toggle(!(cleared === 1 || cleared === '1'));
            }
        });
    }

    function markChequeReceived(source, paymentId) {
        Swal.fire({
            title: 'Confirm Cheque Receipt',
            text: 'Credit this cheque amount to Bank now?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, credit to Bank',
            cancelButtonText: 'Cancel'
        }).then(function(result){
            if (!result.isConfirmed) return;
            $.post('<?= base_url('accounts/receive_cheque'); ?>', {
                source: source,
                payment_id: paymentId
            }, function(res){
                if (res.status === 'success') {
                    Toastify({ text: 'Cheque credited to Bank', className: 'toastify-success', duration: 3000 }).showToast();
                    location.reload();
                } else {
                    Swal.fire('Error', res.message || 'Failed to mark cheque as received', 'error');
                }
            }, 'json').fail(function(){
                Swal.fire('Error', 'Request failed', 'error');
            });
        });
    }
</script>


