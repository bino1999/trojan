 <table id="table1" class="table table-striped table-responsive">
<thead class="table-info">
    <tr>
        <th class="text-start" style="width:5%">##</th>
        <th class="text-start" style="width:10%">Bill ID</th>
        <th class="text-start" style="width:20%">Customer</th>
        <th class="text-start" style="width:10%">Date</th>
        <th class="text-start" style="width:10%">Type</th>
        <th class="text-end" style="width:10%">Amount</th>
        <th class="text-end" style="width:10%">Paid</th>
        <th class="text-end" style="width:10%">Balance</th>
        <th class="text-start" style="width:5%">User</th>
        <th class="text-start" style="width:8%">Return</th>
        <th class="text-start" style="width:10%">Actions</th>
    </tr>
</thead>

     
<tbody>
<?php
$no = 1;
foreach ($quickBills as $bill) { ?>
    <tr>
        <td><?= $no++; ?></td>
        <td><?= str_pad($bill->quick_bill_id, 4, '0', STR_PAD_LEFT) ?></td>
        <td>
            <?php if ($bill->customer_id == 0): ?>
                <span class="badge bg-secondary">W-C</span>
            <?php endif; ?>  
            <?= htmlspecialchars($bill->customer_name ?? '') ?>
            <?php if (!empty($bill->customer_mobile)): ?>
                <div class="text-muted f12"><?= htmlspecialchars($bill->customer_mobile) ?></div>
            <?php endif; ?>
            <?php if (!empty($bill->updated_at)): ?>
                <span class="badge bg-info ms-1">Edited</span>
            <?php endif; ?>
        </td>
        <td><?= date('Y-m-d', strtotime($bill->created_at)); ?></td>

        <td>Quick Bill</td> <!-- You can add a type field if needed -->
        <td class="text-end"><?= number_format($bill->final_bill_amount ?? 0, 2) ?></td>
        <td class="text-end"><?= number_format($bill->total_paid ?? 0, 2) ?></td>
        <td class="text-end">
            <?= number_format($bill->total_balance ?? 0, 2) ?>
            <?php if ($bill->total_balance > 0): ?>
                <span class="badge bg-danger">Due</span>
            <?php endif; ?>
        </td>
        <td><?= $bill->created_by; ?></td>
        <td>
            <?php
            // Check if there are pending return requests for this bill
            $this->load->model('QuickBill_model');
            $pendingReturns = $this->QuickBill_model->getPendingReturnCount($bill->quick_bill_id);
            ?>
            <button class="btn btn-warning btn-sm position-relative" 
                    onclick="openReturnModal(<?= $bill->quick_bill_id ?>)" 
                    title="Return Items">
                <i class="fa fa-undo"></i>
                <?php if ($pendingReturns > 0): ?>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        <?= $pendingReturns ?>
                    </span>
                <?php endif; ?>
            </button>
        </td>
        <td>
            <div class="d-flex gap-2">
                <button class="btn btn-primary btn-sm" onclick="editQuickBill(<?= $bill->quick_bill_id ?>)" title="Edit Bill">
                    <i class="fa fa-edit"></i>
                </button>
                <button class="btn btn-danger btn-sm" onclick="deleteQuickBill(<?= $bill->quick_bill_id ?>)" title="Delete Bill">
                    <i class="fa fa-trash"></i>
                </button>
                <button class="btn btn-info btn-sm" onclick="printQuickBill(<?= $bill->quick_bill_id ?>)" title="Print Bill">
                    <i class="fa fa-print"></i>
                </button>
                <button class="btn btn-secondary btn-sm" onclick="printA4QuickBill(<?= $bill->quick_bill_id ?>)" title="Print A4 Bill">
                    <i class="fa fa-file-text"></i>
                </button>
            </div>
        </td>
    </tr>
<?php } ?>
</tbody>
</table>

<!-- Edit Quick Bill Modal -->
<div class="modal fade" id="editQuickBillModal" tabindex="-1" aria-labelledby="editQuickBillModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editQuickBillModalLabel">Edit Quick Bill</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="editModalBody">
                <!-- Bill data will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- Return Items Modal -->
<div class="modal fade" id="returnModal" tabindex="-1" aria-labelledby="returnModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="returnModalLabel">Return Items - Bill #<span id="returnBillId"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Bill Details -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <h6>Bill Details</h6>
                        <div id="returnBillDetails"></div>
                    </div>
                    <div class="col-md-6">
                        <h6>Customer Details</h6>
                        <div id="returnCustomerDetails"></div>
                    </div>
                </div>

                <!-- Return Request Section -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0">Return Request</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="returnItemsTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>Product</th>
                                        <th>Original Qty</th>
                                        <th>Already Returned</th>
                                        <th>Available for Return</th>
                                        <th>Return Qty</th>
                                        <th>Unit Price</th>
                                        <th>Return Amount</th>
                                        <th>Reason</th>
                                    </tr>
                                </thead>
                                <tbody id="returnItemsBody">
                                    <!-- Items will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <label for="returnReason" class="form-label">General Return Reason:</label>
                                <textarea class="form-control" id="returnReason" rows="2" placeholder="Enter reason for return..."></textarea>
                            </div>
                            <div class="col-md-6 text-end">
                                <button type="button" class="btn btn-primary" onclick="submitReturnRequest()">
                                    <i class="fa fa-paper-plane"></i> Submit Return Request
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Return Approvals Section -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Return Approvals</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="returnApprovalsTable">
                                <thead class="table-light">
                                    <tr>
                                        <th><input type="checkbox" id="selectAllReturns" onchange="toggleAllReturns(this)"></th>
                                        <th>Date</th>
                                        <th>Items</th>
                                        <th>Total Amount</th>
                                        <th>Reason</th>
                                        <th>Status</th>
                                        <th>Requested By</th>
                                    </tr>
                                </thead>
                                <tbody id="returnApprovalsBody">
                                    <!-- Return requests will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <button type="button" class="btn btn-success" onclick="approveSelectedReturns()" id="approveBtn" style="display: none;">
                                    <i class="fa fa-check"></i> Approve Selected Returns
                                </button>
                            </div>
                            <div class="col-md-6 text-end">
                                <button type="button" class="btn btn-secondary" onclick="refreshReturnApprovals()">
                                    <i class="fa fa-refresh"></i> Refresh
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        var table = $('#table1').DataTable({
            "pageLength": 25,
            "lengthMenu": [25, 50, 100, 200],
            "searching": true,
            "language": {
                "search": "Search by customer name or phone:",
                "lengthMenu": "Show _MENU_ entries",
                "info": "Showing _START_ to _END_ of _TOTAL_ entries",
                "infoEmpty": "No entries available",
                "infoFiltered": "(filtered from _MAX_ total entries)"
            },
            "columnDefs": [
                {
                    "targets": [2], // Customer column (index 2)
                    "searchable": true,
                    "type": "string"
                }
            ]
        });

        // Enhanced search functionality for customer name and phone
        $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
            var searchTerm = table.search();
            if (!searchTerm) {
                return true; // Show all rows if no search term
            }
            
            // Get customer data from the customer column (index 2)
            var customerData = data[2].toLowerCase();
            
            // Check if search term matches customer name or phone
            return customerData.indexOf(searchTerm.toLowerCase()) !== -1;
        });
    });

    // Edit Quick Bill
    function editQuickBill(billId) {
        // Open edit modal
        $('#editQuickBillModal').modal('show');
        
        // Load bill data
        $.ajax({
            url: '<?= base_url("quickBill/getQuickBillForEdit") ?>',
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
                        $('#editModalBody').html(result.html);
                    } else {
                        Swal.fire('Error', result.message, 'error');
                    }
                } catch (e) {
                    console.error('JSON parse error:', e);
                    Swal.fire('Error', 'Error processing response', 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'An error occurred while loading the bill', 'error');
            }
        });
    }

    // Open Return Modal
    function openReturnModal(billId) {
        $('#returnBillId').text(billId);
        $('#returnModal').modal('show');
        loadReturnData(billId);
    }

    // Load Return Data
    function loadReturnData(billId) {
        $.ajax({
            url: '<?= base_url("quickBill/getReturnData") ?>',
            type: 'POST',
            data: { bill_id: billId },
            success: function(response) {
                console.log('Raw response:', response);
                console.log('Response type:', typeof response);
                
                try {
                    let result;
                    if (typeof response === 'string') {
                        result = JSON.parse(response);
                    } else {
                        result = response;
                    }
                    
                    console.log('Parsed result:', result);
                    
                    if (result.status === 'success') {
                        $('#returnBillDetails').html(result.billDetails);
                        $('#returnCustomerDetails').html(result.customerDetails);
                        loadReturnItems(result.items);
                        loadReturnApprovals(billId);
                    } else {
                        Swal.fire('Error', result.message, 'error');
                    }
                } catch (e) {
                    console.error('JSON parse error:', e);
                    console.error('Response that failed to parse:', response);
                    Swal.fire('Error', 'Error processing response: ' + e.message, 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'Error occurred while loading return data', 'error');
            }
        });
    }

    // Load Return Items
    function loadReturnItems(items) {
        console.log('Loading return items:', items);
        let html = '';
        items.forEach(function(item, index) {
            console.log('Processing item:', item);
            html += `
                <tr data-item-id="${item.id}" data-original-qty="${item.quantity}">
                    <td>${item.item_name}</td>
                    <td>${item.quantity}</td>
                    <td>${item.already_returned_qty || 0}</td>
                    <td>${item.available_for_return_qty || 0}</td>
                    <td>
                        <input type="number" class="form-control return-qty" 
                               min="0" max="${item.available_for_return_qty || 0}" value="0" 
                               onchange="calculateReturnAmount(${index})"
                               ${item.available_for_return_qty <= 0 ? 'disabled' : ''}>
                    </td>
                    <td>${parseFloat(item.price).toFixed(2)}</td>
                    <td>
                        <input type="text" class="form-control return-amount" readonly value="0.00">
                    </td>
                    <td>
                        <input type="text" class="form-control return-item-reason" 
                               placeholder="Reason for this item..."
                               ${item.available_for_return_qty <= 0 ? 'disabled' : ''}>
                    </td>
                </tr>
            `;
        });
        console.log('Generated HTML:', html);
        $('#returnItemsBody').html(html);
        console.log('Items loaded successfully');
    }

    // Calculate Return Amount
    function calculateReturnAmount(index) {
        const row = $(`#returnItemsBody tr:eq(${index})`);
        const returnQty = parseFloat(row.find('.return-qty').val()) || 0;
        const price = parseFloat(row.find('td:eq(5)').text()) || 0; // Price is at index 5
        const returnAmount = returnQty * price;
        row.find('.return-amount').val(returnAmount.toFixed(2));
    }

    // Submit return request
    function submitReturnRequest() {
        const returnItems = [];
        let hasValidReturns = false;
        
        $('#returnItemsBody tr').each(function(index) {
            const returnQty = parseFloat($(this).find('.return-qty').val()) || 0;
            if (returnQty > 0) {
                hasValidReturns = true;
                returnItems.push({
                    item_id: $(this).data('item-id'),
                    quick_bill_item_id: $(this).data('item-id'),
                    return_quantity: returnQty,
                    return_amount: parseFloat($(this).find('.return-amount').val()) || 0,
                    reason: $(this).find('.return-item-reason').val() || ''
                });
            }
        });
        
        if (!hasValidReturns) {
            Swal.fire('Warning', 'Please select at least one item to return.', 'warning');
            return;
        }
        
        const generalReason = $('#returnReason').val().trim();
        if (!generalReason) {
            Swal.fire('Warning', 'Please provide a general return reason.', 'warning');
            return;
        }
        
        const totalAmount = returnItems.reduce((sum, item) => sum + item.return_amount, 0);
        
        $.ajax({
            url: '<?= base_url("quickBill/submitReturnRequest") ?>',
            type: 'POST',
            data: {
                bill_id: $('#returnBillId').text(),
                general_reason: generalReason,
                total_amount: totalAmount,
                return_items: JSON.stringify(returnItems)
            },
            success: function(response) {
                try {
                    const result = JSON.parse(response);
                    if (result.status === 'success') {
                        Swal.fire('Success', 'Return request submitted successfully!', 'success');
                        // Clear form and refresh return approvals
                        $('#returnReason').val('');
                        $('#returnItemsBody .return-qty').val('0');
                        $('#returnItemsBody .return-amount').val('0.00');
                        $('#returnItemsBody .return-item-reason').val('');
                        loadReturnApprovals($('#returnBillId').text());
                    } else {
                        Swal.fire('Error', result.message, 'error');
                    }
                } catch (e) {
                    Swal.fire('Error', 'Error processing response', 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'An error occurred while submitting return request', 'error');
            }
        });
    }

    // Load Return Approvals
    function loadReturnApprovals(billId) {
        console.log('Loading return approvals for bill:', billId);
        
        if (!billId || billId === '') {
            console.log('No bill ID provided, skipping return approvals load');
            return;
        }
        
        $.ajax({
            url: '<?= base_url("quickBill/getReturnApprovals") ?>',
            type: 'POST',
            data: { bill_id: billId },
            success: function(response) {
                console.log('Raw return approvals response:', response);
                try {
                    const result = JSON.parse(response);
                    console.log('Parsed return approvals result:', result);
                    if (result.status === 'success') {
                        displayReturnApprovals(result.approvals);
                    } else {
                        Swal.fire('Error', result.message, 'error');
                    }
                } catch (e) {
                    console.error('JSON parse error for return approvals:', e);
                    console.error('Response that failed to parse:', response);
                    Swal.fire('Error', 'Error processing response: ' + e.message, 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'Error occurred while loading return approvals', 'error');
            }
        });
    }

    // Display Return Approvals
    function displayReturnApprovals(approvals) {
        console.log('Displaying approvals:', approvals);
        let html = '';
        
        try {
            if (approvals.length === 0) {
                html = '<tr><td colspan="7" class="text-center">No return requests found</td></tr>';
            } else {
                approvals.forEach(function(approval) {
                    html += `
                        <tr data-approval-id="${approval.id}">
                            <td>
                                <input type="checkbox" class="return-approval-checkbox" 
                                       value="${approval.id}" onchange="toggleApproveButton()">
                            </td>
                            <td>${approval.created_at}</td>
                            <td>${approval.items_summary}</td>
                            <td>${parseFloat(approval.total_amount).toFixed(2)}</td>
                            <td>${approval.reason}</td>
                            <td>
                                <span class="badge bg-warning">Pending</span>
                            </td>
                            <td>${approval.created_by_name}</td>
                        </tr>
                    `;
                });
            }
            console.log('Generated approvals HTML:', html);
            $('#returnApprovalsBody').html(html);
            console.log('Checkboxes after rendering:', $('.return-approval-checkbox').length);
            toggleApproveButton();
            console.log('Display approvals completed successfully');
        } catch (error) {
            console.error('Error in displayReturnApprovals:', error);
            Swal.fire('Error', 'Error displaying return approvals: ' + error.message, 'error');
        }
    }

    // Toggle Approve Button
    function toggleApproveButton() {
        const checkedBoxes = $('.return-approval-checkbox:checked');
        console.log('Toggle button - Checked boxes:', checkedBoxes.length);
        
        if (checkedBoxes.length > 0) {
            $('#approveBtn').show();
            console.log('Showing approve button');
        } else {
            $('#approveBtn').hide();
            console.log('Hiding approve button');
        }
    }

    // Toggle All Returns
    function toggleAllReturns(checkbox) {
        console.log('Toggle all - Checkbox checked:', checkbox.checked);
        $('.return-approval-checkbox').prop('checked', checkbox.checked);
        console.log('Checkboxes after toggle all:', $('.return-approval-checkbox:checked').length);
        toggleApproveButton();
    }

    // Approve selected returns
    function approveSelectedReturns() {
        const selectedReturns = [];
        const checkedBoxes = $('.return-approval-checkbox:checked');
        
        console.log('Total checkboxes found:', $('.return-approval-checkbox').length);
        console.log('Checked checkboxes:', checkedBoxes.length);
        
        checkedBoxes.each(function() {
            selectedReturns.push($(this).val());
        });
        
        console.log('Selected returns:', selectedReturns);
        
        if (selectedReturns.length === 0) {
            Swal.fire('Warning', 'Please select at least one return request to approve.', 'warning');
            return;
        }
        
        Swal.fire({
            title: 'Confirm Approval',
            text: `Are you sure you want to approve ${selectedReturns.length} return request(s)? This will update stock quantities.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Approve',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '<?= base_url("quickBill/approveReturns") ?>',
                    type: 'POST',
                    data: {
                        return_ids: selectedReturns
                    },
                    success: function(response) {
                        try {
                            const result = JSON.parse(response);
                            if (result.status === 'success') {
                                Swal.fire('Success', 'Returns approved successfully! Stock quantities have been updated.', 'success');
                                // Refresh the modal data instead of closing
                                const billId = $('#returnBillId').text();
                                loadReturnApprovals(billId);
                                // Also refresh the return items to show updated quantities
                                loadReturnData(billId);
                            } else {
                                Swal.fire('Error', result.message, 'error');
                            }
                        } catch (e) {
                            Swal.fire('Error', 'Error processing response', 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'An error occurred while approving returns', 'error');
                    }
                });
            }
        });
    }

    // Refresh Return Approvals
    function refreshReturnApprovals() {
        const billId = $('#returnBillId').text();
        loadReturnApprovals(billId);
    }

    // Delete Quick Bill
    function deleteQuickBill(billId) {
        Swal.fire({
            title: 'Delete Quick Bill',
            text: 'Are you sure you want to delete this bill? This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!',
            input: 'textarea',
            inputLabel: 'Deletion Reason',
            inputPlaceholder: 'Please provide a reason for deletion (mandatory)',
            inputValidator: (value) => {
                if (!value || value.trim() === '') {
                    return 'Deletion reason is mandatory!';
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const deletionReason = result.value.trim();
                
                // Show loading state
                Swal.fire({
                    title: 'Deleting...',
                    text: 'Please wait while we delete the bill and restore stock items.',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                $.ajax({
                    url: '<?= base_url("quickBill/deleteQuickBill") ?>',
                    type: 'POST',
                    data: { 
                        bill_id: billId,
                        deletion_reason: deletionReason
                    },
                    success: function(response) {
                        try {
                            const result = JSON.parse(response);
                            if (result.status === 'success') {
                                Swal.fire('Deleted!', result.message, 'success');
                                location.reload();
                            } else {
                                Swal.fire('Error', result.message, 'error');
                            }
                        } catch (e) {
                            Swal.fire('Error', 'Error processing response', 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'An error occurred while deleting the bill', 'error');
                    }
                });
            }
        });
    }

    // Print Quick Bill
    function printQuickBill(billId) {
        // Open print window with the bill details
        const printWindow = window.open('<?= base_url("quickBill/print_receipt/") ?>' + billId, '_blank');
        if (printWindow) {
            printWindow.focus();
        } else {
            Swal.fire('Error', 'Please allow popups to print bills', 'error');
        }
    }

    // Print A4 Quick Bill
    function printA4QuickBill(billId) {
        // Open print window with the A4 bill details
        const printWindow = window.open('<?= base_url("quickBill/printA4Receipt/") ?>' + billId, '_blank');
        if (printWindow) {
            printWindow.focus();
        } else {
            Swal.fire('Error', 'Please allow popups to print A4 bills', 'error');
        }
    }
</script>