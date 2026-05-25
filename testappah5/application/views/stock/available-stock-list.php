<div class="table-responsive">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="mb-0">
            Stock Report
            <?php if ($has_date_filter): ?>
                <span class="badge bg-info text-white ms-2" style="font-size:11px;">Date Filtered</span>
            <?php else: ?>
                <span class="badge bg-secondary text-white ms-2" style="font-size:11px;">All Available Stock</span>
            <?php endif; ?>
        </h6>
        <div class="btn-group">
            <button class="btn btn-outline-primary btn-sm" onclick="printStockTable()">
                <i class="fas fa-print"></i> Print
            </button>
            <button class="btn btn-outline-success btn-sm" onclick="downloadStockPDF()">
                <i class="fas fa-file-pdf"></i> Download PDF
            </button>
        </div>
    </div>

    <table class="table table-bordered table-hover align-middle" id="stockTable">
        <thead class="table-primary">
            <tr>
                <th>#</th>
                <th>Date</th>
                <th>Description</th>
                <th>SKU</th>
                <th>Supplier</th>
                <th>Product Type</th>
                <th>Stock In</th>
                <th>Stock Out</th>
                <th>Closing Balance</th>
                <th class="text-center">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $i = 1;
            $current_product = null;
            foreach ($stock as $item):
                $closing   = (float)$item->closing_balance;
                $stock_in  = (float)$item->stock_in;
                $stock_out = (float)$item->stock_out;

                // Carry-forward = purchased before the filter start date
                $is_carry_forward = $has_date_filter
                    && isset($filter_sdate)
                    && $item->purchase_date < $filter_sdate;

                // Group separator when product name changes
                $show_separator = ($current_product !== null && $current_product !== $item->product_name);
                $current_product = $item->product_name;
                ?>
                <?php if ($show_separator): ?>
                <tr class="table-light">
                    <td colspan="10" style="height:4px;padding:0;border:none;background:#e9ecef;"></td>
                </tr>
                <?php endif; ?>
                <tr class="<?= $is_carry_forward ? 'table-warning' : '' ?>">
                    <td><?= $i++ ?></td>
                    <td style="white-space:nowrap;">
                        <?= $item->purchase_date ? date('d M Y', strtotime($item->purchase_date)) : '—' ?>
                        <?php if ($is_carry_forward): ?>
                            <br><span class="badge bg-warning text-dark" style="font-size:10px;">Carry-forward</span>
                        <?php endif; ?>
                    </td>
                    <td><?= strtoupper($item->product_name) ?></td>
                    <td><?= $item->sku ?: '—' ?></td>
                    <td><?= $item->supplier_name ?: 'N/A' ?></td>
                    <td><?= $item->uom ?: '—' ?></td>
                    <td><?= number_format($stock_in, 2) ?></td>
                    <td><?= number_format($stock_out, 2) ?></td>
                    <td class="<?= $closing < 0 ? 'text-danger fw-bold' : 'text-success fw-semibold' ?>">
                        <?= number_format($closing, 2) ?>
                    </td>
                    <td class="text-center">
                        <?php if ($closing > 0): ?>
                        <button class="btn btn-danger btn-sm"
                                onclick="confirmBatchWriteOff(
                                    <?= (int)$item->po_item_id ?>,
                                    '<?= htmlspecialchars($item->product_name, ENT_QUOTES) ?>',
                                    '<?= date('d M Y', strtotime($item->purchase_date)) ?>',
                                    <?= $closing ?>
                                )"
                                title="Write off this batch">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                        <?php else: ?>
                        <span class="text-muted small">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>

            <?php if (empty($stock)): ?>
            <tr>
                <td colspan="10" class="text-center text-muted py-4">No stock records found.</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
function printStockTable() {
    const printWindow = window.open('', '_blank');
    const table = document.getElementById('stockTable');
    const currentDate = new Date().toLocaleDateString();
    const sdate = document.getElementById('sdate').value;
    const edate = document.getElementById('edate').value;
    const cleanTable = table.cloneNode(true);

    printWindow.document.write(`
        <html>
        <head>
            <title>Stock Report - ${currentDate}</title>
            <style>
                @page { size: A4; margin: 1cm; }
                body { font-family: Arial, sans-serif; font-size: 12px; margin: 0; padding: 0; }
                .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
                .header h1 { margin: 0; font-size: 18px; }
                .header p { margin: 5px 0; font-size: 12px; }
                table { width: 100%; border-collapse: collapse; margin-top: 10px; }
                th, td { border: 1px solid #333; padding: 6px; text-align: left; font-size: 10px; }
                th { background-color: #f5f5f5; font-weight: bold; }
                .footer { margin-top: 20px; text-align: center; font-size: 10px; color: #666; }
                .btn-group, .d-flex { display: none !important; }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>STOCK REPORT</h1>
                <p>Generated on: ${currentDate}</p>
                <p>Report Period: ${sdate || 'All Time'} to ${edate || 'Current'}</p>
            </div>
            ${cleanTable.outerHTML}
            <div class="footer"><p>This report was generated from the Stock Management System</p></div>
        </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.print();
}

function downloadStockPDF() {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '<?php echo site_url('purchase/downloadStockPDF') ?>';
    form.target = '_blank';

    const fields = {
        sdate:       document.getElementById('sdate').value,
        edate:       document.getElementById('edate').value,
        category_id: document.getElementById('filter_item_category').value,
        brand_id:    document.getElementById('filter_item_brand').value,
        supplier_id: document.getElementById('filter_supplier').value
    };

    Object.entries(fields).forEach(([name, value]) => {
        const input = document.createElement('input');
        input.type  = 'hidden';
        input.name  = name;
        input.value = value;
        form.appendChild(input);
    });

    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}

function confirmBatchWriteOff(poItemId, productName, purchaseDate, closingBalance) {
    Swal.fire({
        title: 'Write Off Batch?',
        html: `<p>You are about to <strong>zero out the remaining stock</strong> for this batch:</p>
               <p><strong>${productName}</strong></p>
               <p>Purchase Date: <strong>${purchaseDate}</strong></p>
               <p>Remaining: <strong>${closingBalance}</strong> units</p>
               <hr>
               <label class="form-label text-start d-block">Reason <span class="text-danger">*</span></label>
               <textarea id="writeoff-reason" class="form-control" rows="3" placeholder="e.g. damaged goods, stock audit correction…"></textarea>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Write Off',
        cancelButtonText: 'Cancel',
        preConfirm: () => {
            const reason = document.getElementById('writeoff-reason').value.trim();
            if (!reason) {
                Swal.showValidationMessage('A reason is required.');
                return false;
            }
            return reason;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '<?= site_url('purchase/writeOffBatchStock') ?>',
                type: 'POST',
                data: { po_item_id: poItemId, reason: result.value },
                success: function(response) {
                    const res = typeof response === 'string' ? JSON.parse(response) : response;
                    if (res.status === 'success') {
                        Swal.fire('Written Off', res.message, 'success').then(() => {
                            loadAvailableStock();
                        });
                    } else {
                        Swal.fire('Error', res.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Request failed. Please try again.', 'error');
                }
            });
        }
    });
}
</script>
