<select name="product_id" id="product_id" class="form-select" onchange="setDefaultValues();" required>
    <option value="">Select Product</option>
    <?php foreach ($products as $product) { ?>
        <option value="<?= $product->product_id ?>"
            data-brand-id="<?= $product->item_brand_id ?>"
            data-category-id="<?= $product->item_category_id ?>"
            data-brand="<?= $product->brand_name ?>"
            data-category="<?= $product->category_name ?>"
            data-measurement_unit="<?= $product->measurement_unit ?>"
            data-sale_price="<?= $product->sale_price ?>"
            data-reorder_level="<?= $product->reorder_level ?>"
            data-barcode="<?= $product->barcode ?>"
            data-inventory_type="<?= strtolower($product->inventory_type ?? 'sale') ?>"><?= strtoupper($product->product_name) ?>
        </option>
    <?php } ?>
</select>