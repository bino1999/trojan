<?php if ($data): ?>
    <?php 
    $history = $data['history'];
    $items = $data['items'];
    $payments = $data['payments'];
    ?>

    <!-- PO Header Information -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card border-primary">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="fa fa-info-circle"></i> Purchase Order Information</h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>PO ID:</strong></td>
                            <td><?= $history->po_id ?></td>
                        </tr>
                        <tr>
                            <td><strong>Bill No:</strong></td>
                            <td><?= $history->bill_no ?></td>
                        </tr>
                        <tr>
                            <td><strong>Bill Date:</strong></td>
                            <td><?= date('d/m/Y', strtotime($history->bill_date)) ?></td>
                        </tr>
                        <tr>
                            <td><strong>Supplier:</strong></td>
                            <td><?= $history->supplier_name ?></td>
                        </tr>
                        <tr>
                            <td><strong>Contact:</strong></td>
                            <td><?= $history->contact_no ?: 'N/A' ?></td>
                        </tr>
                        <tr>
                            <td><strong>Email:</strong></td>
                            <td><?= $history->email ?: 'N/A' ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-success">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0"><i class="fa fa-credit-card"></i> Payment Summary</h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>Total Amount:</strong></td>
                            <td class="text-end">Rs. <?= number_format($history->total_amount, 2) ?></td>
                        </tr>
                        <tr>
                            <td><strong>Total Paid:</strong></td>
                            <td class="text-end">Rs. <?= number_format($history->total_paid, 2) ?></td>
                        </tr>
                        <tr>
                            <td><strong>VAT Type:</strong></td>
                            <td>
                                <?php 
                                if ($history->vat_type === 'vat_whole') {
                                    echo 'VAT - Whole Bill (' . $history->vat_percent . '%)';
                                } elseif ($history->vat_type === 'vat_per_item') {
                                    echo 'VAT - Per Items (' . $history->vat_percent . '%)';
                                } else {
                                    echo 'Non VAT';
                                }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Payment Completed:</strong></td>
                            <td><?= date('d/m/Y H:i', strtotime($history->payment_completed_at)) ?></td>
                        </tr>
                        <tr>
                            <td><strong>Moved By:</strong></td>
                            <td><?= $history->moved_by_name ?></td>
                        </tr>
                        <tr>
                            <td><strong>Moved At:</strong></td>
                            <td><?= date('d/m/Y H:i', strtotime($history->moved_at)) ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- PO Items -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-info">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="fa fa-list"></i> Purchase Order Items</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Product</th>
                                    <th>Barcode</th>
                                    <th>Category</th>
                                    <th>Brand</th>
                                    <th>Quantity</th>
                                    <th>Unit</th>
                                    <th>Company Price</th>
                                    <th>Sale Price</th>
                                    <th>Discount</th>
                                    <th>Total</th>
                                    <?php if ($history->vat_type === 'vat_per_item'): ?>
                                        <th>VAT</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $grandTotal = 0;
                                $totalVat = 0;
                                foreach ($items as $index => $item): 
                                    $qty = (float)$item->quantity;
                                    $price = (float)$item->company_price;
                                    $discountAmount = 0;

                                    if ($item->discount_percent > 0) {
                                        $discountAmount = ($price * $item->discount_percent) / 100;
                                    } elseif ($item->discount_amount > 0) {
                                        $discountAmount = (float)$item->discount_amount;
                                    }

                                    $netPrice = $price - $discountAmount;
                                    $lineTotal = $netPrice * $qty;
                                    
                                    // Calculate VAT for this item if VAT per item
                                    $itemVat = 0;
                                    if ($history->vat_type === 'vat_per_item') {
                                        $itemVat = $lineTotal * ($history->vat_percent / 100);
                                        $totalVat += $itemVat;
                                    }
                                    
                                    $grandTotal += $lineTotal;
                                ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td><?= strtoupper($item->product_name) ?></td>
                                        <td><?= strtoupper($item->barcode) ?></td>
                                        <td><?= strtoupper($item->category_name ?? 'N/A') ?></td>
                                        <td><?= strtoupper($item->brand_name ?? 'N/A') ?></td>
                                        <td class="text-end"><?= $qty ?></td>
                                        <td><?= $item->uom ?></td>
                                        <td class="text-end">Rs. <?= number_format($price, 2) ?></td>
                                        <td class="text-end">Rs. <?= number_format($item->sale_price, 2) ?></td>
                                        <td class="text-end">
                                            <?php
                                            if ($item->discount_percent > 0) {
                                                echo $item->discount_percent . '%';
                                            } elseif ($item->discount_amount > 0) {
                                                echo 'Rs. ' . number_format($item->discount_amount, 2);
                                            } else {
                                                echo '-';
                                            }
                                            ?>
                                        </td>
                                        <td class="text-end">Rs. <?= number_format($lineTotal, 2) ?></td>
                                        <?php if ($history->vat_type === 'vat_per_item'): ?>
                                            <td class="text-end">Rs. <?= number_format($itemVat, 2) ?></td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="table-light fw-bold">
                                <tr>
                                    <td colspan="<?= ($history->vat_type === 'vat_per_item') ? '10' : '9' ?>" class="text-end">Total</td>
                                    <td class="text-end">Rs. <?= number_format($grandTotal, 2) ?></td>
                                    <?php if ($history->vat_type === 'vat_per_item'): ?>
                                        <td class="text-end">Rs. <?= number_format($totalVat, 2) ?></td>
                                    <?php endif; ?>
                                </tr>
                                <?php if ($history->vat_type === 'vat_whole'): ?>
                                    <?php
                                    $wholeBillVat = $grandTotal * ($history->vat_percent / 100);
                                    $finalTotal = $grandTotal + $wholeBillVat;
                                    ?>
                                    <tr>
                                        <td colspan="<?= ($history->vat_type === 'vat_per_item') ? '10' : '9' ?>" class="text-end">VAT (<?= $history->vat_percent ?>%)</td>
                                        <td class="text-end">Rs. <?= number_format($wholeBillVat, 2) ?></td>
                                        <?php if ($history->vat_type === 'vat_per_item'): ?>
                                            <td></td>
                                        <?php endif; ?>
                                    </tr>
                                    <tr>
                                        <td colspan="<?= ($history->vat_type === 'vat_per_item') ? '10' : '9' ?>" class="text-end">Grand Total</td>
                                        <td class="text-end">Rs. <?= number_format($finalTotal, 2) ?></td>
                                        <?php if ($history->vat_type === 'vat_per_item'): ?>
                                            <td></td>
                                        <?php endif; ?>
                                    </tr>
                                <?php elseif ($history->vat_type === 'vat_per_item'): ?>
                                    <tr>
                                        <td colspan="<?= ($history->vat_type === 'vat_per_item') ? '10' : '9' ?>" class="text-end">Grand Total</td>
                                        <td colspan="2" class="text-end">Rs. <?= number_format($grandTotal + $totalVat, 2) ?></td>
                                    </tr>
                                <?php endif; ?>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Details -->
    <?php if (!empty($payments)): ?>
    <div class="row">
        <div class="col-12">
            <div class="card border-success">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0"><i class="fa fa-credit-card"></i> Payment Details</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Payment Method</th>
                                    <th>Amount</th>
                                    <th>Details</th>
                                    <th>Payment Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($payments as $index => $payment): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td><?= $payment->payment_method ?></td>
                                    <td class="text-end">Rs. <?= number_format($payment->paid_amount, 2) ?></td>
                                    <td><?= getPaymentDetails($payment) ?></td>
                                    <td><?= date('d/m/Y H:i', strtotime($payment->payment_date)) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="table-light fw-bold">
                                <tr>
                                    <td colspan="2" class="text-end"><strong>Total Paid:</strong></td>
                                    <td class="text-end"><strong>Rs. <?= number_format($history->total_paid, 2) ?></strong></td>
                                    <td colspan="2"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Description -->
    <?php if (!empty($history->description)): ?>
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-warning">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0"><i class="fa fa-sticky-note"></i> Description</h6>
                </div>
                <div class="card-body">
                    <p class="mb-0"><?= nl2br(htmlspecialchars($history->description)) ?></p>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

<?php else: ?>
    <div class="text-center py-5">
        <i class="fa fa-exclamation-triangle fa-3x text-warning mb-3"></i>
        <h5 class="text-warning">Purchase Order Not Found</h5>
        <p class="text-muted">The requested purchase order could not be found in the history.</p>
    </div>
<?php endif; ?>

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


