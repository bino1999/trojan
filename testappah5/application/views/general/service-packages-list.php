<table class="table table-bordered">
    <thead class="table-info">
        <tr>
            <th>#</th>
            <th>Package Name</th>
            <th>Service Items</th>
            <th>Total Price (LKR)</th>
            <th>Availability</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($packages)): ?>
            <?php foreach ($packages as $index => $pkg): ?>
                <tr>
                    <td><?= $index + 1 ?></td>
                    <td><?= htmlspecialchars($pkg->package_name) ?></td>
                    <td>
                        <?php if (isset($pkg->items) && is_array($pkg->items)): ?>
                            <ul class="list-unstyled">
                                <?php foreach ($pkg->items as $item): ?>
                                    <li>
                                        <?= htmlspecialchars($item->name) ?>
                                        <div class="text-muted small">
                                            Original: LKR <?= number_format($item->price, 2) ?>
                                            <?php if (isset($item->discount_type) && $item->discount_type !== 'none'): ?>
                                                <br>
                                                Discount: 
                                                <?= $item->discount_type === 'percentage' 
                                                    ? $item->discount_value.'%' 
                                                    : 'LKR '.number_format($item->discount_value, 2) ?>
                                                <br>
                                                Final: LKR <?= number_format(
                                                    $item->discount_type === 'percentage'
                                                        ? $item->price - ($item->price * $item->discount_value / 100)
                                                        : $item->price - $item->discount_value, 
                                                    2
                                                ) ?>
                                            <?php endif; ?>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            No items in this package
                        <?php endif; ?>
                    </td>
                    <td class="text-end"><?= number_format($pkg->total_price, 2) ?></td>
                    <td>
                        <?php if (!empty($pkg->availability_start) || !empty($pkg->availability_end)): ?>
                            <span class="badge bg-info">
                                <?= htmlspecialchars($pkg->availability_start ?: '—') ?> to <?= htmlspecialchars($pkg->availability_end ?: '—') ?>
                            </span>
                        <?php else: ?>
                            <span class="text-muted">Not set</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($pkg->active == 1): ?>
                            <span class="badge bg-success">Active</span>
                        <?php else: ?>
                            <span class="badge bg-danger">Inactive</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-primary edit-package" data-id="<?= $pkg->id ?>">
                                <i class="fa fa-edit"></i> Edit
                            </button>
                            <button class="btn btn-secondary" onclick="toggleServicePackage(<?= $pkg->id ?>, <?= $pkg->active ?>)">
                                <i class="fa fa-undo"></i> Status 
                            </button>
                            <button class="btn btn-danger" onclick="deleteServicePackage(<?= $pkg->id ?>)">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="7" class="text-center">No Service Packages Found</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>


<script>
function toggleServicePackage(packageId, currentStatus) {
    const newStatus = currentStatus ? 0 : 1;
    const action = currentStatus ? 'deactivate' : 'activate';
    const actionText = currentStatus ? 'Deactivate' : 'Activate';
    Swal.fire({
        title: `Are you sure?`,
        text: `You're about to ${action} this package.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: `Yes, ${actionText}!`,
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "<?= site_url('services/toggleServicePackage') ?>",
                method: 'POST',
                data: { 
                    package_id: packageId,
                    status: newStatus
                },
                dataType: 'json',
                beforeSend: function() {
                    // Show loading state if needed
                    Swal.showLoading();
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            title: 'Success!',
                            text: `Package has been ${action}d.`,
                            icon: 'success',
                            timer: 1000,
                            timerProgressBar: true,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                        
                         setTimeout(() => location.reload(), 2000);
                    } else {
                        Swal.fire(
                            'Failed!',
                            response.message || 'Operation failed',
                            'error'
                        );
                    }
                },
                error: function(xhr, status, error) {
                    Swal.fire(
                        'Error!',
                        'Failed to update package status',
                        'error'
                    );
                    console.error('Error:', error);
                }
            });
        }
    });
}

function deleteServicePackage(id) {
        Swal.fire({
            title: 'Are you sure?',
            text: 'Delete this service package?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "<?= site_url('services/deleteServicePackage') ?>",
                    type: "POST",
                    data: { package_id: id },
                    dataType: "json",
                    success: function(response) {
                        if (response.status === 'success') {
                            loadServicePackages();
                            showToast('Package deleted successfully', 'success');
                        } else {
                            showToast(response.message, 'error');
                        }
                    },
                    error: function() {
                        showToast('Error deleting package', 'error');
                    }
                });
            }
        });
    }
</script>