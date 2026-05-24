<?php if (empty($products)) : ?>
    <div class="alert alert-info">
        <strong>No internal use products found!</strong> 
        <br>Make sure you have completed purchase orders with items marked as "Internal Use" or "Both".
    </div>
<?php else : ?>
    <div class="table-responsive">
        <table class="table table-striped table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>Product Name</th>
                    <th>Brand</th>
                    <th>Category</th>
                    <th>Supplier</th>
                    <th>Available Stock</th>
                    <th>Sale Price</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product) : ?>
                    <tr>
                        <td>
                            <strong><?= strtoupper($product->product_name) ?></strong>
                            <?php if ($product->barcode) : ?>
                                <br><small class="text-muted">Barcode: <?= $product->barcode ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?= $product->brand_name ?? 'N/A' ?></td>
                        <td><?= $product->category_name ?? 'N/A' ?></td>
                        <td><?= $product->supplier_name ?? 'N/A' ?></td>
                        <td>
                            <span class="badge bg-success"><?= number_format($product->available_stock, 2) ?></span>
                            <br><small class="text-muted"><?= $product->uom ?? 'units' ?></small>
                        </td>
                        <td>
                            <strong class="text-primary"><?= number_format($product->sale_price, 2) ?></strong>
                        </td>
                        <td>
                            <button class="btn btn-success btn-sm" 
                                    onclick="addToCart(<?= $product->product_id ?>, <?= $product->po_item_id ?>, '<?= htmlspecialchars($product->product_name, ENT_QUOTES) ?>', <?= $product->available_stock ?>)">
                                <i class="fas fa-plus"></i> Add to Cart
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
