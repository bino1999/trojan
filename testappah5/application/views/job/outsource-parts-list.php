<?php if (!empty($outsourceParts)) : ?>
    <div class="table-responsive">
        <table class="table table-striped table-sm">
            <thead class="table-info">
                <tr>
                    <th>Item Name</th>
                    <th>Purchased From</th>
                    <th class="text-end">Purchased Price</th>
                    <th class="text-end">Invoice Price</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($outsourceParts as $part) : ?>
                    <tr>
                        <td class="f12"><?= htmlspecialchars($part->item_name) ?></td>
                        <td class="f12"><?= htmlspecialchars($part->purchased_from) ?></td>
                        <td class="f12 text-end">LKR <?= number_format($part->purchased_price, 2) ?></td>
                        <td class="f12 text-end">LKR <?= number_format($part->invoice_price, 2) ?></td>
                        <td class="text-center">
                            <button type="button" class="btn btn-danger btn-sm" onclick="deleteOutsourcePart(<?= $part->id ?>)">
                                <i class="fa fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else : ?>
    <div class="alert alert-info">
        <i class="fa fa-info-circle"></i> No outsource parts added yet.
    </div>
<?php endif; ?>
