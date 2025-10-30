/**
 * Custom Fields Management JavaScript
 * Handles all custom field-related functionality including form handling and status toggling
 */

$(document).ready(function() {
    // Initialize custom fields functionality
    initCustomFieldsForm();
    initCustomFieldsToggle();
});

// Custom field form handling
function initCustomFieldsForm() {
    // Handle field type change
    $('#type').on('change', function() {
        if ($(this).val() === 'select') {
            $('#optionsContainer').show();
        } else {
            $('#optionsContainer').hide();
        }
    });

    // Initialize options container visibility
    if ($('#type').val() === 'select') {
        $('#optionsContainer').show();
    }

    // Add option
    $('#addOption').on('click', function() {
        const optionCount = $('#optionsList .input-group').length + 1;
        const newOption = `
            <div class="input-group mb-2">
                <input type="text" class="form-control" name="options[]" placeholder="Option ${optionCount}">
                <button type="button" class="btn btn-outline-danger remove-option">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        $('#optionsList').append(newOption);
        updateRemoveButtons();
    });

    // Remove option
    $(document).on('click', '.remove-option', function() {
        $(this).closest('.input-group').remove();
        updateRemoveButtons();
    });

    function updateRemoveButtons() {
        const options = $('#optionsList .input-group');
        if (options.length === 1) {
            options.find('.remove-option').hide();
        } else {
            options.find('.remove-option').show();
        }
    }

    // Initialize remove buttons
    updateRemoveButtons();

    // Load existing options for edit mode
    if (typeof customFieldOptions !== 'undefined' && customFieldOptions.length > 0) {
        $('#optionsList').empty();
        customFieldOptions.forEach(function(option) {
            const optionCount = $('#optionsList .input-group').length + 1;
            const existingOption = `
                <div class="input-group mb-2">
                    <input type="text" class="form-control" name="options[]" value="${option}" placeholder="Option ${optionCount}">
                    <button type="button" class="btn btn-outline-danger remove-option">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
            $('#optionsList').append(existingOption);
        });
        updateRemoveButtons();
    }
}

// Custom field status toggle
function initCustomFieldsToggle() {
    // This function is called from the toggle switch in the index view
}

// Toggle custom field status
function toggleStatus(fieldId) {
    $.ajax({
        url: `/custom-fields/${fieldId}/toggle-status`,
        type: 'PATCH',
        success: function(response) {
            showSuccess(response.message);
        },
        error: function() {
            showError('An error occurred while updating the status.');
        }
    });
}

// Custom field form validation
function validateCustomFieldForm() {
    var isValid = true;
    var errors = [];

    // Check required fields
    if (!$('#name').val().trim()) {
        errors.push('Field name is required');
        isValid = false;
    }

    if (!$('#label').val().trim()) {
        errors.push('Display label is required');
        isValid = false;
    }

    if (!$('#type').val()) {
        errors.push('Field type is required');
        isValid = false;
    }

    // Check if name contains only lowercase letters, numbers, and underscores
    var nameRegex = /^[a-z0-9_]+$/;
    if (!$('#name').val().match(nameRegex)) {
        errors.push('Field name can only contain lowercase letters, numbers, and underscores');
        isValid = false;
    }

    // Check select options if type is select
    if ($('#type').val() === 'select') {
        var options = [];
        $('#optionsList input[name="options[]"]').each(function() {
            if ($(this).val().trim()) {
                options.push($(this).val().trim());
            }
        });

        if (options.length === 0) {
            errors.push('At least one option is required for select fields');
            isValid = false;
        }

        // Check for duplicate options
        var uniqueOptions = [...new Set(options)];
        if (uniqueOptions.length !== options.length) {
            errors.push('Duplicate options are not allowed');
            isValid = false;
        }
    }

    if (!isValid) {
        showError(errors.join('<br>'));
    }

    return isValid;
}

// Initialize custom field form submission
function initCustomFieldFormSubmission() {
    $('#customFieldForm').on('submit', function(e) {
        if (!validateCustomFieldForm()) {
            e.preventDefault();
            return false;
        }
    });
}

// Initialize all custom field functionality
$(document).ready(function() {
    initCustomFieldsForm();
    initCustomFieldsToggle();
    initCustomFieldFormSubmission();
}); 