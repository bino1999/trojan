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
            <th class="text-left">Date</th>
            <th class="text-left">User</th>
            <?php
            if ($receiptStatus == 'deleted') {
                echo '<th class="text-left">Status</th>';
                echo '<th class="text-left">Delete Reason</th>';
            }
            ?>
            <?php
            if ($paymentMethod == 'cheque') {
                echo '<th class="text-left">Bounced</th>';
                echo '<th class="text-left">Reason</th>';
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
            echo '<td class="text-left">' . $payment->cheque_number . '</td>';
            echo '<td class="text-left">' . $payment->cheque_date . '</td>';
            echo '<td class="text-left">' . date('Y-m-d', strtotime($payment->payment_date)) . '</td>';
            echo '<td class="text-left">' . $payment->UserName . '</td>';
            //if deleted_by>0 then payment is deleted
            if ($receiptStatus == 'deleted' && $payment->deleted_by > 0) {
                echo '<td class="text-left text-danger">Deleted</td>';
                echo '<td class="text-left text-danger">' . $payment->deleted_reason . '</td>';
            }
            //cheuque payment status
            if ($paymentMethod == 'cheque') {
                if($payment->cheque_return_by > 0) {
                    echo '<td class="text-left text-danger">Bounced</td>';
                    echo '<td class="text-left text-danger">' . $payment->cheque_return_note . '</td>';
                } else {
                    echo '<td class="text-left text-warning">Not Bounced</td>';

$chequeTimestamp = strtotime($payment->cheque_date);
$now = time();

// Check if cheque date is today or within the past 10 days
if ($chequeTimestamp <= $now && $chequeTimestamp >= ($now - 10 * 24 * 60 * 60)) {
    echo '<td class="text-left"><button class="btn btn-danger btn-sm" onclick="returnCheque(' . $payment->id . ', ' . json_encode($payment->cheque_number) . ')">Return Cheque</button></td>';
} else {
    echo '<td class="text-left">N/A</td>';
}

                }
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


function returnCheque(payment_id, chequeNumber) {
    Swal.fire({
        title: 'Return Cheque',
        input: 'text',
        inputLabel: 'Enter reason for returning the cheque ' + chequeNumber,
        inputPlaceholder: 'Enter reason here...',
        inputAttributes: {
            'aria-label': 'Reason'
        },
        showCancelButton: true,
        confirmButtonText: 'Submit',
        cancelButtonText: 'Cancel',
        inputValidator: (value) => {
            if (!value) {
                return 'You need to write a reason!';
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const reason = result.value;

            // Send reason and cheque ID via AJAX to your controller
            $.ajax({
                url: '<?= base_url("payments/returnCheque") ?>',
                method: 'POST',
                data: {
                    cheque_id: payment_id,
                    reason: reason
                },
                success: function(response) {
                    Swal.fire('Success!', 'Cheque marked as returned.', 'success');
                    loadPayments();
                },
                error: function() {
                    Swal.fire('Error', 'Failed to return cheque. Try again later.', 'error');
                }
            });
        }
    });
}

</script>