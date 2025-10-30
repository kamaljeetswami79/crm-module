@extends('layouts.app')

@section('title', 'Edit Contact - CRUD System')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="fas fa-user-edit"></i> Edit Contact</h1>
    <a href="{{ route('contacts.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Contacts
    </a>
</div>

@if($contact->merged_into_id)
<div class="alert alert-warning mb-4">
    <i class="fas fa-exclamation-triangle"></i>
    <strong>This contact has been merged and cannot be edited.</strong>
    <br>
    <a href="{{ route('contacts.merged.view', $contact) }}" class="btn btn-secondary btn-sm mt-2">
        <i class="fas fa-user-clock"></i> View Merged Data
    </a>
</div>
@endif

<div class="card">
    <div class="card-body">
        <form id="contactEditForm" method="POST" action="{{ route('contacts.update', $contact) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="row">
                <div class="col-md-6">
                    <h5 class="mb-3">Basic Information</h5>
                    <div class="mb-3">
                        <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $contact->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $contact->email) }}" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone', $contact->phone) }}" required>
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Gender <span class="text-danger">*</span></label>
                        <div class="form-check">
                            <input class="form-check-input @error('gender') is-invalid @enderror" type="radio" name="gender" id="gender_male" value="male" {{ old('gender', $contact->gender) == 'male' ? 'checked' : '' }} required>
                            <label class="form-check-label" for="gender_male">Male</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input @error('gender') is-invalid @enderror" type="radio" name="gender" id="gender_female" value="female" {{ old('gender', $contact->gender) == 'female' ? 'checked' : '' }}>
                            <label class="form-check-label" for="gender_female">Female</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input @error('gender') is-invalid @enderror" type="radio" name="gender" id="gender_other" value="other" {{ old('gender', $contact->gender) == 'other' ? 'checked' : '' }}>
                            <label class="form-check-label" for="gender_other">Other</label>
                        </div>
                        @error('gender')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <h5 class="mb-3">Files</h5>
                    <div class="mb-3">
                        <label for="profile_image" class="form-label">Profile Image</label>
                        <input type="file" class="form-control @error('profile_image') is-invalid @enderror" id="profile_image" name="profile_image" accept="image/*">
                        <div class="form-text">Accepted formats: JPEG, PNG, JPG, GIF (Max: 2MB)</div>
                        @error('profile_image')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div id="profile_preview" class="mt-2">
                            @if($contact->profile_image)
                                <a href="{{ asset('storage/profile_images/' . $contact->profile_image) }}" 
                                   data-fancybox="edit-profile" 
                                   data-caption="{{ $contact->name }}'s Current Profile Image">
                                    <img src="{{ asset('storage/profile_images/' . $contact->profile_image) }}" class="img-thumbnail" style="max-width: 150px; max-height: 150px;">
                                </a>
                            @endif
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="additional_file" class="form-label">Additional File</label>
                        <input type="file" class="form-control @error('additional_file') is-invalid @enderror" id="additional_file" name="additional_file" accept=".pdf,.doc,.docx,.txt">
                        <div class="form-text">Accepted formats: PDF, DOC, DOCX, TXT (Max: 5MB)</div>
                        @error('additional_file')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div id="file_preview" class="mt-2">
                            @if($contact->additional_file)
                                <a href="{{ asset('storage/additional_files/' . $contact->additional_file) }}" target="_blank">Download File</a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @if($customFields->count() > 0)
            <hr class="my-4">
            <h5 class="mb-3">Custom Fields</h5>
            <div class="row">
                @foreach($customFields as $field)
                <div class="col-md-6 mb-3">
                    <label for="custom_field_{{ $field->name }}" class="form-label">
                        {{ $field->label }}
                        @if($field->required)
                            <span class="text-danger">*</span>
                        @endif
                    </label>
                    @php
                        $customValue = $contact->customFieldValues->where('contact_custom_field_id', $field->id)->first();
                        $customValue = old('custom_field_' . $field->name, $customValue ? $customValue->value : '');
                    @endphp
                    @switch($field->type)
                        @case('text')
                            <input type="text" class="form-control" id="custom_field_{{ $field->name }}" name="custom_field_{{ $field->name }}" value="{{ $customValue }}" @if($field->required) required @endif>
                            @break
                        @case('email')
                            <input type="email" class="form-control" id="custom_field_{{ $field->name }}" name="custom_field_{{ $field->name }}" value="{{ $customValue }}" @if($field->required) required @endif>
                            @break
                        @case('number')
                            <input type="number" class="form-control" id="custom_field_{{ $field->name }}" name="custom_field_{{ $field->name }}" value="{{ $customValue }}" @if($field->required) required @endif>
                            @break
                        @case('date')
                            <input type="date" class="form-control" id="custom_field_{{ $field->name }}" name="custom_field_{{ $field->name }}" value="{{ $customValue }}" @if($field->required) required @endif>
                            @break
                        @case('select')
                            <select class="form-select" id="custom_field_{{ $field->name }}" name="custom_field_{{ $field->name }}" @if($field->required) required @endif>
                                <option value="">Select {{ $field->label }}</option>
                                @if($field->options)
                                    @foreach($field->options as $option)
                                        <option value="{{ $option }}" {{ $customValue == $option ? 'selected' : '' }}>{{ $option }}</option>
                                    @endforeach
                                @endif
                            </select>
                            @break
                        @case('textarea')
                            <textarea class="form-control" id="custom_field_{{ $field->name }}" name="custom_field_{{ $field->name }}" rows="3" @if($field->required) required @endif>{{ $customValue }}</textarea>
                            @break
                    @endswitch
                </div>
                @endforeach
            </div>
            @endif
            <div class="d-flex justify-content-end gap-2 mt-4">
                <button type="button" class="btn btn-secondary" onclick="window.location.href='{{ route('contacts.index') }}'">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Contact</button>
            </div>
        </form>
    </div>
</div>
@endsection
@section('scripts')
<script>
$(document).ready(function() {
    $('#profile_image').on('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#profile_preview').html(`
                    <a href="${e.target.result}" data-fancybox="new-profile" data-caption="New Profile Image Preview">
                        <img src="${e.target.result}" class="img-thumbnail" style="max-width: 150px; max-height: 150px;">
                    </a>
                `);
            };
            reader.readAsDataURL(file);
        } else {
            $('#profile_preview').html('');
        }
    });
    $('#additional_file').on('change', function() {
        const file = this.files[0];
        if (file) {
            $('#file_preview').html(`
                <div class="alert alert-info">
                    <i class="fas fa-file"></i> ${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)
                </div>
            `);
        } else {
            $('#file_preview').html('');
        }
    });
    $('#contactEditForm').on('submit', function(e) {
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
                    window.location.href = '{{ route("contacts.index") }}';
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
                    showError('An error occurred while updating the contact.');
                }
            }
        });
    });
});
</script>
@endsection 