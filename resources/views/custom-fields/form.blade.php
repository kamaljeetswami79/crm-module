@extends('layouts.app')

@section('title', isset($custom_field) ? ($mode === 'edit' ? 'Edit Custom Field' : 'View Custom Field') : 'Create Custom Field')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>
        <i class="fas fa-cogs"></i> 
        @if(isset($custom_field))
            @if($mode === 'edit')
                Edit Custom Field
            @else
                View Custom Field
            @endif
        @else
            Create Custom Field
        @endif
    </h1>
    <a href="{{ route('custom-fields.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to List
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                @if(isset($custom_field) && $mode === 'show')
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Field Name:</label>
                                <p class="form-control-plaintext">{{ $custom_field->name }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Display Label:</label>
                                <p class="form-control-plaintext">{{ $custom_field->label }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Field Type:</label>
                                <p class="form-control-plaintext">
                                    <span class="badge bg-primary">{{ ucfirst($custom_field->type) }}</span>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Required:</label>
                                <p class="form-control-plaintext">
                                    @if($custom_field->required)
                                        <span class="badge bg-success">Yes</span>
                                    @else
                                        <span class="badge bg-secondary">No</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Sort Order:</label>
                                <p class="form-control-plaintext">{{ $custom_field->sort_order ?? 'Not set' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Status:</label>
                                <p class="form-control-plaintext">
                                    @if($custom_field->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-danger">Inactive</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    @if($custom_field->type === 'select' && $custom_field->options)
                        <div class="mb-3">
                            <label class="form-label fw-bold">Options:</label>
                            <div class="form-control-plaintext">
                                @foreach($custom_field->options as $option)
                                    <span class="badge bg-info me-1">{{ $option }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label fw-bold">Created:</label>
                        <p class="form-control-plaintext">{{ $custom_field->created_at ? $custom_field->created_at->format('M d, Y H:i') : 'Not available' }}</p>
                    </div>

                    @if($custom_field->updated_at && $custom_field->updated_at != $custom_field->created_at)
                        <div class="mb-3">
                            <label class="form-label fw-bold">Last Updated:</label>
                            <p class="form-control-plaintext">{{ $custom_field->updated_at->format('M d, Y H:i') }}</p>
                        </div>
                    @endif

                    <div class="d-flex gap-2">
                        <a href="{{ route('custom-fields.edit', $custom_field) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <button type="button" class="btn btn-danger" 
                                onclick="confirmDelete('{{ route('custom-fields.destroy', $custom_field) }}', 'Are you sure you want to delete this custom field?')">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>

                @else
                    <form action="{{ isset($custom_field) ? route('custom-fields.update', $custom_field) : route('custom-fields.store') }}" 
                          method="POST" id="customFieldForm">
                        @csrf
                        @if(isset($custom_field))
                            @method('PUT')
                        @endif

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Field Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" 
                                           value="{{ old('name', $custom_field->name ?? '') }}" 
                                           placeholder="e.g., company, department, position"
                                           {{ isset($custom_field) && $mode === 'show' ? 'readonly' : '' }}>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">Internal field name (no spaces, lowercase)</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="label" class="form-label">Display Label <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('label') is-invalid @enderror" 
                                           id="label" name="label" 
                                           value="{{ old('label', $custom_field->label ?? '') }}" 
                                           placeholder="e.g., Company, Department, Position"
                                           {{ isset($custom_field) && $mode === 'show' ? 'readonly' : '' }}>
                                    @error('label')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">Label shown to users</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="type" class="form-label">Field Type <span class="text-danger">*</span></label>
                                    <select class="form-select @error('type') is-invalid @enderror" 
                                            id="type" name="type" 
                                            {{ isset($custom_field) && $mode === 'show' ? 'disabled' : '' }}>
                                        <option value="">Select Type</option>
                                        <option value="text" {{ old('type', $custom_field->type ?? '') == 'text' ? 'selected' : '' }}>Text</option>
                                        <option value="email" {{ old('type', $custom_field->type ?? '') == 'email' ? 'selected' : '' }}>Email</option>
                                        <option value="number" {{ old('type', $custom_field->type ?? '') == 'number' ? 'selected' : '' }}>Number</option>
                                        <option value="date" {{ old('type', $custom_field->type ?? '') == 'date' ? 'selected' : '' }}>Date</option>
                                        <option value="select" {{ old('type', $custom_field->type ?? '') == 'select' ? 'selected' : '' }}> Dropdown</option>
                                        <option value="textarea" {{ old('type', $custom_field->type ?? '') == 'textarea' ? 'selected' : '' }}>Text Area</option>
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
                                           id="sort_order" name="sort_order" 
                                           value="{{ old('sort_order', $custom_field->sort_order ?? '') }}" 
                                           min="0" placeholder="0"
                                           {{ isset($custom_field) && $mode === 'show' ? 'readonly' : '' }}>
                                    @error('sort_order')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">Order in which fields appear (lower numbers first)</small>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input @error('required') is-invalid @enderror" 
                                       type="checkbox" id="required" name="required" value="1"
                                       {{ old('required', $custom_field->required ?? false) ? 'checked' : '' }}
                                       {{ isset($custom_field) && $mode === 'show' ? 'disabled' : '' }}>
                                <label class="form-check-label" for="required">
                                    This field is required
                                </label>
                                @error('required')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div id="optionsContainer" class="mb-3" style="display: none;">
                            <label class="form-label">Options for Select Field</label>
                            <div id="optionsList">
                                <div class="input-group mb-2">
                                    <input type="text" class="form-control" name="options[]" placeholder="Option 1">
                                    <button type="button" class="btn btn-outline-danger remove-option" style="display: none;">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm" id="addOption">
                                <i class="fas fa-plus"></i> Add Option
                            </button>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> 
                                {{ isset($custom_field) ? 'Update' : 'Create' }} Custom Field
                            </button>
                            <a href="{{ route('custom-fields.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/custom-fields.js') }}"></script>
@if(isset($custom_field) && $custom_field->type === 'select' && $custom_field->options)
<script>
    var customFieldOptions = @json($custom_field->options);
</script>
@endif
@endsection 