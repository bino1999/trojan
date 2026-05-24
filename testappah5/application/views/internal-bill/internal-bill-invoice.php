<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Bill List</h4>

            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="<?php echo base_url(); ?>">Home</a></li>
                    <li class="breadcrumb-item active">Bill List</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-3">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="sdate" id="sdate" class="form-control" value="<?= date('Y-m-01') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">End Date</label>
                        <input type="date" name="edate" id="edate" class="form-control" value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div>
                            <button class="btn btn-primary" onclick="loadInternalBillList();">Load Bills</button>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div>
                            <a href="<?= base_url('internalBill') ?>" class="btn btn-success">
                                <i class="fa fa-plus"></i> New Bill
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div id="internalBillTable">
                    <!-- Internal bills will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Load initial internal bill list
    loadInternalBillList();
});

function loadInternalBillList() {
    let sdate = $('#sdate').val();
    let edate = $('#edate').val();
    
    $.ajax({
        url: "<?php echo site_url('internalBill/loadInternalBillList') ?>",
        type: "POST",
        data: {
            sdate: sdate,
            edate: edate
        },
        success: function(data) {
            $('#internalBillTable').html(data);
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.log(jqXHR.responseText);
        }
    });
}
</script>
