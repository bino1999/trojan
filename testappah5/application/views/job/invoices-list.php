<table id="table1" class="table table-striped table-responsive">
    <thead class="table-info">
        <tr>
        <th>##</th>
            <th>Job ID</th>
            <th>Vehicle</th>
            <th width="20%">Customer</th>
            <th>Date</th>
            <th>Type</th>
            <th>Amount</th>
            <th>Paid</th>
            <th>Balance</th>
            <th>Created</th>
            <th>Printed By</th>
            <th>Print</th>
            <th>Action</th>
        </tr>
    </thead>
     
    <tbody>
    <?php
        $no = 1;
        foreach ($jobs as $key => $job) { ?>
            <tr class="<?= ($job->total_balance ?? 0) <= 0 ? 'table-success' : '' ?>">
            <td><?= $no++; ?></td>
                <td><?= $job->job_id ?></td>
                <td><?= $job->vehicle_no ?></td>
                <td><?= $job->customer_name ?><br>
                <?= $job->job_contact_no ?></td>
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
                <td class="text-end">
                    <?= number_format($job->total_balance ?? 0, 2) ?>
                    <?php
                    if($job->total_balance > 0){
                        echo '<span class="badge bg-danger">Due</span>'; 
                    } ?>
                </td>
               
                <td><?= date('Y-m-d', strtotime($job->created_at)); ?></td>
                <td><?= $job->print_access_at ? date('Y-m-d', strtotime($job->print_access_at)) : ''; ?></td>
                <td>
                    <?php if (($job->total_paid ?? 0) > 0) { ?>
                        <div class="d-flex gap-1">
                            <a class="btn btn-primary btn-sm" target="_blank" href="<?= base_url('print-job-mini/' . urlencode(base64_encode($job->job_id))) ?>" title="Mini Invoice">
                                <i class="fa fa-print"></i>
                            </a>
                            <a class="btn btn-info btn-sm" target="_blank" href="<?= base_url('print-job/' . urlencode(base64_encode($job->job_id))) ?>" title="Full Invoice">
                                <i class="fa fa-file-text"></i>
                            </a>
                        </div>
                    <?php } else { ?>
                        <span class="text-muted">No payments</span>
                    <?php } ?>
                </td>
                <td>
                    <div class="d-flex gap-2">
                        <button class="btn btn-success btn-sm" onclick="openAddItemModal(<?= $job->job_id ?>, <?= $job->vehicle_id ?? 0 ?>, <?= $job->customer_id ?? 0 ?>, '<?= htmlspecialchars($job->customer_name ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($job->vehicle_no ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars((isset($job->service_date) && $job->service_date ? date('Y-m-d', strtotime($job->service_date)) : ''), ENT_QUOTES) ?>', '<?= htmlspecialchars($job->service_type ?? '', ENT_QUOTES) ?>')">
                            <i class="fa fa-list"></i> Panel
                        </button>
                    </div>
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
</script>
