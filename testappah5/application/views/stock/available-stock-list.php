<div class="table-responsive">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="mb-0">Stock Report</h6>
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
                <th>Description</th>
                <th>SKU</th>
                <th>Supplier</th>
                <th>Product Type</th>
                <th>Open Balance</th>
                <th>Stock In</th>
                <th>Stock Out</th>
                <th>Closing Balance</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $i = 1;
            foreach ($stock as $item) { 
                ?>
                <tr>
                    <td><?= $i++ ?></td>
                    <td><?= strtoupper($item->product_name) ?></td>
                    <td><?= $item->sku ?></td>
                    <td><?= isset($item->supplier_name) ? $item->supplier_name : 'N/A' ?></td>
                    <td><?= isset($item->uom) ? $item->uom : 'Piece' ?></td>
                    <td><?= number_format($item->open_balance, 2) ?></td>
                    <td><?= number_format($item->stock_in, 2) ?></td>
                    <td><?= number_format($item->stock_out, 2) ?></td>
                    <td><?= number_format($item->closing_balance, 2) ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<script>
function printStockTable() {
    // Create a new window for printing
    const printWindow = window.open('', '_blank');
    const table = document.getElementById('stockTable');
    
    // Get current date for header
    const currentDate = new Date().toLocaleDateString();
    const sdate = document.getElementById('sdate').value;
    const edate = document.getElementById('edate').value;
    
    // Create a clean table without buttons for printing
    const cleanTable = table.cloneNode(true);
    
    printWindow.document.write(`
        <html>
        <head>
            <title>Stock Report - ${currentDate}</title>
            <style>
                @page {
                    size: A4;
                    margin: 1cm;
                }
                body {
                    font-family: Arial, sans-serif;
                    font-size: 12px;
                    margin: 0;
                    padding: 0;
                }
                .header {
                    text-align: center;
                    margin-bottom: 20px;
                    border-bottom: 2px solid #333;
                    padding-bottom: 10px;
                }
                .header h1 {
                    margin: 0;
                    font-size: 18px;
                }
                .header p {
                    margin: 5px 0;
                    font-size: 12px;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-top: 10px;
                }
                th, td {
                    border: 1px solid #333;
                    padding: 6px;
                    text-align: left;
                    font-size: 10px;
                }
                th {
                    background-color: #f5f5f5;
                    font-weight: bold;
                }
                .footer {
                    margin-top: 20px;
                    text-align: center;
                    font-size: 10px;
                    color: #666;
                }
                .btn-group, .d-flex {
                    display: none !important;
                }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>STOCK REPORT</h1>
                <p>Generated on: ${currentDate}</p>
                <p>Report Period: ${sdate || 'All Time'} to ${edate || 'Current'}</p>
            </div>
            ${cleanTable.outerHTML}
            <div class="footer">
                <p>This report was generated from the Stock Management System</p>
            </div>
        </body>
        </html>
    `);
    
    printWindow.document.close();
    printWindow.print();
}

function downloadStockPDF() {
    // Create a temporary form to submit data for PDF generation
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '<?php echo site_url('purchase/downloadStockPDF') ?>';
    form.target = '_blank';
    
    // Add current filter values
    const sdate = document.getElementById('sdate').value;
    const edate = document.getElementById('edate').value;
    const category_id = document.getElementById('filter_item_category').value;
    const brand_id = document.getElementById('filter_item_brand').value;
    const supplier_id = document.getElementById('filter_supplier').value;
    
    const inputs = [
        {name: 'sdate', value: sdate},
        {name: 'edate', value: edate},
        {name: 'category_id', value: category_id},
        {name: 'brand_id', value: brand_id},
        {name: 'supplier_id', value: supplier_id}
    ];
    
    inputs.forEach(input => {
        const inputElement = document.createElement('input');
        inputElement.type = 'hidden';
        inputElement.name = input.name;
        inputElement.value = input.value;
        form.appendChild(inputElement);
    });
    
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}
</script>