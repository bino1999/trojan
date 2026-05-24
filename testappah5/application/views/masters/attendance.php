<style>
.calendar-header {
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 0.375rem 0.375rem 0 0;
    font-weight: 600;
    color: #495057;
}

.calendar-day-header {
    padding: 0.75rem 0.5rem;
    border-right: 1px solid #dee2e6;
    font-size: 0.875rem;
}

.calendar-day-header:last-child {
    border-right: none;
}

.calendar-week {
    display: flex;
    border-left: 1px solid #dee2e6;
    border-right: 1px solid #dee2e6;
}

.calendar-week:last-child {
    border-bottom: 1px solid #dee2e6;
    border-radius: 0 0 0.375rem 0.375rem;
}

.calendar-day {
    flex: 1;
    min-height: 80px;
    border-right: 1px solid #dee2e6;
    padding: 0.5rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background-color: #fff;
    transition: background-color 0.2s;
}

.calendar-day:last-child {
    border-right: none;
}

.calendar-day:hover {
    background-color: #f8f9fa;
}

.calendar-day.other-month {
    background-color: #f8f9fa;
    color: #6c757d;
}

.calendar-day.today {
    background-color: #e3f2fd;
    font-weight: bold;
}

.calendar-day.present {
    background-color: #d4edda;
    color: #155724;
}

.calendar-day.absent {
    background-color: #f8d7da;
    color: #721c24;
}

.calendar-day.off-day {
    background-color: #fff3cd;
    color: #856404;
    position: relative;
}

.calendar-day.off-day::after {
    content: "OFF";
    position: absolute;
    top: 2px;
    right: 2px;
    font-size: 8px;
    font-weight: bold;
    background-color: #ffc107;
    color: #000;
    padding: 1px 3px;
    border-radius: 2px;
}

.calendar-day-number {
    font-size: 0.875rem;
    font-weight: 500;
    margin-bottom: 0.25rem;
}

.calendar-day-checkbox {
    margin: 0;
}

.calendar-day-checkbox:checked + .calendar-day-number {
    font-weight: bold;
}

.attendance-legend {
    margin-top: 1rem;
}

.attendance-legend .badge {
    margin-right: 0.5rem;
}
</style>

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Attendance Management</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="<?php echo base_url(); ?>">Home</a></li>
                    <li class="breadcrumb-item active">Attendance</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Mark Attendance</h4>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="employeeSelect" class="form-label">Select Employee</label>
                        <select class="form-select" id="employeeSelect">
                            <option value="">Choose Employee</option>
                            <?php foreach ($users as $user): ?>
                                <option value="<?= $user->UserID ?>"><?= $user->FirstName ?> <?= $user->LastName ?> (<?= $user->UserName ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="monthSelect" class="form-label">Select Month</label>
                        <select class="form-select" id="monthSelect">
                            <option value="">Choose Month</option>
                            <?php 
                            $currentMonth = date('n'); // n = 1-12
                            $months = [
                                1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                                5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                                9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
                            ];
                            foreach ($months as $num => $name): 
                            ?>
                                <option value="<?= $num ?>" <?= $num == $currentMonth ? 'selected' : '' ?>><?= $name ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="yearSelect" class="form-label">Select Year</label>
                        <select class="form-select" id="yearSelect">
                            <option value="">Choose Year</option>
                            <?php 
                            $currentYear = date('Y');
                            for ($i = $currentYear - 2; $i <= $currentYear + 1; $i++): 
                            ?>
                                <option value="<?= $i ?>" <?= $i == $currentYear ? 'selected' : '' ?>><?= $i ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <button type="button" class="btn btn-primary d-block" id="loadAttendanceBtn" disabled>Load</button>
                    </div>
                </div>

                <!-- Off Days Management Section -->
                <div class="row mb-3" id="offDaysSection" style="display: none;">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">Manage Off Days</h5>
                                <button type="button" class="btn btn-sm btn-outline-primary" id="addOffDayBtn">
                                    <i class="fas fa-plus"></i> Add Off Day
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <label for="offDayDate" class="form-label">Date</label>
                                        <input type="date" class="form-control" id="offDayDate">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="offDayDescription" class="form-label">Description</label>
                                        <input type="text" class="form-control" id="offDayDescription" placeholder="e.g., Holiday, Personal Leave">
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-check mt-4">
                                            <input class="form-check-input" type="checkbox" id="isCompanyHoliday">
                                            <label class="form-check-label" for="isCompanyHoliday">
                                                Company Holiday
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">&nbsp;</label>
                                        <button type="button" class="btn btn-success d-block" id="saveOffDayBtn">Add Off Day</button>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <h6>Current Off Days:</h6>
                                    <div id="offDaysList" class="row">
                                        <!-- Off days will be listed here -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Attendance Grid -->
                <div id="attendanceGrid" style="display: none;">
                    <div class="row mb-3">
                        <div class="col-12">
                            <h5 id="employeeName"></h5>
                            <p class="text-muted" id="monthYear"></p>
                        </div>
                    </div>
                    
                    <!-- Calendar Header -->
                    <div class="row mb-2">
                        <div class="col-12">
                            <div class="calendar-header d-flex">
                                <div class="calendar-day-header text-center flex-fill">Mon</div>
                                <div class="calendar-day-header text-center flex-fill">Tue</div>
                                <div class="calendar-day-header text-center flex-fill">Wed</div>
                                <div class="calendar-day-header text-center flex-fill">Thu</div>
                                <div class="calendar-day-header text-center flex-fill">Fri</div>
                                <div class="calendar-day-header text-center flex-fill">Sat</div>
                                <div class="calendar-day-header text-center flex-fill">Sun</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Calendar Body -->
                    <div id="calendarBody">
                        <!-- Calendar rows will be populated here -->
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-12">
                            <button type="button" class="btn btn-success" id="saveAttendanceBtn">Save Attendance</button>
                        </div>
                    </div>
                    
                    <!-- Legend -->
                    <div class="attendance-legend">
                        <span class="badge bg-success">Present</span>
                        <span class="badge bg-danger">Absent</span>
                        <span class="badge bg-warning text-dark">Off Day</span>
                        <span class="badge bg-info">Today</span>
                        <span class="badge bg-light text-dark">Other Month</span>
                    </div>
                </div>

                <!-- Attendance Summary -->
                <div id="attendanceSummary" style="display: none;" class="mt-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Attendance Summary</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Total Days in Month:</strong> <span id="totalDaysInMonth">0</span></p>
                                    <p><strong>Working Days:</strong> <span id="workingDays">0</span></p>
                                    <p><strong>Off Days:</strong> <span id="offDays">0</span></p>
                                    <p><strong>Present Days:</strong> <span id="presentDays">0</span></p>
                                    <p><strong>Absent Days:</strong> <span id="absentDays">0</span></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Present Dates:</strong></p>
                                    <div id="presentDatesList" class="text-muted">
                                        <!-- Present dates will be listed here -->
                                    </div>
                                    <p class="mt-3"><strong>Off Days:</strong></p>
                                    <div id="offDaysSummary" class="text-muted">
                                        <!-- Off days will be listed here -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
$(document).ready(function() {
    let currentUserId = null;
    let currentYear = null;
    let currentMonth = null;
    let attendanceData = {};
    let offDaysData = [];

    // Enable/disable load button based on selections
    function checkSelections() {
        const employee = $('#employeeSelect').val();
        const month = $('#monthSelect').val();
        const year = $('#yearSelect').val();
        
        if (employee && month && year) {
            $('#loadAttendanceBtn').prop('disabled', false);
        } else {
            $('#loadAttendanceBtn').prop('disabled', true);
        }
    }

    $('#employeeSelect, #monthSelect, #yearSelect').change(checkSelections);
    
    // Check selections on page load (month and year are already pre-selected)
    checkSelections();

    // Load attendance data
    $('#loadAttendanceBtn').click(function() {
        currentUserId = $('#employeeSelect').val();
        currentYear = $('#yearSelect').val();
        currentMonth = $('#monthSelect').val();

        if (!currentUserId || !currentYear || !currentMonth) {
            Swal.fire('Error!', 'Please select employee, month, and year.', 'error');
            return;
        }

        // Get days in month
        $.ajax({
            url: "<?php echo base_url('attendance/getDaysInMonth'); ?>",
            method: "POST",
            data: { year: currentYear, month: currentMonth },
            dataType: 'json',
            success: function(response) {
                console.log('Response:', response);
                if (response.status === 'success') {
                    generateDateCheckboxes(response.days);
                    loadAttendanceData();
                    loadOffDays();
                } else {
                    Swal.fire('Error!', response.message, 'error');
                }
            },
            error: function(xhr, status, error) {
                console.log('AJAX Error:', xhr.responseText);
                console.log('Status:', status);
                console.log('Error:', error);
                Swal.fire('Error!', 'Something went wrong. Check console for details.', 'error');
            }
        });
    });

    // Generate calendar layout
    function generateDateCheckboxes(daysInMonth) {
        const container = $('#calendarBody');
        container.empty();

        // Get first day of month and calculate starting position
        const firstDay = new Date(currentYear, currentMonth - 1, 1);
        const firstDayOfWeek = firstDay.getDay(); // 0 = Sunday, 1 = Monday, etc.
        const startDay = firstDayOfWeek === 0 ? 6 : firstDayOfWeek - 1; // Convert to Monday = 0

        // Get last day of previous month for padding
        const lastMonth = new Date(currentYear, currentMonth - 2, 0);
        const daysInLastMonth = lastMonth.getDate();

        // Get today's date for highlighting
        const today = new Date();
        const isCurrentMonth = today.getFullYear() === currentYear && today.getMonth() === currentMonth - 1;

        let calendarHTML = '';
        let dayCounter = 1;
        let currentWeek = 0;

        const todayNormalized = new Date();
        todayNormalized.setHours(0,0,0,0);

        // Generate 6 weeks (42 days) to ensure we have enough rows
        for (let week = 0; week < 6; week++) {
            calendarHTML += '<div class="calendar-week">';
            
            for (let day = 0; day < 7; day++) {
                let dayNumber, dateStr, dayClass = 'calendar-day', isCurrentMonthDay = false;
                
                if (week === 0 && day < startDay) {
                    // Previous month days
                    dayNumber = daysInLastMonth - startDay + day + 1;
                    dayClass += ' other-month';
                } else if (dayCounter <= daysInMonth) {
                    // Current month days
                    dayNumber = dayCounter;
                    dateStr = `${currentYear}-${currentMonth.toString().padStart(2, '0')}-${dayNumber.toString().padStart(2, '0')}`;
                    isCurrentMonthDay = true;
                    const cellDate = new Date(currentYear, currentMonth - 1, dayNumber);
                    cellDate.setHours(0,0,0,0);
                    if (cellDate > todayNormalized) {
                        dayClass += ' future-day';
                    }
                    dayCounter++;
                } else {
                    // Next month days
                    dayNumber = dayCounter - daysInMonth;
                    dayClass += ' other-month';
                    dayCounter++;
                }

                // Add today class if it's today
                if (isCurrentMonthDay && isCurrentMonth && dayNumber === today.getDate()) {
                    dayClass += ' today';
                }

                const isFutureDate = (() => {
                    if (!isCurrentMonthDay) return true;
                    const cellDate = new Date(currentYear, currentMonth - 1, dayNumber);
                    cellDate.setHours(0,0,0,0);
                    return cellDate > todayNormalized;
                })();

                calendarHTML += `
                    <div class="${dayClass}">
                        <input class="calendar-day-checkbox attendance-checkbox" type="checkbox" 
                               id="day_${dayNumber}_${week}_${day}" 
                               ${dateStr ? `data-date="${dateStr}" data-day="${dayNumber}"` : ''}
                               ${!isCurrentMonthDay || isFutureDate ? 'disabled' : ''}>
                        <div class="calendar-day-number">${dayNumber}</div>
                    </div>
                `;
            }
            
            calendarHTML += '</div>';
        }

        container.html(calendarHTML);

        // Add event handlers for checkboxes
        $('.attendance-checkbox').change(function() {
            const dayElement = $(this).closest('.calendar-day');
            dayElement.removeClass('present absent');
            
            if ($(this).is(':checked')) {
                dayElement.addClass('present');
            } else {
                dayElement.addClass('absent');
            }
        });

        // Update employee name and month/year display
        const selectedOption = $('#employeeSelect option:selected').text();
        $('#employeeName').text(selectedOption);
        $('#monthYear').text(`${$('#monthSelect option:selected').text()} ${currentYear}`);
        
        $('#attendanceGrid').show();
        $('#offDaysSection').show();
    }

    // Load existing attendance data
    function loadAttendanceData() {
        $.ajax({
            url: "<?php echo base_url('attendance/getAttendanceData'); ?>",
            method: "POST",
            data: { 
                user_id: currentUserId, 
                year: currentYear, 
                month: currentMonth 
            },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    // Clear all checkboxes first
                    $('.attendance-checkbox').prop('checked', false);
                    $('.calendar-day').removeClass('present absent');
                    
                    // Check boxes for present days and update visual state
                    response.attendance.forEach(function(record) {
                        const day = new Date(record.attendance_date).getDate();
                        const checkbox = $(`.attendance-checkbox[data-day="${day}"]`);
                        if (!checkbox.length) return;

                        if (record.is_present == 1) {
                            checkbox.prop('checked', true);
                            checkbox.closest('.calendar-day').addClass('present');
                        } else {
                            checkbox.closest('.calendar-day').addClass('absent');
                        }

                        // Lock the checkbox so it can't be modified again
                        checkbox.prop('disabled', true).attr('data-locked', '1');
                    });

                    // Update summary
                    updateSummary(response.summary, response.presentDates, response.offDays);
                } else {
                    Swal.fire('Error!', response.message, 'error');
                }
            },
            error: function() {
                Swal.fire('Error!', 'Something went wrong.', 'error');
            }
        });
    }

    // Load off days
    function loadOffDays() {
        $.ajax({
            url: "<?php echo base_url('attendance/getOffDays'); ?>",
            method: "POST",
            data: { 
                user_id: currentUserId, 
                year: currentYear, 
                month: currentMonth 
            },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    offDaysData = response.offDays;
                    updateOffDaysDisplay();
                    markOffDaysInCalendar();
                }
            },
            error: function() {
                console.log('Error loading off days');
            }
        });
    }

    // Update off days display
    function updateOffDaysDisplay() {
        const offDaysList = $('#offDaysList');
        offDaysList.empty();
        
        if (offDaysData.length > 0) {
            offDaysData.forEach(function(offDay) {
                const date = new Date(offDay.date);
                const day = date.getDate();
                const isCompanyHoliday = offDay.is_company_holiday || false;
                const description = offDay.description || 'No description';
                
                const offDayCard = $(`
                    <div class="col-md-3 mb-2">
                        <div class="card ${isCompanyHoliday ? 'border-warning' : 'border-info'}">
                            <div class="card-body p-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <small class="text-muted">Day ${day}</small>
                                        <div class="fw-bold">${description}</div>
                                        <small class="badge ${isCompanyHoliday ? 'bg-warning text-dark' : 'bg-info'}">${isCompanyHoliday ? 'Company' : 'Personal'}</small>
                                    </div>
                                    <button class="btn btn-sm btn-outline-danger" onclick="removeOffDay('${offDay.date}', ${offDay.user_id || 'null'})">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                `);
                offDaysList.append(offDayCard);
            });
        } else {
            offDaysList.html('<div class="col-12"><p class="text-muted">No off days set for this month.</p></div>');
        }
    }

    // Mark off days in calendar
    function markOffDaysInCalendar() {
        // Clear previous off day markings
        $('.calendar-day').removeClass('off-day');
        
        offDaysData.forEach(function(offDay) {
            const date = new Date(offDay.date);
            const day = date.getDate();
            const dayElement = $(`.attendance-checkbox[data-day="${day}"]`).closest('.calendar-day');
            dayElement.addClass('off-day');
            
            // Disable checkbox for off days
            const checkbox = dayElement.find('.attendance-checkbox');
            checkbox.prop('disabled', true);
        });
    }

    // Add off day
    function addOffDay() {
        const date = $('#offDayDate').val();
        const description = $('#offDayDescription').val();
        const isCompanyHoliday = $('#isCompanyHoliday').is(':checked');

        if (!date) {
            Swal.fire('Error!', 'Please select a date.', 'error');
            return;
        }

        // Check if date is in current month
        const selectedDate = new Date(date);
        if (selectedDate.getFullYear() != currentYear || selectedDate.getMonth() != (currentMonth - 1)) {
            Swal.fire('Error!', 'Please select a date within the current month.', 'error');
            return;
        }

        $.ajax({
            url: "<?php echo base_url('attendance/addOffDay'); ?>",
            method: "POST",
            data: { 
                date: date,
                description: description,
                is_company_holiday: isCompanyHoliday ? 1 : 0,
                user_id: isCompanyHoliday ? null : currentUserId
            },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    Swal.fire('Success!', response.message, 'success');
                    $('#offDayDate').val('');
                    $('#offDayDescription').val('');
                    $('#isCompanyHoliday').prop('checked', false);
                    loadOffDays();
                    loadAttendanceData(); // Reload to update summary
                } else {
                    Swal.fire('Error!', response.message, 'error');
                }
            },
            error: function() {
                Swal.fire('Error!', 'Something went wrong.', 'error');
            }
        });
    }

    // Remove off day
    function removeOffDay(date, userId) {
        Swal.fire({
            title: 'Are you sure?',
            text: "This will remove the off day.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, remove it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "<?php echo base_url('attendance/removeOffDay'); ?>",
                    method: "POST",
                    data: { 
                        date: date,
                        user_id: userId
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            Swal.fire('Success!', response.message, 'success');
                            loadOffDays();
                            loadAttendanceData(); // Reload to update summary
                        } else {
                            Swal.fire('Error!', response.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error!', 'Something went wrong.', 'error');
                    }
                });
            }
        });
    }

    // Update attendance summary
    function updateSummary(summary, presentDates, offDays) {
        const totalDaysInMonth = summary.total_days_in_month || 0;
        const workingDays = summary.working_days || 0;
        const offDaysCount = summary.off_days || 0;
        const presentDays = summary.present_days || 0;
        const absentDays = workingDays - presentDays;

        $('#totalDaysInMonth').text(totalDaysInMonth);
        $('#workingDays').text(workingDays);
        $('#offDays').text(offDaysCount);
        $('#presentDays').text(presentDays);
        $('#absentDays').text(absentDays);

        // List present dates
        const presentDatesList = $('#presentDatesList');
        presentDatesList.empty();
        
        if (presentDates.length > 0) {
            const dates = presentDates.map(function(item) {
                return item.day;
            });
            presentDatesList.text(dates.join(', '));
        } else {
            presentDatesList.text('No attendance recorded');
        }

        // List off days
        const offDaysSummary = $('#offDaysSummary');
        offDaysSummary.empty();
        
        if (offDays && offDays.length > 0) {
            const offDaysList = offDays.map(function(offDay) {
                const date = new Date(offDay.date);
                const day = date.getDate();
                const type = offDay.is_company_holiday ? 'Company' : 'Personal';
                return `Day ${day} (${type})`;
            });
            offDaysSummary.text(offDaysList.join(', '));
        } else {
            offDaysSummary.text('No off days set');
        }

        $('#attendanceSummary').show();
    }

    // Save attendance
    $('#saveAttendanceBtn').click(function() {
        if (!currentUserId || !currentYear || !currentMonth) {
            Swal.fire('Error!', 'Please load attendance data first.', 'error');
            return;
        }

        const checkedBoxes = $('.attendance-checkbox:checked').not(':disabled');
        const promises = [];

        // Save all checked dates
        checkedBoxes.each(function() {
            const date = $(this).data('date');
            promises.push(
                $.ajax({
                    url: "<?php echo base_url('attendance/saveAttendance'); ?>",
                    method: "POST",
                    data: { 
                        user_id: currentUserId, 
                        date: date, 
                        is_present: 1 
                    },
                    dataType: 'json'
                })
            );
        });

        // Save all unchecked dates
        $('.attendance-checkbox:not(:checked)').not(':disabled').each(function() {
            const date = $(this).data('date');
            promises.push(
                $.ajax({
                    url: "<?php echo base_url('attendance/saveAttendance'); ?>",
                    method: "POST",
                    data: { 
                        user_id: currentUserId, 
                        date: date, 
                        is_present: 0 
                    },
                    dataType: 'json'
                })
            );
        });

        Promise.all(promises).then(function() {
            Swal.fire('Success!', 'Attendance saved successfully.', 'success');
            loadAttendanceData(); // Reload to update summary
        }).catch(function() {
            Swal.fire('Error!', 'Some attendance records could not be saved.', 'error');
        });
    });

    // Off Days Event Handlers
    $('#saveOffDayBtn').click(function() {
        addOffDay();
    });

    $('#addOffDayBtn').click(function() {
        // Set default date to current month
        const today = new Date();
        const currentDate = new Date(currentYear, currentMonth - 1, today.getDate());
        const dateString = currentDate.toISOString().split('T')[0];
        $('#offDayDate').val(dateString);
    });

    // Make removeOffDay function globally available
    window.removeOffDay = removeOffDay;
});
</script>
