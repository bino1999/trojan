<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Services Management</h4>

            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="<?php echo base_url(); ?>">Home</a></li>
                    <li class="breadcrumb-item active">Invoices</li>
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
                        <button class="btn btn-primary" onclick="loadInvoices();">Service Job Invoices</button>
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
    loadInvoices();

    function loadInvoices() {
        let sdate = $('#sdate').val();
        let edate = $('#edate').val();

        // Show a loading spinner or message
        $('#servicesTable').html('<p>Loading...</p>');

        $.ajax({
            url: "<?php echo site_url('job/loadInvoices') ?>",
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

   // Panel modal for invoice summary & payment
   function openAddItemModal(jobId, vehicleId, customerId, customerName, vehicleNo, serviceDate, serviceType){
        // Populate hidden fields
        if (!document.getElementById('invoice_job_id')) return;
        document.getElementById('invoice_job_id').value = jobId;
        document.getElementById('invoice_vehicle_id').value = vehicleId || 0;
        document.getElementById('invoice_customer_id').value = customerId || 0;
        // Show modal
        var modal = new bootstrap.Modal(document.getElementById('invoicePanelModal'));
        modal.show();
        // Load items summary + payment UI
        loadInvoiceJobItems();
   }

   function closeAddItemModal(){
        const modalEl = document.getElementById('invoicePanelModal');
        const modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) modal.hide();
   }

   function loadInvoiceJobItems(){
        var jobId = document.getElementById('invoice_job_id').value;
        fetch("<?php echo site_url('job/loadJobItems') ?>", {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ jobId: jobId })
        }).then(r=>r.text()).then(html=>{
            const container = document.getElementById('invoiceItemList');
            container.innerHTML = html;
            // Execute inline scripts from the loaded partial so functions like makePayment are available globally
            const scripts = container.querySelectorAll('script');
            scripts.forEach(scr => {
                try {
                    const s = document.createElement('script');
                    s.type = 'text/javascript';
                    s.text = scr.textContent;
                    document.body.appendChild(s);
                } catch (e) { console.error('Script injection error:', e); }
            });
        }).catch(err=>{
            console.error(err);
        });
   }
   </script>

<!-- Invoice Panel Modal -->
<div class="modal fade" id="invoicePanelModal" tabindex="-1" aria-labelledby="invoicePanelLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="invoicePanelLabel">Invoice Summary & Payment</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="invoice_job_id">
        <input type="hidden" id="invoice_vehicle_id">
        <input type="hidden" id="invoice_customer_id">
        <div id="invoiceItemList"><div class="text-center text-muted">Loading...</div></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" onclick="openPaymentHistoryFromPanel()">
          <i class="fa fa-list"></i> Payment History
        </button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
  </div>

<script>
function openPaymentHistoryFromPanel(){
  try {
    const container = document.getElementById('invoiceItemList');
    if (!container) return;
    const phm = container.querySelector('#paymentHistoryModal');
    if (phm) {
      const modal = new bootstrap.Modal(phm);
      modal.show();
    } else {
      // If not yet loaded, try loading items then open
      if (document.getElementById('invoice_job_id')?.value) {
        loadInvoiceJobItems();
        setTimeout(() => {
          const phm2 = container.querySelector('#paymentHistoryModal');
          if (phm2) new bootstrap.Modal(phm2).show();
        }, 300);
      }
    }
  } catch(e) { console.error(e); }
}
</script>