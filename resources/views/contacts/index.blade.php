@extends('layouts.app')

@section('title', 'Contacts - CRUD System')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="fas fa-address-book"></i> Contacts</h1>
    <div>
        <a href="{{ route('contacts.merged') }}" class="btn btn-danger me-2">
            <i class="fas fa-user-clock"></i> View Merged Contacts
        </a>
        <button id="mergeContactsBtn" class="btn btn-warning me-2" disabled>
            <i class="fas fa-object-group"></i> Merge Selected
        </button>
        <a href="{{ route('contacts.create') }}" class="btn btn-secondary">
            <i class="fas fa-plus"></i> Add New Contact
        </a>
    </div>
</div>

<div class="search-filters">
    <form id="searchForm" method="GET" action="{{ route('contacts.index') }}">
        <div class="row">
            <div class="col-md-3">
                <label for="search" class="form-label">Search</label>
                <input type="text" class="form-control" id="search" name="search" 
                       value="{{ request('search') }}" placeholder="Name, Email, or Phone">
            </div>
            <div class="col-md-2">
                <label for="gender" class="form-label">Gender</label>
                <select class="form-select" id="gender" name="gender">
                    <option value="">All</option>
                    <option value="male" {{ request('gender') == 'male' ? 'selected' : '' }}>Male</option>
                    <option value="female" {{ request('gender') == 'female' ? 'selected' : '' }}>Female</option>
                    <option value="other" {{ request('gender') == 'other' ? 'selected' : '' }}>Other</option>
                </select>
            </div>
            @if($customFields->count() > 0)
            <div class="col-md-3">
                <label for="custom_field" class="form-label">Custom Field</label>
                <select class="form-select" id="custom_field" name="custom_field">
                    <option value="">Select Field</option>
                    @foreach($customFields as $field)
                        <option value="{{ $field->name }}" {{ request('custom_field') == $field->name ? 'selected' : '' }}>
                            {{ $field->label }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="custom_value" class="form-label">Custom Value</label>
                <input type="text" class="form-control" id="custom_value" name="custom_value" 
                       value="{{ request('custom_value') }}" placeholder="Enter value">
            </div>
            @endif
            <div class="col-md-1">
                <label class="form-label">&nbsp;</label>
                <button type="submit" class="btn btn-success w-100">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </div>
    </form>
</div>

<div class="table-container">
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>
                                <input type="checkbox" id="selectAll" class="form-check-input">
                            </th>
                            <th>ID</th>
                            <th>Profile</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Gender</th>
                            <th>Custom Fields</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="contactsTableBody">
                        @forelse($contacts as $contact)
                        <tr>
                            <td>
                                <input type="checkbox" class="form-check-input contact-checkbox" value="{{ $contact->id }}">
                            </td>
                            <td>{{ $contact->id }}</td>
                            <td>
                                @if($contact->profile_image)
                                    <a href="{{ asset('storage/profile_images/' . $contact->profile_image) }}" 
                                       data-fancybox="gallery" 
                                       data-caption="{{ $contact->name }}'s Profile Image">
                                        <img src="{{ asset('storage/profile_images/' . $contact->profile_image) }}" 
                                             alt="Profile" class="profile-image">
                                    </a>
                                @else
                                <img src="{{ asset('images/default-profile.png') }}" alt="No Image" class="profile-image">

                                @endif
                            </td>
                            <td>{{ $contact->name }}</td>
                            <td>{{ $contact->email }}</td>
                            <td>{{ $contact->phone }}</td>
                            <td>
                                <span class="badge bg-{{ $contact->gender == 'male' ? 'primary' : ($contact->gender == 'female' ? 'danger' : 'secondary') }}">
                                    {{ ucfirst($contact->gender) }}
                                </span>
                            </td>
                            <td>
                                @foreach($contact->customFieldValues as $value)
                                    @if($value->customField)
                                        <small class="d-block">
                                            <strong>{{ $value->customField->label }}:</strong> {{ $value->value }}
                                        </small>
                                    @endif
                                @endforeach
                            </td>
                            <td>{{ $contact->created_at->format('M d, Y') }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    @if($contact->merged_into_id)
                                        <a href="{{ route('contacts.merged.view', $contact) }}" class="btn btn-sm btn-secondary" title="View Merged Contact">
                                            <i class="fas fa-user-clock"></i>
                                        </a>
                                        <span class="badge bg-warning ms-1">Merged</span>
                                    @else
                                        <a href="{{ route('contacts.show', $contact) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('contacts.edit', $contact) }}" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-danger" 
                                                onclick="confirmDelete('{{ route('contacts.destroy', $contact) }}', 'Are you sure you want to delete this contact?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center">No contacts found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($contacts->hasPages())
            <div class="pagination-container">
                @php
                    $paginationView = 'vendor.pagination.contacts-ajax';
                @endphp
                {{ $contacts->links($paginationView) }}
            </div>
            @endif
        </div>
    </div>
</div>

<div class="modal fade" id="mergeModal" tabindex="-1" aria-labelledby="mergeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mergeModalLabel">
                    <i class="fas fa-object-group"></i> Merge Contacts
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmMergeBtn">
                    <i class="fas fa-object-group"></i> Confirm Merge
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/contacts.js') }}"></script>
@endsection 