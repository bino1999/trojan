<?php if (empty($internalBills)) : ?>
    <div class="alert alert-info">
        <strong>No internal bills found!</strong> 
        <br>Internal bills will appear here once they are created.
    </div>
<?php else : ?>
    <div class="table-responsive">
        <table class="table table-striped table-bordered">
                            <thead class="table-dark">
                    <tr>
                        <th>Bill ID</th>
                        <th>Date</th>
                        <th>Employee</th>
                        <th>Payment Method</th>
                        <th>Total Amount</th>
                        <th>Paid Amount</th>
                        <th>Created By</th>
                        <th>Actions</th>
                    </tr>
                </thead>
            <tbody>
                <?php foreach ($internalBills as $bill) : ?>
                    <tr>
                        <td>
                            <strong>#<?= $bill->id ?></strong>
                        </td>
                        <td><?= date('d/m/Y', strtotime($bill->bill_date)) ?></td>
                        <td>
                            <?php if ($bill->customer_id == 0): ?>
                                <span class="badge bg-info">Walk-in Person</span>
                            <?php else: ?>
                                <?= ($bill->customer_name ?? '') . ' ' . ($bill->customer_last_name ?? '') ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge bg-secondary"><?= $bill->payment_method ?? 'N/A' ?></span>
                        </td>
                        <td>
                            <span class="badge bg-success"><?= number_format($bill->grand_total, 2) ?></span>
                        </td>
                        <td>
                            <span class="badge bg-primary"><?= number_format($bill->paid_amount ?? 0, 2) ?></span>
                        </td>
                        <td><?= $bill->created_by_name ?? 'Unknown' ?></td>
                        <td>
                            <button class="btn btn-info btn-sm" onclick="viewBillDetails(<?= $bill->id ?>)">
                                <i class="fas fa-eye"></i> View
                            </button>
                            <button class="btn btn-danger btn-sm" onclick="deleteBill(<?= $bill->id ?>)">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<!-- Bill Details Modal -->
<div class="modal fade" id="billDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bill Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="billDetailsContent">
                    <!-- Bill details will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function viewBillDetails(billId) {
    $.ajax({
        url: "<?= site_url('internalBill/getBillDetails') ?>",
        type: "POST",
        data: { bill_id: billId },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                let bill = response.bill;
                let items = response.items;
                
                let content = `
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Bill ID:</strong> #${bill.id}<br>
                            <strong>Date:</strong> ${new Date(bill.bill_date).toLocaleDateString()}<br>
                            <strong>Employee:</strong> ${bill.customer_id == 0 ? 'Walk-in Person' : ((bill.customer_name || '') + ' ' + (bill.customer_last_name || ''))}<br>
                            <strong>Payment Method:</strong> ${bill.payment_method || 'N/A'}<br>
                            <strong>Paid Amount:</strong> Rs. ${parseFloat(bill.paid_amount || 0).toFixed(2)}<br>
                            <strong>Created By:</strong> ${bill.created_by_name}
                        </div>
                        <div class="col-md-6 text-end">
                            <h4>Total: Rs. ${parseFloat(bill.grand_total).toFixed(2)}</h4>
                            ${bill.note ? `<br><strong>Note:</strong> ${bill.note}` : ''}
                        </div>
                    </div>
                    <hr>
                    <h6>Bill Items:</h6>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                `;
                
                items.forEach(function(item) {
                    content += `
                        <tr>
                            <td>${item.product_name}</td>
                            <td>${item.quantity}</td>
                            <td>${parseFloat(item.price).toFixed(2)}</td>
                            <td>${parseFloat(item.total_price).toFixed(2)}</td>
                        </tr>
                    `;
                });
                
                content += `
                        </tbody>
                    </table>
                `;
                
                $('#billDetailsContent').html(content);
                $('#billDetailsModal').modal('show');
            } else {
                Swal.fire('Error', response.message, 'error');
            }
        },
        error: function(xhr, status, error) {
            Swal.fire('Error!', 'Failed to load bill details.', 'error');
        }
    });
}

function deleteBill(billId) {
    Swal.fire({
        title: 'Delete Internal Bill',
        text: 'Are you sure you want to delete this internal bill? This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "<?= site_url('internalBill/deleteBill') ?>",
                type: "POST",
                data: { bill_id: billId },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        Swal.fire('Deleted!', 'Internal bill has been deleted.', 'success').then(() => {
                            loadInternalBillList(); // Refresh the list
                        });
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function(xhr, status, error) {
                    Swal.fire('Error!', 'Failed to delete internal bill.', 'error');
                }
            });
        }
    });
}
</script>
