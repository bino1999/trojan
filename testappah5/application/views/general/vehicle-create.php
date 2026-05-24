<form id="vehicleForm">
    <input type="hidden" id="vehicle_id" name="vehicle_id">
    <div class="alert alert-danger" id="createError" style="display:none;"></div>
    
    <div class="row">
        <div class="col-md-12 mb-3">
            <label for="owner" class="form-label">Owner *</label>
            <select class="form-select select2" id="owner" name="owner_id" required>
                <option value="0">Select Owner</option>
                <?php foreach ($customers as $customer) { ?>
                    <option value="<?= $customer->customers_id; ?>"><?= htmlspecialchars($customer->name); ?></option>
                <?php } ?>
            </select>
        </div>

        <div class="col-md-6 mb-3">
            <label for="vehicle_no" class="form-label">Vehicle No *</label>
            <input type="text" class="form-control" id="vehicle_no" name="vehicle_no" placeholder="BDX 0664" required style="text-transform: uppercase;">
        </div>

        <div class="col-md-6 mb-3">
            <label for="chassis_no" class="form-label">Frame/ Chassis No</label>
            <input type="text" class="form-control" id="chassis_no" name="chassis_no">
        </div>

        <div class="col-md-6 mb-3">
            <label for="engine_no" class="form-label">Engine No</label>
            <input type="text" class="form-control" id="engine_no" name="engine_no">
        </div>

        <div class="col-md-6 mb-3">
            <label for="color" class="form-label">Color *</label>
            <input type="text" class="form-control" id="color" name="color">
        </div>

        <div class="col-md-6 mb-3">
            <label for="battery_no" class="form-label">Battery No</label>
            <input type="text" class="form-control" id="battery_no" name="battery_no">
        </div>

        <div class="col-md-6 mb-3">
            <label for="year_of_manufacture" class="form-label">Year of Manufacture</label>
            <input type="number" class="form-control" id="year_of_manufacture" name="year_of_manufacture">
        </div>

        <div class="col-md-12 mb-3">
            <label for="category" class="form-label">Category *</label>
            <select class="form-select select2" id="category" name="category_id" required>
                <option value="0">Select Category</option>
                <?php foreach ($categories as $category) { ?>
                    <option value="<?= $category->vehicleCategoryId; ?>"><?= htmlspecialchars($category->vehicleCategoryName); ?></option>
                <?php } ?>
            </select>
        </div>

        <div class="col-md-6 mb-3">
            <label for="brand" class="form-label">Brand *</label>
            <select class="form-select select2" id="brand" name="brand_id" required>
                <option value="0">Select Brand</option>
                <?php foreach ($brands as $brand) { ?>
                    <option value="<?= $brand->vehicleBrandId; ?>"><?= htmlspecialchars($brand->vehicleBrandName); ?></option>
                <?php } ?>
            </select>
        </div>

        <div class="col-md-6 mb-3">
            <label for="model" class="form-label">Model *</label>
            <input type="text" class="form-control" id="model" name="model" required>
        </div>

        <div class="col-md-6 mb-1">
            <label for="service_mileage" class="form-label">Service Mileage *</label>
            <input type="number" class="form-control" id="service_mileage" name="service_mileage" value="0" required>
        </div>

        <div class="col-md-6 mb-1">
            <label for="next_service_date" class="form-label">Next Service Date *</label>
            <input type="date" class="form-control" id="next_service_date" name="next_service_date" required>
        </div>
    </div>
</form>
