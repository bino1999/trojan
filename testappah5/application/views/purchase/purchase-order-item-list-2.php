<?php if (!empty($purchases)) { ?>
    <div class="table-responsive">
    <table id="table1" class="table table-striped table-responsive">
    <thead class="table-info">
                <tr>
                    <th>#</th>
                    <th style="text-align: center;">Product</th>
                    <th style="text-align: center;">Barcode</th>
                    <th style="text-align: center;">Category</th>
                    <th style="text-align: center;">Brand</th>
                    <th style="text-align: center;">Rack</th>
                    <th style="text-align: center;">Bin</th>
                    <th style="text-align: center;">Unit</th>
                    <th style="text-align: center;">Selling Price</th>
                    <th style="text-align: center;">Quantity</th>
                    <th style="text-align: center;">Buying Price</th>
                    <th style="text-align: center;">Discount</th>
                    <th style="text-align: center;">Total</th>
                    <?php if (!empty($purchases[0]->vat_type) && $purchases[0]->vat_type === 'vat_per_item'): ?>
                        <th style="text-align: center;">VAT</th>
                    <?php endif; ?>
                    <th style="text-align: center;">User</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $i = 1;
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
                    ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td><?= strtoupper($item->product_name) ?></td>
                        <td><?= strtoupper($item->barcode) ?></td>
                        <td><?= strtoupper($item->category_name) ?></td>
                        <td><?= strtoupper($item->brand_name) ?></td>
                        <td style="text-align: right;"><?= strtoupper($item->rack_no) ?></td>
                        <td style="text-align: right;"><?= strtoupper($item->bin_no) ?></td>
                        <td><?= $item->uom ?></td>
                        <td style="text-align: right;"><?= number_format($item->sale_price, 2) ?></td>
                        <td style="text-align: right;"><?= $qty ?></td>
                        <td style="text-align: right;"><?= number_format($price, 2) ?></td>
                        <td style="text-align: right;">
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
                        <td style="text-align: right;"><?= number_format($lineTotal, 2) ?></td>
                        <?php if ($vatType === 'vat_per_item'): ?>
                            <td style="text-align: right;"><?= number_format($itemVat, 2) ?></td>
                        <?php endif; ?>
                        <td><?= $item->created_by ?></td>
                    </tr>
                <?php } ?>
            </tbody>
            <tfoot class="table-light fw-bold">
                <tr>
                    <td colspan="12" class="text-end">Total</td>
                    <td><?= number_format($grandTotal, 2) ?></td>
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
                        <td colspan="12" class="text-end">VAT (<?= $vatPercent ?>%)</td>
                        <td><?= number_format($vatAmount, 2) ?></td>
                        <?php if ($vatType === 'vat_per_item'): ?>
                            <td></td>
                        <?php endif; ?>
                        <td></td>
                    </tr>
                    <tr>
                        <td colspan="12" class="text-end">Subtotal</td>
                        <td colspan="<?= ($vatType === 'vat_per_item') ? '2' : '1' ?>"><?= number_format($grandTotal + $vatAmount, 2) ?></td>
                        <td></td>
                    </tr>
                <?php endif; ?>
            </tfoot>
        </table>
    </div>
<?php } else { ?>
    <p class="text-muted">No purchase items found.</p>
<?php } ?>
