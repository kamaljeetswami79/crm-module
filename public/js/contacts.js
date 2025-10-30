$(document).ready(function() {
    initContacts();
});

function initContacts() {
    $('#searchForm').on('submit', function(e) {
        e.preventDefault();
        var queryString = $(this).serialize();
        queryString = queryString.replace(/[^&]+=&/g, '').replace(/[^&]+=$/, '');
        var url = '/contacts' + (queryString ? '?' + queryString : '');
        fetchContacts(url);
    });
    
    $(document).on('click', '.pagination a', function(e) {
        e.preventDefault();
        var url = $(this).attr('href');
        
        if (url && !url.startsWith('http')) {
            url = window.location.origin + url;
        }
        
        fetchContacts(url);
    });
}

function fetchContacts(url) {
    $.ajax({
        url: url,
        type: 'GET',
        dataType: 'html',
        success: function(data) {
            $('.table-container').html(data);
            // Reinitialize Fancybox for new images
            if (typeof Fancybox !== 'undefined') {
                Fancybox.bind('[data-fancybox]', {
                    loop: true,
                    buttons: ["zoom", "slideShow", "fullScreen", "thumbs", "close"],
                    animationEffect: "fade",
                    transitionEffect: "slide"
                });
            }
        },
        error: function(xhr) {
            showError('Error loading data');
        }
    });
}

function initContactForm() {
    $('#contactForm').on('submit', function(e) {
        e.preventDefault();
        
        var formData = new FormData(this);
        
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    showSuccess(response.message);
                    setTimeout(() => {
                        window.location.href = '/contacts';
                    }, 1000);
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    var errors = xhr.responseJSON.errors;
                    var errorMessages = [];
                    
                    for (var field in errors) {
                        errorMessages.push(errors[field][0]);
                    }
                    
                    showError(errorMessages.join('<br>'));
                } else {
                    showError('An error occurred while saving the contact.');
                }
            }
        });
    });
}

function initImagePreview() {
    $('#profile_image').on('change', function() {
        var file = this.files[0];
        if (file) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#imagePreview').attr('src', e.target.result).show();
            };
            reader.readAsDataURL(file);
        }
    });
}

function initCustomFields() {
    $(document).on('change', '.custom-field-type', function() {
        var fieldType = $(this).val();
        var fieldContainer = $(this).closest('.custom-field-container');
        var valueField = fieldContainer.find('.custom-field-value');
        
        valueField.val('');
        
        fieldContainer.find('.field-input').hide();
        fieldContainer.find('.field-input[data-type="' + fieldType + '"]').show();
        
        if (fieldType === 'select') {
            fieldContainer.find('.select-options').show();
        } else {
            fieldContainer.find('.select-options').hide();
        }
    });
    
    $(document).on('click', '.add-option', function() {
        var optionsContainer = $(this).siblings('.options-list');
        var optionCount = optionsContainer.find('.option-item').length + 1;
        
        var newOption = `
            <div class="option-item input-group mb-2">
                <input type="text" class="form-control" name="options[]" placeholder="Option ${optionCount}">
                <button type="button" class="btn btn-outline-danger remove-option">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        
        optionsContainer.append(newOption);
        updateRemoveButtons(optionsContainer);
    });
    
    $(document).on('click', '.remove-option', function() {
        $(this).closest('.option-item').remove();
        updateRemoveButtons($(this).closest('.options-list'));
    });
}

function updateRemoveButtons(container) {
    var options = container.find('.option-item');
    if (options.length === 1) {
        options.find('.remove-option').hide();
    } else {
        options.find('.remove-option').show();
    }
}

function initMergeContacts() {
    $('#selectAll').on('change', function() {
        $('.contact-checkbox').prop('checked', $(this).prop('checked'));
        updateMergeButton();
    });

    $(document).on('change', '.contact-checkbox', function() {
        updateMergeButton();
        updateSelectAll();
        
        var row = $(this).closest('tr');
        if ($(this).is(':checked')) {
            row.addClass('selected');
        } else {
            row.removeClass('selected');
        }
    });

    $('#mergeContactsBtn').on('click', function() {
        var selectedContacts = $('.contact-checkbox:checked');
        if (selectedContacts.length !== 2) {
            showError('Please select exactly 2 contacts to merge.');
            return;
        }

        var contactIds = selectedContacts.map(function() {
            return $(this).val();
        }).get();

        showMergeModal(contactIds);
    });

    // Confirm merge
    $('#confirmMergeBtn').on('click', function() {
        var masterId = $('input[name="master_contact"]:checked').val();
        var mergeId = $('input[name="merge_contact"]:checked').val();

        if (!masterId || !mergeId) {
            showError('Please select which contact will be the master.');
            return;
        }

        performMerge(masterId, mergeId);
    });
}

function updateMergeButton() {
    var selectedCount = $('.contact-checkbox:checked').length;
    var mergeBtn = $('#mergeContactsBtn');
    
    if (selectedCount === 2) {
        mergeBtn.prop('disabled', false).text('Merge Selected (' + selectedCount + ')');
    } else {
        mergeBtn.prop('disabled', true).text('Merge Selected (Select 2 contacts)');
    }
}

function updateSelectAll() {
    var totalCheckboxes = $('.contact-checkbox').length;
    var checkedCheckboxes = $('.contact-checkbox:checked').length;
    
    if (checkedCheckboxes === 0) {
        $('#selectAll').prop('indeterminate', false).prop('checked', false);
    } else if (checkedCheckboxes === totalCheckboxes) {
        $('#selectAll').prop('indeterminate', false).prop('checked', true);
    } else {
        $('#selectAll').prop('indeterminate', true);
    }
}

function showMergeModal(contactIds) {
    $.ajax({
        url: '/contacts/by-ids',
        type: 'GET',
        data: { ids: contactIds.join(',') },
        success: function(response) {
            if (response.success) {
                populateMergeModal(response.contacts);
                $('#mergeModal').modal('show');
            } else {
                showError('Error loading contact details for merge.');
            }
        },
        error: function() {
            showError('Error loading contact details for merge.');
        }
    });
}

function populateMergeModal(contacts) {
    var modalBody = $('#mergeModal .modal-body');
    var html = `
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i>
            <strong>Warning:</strong> This action will merge the selected contacts. The master contact will be kept, and all data from the secondary contact will be preserved in the master record.
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <h5><i class="fas fa-crown text-warning"></i> Select Master Contact (Will be kept):</h5>
                ${contacts.map(function(contact, index) {
                    var profileImage = contact.profile_image ? 
                        '/storage/profile_images/' + contact.profile_image : 
                        '/images/default-profile.png';
                    return `
                        <div class="card mb-3">
                            <div class="card-body">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="master_contact" 
                                           id="master_${contact.id}" value="${contact.id}" ${index === 0 ? 'checked' : ''}>
                                    <label class="form-check-label" for="master_${contact.id}">
                                        <div class="d-flex align-items-center">
                                            <img src="${profileImage}" 
                                                 class="rounded-circle me-2" style="width: 40px; height: 40px;">
                                            <div>
                                                <strong>${contact.name}</strong><br>
                                                <small><i class="fas fa-envelope"></i> ${contact.email}</small><br>
                                                <small><i class="fas fa-phone"></i> ${contact.phone}</small><br>
                                                <small><i class="fas fa-user"></i> ${contact.gender}</small>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    `;
                }).join('')}
            </div>
            <div class="col-md-6">
                <h5><i class="fas fa-arrow-right text-danger"></i> Secondary Contact (Will be merged):</h5>
                ${contacts.map(function(contact, index) {
                    var profileImage = contact.profile_image ? 
                        '/storage/profile_images/' + contact.profile_image : 
                        '/images/default-profile.png';
                    return `
                        <div class="card mb-3">
                            <div class="card-body">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="merge_contact" 
                                           id="merge_${contact.id}" value="${contact.id}" ${index === 1 ? 'checked' : ''}>
                                    <label class="form-check-label" for="merge_${contact.id}">
                                        <div class="d-flex align-items-center">
                                            <img src="${profileImage}" 
                                                 class="rounded-circle me-2" style="width: 40px; height: 40px;">
                                            <div>
                                                <strong>${contact.name}</strong><br>
                                                <small><i class="fas fa-envelope"></i> ${contact.email}</small><br>
                                                <small><i class="fas fa-phone"></i> ${contact.phone}</small><br>
                                                <small><i class="fas fa-user"></i> ${contact.gender}</small>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    `;
                }).join('')}
            </div>
        </div>
        
        <div class="alert alert-info mt-3">
            <h6><i class="fas fa-info-circle"></i> Fill Gaps Merge Strategy:</h6>
            <ul class="mb-0">
                <li><strong>Master Contact Priority:</strong> All existing master contact data is preserved</li>
                <li><strong>Missing Information:</strong> Only missing/empty fields are filled from secondary contact</li>
                <li><strong>Emails & Phones:</strong> Added only if master lacks them, otherwise combined if different</li>
                <li><strong>Custom Fields:</strong> Added only if master doesn't have the field or has empty value</li>
                <li><strong>Files:</strong> Transferred only if master lacks profile image or documents</li>
                <li><strong>Data Safety:</strong> Secondary contact is preserved as read-only record</li>
            </ul>
        </div>
    `;
    
    modalBody.html(html);
}

function performMerge(masterId, mergeId) {
    // Show loading indicator
    Swal.fire({
        title: 'Merging Contacts...',
        text: 'Please wait while we merge the contacts.',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    $.ajax({
        url: '/contacts/merge-with-master',
        type: 'POST',
        data: {
            master_id: masterId,
            merge_id: mergeId,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            // Close loading indicator
            Swal.close();
            
            console.log('Merge response:', response);
            
            if (response.success) {
                // Show detailed merge summary
                if (response.merge_summary) {
                    console.log('Showing merge summary with data:', response.merge_summary);
                    showMergeSummary(response.merge_summary, response.master_contact);
                } else {
                    console.log('No merge summary found, showing fallback message');
                    // Fallback success message
                    showSuccess('Contacts merged successfully!');
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                }
                $('#mergeModal').modal('hide');
            }
        },
        error: function(xhr) {
            // Close loading indicator
            Swal.close();
            
            var errorMessage = 'An error occurred while merging contacts.';
            
            if (xhr.responseJSON) {
                if (xhr.status === 422) {
                    // Validation errors
                    if (xhr.responseJSON.errors) {
                        var errorMessages = [];
                        for (var field in xhr.responseJSON.errors) {
                            errorMessages.push(xhr.responseJSON.errors[field][0]);
                        }
                        errorMessage = errorMessages.join('<br>');
                    } else if (xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                } else if (xhr.responseJSON.message) {
                    // Other error messages from the server
                    errorMessage = xhr.responseJSON.message;
                }
            }
            
            showError(errorMessage);
            $('#mergeModal').modal('hide');
        }
    });
}

function showMergeSummary(mergeSummary, masterContact) {
    var summaryHtml = `
        <div class="alert alert-success">
            <h5><i class="fas fa-check-circle"></i> Merge Completed Successfully!</h5>
            <p><strong>Master Contact:</strong> ${masterContact.name}</p>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h6><i class="fas fa-info-circle"></i> Merge Summary</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Data Merged:</h6>
                        <ul class="list-unstyled">
                            ${mergeSummary.emails_merged ? '<li><i class="fas fa-envelope text-success"></i> Emails combined</li>' : ''}
                            ${mergeSummary.phones_merged ? '<li><i class="fas fa-phone text-success"></i> Phone numbers combined</li>' : ''}
                            ${(mergeSummary.files_merged && mergeSummary.files_merged.length > 0) ? '<li><i class="fas fa-file text-success"></i> Files transferred</li>' : ''}
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6>Custom Fields:</h6>
                        <ul class="list-unstyled">
                            ${(mergeSummary.custom_fields_added && mergeSummary.custom_fields_added.length > 0) ? `<li><i class="fas fa-plus text-info"></i> ${mergeSummary.custom_fields_added.length} fields added</li>` : ''}
                            ${(mergeSummary.custom_fields_merged && mergeSummary.custom_fields_merged.length > 0) ? `<li><i class="fas fa-merge text-warning"></i> ${mergeSummary.custom_fields_merged.length} fields merged</li>` : ''}
                            ${(mergeSummary.custom_fields_conflicted && mergeSummary.custom_fields_conflicted.length > 0) ? `<li><i class="fas fa-exclamation-triangle text-danger"></i> ${mergeSummary.custom_fields_conflicted.length} conflicts resolved</li>` : ''}
                        </ul>
                    </div>
                </div>
                
                ${(mergeSummary.custom_fields_added && mergeSummary.custom_fields_added.length > 0) ? `
                <div class="mt-3">
                    <h6><i class="fas fa-plus-circle text-info"></i> Custom Fields Added:</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-info">
                            <thead>
                                <tr>
                                    <th>Field Name</th>
                                    <th>Type</th>
                                    <th>Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${mergeSummary.custom_fields_added.map(function(field) {
                                    return `<tr>
                                        <td><strong>${field.field_name || 'Unknown Field'}</strong></td>
                                        <td><span class="badge bg-secondary">${field.field_type || 'text'}</span></td>
                                        <td>${field.value || 'N/A'}</td>
                                    </tr>`;
                                }).join('')}
                            </tbody>
                        </table>
                    </div>
                </div>
                ` : ''}
                
                ${(mergeSummary.custom_fields_merged && mergeSummary.custom_fields_merged.length > 0) ? `
                <div class="mt-3">
                    <h6><i class="fas fa-merge text-warning"></i> Custom Fields Merged:</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-warning">
                            <thead>
                                <tr>
                                    <th>Field Name</th>
                                    <th>Type</th>
                                    <th>Strategy</th>
                                    <th>Master Value</th>
                                    <th>Merged Value</th>
                                    <th>Result</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${mergeSummary.custom_fields_merged.map(function(field) {
                                    return `<tr>
                                        <td><strong>${field.field_name || 'Unknown Field'}</strong></td>
                                        <td><span class="badge bg-secondary">${field.field_type || 'text'}</span></td>
                                        <td><span class="badge bg-info">${field.strategy_used || 'append_unique'}</span></td>
                                        <td><code>${field.master_value || 'N/A'}</code></td>
                                        <td><code>${field.merged_value || 'N/A'}</code></td>
                                        <td><span class="badge bg-success">Merged</span></td>
                                    </tr>`;
                                }).join('')}
                            </tbody>
                        </table>
                    </div>
                </div>
                ` : ''}
                
                ${(mergeSummary.custom_fields_conflicted && mergeSummary.custom_fields_conflicted.length > 0) ? `
                <div class="mt-3">
                    <h6><i class="fas fa-exclamation-triangle text-danger"></i> Conflicts Resolved:</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-danger">
                            <thead>
                                <tr>
                                    <th>Field Name</th>
                                    <th>Resolution</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${mergeSummary.custom_fields_conflicted.map(function(field) {
                                    return `<tr>
                                        <td><strong>${field.field_name || 'Unknown Field'}</strong></td>
                                        <td><i class="fas fa-check-circle text-success"></i> ${field.resolution || 'Values combined'}</td>
                                    </tr>`;
                                }).join('')}
                            </tbody>
                        </table>
                    </div>
                </div>
                ` : ''}
                
                <div class="alert alert-info mt-3">
                    <h6><i class="fas fa-cogs"></i> Merge Strategies Used:</h6>
                    <ul class="mb-0">
                        <li><strong>append_unique:</strong> Combines different values (text, email, textarea)</li>
                        <li><strong>keep_master:</strong> Preserves master's value (select fields)</li>
                        <li><strong>keep_latest:</strong> Keeps most recent date (date fields)</li>
                        <li><strong>keep_higher:</strong> Keeps higher numeric value (number fields)</li>
                        <li><strong>keep_non_null:</strong> Keeps non-null value when one is empty</li>
                    </ul>
                </div>
                
                <div class="alert alert-success mt-3">
                    <i class="fas fa-shield-alt"></i>
                    <strong>Data Integrity:</strong> The secondary contact has been marked as merged and is preserved for audit purposes. No data was permanently lost.
                </div>
            </div>
        </div>
    `;
    
    // Show the summary in a modal
    Swal.fire({
        title: 'Advanced Merge Summary',
        html: summaryHtml,
        width: '900px',
        confirmButtonText: 'View Updated Contact',
        showCancelButton: true,
        cancelButtonText: 'Close',
        icon: 'success',
        allowOutsideClick: false,
        allowEscapeKey: false,
        timer: undefined,
        timerProgressBar: false
    }).then((result) => {
        if (result.isConfirmed) {
            // Redirect to the master contact view
            window.location.href = '/contacts/' + masterContact.id;
        } else {
            // If user clicks close, refresh the page to show updated contact list
            window.location.reload();
        }
    });
}

// Initialize all contact functionality
$(document).ready(function() {
    initContacts();
    initContactForm();
    initImagePreview();
    initCustomFields();
    initMergeContacts();
}); 