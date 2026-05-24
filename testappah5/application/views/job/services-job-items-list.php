<?php
// Load auth helper for permission checking
$CI =& get_instance();
$CI->load->helper('auth');

$jobCurrentStatus = $jobData->status;
// Initialize total counters
$serviceCost = 0;
$spareCost = 0;
$otherCost = 0;
$subTotal = 0;
$totalCost = 0;
$receivedAmount = 0;
$balanceAmount = 0;
                foreach ($jobItems as $item) {
                    if ($item->item_type == 'service') {
                        $serviceCost += $item->total_price; // Add service cost
                    } elseif ($item->item_type == 'product') {
                        $spareCost += $item->total_price; // Add spare part cost
                    } elseif ($item->item_type == 'labour' || $item->item_type == 'other' || $item->item_type == 'outsource') {
                        $otherCost += $item->total_price;
                    }
                }

// Calculate the total cost
$subTotal = $serviceCost + $spareCost + $otherCost;
?>

<div class="container">
    <!-- Scrollable Table Container -->
    <div class="table-container">
        <table id="table2" class="table table-striped table-sm table-hover table-responsive">
            <thead class="table-warning">
                <tr>
                    <th class="text-center">##</th>
                    <th class="text-center">Description</th>
                    <th class="text-end">Qty</th>
                    <th class="text-end">Price</th>
                    <th class="text-end">Discount</th>
                    <th class="text-end">Amount</th>
                    <th class="text-end"><i class="fa fa-trash"></i></th>
                </tr>
            </thead>
            <tbody>
                <?php
                $no = 1;
                $confirm_item = 0;
                $none_confirm_item = 0;
                $totalItemDiscuntedAmount = 0;
                foreach ($jobItems as $item) {
                    if ($item->item_type == 'service') {
                        $item_name = $item->item_name;
                    } elseif ($item->item_type == 'sparepart') {
                        $item_name = $item->package_name;
                    } elseif ($item->item_type == 'product') {
                        $item_name = $item->product_name.'<br><small>'.$item->sku.'</small>';
                    } elseif ($item->item_type == 'labour') {
                        $item_name = $item->description;
                    } elseif ($item->item_type == 'other') {
                        $item_name = $item->description;
                    } elseif ($item->item_type == 'outsource') {
                        $item_name = $item->description . ' <span class="badge bg-info">Outsource</span>';
                    } else {
                        $item_name = 'Unknown Item Type';
                    }

                    $package_id = 0;
                    if ($item->package_id > 0) {
                        $package_id = $item->package_id;
                    }
                ?>
                    <tr>
                        <td><?= $no++; ?></td>
                        <td><?= $item_name ?></td>
                        <td class="text-end"><?= $item->quantity ?></td>
                        <td class="text-end"><?= number_format($item->price, 2) ?></td>
                        <?php
                        $display_value = '0%';
                        $discount = 0;

                        if ($item->discount_percent > 0) {
                            $discount = ($item->price * $item->discount_percent / 100) * $item->quantity;
                            $display_value = $item->discount_percent . '%';
                        } elseif ($item->discount_amount > 0) {
                            $discount = $item->discount_amount * $item->quantity;
                            $display_value = number_format($item->discount_amount, 2);
                        }

                        $totalItemDiscuntedAmount += $discount;
                        ?>

                        <td class="text-end" style="cursor: <?= ((($receivedAmount >= $final_total_amount && $final_total_amount > 0 && $jobData->confirmed_by > 0) && !$this->session->userdata('filter_job_status_is_manager')) || !has_permission('job.edit')) ? 'not-allowed' : 'pointer' ?>;"
                            onclick="<?= ((($receivedAmount >= $final_total_amount && $final_total_amount > 0 && $jobData->confirmed_by > 0) && !$this->session->userdata('filter_job_status_is_manager')) || !has_permission('job.edit')) ? 'return false;' : 'openDiscountModal(' . $item->id . ', \'' . htmlspecialchars($item_name, ENT_QUOTES) . '\', ' . $item->discount_percent . ', ' . $item->discount_amount . ')' ?>">
                            <?= $display_value ?>
                        </td>

                        <td class="text-end"><?= number_format($item->total_price, 2) ?></td>
                        <td class="text-end">
                            <?php if ((($jobCurrentStatus < 4  && $item->confirmed_by == 0) || ($jobCurrentStatus == 4 && $this->session->userdata('filter_job_status_is_manager'))) && has_permission('job.edit')) { ?>
                                <button class="btn btn-danger btn-sm" id="delete_<?= $item->id ?>" onclick="deleteJobItem(<?= $item->id ?>,<?= $package_id ?>,<?= $item->service_job_id ?>)">
                                    <i class="fa fa-trash"></i>
                                </button>
                            <?php } ?>

                            <?php if ($jobCurrentStatus == 4 && $item->confirmed_by > 0 && has_permission('job.approve')) {
                                $confirm_item++;
                            ?>
                                <button class="btn btn-warning btn-sm" id="confirm_<?= $item->id ?>" onclick="confirmJobItem(<?= $item->id ?>, 0)">
                                    <i class="fa fa-undo"></i>
                                </button>
                            <?php } ?>

                            <?php if ($jobCurrentStatus == 4 && $item->confirmed_by == 0 && has_permission('job.approve')) {
                                $none_confirm_item++;
                            ?>
                                <button class="btn btn-info btn-sm" id="confirm_<?= $item->id ?>" onclick="confirmJobItem(<?= $item->id ?>, 1)">
                                    <i class="fa fa-check"></i>
                                </button>
                            <?php } ?>

                        </td>
                    </tr>
                <?php } ?>
                <tr>
                    <td colspan="4" class="text-end"><strong>Total Discount:</strong></td>
                    <td class="text-end text-danger"><strong><?= number_format($totalItemDiscuntedAmount, 2) ?></strong></td>
                    <td></td>
                    <td></td>
                </tr>

            </tbody>
        </table>
    </div>


    <?php
    $final_total_amount = $subTotal;
    $bill_discounted_amount = 0;

    $job_id = isset($jobData->job_id) ? $jobData->job_id : 0;
    $job_cost = isset($jobData->job_cost) ? $jobData->job_cost : 0;
    $job_discount_percent = isset($jobData->discount_percent) ? $jobData->discount_percent : 0;
    $job_discount_amount = isset($jobData->discount_amount) ? $jobData->discount_amount : 0;
    $job_final_bill_amount = isset($jobData->final_bill_amount) ? $jobData->final_bill_amount : 0;
    $job_total_paid = isset($jobData->total_paid) ? $jobData->total_paid : 0;
    $job_total_balance = isset($jobData->total_balance) ? $jobData->total_balance : 0;


    if ($job_discount_percent > 0) {
        $bill_discounted_amount = ($subTotal * $job_discount_percent) / 100;
        $final_total_amount = $subTotal - $bill_discounted_amount;
    }

    if ($job_discount_amount > 0) {
        $final_total_amount = $subTotal - $job_discount_amount;
    }

    $receivedAmount = isset($jobPayment->total_payment) ? $jobPayment->total_payment : 0;


    $balanceAmount = $final_total_amount - $receivedAmount;

    //update the job data with the calculated values
    if ($job_cost != $subTotal || $job_final_bill_amount != $final_total_amount || $job_total_paid != $receivedAmount || $job_total_balance != $balanceAmount) {
        $this->db->where('job_id', $job_id);
        $this->db->update('services_job', [
            'job_cost' => $subTotal,
            'final_bill_amount' => $final_total_amount,
            'total_paid' => $receivedAmount,
            'total_balance' => $balanceAmount
        ]);
    }

    ?>
    <!-- Bottom Section for additional features -->
    <div class="bottom-section">


        <div class="row">
            <div class="col-md-7" style="">
                <table id="table2" class="table table-striped table-responsive table-sm">
                    <tbody>
                        <tr>
                            <td class="text-left f3">Service Cost :</td>
                            <td class="text-end f3"><?= number_format($serviceCost, 2) ?> &nbsp;&nbsp; LKR</td>
                        </tr>
                        <tr>
                            <td class="text-left f3">Spare Cost :</td>
                            <td class="text-end f3"><?= number_format($spareCost, 2) ?> &nbsp;&nbsp; LKR</td>
                        </tr>
                        <tr>
                            <td class="text-left f3">Other/Labour Cost :</td>
                            <td class="text-end f3"><?= number_format($otherCost, 2) ?> &nbsp;&nbsp; LKR</td>
                        </tr>
                        <tr>
                            <td class="text-left f3">Item Discounts Total:</td>
                            <td class="text-end f3 text-danger"><?= number_format($totalItemDiscuntedAmount, 2) ?> &nbsp;&nbsp; LKR</td>
                        </tr>
                        <tr>
                            <td class="text-left f3">Sub Total :</td>
                            <td class="text-end f3"><?= number_format($subTotal, 2) ?> &nbsp;&nbsp; LKR</td>
                        </tr>


                        <?php if (true) { ?>
                            <tr>
                                <td class="text-left f3">Invoice Discount Amount <?= $job_discount_percent ?>%:</td>
                                <?php if($job_discount_amount>0){ ?>
                                <td class="text-end f3"><?= number_format($job_discount_amount, 2) ?> &nbsp;&nbsp; LKR</td>
                                <?php }else{ ?>
                                    <td class="text-end f3"><?= number_format($bill_discounted_amount, 2) ?> &nbsp;&nbsp; LKR</td>
                                    <?php } ?>
                            </tr>
                            <tr>
                                <td class="text-left f3">Total:</td>
                                <td class="text-end f3"><?= number_format($final_total_amount, 2) ?> &nbsp;&nbsp; LKR</td>
                            </tr>
                            <tr>
                                <td class="text-left f3">Paid:</td>
                                <td class="text-end f3"><?= number_format($receivedAmount, 2) ?> &nbsp;&nbsp; LKR</td>
                            </tr>
                            <tr>
                                <td class="text-left f3">Balance:</td>
                                <td class="text-end f3"><?= number_format(max($final_total_amount - $receivedAmount, 0), 2) ?> &nbsp;&nbsp; LKR</td>
                            </tr>
                            
                        <?php } ?>
                    </tbody>
                </table>


                <?php if ($this->session->userdata('filter_job_status_is_manager')) { ?>
                    <div class="row mt-3">
                        <div class="col-md-4">
                            <label for="discountRate" class="f4">Discount %:</label>
                            <input type="number" value="<?= $job_discount_percent ?>" id="discountRate" class="form-control" placeholder="0.00" min="0" max="100" step="0.01" style="width: 100%;" <?= (($receivedAmount >= $final_total_amount && $final_total_amount > 0 && $jobData->confirmed_by > 0) && !$this->session->userdata('filter_job_status_is_manager')) ? 'disabled' : '' ?>>
                        </div>
                        <div class="col-md-5">
                            <label for="discountAmount" class="f4">Discount Amount:</label>
                            <input type="number" value="<?= $job_discount_amount ?>" id="discountAmount" class="form-control" placeholder="0.00" min="0" step="0.01" style="width: 100%;" <?= (($receivedAmount >= $final_total_amount && $final_total_amount > 0 && $jobData->confirmed_by > 0) && !$this->session->userdata('filter_job_status_is_manager')) ? 'disabled' : '' ?>>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-primary mt-4 w-100" id="addDiscountButton" onclick="addDiscount()" <?= (($receivedAmount >= $final_total_amount && $final_total_amount > 0 && $jobData->confirmed_by > 0) && !$this->session->userdata('filter_job_status_is_manager')) ? 'disabled' : '' ?>>Apply</button>
                        </div>
                    </div>
                <?php } ?>
            </div> <!-- end of first column -->


            <div class="col-md-5" style="border-left: 2px solid #044756">
    <?php if ($this->session->userdata('filter_job_status_is_invoices') || $receivedAmount > 0) { ?>

        <!-- Payment Section -->
        <div id="paymentSection" <?= ($receivedAmount >= $final_total_amount && $final_total_amount > 0 && $jobData->confirmed_by > 0) ? 'style="display: none;"' : '' ?>>
            <!-- Payment Method & Paid Amount in same row -->
            <div class="row mt-3">
                <div class="col-md-6">
                    <label for="paymentMethod" class="f4">Payment Method:</label>
                    <select id="paymentMethod" class="form-select" onchange="showBankChequeFields()">
                        <option value="" disabled selected>Select Payment Method</option>
                        <option value="cash">Cash Payment</option>
                        <option value="card">Card Payment</option>
                        <option value="credit">Credit Payment</option>
                        <option value="cheque">Cheque Payment</option>
                        <option value="bank_transfer">Bank Transfer</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="paidAmount" class="f4">Paid Amount:</label>
                    <input type="number" id="paidAmount" class="form-control" placeholder="Enter Paid Amount" value="<?= $balanceAmount ?>">
                </div>
            </div>

            <!-- Bank & Cheque fields (same row) -->
            <div class="row" id="bankChequeFields" style="display: none;">
                <div class="col-md-12 mt-3">
                    <label for="bankName" class="f4">Bank Name:</label>
                    <select id="bankName" class="form-select">
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
                <div class="col-md-12 mt-3">
                    <label for="chequeNumber" class="f4">Cheque No/Transaction ID:</label>
                    <input type="text" id="chequeNumber" class="form-control" placeholder="Transaction ID / Cheque No">
                </div>
                <div class="col-md-12 mt-3" id="chequeDateField">
                    <label for="chequeDate" class="f4">Cheque Date:</label>
                    <input type="date" id="chequeDate" class="form-control">
                </div>
            </div>

            <!-- For credit payments -->
            <div class="row" id="creditFields" style="display: none;">
                <div class="col-md-7 mt-3">
                    <label for="creditPersonName" class="f4">Credit Person / Company Name:</label>
                    <input type="text" id="creditPersonName" class="form-control" placeholder="Enter name">
                </div>
                <div class="col-md-4 mt-3">
                    <label for="creditPersonPhone" class="f4">Phone Number:</label>
                    <input type="text" id="creditPersonPhone" class="form-control" placeholder="Enter phone">
                </div>
                <div class="col-md-12 mt-3">
                    <label for="creditRemark" class="f4">Credit Remark (Optional):</label>
                    <textarea id="creditRemark" class="form-control" placeholder="Enter any remarks about this credit payment" rows="2"></textarea>
                </div>
                <div class="col-md-12 mt-3">
                    <label for="creditPayDate" class="f4">Expected Payment Date (Required):</label>
                    <input type="date" id="creditPayDate" class="form-control" min="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="col-md-12 mt-2">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="creditSameAsCustomer">
                        <label class="form-check-label" for="creditSameAsCustomer">Same as customer</label>
                    </div>
                </div>
            </div>

            <!-- Payment buttons -->
            <div class="row mt-3">
                <div class="col-md-6">
                    <button class="btn btn-success w-100" onclick="makePayment()">Make Payment</button>
                </div>
                <div class="col-md-6">
                    <button class="btn btn-danger w-100" onclick="location.reload()">Cancel</button>
                </div>
            </div>
            <div class="row mt-2">
                
            </div>
            

        </div>

        <!-- Payment History Wrapper - This will be replaced dynamically -->
        <div id="paymentHistoryWrapper">
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
                                    <span class="text-success">Rs. <?= number_format($receivedAmount, 2) ?></span>
                                </div>
                                <div class="col-md-3">
                                    <strong>Balance:</strong><br>
                                    <span class="text-<?= ($balanceAmount > 0) ? 'danger' : 'success' ?>">Rs. <?= number_format($balanceAmount, 2) ?></span>
                                </div>
                                <div class="col-md-3">
                                    <strong>Status:</strong><br>
                                    <span class="badge bg-<?= ($balanceAmount > 0) ? 'warning' : 'success' ?>">
                                        <?= ($balanceAmount > 0) ? 'Pending' : 'Complete' ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Confirm Invoice Section -->
            <div id="confirmInvoiceSection" <?= ($receivedAmount >= $final_total_amount && $final_total_amount > 0 && $jobData->confirmed_by == 0) ? '' : 'style="display: none;"' ?>>
                <div class="row mt-3">
                    <div class="col-md-12">
                        <div class="alert alert-success">
                            <h5><i class="fa fa-check-circle"></i> Payments Complete</h5>
                            <p>Total: Rs. <?= number_format($final_total_amount, 2) ?> | Paid: Rs. <?= number_format($receivedAmount, 2) ?>. Please confirm payments to finalize the invoice and lock discounts.</p>
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
            <div id="printInvoiceSection" <?= ($receivedAmount > 0) ? 'style="display: block;"' : 'style="display: none;"' ?>>
                <div class="row mt-3">
                    <div class="col-md-12">
                        <div class="alert alert-info">
                            <h5><i class="fa fa-info-circle"></i> Invoice Available</h5>
                            <p>You can print the invoice for payment proof. <?= ($receivedAmount >= $final_total_amount && $final_total_amount > 0) ? 'This invoice has been confirmed and is now locked.' : 'Additional payments can be made if needed.' ?></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <a class="btn btn-primary w-100 print-btn" target="_blank" href="<?= base_url('print-job-mini/' . urlencode(base64_encode($job_id))) ?>">
                            <i class="fa fa-print"></i> Mini Invoice
                        </a>
                    </div>
                    <div class="col-md-6">
                        <a class="btn btn-primary w-100 print-btn" target="_blank" href="<?= base_url('print-job/' . urlencode(base64_encode($job_id))) ?>">
                            <i class="fa fa-print"></i> A4 Invoice
                        </a>
                    </div>
                </div>
            </div>

            <!-- Job Payment History table temporarily removed -->
        </div>

    <?php } ?>



    <!-- Confirm Job -->
    <?php if ($jobData->confirmed_by == 0 && $jobData->status == 4) { ?>
        <div class="row mt-3">
            <div class="col-md-12">
                <button type="button" onclick="showConfirmAccessModal()" class="btn btn-success w-100">
                    <i class="ri-check-double-line me-2"></i> Confirm & Complete This Job
                </button>
            </div>
        </div>
    <?php } ?>

    <!-- Add Print Access (hidden) -->
    <?php if ($jobData->print_access_by == 0 && $jobData->status > 4) { ?>
        <div class="row mt-3 d-none">
            <div class="col-md-12">
                <button type="button" onclick="showPrintAccessModal()" class="btn btn-success w-100">
                    <i class="ri-check-double-line me-2"></i> Add Print Access
                </button>
            </div>
        </div>
    <?php } ?>

    <!-- Change Job Status -->
    <?php if ($receivedAmount == 0 ) { ?>
    <?php if ($jobData->status < 4 || $this->session->userdata('role') == 1) { ?>
        <div class="row mt-4">
            
        </div>
    <?php }  } ?>
</div>

        </div>
    </div>




    <script>

        function makePayment() {
            var paymentMethod = document.getElementById("paymentMethod") ? document.getElementById("paymentMethod").value : '';
            var paidAmount = document.getElementById("paidAmount") ? document.getElementById("paidAmount").value : '';
            var bankName = document.getElementById("bankName") ? document.getElementById("bankName").value : '';
            var chequeNumber = document.getElementById("chequeNumber") ? document.getElementById("chequeNumber").value : '';
            var chequeDate = document.getElementById("chequeDate") ? document.getElementById("chequeDate").value : '';

            var creditPersonName = document.getElementById("creditPersonName") ? document.getElementById("creditPersonName").value : '';
            var creditPersonPhone = document.getElementById("creditPersonPhone") ? document.getElementById("creditPersonPhone").value : '';
            var creditRemark = document.getElementById("creditRemark") ? document.getElementById("creditRemark").value : '';
            var creditPayDate = document.getElementById("creditPayDate") ? document.getElementById("creditPayDate").value : '';

            var jobId = $('#job_id').val() || document.getElementById('invoice_job_id')?.value;

            // Perform validation
            if (paymentMethod === "") {
                Toastify({
                    text: 'Please select a payment method.',
                    className: "toastify-error",
                    duration: 5000,
                    close: true,
                    gravity: "top",
                    position: "right"
                }).showToast();
                return;
            }
            
            if ((paymentMethod === "cheque" || paymentMethod === "bank_transfer" || paymentMethod === "card") && (bankName === "" || chequeNumber === "")) {
                Toastify({
                    text: 'Please enter bank name and cheque/card number.',
                    className: "toastify-error",
                    duration: 5000,
                    close: true,
                    gravity: "top",
                    position: "right"
                }).showToast();
                return;
            }

            if (paymentMethod === "credit") {
                if (!creditPersonName || !creditPayDate) {
                    Toastify({
                        text: 'Credit Person and Expected Payment Date are required for credit payments.',
                        className: "toastify-error",
                        duration: 5000,
                        close: true,
                        gravity: "top",
                        position: "right"
                    }).showToast();
                    return;
                }
            }

            // Show confirmation dialog
            Swal.fire({
                title: 'Confirm Payment',
                text: `Are you sure you want to make a payment of LKR ${paidAmount} using ${paymentMethod}?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, proceed!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // If user confirms, proceed with AJAX request
                    $.ajax({
                        url: "<?php echo site_url('job/addPaymentToJob') ?>",
                        type: "POST",
                        data: {
                            jobId: jobId,
                            paymentMethod: paymentMethod,
                            paidAmount: paidAmount,
                            bankName: bankName,
                            chequeNumber: chequeNumber,
                            chequeDate: chequeDate,
                            creditPersonName: creditPersonName,
                            creditPersonPhone: creditPersonPhone,
                            creditRemark: creditRemark,
                            creditPayDate: creditPayDate
                        },
                        success: function(response) {
                            var res = JSON.parse(response);
                            if (res.status === 'success') {
                                Toastify({
                                    text: res.message,
                                    className: "toastify-success",
                                    duration: 5000,
                                    close: true,
                                    gravity: "top",
                                    position: "right"
                                }).showToast();
                                
                                // Load updated payment history (similar to how items work)
                                loadPaymentHistory();
                                
                                // Clear the payment form
                                clearPaymentForm();
                            } else {
                                Toastify({
                                    text: res.message,
                                    className: "toastify-error",
                                    duration: 5000,
                                    close: true,
                                    gravity: "top",
                                    position: "right"
                                }).showToast();
                            }
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            Toastify({
                                text: 'An error occurred while processing the payment.',
                                className: "toastify-error",
                                duration: 5000,
                                close: true,
                                gravity: "top",
                                position: "right"
                            }).showToast();
                        }
                    });
                } else {
                    // If user cancels the confirmation
                    Toastify({
                        text: 'Payment was not processed.',
                        className: "toastify-info",
                        duration: 5000,
                        close: true,
                        gravity: "top",
                        position: "right"
                    }).showToast();
                }
            });
        }

        function checkPaymentCompletion(jobId) {
            // Check if payment is complete by getting current balance
            $.ajax({
                url: "<?php echo site_url('job/getJobBalance') ?>",
                type: "POST",
                data: { jobId: jobId },
                success: function(response) {
                    var res = JSON.parse(response);
                    if (res.balance <= 0 && res.finalAmount > 0) {
                        // Payment is complete and invoice has amount, show confirm invoice button
                        showConfirmInvoiceButton();
                        // Don't call loadPaymentHistory() here as it will hide the confirm button
                    } else {
                        // Payment not complete, refresh payment history
                        loadPaymentHistory();
                    }
                }
            });
        }

        function showConfirmInvoiceButton() {
            // Show the confirm invoice button
            $('#confirmInvoiceSection').show();
            

            
            Toastify({
                text: 'Payment complete! Please confirm the invoice to proceed.',
                className: "toastify-success",
                duration: 5000,
                close: true,
                gravity: "top",
                position: "right"
            }).showToast();
        }

        function continueAddingPayments() {
            // Hide the confirm invoice section
            $('#confirmInvoiceSection').hide();
            

            
            Toastify({
                text: 'You can continue adding more payments. The invoice will remain editable until confirmed.',
                className: "toastify-info",
                duration: 5000,
                close: true,
                gravity: "top",
                position: "right"
            }).showToast();
        }

        function confirmInvoice() {
            var jobId = $('#job_id').val() || document.getElementById('invoice_job_id')?.value;
            
            // Show confirmation dialog
            Swal.fire({
                title: 'Confirm Payments',
                text: 'Are you sure you want to confirm payments? This will finalize and lock discounts, and enable printing.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, confirm',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Save confirmation to database
                    $.ajax({
                        url: "<?php echo site_url('job/confirmInvoice') ?>",
                        type: "POST",
                        data: {
                            jobId: jobId
                        },
                        success: function(response) {
                            var res = JSON.parse(response);
                            if (res.status === 'success') {
                                // Lock the invoice (disable all fields)
                                lockInvoice();
                                
                                // Hide confirm button and show print buttons
                                $('#confirmInvoiceSection').hide();
                                $('#printInvoiceSection').show();
                                
                                // Refresh payment history to reflect the new confirmed state
                                loadPaymentHistory();
                                
                                Toastify({
                                    text: res.message || 'Payments confirmed. Invoice finalized.',
                                    className: "toastify-success",
                                    duration: 5000,
                                    close: true,
                                    gravity: "top",
                                    position: "right"
                                }).showToast();
                            } else {
                                Toastify({
                                    text: res.message,
                                    className: "toastify-error",
                                    duration: 5000,
                                    close: true,
                                    gravity: "top",
                                    position: "right"
                                }).showToast();
                            }
                        },
                        error: function() {
                            Toastify({
                                text: 'An error occurred while confirming the invoice.',
                                className: "toastify-error",
                                duration: 5000,
                                close: true,
                                gravity: "top",
                                position: "right"
                            }).showToast();
                        }
                    });
                }
            });
        }

        function lockInvoice() {
            // Disable all input fields and buttons (except print buttons)
            $('input, select, textarea').prop('disabled', true);
            $('button:not(.print-btn)').prop('disabled', true);
            
            // Ensure print buttons remain enabled
            $('.print-btn').prop('disabled', false);
            
            // Add locked class to prevent any interactions
            $('.bottom-section').addClass('invoice-locked');
        }

        function showConfirmAccessModal() {
            var jobId = $('#job_id').val();
            var confirmItems = "<?php echo $confirm_item; ?>";
            var noneConfirmItems = "<?php echo $none_confirm_item; ?>";

            if (confirmItems == 0 && noneConfirmItems == 0) {
                Toastify({
                    text: 'Please add items to make invoice.',
                    className: "toastify-error",
                    duration: 5000,
                    close: true,
                    gravity: "top",
                    position: "right"
                }).showToast();
                return;
            }

            if (noneConfirmItems > 0) {
                Toastify({
                    text: 'Please confirm all items before making invoice.',
                    className: "toastify-error",
                    duration: 5000,
                    close: true,
                    gravity: "top",
                    position: "right"
                }).showToast();
                return;
            }

            Swal.fire({
                title: 'Confirm & Make Invoice',
                text: 'Are you sure you want to make a invoice:',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Save',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // If user confirms, proceed with AJAX request
                    var print_access_by = $('#print_access_by').val();
                    $.ajax({
                        url: "<?php echo site_url('job/confirmJob') ?>",
                        type: "POST",
                        data: {
                            jobId: jobId,
                            print_access_by: print_access_by
                        },
                        success: function(response) {
                            var res = JSON.parse(response);
                            if (res.status === 'success') {
                                Toastify({
                                    text: res.message,
                                    className: "toastify-success",
                                    duration: 5000,
                                    close: true,
                                    gravity: "top",
                                    position: "right"
                                }).showToast();
                                loadJobItems();
                                closeAddItemModal();
                                loadServicesJobs();
                                
                                // Refresh payment history to reflect any changes in totals
                                loadPaymentHistory();

                            } else {
                                Toastify({
                                    text: res.message,
                                    className: "toastify-error",
                                    duration: 5000,
                                    close: true,
                                    gravity: "top",
                                    position: "right"
                                }).showToast();
                            }
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            Toastify({
                                text: 'An error occurred while processing the payment.',
                                className: "toastify-error",
                                duration: 5000,
                                close: true,
                                gravity: "top",
                                position: "right"
                            }).showToast();
                        }
                    });
                }
            })
        }

        function showPrintAccessModal() {
            var jobId = $('#job_id').val();

            Swal.fire({
                title: 'Add Print Access',
                text: 'Are you sure you want to add print access:',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Save',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // If user confirms, proceed with AJAX request
                    var print_access_by = $('#print_access_by').val();
                    $.ajax({
                        url: "<?php echo site_url('job/addPrintAccess') ?>",
                        type: "POST",
                        data: {
                            jobId: jobId,
                            print_access_by: print_access_by
                        },
                        success: function(response) {
                            var res = JSON.parse(response);
                            if (res.status === 'success') {
                                Toastify({
                                    text: res.message,
                                    className: "toastify-success",
                                    duration: 5000,
                                    close: true,
                                    gravity: "top",
                                    position: "right"
                                }).showToast();
                                loadJobItems();
                                loadServicesJobs();
                                
                                // Refresh payment history to reflect any changes in totals
                                loadPaymentHistory();
                            } else {
                                Toastify({
                                    text: res.message,
                                    className: "toastify-error",
                                    duration: 5000,
                                    close: true,
                                    gravity: "top",
                                    position: "right"
                                }).showToast();
                            }
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            Toastify({
                                text: 'An error occurred while processing the payment.',
                                className: "toastify-error",
                                duration: 5000,
                                close: true,
                                gravity: "top",
                                position: "right"
                            }).showToast();
                        }
                    });
                }
            })
        }


        function changeJobStatus() {
            var jobId = $('#job_id').val();
            var jobStatus = document.getElementById("job_new_status").value;
            //get job status text
            var jobStatusText = document.getElementById("job_new_status").options[document.getElementById("job_new_status").selectedIndex].text;

            Swal.fire({
                title: 'Confirm Status Change',
                text: `Are you sure you want to change the status to ${jobStatusText}?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, proceed!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // If user confirms, proceed with AJAX request
                    $.ajax({
                        url: "<?php echo site_url('job/updateJobStatus') ?>",
                        type: "POST",
                        data: {
                            jobId: jobId,
                            jobStatus: jobStatus
                        },
                        success: function(response) {
                            var res = JSON.parse(response);
                            if (res.status === 'success') {
                                Toastify({
                                    text: res.message,
                                    className: "toastify-success",
                                    duration: 5000,
                                    close: true,
                                    gravity: "top",
                                    position: "right"
                                }).showToast();
                                closeAddItemModal();
                                loadServicesJobs();
                            } else {
                                Toastify({
                                    text: res.message,
                                    className: "toastify-error",
                                    duration: 5000,
                                    close: true,
                                    gravity: "top",
                                    position: "right"
                                }).showToast();
                            }
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            Toastify({
                                text: 'An error occurred while processing the payment.',
                                className: "toastify-error",
                                duration: 5000,
                                close: true,
                                gravity: "top",
                                position: "right"
                            }).showToast();
                        }
                    });
                } else {
                    // If user cancels the confirmation
                    Toastify({
                        text: 'Status was not changed.',
                        className: "toastify-info",
                        duration: 5000,
                        close: true,
                        gravity: "top",
                        position: "right"
                    }).showToast();
                }
            });
        }

        function confirmJobItem(jobItemId, status) {

            if (status > 0) {
                var buttonId = 'confirm_' + jobItemId;
              //  var deleteButtonId = 'delete_' + jobItemId;
                //disable button
                document.getElementById(buttonId).disabled = true;
                //document.getElementById(deleteButtonId).disabled = true;
            } else {

            }

            $.ajax({
                url: "<?php echo site_url('job/confirmJobItem') ?>",
                type: "POST",
                data: {
                    jobItemId: jobItemId,
                    status: status
                },
                success: function(response) {
                    var res = JSON.parse(response);
                    if (res.status === 'success') {
                        loadJobItems();
                        
                        // Refresh payment history to reflect any changes in totals
                        loadPaymentHistory();
                        
                        Toastify({
                            text: res.message,
                            className: "toastify-success",
                            duration: 5000,
                            close: true,
                            gravity: "top",
                            position: "right"
                        }).showToast();
                        // closeAddItemModal();
                        // loadServicesJobs();
                    } else {
                        Toastify({
                            text: res.message,
                            className: "toastify-error",
                            duration: 5000,
                            close: true,
                            gravity: "top",
                            position: "right"
                        }).showToast();
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    Toastify({
                        text: 'An error occurred while processing the payment.',
                        className: "toastify-error",
                        duration: 5000,
                        close: true,
                        gravity: "top",
                        position: "right"
                    }).showToast();
                }
            });
        }

        function showBankChequeFields() {
            var paymentMethod = document.getElementById("paymentMethod").value;
            var bankChequeFields = document.getElementById("bankChequeFields");
            var balanceAmount = "<?= $balanceAmount ?>";
            document.getElementById("paidAmount").value = balanceAmount;
            

            if (paymentMethod === "cheque" || paymentMethod === "bank_transfer" || paymentMethod === "card") {
                bankChequeFields.style.display = "block";
            } else {
                bankChequeFields.style.display = "none";
                document.getElementById("bankName").value = ""; // Clear the bank name field (select)
                document.getElementById("chequeNumber").value = ""; // Clear the cheque number field
            }

            if (paymentMethod === "cheque") {
                chequeDateField.style.display = "block";
            } else {
                chequeDateField.style.display = "none";
                document.getElementById("chequeDate").value = "";
            }


            if (paymentMethod === "credit") {
                creditFields.style.display = "block";
                document.getElementById("paidAmount").value = 0
            } else {
                creditFields.style.display = "none";
                document.getElementById("creditPersonName").value = "";
                var cp = document.getElementById("creditPersonPhone"); if (cp) cp.value = "";
                var cs = document.getElementById("creditSameAsCustomer"); if (cs) cs.checked = false;
            }

        }


        function addDiscount() {
            // Check if invoice is fully paid AND confirmed (received amount >= final amount and final amount > 0 and confirmed)
            var receivedAmount = <?= $receivedAmount ?>;
            var finalTotalAmount = <?= $final_total_amount ?>;
            var isConfirmed = <?= $jobData->confirmed_by > 0 ? 'true' : 'false' ?>;
            if (receivedAmount >= finalTotalAmount && finalTotalAmount > 0 && isConfirmed) {
                Toastify({
                    text: 'Cannot modify discounts after invoice completion.',
                    className: "toastify-error",
                    duration: 5000,
                    close: true,
                    gravity: "top",
                    position: "right"
                }).showToast();
                return;
            }

            var discountRate = document.getElementById("discountRate").value;
            var discountAmount = document.getElementById("discountAmount").value;
            var jobId = $('#job_id').val() || document.getElementById('invoice_job_id')?.value || <?= $job_id ?>;

            if (!jobId) {
                Toastify({
                    text: 'Job ID is missing. Please refresh the page and try again.',
                    className: "toastify-error",
                    duration: 5000,
                    close: true,
                    gravity: "top",
                    position: "right"
                }).showToast();
                return;
            }

            if (discountRate === "" && discountAmount === "") {
                Toastify({
                    text: 'Please enter a discount rate or amount.',
                    className: "toastify-error",
                    duration: 5000,
                    close: true,
                    gravity: "top",
                    position: "right"
                }).showToast();
                return;
            }

            if (discountRate > 0 && discountAmount > 0) {
                Toastify({
                    text: 'Please enter either a discount rate or amount, not both.',
                    className: "toastify-error",
                    duration: 5000,
                    close: true,
                    gravity: "top",
                    position: "right"
                }).showToast();
                return;
            }

            if (discountRate > 0) {
                discountAmount = null;
            } else if (discountAmount > 0) {
                discountRate = null;
            }

            $.ajax({
                url: "<?php echo site_url('job/addDiscountToJob') ?>",
                type: "POST",
                data: {
                    jobId: jobId,
                    discountRate: discountRate,
                    discountAmount: discountAmount
                },
                success: function(response) {

                    var res = JSON.parse(response);

                    if (res.status === 'success') {
                        Toastify({
                            text: res.message,
                            className: "toastify-success",
                            duration: 5000,
                            close: true,
                            gravity: "top",
                            position: "right"
                        }).showToast();

                        // Reload job items to update totals
                        if (typeof loadJobItems === 'function') {
                            loadJobItems();
                        }
                        if (typeof loadInvoiceJobItems === 'function') {
                            loadInvoiceJobItems();
                        }
                        
                        // Refresh payment history to reflect any changes in totals
                        loadPaymentHistory();
                    } else {
                        Toastify({
                            text: res.message,
                            className: "toastify-error",
                            duration: 5000,
                            close: true,
                            gravity: "top",
                            position: "right"
                        }).showToast();
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    Toastify({
                        text: 'An error occurred while processing the discount.',
                        className: "toastify-error",
                        duration: 5000,
                        close: true,
                        gravity: "top",
                        position: "right"
                    }).showToast();
                }
            });

        }



        function openDiscountModal(itemId, itemName, currentDiscount, discount_amount) {
            // Check if invoice is fully paid AND confirmed (received amount >= final amount and final amount > 0 and confirmed)
            var receivedAmount = <?= $receivedAmount ?>;
            var finalTotalAmount = <?= $final_total_amount ?>;
            var isConfirmed = <?= $jobData->confirmed_by > 0 ? 'true' : 'false' ?>;
            if (receivedAmount >= finalTotalAmount && finalTotalAmount > 0 && isConfirmed) {
                Toastify({
                    text: 'Cannot modify item discounts after invoice completion.',
                    className: "toastify-error",
                    duration: 5000,
                    close: true,
                    gravity: "top",
                    position: "right"
                }).showToast();
                return;
            }

            // Set all the values
            $('#editItemId').val(itemId);
            $('#editItemName').val(itemName);

            if (currentDiscount > 0) {
                $('#currentDiscount').val(currentDiscount + '%');
            }

            if (discount_amount > 0) {
                $('#currentDiscount').val(discount_amount + ' LKR');
            }

            $('#newDiscount').val(currentDiscount);
            $('#newDiscountAmount').val(discount_amount);
            // Reset error messages
            $('#discountError').hide();
            $('#discountAmountError').hide();

            // Show the modal
            $('#discountModal').modal('show');

            // Focus after modal is fully shown
            $('#discountModal').on('shown.bs.modal', function() {
                $('#newDiscount').trigger('focus').select();
            });
        }

        function checkInitialPaymentStatus() {
            // Check if payments are already complete on page load
            var receivedAmount = <?= $receivedAmount ?>;
            var finalTotalAmount = <?= $final_total_amount ?>;
            var isConfirmed = <?= $jobData->confirmed_by > 0 ? 'true' : 'false' ?>;
            
            if (receivedAmount > 0) {
                // Show print section if there are any payments (partial or full)
                $('#printInvoiceSection').show();
                // If fully paid and not confirmed, show confirm prompt
                if (receivedAmount >= finalTotalAmount && finalTotalAmount > 0 && !isConfirmed) { 
                    $('#confirmInvoiceSection').show(); 
                }
            }
        }

        function loadPaymentHistory() {
            var jobId = $('#job_id').val() || document.getElementById('invoice_job_id')?.value;
            
            $.ajax({
                url: "<?php echo site_url('job/loadPaymentHistory') ?>",
                type: "POST",
                data: { jobId: jobId },
                success: function(data) {
                    // Replace the payment history wrapper with updated data
                    $('#paymentHistoryWrapper').html(data);
                    
                    // Update the payment summary display in the main form
                    updatePaymentSummaryDisplay();
                },
                error: function() {
                    // Fallback: reload the page
                    location.reload();
                }
            });
        }

        function refreshPaymentHistoryOnly() {
            var jobId = $('#job_id').val() || document.getElementById('invoice_job_id')?.value;
            
            $.ajax({
                url: "<?php echo site_url('job/loadPaymentHistory') ?>",
                type: "POST",
                data: { jobId: jobId },
                success: function(data) {
                    // Only update the payment history table, not the confirm button section
                    var $newData = $(data);
                    var $paymentTable = $newData.find('#paymentHistoryTable');
                    var $paymentSummary = $newData.find('#paymentSummarySection');
                    
                    // Update only the payment summary and table
                    $('#paymentSummarySection').replaceWith($paymentSummary);
                    $('#paymentHistoryTable').replaceWith($paymentTable);
                    
                    // Update the payment summary display in the main form
                    updatePaymentSummaryDisplay();
                },
                error: function() {
                    console.error('Failed to refresh payment history');
                }
            });
        }
        
        function updatePaymentSummaryDisplay() {
            // This function updates any payment summary displays outside the wrapper
            // that might need to be updated after payment history changes
            var jobId = $('#job_id').val() || document.getElementById('invoice_job_id')?.value;
            
            // You can add additional updates here if needed
            // For example, updating any other payment-related displays on the page
        }

        function clearPaymentForm() {
            // Clear payment form fields
            $('#paymentMethod').val('');
            $('#paidAmount').val('');
            $('#bankName').val('');
            $('#chequeNumber').val('');
            $('#chequeDate').val('');
            $('#creditPersonName').val('');
            $('#creditPersonPhone').val('');
            $('#bankChequeFields').hide();
            $('#creditFields').hide();
        }





        function deletePayment(paymentId, paymentMethod, paidAmount) {
            // Show confirmation dialog
            Swal.fire({
                title: 'Delete Payment',
                text: `Are you sure you want to delete this payment of LKR ${paidAmount} (${paymentMethod})?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    var jobId = $('#job_id').val() || document.getElementById('invoice_job_id')?.value;
                    
                    $.ajax({
                        url: "<?php echo site_url('job/deletePaymentFromJob') ?>",
                        type: "POST",
                        data: {
                            jobId: jobId,
                            paymentId: paymentId
                        },
                        success: function(response) {
                            var res = JSON.parse(response);
                            if (res.status === 'success') {
                                Toastify({
                                    text: res.message,
                                    className: "toastify-success",
                                    duration: 5000,
                                    close: true,
                                    gravity: "top",
                                    position: "right"
                                }).showToast();
                                
                                // Load updated payment history
                                loadPaymentHistory();
                            } else {
                                Toastify({
                                    text: res.message,
                                    className: "toastify-error",
                                    duration: 5000,
                                    close: true,
                                    gravity: "top",
                                    position: "right"
                                }).showToast();
                            }
                        },
                        error: function() {
                            Toastify({
                                text: 'An error occurred while deleting the payment.',
                                className: "toastify-error",
                                duration: 5000,
                                close: true,
                                gravity: "top",
                                position: "right"
                            }).showToast();
                        }
                    });
                }
            });
        }

        // Check payment status on page load
        $(document).ready(function() {
            // Check if payments are complete and show appropriate sections
            checkInitialPaymentStatus();
            
            // Load payment history on page load to ensure it's up to date
            loadPaymentHistory();
            
            $('#saveDiscountBtn').click(function() {
                // Check if invoice is fully paid AND confirmed (received amount >= final amount and final amount > 0 and confirmed)
                var receivedAmount = <?= $receivedAmount ?>;
                var finalTotalAmount = <?= $final_total_amount ?>;
                var isConfirmed = <?= $jobData->confirmed_by > 0 ? 'true' : 'false' ?>;
                if (receivedAmount >= finalTotalAmount && finalTotalAmount > 0 && isConfirmed) {
                    Toastify({
                        text: 'Cannot modify discounts after invoice completion.',
                        className: "toastify-error",
                        duration: 5000,
                        close: true,
                        gravity: "top",
                        position: "right"
                    }).showToast();
                    return;
                }

                var btn = $(this);
                var itemId = $('#editItemId').val();
                var newDiscount = $('#newDiscount').val();
                var newDiscountAmount = $('#newDiscountAmount').val();
                $('#discountError').hide();
                $('#discountAmountError').hide();

                if (newDiscount === '' || newDiscount < 0 || newDiscount > 100) {
                    $('#discountError').show();
                    return;
                }

                if (newDiscountAmount === '' || newDiscountAmount < 0) {
                    $('#discountAmountError').show();
                    return;
                }

                //check if have values for both discount and discount amount
                if (newDiscount > 0 && newDiscountAmount > 0) {
                    Toastify({
                        text: 'Please enter either a discount percentage or amount, not both.',
                        className: "toastify-error",
                        duration: 5000,
                        close: true,
                        gravity: "top",
                        position: "right"
                    }).showToast();
                    return;
                }

                btn.html('<i class="fa fa-spinner fa-spin"></i> Saving...');
                btn.prop('disabled', true);

                $.ajax({
                    url: '<?= site_url("job/updateItemDiscount") ?>',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        item_id: itemId,
                        discount: newDiscount,
                        discount_amount: newDiscountAmount
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#discountModal').modal('hide');
                            Toastify({
                                text: response.message,
                                className: "toastify-success",
                                duration: 5000,
                                close: true,
                                gravity: "top",
                                position: "right"
                            }).showToast();
                            
                            // Reload job items to update totals dynamically
                            if (typeof loadJobItems === 'function') {
                                loadJobItems();
                            }
                            if (typeof loadInvoiceJobItems === 'function') {
                                loadInvoiceJobItems();
                            }
                            
                            // Refresh payment history to reflect any changes in totals
                            loadPaymentHistory();
                        } else {
                            Toastify({
                                text: response.message,
                                className: "toastify-error",
                                duration: 5000,
                                close: true,
                                gravity: "top",
                                position: "right"
                            }).showToast();
                        }
                    },
                    error: function(xhr) {
                        alert('Error: ' + xhr.statusText);
                    },
                    complete: function() {
                        // Reset button state
                        btn.html('Save Changes');
                        btn.prop('disabled', false);
                    }
                });
            });

            // Allow pressing Enter in discount field
            $('#newDiscount').keypress(function(e) {
                if (e.which === 13) {
                    $('#saveDiscountBtn').click();
                    return false;
                }
            });
        });

        // Same as customer for credit
        $(document).on('change', '#creditSameAsCustomer', function(){
            if (!this.checked) { $('#creditPersonName').val(''); $('#creditPersonPhone').val(''); return; }
            const selectedText = $('#serviceOrderNoText').text() || '';
            // Fallback: use customer name visible above table if present
            const custCell = $('td:contains(<?= isset($jobData->customer_name) ? json_encode($jobData->customer_name) : "" ?>)').first();
            let name = '<?= isset($jobData->customer_name) ? htmlspecialchars($jobData->customer_name, ENT_QUOTES) : "" ?>';
            let phone = '<?= isset($jobData->job_contact_no) ? htmlspecialchars($jobData->job_contact_no, ENT_QUOTES) : "" ?>';
            $('#creditPersonName').val(name);
            $('#creditPersonPhone').val(phone);
        });
    </script>



    <!-- Discount Edit Modal -->
    <div class="modal fade" id="discountModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered zoomIn" role="document">
            <div class="modal-content">
                <div class="alert alert-primary shadow" role="alert">
                    <div class="modal-header">
                        <h5 class="modal-title" id="discountModalLabel">Update Discount</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"> </button>
                    </div>
                    <div class="modal-body">

                        <form id="discountForm">
                            <input type="hidden" id="editItemId">
                            <div class="form-group">
                                <label>Item Name</label>
                                <input type="text" class="form-control" id="editItemName" readonly>
                            </div>
                            <div class="form-group my-2">
                                <label>Current Discount</label>
                                <input type="text" class="form-control" id="currentDiscount" readonly>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <label>New Discount - Percentage (%)</label>
                                    <input type="number" class="form-control" id="newDiscount" min="0" max="100" required <?= (($receivedAmount >= $final_total_amount && $final_total_amount > 0 && $jobData->confirmed_by > 0)) ? 'disabled' : '' ?>>
                                    <small class="text-danger" id="discountError" style="display:none">Please enter 0-100</small>
                                </div>
                                <div class="col-md-6">
                                    <label>New Discount - Amount</label>
                                    <input type="number" class="form-control" id="newDiscountAmount" min="0" required <?= (($receivedAmount >= $final_total_amount && $final_total_amount > 0 && $jobData->confirmed_by > 0)) ? 'disabled' : '' ?>>
                                    <small class="text-danger" id="discountAmountError" style="display:none">Please enter valid amount</small>
                                </div>
                            </div>

                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" id="saveDiscountBtn" <?= (($receivedAmount >= $final_total_amount && $final_total_amount > 0 && $jobData->confirmed_by > 0)) ? 'disabled' : '' ?>>Save Changes</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .f3 {
            font-size: 1em;
            font-weight: bold;
            color: #fff;
        }

        .f4 {
            font-size: 1em;
            font-weight: 500;
            color: rgb(18, 20, 20);
        }

        f55 {
            color: rgb(202, 0, 0);
            font-size: 0.8rem;
            font-weight: 600;
        }

        

        .bottom-section .row {
            margin-bottom: 10px;
        }

        .bottom-section .form-control {
            width: 100%;
        }

        .bottom-section .d-flex {
            align-items: center;
            justify-content: flex-end;
        }

        .bottom-section button {
            margin-left: 10px;
        }

        /* Invoice locked state */
        .invoice-locked {
            pointer-events: none;
        }

        .invoice-locked input,
        .invoice-locked select,
        .invoice-locked textarea,
        .invoice-locked button:not(.print-btn) {
            opacity: 0.6;
            cursor: not-allowed;
        }

        /* Ensure print buttons are always clickable */
        .print-btn {
            display: inline-block !important;
            pointer-events: auto !important;
            opacity: 1 !important;
            cursor: pointer !important;
        }

        .invoice-locked .print-btn {
            pointer-events: auto !important;
            opacity: 1 !important;
            cursor: pointer !important;
        }

        /* Hide sections by default */
        #confirmInvoiceSection {
            display: none;
        }

        /* Payment summary styling */
        #paymentSummarySection .card {
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        #paymentSummarySection .card-header {
            background: linear-gradient(135deg, #17a2b8, #138496);
        }

        #paymentSummarySection .text-primary {
            font-size: 1.1em;
            font-weight: 600;
        }

        #paymentSummarySection .text-success {
            font-size: 1.1em;
            font-weight: 600;
        }

        #paymentSummarySection .text-danger {
            font-size: 1.1em;
            font-weight: 600;
        }

        /* Confirm invoice section styling */
        #confirmInvoiceSection .alert {
            border-left: 4px solid #28a745;
        }

        /* Print section styling */
        #printInvoiceSection .alert {
            border-left: 4px solid #17a2b8;
        }
    </style>

    <!-- Payment History Modal -->
    <div class="modal fade" id="paymentHistoryModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Payment History</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-sm" id="paymentHistoryTableModal">
                            <thead class="table-primary">
                                <tr>
                                    <th class="text-center">Payment Method</th>
                                    <th class="text-end">Paid Amount</th>
                                    <th class="text-center">Bank Name</th>
                                    <th class="text-center">Cheque No/Transaction ID</th>
                                    <th class="text-center">Cheque Status</th>
                                    <th class="text-center">Date</th>
                                    <th class="text-center">User</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($paymentHistory)) { ?>
                                    <?php foreach ($paymentHistory as $payment) { ?>
                                        <tr>
                                            <td class="text-center"><?= $payment->payment_method ?></td>
                                            <td class="text-end"><?= number_format($payment->paid_amount, 2) ?></td>
                                            <td class="text-center"><?= $payment->bank_name ?></td>
                                            <td class="text-center"><?= $payment->cheque_number ?></td>
                                            <td class="text-center">
                                                <?php if ($payment->cheque_return_by > 0) { ?>
                                                    <span class="text-danger">Bounced</span>
                                                <?php } else { ?>
                                                    <span class="text-success">Not Bounced</span>
                                                <?php } ?>
                                            </td>
                                            <td class="text-center"><?= $payment->payment_date ?></td>
                                            <td class="text-center"><?= $payment->UserName ?></td>
                                            <td class="text-center">
                                                <?php if ($jobData->confirmed_by == 0) { ?>
                                                    <button class="btn btn-danger btn-sm" onclick="deletePayment(<?= $payment->id ?>, '<?= addslashes($payment->payment_method) ?>', <?= $payment->paid_amount ?>)">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                <?php } else { ?>
                                                    <span class="text-muted">-</span>
                                                <?php } ?>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                <?php } else { ?>
                                    <tr>
                                        <td colspan="8" class="text-center text-muted">No payments recorded yet.</td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>