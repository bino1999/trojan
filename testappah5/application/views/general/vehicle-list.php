<?php if ($this->session->flashdata('success')): ?>
    <div class="alert alert-success"><?php echo $this->session->flashdata('success'); ?></div>
<?php endif; ?>

<table id="table1" class="table table-striped table-responsive">
    <thead class="table-info">
        <tr>
            <th>##</th>
            <th>Vehicle No</th>
            <th>Owner</th>
            <th>Category</th>
            <th>Brand</th>
            <th>Model</th>
            <th>Mileage</th>
            <th>Next Service</th>
            <th>Added</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $no = 1;
        foreach ($vehicles as $vehicle) { ?>
            <tr>
                <td><?= $no++; ?></td>
                <td><?= $vehicle->vehicle_no; ?></td>
                <td><?= htmlspecialchars((string) $vehicle->owner_name); ?></td>
                <td><?= htmlspecialchars((string) $vehicle->vehicle_category ?? '-'); ?></td>
                <td><?= htmlspecialchars((string) $vehicle->vehicle_brand ?? '-'); ?></td>
                <td><?= htmlspecialchars((string) $vehicle->model ?? '-'); ?></td>
                <td><?= number_format($vehicle->service_mileage, 0); ?> km</td>
                <td><?= $vehicle->next_service_date ? date('Y-m-d', strtotime($vehicle->next_service_date)) : '-'; ?></td>
                <td><?= date('Y-m-d', strtotime($vehicle->created_at)); ?></td>
                <td>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-primary edit-vehicle-btn" onclick="openVehicleDetailsModal(<?= $vehicle->vehicle_id; ?>)">
                            <i class="fa fa-search"></i> More
                        </button>
                        <button class="btn btn-sm btn-secondary edit-vehicle-btn" onclick="openVehicleEditModal(<?= $vehicle->vehicle_id; ?>)">
                            <i class="fa fa-pencil"></i> Edit
                        </button>
                    </div>
                </td>
            </tr>
        <?php } ?>
    </tbody>
</table>