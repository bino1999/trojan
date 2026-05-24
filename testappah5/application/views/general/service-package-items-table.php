<?php foreach ($items as $item): ?>
    <tr class="item-row" data-item-id="<?= $item->id ?>">
        <td>
            <div class="form-check">
                <input class="form-check-input item-checkbox"
                    type="checkbox"
                    name="item_ids[]"
                    value="<?= $item->id ?>"
                    data-price="<?= $item->price ?>"
                    id="item<?= $item->id ?>">
                <label class="form-check-label" for="item<?= $item->id ?>">
                    <?= htmlspecialchars($item->name) ?>
                </label>
            </div>
        </td>
        <td class="original-price">LKR <?= number_format($item->price, 2) ?></td>
        <td>
            <select class="form-select discount-type" name="discount_type[<?= $item->id ?>]" disabled>
                <option value="none">No Discount</option>
                <option value="percentage">Percentage</option>
                <option value="fixed">Fixed Amount</option>
            </select>
        </td>
        <td>
            <input type="number" class="form-control discount-value" 
                name="discount_value[<?= $item->id ?>]" 
                min="0" step="0.01" disabled>
        </td>
        <td class="final-price">LKR <?= number_format($item->price, 2) ?></td>
    </tr>
<?php endforeach; ?>
