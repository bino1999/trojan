<?php if (empty($categorizedPermissions)): ?>
    <div class="alert alert-warning text-center">
        <i class="fas fa-exclamation-triangle"></i> No permissions available.
    </div>
<?php else: ?>
    <div class="permissions-container">
        <!-- Categorized Permissions -->
        <div class="categorized-permissions">
            <?php foreach ($categorizedPermissions as $category => $permissions): ?>
                <div class="permission-category mb-4">
                    <!-- Category Header with Select All -->
                    <div class="category-header bg-light p-3 rounded-top border">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 font-weight-bold text-primary">
                                <i class="fas fa-folder"></i> <?php echo $category; ?>
                                <small class="text-muted">(<?php echo count($permissions); ?> permissions)</small>
                            </h6>
                            <div class="category-controls">
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectCategoryPermissions('<?php echo $category; ?>')">
                                    <i class="fas fa-check"></i> All
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="deselectCategoryPermissions('<?php echo $category; ?>')">
                                    <i class="fas fa-times"></i> None
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Category Permissions -->
                    <div class="category-permissions border border-top-0 rounded-bottom p-3">
                        <div class="row">
                            <?php foreach ($permissions as $index => $permission): ?>
                                <?php 
                                $isAssigned = false;
                                foreach ($assignedPermissions as $assigned) {
                                    if ($assigned->permission_id == $permission->permission_id) {
                                        $isAssigned = true;
                                        break;
                                    }
                                }
                                ?>
                                <div class="col-md-6 col-lg-4 mb-2">
                                    <div class="permission-item">
                                        <div class="form-check">
                                            <input class="form-check-input permission-checkbox" 
                                                   type="checkbox" 
                                                   name="permissions[]" 
                                                   value="<?php echo $permission->permission_id; ?>"
                                                   id="permission_<?php echo $permission->permission_id; ?>"
                                                   data-category="<?php echo $category; ?>"
                                                   <?php echo $isAssigned ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="permission_<?php echo $permission->permission_id; ?>">
                                                <strong><?php echo ucfirst(str_replace('.', ' ', $permission->permission_name)); ?></strong>
                                                <br>
                                                <small class="text-muted"><?php echo $permission->description; ?></small>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
    function selectCategoryPermissions(category) {
        $('.permission-checkbox[data-category="' + category + '"]').prop('checked', true);
        updateCategoryStates();
    }

    function deselectCategoryPermissions(category) {
        $('.permission-checkbox[data-category="' + category + '"]').prop('checked', false);
        updateCategoryStates();
    }

    function updateCategoryStates() {
        // Update category select all buttons based on current state
        $('.permission-category').each(function() {
            var category = $(this).find('.permission-checkbox').first().data('category');
            var totalCheckboxes = $('.permission-checkbox[data-category="' + category + '"]').length;
            var checkedCheckboxes = $('.permission-checkbox[data-category="' + category + '"]:checked').length;
            
            var categoryControls = $(this).find('.category-controls');
            
            if (checkedCheckboxes === 0) {
                categoryControls.find('button:first').removeClass('btn-primary').addClass('btn-outline-primary');
                categoryControls.find('button:last').removeClass('btn-secondary').addClass('btn-outline-secondary');
            } else if (checkedCheckboxes === totalCheckboxes) {
                categoryControls.find('button:first').removeClass('btn-outline-primary').addClass('btn-primary');
                categoryControls.find('button:last').removeClass('btn-secondary').addClass('btn-outline-secondary');
            } else {
                categoryControls.find('button:first').removeClass('btn-primary').addClass('btn-outline-primary');
                categoryControls.find('button:last').removeClass('btn-secondary').addClass('btn-outline-secondary');
            }
        });
    }

    // Update category states when individual checkboxes change
    $(document).on('change', '.permission-checkbox', function() {
        updateCategoryStates();
    });

    // Initialize category states on page load
    $(document).ready(function() {
        updateCategoryStates();
    });
    </script>

    <style>
    .permission-category {
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        overflow: hidden;
    }
    
    .category-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-bottom: 1px solid #dee2e6;
    }
    
    .permission-item {
        padding: 0.5rem;
        border-radius: 0.25rem;
        transition: background-color 0.2s;
    }
    
    .permission-item:hover {
        background-color: #f8f9fa;
    }
    
    .form-check-label {
        cursor: pointer;
        font-size: 0.9rem;
    }
    
    .category-controls .btn {
        margin-left: 0.25rem;
    }
    
    .permissions-container {
        max-height: 70vh;
        overflow-y: auto;
    }
    </style>
<?php endif; ?>