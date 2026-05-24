<!-- Payment Summary Section -->
<div class="row mt-3" id="paymentSummarySection">
    <div class="col-md-12">
        <div class="card border-info">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0"><i class="fa fa-credit-card"></i> Payment Summary</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <strong>Invoice Total:</strong><br>
                        <span class="text-primary">Rs. <?= number_format($final_total_amount, 2) ?></span>
                    </div>
                    <div class="col-md-3">
                        <strong>Total Paid:</strong><br>
                        <span class="text-success">Rs. <?= number_format($totalPaid, 2) ?></span>
                    </div>
                    <div class="col-md-3">
                        <strong>Balance:</strong><br>
                        <span class="text-<?= ($balance > 0) ? 'danger' : 'success' ?>">Rs. <?= number_format($balance, 2) ?></span>
                    </div>
                    <div class="col-md-3">
                        <strong>Status:</strong><br>
                        <span class="badge bg-<?= ($balance > 0) ? 'warning' : 'success' ?>">
                            <?= ($balance > 0) ? 'Pending' : 'Complete' ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Confirm Invoice Section -->
<div id="confirmInvoiceSection" <?= ($totalPaid >= $final_total_amount && $final_total_amount > 0 && $jobData->confirmed_by == 0) ? '' : 'style="display: none;"' ?>>
    <div class="row mt-3">
        <div class="col-md-12">
            <div class="alert alert-success">
                <h5><i class="fa fa-check-circle"></i> Payments Complete</h5>
                <p>Total: Rs. <?= number_format($final_total_amount, 2) ?> | Paid: Rs. <?= number_format($totalPaid, 2) ?>. Please confirm payments to finalize the invoice and lock discounts.</p>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <button type="button" onclick="confirmInvoice()" class="btn btn-success w-100">
                        <i class="fa fa-check"></i> Confirm Payments
                    </button>
                </div>
                <div class="col-md-6">
                    <button type="button" onclick="continueAddingPayments()" class="btn btn-info w-100">
                        <i class="fa fa-plus"></i> Continue Adding Payments
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Print Invoice Section -->
<div id="printInvoiceSection" <?= ($totalPaid >= $final_total_amount && $final_total_amount > 0) ? 'style="display: block;"' : 'style="display: none;"' ?>>
    <div class="row mt-3">
        <div class="col-md-12">
            <div class="alert alert-info">
                <h5><i class="fa fa-info-circle"></i> Invoice Confirmed</h5>
                <p>This invoice has been confirmed and is now locked. You can print the invoice or make additional payments if needed.</p>
            </div>
        </div>
        <div class="col-md-6">
            <a class="btn btn-primary w-100 print-btn" target="_blank" href="<?= base_url('print-job-mini/' . urlencode(base64_encode($jobData->job_id))) ?>">
                <i class="fa fa-print"></i> Mini Invoice
            </a>
        </div>
        <div class="col-md-6">
            <a class="btn btn-primary w-100 print-btn" target="_blank" href="<?= base_url('print-job/' . urlencode(base64_encode($jobData->job_id))) ?>">
                <i class="fa fa-print"></i> A4 Invoice
            </a>
        </div>
    </div>
</div>

<!-- Job Payment History table temporarily removed -->


