<div class="row">
<div class="col-12">
<div class="page-title-box d-sm-flex align-items-center justify-content-between">
<h4 class="mb-sm-0">Employee List</h4>

<div class="page-title-right">
<ol class="breadcrumb m-0">
<li class="breadcrumb-item"><a href="<?php echo base_url(); ?>">Home</a></li>
<li class="breadcrumb-item active">Employee List</li>
</ol>
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
<h4 class="card-title mb-0 flex-grow-1">All Employees</h4>
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
<th>Is Active</th>
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
    <span class="badge <?= $result->IsActive ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger'; ?> p-2">
        <?= $result->IsActive ? 'Active' : 'Inactive'; ?>
    </span>
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

</div>
</div>
<!--end row-->

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

</script>
