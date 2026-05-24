<table id="table1" class="table table-striped table-responsive">
    <thead class="table-info">
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>NIC</th>
            <th>Contacts</th>
            <th>Address</th>
            <th>Registered</th>
            <th>Updated</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $no = 1;
        foreach ($customers as $key => $customer) { ?>
            <tr>
                <td><?= $no++; ?></td>
                <td><?= $customer->salutation; ?>. <?= $customer->name; ?></td>
                <td><?= $customer->nic ? $customer->nic : '-'; ?></td>
                <td>
                    <?= $customer->email ? $customer->email . '<br>' : ''; ?>
                    <?= $customer->mobile; ?>
                    <?= $customer->mobile_2 ? '<br>' . $customer->mobile_2 : ''; ?>
                </td>
                <td>
                    <?= $customer->address_no . ', ' . $customer->street; ?><br>
                    <?= $customer->city_name . ', ' . $customer->district_name; ?><br>
                    <?= $customer->province_name . ' (' . $customer->postal_code . ')'; ?>
                </td>
                <td><?= date('Y-m-d', strtotime($customer->created_at)); ?></td>
                <td><?php if($customer->updated_at){ 
                    echo date('Y-m-d', strtotime($customer->updated_at)); 
                }
                ?></td>
                <td>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-secondary edit-customer-btn"
                            data-id="<?= $customer->customers_id; ?>"
                            data-salutation="<?= $customer->salutation; ?>"
                            data-name="<?= $customer->name; ?>"
                            data-nic="<?= $customer->nic; ?>"
                            data-email="<?= $customer->email; ?>"
                            data-mobile="<?= $customer->mobile; ?>"
                            data-mobile2="<?= $customer->mobile_2; ?>"
                            data-address_no="<?= $customer->address_no; ?>"
                            data-street="<?= $customer->street; ?>"
                            data-province="<?= $customer->province_id; ?>"
                            data-district="<?= $customer->district_id; ?>"
                            data-city="<?= $customer->city_id; ?>"
                            data-postal_code="<?= $customer->postal_code; ?>"
                            data-description="<?= $customer->description; ?>"
                            data-bs-toggle="modal"
                            data-bs-target="#editCustomerModal">
                            <i class="fa fa-pencil"></i> Edit
                        </button>
                    </div>
                </td>
            </tr>
        <?php } ?>
    </tbody>
</table>


<script>
    $(document).ready(function() {
        $('#table1').DataTable({
            "lengthMenu": [
                [150, 250, 500, -1],
                [150, 250, 500, "All"]
            ],
            "searching": true,
            "responsive": true
        });


    });
</script>