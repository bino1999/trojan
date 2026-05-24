<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Quick Bill Invoices</h4>

            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="<?php echo base_url(); ?>">Home</a></li>
                    <li class="breadcrumb-item active">Quick Bill</li>
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
                        <input type="date" name="sdate" id="sdate" class="form-control" value="">
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="edate" id="edate" class="form-control" value="">
                    </div>
                    <div class="col-md-6">
                        <button class="btn btn-primary" onclick="loadQuickBillList();">Load Quick Bill</button>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <div id="servicesTable"></div>
            </div>
        </div>
    </div>
    <!--end col-->
</div>



<script>
    loadQuickBillList();

    function loadQuickBillList() {
        let sdate = $('#sdate').val();
        let edate = $('#edate').val();

        // Show a loading spinner or message
        $('#servicesTable').html('<p>Loading...</p>');

        $.ajax({
            url: "<?php echo site_url('quickBill/loadQuickBillList') ?>",
            type: "POST",
            data: {
                sdate: sdate,
                edate: edate
            },
            success: function(data) {
                $('#servicesTable').html(data); // Populate the table with data
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText);
                $('#servicesTable').html('<p>Error loading data.</p>');
            }
        });
    }

   </script>