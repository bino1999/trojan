<table id="table1" class="table table-striped table-responsive">
    <thead class="table-info">
        <tr>
            <th class="text-left">ID</th>
            <th class="text-left">Category</th>
            <th class="text-left">Voucher</th>
            <th class="text-left">Expense Date</th>
            <th class="text-left">Employee</th>
            <th class="text-left">Person Name</th>
            <th class="text-left">Person Phone</th>
            <th class="text-left">Created</th>
             <th class="text-end">Amount</th>
            <th class="text-left">Action</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $totalAmount = 0;
        $today = date('Y-m-d');
        foreach ($expenses as $exp) {
            $totalAmount += $exp->amount;
            
            // Determine employee name based on which field is populated
            $employeeName = '';
            if (!empty($exp->employee_fname) && !empty($exp->employee_lname)) {
                $employeeName = trim($exp->employee_fname . ' ' . $exp->employee_lname);
            } else {
                $employeeName = 'N/A';
            }
            
            echo '<tr>';
            echo '<td class="text-left">' . str_pad($exp->id, 2, '0', STR_PAD_LEFT) . '</td>';
            echo '<td class="text-left">' . htmlspecialchars($exp->category_name) . '</td>';
            echo '<td class="text-left">' . htmlspecialchars($exp->reference_no) . '</td>';
            echo '<td class="text-left">' . date('Y-m-d', strtotime($exp->expense_date)) . '</td>';
            echo '<td class="text-left">' . htmlspecialchars($employeeName) . '</td>';
            echo '<td class="text-left">' . htmlspecialchars($exp->person_name) . '</td>';
            echo '<td class="text-left">' . htmlspecialchars($exp->person_phone) . '</td>';
            echo '<td class="text-left">' . htmlspecialchars($exp->UserName) . '</td>';
            echo '<td class="text-end">' . number_format($exp->amount, 2) . '</td>';
            // Show delete button only if created_at is today
        if (date('Y-m-d', strtotime($exp->created_at)) == $today) {
            echo '<td class="text-left">';
            echo '<button class="btn btn-sm btn-danger delete-expense" data-id="' . $exp->id . '"><i class="fa fa-trash"></i></button>';
            echo '</td>';
        } else {
            echo '<td></td>';
        }
            echo '</tr>';
        }
        ?>
    </tbody>
    <tfoot>
        <tr>
            <th colspan="9" class="text-start">Total Expenses:</th>
            <th class="text-end"><?= number_format($totalAmount, 2) ?></th>
        </tr>
    </tfoot>
</table>

<script>
    $('#table1').DataTable({
        "lengthMenu": [
            [10, 25, 50, -1],
            [10, 25, 50, "All"]
        ],
        "searching": true,
        "responsive": true,
        "columnDefs": [
            {
                "targets": 0,
                "className": "text-center"
            }
        ]
    });
</script>
