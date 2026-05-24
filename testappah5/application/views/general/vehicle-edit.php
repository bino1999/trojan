<?php
$vehicle_id = $vehicle->vehicle_id;
$owner_id = $vehicle->owner_id;
$vehicle_no = $vehicle->vehicle_no;
$chassis_no = $vehicle->chassis_no;
$engine_no = $vehicle->engine_no;
$color = $vehicle->color;
$battery_no = $vehicle->battery_no;
$year_of_manufacture = $vehicle->year_of_manufacture;
$category_id = $vehicle->category_id;
$brand_id = $vehicle->brand_id;
$model = $vehicle->model;
$service_mileage = $vehicle->service_mileage;
$next_service_date = $vehicle->next_service_date;
$created_at = $vehicle->created_at;
$created_by = $vehicle->created_by;
$updated_at = $vehicle->updated_at;
$updated_by = $vehicle->updated_by;
?>


<form id="vehicleForm">
    <input type="hidden" id="vehicle_id" name="vehicle_id" value="<?= $vehicle->vehicle_id ?? ''; ?>">
    <div class="alert alert-danger" id="createError" style="display:none;"></div>

    <div class="row">
        <div class="col-md-12 mb-3">
            <label for="owner" class="form-label">Owner *</label>
            <select class="form-select select2" id="owner" name="owner_id" required>
                <option value="0">Select Owner</option>
                <?php foreach ($customers as $customer) { ?>
                    <option value="<?= $customer->customers_id; ?>" 
                        <?= isset($vehicle->owner_id) && $vehicle->owner_id == $customer->customers_id ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($customer->name); ?>
                    </option>
                <?php } ?>
            </select>
        </div>

        <div class="col-md-6 mb-3">
            <label for="vehicle_no" class="form-label">Vehicle No *</label>
            <input type="text" class="form-control" id="vehicle_no" name="vehicle_no" placeholder="BDX 0664"
                   value="<?= $vehicle->vehicle_no ?? ''; ?>" required style="text-transform: uppercase;" disabled>
        </div>

        <div class="col-md-6 mb-3">
            <label for="chassis_no" class="form-label">Frame/ Chassis No</label>
            <input type="text" class="form-control" id="chassis_no" name="chassis_no" 
                   value="<?= $vehicle->chassis_no ?? ''; ?>">
        </div>

        <div class="col-md-6 mb-3">
            <label for="engine_no" class="form-label">Engine No</label>
            <input type="text" class="form-control" id="engine_no" name="engine_no" 
                   value="<?= $vehicle->engine_no ?? ''; ?>">
        </div>

        <div class="col-md-6 mb-3">
            <label for="color" class="form-label">Color *</label>
            <input type="text" class="form-control" id="color" name="color" 
                   value="<?= $vehicle->color ?? ''; ?>">
        </div>

        <div class="col-md-6 mb-3">
            <label for="battery_no" class="form-label">Battery No</label>
            <input type="text" class="form-control" id="battery_no" name="battery_no" 
                   value="<?= $vehicle->battery_no ?? ''; ?>">
        </div>

        <div class="col-md-6 mb-3">
            <label for="year_of_manufacture" class="form-label">Year of Manufacture</label>
            <input type="number" class="form-control" id="year_of_manufacture" name="year_of_manufacture" 
                   value="<?= $vehicle->year_of_manufacture ?? ''; ?>">
        </div>

        <div class="col-md-12 mb-3">
            <label for="category" class="form-label">Category *</label>
            <select class="form-select select2" id="category" name="category_id" required>
                <option value="0">Select Category</option>
                <?php foreach ($categories as $category) { ?>
                    <option value="<?= $category->vehicleCategoryId; ?>" 
                        <?= isset($vehicle->category_id) && $vehicle->category_id == $category->vehicleCategoryId ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($category->vehicleCategoryName); ?>
                    </option>
                <?php } ?>
            </select>
        </div>

        <div class="col-md-6 mb-3">
            <label for="brand" class="form-label">Brand *</label>
            <select class="form-select select2" id="brand" name="brand_id" required>
                <option value="0">Select Brand</option>
                <?php foreach ($brands as $brand) { ?>
                    <option value="<?= $brand->vehicleBrandId; ?>" 
                        <?= isset($vehicle->brand_id) && $vehicle->brand_id == $brand->vehicleBrandId ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($brand->vehicleBrandName); ?>
                    </option>
                <?php } ?>
            </select>
        </div>

        <div class="col-md-6 mb-3">
            <label for="model" class="form-label">Model *</label>
            <input type="text" class="form-control" id="model" name="model" 
                   value="<?= $vehicle->model ?? ''; ?>" required>
        </div>

        <div class="col-md-6 mb-1">
            <label for="service_mileage" class="form-label">Service Mileage *</label>
            <input type="number" class="form-control" id="service_mileage" name="service_mileage" 
                   value="<?= $vehicle->service_mileage ?? '0'; ?>" required>
        </div>

        <div class="col-md-6 mb-1">
            <label for="next_service_date" class="form-label">Next Service Date *</label>
            <input type="date" class="form-control" id="next_service_date" name="next_service_date" 
                   value="<?= $vehicle->next_service_date ?? ''; ?>" required>
        </div>
    </div>
</form>
