<?php if (!empty($purchases)) { ?>
    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-primary">
                <tr>
                    <th>#</th>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Uni</th>
                    <th>Buying Price</th>
                    <th>Discount</th>
                    <th>Total</th>
                    <?php if (!empty($purchases[0]->vat_type) && $purchases[0]->vat_type === 'vat_per_item'): ?>
                        <th>VAT</th>
                    <?php endif; ?>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $i = 1;
                $total_no_of_items = 0;
                $grandTotal = 0;
                $totalVat = 0;
                $vatType = !empty($purchases[0]->vat_type) ? $purchases[0]->vat_type : 'none';
                $vatPercent = !empty($purchases[0]->vat_percent) ? (float)$purchases[0]->vat_percent : 18;
                
                foreach ($purchases as $item) { 
                    $qty = (float)$item->quantity;
                    $price = (float)$item->company_price;
                    $discountAmount = 0;

                    if ($item->discount_percent > 0) {
                        $discountAmount = ($price * $item->discount_percent) / 100;
                    } elseif ($item->discount_amount > 0) {
                        $discountAmount = (float)$item->discount_amount;
                    }

                    $netPrice = $price - $discountAmount;
                    $lineTotal = $netPrice * $qty;
                    
                    // Calculate VAT for this item if VAT per item
                    $itemVat = 0;
                    if ($vatType === 'vat_per_item') {
                        $itemVat = $lineTotal * ($vatPercent / 100);
                        $totalVat += $itemVat;
                    }
                    
                    $grandTotal += $lineTotal;
                    $total_no_of_items += 1;

                    ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td><?= strtoupper($item->product_name) ?></td>
                        <td><?= $qty ?></td>
                        <td><?= $item->uom ?></td>
                        <td><?= number_format($price, 2) ?></td>
                        <td>
                            <?php
                            if ($item->discount_percent > 0) {
                                echo $item->discount_percent . '%';
                            } elseif ($item->discount_amount > 0) {
                                echo 'Rs. ' . number_format($item->discount_amount, 2);
                            } else {
                                echo '-';
                            }
                            ?>
                        </td>
                        <td><?= number_format($lineTotal, 2) ?></td>
                        <?php if ($vatType === 'vat_per_item'): ?>
                            <td><?= number_format($itemVat, 2) ?></td>
                        <?php else: ?>
                        <?php endif; ?>
                        <td>
                            <button class="btn btn-sm btn-danger" onclick="deletePurchaseItem(<?= $item->po_item_id ?>)">
                                Delete
                            </button>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
            <tfoot class="table-light fw-bold">
                <tr>
                    <td colspan="6" class="text-end">Total</td>
                    <td <?= ($vatType === 'vat_per_item' || $vatType === 'vat_whole') ? '' : 'id="grandTotalCell"' ?> class="text-end" data-grand="<?= $grandTotal ?>"><?= number_format($grandTotal, 2) ?></td>
                    <?php if ($vatType === 'vat_per_item'): ?>
                        <td></td>
                    <?php endif; ?>
                    <td></td>
                </tr>
                <?php if ($vatType === 'vat_per_item' || $vatType === 'vat_whole'): ?>
                    <?php 
                    $vatAmount = 0;
                    if ($vatType === 'vat_per_item') {
                        $vatAmount = $totalVat;
                    } elseif ($vatType === 'vat_whole') {
                        $vatAmount = $grandTotal * ($vatPercent / 100);
                    }
                    ?>
                    <tr>
                        <td colspan="6" class="text-end">VAT (<?= $vatPercent ?>%)</td>
                        <td><?= number_format($vatAmount, 2) ?></td>
                        <?php if ($vatType === 'vat_per_item'): ?>
                            <td></td>
                        <?php endif; ?>
                        <td></td>
                    </tr>
                    <tr>
                        <td colspan="6" class="text-end">Subtotal</td>
                        <td id="grandTotalCell" class="text-end" data-grand="<?= $grandTotal + $vatAmount ?>" colspan="<?= ($vatType === 'vat_per_item') ? '2' : '1' ?>"><?= number_format($grandTotal + $vatAmount, 2) ?></td>
                        <td></td>
                    </tr>
                <?php endif; ?>
            </tfoot>
        </table>
    </div>
<?php } else {
    $total_no_of_items = 0;
     ?>
    <p class="text-muted">No purchase items found.</p>
<?php } ?>







<div class="text-end mt-3">
    <?php if($total_no_of_items > 0) { ?>
        <button type="button" class="btn btn-success" onclick="completePurchaseOrder()" id="completePurchaseOrderBtn">
        Complete Purchase Order</button>
    <?php }else{
        echo '<button type="button" class="btn btn-secondary" disabled>No items to complete</button>';
    } ?>
</div>
