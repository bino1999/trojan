<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Payments</h4>

            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="<?php echo base_url(); ?>">Home</a></li>
                    <li class="breadcrumb-item active">Payments</li>
                </ol>
            </div>

        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">

        <style>
            .mainCard {
                margin: -10px;
            }
        </style>

        <div class="card mainCard">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-2">
                        <input type="date" name="sdate" id="sdate" class="form-control" value="<?= date('Y-m-d', strtotime('-7 day')); ?>">
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="edate" id="edate" class="form-control" value="<?= date('Y-m-d'); ?>">
                    </div>
                    <div class="col-md-3">
                <select id="paymentMethod" class="form-select" onchange="loadPayments()">
                    <option value="" selected>Select Payment Method</option>
                    <option value="cash">Cash Payment</option>
                    <option value="card">Card Payment</option>
                    <option value="cheque">Cheque Payment</option>
                    <option value="credit">Credit</option>
                    <option value="bank_transfer">Bank Transfer</option>
                </select>
            </div>
            <div class="col-md-2">
                <select id="receiptStatus" class="form-select" onchange="loadPayments()">
                    <option value="" selected>Receipt Status</option>
                    <option value="active">Active</option>
                    <option value="deleted">Deleted Receipts</option>
                </select>
            </div>
                    <div class="col-md-3">
<div class="btn-group shadow" role="group" aria-label="Basic example">
    <button type="button" class="btn btn-primary waves-effect waves-light shadow-none"  onclick="loadPayments('job');">Job Payment</button>
    <button type="button" class="btn btn-info waves-effect waves-light shadow-none" onclick="loadPayments('bill');">Bill Payment</button>
</div>
                    </div>

                </div>
            </div>

            <div class="card-body">
                <div id="paymentTable"></div>
            </div>
        </div>
    </div>
    <!--end col-->
</div>



<script>
    loadPayments();

    function loadPayments(typeOfPayment) {
        let sdate = $('#sdate').val();
        let edate = $('#edate').val();
        let paymentMethod = $('#paymentMethod').val();
        let receiptStatus = $('#receiptStatus').val();

        // Show a loading spinner or message
        $('#paymentTable').html('<p>Loading...</p>');
        $.ajax({
            url: "<?php echo site_url('payments/loadPayments') ?>",
            type: "POST",
            data: {
                sdate: sdate,
                edate: edate,
                paymentMethod: paymentMethod,
                receiptStatus: receiptStatus,
                typeOfPayment: typeOfPayment
            },
            success: function(data) {
                $('#paymentTable').html(data); // Populate the table with data
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText);
                $('#paymentTable').html('<p>Error loading data.</p>');
            }
        });
    }

   </script>