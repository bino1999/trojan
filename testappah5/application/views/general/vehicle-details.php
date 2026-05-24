<?php
// Initialize variables first
$created_by_name = 'N/A';
$updated_by_name = 'N/A';

// Get created_by user name safely
if (!empty($vehicle->created_by) && $vehicle->created_by > 0) {
    $creator = $this->general_model->loadUserById($vehicle->created_by);
    if ($creator && isset($creator->UserName)) {
        $created_by_name = $creator->UserName;
    }
}

// Get updated_by user name safely
if (!empty($vehicle->updated_by) && $vehicle->updated_by > 0) {
    $updater = $this->general_model->loadUserById($vehicle->updated_by);
    if ($updater && isset($updater->UserName)) {
        $updated_by_name = $updater->UserName;
    }
}

$fields = [
    'Chassis No' => 'chassis_no',
    'Engine No' => 'engine_no',
    'Color' => 'color',
    'Battery No' => 'battery_no',
    'Year' => 'year_of_manufacture',
    'Category' => 'vehicle_category',
    'Brand' => 'vehicle_brand',
    'Model' => 'model',
    'Mileage (KM)' => 'service_mileage',
    'Next Service' => 'next_service_date'
];
?>

<h6>Owner Name : <?php echo $vehicle->owner_name; ?></h6>
<h6>Vehicle Number : <?php echo $vehicle->vehicle_no; ?></h6>
<h6>System Date & User : <?php echo $vehicle->created_at; ?> - (<?php echo $created_by_name; ?>) 
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Updated Date & User : <?php echo $vehicle->updated_at; ?> - (<?php echo $updated_by_name; ?>)
</h6>

<table id="table2" class="table table-striped table-responsive">
                    <thead class="table-info">
        <tr>
        <?php foreach ($fields as $label => $field): ?>
                <th width=""><?php echo $label; ?></th>
            <?php endforeach; ?>
        </tr>
    </thead>

    <tbody>
        <tr>
            
            <?php foreach ($fields as $label => $field): ?>
                <td><?php echo $vehicle->$field; ?></td>
        <?php endforeach; ?>
        </tr>
    </tbody>
</table>