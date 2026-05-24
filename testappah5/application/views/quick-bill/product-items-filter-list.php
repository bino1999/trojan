<div class="table-responsive">
    <table id="productsTable" class="table table-sm table-striped">
        <thead class="table-info">
            <tr>
                <th>Product</th>
                <th>SKU</th>
                <th>Supplier</th>
                <th>Brand</th>
                <th>Category</th>
                <th class="text-end">Price</th>
                <th>Unit</th>
                <th>Available</th>
                <th class="text-center" width="10%">Add</th>
            </tr>
        </thead>
        <tbody id="productsBody">
            <?php foreach ($products as $products) : ?>
                <tr class="products-row">
                    <td class="f12"><?= $products->product_name ?></td>
                    <td class="f12"><?= $products->sku ?></td>
                    <td class="f12"><?= $products->supplier_name ?></td>
                    <td class="f12"><?= $products->brand_name ?></td>
                    <td class="f12"><?= $products->category_name ?></td>
                    <td class="f12 text-end"><?= number_format($products->sale_price, 2) ?></td>
                    <td class="f12"><?= $products->measurement_unit ?></td>
                    <td class="f12 text-end"><?= $products->available_stock ?></td>
                    <td class="text-center">
                        <button type="button" class="btn btn-primary btn-sm" onclick="addProductsToJob(<?= $products->po_item_id  ?>, <?= $products->product_id  ?>, '<?= htmlspecialchars($products->product_name, ENT_QUOTES, 'UTF-8') ?>', '<?= $products->measurement_unit ?>', '<?= $products->discount_percent ?>', '<?= $products->discount_amount ?>')">
                            <i class="fa fa-plus"></i>
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>