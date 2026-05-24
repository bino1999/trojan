<?php $this->load->view('layout/header'); ?>
<?php $this->load->view('layout/top_navbar'); ?>
<?php $this->load->view('layout/left_sidebar'); ?>
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Purchase Invoice History</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">Purchase Order</a></li>
                    <li class="breadcrumb-item active">Purchase Invoice History</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <style>
            .historyCard { margin: -10px; }
        </style>
        <div class="card historyCard">
            <div class="card-header">
                <h4 class="card-title">Completed Purchase Orders</h4>
                <p class="card-title-desc">Purchase orders that have been fully paid and moved to history</p>
            </div>
            
            <!-- Filters Section -->
            <div class="card-body border-bottom">
                <div class="row">
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
                    <div class="col-md-3">
                        <label class="form-label">Bill Number</label>
                        <input type="text" class="form-control" id="filterBillNumber" placeholder="Search by bill number..." value="<?= isset($filters['bill_number']) ? $filters['bill_number'] : '' ?>">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="button" class="btn btn-primary me-2" id="applyFilters">
                            <i class="fa fa-filter"></i> Apply Filters
                        </button>
                        <button type="button" class="btn btn-secondary" id="clearFilters">
                            <i class="fa fa-times"></i> Clear
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="card-body">
                <?php if (!empty($purchases)): ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>#</th>
                                    <th>PO ID</th>
                                    <th>Supplier</th>
                                    <th>Bill No</th>
                                    <th>Bill Date</th>
                                    <th>Total Amount</th>
                                    <th>Total Paid</th>
                                    <th>Payment Completed</th>
                                    <th>Moved By</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($purchases as $index => $row): ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td><?= $row->po_id ?></td>
                                        <td><?= $row->supplier_name ?></td>
                                        <td><?= $row->bill_no ?></td>
                                        <td><?= date('d/m/Y', strtotime($row->bill_date)) ?></td>
                                        <td class="text-end">Rs. <?= number_format($row->total_amount, 2) ?></td>
                                        <td class="text-end">Rs. <?= number_format($row->total_paid, 2) ?></td>
                                        <td><?= date('d/m/Y H:i', strtotime($row->payment_completed_at)) ?></td>
                                        <td><?= $row->moved_by_name ?></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-info" onclick="viewPurchaseOrder('<?= $row->po_id ?>')" title="View Details">
                                                <i class="fa fa-eye"></i> View
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fa fa-inbox fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No Purchase History Found</h5>
                        <p class="text-muted">Completed and fully paid purchase orders will appear here.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>





<!-- Purchase Order Details Modal -->
<div class="modal fade" id="poDetailsModal" tabindex="-1" aria-labelledby="poDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="poDetailsModalLabel">
                    <i class="fa fa-file-text-o"></i> Purchase Order Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="poDetailsContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading purchase order details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
// Filter functionality
$('#applyFilters').click(function() {
    var supplierId = $('#filterSupplier').val();
    var dateFrom = $('#filterDateFrom').val();
    var dateTo = $('#filterDateTo').val();
    var billNumber = $('#filterBillNumber').val();
    
    // Reload page with filters
    var url = new URL(window.location);
    if (supplierId) url.searchParams.set('supplier_id', supplierId);
    if (dateFrom) url.searchParams.set('date_from', dateFrom);
    if (dateTo) url.searchParams.set('date_to', dateTo);
    if (billNumber) url.searchParams.set('bill_number', billNumber);
    
    window.location.href = url.toString();
});

// Clear filters
$('#clearFilters').click(function() {
    $('#filterSupplier').val('');
    $('#filterDateFrom').val('');
    $('#filterDateTo').val('');
    $('#filterBillNumber').val('');
    window.location.href = window.location.pathname;
});

function viewPurchaseOrder(historyId) {
    // Show modal
    $('#poDetailsModal').modal('show');
    
    // Reset content to loading state
    $('#poDetailsContent').html(`
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading purchase order details...</p>
        </div>
    `);
    
    // Fetch purchase order details
    console.log('Sending AJAX request with history_id:', historyId);
    console.log('AJAX URL:', '<?= base_url("purchase/getPurchaseHistoryDetails") ?>');
    $.ajax({
        url: '<?= base_url("purchase/getPurchaseHistoryDetails") ?>',
        type: 'POST',
        data: { history_id: historyId },
        dataType: 'html',
        beforeSend: function() {
            console.log('AJAX request starting...');
        },
        success: function(response) {
            console.log('AJAX success, response length:', response.length);
            console.log('Response preview:', response.substring(0, 200));
            $('#poDetailsContent').html(response);
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', {xhr: xhr, status: status, error: error});
            console.error('Response text:', xhr.responseText);
            $('#poDetailsContent').html(`
                <div class="text-center py-4">
                    <i class="fa fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                    <h5 class="text-danger">Error Loading Details</h5>
                    <p class="text-muted">Unable to load purchase order details. Please try again.</p>
                    <p class="text-muted small">Error: ${error}</p>
                    <p class="text-muted small">Status: ${status}</p>
                    <p class="text-muted small">Response: ${xhr.responseText}</p>
                    <button type="button" class="btn btn-primary" onclick="viewPurchaseOrder('${historyId}')">
                        <i class="fa fa-refresh"></i> Retry
                    </button>
                </div>
            `);
        }
    });
}



</script>

<?php $this->load->view('layout/footer'); ?>

