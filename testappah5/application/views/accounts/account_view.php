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
                <h5 class="card-title mb-0">Current Balance</h5>
            </div>
            <div class="card-body text-center">
                <h1 class="display-5" style="color:#0ab39c; font-weight:800;">LKR <?= number_format($account->balance ?? 0, 2) ?></h1>
                <div class="text-muted">as of <?= date('Y-m-d H:i') ?></div>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Transaction Filters</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <label class="form-label">Transaction Type</label>
                        <select class="form-control" id="filterTransactionType">
                            <option value="">All Transactions</option>
                            <option value="credit" <?= (isset($filters['txn_type']) && $filters['txn_type'] == 'credit') ? 'selected' : '' ?>>Credit Only</option>
                            <option value="debit" <?= (isset($filters['txn_type']) && $filters['txn_type'] == 'debit') ? 'selected' : '' ?>>Debit Only</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Date From</label>
                        <input type="date" class="form-control" id="filterDateFrom" value="<?= isset($filters['date_from']) ? $filters['date_from'] : '' ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Date To</label>
                        <input type="date" class="form-control" id="filterDateTo" value="<?= isset($filters['date_to']) ? $filters['date_to'] : '' ?>">
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="button" class="btn btn-primary me-2" id="applyFilters">
                            <i class="fa fa-filter"></i> Apply Filters
                        </button>
                        <button type="button" class="btn btn-secondary" id="clearFilters">
                            <i class="fa fa-times"></i> Clear
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="card-title mb-0">Recent Transactions</h5>
                <button type="button" class="btn btn-primary btn-sm" id="btnTransfer">
                    <i class="fa fa-exchange-alt"></i> Make Transfer
                </button>
            </div>
            <div class="card-body">
                <table id="table1" class="table table-striped table-responsive table-sm">
                    <thead class="table-info">
                        <tr>
                            <th>#</th>
                            <th>Description</th>
                            <th>Type</th>
                            <th class="text-end">Amount</th>
                            <th class="text-end">Running Balance</th>
                            <th class="text-center">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i=1; foreach($transactions as $t): ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td><?= htmlspecialchars($t->description) ?></td>
                            <td><span class="badge <?= $t->txn_type=='credit' ? 'bg-success':'bg-danger' ?>"><?= strtoupper($t->txn_type) ?></span></td>
                            <td class="text-end"><?= number_format($t->amount, 2) ?></td>
                            <td class="text-end">
                                <?php if (isset($t->running_balance)): ?>
                                    <strong class="<?= $t->running_balance >= 0 ? 'text-success' : 'text-danger' ?>">
                                        LKR <?= number_format($t->running_balance, 2) ?>
                                    </strong>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center"><?= date('Y-m-d H:i', strtotime($t->created_at)) ?></td>
                        </tr>
                        <?php endforeach; if (empty($transactions)): ?>
                        <tr>
                            <td>1</td>
                            <td class="text-muted">No transactions found</td>
                            <td class="text-muted">-</td>
                            <td class="text-end text-muted">-</td>
                            <td class="text-end text-muted">-</td>
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
    (function(){
        if ($.fn.DataTable) {
            var hasRealRows = $('#table1 tbody tr').length > 0 && $('#table1 tbody tr td').eq(1).text().trim() !== 'No transactions found';
            if (hasRealRows) {
                if ($.fn.DataTable.isDataTable('#table1')) $('#table1').DataTable().destroy();
                $('#table1').DataTable({
                    lengthMenu: [[10,25,50,-1],[10,25,50,'All']],
                    searching: true,
                    responsive: true
                });
            }
        }
    })();

    // Filter functionality
    $('#applyFilters').click(function() {
        var txnType = $('#filterTransactionType').val();
        var dateFrom = $('#filterDateFrom').val();
        var dateTo = $('#filterDateTo').val();
        
        // Reload page with filters
        var url = new URL(window.location);
        if (txnType) url.searchParams.set('txn_type', txnType);
        if (dateFrom) url.searchParams.set('date_from', dateFrom);
        if (dateTo) url.searchParams.set('date_to', dateTo);
        
        window.location.href = url.toString();
    });

    // Clear filters
    $('#clearFilters').click(function() {
        $('#filterTransactionType').val('');
        $('#filterDateFrom').val('');
        $('#filterDateTo').val('');
        window.location.href = window.location.pathname;
    });

    // Transfer functionality
    $(document).on('click', '#btnTransfer', function(e) {
        e.preventDefault();
        $('#transferModal').modal('show');
    });

    $(document).on('click', '#saveTransfer', function(e) {
        e.preventDefault();
        
        var fromSlug = '<?= $account->slug ?>';
        var toSlug = $('#transfer_to').val();
        var amount = parseFloat($('#transfer_amount').val());
        var reason = $('#transfer_reason').val();
        var note = $('#transfer_note').val();
        
        if (!toSlug || !amount || amount <= 0 || !reason) {
            Swal.fire('Error', 'Please fill all required fields', 'error');
            return;
        }
        
        $.post("<?= base_url('accounts/transfer'); ?>", {
            from_slug: fromSlug,
            to_slug: toSlug,
            amount: amount,
            reason: reason,
            note: note
        }, function(res) {
            if (res.status === 'success') {
                Toastify({ text: 'Transfer completed successfully', className: 'toastify-success', duration: 3000 }).showToast();
                $('#transferModal').modal('hide');
                location.reload();
            } else {
                Swal.fire('Error', res.message || 'Transfer failed', 'error');
            }
        }, 'json').fail(function(jq) {
            Swal.fire('Error', 'Request failed', 'error');
        });
    });
</script>

<!-- Transfer Modal -->
<div class="modal fade" id="transferModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Make Transfer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">From Account</label>
                    <input type="text" class="form-control" value="<?= $account->name ?>" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label">To Account *</label>
                    <select class="form-control" id="transfer_to" required>
                        <option value="">Select Target Account</option>
                        <option value="in_hand" <?= $account->slug === 'in_hand' ? 'disabled' : '' ?>>In Hand</option>
                        <option value="bank" <?= $account->slug === 'bank' ? 'disabled' : '' ?>>Bank</option>
                        <option value="card_machine" <?= $account->slug === 'card_machine' ? 'disabled' : '' ?>>Card Machine</option>
                        <option value="credits" <?= $account->slug === 'credits' ? 'disabled' : '' ?>>Credit</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Amount *</label>
                    <input type="number" min="0" step="0.01" class="form-control" id="transfer_amount" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Reason *</label>
                    <input type="text" class="form-control" id="transfer_reason" placeholder="e.g., Cash management, Payment processing" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Note (Optional)</label>
                    <textarea class="form-control" id="transfer_note" rows="2" placeholder="Additional notes..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="saveTransfer">Transfer</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>


