<?php if (!empty($products)) : ?>
    <?php foreach ($products as $p): ?>
        <tr class="product-row" data-product-id="<?= $p->product_id ?>">
            <td>
                <div class="form-check">
                    <input class="form-check-input product-checkbox" type="checkbox" value="<?= $p->product_id ?>" data-price="<?= $p->sale_price ?>" id="prod<?= $p->product_id ?>">
                    <label class="form-check-label" for="prod<?= $p->product_id ?>"><?= htmlspecialchars($p->product_name) ?></label>
                </div>
            </td>
            <td><?= htmlspecialchars($p->sku ?? 'N/A') ?></td>
            <td><?= htmlspecialchars($p->brand_name ?? 'N/A') ?></td>
            <td><?= htmlspecialchars($p->category_name ?? 'N/A') ?></td>
            <td class="text-end">LKR <?= number_format($p->sale_price, 2) ?></td>
        </tr>
    <?php endforeach; ?>
<?php else : ?>
    <tr><td colspan="5" class="text-center text-muted">No products found</td></tr>
<?php endif; ?>
