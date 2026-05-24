<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Stock Update Tool</h4>
        </div>
    </div>
</div>


<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa fa-search"></i></span>
                            <input type="text" id="searchInput" class="form-control" placeholder="Search by product name, SKU, or barcode...">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <button class="btn btn-info" onclick="clearSearch();">
                            <i class="fa fa-times"></i> Clear Search
                        </button>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <select id="filterBrand" name="filterBrand" class="form-select">
                            <option value="">All Brands</option>
                            <?php foreach ($brands as $brand): ?>
                                <option value="<?= $brand->itemBrandId; ?>"><?= $brand->itemBrandName; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select id="filterCategory" name="filterCategory" class="form-select">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category->itemCategoryId; ?>"><?= $category->itemCategoryName; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-primary" onclick="filterProducts();">
                            <i class="fa fa-filter"></i> Filter Products
                        </button>
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-success btn-lg" onclick="saveAllChanges();">
                            <i class="fa fa-save"></i> Save All Changes
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <form id="stockUpdateForm">
                    <div id="productsList">
                        <?php $this->load->view('stock/products-list', ['products' => $products, 'current_page' => $current_page, 'total_pages' => $total_pages, 'total_products' => $total_products]); ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<script>
function filterProducts() {
    const filterBrand = document.getElementById('filterBrand').value;
    const filterCategory = document.getElementById('filterCategory').value;
    const searchTerm = document.getElementById('searchInput').value;
    
    $.ajax({
        url: base_url + 'StockUpdate/filterProducts',
        type: 'POST',
        data: {
            filterBrand: filterBrand,
            filterCategory: filterCategory,
            searchTerm: searchTerm
        },
        success: function(response) {
            $('#productsList').html(response);
            initStockTableSorting();
        },
        error: function() {
            Toastify({
                text: "Error filtering products",
                className: "error",
                duration: 3000
            }).showToast();
        }
    });
}

function clearSearch() {
    document.getElementById('searchInput').value = '';
    filterProducts();
}

            // Add real-time search functionality
            document.addEventListener('DOMContentLoaded', function() {
                const searchInput = document.getElementById('searchInput');
                if (searchInput) {
                    let searchTimeout;
                    searchInput.addEventListener('input', function() {
                        clearTimeout(searchTimeout);
                        searchTimeout = setTimeout(function() {
                            filterProducts(); // Refresh when typing
                        }, 500); // Search after 500ms of no typing
                    });
                }
            
            // Add price validation
            document.addEventListener('input', function(e) {
                if (e.target.name && e.target.name.includes('sale_price')) {
                    validateSalePrice(e.target);
                }
                if (e.target.name && e.target.name.includes('company_price')) {
                    validateCompanyPrice(e.target);
                }
            });
            
            // Add event listeners for filter changes
            document.getElementById('filterBrand').addEventListener('change', function() {
                filterProducts(); // Refresh when filtering
            });
            document.getElementById('filterCategory').addEventListener('change', function() {
                filterProducts(); // Refresh when filtering
            });

    initStockTableSorting();
        });

const STOCK_TABLE_NUMERIC_COLS = [0, 7, 8, 9];

function initStockTableSorting() {
    const productsList = document.getElementById('productsList');
    if (!productsList) return;
    const table = productsList.querySelector('table');
    if (!table) return;
    const headers = table.querySelectorAll('thead th');

    headers.forEach((th, index) => {
        // Skip if header already initialised in this render
        th.classList.add('sortable-header');
        th.setAttribute('data-sort', 'none');
        th.onclick = () => sortStockTable(table, th, index);
    });
}

function sortStockTable(table, header, columnIndex) {
    const tbody = table.tBodies[0];
    if (!tbody) return;

    const currentState = header.getAttribute('data-sort') || 'none';
    const newState = currentState === 'asc' ? 'desc' : 'asc';

    // Reset other headers
    table.querySelectorAll('thead th').forEach(th => {
        if (th !== header) th.setAttribute('data-sort', 'none');
    });
    header.setAttribute('data-sort', newState);

    const rows = Array.from(tbody.querySelectorAll('tr'));
    rows.sort((rowA, rowB) => {
        const aVal = getStockCellValue(rowA, columnIndex);
        const bVal = getStockCellValue(rowB, columnIndex);
        const isNumeric = STOCK_TABLE_NUMERIC_COLS.includes(columnIndex);

        if (isNumeric) {
            const aNum = parseFloat(aVal) || 0;
            const bNum = parseFloat(bVal) || 0;
            return newState === 'asc' ? aNum - bNum : bNum - aNum;
        }

        const aStr = (aVal || '').toString().toUpperCase();
        const bStr = (bVal || '').toString().toUpperCase();
        if (aStr < bStr) return newState === 'asc' ? -1 : 1;
        if (aStr > bStr) return newState === 'asc' ? 1 : -1;
        return 0;
    });

    rows.forEach(row => tbody.appendChild(row));
}

function getStockCellValue(row, columnIndex) {
    const cell = row.children[columnIndex];
    if (!cell) return '';

    const input = cell.querySelector('input');
    if (input) return input.value;

    const select = cell.querySelector('select');
    if (select) {
        const selectedOption = select.options[select.selectedIndex];
        return selectedOption ? selectedOption.text : select.value;
    }

    return cell.textContent.trim();
}

function saveAllChanges() {
    // Collect all form data
    const formData = new FormData(document.getElementById('stockUpdateForm'));
    
    // Show loading state
    const saveBtn = document.querySelector('button[onclick="saveAllChanges()"]');
    if (saveBtn) {
        const originalText = saveBtn.innerHTML;
        saveBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Saving...';
        saveBtn.disabled = true;
    }
    
    $.ajax({
        url: base_url + 'StockUpdate/saveBulkUpdates',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            const result = JSON.parse(response);
            if (result.status === 'success') {
                Toastify({
                    text: result.message,
                    className: "success",
                    duration: 3000
                }).showToast();
                
                // Refresh the list to show updated data
                filterProducts();
            } else {
                Toastify({
                    text: result.message,
                    className: "error",
                    duration: 3000
                }).showToast();
            }
        },
        error: function() {
            Toastify({
                text: "Error saving changes",
                className: "error",
                duration: 3000
            }).showToast();
        },
        complete: function() {
            // Restore button state
            if (saveBtn) {
                saveBtn.innerHTML = originalText;
                saveBtn.disabled = false;
            }
        }
    });
}

// Add visual feedback for changed fields and PO validation
document.addEventListener('DOMContentLoaded', function() {
    // Add change event listeners to all form inputs
    document.addEventListener('change', function(e) {
        if (e.target.matches('input, select')) {
            e.target.classList.add('border-warning');
        }
    });
    
    // Add input event listeners for real-time feedback
    document.addEventListener('input', function(e) {
        if (e.target.matches('input[type="number"]')) {
            e.target.classList.add('border-warning');
        }
    });

    // PO validation for stock increases
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('stock-input')) {
            validateStockAndPO(e.target);
        }
    });

    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('po-select')) {
            validateStockAndPO(e.target);
        }
    });
});

function validateStockAndPO(element) {
    const productId = element.getAttribute('data-product-id');
    const stockInput = document.querySelector(`input[name="total_stock[${productId}]"]`);
    const poSelect = document.querySelector(`select[name="po_id[${productId}]"]`);
    
    if (stockInput && poSelect) {
        const stockValue = parseFloat(stockInput.value) || 0;
        const poValue = poSelect.value;
        
        // Remove previous warnings
        poSelect.classList.remove('border-danger', 'border-warning');
        
        // Check if stock is being increased from 0 and no PO is selected
        if (stockValue > 0 && !poValue) {
            poSelect.classList.add('border-danger');
            poSelect.style.backgroundColor = '#ffebee';
            
            // Add tooltip or warning
            if (!poSelect.title) {
                poSelect.title = '⚠️ Please select a Purchase Order when adding stock!';
            }
        } else {
            poSelect.style.backgroundColor = '';
            poSelect.title = '';
        }
    }
}

// Price validation functions
function validateSalePrice(salePriceInput) {
    const productId = salePriceInput.name.match(/\[(\d+)\]/)[1];
    const companyPriceInput = document.querySelector(`input[name="company_price[${productId}]"]`);
    
    if (salePriceInput.value && companyPriceInput.value) {
        const salePrice = parseFloat(salePriceInput.value);
        const companyPrice = parseFloat(companyPriceInput.value);
        
        if (salePrice < companyPrice) {
            salePriceInput.classList.add('border-danger');
            salePriceInput.title = '⚠️ Sale price cannot be lower than company price!';
        } else {
            salePriceInput.classList.remove('border-danger');
            salePriceInput.title = '';
        }
    }
}

function validateCompanyPrice(companyPriceInput) {
    const productId = companyPriceInput.name.match(/\[(\d+)\]/)[1];
    const salePriceInput = document.querySelector(`input[name="sale_price[${productId}]"]`);
    
    if (salePriceInput.value && companyPriceInput.value) {
        const salePrice = parseFloat(salePriceInput.value);
        const companyPrice = parseFloat(companyPriceInput.value);
        
        if (salePrice < companyPrice) {
            salePriceInput.classList.add('border-danger');
            salePriceInput.title = '⚠️ Sale price cannot be lower than company price!';
        } else {
            salePriceInput.classList.remove('border-danger');
            salePriceInput.title = '';
        }
    }
}
</script>

<style>
    .border-warning {
        border-color: #ffc107 !important;
    }
    .border-danger {
        border-color: #dc3545 !important;
    }
    
    /* Sticky table header styles */
    .table thead th {
        position: sticky;
        top: 0;
        z-index: 10;
        background-color: #212529 !important;
        border-bottom: 2px solid #dee2e6;
    }
    
    /* Add subtle shadow to sticky header */
    .table thead th::after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        right: 0;
        height: 2px;
        background: linear-gradient(to bottom, rgba(0,0,0,0.1), transparent);
    }
    
    /* Ensure table container has proper scrolling */
    .table-responsive {
        max-height: 70vh;
        overflow-y: auto;
    }

    .sortable-header {
        cursor: pointer;
        user-select: none;
        position: relative;
    }

    .sortable-header::after {
        content: '';
        font-size: 0.75rem;
        margin-left: 4px;
    }

    .sortable-header[data-sort="asc"]::after {
        content: '▲';
    }

    .sortable-header[data-sort="desc"]::after {
        content: '▼';
    }
</style>
