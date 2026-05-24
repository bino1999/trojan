<?php if (empty($po_items)): ?>
    <div class="alert alert-info">No items found in this purchase order.</div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-sm table-bordered">
            <thead class="table-light">
                <tr>
                    <th>Product</th>
                    <th>Qty</th>
                    <th>Company Price</th>
                    <th>Sale Price</th>
                    <th>Total</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($po_items as $item): ?>
                    <tr>
                        <td>
                            <strong><?= $item->product_name ?></strong><br>
                            <small class="text-muted">
                                <?= $item->brand_name ? $item->brand_name . ' | ' : '' ?>
                                <?= $item->category_name ? $item->category_name . ' | ' : '' ?>
                                <?= $item->uom ?>
                            </small>
                        </td>
                        <td class="text-center"><?= $item->quantity ?></td>
                        <td class="text-end"><?= number_format($item->company_price, 2) ?></td>
                        <td class="text-end"><?= number_format($item->sale_price, 2) ?></td>
                        <td class="text-end">
                            <?php
                            $total = $item->quantity * $item->sale_price;
                            if ($item->discount_percent > 0) {
                                $total = $total * (1 - $item->discount_percent / 100);
                            } elseif ($item->discount_amount > 0) {
                                $total = $total - $item->discount_amount;
                            }
                            echo number_format($total, 2);
                            ?>
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-warning btn-sm" 
                                onclick="openEditPoItemModal(
                                    <?= $item->po_item_id ?>, 
                                    <?= $item->po_id ?>, 
                                    <?= $item->product_id ?>, 
                                    <?= $item->quantity ?>, 
                                    <?= $item->company_price ?>, 
                                    <?= $item->sale_price ?>, 
                                    <?= $item->discount_percent ?>, 
                                    <?= $item->discount_amount ?>, 
                                    '<?= htmlspecialchars($item->uom, ENT_QUOTES) ?>', 
                                    '<?= htmlspecialchars($item->inventory_type, ENT_QUOTES) ?>', 
                                    '<?= htmlspecialchars($item->rack_no, ENT_QUOTES) ?>', 
                                    '<?= htmlspecialchars($item->bin_no, ENT_QUOTES) ?>', 
                                    <?= $item->reorder_level ?>, 
                                    '<?= $item->purchase_date ?>', 
                                    '<?= htmlspecialchars($item->note, ENT_QUOTES) ?>'
                                )">
                                <i class="fa fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-danger btn-sm" 
                                onclick="deletePoItem(<?= $item->po_item_id ?>, <?= $item->po_id ?>)">
                                <i class="fa fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
