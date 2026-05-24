<div class="row">
<div class="col-12">
<div class="page-title-box d-sm-flex align-items-center justify-content-between">
<h4 class="mb-sm-0">Users</h4>

<div class="page-title-right">
<ol class="breadcrumb m-0">
<li class="breadcrumb-item"><a href="<?php echo base_url(); ?>">Home</a></li>
<li class="breadcrumb-item active">Users</li>
</ol>
</div>

</div>
</div>
</div>

<div class="row">
<div class="col-lg-12">

<style>
.mainCard {
margin: -10px;
}
</style>

<div class="card mainCard">
<div class="card-header align-items-center d-flex">
<h4 class="card-title mb-0 flex-grow-1">All Users</h4>
<div class="flex-shrink-0">
<button type="button" class="btn btn-success btn-label waves-effect waves-light" data-bs-toggle="modal" data-bs-target="#exampleModalgrid"><i class="fa fa-plus label-icon align-middle fs-16 me-2"></i> Create New Users</button>
</div>
</div><!-- end card header -->
<div class="card-body">

<?php if ($this->session->flashdata('success')): ?>
<div class="alert alert-success"><?php echo $this->session->flashdata('success'); ?></div>
<?php endif; ?>

<table id="table1" class="table table-striped table-responsive">
<thead class="table-info">
<tr>
<th>ID</th>
<th>User Name</th>
<th>Name</th>
<th>Contacts</th>
<th>Role</th>
<th>Department</th>
<th>Hire Date/ Employee ID</th>
<th>Salary</th>
<th>Is Active</th>
<th>Password Changed</th>
<th>Last Login</th>
<th>Created At</th>
<th>Updated At</th>
<th>Action</th>
</tr>
</thead>
<tbody>
<?php foreach ($results as $key => $result) { ?>
<tr>
<td><?= $result->UserID; ?></td>
<td><?= $result->UserName; ?></td>
<td><?= $result->FirstName; ?> <?= $result->LastName; ?></td>
<td><?= $result->Email; ?> <?= $result->PhoneNumber; ?></td>
<td><?= $result->system_role_name; ?></td>
<td><?= $result->employeeSectionName; ?></td>
<td><?= $result->HireDate; ?><br><?= $result->EmployeeID; ?></td>
<td>
            <?php if (isset($result->salary) && $result->salary): ?>
                LKR <?= number_format($result->salary, 2); ?>
            <?php else: ?>
                <span class="text-muted">Not set</span>
            <?php endif; ?>
</td>
<td>
    <span class="badge <?= $result->IsActive ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger'; ?> p-2">
        <?= $result->IsActive ? 'Active' : 'Inactive'; ?>
    </span>
</td>

<td>
    <span class="badge <?= isset($result->password_changed) && $result->password_changed ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning'; ?> p-2">
        <i class="fa <?= isset($result->password_changed) && $result->password_changed ? 'fa-check' : 'fa-exclamation-triangle'; ?>"></i>
        <?= isset($result->password_changed) && $result->password_changed ? 'Changed' : 'Default'; ?>
    </span>
</td>

<td>
    <?php if (isset($result->last_login_date) && $result->last_login_date): ?>
        <small class="text-muted">
            <i class="fa fa-clock"></i> <?= date('Y-m-d H:i', strtotime($result->last_login_date)); ?>
        </small>
    <?php else: ?>
        <span class="text-muted">Never</span>
    <?php endif; ?>
</td>

<td><?= date('Y-m-d', strtotime($result->CreatedAt)); ?></td>
<td><?= date('Y-m-d', strtotime($result->UpdatedAt)); ?></td>
<td>
<div class="d-flex gap-2">

<button class="btn btn-sm btn-secondary edit-user-btn"
data-id="<?= $result->UserID; ?>"
data-username="<?= $result->UserName; ?>"
data-firstname="<?= $result->FirstName; ?>"
data-lastname="<?= $result->LastName; ?>"
data-email="<?= $result->Email; ?>"
data-phone="<?= $result->PhoneNumber; ?>"
data-employeeid="<?= $result->EmployeeID; ?>"
data-role="<?= $result->Role; ?>"
data-section="<?= $result->Department; ?>"
data-hiredate="<?= $result->HireDate; ?>"
data-salary="<?= isset($result->salary) ? $result->salary : ''; ?>"
data-isactive="<?= $result->IsActive; ?>"
data-bs-toggle="modal"
data-bs-target="#editModal">
<i class="fa fa-pencil"></i> Edit
</button>

<button class="btn btn-sm btn-warning set-temp-pw-btn"
data-id="<?= $result->UserID; ?>"
data-username="<?= $result->UserName; ?>"
data-email="<?= $result->Email; ?>"
data-bs-toggle="modal"
data-bs-target="#tempPwModal">
<i class="fa fa-key"></i> Set Temp Password
</button>

<button class="btn btn-sm <?= $result->IsActive ? 'btn-danger' : 'btn-success'; ?> toggle-user-status-btn"
data-id="<?= $result->UserID; ?>"
data-username="<?= $result->UserName; ?>"
data-isactive="<?= $result->IsActive; ?>"
title="<?= $result->IsActive ? 'Disable User' : 'Enable User'; ?>">
<i class="fa <?= $result->IsActive ? 'fa-ban' : 'fa-check-circle'; ?>"></i> <?= $result->IsActive ? 'Disable' : 'Enable'; ?>
</button>

<button class="btn btn-sm btn-danger delete-user-btn"
data-id="<?= $result->UserID; ?>"
data-username="<?= $result->UserName; ?>"
title="Delete User">
<i class="fa fa-trash"></i> Delete
</button>

</div>
</td>
</tr>
<?php } ?>
</tbody>
</table>



</div>
</div>
</div>
<!--end col-->
</div>





<div class="modal fade zoomIn" id="exampleModalgrid" tabindex="-1" aria-labelledby="exampleModalgridLabel" aria-modal="true">
<div class="modal-dialog modal-lg modal-dialog-centered">
<div class="modal-content">
<div class="modal-header">
<h5 class="modal-title" id="exampleModalgridLabel">Create New User Account</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
<form id="createUserForm">
<div class="alert alert-danger" id="createError" style="display:none;"></div>
<div class="row g-3">

<div class="col-md-4">
<label for="UserName" class="form-label">User Name</label>
<input type="text" class="form-control" id="userName" name="userName" placeholder="Enter User Name" required>
</div>

<div class="col-md-4">
<label for="FirstName" class="form-label">First Name</label>
<input type="text" class="form-control" id="firstName" name="firstName" placeholder="Enter First Name" required>
</div>

<div class="col-md-4">
<label for="LastName" class="form-label">Last Name</label>
<input type="text" class="form-control" id="lastName" name="lastName" placeholder="Enter Last Name" required>
</div>

<div class="col-md-4">
<label for="Role" class="form-label">Role</label>
<select class="form-select" id="role" name="role" required>
<option value="0" selected>Select User Role</option>
<?php
foreach ($roles as $role) {
if ($role->isDeleted == 0) {
echo '<option value="' . $role->system_role_id . '">' . $role->system_role_name . '</option>';
}
}
?>
</select>
</div>

<div class="col-md-4">
<label for="Department" class="form-label">Department/Section</label>
<select class="form-select" id="section" name="section" required>
<option value="0" selected>Select Department</option>
<?php
foreach ($sections as $section) {
if ($section->isDeleted == 0) {
echo '<option value="' . $section->employeeSectionId . '">' . $section->employeeSectionName . '</option>';
}
}
?>
</select>
</div>

<div class="col-md-4">
<label for="EmployeeID" class="form-label">Employee ID</label>
<input type="text" class="form-control" id="employeeID" name="employeeID" placeholder="Enter Employee ID">
</div>

<div class="col-md-6">
<label for="Email" class="form-label">Email (Optional)</label>
<input type="email" class="form-control" id="email" name="email" placeholder="Enter Email">
</div>

<div class="col-md-6">
<label for="PhoneNumber" class="form-label">Phone Number</label>
<input type="text" class="form-control" id="phone" name="phone" placeholder="Enter Phone Number" required>
</div>

<div class="col-md-6">
<label for="Password" class="form-label">Password</label>
<input type="password" class="form-control" id="password" name="password" placeholder="Enter Password" required>
</div>

<div class="col-md-6">
<label for="Password" class="form-label">Confirm Password</label>
<input type="password" class="form-control" id="password2" name="password2" placeholder="Confirm Password" required>
</div>

<div class="col-md-4">
<label for="HireDate" class="form-label">Hire Date</label>
<input type="date" class="form-control" id="hireDate" name="hireDate" required>
</div>

<div class="col-md-4">
<label for="IsActive" class="form-label">Is Active</label>
<select class="form-select" id="isActive" name="isActive" required>
<option value="1" selected>Active</option>
<option value="0">Inactive</option>
</select>
</div>

<div class="col-md-4">
<label for="Salary" class="form-label">Salary (Optional)</label>
<input type="number" class="form-control" id="salary" name="salary" placeholder="Enter Salary" step="0.01" min="0">
</div>

<div class="col-lg-12">
<div class="hstack gap-2 justify-content-end">
<button type="button" class="btn btn-link link-danger fw-medium shadow-none" data-bs-dismiss="modal">Close</button>
<button type="button" class="btn btn-success" id="saveUserBtn">Save User</button>
</div>
</div>
</div>
</form>
</div>

</div>
</div>
</div>


<!-- Update Category Modal -->
<div class="modal fade zoomIn" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-modal="true">
<div class="modal-dialog modal-dialog-centered modal-lg">
<div class="modal-content">
<div class="modal-header">
<h5 class="modal-title" id="editModalLabel">Edit User</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>

<div class="modal-body">
<form id="editUserForm">
<input type="hidden" id="editUserId" name="userID">
<div class="alert alert-danger" id="editError" style="display:none;"></div>

<div class="row g-3">
<div class="col-md-4">
<label for="editUserName" class="form-label">User Name</label>
<input type="text" class="form-control" id="editUserName" name="userName" required disabled> 
</div>

<div class="col-md-4">
<label for="editFirstName" class="form-label">First Name</label>
<input type="text" class="form-control" id="editFirstName" name="firstName" required>
</div>

<div class="col-md-4">
<label for="editLastName" class="form-label">Last Name</label>
<input type="text" class="form-control" id="editLastName" name="lastName" required>
</div>

<div class="col-md-4">
<label for="editRole" class="form-label">Role</label>
<select class="form-select" id="editRole" name="role" required>
<option value="0">Select User Role</option>
<?php
foreach ($roles as $role) {
if ($role->isDeleted == 0) {
echo '<option value="' . $role->system_role_id . '">' . $role->system_role_name . '</option>';
}
}
?>
</select>
</div>

<div class="col-md-4">
<label for="editSection" class="form-label">Department/Section</label>
<select class="form-select" id="editSection" name="section" required>
<option value="0">Select Department</option>
<?php
foreach ($sections as $section) {
if ($section->isDeleted == 0) {
echo '<option value="' . $section->employeeSectionId . '">' . $section->employeeSectionName . '</option>';
}
}
?>
</select>
</div>

<div class="col-md-4">
<label for="editEmployeeID" class="form-label">Employee ID</label>
<input type="text" class="form-control" id="editEmployeeID" name="employeeID">
</div>

<div class="col-md-6">
<label for="editEmail" class="form-label">Email</label>
<input type="email" class="form-control" id="editEmail" name="email" required>
</div>

<div class="col-md-6">
<label for="editPhone" class="form-label">Phone Number</label>
<input type="text" class="form-control" id="editPhone" name="phone" required>
</div>

<div class="col-md-4">
<label for="editHireDate" class="form-label">Hire Date</label>
<input type="date" class="form-control" id="editHireDate" name="hireDate" required>
</div>

<div class="col-md-4">
<label for="editIsActive" class="form-label">Is Active</label>
<select class="form-select" id="editIsActive" name="editIsActive" required>
<option value="1">Active</option>
<option value="0">Inactive</option>
</select>
</div>

<div class="col-md-4">
<label for="editSalary" class="form-label">Salary (Optional)</label>
<input type="number" class="form-control" id="editSalary" name="salary" placeholder="Enter Salary" step="0.01" min="0">
</div>

<div class="col-lg-12">
<div class="hstack gap-2 justify-content-end">
<button type="button" class="btn btn-link link-danger fw-medium shadow-none" data-bs-dismiss="modal">Close</button>
<button type="button" class="btn btn-warning" id="updateBtn">Update User</button>
</div>
</div>
</div> <!-- End row -->
</form>
</div>
</div>
</div>
</div>



</div>
</div>
</div>
<!--end row-->

<!-- Temp Password Modal -->
<div class="modal fade" id="tempPwModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Set Temporary Password</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div id="tempPwError" class="alert alert-danger" style="display:none;"></div>
        <input type="hidden" id="tempPwUserId">
        <div class="mb-3">
          <label class="form-label">User</label>
          <input type="text" class="form-control" id="tempPwUserLabel" readonly>
        </div>
        <div class="mb-3">
          <label class="form-label">Temporary Password</label>
          <input type="password" class="form-control" id="tempPw" placeholder="Enter temporary password">
        </div>
        <div class="mb-3">
          <label class="form-label">Confirm Password</label>
          <input type="password" class="form-control" id="tempPw2" placeholder="Confirm password">
        </div>
        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="tempPwForceChange" checked>
          <label class="form-check-label" for="tempPwForceChange">Force password change on next login</label>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-warning" id="saveTempPwBtn"><i class="fa fa-key"></i> Save</button>
      </div>
    </div>
  </div>
 </div>

<script type="text/javascript">
// Initialize DataTable
$('#table1').DataTable({
"lengthMenu": [
[150, 250, 500, -1],
[150, 250, 500, "All"]
],
searching: true,
responsive: true
});

$('#saveUserBtn').click(function(e) {
e.preventDefault();

$.ajax({
url: "<?php echo base_url('saveSystemUser'); ?>",
method: "POST",
data: $('#createUserForm').serialize(),
dataType: 'json',
success: function(response) {
if (response.status === 'success') {
Swal.fire('Saved!', response.message, 'success').then(() => {
location.reload(); // Or reset form / close modal as you need
});
} else {
$('#createError').html(response.message).show();
}
},
error: function() {
Swal.fire('Error!', 'Something went wrong. Please try again.', 'error');
}
});
});



$(document).on('click', '#updateBtn', function () {
// Get form data
let userId = $('#editUserId').val();
let userName = $('#editUserName').val();
let firstName = $('#editFirstName').val();
let lastName = $('#editLastName').val();
let email = $('#editEmail').val();
let phone = $('#editPhone').val();
let employeeID = $('#editEmployeeID').val();
let role = $('#editRole').val();
let section = $('#editSection').val();
let hireDate = $('#editHireDate').val();
let isActive = $('#editIsActive').val();

// Validation (Optional: You can add your own validation logic here)
if (!userName || !firstName || !lastName || !email || !phone) {
Swal.fire('Error!', 'All fields are required.', 'error');
return;
}

let formData = {
userId: userId,
userName: userName,
firstName: firstName,
lastName: lastName,
email: email,
phone: phone,
employeeID: employeeID,
role: role,
section: section,
hireDate: hireDate,
salary: $('#editSalary').val(),
isActive: isActive
};

$.ajax({
url: "<?php echo base_url('updateSystemUser'); ?>",
type: "POST",
data: formData,
success: function (response) {
let res = JSON.parse(response);
if (res.status === 'success') {
Swal.fire('Success!', res.message, 'success').then(() => {
location.reload(); // Reload page or update the table
});
} else {
Swal.fire('Error!', res.message, 'error');
}
},
error: function () {
Swal.fire('Error!', 'Something went wrong. Please try again later.', 'error');
}
});
});


$(document).on('click', '.edit-user-btn', function () {
// Get data attributes
let userId = $(this).data('id');
let userName = $(this).data('username');
let firstName = $(this).data('firstname');
let lastName = $(this).data('lastname');
let email = $(this).data('email');
let phone = $(this).data('phone');
let employeeID = $(this).data('employeeid');
let role = $(this).data('role');
let section = $(this).data('section');
let hireDate = $(this).data('hiredate');
let salary = $(this).data('salary');
let isActive = $(this).data('isactive');

// Populate the modal fields
$('#editUserId').val(userId);
$('#editUserName').val(userName);
$('#editFirstName').val(firstName);
$('#editLastName').val(lastName);
$('#editEmail').val(email);
$('#editPhone').val(phone);
$('#editEmployeeID').val(employeeID);
$('#editRole').val(role);
$('#editSection').val(section);
$('#editHireDate').val(hireDate);
$('#editSalary').val(salary);
$('#editIsActive').val(isActive);
});

// Open temp password modal with user info
$(document).on('click', '.set-temp-pw-btn', function () {
  const userId = $(this).data('id');
  const userName = $(this).data('username');
  const email = $(this).data('email');
  $('#tempPwUserId').val(userId);
  $('#tempPwUserLabel').val(userName + ' (' + email + ')');
  $('#tempPw').val('');
  $('#tempPw2').val('');
  $('#tempPwForceChange').prop('checked', true);
  $('#tempPwError').hide();
});

// Save temp password
$(document).on('click', '#saveTempPwBtn', function(){
  const userId = $('#tempPwUserId').val();
  const pw = $('#tempPw').val();
  const pw2 = $('#tempPw2').val();
  const force = $('#tempPwForceChange').is(':checked') ? 1 : 0;

  if (!pw || pw.length < 6) {
    $('#tempPwError').text('Password must be at least 6 characters.').show();
    return;
  }
  if (pw !== pw2) {
    $('#tempPwError').text('Passwords do not match.').show();
    return;
  }

  $.post('<?= base_url('setTemporaryPassword'); ?>', {
    user_id: userId,
    password: pw,
    confirm_password: pw2,
    force_change: force
  }, function(res){
    if (res.status === 'success') {
      Swal.fire('Success', res.message, 'success').then(() => { location.reload(); });
    } else {
      $('#tempPwError').text(res.message || 'Failed to set temporary password').show();
    }
  }, 'json').fail(function(){
    $('#tempPwError').text('Request failed').show();
  });
});

// Delete user
$(document).on('click', '.delete-user-btn', function(){
  const userId = $(this).data('id');
  const userName = $(this).data('username');
  
  Swal.fire({
    title: 'Are you sure?',
    text: 'You are about to permanently delete user "' + userName + '". This action cannot be undone!',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Yes, delete it!',
    cancelButtonText: 'Cancel'
  }).then((result) => {
    if (result.isConfirmed) {
      $.ajax({
        url: "<?php echo base_url('deleteSystemUser'); ?>",
        method: "POST",
        data: { userId: userId },
        dataType: 'json',
        success: function(response) {
          if (response.status === 'success') {
            Swal.fire('Deleted!', response.message, 'success').then(() => {
              location.reload();
            });
          } else {
            Swal.fire('Error!', response.message, 'error');
          }
        },
        error: function() {
          Swal.fire('Error!', 'Something went wrong. Please try again.', 'error');
        }
      });
    }
  });
});

// Toggle user status (Enable/Disable)
$(document).on('click', '.toggle-user-status-btn', function(){
  const userId = $(this).data('id');
  const userName = $(this).data('username');
  const isActive = $(this).data('isactive');
  const action = isActive == 1 ? 'disable' : 'enable';
  const actionText = action.charAt(0).toUpperCase() + action.slice(1);
  
  Swal.fire({
    title: actionText + ' User?',
    text: 'Are you sure you want to ' + action + ' user "' + userName + '"?',
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: isActive == 1 ? '#d33' : '#28a745',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'Yes, ' + action + ' it!',
    cancelButtonText: 'Cancel'
  }).then((result) => {
    if (result.isConfirmed) {
      $.ajax({
        url: "<?php echo base_url('toggleUserStatus'); ?>",
        method: "POST",
        data: { userId: userId },
        dataType: 'json',
        success: function(response) {
          if (response.status === 'success') {
            Swal.fire('Success!', response.message, 'success').then(() => {
              location.reload();
            });
          } else {
            Swal.fire('Error!', response.message, 'error');
          }
        },
        error: function() {
          Swal.fire('Error!', 'Something went wrong. Please try again.', 'error');
        }
      });
    }
  });
});

</script>