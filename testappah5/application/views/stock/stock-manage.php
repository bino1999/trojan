<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Stock</h4>

            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="<?php echo base_url(); ?>">Home</a></li>
                    <li class="breadcrumb-item active">Stock</li>
                </ol>
            </div>

        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">

        <style>
            .mainCard {
                margin: -10px;
            }
        </style>

        <div class="card mainCard">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-2">
                        <label class="form-label">Start Date (Optional)</label>
                        <input type="date" name="sdate" id="sdate" class="form-control" value="">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">End Date (Optional)</label>
                        <input type="date" name="edate" id="edate" class="form-control" value="">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Category</label>
                        <select name="filter_item_category" id="filter_item_category" class="form-select" onchange="loadAvailableStock();">
                            <option value="">Select Category</option>
                            <?php foreach ($productCategories as $category) { ?>
                                <option value="<?= $category->itemCategoryId ?>"><?= $category->itemCategoryName ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Brand</label>
                        <select name="filter_item_brand" id="filter_item_brand" class="form-select" onchange="loadAvailableStock();">
                            <option value="">Select Brand</option>
                            <?php foreach ($productBrands as $brand) { ?>
                                <option value="<?= $brand->itemBrandId ?>"><?= $brand->itemBrandName ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Supplier</label>
                        <select name="filter_supplier" id="filter_supplier" class="form-select" onchange="loadAvailableStock();">
                            <option value="">Select Supplier</option>
                            <?php foreach ($suppliers as $sup) { ?>
                                <option value="<?= $sup->supplier_id ?>"><?= $sup->name ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button class="btn btn-primary" onclick="loadAvailableStock();">Load Stock</button>
                            <button class="btn btn-secondary" onclick="clearFilters();">Clear Filters</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <div id="stockTable"></div>
            </div>
        </div>
    </div>
    <!--end col-->
</div>






<script>
    function loadAvailableStock() {
    let sdate = $('#sdate').val();
    let edate = $('#edate').val();
    let category_id = $('#filter_item_category').val();
    let brand_id = $('#filter_item_brand').val();
    let supplier_id = $('#filter_supplier').val();

    $.ajax({
        url: "<?php echo site_url('purchase/loadAvailableStock') ?>",
        type: "POST",
        data: {
            sdate: sdate,
            edate: edate,
            category_id: category_id,
            brand_id: brand_id,
            supplier_id: supplier_id
        },
        success: function(data) {
            $('#stockTable').html(data);
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.log(jqXHR.responseText);
        }
    });
}

function clearFilters() {
    $('#sdate').val('');
    $('#edate').val('');
    $('#filter_item_category').val('').trigger('change');
    $('#filter_item_brand').val('').trigger('change');
    $('#filter_supplier').val('').trigger('change');
    loadAvailableStock();
}

$(document).ready(function () {
    $('#filter_item_category').select2({
        placeholder: "Select Category",
        allowClear: true
    });
    $('#filter_item_brand').select2({
        placeholder: "Select Brand",
        allowClear: true
    });
    $('#filter_supplier').select2({
        placeholder: "Select Supplier",
        allowClear: true
    });

    loadAvailableStock(); // Load initial data
});

</script>