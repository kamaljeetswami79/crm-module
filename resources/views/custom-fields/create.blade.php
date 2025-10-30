@extends('layouts.app')

@section('title', 'Create Custom Field - CRUD System')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="fas fa-plus"></i> Add New Custom Field</h1>
    <a href="{{ route('custom-fields.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back 
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form id="customFieldForm" method="POST" action="{{ route('custom-fields.store') }}">
            @csrf
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="name" class="form-label">Field Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               id="name" name="name" value="{{ old('name') }}" required>
                        <div class="form-text">Unique identifier (e.g., birthday, company_name)</div>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="label" class="form-label">Display Label <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('label') is-invalid @enderror" 
                               id="label" name="label" value="{{ old('label') }}" required>
                        <div class="form-text">User-friendly label (e.g., Birthday, Company Name)</div>
                        @error('label')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="type" class="form-label">Field Type <span class="text-danger">*</span></label>
                        <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                            <option value="">Select Type</option>
                            <option value="text" {{ old('type') == 'text' ? 'selected' : '' }}>Text</option>
                            <option value="email" {{ old('type') == 'email' ? 'selected' : '' }}>Email</option>
                            <option value="number" {{ old('type') == 'number' ? 'selected' : '' }}>Number</option>
                            <option value="date" {{ old('type') == 'date' ? 'selected' : '' }}>Date</option>
                            <option value="select" {{ old('type') == 'select' ? 'selected' : '' }}>Dropdown</option>
                            <option value="textarea" {{ old('type') == 'textarea' ? 'selected' : '' }}>Text Area</option>
                        </select>
                        @error('type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="sort_order" class="form-label">Sort Order</label>
                        <input type="number" class="form-control @error('sort_order') is-invalid @enderror" 
                               id="sort_order" name="sort_order" value="{{ old('sort_order', 0) }}" min="0">
                        <div class="form-text">Order in which fields appear (lower numbers first)</div>
                        @error('sort_order')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="required" name="required" value="1" 
                                   {{ old('required') ? 'checked' : '' }}>
                            <label class="form-check-label" for="required">
                                Required Field
                            </label>
                        </div>
                    </div>

                    <div id="optionsContainer" class="mb-3" style="display: none;">
                        <label class="form-label">Select Options</label>
                        <div id="optionsList">
                            <div class="input-group mb-2">
                                <input type="text" class="form-control" name="options[]" placeholder="Option 1">
                                <button type="button" class="btn btn-outline-danger" onclick="removeOption(this)">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="addOption()">
                            <i class="fas fa-plus"></i> Add Option
                        </button>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <button type="button" class="btn btn-secondary" onclick="window.location.href='{{ route('custom-fields.index') }}'">
                    Cancel
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Create Custom Field
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    $('#type').on('change', function() {
        if ($(this).val() === 'select') {
            $('#optionsContainer').show();
        } else {
            $('#optionsContainer').hide();
        }
    });

    $('#type').trigger('change');

    $('#customFieldForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                showSuccess(response.message);
                setTimeout(() => {
                    window.location.href = '{{ route("custom-fields.index") }}';
                }, 1500);
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    let errorMessage = 'Please fix the following errors:\n';
                    
                    for (const field in errors) {
                        errorMessage += `• ${errors[field][0]}\n`;
                    }
                    
                    showError(errorMessage);
                } else {
                    showError('An error occurred while creating the custom field.');
                }
            }
        });
    });
});

function addOption() {
    const optionsList = document.getElementById('optionsList');
    const optionCount = optionsList.children.length + 1;
    
    const optionDiv = document.createElement('div');
    optionDiv.className = 'input-group mb-2';
    optionDiv.innerHTML = `
        <input type="text" class="form-control" name="options[]" placeholder="Option ${optionCount}">
        <button type="button" class="btn btn-outline-danger" onclick="removeOption(this)">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    optionsList.appendChild(optionDiv);
}

function removeOption(button) {
    button.closest('.input-group').remove();
}
</script>
@endsection 