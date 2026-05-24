<div class="table-responsive">
    <table id="productsTable" class="table table-striped table-hover table-sm table-responsive">
        <thead class="table-info">
            <tr>
                <th>Product</th>
                <th class="text-end">Price</th>
                <th>Brand</th>
                <th class="text-center" width="10%">Add</th>
                <th>Unit</th>
                <th>Barcode</th>
                <th>Category</th>
            </tr>
        </thead>
        <tbody id="productsBody">
            <?php foreach ($products as $products) : ?>
                <tr class="products-row">
                    <td class="f12"><?= $products->product_name ?></td>
                    <td class="f12 text-end"><?= number_format($products->sale_price, 2) ?></td>
                    <td class="f12"><?= $products->brand_name ?></td>

                    <td class="text-center">
                        <button type="button" class="btn btn-info btn-sm" onclick="addProductsToJob(<?= $products->product_id  ?>, <?= json_encode($products->product_name) ?>, '<?= $products->measurement_unit ?>')">
                            <i class="fa fa-plus"></i>
                        </button>
                    </td>
                    <td class="f12"><?= $products->measurement_unit ?></td>
                    <td class="f12"><?= $products->barcode ?></td>
                    <td class="f12"><?= $products->category_name ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
    productsTableToDatatable();
</script>