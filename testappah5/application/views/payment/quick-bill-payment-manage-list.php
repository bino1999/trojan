<table id="table1" class="table table-striped table-responsive">
    <thead class="table-info">
        <tr>
            <th class="text-left">#</th>
            <th class="text-left">Receipt</th>
            <th class="text-left">Method</th>
            <th class="text-end">Paid Amount</th>
            <th class="text-left">Bank Name</th>
            <th class="text-left">Cheque/Card Number</th>
            <th class="text-left">Cheque Date</th>
            <th class="text-left">Note</th>
            <th class="text-left">Date</th>
            <th class="text-left">User</th>
            <?php
            if ($receiptStatus == 'deleted') {
                echo '<th class="text-left">Status</th>';
                echo '<th class="text-left">Delete Reason</th>';
            }
            ?>
        </tr>
    </thead>
    <tbody>

        <?php
        $i = 1;
        $totalPaidPfor_summary = 0;
        foreach ($payments as $payment) {
            $totalPaidPfor_summary += $payment->paid_amount;
            echo '<tr>';
            echo '<td class="text-left">' . $i . '</td>';
            echo '<td class="text-left">' . str_pad($payment->id, 4, '0', STR_PAD_LEFT) . '</td>';
            echo '<td class="text-left">' . $payment->payment_method . '</td>';
            echo '<td class="text-end">' . number_format($payment->paid_amount, 2) . '</td>';
            echo '<td class="text-left">' . $payment->bank_name . '</td>';
            echo '<td class="text-left">' . $payment->card_or_cheque_no . '</td>';
            echo '<td class="text-left">' . $payment->cheque_date . '</td>';
            echo '<td class="text-left">' . $payment->note . '</td>';
            echo '<td class="text-left">' . date('Y-m-d', strtotime($payment->payment_date)) . '</td>';
            echo '<td class="text-left">' . $payment->UserName . '</td>';
            //if deleted_by>0 then payment is deleted
            if ($receiptStatus == 'deleted' && $payment->deleted_by > 0) {
                echo '<td class="text-left text-danger">Deleted</td>';
                echo '<td class="text-left text-danger">' . $payment->deleted_reason . '</td>';
            }
            echo '</tr>';
            $i++;
        }

        ?>
    </tbody>
    <tfoot>
        <tr>
            <th colspan="3" class="text-start">Total Payment:</th>
            <th class="text-end"><?= number_format($totalPaidPfor_summary, 2) ?></th>
            <th colspan="3"></th>
        </tr>
</table>

<script>
    // Initialize new DataTable
    $('#table1').DataTable({
        "lengthMenu": [
            [10, 25, 50, -1],
            [10, 25, 50, "All"]
        ],
        "searching": true,
        "responsive": true
    });
</script>