<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Attendance - Bulk Entry</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="<?php echo base_url(); ?>">Home</a></li>
                    <li class="breadcrumb-item active">Attendance Bulk</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Month</label>
                <select id="bulkMonth" class="form-select">
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
                <label class="form-label">Year</label>
                <select id="bulkYear" class="form-select">
                    <?php $currentYear = date('Y'); for ($i=$currentYear-2;$i<=$currentYear+1;$i++): ?>
                        <option value="<?= $i ?>" <?= $i==$currentYear?'selected':''; ?>><?= $i ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-3">
                <button id="loadGridBtn" class="btn btn-primary">Load Grid</button>
            </div>
            <div class="col-md-3 text-end">
                <span class="badge bg-success">P = Present</span>
                <span class="badge bg-danger">A = Absent</span>
                <span class="badge bg-warning text-dark">O = Off</span>
                <button id="viewOffDaysBtn" class="btn btn-sm btn-outline-secondary ms-2">View Off Days</button>
                <button id="addOffDayBtn" class="btn btn-sm btn-outline-primary ms-1">Add Off Day</button>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div id="gridWrapper" style="display:none;">
            <div class="table-responsive" style="overflow-x: auto; max-height: 80vh;">
                <table id="attendanceGridTable" class="table table-bordered table-sm align-middle">
                    <thead id="gridHead"></thead>
                    <tbody id="gridBody"></tbody>
                </table>
            </div>
            <div class="d-flex justify-content-between mt-2">
                <div class="small text-muted">Tip: Edit cells, then click Save.</div>
                <div>
                    <button id="saveGridBtn" class="btn btn-success">Save</button>
                </div>
            </div>
            <div class="row mt-3" id="summaryRow" style="display:none;">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header"><strong>Attendance Summary</strong></div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Employee</th>
                                            <th class="text-center">Total Days</th>
                                            <th class="text-center">Working Days</th>
                                            <th class="text-center">Off Days</th>
                                            <th class="text-center">Present</th>
                                            <th class="text-center">Absent</th>
                                        </tr>
                                    </thead>
                                    <tbody id="empSummaryBody"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let monthDays = 0;
let employees = [];
let companyOff = {}; // day => true
let userOff = {};    // user_id => { day: true }
let loadedData = null; // cache grid data for modals

function buildHead(days, year, month){
    const head = document.getElementById('gridHead');
    let th = '<tr><th style="min-width:220px; position:sticky; left:0; background-color:#fff; z-index:10; box-shadow:2px 0 4px rgba(0,0,0,0.1);">Employee</th>';
    
    // Get today's date
    const today = new Date();
    const currentYear = today.getFullYear();
    const currentMonth = today.getMonth() + 1; // JavaScript months are 0-indexed
    const todayDay = today.getDate();
    
    // Determine if we're showing the current month
    const isCurrentMonth = (parseInt(year) === currentYear && parseInt(month) === currentMonth);
    const todayDate = isCurrentMonth ? todayDay : null;
    
    // Calculate next month info for greyed out dates
    let nextMonth = parseInt(month) + 1;
    let nextYear = parseInt(year);
    if (nextMonth > 12) {
        nextMonth = 1;
        nextYear++;
    }
    const daysInNextMonth = new Date(nextYear, nextMonth, 0).getDate();
    
    // Build column order: today first, then future dates, then past dates, then next month dates
    let dayOrder = [];
    
    if (todayDate) {
        // Today first
        dayOrder.push({ day: todayDate, month: 'current', isToday: true });
        
        // Future dates in current month (today+1 to end)
        for(let d = todayDate + 1; d <= days; d++) {
            dayOrder.push({ day: d, month: 'current', isToday: false });
        }
        
        // Past dates in current month (1 to today-1)
        for(let d = 1; d < todayDate; d++) {
            dayOrder.push({ day: d, month: 'current', isToday: false });
        }
        
        // Next month dates (greyed out) - show enough to fill remaining space or up to end of next month
        const remainingDays = 31 - days; // How many days we can add from next month
        for(let d = 1; d <= Math.min(remainingDays, daysInNextMonth); d++) {
            dayOrder.push({ day: d, month: 'next', isToday: false });
        }
    } else {
        // Not current month, show in normal order
        for(let d = 1; d <= days; d++) {
            dayOrder.push({ day: d, month: 'current', isToday: false });
        }
    }
    
    // Build header columns
    dayOrder.forEach(item => {
        const isToday = item.isToday;
        const isNextMonth = item.month === 'next';
        const dayNum = item.day;
        
        let classes = 'text-center';
        let style = 'min-width:36px;';
        let label = dayNum.toString();
        
        if (isToday) {
            classes += ' bg-info text-white';
            label += ' (Today)';
        } else if (isNextMonth) {
            classes += ' bg-secondary text-white';
            style += ' opacity:0.5;';
            label = dayNum + '/' + nextMonth;
        }
        
        th += `<th class="${classes}" style="${style}" data-day="${dayNum}" data-month="${item.month}" data-is-today="${isToday ? '1' : '0'}">${label}</th>`;
    });
    
    th += '<th class="text-center" style="position:sticky; right:0; background-color:#fff; z-index:10; box-shadow:-2px 0 4px rgba(0,0,0,0.1);">Total P</th></tr>';
    head.innerHTML = th;
}

function cellClass(u,d){
    if (companyOff[d]) return 'table-warning';
    if (userOff[u] && userOff[u][d]) return 'table-warning';
    return '';
}

function buildBody(days, data, year, month){
    const body = document.getElementById('gridBody');
    body.innerHTML = '';
    employees = data.employees || [];
    const att = data.attendance || {};
    userOff = {}; companyOff = {};
    loadedData = data;
    for (const k in data.companyOffDays){ companyOff[k] = true; }
    const uo = data.userOffDays || {};
    for (const uid in uo){ userOff[uid] = {}; for (const day in uo[uid]) userOff[uid][day]=true; }

    const selectedYearInt = parseInt(year);
    const selectedMonthInt = parseInt(month);

    // Get today's date
    const today = new Date();
    today.setHours(0,0,0,0);
    const currentYear = today.getFullYear();
    const currentMonth = today.getMonth() + 1;
    const todayDay = today.getDate();
    
    // Determine if we're showing the current month
    const isCurrentMonth = (selectedYearInt === currentYear && selectedMonthInt === currentMonth);
    const todayDate = isCurrentMonth ? todayDay : null;
    
    // Calculate next month info
    let nextMonth = selectedMonthInt + 1;
    let nextYear = selectedYearInt;
    if (nextMonth > 12) {
        nextMonth = 1;
        nextYear++;
    }
    const daysInNextMonth = new Date(nextYear, nextMonth, 0).getDate();
    
    // Build column order same as header
    let dayOrder = [];
    
    if (todayDate) {
        // Today first
        dayOrder.push({ day: todayDate, month: 'current', isToday: true });
        
        // Future dates in current month
        for(let d = todayDate + 1; d <= days; d++) {
            dayOrder.push({ day: d, month: 'current', isToday: false });
        }
        
        // Past dates in current month
        for(let d = 1; d < todayDate; d++) {
            dayOrder.push({ day: d, month: 'current', isToday: false });
        }
        
        // Next month dates (greyed out)
        const remainingDays = 31 - days;
        for(let d = 1; d <= Math.min(remainingDays, daysInNextMonth); d++) {
            dayOrder.push({ day: d, month: 'next', isToday: false });
        }
    } else {
        // Not current month, show in normal order
        for(let d = 1; d <= days; d++) {
            dayOrder.push({ day: d, month: 'current', isToday: false });
        }
    }

    let html = '';
    employees.forEach(emp => {
        const uid = emp.user_id;
        const rowMap = att[uid] || {};
        let totalP = 0;
        html += `<tr data-user="${uid}"><td style="position:sticky; left:0; background-color:#fff; z-index:9; box-shadow:2px 0 4px rgba(0,0,0,0.1);">${emp.FirstName} ${emp.LastName} (${emp.UserName})</td>`;
        
        // Build cells in the reordered sequence
        dayOrder.forEach(item => {
            const dayNum = item.day;
            const isNextMonth = item.month === 'next';
            const isToday = item.isToday;
            
            let cellHtml = '';
            let cellClasses = 'text-center';
            let cellStyle = '';
            
            if (isNextMonth) {
                // Next month - greyed out and disabled
                cellClasses += ' bg-secondary bg-opacity-10';
                cellStyle += ' opacity:0.5;';
                cellHtml = `<select class="form-select form-select-sm status-select" disabled>
                                <option value=""></option>
                            </select>`;
            } else {
                const cellDate = new Date(selectedYearInt, selectedMonthInt - 1, dayNum);
                cellDate.setHours(0,0,0,0);
                const isFutureDate = cellDate > today;

                // Current month - normal functionality
                const isOff = companyOff[dayNum] || (userOff[uid] && userOff[uid][dayNum]);
                const val = rowMap[dayNum] === 1 ? 'P' : (rowMap[dayNum] === 0 ? 'A' : '');
                const hasExistingValue = rowMap[dayNum] === 1 || rowMap[dayNum] === 0;
                if (val === 'P') totalP++;
                
                if (val === 'P') cellClasses += ' bg-success text-white';
                if (val === 'A') cellClasses += ' bg-danger text-white';
                if (isToday) cellClasses += ' border border-primary border-3';
                if (isFutureDate) cellClasses += ' future-day';
                
                const disableSelect = isOff || hasExistingValue || isFutureDate;
                
                cellHtml = `<select class="form-select form-select-sm status-select" ${disableSelect?'disabled':''}>
                                <option value=""></option>
                                <option value="P" ${val==='P'?'selected':''}>P</option>
                                <option value="A" ${val==='A'?'selected':''}>A</option>
                            </select>`;
            }
            
            html += `<td class="${cellClasses}" style="${cellStyle}" data-day="${dayNum}" data-month="${item.month}" data-is-today="${isToday ? '1' : '0'}" data-off="${isNextMonth ? '0' : ((companyOff[dayNum] || (userOff[uid] && userOff[uid][dayNum])) ? '1' : '0')}">${cellHtml}</td>`;
        });
        
        html += `<td class="text-center fw-bold total-present" style="position:sticky; right:0; background-color:#fff; z-index:9; box-shadow:-2px 0 4px rgba(0,0,0,0.1);">${totalP}</td></tr>`;
    });
    body.innerHTML = html;

    // Listeners
    document.querySelectorAll('.status-select').forEach(sel => {
        sel.addEventListener('change', function(){
            const td = this.closest('td');
            const tr = this.closest('tr');
            const uid = tr.getAttribute('data-user');
            const day = parseInt(td.getAttribute('data-day'));
            const val = this.value;
            td.classList.remove('bg-success','bg-danger','text-white','table-warning');
            if (val==='P') td.classList.add('bg-success','text-white');
            if (val==='A') td.classList.add('bg-danger','text-white');
            recalcRowPresent(tr);
            recalcSummary();
        });
    });

    // After building, show summary
    document.getElementById('summaryRow').style.display = 'block';
    recalcSummary();
}

function recalcRowPresent(tr){
    let total = 0;
    tr.querySelectorAll('.status-select').forEach(s=>{ if (s.value==='P') total++; });
    const cell = tr.querySelector('.total-present');
    if (cell) cell.textContent = total;
}

function recalcSummary(){
    const totalDays = monthDays;
    const empBody = document.getElementById('empSummaryBody');
    let html = '';
    const loadedUserOff = (loadedData && loadedData.userOffDays) ? loadedData.userOffDays : {};
    const companyOffCount = Object.keys(companyOff).length;

    employees.forEach(emp => {
        const uid = emp.user_id;
        let present = 0, absent = 0;
        const row = document.querySelector(`#gridBody tr[data-user="${uid}"]`);
        if (row){
            row.querySelectorAll('td[data-day]').forEach(td => {
                if (td.getAttribute('data-month') === 'next') {
                    return;
                }
                const sel = td.querySelector('select');
                if (!sel || sel.disabled) return;
                if (sel.value==='P') present++;
                if (sel.value==='A') absent++;
            });
        }
        const userOffCount = Object.keys(loadedUserOff[uid] || {}).length;
        const workingDays = totalDays - companyOffCount - userOffCount;
        const name = `${emp.FirstName} ${emp.LastName}`;
        html += `<tr>
                    <td>${name}</td>
                    <td class="text-center">${totalDays}</td>
                    <td class="text-center">${Math.max(workingDays,0)}</td>
                    <td class="text-center">${companyOffCount + userOffCount}</td>
                    <td class="text-center text-success fw-bold">${present}</td>
                    <td class="text-center text-danger fw-bold">${absent}</td>
                 </tr>`;
    });
    empBody.innerHTML = html;
}

// Function to load the grid
function loadGrid(){
    const year = document.getElementById('bulkYear').value;
    const month = document.getElementById('bulkMonth').value;
    if (!year || !month){ 
        Swal.fire('Select month and year'); 
        return; 
    }
    $.post("<?= base_url('attendance/getMonthlyGrid'); ?>", {year:year, month:month}, function(res){
        if (res.status==='success'){
            monthDays = res.daysInMonth;
            buildHead(monthDays, year, month);
            buildBody(monthDays, res.data, year, month);
            document.getElementById('gridWrapper').style.display='block';
            
            // Auto-scroll to show today's column (which is now first after employee name)
            setTimeout(function(){
                const gridWrapper = document.getElementById('gridWrapper');
                const scrollContainer = gridWrapper.querySelector('.table-responsive');
                if (scrollContainer) {
                    // Today is now the first column, so scroll to start
                    scrollContainer.scrollLeft = 0;
                }
            }, 100);
        } else {
            Swal.fire('Error', res.message || 'Failed to load grid', 'error');
        }
    }, 'json');
}

// Load button click handler
document.getElementById('loadGridBtn').addEventListener('click', loadGrid);

// Auto-load on page load if month and year are already selected
$(document).ready(function(){
    const year = document.getElementById('bulkYear').value;
    const month = document.getElementById('bulkMonth').value;
    if (year && month){
        loadGrid();
    }
});

document.getElementById('saveGridBtn').addEventListener('click', function(){
    const year = document.getElementById('bulkYear').value;
    const month = document.getElementById('bulkMonth').value;
    const records = [];
    const offAdds = [];
    document.querySelectorAll('#gridBody tr').forEach(tr=>{
        const uid = tr.getAttribute('data-user');
        // Get all date cells, but only process current month dates
        tr.querySelectorAll('td[data-day]').forEach(td => {
            const dayMonth = td.getAttribute('data-month');
            // Skip next month dates
            if (dayMonth === 'next') return;
            
            const d = parseInt(td.getAttribute('data-day'));
            const sel = td.querySelector('select');
            if (!sel || sel.disabled) return;
            
            const isUserOff = (userOff[uid] && userOff[uid][d]);
            const isCompanyOff = companyOff[d] || td.getAttribute('data-off')==='1';
            if (isUserOff || isCompanyOff){
                if (!isCompanyOff){
                    const date = `${year}-${String(month).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
                    offAdds.push({ user_id: uid, date: date });
                }
                return; // skip attendance for off days
            }
            const v = sel.value;
            if (v==='P' || v==='A'){
                const date = `${year}-${String(month).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
                records.push({ user_id: uid, date: date, is_present: v==='P'?1:0 });
            }
        });
    });

    const attendanceReq = $.post("<?= base_url('attendance/saveMonthlyGrid'); ?>", {records: JSON.stringify(records)}, null, 'json');
    Promise.all([attendanceReq]).then(()=>{
        Swal.fire('Saved', 'Attendance and off-days updated', 'success');
    }).catch(()=>{
        Swal.fire('Error', 'Some records failed to save', 'error');
    });
});

// View Off Days: highlight off days in the grid
document.getElementById('viewOffDaysBtn').addEventListener('click', function(){
    if (!loadedData){ Swal.fire('Load a month first'); return; }
    document.querySelectorAll('#gridBody td[data-off="1"]').forEach(td=>{
        td.classList.toggle('table-warning');
    });
});

// Add Off Day (per user or all users)
document.getElementById('addOffDayBtn').addEventListener('click', function(){
    const year = document.getElementById('bulkYear').value;
    const month = document.getElementById('bulkMonth').value;
    if (!year || !month){ Swal.fire('Select month and year first'); return; }
    let options = '';
    employees.forEach(e=>{ options += `<option value="${e.user_id}">${e.FirstName} ${e.LastName}</option>`; });
    const html = `
        <div class="row g-2">
            <div class="col-md-4"><label>Date</label><input id="offDateInp" type="date" class="form-control" value="${year}-${String(month).padStart(2,'0')}-01"></div>
            <div class="col-md-5"><label>Employee</label><select id="offEmpSel" class="form-select">${options}</select></div>
            <div class="col-md-3"><label class="form-label">&nbsp;</label><div class="form-check"><input class="form-check-input" type="checkbox" id="offAllUsers"><label class="form-check-label" for="offAllUsers">Apply to all users</label></div></div>
            <div class="col-md-12"><label>Description</label><input id="offDescInp" type="text" class="form-control" placeholder="Holiday/Leave"></div>
        </div>`;
    Swal.fire({ title:'Add Off Day', html: html, showCancelButton:true, confirmButtonText:'Add' }).then(res=>{
        if (!res.isConfirmed) return;
        const date = document.getElementById('offDateInp').value;
        const emp = document.getElementById('offEmpSel').value;
        const desc = document.getElementById('offDescInp').value;
        const allUsers = document.getElementById('offAllUsers').checked;

        if (allUsers){
            // company-wide
            $.post("<?= base_url('attendance/addOffDay'); ?>", {date: date, description: desc, is_company_holiday: 1, user_id: null}, function(r){
                if (r.status==='success'){ Swal.fire('Added','Company off day added','success'); document.getElementById('loadGridBtn').click(); } else { Swal.fire('Error', r.message||'Failed', 'error'); }
            }, 'json');
        } else {
            // user-specific
            if (!emp){ Swal.fire('Select an employee or choose all users'); return; }
            $.post("<?= base_url('attendance/addOffDay'); ?>", {date: date, description: desc, is_company_holiday: 0, user_id: emp}, function(r){
                if (r.status==='success'){ Swal.fire('Added','User off day added','success'); document.getElementById('loadGridBtn').click(); } else { Swal.fire('Error', r.message||'Failed', 'error'); }
            }, 'json');
        }
    });
});

</script>


