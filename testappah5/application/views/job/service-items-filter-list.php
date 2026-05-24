<table id="serviceItemsTable" class="table table-striped table-sm table-responsive">
    <thead class="table-info">
        <tr>
            <th>Package Name</th>
            <th class="text-end">Price</th>
            <th class="text-end" width="10%">Add</th>
        </tr>
    </thead>
    <tbody id="serviceItemsBody">
        <?php foreach ($serviceItems as $item) : ?>
            <tr class="item-row">
                <td class="f12"><?= $item->name ?></td>
                <td class="f12 text-end"><?= number_format($item->price, 2) ?></td>
                <td class="text-end">
                    <button type="button" class="btn btn-info btn-sm" onclick="addServiceItemToJob(<?= $item->id ?>)">
                        <i class="fa fa-plus"></i>
                    </button>

                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<script>
    serviceItemsTableToDatatable(); 
</script>