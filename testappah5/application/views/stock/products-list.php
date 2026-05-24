<div class="table-responsive">
    <table class="table table-striped table-hover">
    <thead class="table-dark">
        <tr>
            <th>ID</th>
            <th>Product Name</th>
            <th>Brand</th>
            <th>Category</th>
            <th>SKU</th>
            <th>Barcode</th>
            <th>Unit</th>
            <th>Sale Price</th>
            <th>Company Price</th>
            <th>Total Stock</th>
            <th>Purchase Order</th>
            <th>Inventory Type</th>
            <th>Status</th>
        </tr>
    </thead>
        <tbody>
            <?php if (empty($products)): ?>
                <tr>
                    <td colspan="13" class="text-center">No products found</td>
                </tr>
            <?php else: ?>
                <?php foreach ($products as $product): ?>
                    <tr>
                        <td><?= $product->product_id; ?></td>
                        <td>
                            <strong><?= htmlspecialchars($product->product_name); ?></strong>
                        </td>
                        <td><?= htmlspecialchars($product->brand_name ?? 'N/A'); ?></td>
                        <td><?= htmlspecialchars($product->category_name ?? 'N/A'); ?></td>
                        <td><?= htmlspecialchars($product->sku); ?></td>
                        <td><?= htmlspecialchars($product->barcode); ?></td>
                        <td>
                            <select name="unit[<?= $product->product_id; ?>]" class="form-select form-select-sm">
                                <option value="p" <?= $product->measurement_unit === 'p' ? 'selected' : ''; ?>>Pieces</option>
                                <option value="kg" <?= $product->measurement_unit === 'kg' ? 'selected' : ''; ?>>Kilogram</option>
                                <option value="g" <?= $product->measurement_unit === 'g' ? 'selected' : ''; ?>>Gram</option>
                                <option value="l" <?= $product->measurement_unit === 'l' ? 'selected' : ''; ?>>Liter</option>
                                <option value="ml" <?= $product->measurement_unit === 'ml' ? 'selected' : ''; ?>>Milliliter</option>
                            </select>
                        </td>
                        <td>
                            <input type="number" name="sale_price[<?= $product->product_id; ?>]" 
                                   class="form-control form-control-sm" 
                                   value="<?= $product->sale_price; ?>" 
                                   step="0.01" min="0">
                        </td>
                        <td>
                            <input type="number" name="company_price[<?= $product->product_id; ?>]" 
                                   class="form-control form-control-sm" 
                                   value="<?= $product->cost_price ?? 0; ?>" 
                                   step="0.01" min="0">
                        </td>
                        <td>
                            <input type="number" name="total_stock[<?= $product->product_id; ?>]" 
                                   class="form-control form-control-sm stock-input" 
                                   data-product-id="<?= $product->product_id; ?>"
                                   value="<?= $product->total_available_stock; ?>" 
                                   step="0.001" min="0">
                        </td>
                        <td>
                            <select name="po_id[<?= $product->product_id; ?>]" 
                                    class="form-select form-select-sm po-select" 
                                    data-product-id="<?= $product->product_id; ?>">
                                <option value="">Select PO</option>
                                <?php 
                                $existing_po_ids = $product->existing_po_ids ? explode(',', $product->existing_po_ids) : [];
                                foreach ($purchase_orders as $po): 
                                    $is_selected = in_array($po->po_id, $existing_po_ids);
                                ?>
                                    <option value="<?= $po->po_id; ?>" <?= $is_selected ? 'selected' : ''; ?>>
                                        <?= $po->bill_no; ?> (<?= date('Y-m-d', strtotime($po->bill_date)); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td>
                            <select name="inventory_type[<?= $product->product_id; ?>]" class="form-select form-select-sm">
                                <option value="sale" <?= $product->inventory_type === 'sale' ? 'selected' : ''; ?>>Sale</option>
                                <option value="internal" <?= $product->inventory_type === 'internal' ? 'selected' : ''; ?>>Internal</option>
                                <option value="both" <?= $product->inventory_type === 'both' ? 'selected' : ''; ?>>Both</option>
                            </select>
                        </td>
                        <td>
                            <select name="is_active[<?= $product->product_id; ?>]" class="form-select form-select-sm">
                                <option value="1" <?= $product->is_active ? 'selected' : ''; ?>>Active</option>
                                <option value="0" <?= !$product->is_active ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
