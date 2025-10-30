@extends('layouts.app')

@section('title', 'Merged Contacts - CRUD System')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="fas fa-user"></i> Merged Contacts</h1>
    <div>
        <a href="{{ route('contacts.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5><i class="fas fa-info-circle"></i> Merged Contacts Audit</h5>
    </div>
    <div class="card-body">
        @if($mergedContacts->count() > 0)
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Profile</th>
                            <th>Contact Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Gender</th>
                            <th>Merged Into</th>
                            <th>Merge Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($mergedContacts as $contact)
                        <tr>
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
                                    <div class="profile-image bg-secondary d-flex align-items-center justify-content-center">
                                        <i class="fas fa-user text-white"></i>
                                    </div>
                                @endif
                            </td>
                            <td>
                                <strong>{{ $contact->name }}</strong>
                                <br>
                                <small class="text-muted">Merged Contact</small>
                            </td>
                            <td>{{ $contact->email }}</td>
                            <td>{{ $contact->phone }}</td>
                            <td>
                                <span class="badge bg-{{ $contact->gender == 'male' ? 'primary' : ($contact->gender == 'female' ? 'danger' : 'secondary') }}">
                                    {{ ucfirst($contact->gender) }}
                                </span>
                            </td>
                            <td>
                                @if($contact->mergedInto)
                                    <div>
                                        <strong>{{ $contact->mergedInto->name }}</strong>
                                        <br>
                                        <small class="text-muted">Master Contact</small>
                                    </div>
                                @else
                                    <span class="text-danger">Master contact not found</span>
                                @endif
                            </td>
                            <td>{{ $contact->updated_at->format('M d, Y H:i') }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('contacts.merged.view', $contact) }}" class="btn btn-sm btn-info" title="View Merged Data">
                                        <i class="fas fa-eye"></i> View Merged
                                    </a>
                                    @if($contact->mergedInto)
                                        <a href="{{ route('contacts.show', $contact->mergedInto) }}" class="btn btn-sm btn-primary" title="View Master Contact">
                                            <i class="fas fa-crown"></i> Master
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($mergedContacts->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $mergedContacts->links() }}
            </div>
            @endif
        @else
            <div class="text-center py-5">
                <i class="fas fa-user fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No Merged Contacts</h5>
                <p class="text-muted">There are no merged contacts to display.</p>
                <a href="{{ route('contacts.index') }}" class="btn btn-info">
                    <i class="fas fa-users"></i> View All Contacts
                </a>
            </div>
        @endif
    </div>
</div>

@if($mergedContacts->count() > 0)
<div class="row mt-4">
    <div class="col-md-4">
        <div class="card bg-primary text-white">
            <div class="card-body text-center">
                <i class="fas fa-user-clock fa-2x mb-2"></i>
                <h4>{{ $mergedContacts->count() }}</h4>
                <p class="mb-0">Total Merged Contacts</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <i class="fas fa-crown fa-2x mb-2"></i>
                <h4>{{ $mergedContacts->pluck('merged_into_id')->unique()->count() }}</h4>
                <p class="mb-0">Master Contacts</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-info text-white">
            <div class="card-body text-center">
                <i class="fas fa-calendar fa-2x mb-2"></i>
                <h4>{{ $mergedContacts->max('updated_at')->format('M d, Y') }}</h4>
                <p class="mb-0">Latest Merge</p>
            </div>
        </div>
    </div>
</div>
@endif

@endsection

@section('styles')
<style>
.profile-image {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
}
</style>
@endsection 