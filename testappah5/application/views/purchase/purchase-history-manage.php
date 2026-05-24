<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Item Purchase History</h4>

            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="<?php echo base_url(); ?>">Home</a></li>
                    <li class="breadcrumb-item active">Item Purchase History</li>
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
                        <input type="date" name="sdate" id="sdate" class="form-control" value="<?= date('Y-m-d', strtotime('-1 month')); ?>">
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="edate" id="edate" class="form-control" value="<?= date('Y-m-d'); ?>">
                    </div>
                    <div class="col-md-2">
                        <select name="supplier_id" id="supplier_id" class="form-control" onchange="loadPurchasesItemHistory();">
                            <option value="">-- Select Supplier --</option>
                            <?php foreach ($suppliers as $supplier) : ?>
                                <option value="<?= $supplier->supplier_id ?>"><?= $supplier->name ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="product_id" id="product_id" class="form-control" onchange="loadPurchasesItemHistory();">
                            <option value="">-- Select Product --</option>
                            <?php foreach ($products as $product) : ?>
                                <option value="<?= $product->product_id ?>"><?= $product->product_name ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary" onclick="loadPurchasesItemHistory();">Search</button>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <div id="purchasehistoryTable"></div>
            </div>
        </div>
    </div>
    <!--end col-->
</div>






<script>

$(document).ready(function () {
    $('#supplier_id').select2({
        placeholder: "-- Supplier --",
        allowClear: true,
        width: '100%'
    });

    $('#product_id').select2({
        placeholder: "-- Select Product --",
        allowClear: true,
        width: '100%'
    });
});

   
   loadPurchasesItemHistory();

   function loadPurchasesItemHistory() {
        let sdate = $('#sdate').val();
        let edate = $('#edate').val();
        let supplier_id = $('#supplier_id').val();
        let product_id = $('#product_id').val();

        $.ajax({
            url: "<?php echo site_url('purchase/loadPurchasesItemHistory') ?>",
            type: "POST",
            data: {
                sdate: sdate,
                edate: edate,
                supplier_id: supplier_id,
                product_id: product_id
            },
            success: function(data) {
                $('#purchasehistoryTable').html(data);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText);
            }
        });
    }



</script>