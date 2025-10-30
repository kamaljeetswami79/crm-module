@extends('layouts.app')

@section('title', 'View Merged Contact - CRUD System')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="fas fa-user-clock"></i> View Merged Contact</h1>
    <div>
        <a href="{{ route('contacts.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Contacts
        </a>
        @if($masterContact)
            <a href="{{ route('contacts.show', $masterContact) }}" class="btn btn-primary">
                <i class="fas fa-eye"></i> View Master Contact
            </a>
        @endif
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-user"></i> Original Contact Data</h5>
                <span class="badge bg-warning">Read Only</span>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 text-center mb-3">
                        @if($contact->profile_image)
                            <img src="{{ asset('storage/profile_images/' . $contact->profile_image) }}" 
                                 class="img-thumbnail" style="max-width: 150px; max-height: 150px;">
                        @else
                            <img src="{{ asset('images/default-profile.png') }}" 
                                 class="img-thumbnail" style="max-width: 150px; max-height: 150px;">
                        @endif
                    </div>
                    <div class="col-md-8">
                        <h6><strong>{{ $contact->name }}</strong></h6>
                        <p><i class="fas fa-envelope"></i> {{ $contact->email }}</p>
                        <p><i class="fas fa-phone"></i> {{ $contact->phone }}</p>
                        <p><i class="fas fa-user"></i> {{ ucfirst($contact->gender) }}</p>
                        <p><i class="fas fa-calendar"></i> Created: {{ $contact->created_at->format('M d, Y') }}</p>
                    </div>
                </div>

                @if($contact->customFieldValues->count() > 0)
                    <hr>
                    <h6><i class="fas fa-cogs"></i> Custom Fields:</h6>
                    @foreach($contact->customFieldValues as $fieldValue)
                        <div class="mb-2">
                            <strong>{{ $fieldValue->customField->label }}:</strong>
                            <span class="text-muted">{{ $fieldValue->value }}</span>
                        </div>
                    @endforeach
                @endif

                @if($contact->additional_file)
                    <hr>
                    <h6><i class="fas fa-file"></i> Additional File:</h6>
                    <a href="{{ asset('storage/additional_files/' . $contact->additional_file) }}" 
                       target="_blank" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-download"></i> Download File
                    </a>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-merge"></i> Merged/Combined Data</h5>
                <span class="badge bg-success">Combined Information</span>
            </div>
            <div class="card-body">
                @if($mergedData)
                    <div class="row">
                        <div class="col-md-4 text-center mb-3">
                            @if($mergedData->merged_combined_profile_image)
                                <img src="{{ asset('storage/profile_images/' . $mergedData->merged_combined_profile_image) }}" 
                                     class="img-thumbnail" style="max-width: 150px; max-height: 150px;">
                            @else
                                <img src="{{ asset('images/default-profile.png') }}" 
                                     class="img-thumbnail" style="max-width: 150px; max-height: 150px;">
                            @endif
                        </div>
                        <div class="col-md-8">
                            <h6><strong>{{ $mergedData->merged_combined_name }}</strong></h6>
                            <p><i class="fas fa-envelope"></i> {{ $mergedData->merged_combined_email }}</p>
                            <p><i class="fas fa-phone"></i> {{ $mergedData->merged_combined_phone }}</p>
                            <p><i class="fas fa-user"></i> {{ ucfirst($mergedData->merged_combined_gender) }}</p>
                            <p><i class="fas fa-calendar"></i> Merged: {{ $mergedData->merged_at->format('M d, Y H:i') }}</p>
                        </div>
                    </div>

                    @if($mergedData->mergedCustomFieldValues->count() > 0)
                        <hr>
                        <h6><i class="fas fa-cogs"></i> Combined Custom Fields:</h6>
                        @foreach($mergedData->mergedCustomFieldValues as $mergedFieldValue)
                            <div class="mb-2">
                                <strong>{{ $mergedFieldValue->customField->label }}:</strong>
                                <span class="text-muted">{{ $mergedFieldValue->combined_value }}</span>
                                @if($mergedFieldValue->merge_details)
                                    <small class="text-info d-block">
                                        <i class="fas fa-info-circle"></i> 
                                        {{ $mergedFieldValue->merge_details['resolution'] ?? 'Merged' }}
                                    </small>
                                @endif
                            </div>
                        @endforeach
                    @endif

                    @if(isset($mergedData->merge_summary['custom_fields_added']) && count($mergedData->merge_summary['custom_fields_added']) > 0)
                        <hr>
                        <h6 class="text-success"><i class="fas fa-plus-circle"></i> Values Added to Master:</h6>
                        <ul class="mb-3">
                            @foreach($mergedData->merge_summary['custom_fields_added'] as $added)
                                <li>
                                    <strong>{{ $added['field_name'] ?? 'Field' }}:</strong>
                                    <span class="text-muted">{{ $added['value'] }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif

                    @if(isset($mergedData->merge_summary['custom_fields_conflicted']) && count($mergedData->merge_summary['custom_fields_conflicted']) > 0)
                        <hr>
                        <h6 class="text-warning"><i class="fas fa-exchange-alt"></i> Conflicting Values (Both Values Shown):</h6>
                        <ul class="mb-3">
                            @foreach($mergedData->merge_summary['custom_fields_conflicted'] as $conflict)
                                <li>
                                    <strong>{{ $conflict['field_name'] ?? 'Field' }}:</strong>
                                    <span class="text-primary">Master:</span> <span class="text-muted">{{ $conflict['master_value'] }}</span>
                                    <span class="text-danger ms-2">Secondary:</span> <span class="text-muted">{{ $conflict['merged_value'] }}</span>
                                    @if(isset($conflict['resolution']))
                                        <small class="text-info ms-2">({{ $conflict['resolution'] }})</small>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif

                    @if($mergedData->merged_combined_additional_file)
                        <hr>
                        <h6><i class="fas fa-file"></i> Combined Files:</h6>
                        <a href="{{ asset('storage/additional_files/' . $mergedData->merged_combined_additional_file) }}" 
                           target="_blank" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-download"></i> Download Combined File
                        </a>
                    @endif

                    @if($mergedData->merge_summary)
                        <hr>
                        <h6><i class="fas fa-chart-bar"></i> Merge Summary:</h6>
                        <div class="small">
                            @if($mergedData->merge_summary['emails_merged'])
                                <span class="badge bg-success me-1">Emails Combined</span>
                            @endif
                            @if($mergedData->merge_summary['phones_merged'])
                                <span class="badge bg-success me-1">Phones Combined</span>
                            @endif
                            @if(count($mergedData->merge_summary['custom_fields_added'] ?? []) > 0)
                                <span class="badge bg-info me-1">{{ count($mergedData->merge_summary['custom_fields_added']) }} Fields Added</span>
                            @endif
                            @if(count($mergedData->merge_summary['custom_fields_merged'] ?? []) > 0)
                                <span class="badge bg-warning me-1">{{ count($mergedData->merge_summary['custom_fields_merged']) }} Fields Merged</span>
                            @endif
                            @if(count($mergedData->merge_summary['files_merged'] ?? []) > 0)
                                <span class="badge bg-primary me-1">Files Transferred</span>
                            @endif
                        </div>
                    @endif
                @else
                    <div class="text-center text-muted">
                        <i class="fas fa-info-circle fa-3x mb-3"></i>
                        <p>No merged data available for this contact.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@if($mergedContacts->count() > 0)
<div class="card mt-4">
    <div class="card-header">
        <h5><i class="fas fa-history"></i> Merge History</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Contact</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Merged Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($mergedContacts as $mergedContact)
                    <tr>
                        <td>{{ $mergedContact->name }}</td>
                        <td>{{ $mergedContact->email }}</td>
                        <td>{{ $mergedContact->phone }}</td>
                        <td>{{ $mergedContact->updated_at->format('M d, Y H:i') }}</td>
                        <td>
                            <a href="{{ route('contacts.merged.view', $mergedContact) }}" 
                               class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye"></i> View
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

@if($masterContact)
<div class="card mt-4">
    <div class="card-header">
        <h5><i class="fas fa-crown"></i> Master Contact Information</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-2 text-center">
                @if($masterContact->profile_image)
                    <img src="{{ asset('storage/profile_images/' . $masterContact->profile_image) }}" 
                         class="img-thumbnail" style="max-width: 80px; max-height: 80px;">
                @else
                    <img src="{{ asset('images/default-profile.png') }}" 
                         class="img-thumbnail" style="max-width: 80px; max-height: 80px;">
                @endif
            </div>
            <div class="col-md-10">
                <h6><strong>{{ $masterContact->name }}</strong></h6>
                <p class="mb-1"><i class="fas fa-envelope"></i> {{ $masterContact->email }}</p>
                <p class="mb-1"><i class="fas fa-phone"></i> {{ $masterContact->phone }}</p>
                <p class="mb-0"><i class="fas fa-user"></i> {{ ucfirst($masterContact->gender) }}</p>
            </div>
        </div>
    </div>
</div>
@endif

@endsection 