@extends('layouts.app')

@section('title', 'Custom Fields - CRUD System')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="fas fa-box"></i> Custom Fields</h1>
    <a href="{{ route('custom-fields.create') }}" class="btn btn-secondary">
        <i class="fas fa-plus"></i> Add New Custom Field
    </a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Label</th>
                        <th>Type</th>
                        <th>Required</th>
                        <th>Status</th>
                        <th>Sort Order</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customFields as $field)
                    <tr>
                        <td>{{ $field->id }}</td>
                        <td><code>{{ $field->name }}</code></td>
                        <td>{{ $field->label }}</td>
                        <td>
                            <span class="badge bg-info">{{ ucfirst($field->type) }}</span>
                        </td>
                        <td>
                            @if($field->required)
                                <span class="badge bg-success">Yes</span>
                            @else
                                <span class="badge bg-secondary">No</span>
                            @endif
                        </td>
                        <td>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" 
                                       onchange="toggleStatus({{ $field->id }})"
                                       {{ $field->is_active ? 'checked' : '' }}>
                            </div>
                        </td>
                        <td>{{ $field->sort_order }}</td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="{{ route('custom-fields.show', $field) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('custom-fields.edit', $field) }}" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-danger" 
                                        onclick="confirmDelete('{{ route('custom-fields.destroy', $field) }}', 'Are you sure you want to delete this custom field?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center">No custom fields found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-4">
    <a href="{{ route('contacts.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back
    </a>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/custom-fields.js') }}"></script>
@endsection 