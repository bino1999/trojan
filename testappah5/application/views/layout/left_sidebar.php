<?php
// Load auth helper for permission checking
$CI =& get_instance();
$CI->load->helper('auth');

// Helper function to check if current page matches a URL pattern
function isActivePage($patterns) {
    // Use full request URI (includes query params) for accurate matching
    $currentUrl = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : current_url();
    foreach ($patterns as $pattern) {
        if (strpos($currentUrl, $pattern) !== false) {
            return true;
        }
    }
    return false;
}

// Helper function to check if current page matches a specific URL
function isActiveUrl($url) {
    // Use full request URI (includes query params) for accurate matching
    $currentUrl = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : current_url();
    return strpos($currentUrl, $url) !== false;
}
?>

<div class="app-menu navbar-menu sidebar-enhanced">
    <!-- LOGO -->
    <div class="navbar-brand-box">
        <!-- Dark Logo-->
        <a href="<?php echo base_url(); ?>dashboard" class="logo logo-dark">
            <span class="logo-sm">
                <img src="<?php echo base_url(); ?>assets/images/logo.png" alt="" style="width: 40px; height: auto; object-fit: contain; display:block;">
            </span>
            <span class="logo-lg">
                <img src="<?php echo base_url(); ?>assets/images/logo.png" alt="Logo" style="height: 48px; width: auto; object-fit: contain; display:block;">
            </span>
        </a>
        <!-- Light Logo-->
        <a href="<?php echo base_url(); ?>dashboard" class="logo logo-light">
            <span class="logo-sm">
                <img src="<?php echo base_url(); ?>assets/images/logo.png" alt="" style="width: 40px; height: auto; object-fit: contain; display:block;">
            </span>
            <span class="logo-lg">
                <img src="<?php echo base_url(); ?>assets/images/logo.png" alt="" style="height: 48px; width: auto; object-fit: contain; display:block;">
            </span>
        </a>
        <button type="button" class="btn btn-sm p-0 fs-20 header-item float-end btn-vertical-sm-hover" id="vertical-hover">
            <i class="ri-record-circle-line"></i>
        </button>
    </div>

    <div id="scrollbar">
        <div class="container-fluid">
            <div id="two-column-menu"></div>
            <ul class="navbar-nav" id="navbar-nav">
                <!-- Work Base Category -->
                <li class="main-category">
                    <button class="main-category-header <?php 
                        $workbaseActive = isActivePage(['dashboard', 'quick-bill', 'internalBill', 'services-manage', 'invoices']);
                        echo $workbaseActive ? 'active' : '';
                    ?>" 
                            onclick="toggleCategory('workbase')">
                        <i class="fa fa-briefcase"></i>
                        <span>Work Base</span>
                        <i class="fa fa-chevron-down dropdown-arrow"></i>
                    </button>
                    <div class="submenu <?php echo $workbaseActive ? 'open' : ''; ?>" id="workbase">
                        <!-- Dashboard -->
                        <?php if (has_permission('dashboard.view')): ?>
                        <a href="<?php echo base_url('dashboard'); ?>" 
                           class="submenu-item <?php echo isActiveUrl('dashboard') ? 'active' : ''; ?>">
                            Dashboard
                        </a>
                        <?php endif; ?>
                        
                        <!-- Quick Bill Section -->
                        <?php if (has_permission('quickbill.view') || has_permission('quickbill.create')): ?>
                        <div class="submenu-section">
                            <div class="submenu-section-title">Quick Bill</div>
                            <?php if (has_permission('quickbill.create')): ?>
                                        <a href="<?php echo base_url('quick-bill'); ?>"
                               class="submenu-item <?php echo (isActiveUrl('quick-bill') && !isActiveUrl('quick-bill-list')) ? 'active' : ''; ?>">
                                Quick Bill
                            </a>
                            <?php endif; ?>
                            <?php if (has_permission('quickbill.view')): ?>
                                        <a href="<?php echo base_url('quick-bill-list'); ?>"
                               class="submenu-item <?php echo isActiveUrl('quick-bill-list') ? 'active' : ''; ?>">
                                Quick Bill List
                            </a>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Internal Use Section -->
                        <?php if (has_permission('internalbill.view') || has_permission('internalbill.create')): ?>
                        <div class="submenu-section">
                            <div class="submenu-section-title">Internal Use</div>
                            <?php if (has_permission('internalbill.create')): ?>
                            <a href="<?php echo base_url('internalBill'); ?>" 
                               class="submenu-item <?php echo (isActiveUrl('internalBill') && !isActiveUrl('internalBillList')) ? 'active' : ''; ?>">
                                Issue Note
                            </a>
                            <?php endif; ?>
                            <?php if (has_permission('internalbill.view')): ?>
                                        <a href="<?php echo base_url('internalBillList'); ?>"
                               class="submenu-item <?php echo isActiveUrl('internalBillList') ? 'active' : ''; ?>">
                                Issue Note List
                            </a>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Services Job Section -->
                        <?php if (has_permission('job.view')): ?>
                        <div class="submenu-section">
                            <div class="submenu-section-title">Services Job</div>
                            <a href="<?php echo base_url('services-manage?status=0'); ?>" 
                               class="submenu-item <?php echo (isActiveUrl('services-manage') && isActiveUrl('status=0')) ? 'active' : ''; ?>">
                                New Job
                            </a>
                                        <a href="<?php echo base_url('services-manage?status=1'); ?>"
                               class="submenu-item <?php echo (isActiveUrl('services-manage') && isActiveUrl('status=1')) ? 'active' : ''; ?>">
                                Ongoing Job
                            </a>
                                        <a href="<?php echo base_url('services-manage?status=4'); ?>"
                               class="submenu-item <?php echo (isActiveUrl('services-manage') && isActiveUrl('status=4') && !isActiveUrl('manager=yes')) ? 'active' : ''; ?>">
                                Completed Job
                            </a>
                                        <a href="<?php echo base_url('services-manage?status=2'); ?>"
                               class="submenu-item <?php echo (isActiveUrl('services-manage') && isActiveUrl('status=2')) ? 'active' : ''; ?>">
                                Hold Job
                            </a>
                                        <a href="<?php echo base_url('services-manage?status=3'); ?>"
                               class="submenu-item <?php echo (isActiveUrl('services-manage') && isActiveUrl('status=3')) ? 'active' : ''; ?>">
                                Cancelled Job
                            </a>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Job Approval -->
                        <?php if (has_permission('job.approve')): ?>
                        <a href="<?php echo base_url('services-manage?status=4&manager=yes'); ?>" 
                           class="submenu-item <?php echo (isActiveUrl('services-manage') && isActiveUrl('manager=yes')) ? 'active' : ''; ?>">
                            Job Approval
                        </a>
                        <?php endif; ?>
                        
                        <!-- Job Invoices -->
                        <?php if (has_permission('job.invoice')): ?>
                        <a href="<?php echo base_url('invoices'); ?>" 
                           class="submenu-item job-invoices-highlight <?php echo isActiveUrl('invoices') ? 'active' : ''; ?>">
                            Job Invoices
                        </a>
                        <?php endif; ?>
                    </div>
                    </li>

                <!-- Purchasing Category -->
                <?php if (has_permission('purchase.view') || has_permission('supplier.view') || has_permission('stock.view') || has_permission('products.view')): ?>
                <li class="main-category">
                    <button class="main-category-header <?php 
                        $purchasingActive = isActivePage(['supplier', 'purchase-manage', 'purchase-history', 'purchaseditem-history', 'item-stock', 'products']);
                        echo $purchasingActive ? 'active' : '';
                    ?>" 
                            onclick="toggleCategory('purchasing')">
                        <i class="fa fa-shopping-cart"></i>
                        <span>Purchasing</span>
                        <i class="fa fa-chevron-down dropdown-arrow"></i>
                    </button>
                    <div class="submenu <?php echo $purchasingActive ? 'open' : ''; ?>" id="purchasing">
                        <!-- Supplier -->
                        <?php if (has_permission('supplier.view')): ?>
                        <a href="<?php echo base_url('supplier'); ?>" 
                           class="submenu-item <?php echo isActiveUrl('supplier') ? 'active' : ''; ?>">
                            Supplier
                        </a>
                        <?php endif; ?>
                        
                        <!-- Purchase Order Section -->
                        <?php if (has_permission('purchase.view') || has_permission('stock.view')): ?>
                        <div class="submenu-section">
                            <div class="submenu-section-title">Purchase Order</div>
                            <?php if (has_permission('purchase.view')): ?>
                            <a href="<?php echo base_url('purchase-manage'); ?>" 
                               class="submenu-item <?php echo isActiveUrl('purchase-manage') ? 'active' : ''; ?>">
                                Purchase Invoices
                            </a>
                            <?php endif; ?>
                            <?php if (has_permission('purchase.view')): ?>
                                        <a href="<?php echo base_url('purchase-history'); ?>"
                               class="submenu-item <?php echo isActiveUrl('purchase-history') ? 'active' : ''; ?>">
                                Purchase Invoice History
                            </a>
                            <?php endif; ?>
                            <?php if (has_permission('purchase.view')): ?>
                                        <a href="<?php echo base_url('purchaseditem-history'); ?>"
                               class="submenu-item <?php echo isActiveUrl('purchaseditem-history') ? 'active' : ''; ?>">
                                Purchaseditem History
                            </a>
                            <?php endif; ?>
                            <?php if (has_permission('stock.view')): ?>
                                        <a href="<?php echo base_url('item-stock'); ?>"
                               class="submenu-item <?php echo isActiveUrl('item-stock') ? 'active' : ''; ?>">
                                Stock
                            </a>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Products -->
                        <?php if (has_permission('products.view')): ?>
                        <a href="<?php echo base_url('products'); ?>" 
                           class="submenu-item <?php echo isActiveUrl('products') ? 'active' : ''; ?>">
                            Products
                        </a>
                        <?php endif; ?>
                    </div>
                </li>
                <?php endif; ?>

                <!-- Wallet Category -->
                <?php if (has_permission(['payments.view','accounts.inhand','accounts.bank','accounts.banktransfer','accounts.cardmachine','accounts.cheque','accounts.credits','accounts.loans','expenses.view'])): ?>
                <li class="main-category">
                    <button class="main-category-header <?php echo (isset($mainMenuName) && in_array($mainMenuName, ['Payments', 'Accounts - In Hand', 'Accounts - Bank', 'Accounts - Bank Transfer', 'Accounts - Card Machine', 'Accounts - Cheque', 'Accounts - Credits', 'Credit Payment History', 'Accounts - Loan', 'Expenses'])) ? 'active' : ''; ?>" 
                            onclick="toggleCategory('wallet')">
                        <i class="fa fa-money"></i>
                        <span>Wallet</span>
                        <i class="fa fa-chevron-down dropdown-arrow"></i>
                    </button>
                    <div class="submenu <?php echo (isset($mainMenuName) && in_array($mainMenuName, ['Payments', 'Accounts - In Hand', 'Accounts - Bank', 'Accounts - Bank Transfer', 'Accounts - Card Machine', 'Accounts - Cheque', 'Accounts - Credits', 'Credit Payment History', 'Accounts - Loan', 'Expenses'])) ? 'open' : ''; ?>" id="wallet">
                        <!-- Payments -->
                        <?php if (has_permission('payments.view')): ?>
                        <a href="<?php echo base_url('payments'); ?>" 
                           class="submenu-item <?php echo (isset($mainMenuName) && $mainMenuName == 'Payments') ? 'active' : ''; ?>">
                            Payments
                        </a>
                        <?php endif; ?>
                        
                        <!-- In Hand -->
                        <?php if (has_permission('accounts.inhand')): ?>
                        <a href="<?php echo base_url('accounts/in-hand'); ?>" 
                           class="submenu-item <?php echo (isset($mainMenuName) && $mainMenuName == 'Accounts - In Hand') ? 'active' : ''; ?>">
                            In Hand
                        </a>
                        <?php endif; ?>
                        
                        <!-- Bank Section -->
                        <?php if (has_permission(['accounts.banktransfer','accounts.cardmachine','accounts.cheque'])): ?>
                        <div class="submenu-section">
                            <div class="submenu-section-title">Bank</div>
                            <?php if (has_permission('accounts.banktransfer')): ?>
                            <a href="<?php echo base_url('accounts/bank_transfer'); ?>" 
                               class="submenu-item <?php echo (isset($mainMenuName) && $mainMenuName == 'Accounts - Bank Transfer') ? 'active' : ''; ?>">
                                Bank Transfer
                            </a>
                            <?php endif; ?>
                            <?php if (has_permission('accounts.cardmachine')): ?>
                            <a href="<?php echo base_url('accounts/card_machine'); ?>" 
                               class="submenu-item <?php echo (isset($mainMenuName) && $mainMenuName == 'Accounts - Card Machine') ? 'active' : ''; ?>">
                                Card Machine
                            </a>
                            <?php endif; ?>
                            <?php if (has_permission('accounts.cheque')): ?>
                            <a href="<?php echo base_url('accounts/cheque'); ?>" 
                               class="submenu-item <?php echo (isset($mainMenuName) && $mainMenuName == 'Accounts - Cheque') ? 'active' : ''; ?>">
                                Cheque
                            </a>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Credit -->
                        <?php if (has_permission('accounts.credits')): ?>
                        <a href="<?php echo base_url('accounts/credits'); ?>" 
                           class="submenu-item <?php echo (isset($mainMenuName) && $mainMenuName == 'Accounts - Credits') ? 'active' : ''; ?>">
                            Credit
                        </a>
                        
                        <!-- Credit History -->
                        <a href="<?php echo base_url('accounts/credit_history'); ?>" 
                           class="submenu-item <?php echo (isset($mainMenuName) && $mainMenuName == 'Credit Payment History') ? 'active' : ''; ?>">
                            Credit History
                        </a>
                        <?php endif; ?>
                        
                        <!-- Loan -->
                        <?php if (has_permission('accounts.loans')): ?>
                        <a href="<?php echo base_url('accounts/loan'); ?>" 
                           class="submenu-item <?php echo (isset($mainMenuName) && $mainMenuName == 'Accounts - Loan') ? 'active' : ''; ?>">
                            Loan
                        </a>
                        <?php endif; ?>
                        
                        <!-- Expenses -->
                        <?php if (has_permission('expenses.view')): ?>
                        <a href="<?php echo base_url('expenses-manage'); ?>" 
                           class="submenu-item <?php echo (isset($mainMenuName) && $mainMenuName == 'Expenses') ? 'active' : ''; ?>">
                            Expenses
                        </a>
                        <?php endif; ?>
                    </div>
                </li>
                <?php endif; ?>

                <!-- General Category -->
                <?php if (has_permission(['serviceitems.view','servicepackages.view','employee.view','employee.attendance','employee.advance','settings.categories','settings.servicecategories','settings.brands','settings.vehiclecategories','settings.vehiclebrands','settings.expensecategories','settings.employeesections'])): ?>
                <li class="main-category">
                    <button class="main-category-header <?php 
                        $generalActive = isActivePage(['services-items-manage', 'services-packages-manage', 'employeeList', 'attendance', 'advance', 'category-manage', 'service-item-category-manage', 'brand-manage', 'vehicle-category-manage', 'vehicle-brand-manage', 'expenses-category-manage', 'employee-section-manage']);
                        echo $generalActive ? 'active' : '';
                    ?>" 
                            onclick="toggleCategory('general')">
                        <i class="fa fa-cog"></i>
                        <span>General</span>
                        <i class="fa fa-chevron-down dropdown-arrow"></i>
                    </button>
                    <div class="submenu <?php echo $generalActive ? 'open' : ''; ?>" id="general">
                        <!-- Service Packages Section -->
                        <?php if (has_permission(['serviceitems.view','servicepackages.view'])): ?>
                        <div class="submenu-section">
                            <div class="submenu-section-title">Service Packages</div>
                            <?php if (has_permission('serviceitems.view')): ?>
                            <a href="<?php echo base_url('services-items-manage'); ?>" 
                               class="submenu-item <?php echo isActiveUrl('services-items-manage') ? 'active' : ''; ?>">
                                Service Items
                            </a>
                            <?php endif; ?>
                            <?php if (has_permission('servicepackages.view')): ?>
                            <a href="<?php echo base_url('services-packages-manage'); ?>" 
                               class="submenu-item <?php echo isActiveUrl('services-packages-manage') ? 'active' : ''; ?>">
                                Service Packages
                            </a>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Members Section -->
                        <?php if (has_permission(['employee.view','employee.attendance','employee.advance'])): ?>
                        <div class="submenu-section">
                            <div class="submenu-section-title">Members</div>
                            <?php if (has_permission('employee.view')): ?>
                            <a href="<?php echo base_url('employeeList'); ?>" 
                               class="submenu-item <?php echo isActiveUrl('employeeList') ? 'active' : ''; ?>">
                                Employee List
                            </a>
                            <?php endif; ?>
                            <?php if (has_permission('employee.attendance')): ?>
                            <a href="<?php echo base_url('attendance'); ?>" 
                               class="submenu-item <?php echo isActiveUrl('attendance') ? 'active' : ''; ?>">
                                Attendant
                            </a>
                            <?php endif; ?>
                            <?php if (has_permission('employee.advance')): ?>
                            <a href="<?php echo base_url('advance'); ?>" 
                               class="submenu-item <?php echo isActiveUrl('advance') ? 'active' : ''; ?>">
                                Advance
                            </a>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Settings Section -->
                        <?php if (has_permission(['settings.categories','settings.servicecategories','settings.brands','settings.vehiclecategories','settings.vehiclebrands','settings.expensecategories','settings.employeesections'])): ?>
                        <div class="submenu-section">
                            <div class="submenu-section-title">Settings</div>
                            <?php if (has_permission('settings.categories')): ?>
                            <a href="<?php echo base_url('category-manage'); ?>" 
                               class="submenu-item <?php echo isActiveUrl('category-manage') ? 'active' : ''; ?>">
                                Item Categories
                            </a>
                            <?php endif; ?>
                            <?php if (has_permission('settings.servicecategories')): ?>
                            <a href="<?php echo base_url('service-item-category-manage'); ?>"
                               class="submenu-item <?php echo isActiveUrl('service-item-category-manage') ? 'active' : ''; ?>">
                                Service Item Categories
                            </a>
                            <?php endif; ?>
                            <?php if (has_permission('settings.brands')): ?>
                            <a href="<?php echo base_url('brand-manage'); ?>"
                               class="submenu-item <?php echo isActiveUrl('brand-manage') ? 'active' : ''; ?>">
                                Item Brands
                            </a>
                            <?php endif; ?>
                            <?php if (has_permission('settings.vehiclecategories')): ?>
                            <a href="<?php echo base_url('vehicle-category-manage'); ?>"
                               class="submenu-item <?php echo isActiveUrl('vehicle-category-manage') ? 'active' : ''; ?>">
                                Vehicle Categories
                            </a>
                            <?php endif; ?>
                            <?php if (has_permission('settings.vehiclebrands')): ?>
                            <a href="<?php echo base_url('vehicle-brand-manage'); ?>"
                               class="submenu-item <?php echo isActiveUrl('vehicle-brand-manage') ? 'active' : ''; ?>">
                                Vehicle Brands
                            </a>
                            <?php endif; ?>
                            <?php if (has_permission('settings.expensecategories')): ?>
                            <a href="<?php echo base_url('expenses-category-manage'); ?>"
                               class="submenu-item <?php echo isActiveUrl('expenses-category-manage') ? 'active' : ''; ?>">
                                Expenses Category
                            </a>
                            <?php endif; ?>
                            <?php if (has_permission('settings.employeesections')): ?>
                            <a href="<?php echo base_url('employee-section-manage'); ?>"
                               class="submenu-item <?php echo isActiveUrl('employee-section-manage') ? 'active' : ''; ?>">
                                Employee Section
                            </a>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </li>
                <?php endif; ?>

                <!-- Others Category -->
                <?php if (has_permission(['customers.view','vehicles.view'])): ?>
                <li class="main-category">
                    <button class="main-category-header <?php 
                        $othersActive = isActivePage(['customers', 'vehicles']);
                        echo $othersActive ? 'active' : '';
                    ?>" 
                            onclick="toggleCategory('others')">
                        <i class="fa fa-ellipsis-h"></i>
                        <span>Others</span>
                        <i class="fa fa-chevron-down dropdown-arrow"></i>
                    </button>
                    <div class="submenu <?php echo $othersActive ? 'open' : ''; ?>" id="others">
                        <?php if (has_permission('customers.view')): ?>
                        <a href="<?php echo base_url('customers'); ?>" 
                           class="submenu-item <?php echo isActiveUrl('customers') ? 'active' : ''; ?>">
                            Customers
                        </a>
                        <?php endif; ?>
                        <?php if (has_permission('vehicles.view')): ?>
                        <a href="<?php echo base_url('vehicles'); ?>" 
                           class="submenu-item <?php echo isActiveUrl('vehicles') ? 'active' : ''; ?>">
                            Vehicles
                        </a>
                        <?php endif; ?>
                    </div>
                </li>
                <?php endif; ?>

                <!-- Report Category -->
                <?php if (has_permission(['reports.view','reports.financial','reports.jobs','reports.inventory'])): ?>
                <li class="main-category">
                    <button class="main-category-header <?php 
                        $reportActive = isActivePage(['reports/daily', 'reports/monthly', 'reports/stock', 'reports/expenses']);
                        echo $reportActive ? 'active' : '';
                    ?>" 
                            onclick="toggleCategory('report')">
                        <i class="fa fa-bar-chart"></i>
                        <span>Report</span>
                        <i class="fa fa-chevron-down dropdown-arrow"></i>
                    </button>
                    <div class="submenu <?php echo $reportActive ? 'open' : ''; ?>" id="report">
                        <!-- All Report Section -->
                        <div class="submenu-section">
                            <div class="submenu-section-title">All Report</div>
                            <?php if (has_permission('reports.view')): ?>
                            <a href="<?php echo base_url('reports/daily'); ?>" 
                               class="submenu-item <?php echo isActiveUrl('reports/daily') ? 'active' : ''; ?>">
                                Custom Daily Report
                            </a>
                            <a href="<?php echo base_url('reports/monthly'); ?>" 
                               class="submenu-item <?php echo isActiveUrl('reports/monthly') ? 'active' : ''; ?>">
                                Custom Month Report
                            </a>
                            <?php endif; ?>
                            <?php if (has_permission('reports.inventory')): ?>
                            <a href="<?php echo base_url('reports/stock'); ?>" 
                               class="submenu-item <?php echo isActiveUrl('reports/stock') ? 'active' : ''; ?>">
                                Stock Report
                            </a>
                            <?php endif; ?>
                            <?php if (has_permission(['reports.view','reports.financial'])): ?>
                            <a href="<?php echo base_url('reports/expenses'); ?>" 
                               class="submenu-item <?php echo isActiveUrl('reports/expenses') ? 'active' : ''; ?>">
                                Expenses Report
                            </a>
                            <?php endif; ?>
                            <a href="javascript:void(0);" class="submenu-item">
                                Bank Report
                            </a>
                            <a href="javascript:void(0);" class="submenu-item">
                                Cash Book Report
                            </a>
                        </div>
                    </div>
                </li>
                <?php endif; ?>

                <!-- Admin Panel Category -->
                <?php if (has_permission(['admin.roles','admin.users','admin.settings'])): ?>
                <li class="main-category">
                    <button class="main-category-header <?php 
                        $adminActive = isActivePage(['system-role-manage', 'system-user-manage']);
                        echo $adminActive ? 'active' : '';
                    ?>" 
                            onclick="toggleCategory('admin')">
                        <i class="fa fa-shield"></i>
                        <span>Admin Panel</span>
                        <i class="fa fa-chevron-down dropdown-arrow"></i>
                    </button>
                    <div class="submenu <?php echo $adminActive ? 'open' : ''; ?>" id="admin">
                        <?php if (has_permission('admin.roles')): ?>
                        <a href="<?php echo base_url('system-role-manage'); ?>" 
                           class="submenu-item <?php echo isActiveUrl('system-role-manage') ? 'active' : ''; ?>">
                            System Access Level
                        </a>
                        <?php endif; ?>
                        <?php if (has_permission('admin.users')): ?>
                        <a href="<?php echo base_url('system-user-manage'); ?>" 
                           class="submenu-item <?php echo isActiveUrl('system-user-manage') ? 'active' : ''; ?>">
                            Employee Create
                        </a>
                        <?php endif; ?>
                    </div>
                </li>
                <?php endif; ?>

            </ul>
        </div>
    </div>

    <div class="sidebar-background"></div>
</div>

<script>
// Fallback JavaScript for sidebar functionality
function toggleCategory(categoryId) {
    console.log('Fallback toggleCategory called with:', categoryId);
    
    // Find the submenu
    const submenu = document.getElementById(categoryId);
    if (!submenu) {
        console.error('Submenu not found:', categoryId);
        return;
    }
    
    // Find the header
    const header = document.querySelector(`[onclick*="toggleCategory('${categoryId}')"]`);
    if (!header) {
        console.error('Header not found for:', categoryId);
        return;
    }
    
    // Close all other submenus
    const allSubmenus = document.querySelectorAll('.submenu');
    const allHeaders = document.querySelectorAll('.main-category-header');
    
    allSubmenus.forEach(menu => {
        if (menu.id !== categoryId) {
            menu.classList.remove('open');
        }
    });
    
    allHeaders.forEach(h => {
        if (h !== header) {
            h.classList.remove('active');
        }
    });
    
    // Toggle current category
    const isOpen = submenu.classList.contains('open');
    
    if (isOpen) {
        submenu.classList.remove('open');
        header.classList.remove('active');
        console.log('Closed:', categoryId);
    } else {
        submenu.classList.add('open');
        header.classList.add('active');
        console.log('Opened:', categoryId);
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    console.log('Sidebar fallback script loaded');
    
    // Open any categories that should be open based on PHP
    const activeCategories = document.querySelectorAll('.main-category-header.active');
    activeCategories.forEach(category => {
        const onclickAttr = category.getAttribute('onclick');
        if (onclickAttr) {
            const match = onclickAttr.match(/'([^']+)'/);
            if (match) {
                const categoryId = match[1];
                const submenu = document.getElementById(categoryId);
                if (submenu) {
                    submenu.classList.add('open');
                    console.log('Auto-opened category:', categoryId);
                }
            }
        }
    });
});
</script>

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <!-- Rest in fooer page -->