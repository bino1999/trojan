<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0">My Profile</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($user)) { ?>
                <div class="row g-3">
                    <div class="col-md-3 text-center">
                        <img src="<?php echo base_url(); ?>assets/images/users/user.png" alt="Avatar" class="rounded-circle" style="width: 96px; height: 96px; object-fit: cover;">
                        <div class="mt-2 text-muted small">User ID: <?php echo (int)$user->UserID; ?></div>
                    </div>
                    <div class="col-md-9">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">First Name</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($user->FirstName ?? ''); ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Last Name</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($user->LastName ?? ''); ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Username</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($user->UserName ?? ''); ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($user->Email ?? ''); ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($user->PhoneNumber ?? ''); ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Department</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($user->Department ?? ''); ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Role</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($user->Role ?? ''); ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Hire Date</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($user->HireDate ?? ''); ?>" readonly>
                            </div>
                        </div>
                    </div>
                </div>
                <?php } else { ?>
                    <div class="alert alert-warning mb-0">User information not found.</div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>

