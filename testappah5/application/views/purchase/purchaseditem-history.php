<?php $this->load->view('layout/header'); ?>
<?php $this->load->view('layout/top_navbar'); ?>
<?php $this->load->view('layout/left_sidebar'); ?>
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Purchaseditem History</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">Purchase Order</a></li>
                    <li class="breadcrumb-item active">Purchaseditem History</li>
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
                <h4 class="card-title">Purchased Items History</h4>
                <p class="card-title-desc">History of all purchased items across all purchase orders</p>
            </div>
            
            <!-- Filters Section -->
            <div class="card-body border-bottom">
                <div class="row">
                    <div class="col-md-3">
                        <label class="form-label">Supplier</label>
                        <select class="form-control" id="filterSupplier">
                            <option value="">All Suppliers</option>
                            <?php if(isset($suppliers) && !empty($suppliers)): ?>
                                <?php foreach($suppliers as $supplier): ?>
                                    <option value="<?= $supplier->supplier_id ?>" <?= (isset($filters['supplier_id']) && $filters['supplier_id'] == $supplier->supplier_id) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($supplier->name) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
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
                        <label class="form-label">Item Name</label>
                        <input type="text" class="form-control" id="filterItemName" placeholder="Search by item name..." value="<?= isset($filters['item_name']) ? $filters['item_name'] : '' ?>">
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
                <?php if (isset($purchased_items) && !empty($purchased_items)): ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>PO ID</th>
                                    <th>Bill No</th>
                                    <th>Supplier</th>
                                    <th>Item Name</th>
                                    <th>SKU</th>
                                    <th>Quantity</th>
                                    <th>Company Price</th>
                                    <th>Sell Price</th>
                                    <th>Purchase Date</th>
                                    <th>Sell Price Edit</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($purchased_items as $index => $row): ?>
                                    <tr>
                                        <td><?= isset($row->po_id) ? $row->po_id : 'N/A' ?></td>
                                        <td><?= isset($row->bill_no) ? $row->bill_no : 'N/A' ?></td>
                                        <td><?= isset($row->supplier_name) ? $row->supplier_name : 'N/A' ?></td>
                                        <td><?= isset($row->item_name) ? $row->item_name : 'N/A' ?></td>
                                        <td><?= isset($row->sku) ? $row->sku : 'N/A' ?></td>
                                        <td class="text-center"><?= isset($row->quantity) ? $row->quantity : '0' ?></td>
                                        <td class="text-end">Rs. <?= isset($row->company_price) ? number_format($row->company_price, 2) : '0.00' ?></td>
                                        <td class="text-end">Rs. <?= isset($row->sale_price) ? number_format($row->sale_price, 2) : '0.00' ?></td>
                                        <td class="text-center"><?= isset($row->purchase_date) ? date('d/m/Y', strtotime($row->purchase_date)) : 'N/A' ?></td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-warning" onclick="editSellPrice('<?= isset($row->po_item_id) ? $row->po_item_id : '' ?>', '<?= isset($row->sale_price) ? $row->sale_price : '0' ?>')" title="Edit Sell Price">
                                                <i class="fa fa-edit"></i> Edit
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
                        <h5 class="text-muted">No Purchased Items History Found</h5>
                        <p class="text-muted">Purchased items will appear here once data is available.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Edit Sell Price Modal -->
<div class="modal fade" id="editSellPriceModal" tabindex="-1" aria-labelledby="editSellPriceModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editSellPriceModalLabel">
                    <i class="fa fa-edit"></i> Edit Sell Price
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editSellPriceForm">
                    <input type="hidden" id="editPoItemId" name="po_item_id">
                    <div class="mb-3">
                        <label for="editSellPrice" class="form-label">Sell Price (Rs.)</label>
                        <input type="number" class="form-control" id="editSellPrice" name="sale_price" step="0.01" min="0" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveSellPrice()">Save Changes</button>
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
    var itemName = $('#filterItemName').val();
    
    // Reload page with filters
    var url = new URL(window.location);
    if (supplierId) url.searchParams.set('supplier_id', supplierId);
    if (dateFrom) url.searchParams.set('date_from', dateFrom);
    if (dateTo) url.searchParams.set('date_to', dateTo);
    if (itemName) url.searchParams.set('item_name', itemName);
    
    window.location.href = url.toString();
});

// Clear filters
$('#clearFilters').click(function() {
    $('#filterSupplier').val('');
    $('#filterDateFrom').val('');
    $('#filterDateTo').val('');
    $('#filterItemName').val('');
    window.location.href = window.location.pathname;
});

function editSellPrice(poItemId, currentPrice) {
    // Set the PO item ID and current price
    $('#editPoItemId').val(poItemId);
    $('#editSellPrice').val(currentPrice);
    
    // Show modal
    $('#editSellPriceModal').modal('show');
}

function saveSellPrice() {
    var poItemId = $('#editPoItemId').val();
    var newPrice = $('#editSellPrice').val();
    
    if (!poItemId || !newPrice) {
        alert('Please enter a valid sell price.');
        return;
    }
    
    // Send AJAX request to update sell price
    $.ajax({
        url: '<?= base_url("purchase/updateSellPrice") ?>',
        type: 'POST',
        data: {
            po_item_id: poItemId,
            sale_price: newPrice
        },
        dataType: 'json',
        beforeSend: function() {
            $('button[onclick="saveSellPrice()"]').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');
        },
        success: function(response) {
            if (response.success) {
                // Close modal
                $('#editSellPriceModal').modal('hide');
                
                // Show success message
                alert('Sell price updated successfully!');
                
                // Reload the page to show updated data
                location.reload();
            } else {
                alert('Error updating sell price: ' + (response.message || 'Unknown error'));
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', {xhr: xhr, status: status, error: error});
            alert('Error updating sell price. Please try again.');
        },
        complete: function() {
            $('button[onclick="saveSellPrice()"]').prop('disabled', false).html('Save Changes');
        }
    });
}
</script>

<?php $this->load->view('layout/footer'); ?>
