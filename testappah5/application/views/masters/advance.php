<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Advance Management</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="<?php echo base_url(); ?>">Home</a></li>
                    <li class="breadcrumb-item active">Advance</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Employee Advance Calculation</h4>
            </div>
            <div class="card-body">
                <!-- Month/Year Selection -->
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label for="monthSelect" class="form-label">Select Month</label>
                        <select class="form-select" id="monthSelect">
                            <option value="1" <?= $month == 1 ? 'selected' : '' ?>>January</option>
                            <option value="2" <?= $month == 2 ? 'selected' : '' ?>>February</option>
                            <option value="3" <?= $month == 3 ? 'selected' : '' ?>>March</option>
                            <option value="4" <?= $month == 4 ? 'selected' : '' ?>>April</option>
                            <option value="5" <?= $month == 5 ? 'selected' : '' ?>>May</option>
                            <option value="6" <?= $month == 6 ? 'selected' : '' ?>>June</option>
                            <option value="7" <?= $month == 7 ? 'selected' : '' ?>>July</option>
                            <option value="8" <?= $month == 8 ? 'selected' : '' ?>>August</option>
                            <option value="9" <?= $month == 9 ? 'selected' : '' ?>>September</option>
                            <option value="10" <?= $month == 10 ? 'selected' : '' ?>>October</option>
                            <option value="11" <?= $month == 11 ? 'selected' : '' ?>>November</option>
                            <option value="12" <?= $month == 12 ? 'selected' : '' ?>>December</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="yearSelect" class="form-label">Select Year</label>
                        <select class="form-select" id="yearSelect">
                            <?php 
                            $currentYear = date('Y');
                            for ($i = $currentYear - 2; $i <= $currentYear + 1; $i++): 
                            ?>
                                <option value="<?= $i ?>" <?= $i == $year ? 'selected' : '' ?>><?= $i ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <button type="button" class="btn btn-primary d-block" id="loadAdvanceBtn">Load Data</button>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h5 class="card-title">Total Users</h5>
                                <h3 id="totalUsers"><?= $summary['total_users'] ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <h5 class="card-title">Max Allowed</h5>
                                <h3 id="totalMaxAllowed">LKR <?= number_format($summary['total_max_allowed'], 2) ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h5 class="card-title">Actual Allowed</h5>
                                <h3 id="totalActualAllowed">LKR <?= number_format($summary['total_actual_allowed'], 2) ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <h5 class="card-title">Remaining</h5>
                                <h3 id="totalRemaining">LKR <?= number_format($summary['total_remaining'], 2) ?></h3>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Users Table -->
                <div class="table-responsive">
                    <table id="advanceTable" class="table table-striped table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th>Employee</th>
                                <th>Salary</th>
                                <th>Max Allowed (40%)</th>
                                <th>Per Day Salary</th>
                                <th>Present Days</th>
                                <th>Working Days</th>
                                <th>Off Days</th>
                                <th>Attendance %</th>
                                <th>Actual Allowed</th>
                                <th>Advance Taken</th>
                                <th>Remaining</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (isset($users) && is_array($users)): ?>
                                <?php foreach ($users as $user): ?>
                                <tr>
                                    <td>
                                        <strong><?= isset($user->FirstName) ? $user->FirstName : 'N/A' ?> <?= isset($user->LastName) ? $user->LastName : 'N/A' ?></strong><br>
                                        <small class="text-muted"><?= isset($user->UserName) ? $user->UserName : 'N/A' ?></small><br>
                                        <small class="text-info"><?= isset($user->employeeSectionName) ? $user->employeeSectionName : 'N/A' ?></small>
                                    </td>
                                    <td class="text-end">LKR <?= isset($user->salary) ? number_format($user->salary, 2) : '0.00' ?></td>
                                    <td class="text-end">LKR <?= isset($user->max_allowed_advance) ? number_format($user->max_allowed_advance, 2) : '0.00' ?></td>
                                    <td class="text-end">LKR <?= isset($user->per_day_salary) ? number_format($user->per_day_salary, 2) : '0.00' ?></td>
                                    <td class="text-center">
                                        <span class="badge bg-success"><?= isset($user->present_days) ? $user->present_days : '0' ?></span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-info"><?= isset($user->working_days) ? $user->working_days : '0' ?></span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-warning text-dark"><?= isset($user->off_days) ? $user->off_days : '0' ?></span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge <?= (isset($user->attendance_percentage) && $user->attendance_percentage >= 80) ? 'bg-success' : ((isset($user->attendance_percentage) && $user->attendance_percentage >= 60) ? 'bg-warning' : 'bg-danger') ?>">
                                            <?= isset($user->attendance_percentage) ? $user->attendance_percentage : '0' ?>%
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <strong>LKR <?= isset($user->actual_allowed_advance) ? number_format($user->actual_allowed_advance, 2) : '0.00' ?></strong>
                                    </td>
                                    <td class="text-end">LKR <?= isset($user->total_advance_taken) ? number_format($user->total_advance_taken, 2) : '0.00' ?></td>
                                    <td class="text-end">
                                        <span class="badge <?= (isset($user->remaining_advance) && $user->remaining_advance > 0) ? 'bg-success' : 'bg-danger' ?>">
                                            LKR <?= isset($user->remaining_advance) ? number_format($user->remaining_advance, 2) : '0.00' ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="11" class="text-center text-muted">
                                        No employees with salary information found. Please ensure employees have salary amounts set.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Calculation Info -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="alert alert-info">
                            <h6><i class="fa fa-info-circle"></i> Calculation Formula (Updated with Off Days):</h6>
                            <ul class="mb-0">
                                <li><strong>Maximum Allowed Advance</strong> = 40% of Employee Salary</li>
                                <li><strong>Working Days</strong> = Total Days in Month - Off Days (Company Holidays + Personal Off Days)</li>
                                <li><strong>Per Day Salary</strong> = Maximum Allowed Advance ÷ Working Days</li>
                                <li><strong>Actual Allowed Advance</strong> = Per Day Salary × Present Days</li>
                                <li><strong>Attendance Percentage</strong> = (Present Days ÷ Working Days) × 100</li>
                                <li><strong>Remaining Advance</strong> = Actual Allowed Advance - Advance Taken</li>
                            </ul>
                            <small class="text-muted">
                                <i class="fa fa-lightbulb"></i> <strong>Note:</strong> Off days are automatically excluded from advance calculations. 
                                This ensures fair advance calculations based on actual working days rather than total calendar days.
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
$(document).ready(function() {
    // Initialize DataTable
    $('#advanceTable').DataTable({
        "lengthMenu": [[25, 50, 100, -1], [25, 50, 100, "All"]],
        "pageLength": 25,
        "order": [[7, "desc"]], // Sort by Actual Allowed descending
        "columnDefs": [
            { "orderable": false, "targets": [0] } // Disable sorting on Employee column
        ]
    });

    // Load data when month/year changes
    $('#loadAdvanceBtn').click(function() {
        const month = $('#monthSelect').val();
        const year = $('#yearSelect').val();
        
        if (!month || !year) {
            Swal.fire('Error!', 'Please select month and year.', 'error');
            return;
        }

        // Reload page with new parameters
        window.location.href = `<?php echo base_url('advance'); ?>?month=${month}&year=${year}`;
    });

    // Auto-load when month/year changes
    $('#monthSelect, #yearSelect').change(function() {
        $('#loadAdvanceBtn').click();
    });
});
</script>
