<table id="table1" class="table table-striped table-responsive">
    <thead class="table-info">
        <tr>
            <th>#</th>
            <th style="text-align: left;">PO ID</th>
            <th style="text-align: left;">Supplier</th>
            <th style="text-align: left;">Bill No</th>
            <th style="text-align: left;">Bill Date</th>
            <th style="text-align: left;">Payment</th>
            <th style="text-align: right;">Amount</th>
            <th style="text-align: left;">Vat</th>
            <th style="text-align: right;">Total</th>
            <th style="text-align: left;">Description</th>
            <th style="text-align: center;">Status</th>
            <th style="text-align: center;">Payments</th>
            <th style="text-align: right;">Action</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $no = 1;
        foreach ($purchases as $index => $row) { ?>
            <tr>
                <td><?= ($index + 1); ?></td>
                <td><?= $row->po_id; ?></td>
                <td><?= $row->supplier_name; ?></td>
                <td><?= $row->bill_no; ?></td>
                <td><?= $row->bill_date; ?></td>
                <td>
                    <?php
                    $payment_method = $row->intended_payment_method ?? $row->payment_method ?? 0;
                    if ($payment_method == 1) {
                        echo 'Cash';
                    } elseif ($payment_method == 2) {
                        echo 'Cheque';
                    } elseif ($payment_method == 3) {
                        echo 'Card';
                    } elseif ($payment_method == 4) {
                        echo 'Credit';
                    } elseif ($payment_method == 5) {
                        echo 'Bank Transfer';
                    } else {
                        echo 'Other';
                    }
                    ?>
                </td>
                <td style="text-align: right;">
                <?php
                if($row->vat_percent > 0) {
                    $amountDisplay = $row->total_amount - ($row->total_amount * $row->vat_percent / 100);
                } else {
                    $amountDisplay = $row->total_amount;
                }
                echo number_format($amountDisplay, 2);
                ?>
                </td>
                <td><?= $row->vat_percent > 0 ? 'Yes (' . $row->vat_percent . '%)' : 'No' ?></td>
                <td style="text-align: right;">
                <?= number_format($row->total_amount, 2); ?>
                </td>
                <td><?= $row->description; ?></td>
                
                <td style="text-align: center;">
                    <?php if ($row->status == 'Completed') { ?>
                        <span class="badge bg-success">Completed</span>
                    <?php } elseif ($row->status == 'Archived') { ?>
                        <span class="badge bg-secondary">Archived</span>
                    <?php } else { ?>
                        <span class="badge bg-warning">Pending</span>
                    <?php } ?>
                </td>
                <td style="text-align: center;">
                    <?php if ($row->status == 'Completed') { ?>
                        <?php 
                        $payment_status = $row->payment_status ?? 'Pending';
                        $total_paid = $row->total_paid ?? 0;
                        $total_amount = $row->total_amount ?? 0;
                        
                        if ($payment_status === 'Completed') {
                            echo '<span class="badge bg-success">Paid</span>';
                        } elseif ($payment_status === 'Partial') {
                            echo '<span class="badge bg-warning">Partial</span><br><small>Rs. ' . number_format($total_paid, 2) . ' / ' . number_format($total_amount, 2) . '</small>';
                        } else {
                            echo '<span class="badge bg-danger">Pending</span>';
                        }
                        ?>
                    <?php } else { ?>
                        <span class="text-muted">-</span>
                    <?php } ?>
                </td>
                <td style="text-align: right;">
                    <button class="btn btn-info btn-sm"
                        onclick="openViewItemModal(<?= $row->po_id ?>, <?= $row->supplier_id ?>, '<?= htmlspecialchars($row->supplier_name, ENT_QUOTES) ?>', '<?= htmlspecialchars($row->bill_no, ENT_QUOTES) ?>', '<?= htmlspecialchars($row->bill_date, ENT_QUOTES) ?>')">
                        <i class="fa fa-search"></i> View
                    </button>

                    <?php if ($row->status != 'Archived') { ?>
                    <button class="btn btn-warning btn-sm"
                        onclick="openEditPurchaseModal(<?= $row->po_id ?>, <?= $row->supplier_id ?>, '<?= htmlspecialchars($row->bill_no, ENT_QUOTES) ?>', '<?= htmlspecialchars($row->bill_date, ENT_QUOTES) ?>', <?= ($row->intended_payment_method ?? $row->payment_method ?? 0) ?>, '<?= htmlspecialchars($row->vat_type, ENT_QUOTES) ?>', <?= $row->vat_percent ?>, '<?= htmlspecialchars($row->description, ENT_QUOTES) ?>', '<?= htmlspecialchars($row->status, ENT_QUOTES) ?>')">
                        <i class="fa fa-edit"></i> Edit
                    </button>
                    <?php } ?>

                    <?php if ($row->status != 'Completed' && $row->status != 'Archived') { ?>
                        <button class="btn btn-success btn-sm"
                            onclick="openAddItemModal(<?= $row->po_id ?>, <?= $row->supplier_id ?>, '<?= htmlspecialchars($row->supplier_name, ENT_QUOTES) ?>', '<?= htmlspecialchars($row->bill_no, ENT_QUOTES) ?>', '<?= htmlspecialchars($row->bill_date, ENT_QUOTES) ?>', '<?= htmlspecialchars($row->vat_type, ENT_QUOTES) ?>', <?= $row->vat_percent ?>)">
                            + Add Item
                        </button>
                    <?php } ?>
                    
                    <?php if ($row->status == 'Completed') { ?>
                        <button class="btn btn-info btn-sm"
                            onclick="openPoPayments(<?= $row->po_id ?>)">
                            <i class="fa fa-credit-card"></i> Payments
                        </button>
                    <?php } ?>
                    
                    <button class="btn btn-danger btn-sm" onclick="deletePurchase(<?= $row->po_id ?>)">Delete</button>
                </td>

            </tr>
        <?php } ?>
    </tbody>
</table>