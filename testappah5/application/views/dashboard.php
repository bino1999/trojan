<!-- Dashboard Header with Welcome Animation -->
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between">
            <div class="welcome-section">
                <h2 class="fw-bold text-primary mb-1 animate-fade-in">
                    Welcome back, <?php echo $this->session->userdata('name'); ?>! 👋
                </h2>
                <p class="text-muted mb-0 animate-slide-up">
                    Here's what's happening with your service business today
                </p>
            </div>
            <div class="dashboard-actions animate-fade-in" data-delay="200">
                <button class="btn btn-outline-primary btn-sm me-2" onclick="refreshDashboard()">
                    <i class="mdi mdi-refresh"></i> Refresh
                </button>
                <div class="dropdown">
                    <button class="btn btn-primary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="mdi mdi-download"></i> Export
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#" onclick="exportData('pdf')">PDF Report</a></li>
                        <li><a class="dropdown-item" href="#" onclick="exportData('excel')">Excel Report</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Key Metrics Cards with Animations -->
<div class="row mb-4">
    <!-- Revenue Card -->
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card metric-card revenue-card animate-scale-in" data-delay="100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="metric-icon bg-success">
                        <i class="mdi mdi-currency-usd"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="text-muted mb-1">Total Revenue</h6>
                        <h3 class="mb-0 fw-bold text-success">
                            LKR <?php echo number_format($total_revenue ?? 0, 2); ?>
                        </h3>
                        <small class="text-success">
                            <i class="mdi mdi-trending-up"></i> +12.5% from last month
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Today's Revenue Card -->
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card metric-card today-revenue-card animate-scale-in" data-delay="200">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="metric-icon bg-primary">
                        <i class="mdi mdi-calendar-today"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="text-muted mb-1">Today's Revenue</h6>
                        <h3 class="mb-0 fw-bold text-primary">
                            LKR 15,250.00
                        </h3>
                        <small class="text-primary">
                            <i class="mdi mdi-clock-outline"></i> <?php echo date('M d, Y'); ?>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Jobs Card -->
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card metric-card jobs-card animate-scale-in" data-delay="300">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="metric-icon bg-info">
                        <i class="mdi mdi-wrench"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="text-muted mb-1">Total Jobs</h6>
                        <h3 class="mb-0 fw-bold text-info">
                            <?php echo number_format($job_count ?? 0); ?>
                        </h3>
                        <small class="text-info">
                            <i class="mdi mdi-plus-circle"></i> 8 today
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Customers Card -->
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card metric-card customers-card animate-scale-in" data-delay="400">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="metric-icon bg-warning">
                        <i class="mdi mdi-account-group"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="text-muted mb-1">Customers</h6>
                        <h3 class="mb-0 fw-bold text-warning">
                            <?php echo number_format($customer_count ?? 0); ?>
                        </h3>
                        <small class="text-warning">
                            <i class="mdi mdi-account-plus"></i> Active clients
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Secondary Metrics Row -->
<div class="row mb-4">
    <!-- Vehicles Card -->
    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
        <div class="card metric-card-small vehicles-card animate-fade-in" data-delay="500">
            <div class="card-body text-center">
                <div class="metric-icon-small bg-secondary">
                    <i class="mdi mdi-truck"></i>
                </div>
                <h5 class="fw-bold text-secondary mb-1"><?php echo $vehicle_count ?? 0; ?></h5>
                <small class="text-muted">Vehicles</small>
            </div>
        </div>
    </div>

    <!-- Active Users Card -->
    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
        <div class="card metric-card-small users-card animate-fade-in" data-delay="600">
            <div class="card-body text-center">
                <div class="metric-icon-small bg-info">
                    <i class="mdi mdi-account"></i>
                </div>
                <h5 class="fw-bold text-info mb-1">12</h5>
                <small class="text-muted">Active Users</small>
            </div>
        </div>
    </div>

    <!-- Monthly Revenue Card -->
    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
        <div class="card metric-card-small monthly-revenue-card animate-fade-in" data-delay="700">
            <div class="card-body text-center">
                <div class="metric-icon-small bg-success">
                    <i class="mdi mdi-chart-line"></i>
                </div>
                <h5 class="fw-bold text-success mb-1">LKR 125,000</h5>
                <small class="text-muted">This Month</small>
            </div>
        </div>
    </div>

    <!-- Monthly Expenses Card -->
    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
        <div class="card metric-card-small monthly-expenses-card animate-fade-in" data-delay="800">
            <div class="card-body text-center">
                <div class="metric-icon-small bg-danger">
                    <i class="mdi mdi-credit-card"></i>
                </div>
                <h5 class="fw-bold text-danger mb-1">LKR 45,000</h5>
                <small class="text-muted">This Month</small>
            </div>
        </div>
    </div>

    <!-- Attendance Card -->
    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
        <div class="card metric-card-small attendance-card animate-fade-in" data-delay="900">
            <div class="card-body text-center">
                <div class="metric-icon-small bg-primary">
                    <i class="mdi mdi-calendar-check"></i>
                </div>
                <h5 class="fw-bold text-primary mb-1">95%</h5>
                <small class="text-muted">Attendance</small>
            </div>
        </div>
    </div>

    <!-- Profit Card -->
    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
        <div class="card metric-card-small profit-card animate-fade-in" data-delay="1000">
            <div class="card-body text-center">
                <div class="metric-icon-small bg-success">
                    <i class="mdi mdi-trending-up"></i>
                </div>
                <h5 class="fw-bold text-success mb-1">LKR 80,000</h5>
                <small class="text-muted">Net Profit</small>
            </div>
        </div>
    </div>
</div>

<!-- Charts and Analytics Section -->
<div class="row mb-4">
    <!-- Revenue vs Expenses Chart -->
    <div class="col-lg-8 mb-4">
        <div class="card chart-card animate-slide-up" data-delay="1100">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="mdi mdi-chart-line text-primary"></i>
                    Revenue vs Expenses (Last 30 Days)
                </h5>
                <div class="chart-controls">
                    <select class="form-select form-select-sm" id="chartPeriod">
                        <option value="7">Last 7 Days</option>
                        <option value="30" selected>Last 30 Days</option>
                        <option value="90">Last 90 Days</option>
                    </select>
                </div>
            </div>
            <div class="card-body">
                <div id="revenueChart" style="height: 300px;">
                    <div class="d-flex align-items-center justify-content-center h-100">
                        <div class="text-center text-muted">
                            <i class="mdi mdi-chart-line" style="font-size: 3rem;"></i>
                            <p class="mt-2">Revenue vs Expenses Chart</p>
                            <small>Interactive chart will be implemented</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Methods Pie Chart -->
    <div class="col-lg-4 mb-4">
        <div class="card chart-card animate-slide-up" data-delay="1200">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="mdi mdi-chart-pie text-info"></i>
                    Payment Methods
                </h5>
            </div>
            <div class="card-body">
                <div id="paymentChart" style="height: 300px;">
                    <div class="d-flex align-items-center justify-content-center h-100">
                        <div class="text-center text-muted">
                            <i class="mdi mdi-chart-pie" style="font-size: 3rem;"></i>
                            <p class="mt-2">Payment Methods</p>
                            <small>Pie chart will be implemented</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity Tables -->
<div class="row mb-4">
    <!-- Recent Jobs -->
    <div class="col-lg-4 mb-4">
        <div class="card animate-slide-up" data-delay="1300">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="mdi mdi-wrench text-primary"></i>
                    Recent Jobs
                </h5>
                <a href="#" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Job #</th>
                                <th>Customer</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><span class="badge bg-primary">#001</span></td>
                                <td>
                                    <div>
                                        <div class="fw-semibold">John Smith</div>
                                        <small class="text-muted">+94 77 123 4567</small>
                                    </div>
                                </td>
                                <td><span class="badge bg-success">Completed</span></td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-primary">#002</span></td>
                                <td>
                                    <div>
                                        <div class="fw-semibold">Sarah Johnson</div>
                                        <small class="text-muted">+94 77 234 5678</small>
                                    </div>
                                </td>
                                <td><span class="badge bg-warning">In Progress</span></td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-primary">#003</span></td>
                                <td>
                                    <div>
                                        <div class="fw-semibold">Mike Wilson</div>
                                        <small class="text-muted">+94 77 345 6789</small>
                                    </div>
                                </td>
                                <td><span class="badge bg-secondary">Pending</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Payments -->
    <div class="col-lg-4 mb-4">
        <div class="card animate-slide-up" data-delay="1400">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="mdi mdi-currency-usd text-success"></i>
                    Recent Payments
                </h5>
                <a href="#" class="btn btn-sm btn-outline-success">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Customer</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <div class="fw-semibold text-success">LKR 15,250.00</div>
                                </td>
                                <td><span class="badge bg-success">Cash</span></td>
                                <td><div class="fw-semibold">John Smith</div></td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="fw-semibold text-success">LKR 8,500.00</div>
                                </td>
                                <td><span class="badge bg-info">Card</span></td>
                                <td><div class="fw-semibold">Sarah Johnson</div></td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="fw-semibold text-success">LKR 12,000.00</div>
                                </td>
                                <td><span class="badge bg-warning">Bank Transfer</span></td>
                                <td><div class="fw-semibold">Mike Wilson</div></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Expenses -->
    <div class="col-lg-4 mb-4">
        <div class="card animate-slide-up" data-delay="1500">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="mdi mdi-credit-card text-danger"></i>
                    Recent Expenses
                </h5>
                <a href="#" class="btn btn-sm btn-outline-danger">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Amount</th>
                                <th>Category</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <div class="fw-semibold text-danger">LKR 2,500.00</div>
                                </td>
                                <td><span class="badge bg-light text-dark">Supplies</span></td>
                                <td><small class="text-muted">Dec 15</small></td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="fw-semibold text-danger">LKR 1,800.00</div>
                                </td>
                                <td><span class="badge bg-light text-dark">Utilities</span></td>
                                <td><small class="text-muted">Dec 14</small></td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="fw-semibold text-danger">LKR 3,200.00</div>
                                </td>
                                <td><span class="badge bg-light text-dark">Equipment</span></td>
                                <td><small class="text-muted">Dec 13</small></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions and System Status -->
<div class="row">
    <!-- Quick Actions -->
    <div class="col-lg-6 mb-4">
        <div class="card animate-slide-up" data-delay="1600">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="mdi mdi-lightning-bolt text-warning"></i>
                    Quick Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <a href="#" class="btn btn-outline-primary w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3 quick-action-btn">
                            <i class="mdi mdi-plus-circle mb-2" style="font-size: 1.5rem;"></i>
                            <span>New Job</span>
                        </a>
                    </div>
                    <div class="col-md-6">
                        <a href="#" class="btn btn-outline-success w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3 quick-action-btn">
                            <i class="mdi mdi-account-plus mb-2" style="font-size: 1.5rem;"></i>
                            <span>Add Customer</span>
                        </a>
                    </div>
                    <div class="col-md-6">
                        <a href="#" class="btn btn-outline-info w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3 quick-action-btn">
                            <i class="mdi mdi-truck-plus mb-2" style="font-size: 1.5rem;"></i>
                            <span>Add Vehicle</span>
                        </a>
                    </div>
                    <div class="col-md-6">
                        <a href="#" class="btn btn-outline-warning w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3 quick-action-btn">
                            <i class="mdi mdi-credit-card-plus mb-2" style="font-size: 1.5rem;"></i>
                            <span>Record Payment</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- System Status -->
    <div class="col-lg-6 mb-4">
        <div class="card animate-slide-up" data-delay="1700">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="mdi mdi-monitor-dashboard text-info"></i>
                    System Status
                </h5>
            </div>
            <div class="card-body">
                <div class="status-item">
                    <div class="d-flex align-items-center justify-content-between">
                        <span>Database Connection</span>
                        <span class="badge bg-success">Online</span>
                    </div>
                </div>
                <div class="status-item">
                    <div class="d-flex align-items-center justify-content-between">
                        <span>Server Status</span>
                        <span class="badge bg-success">Running</span>
                    </div>
                </div>
                <div class="status-item">
                    <div class="d-flex align-items-center justify-content-between">
                        <span>Last Backup</span>
                        <span class="badge bg-info">2 hours ago</span>
                    </div>
                </div>
                <div class="status-item">
                    <div class="d-flex align-items-center justify-content-between">
                        <span>Active Sessions</span>
                        <span class="badge bg-primary">12</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Enhanced CSS with Beautiful Animations -->
<style>
/* Animation Classes */
.animate-fade-in {
    animation: fadeIn 0.8s ease-in-out;
}

.animate-slide-up {
    animation: slideUp 0.8s ease-out;
}

.animate-scale-in {
    animation: scaleIn 0.6s ease-out;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideUp {
    from { 
        opacity: 0; 
        transform: translateY(30px); 
    }
    to { 
        opacity: 1; 
        transform: translateY(0); 
    }
}

@keyframes scaleIn {
    from { 
        opacity: 0; 
        transform: scale(0.9); 
    }
    to { 
        opacity: 1; 
        transform: scale(1); 
    }
}

/* Enhanced Cards */
.metric-card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
    overflow: hidden;
    position: relative;
}

.metric-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 30px rgba(0,0,0,0.15);
}

.metric-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #007bff, #28a745);
}

.metric-icon {
    width: 60px;
    height: 60px;
    border-radius: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: white;
    position: relative;
    overflow: hidden;
}

.metric-icon::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: linear-gradient(45deg, transparent, rgba(255,255,255,0.2), transparent);
    transform: rotate(45deg);
    animation: shimmer 3s infinite;
}

@keyframes shimmer {
    0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
    100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
}

.metric-card-small {
    border: none;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.06);
    transition: all 0.3s ease;
}

.metric-card-small:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 20px rgba(0,0,0,0.12);
}

.metric-icon-small {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    color: white;
    margin: 0 auto 10px;
}

/* Chart Cards */
.chart-card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
}

.chart-card:hover {
    box-shadow: 0 8px 30px rgba(0,0,0,0.15);
}

.chart-controls {
    display: flex;
    gap: 10px;
}

/* Status Items */
.status-item {
    padding: 10px 0;
    border-bottom: 1px solid #f0f0f0;
}

.status-item:last-child {
    border-bottom: none;
}

/* Quick Actions */
.quick-action-btn {
    border-radius: 10px;
    transition: all 0.3s ease;
    text-decoration: none;
}

.quick-action-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    text-decoration: none;
}

/* Welcome Section */
.welcome-section h2 {
    background: linear-gradient(45deg, #007bff, #28a745);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

/* Responsive Design */
@media (max-width: 768px) {
    .welcome-section h2 {
        font-size: 1.5rem;
    }
    
    .dashboard-actions {
        margin-top: 15px;
    }
    
    .metric-card {
        margin-bottom: 15px;
    }
}

/* Loading Animation */
.loading {
    position: relative;
    overflow: hidden;
}

.loading::after {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
    animation: loading 1.5s infinite;
}

@keyframes loading {
    0% { left: -100%; }
    100% { left: 100%; }
}
</style>

<!-- JavaScript for Dashboard Functionality -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize animations with delays
    const animatedElements = document.querySelectorAll('[data-delay]');
    animatedElements.forEach(element => {
        const delay = parseInt(element.dataset.delay);
        setTimeout(() => {
            element.style.opacity = '1';
            element.style.transform = 'translateY(0) scale(1)';
        }, delay);
    });
    
    // Set up auto-refresh
    setInterval(refreshDashboard, 300000); // Refresh every 5 minutes
});

function refreshDashboard() {
    // Add loading animation
    const cards = document.querySelectorAll('.metric-card, .chart-card');
    cards.forEach(card => {
        card.classList.add('loading');
    });

    // Reload the page
    setTimeout(() => {
        window.location.reload();
    }, 1000);
}

function exportData(format) {
    // Implement export functionality
    alert('Export to ' + format.toUpperCase() + ' functionality will be implemented');
}

// Add hover effects to cards
document.querySelectorAll('.metric-card, .metric-card-small').forEach(card => {
    card.addEventListener('mouseenter', function() {
        this.style.transform = 'translateY(-5px) scale(1.02)';
    });
    
    card.addEventListener('mouseleave', function() {
        this.style.transform = 'translateY(0) scale(1)';
    });
});
</script>