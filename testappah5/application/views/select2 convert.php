$('#addItemModal select').select2({
            dropdownParent: $('#addItemModal')
        });

        $(document).ready(function () {
            $('#supplier_id').select2({
                placeholder: "-- Select Supplier --",
                allowClear: true,
                width: '100%'
            });
        
            $('#product_id').select2({
                placeholder: "-- Select Product --",
                allowClear: true,
                width: '100%'
            });
        });
        