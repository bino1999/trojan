<table id="table1" class="table table-striped table-responsive">
    <thead class="table-info">
        <tr>
            <th>##</th>
            <th>Service Name</th>
            <th>Section</th>
            <th>Category</th>
            <th>Price (LKR)</th>
            <th>Status</th>
            <th>Description</th>
            <th>User</th>
            <th>Created At</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($items)): ?>
            <?php foreach ($items as $index => $item): ?>
                <tr>
                    <td><?= $index + 1 ?></td>
                    <td><?= htmlspecialchars($item->name) ?></td>
                    <td><?= htmlspecialchars($item->section_name) ?></td>
                    <td><?= htmlspecialchars($item->category_name) ?></td>
                    <td class="text-end"><?= number_format($item->price, 2) ?></td>
                    <td>
                        <span class="badge bg-<?= $item->status ? 'success' : 'danger' ?>">
                            <?= $item->status ? 'Active' : 'Inactive' ?>
                        </span>
                    </td>
                    <td><?= htmlspecialchars($item->description) ?></td>
                    <td><?= htmlspecialchars($item->created_by_name) ?></td>
                    <td><?= date('d M Y', strtotime($item->created_at)) ?></td>
                    <td>
                        <button class="btn btn-sm btn-primary" onclick="editServiceItem(<?= $item->id ?>)">
                            <i class="fa fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="deleteServiceItem(<?= $item->id ?>)">
                            <i class="fa fa-trash"></i>
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            
        <?php endif; ?>
    </tbody>
</table>

<script>
    $('#table1').DataTable({
            "lengthMenu": [
                [150, 250, 500, -1],
                [150, 250, 500, "All"]
            ],
            "searching": true,
            "responsive": true
        });
</script>