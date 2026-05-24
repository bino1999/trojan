<?php
$currentStatus = $this->session->userdata('filter_job_status');
$isManager = $this->session->userdata('filter_job_status_is_manager');
?>

<table id="table1" class="table table-striped table-responsive">
    <thead class="table-info">
        <tr>
        <th>##</th>
            <th>Job</th>
            <th>Vehicle</th>
            <th>Customer</th>
            <th>Date</th>
            <th>Type</th>
            <th>Amount</th>
            <th>Paid</th>
            <th>Balance</th>
            <th>Status</th>
            <th>Created</th>
            <th width="10%">Action</th>
        </tr>
    </thead>
     
    <tbody>
    <?php
        $no = 1;
        foreach ($jobs as $key => $job) { ?>
            <tr>
            <td><?= $no++; ?></td>
                <td><?= $job->job_id ?></td>
                <td><?= $job->vehicle_no ?></td>
                <td><?= $job->customer_name ?><br>
                    <small><?= $job->job_contact_no ?></small>
                </td>
                <td><?= date('Y-m-d', strtotime($job->service_date)); ?></td>

                <!--if service_type=N Normal, E=Estimate, M=Mechanical, or multiple types -->
                <td><?php 
                    if($job->service_type) {
                        $service_types = explode(',', $job->service_type);
                        $type_labels = [];
                        foreach($service_types as $type) {
                            $type = trim($type);
                            switch($type) {
                                case 'N':
                                    $type_labels[] = 'Normal';
                                    break;
                                case 'M':
                                    $type_labels[] = 'Mechanical';
                                    break;
                                case 'E':
                                    $type_labels[] = 'Estimate';
                                    break;
                                default:
                                    $type_labels[] = 'Unknown';
                            }
                        }
                        echo implode(', ', $type_labels);
                    } else {
                        echo 'Unknown';
                    }
                ?></td>

                
                <td class="text-end"><?= number_format($job->final_bill_amount ?? 0, 2) ?></td>
                <td class="text-end"><?= number_format($job->total_paid ?? 0, 2) ?></td>
                <td class="text-end"><?= number_format($job->total_balance ?? 0, 2) ?></td>

                <td>
                <!--if status=0 then Pending, 1=Ongoing, 2=Hold, 3=Cancelled, 4=Completed-->
                <?php if($job->status == 0){ ?>
                    <span class="badge bg-warning"><i class="fa fa-bell"></i> Pending</span>
                <?php }else if($job->status == 1){ ?>
                    <span class="badge bg-info"><i class="fa fa-spinner"></i> Ongoing</span>
                <?php }else if($job->status == 2){ ?>
                    <span class="badge bg-secondary"><i class="fa fa-stop-circle-o"></i> Hold</span>
                <?php }else if($job->status == 3){ ?>
                    <span class="badge bg-danger"><i class="fa fa-ban"></i> Cancelled</span>
                <?php }else if($job->status == 4){ ?>
                    <?php if($isManager){ ?>
                        <span class="badge bg-warning"><i class="fa fa-exclamation-circle"></i> Pending Approval</span>
                    <?php } else { ?>
                        <span class="badge bg-primary"><i class="fa fa-hourglass-half"></i> Waiting Manager Approval</span>
                    <?php } ?>
                <?php }else if($job->status == 5){ ?>
                    <span class="badge bg-success"><i class="fa fa-check"></i> Invoiced</span>
                <?php }else{ ?>
                    <span class="badge bg-secondary"><i class="fa fa-exclamation-triangle"></i> Unknown</span>
                <?php } ?>

                <?php if($job->is_credit>0){ ?>
                    <span class="badge bg-warning"><i class="fa fa-share"></i> Credit</span>
                <?php } ?>

                <?php if($job->total_balance>0){ ?>
                    <span class="badge bg-danger"><i class="fa fa-exclamation-triangle"></i> Due</span>
                <?php } ?>
                </td>
               
                <td><?= date('Y-m-d', strtotime($job->created_at)); ?></td>
                <td>
                    <?php if($currentStatus != 5 && $currentStatus != 3 && ($currentStatus != 4 || $isManager)){ ?>
                    <div class="d-flex gap-2">
                        <button class="btn btn-success btn-sm" id="openModalButton"
                            onclick="openAddItemModal(<?= $job->job_id ?>, <?= $job->vehicle_id ?? 0 ?>, <?= $job->customer_id ?? 0 ?>, '<?= htmlspecialchars($job->customer_name ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($job->vehicle_no ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars((isset($job->service_date) && $job->service_date ? date('Y-m-d', strtotime($job->service_date)) : ''), ENT_QUOTES) ?>', '<?= htmlspecialchars($job->service_type ?? '', ENT_QUOTES) ?>')">
                            <i class="fa fa-check-circle"></i>  <?= ($isManager ? 'Approve' : 'Panel') ?>
                        </button>
                        </div>
                    <?php } ?>



                        <?php  if($currentStatus != 5 && $currentStatus != 3 && ($currentStatus != 4 || $isManager)){ ?>
                         <div class="btn-group mt-1">
    <button class="btn btn-info btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        Status
    </button>
    <div class="dropdown-menu">
        <a class="dropdown-item" href="#" onclick="changeJobStatusFromFront(<?= $job->job_id ?>, 0, 'Pending')">Pending</a>
        <a class="dropdown-item" href="#" onclick="changeJobStatusFromFront(<?= $job->job_id ?>, 1, 'Ongoing')">Ongoing</a>
        <a class="dropdown-item" href="#" onclick="changeJobStatusFromFront(<?= $job->job_id ?>, 4, 'Completed')">Completed</a>
        <div class="dropdown-divider"></div>
        <a class="dropdown-item" href="#" onclick="changeJobStatusFromFront(<?= $job->job_id ?>, 2, 'Hold')">Hold</a>
        <a class="dropdown-item" href="#" onclick="changeJobStatusFromFront(<?= $job->job_id ?>, 3, 'Cancelled')">Cancelled</a>
    </div>
</div>

<?php } ?>


<?php  if($currentStatus == 5 && $job->print_access_by>0){ ?>
    <div class="d-flex gap-1 mt-1">
                    <a class="btn btn-secondary btn-sm" target="_blank" style="width: 100%;" href="<?php echo base_url('print-job/' . urlencode(base64_encode($job->job_id))) ?>">
    <i class="fa fa-print"></i>
</a>

<a class="btn btn-warning btn-sm" target="_blank" style="width: 100%;" href="<?php echo base_url('print-job-mini/' . urlencode(base64_encode($job->job_id))) ?>">
    <i class="fa fa-print"></i>
</a>
                    </div>
<?php } ?>

                    
                </td>
            </tr>
            <?php } ?>
    </tbody>
</table>

<style>
    

span.badge.bg-warning {
    background-color:rgb(225, 170, 5) !important;
    font-weight: 800;
}

span.badge.bg-info {
    background-color: rgb(0, 123, 255) !important;
    font-weight: 800;
}

span.badge.bg-secondary {
    background-color: rgb(1, 13, 23) !important;
    font-weight: 800;
}

span.badge.bg-danger {
    background-color: rgb(232, 0, 23) !important;
    font-weight: 800;
}

span.badge.bg-success {
    background-color: rgb(8, 165, 92) !important;
    font-weight: 800;
}
</style>

<script>
    serviceTableToDatatable();
    function serviceTableToDatatable() {
    // Destroy existing DataTable if it exists
    if ($.fn.DataTable.isDataTable('#table1')) {
        $('#table1').DataTable().destroy();
    }
    
    // Initialize new DataTable
    $('#table1').DataTable({
        "lengthMenu": [
            [10, 25, 50, -1],
            [10, 25, 50, "All"]
        ],
        "searching": true,
        "responsive": true
    });
}

function changeJobStatusFromFront(jobId, jobStatus, jobStatusText) {
    //get job status text
        
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
</script>
