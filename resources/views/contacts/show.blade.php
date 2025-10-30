@extends('layouts.app')

@section('title', 'Contact Details - CRUD System')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="fas fa-user"></i> Contact Details</h1>
    <a href="{{ route('contacts.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Contacts
    </a>
</div>

@if($masterContact)
<div class="alert alert-warning mb-4">
    <i class="fas fa-exclamation-triangle"></i>
    <strong>This contact has been merged into:</strong> 
    <a href="{{ route('contacts.show', $masterContact) }}" class="alert-link">{{ $masterContact->name }}</a>
    <br><small>This contact is no longer active but preserved for audit purposes.</small>
</div>
@endif

<div class="card">
    <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-3 text-center">
                @if($contact->profile_image)
                    <a href="{{ asset('storage/profile_images/' . $contact->profile_image) }}" 
                       data-fancybox="profile" 
                       data-caption="{{ $contact->name }}'s Profile Image">
                        <img src="{{ asset('storage/profile_images/' . $contact->profile_image) }}" class="img-thumbnail profile-image mb-2" alt="Profile">
                    </a>
                @else
                    <div class="profile-image bg-secondary d-flex align-items-center justify-content-center mx-auto mb-2">
                        <i class="fas fa-user text-white"></i>
                    </div>
                @endif
                <h4>{{ $contact->name }}</h4>
                <span class="badge bg-{{ $contact->gender == 'male' ? 'primary' : ($contact->gender == 'female' ? 'danger' : 'secondary') }}">
                    {{ ucfirst($contact->gender) }}
                </span>
            </div>
            <div class="col-md-9">
                <table class="table table-bordered">
                    <tr>
                        <th>Email</th>
                        <td>{{ $contact->email }}</td>
                    </tr>
                    <tr>
                        <th>Phone</th>
                        <td>{{ $contact->phone }}</td>
                    </tr>
                    <tr>
                        <th>Profile Image</th>
                        <td>
                            @if($contact->profile_image)
                                <a href="{{ asset('storage/profile_images/' . $contact->profile_image) }}" 
                                   data-fancybox="profile-detail" 
                                   data-caption="{{ $contact->name }}'s Profile Image"
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i> View Image
                                </a>
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Additional File</th>
                        <td>
                            @if($contact->additional_file)
                                <a href="{{ asset('storage/additional_files/' . $contact->additional_file) }}" target="_blank">Download File</a>
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </td>
                    </tr>
                    @foreach($contact->customFieldValues as $value)
                        @if($value->customField)
                        <tr>
                            <th>{{ $value->customField->label ?? $value->name }}</th>
                            <td>{{ $value->value }}</td>
                        </tr>
                        @endif
                    @endforeach
                    <tr>
                        <th>Created At</th>
                        <td>{{ $contact->created_at->format('M d, Y H:i:s') }}</td>
                    </tr>
                    <tr>
                        <th>Updated At</th>
                        <td>{{ $contact->updated_at->format('M d, Y H:i:s') }}</td>
                    </tr>
                </table>
            </div>
        </div>
        
        @php
            $mergedContactAudits = [];
            foreach ($mergedContacts as $mc) {
                $audit = null;
                if (isset($mc->id) && isset($contact->id)) {
                    $audit = \App\Models\MergedContact::where('master_contact_id', $contact->id)
                        ->where('merged_contact_id', $mc->id)
                        ->first();
                }
                $mergedContactAudits[$mc->id] = $audit;
            }
        @endphp

        @if($mergedContacts->count() > 0)
        <div class="mt-4">
            <h5><i class="fas fa-object-group text-success"></i> Contacts Merged Into This Record</h5>
            <div class="table-responsive">
                <table class="table table-sm table-striped align-middle">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Gender</th>
                            <th>Merged Date</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($mergedContacts as $mergedContact)
                        <tr>
                            <td>{{ $mergedContact->name }}</td>
                            <td>{{ $mergedContact->email }}</td>
                            <td>{{ $mergedContact->phone }}</td>
                            <td>
                                <span class="badge bg-{{ $mergedContact->gender == 'male' ? 'primary' : ($mergedContact->gender == 'female' ? 'danger' : 'secondary') }}">
                                    {{ ucfirst($mergedContact->gender) }}
                                </span>
                            </td>
                            <td>{{ $mergedContact->updated_at->format('M d, Y H:i') }}</td>
                            <td>
                                <button class="btn btn-sm btn-outline-info" type="button" data-bs-toggle="collapse" data-bs-target="#mergeDetails{{ $mergedContact->id }}" aria-expanded="false" aria-controls="mergeDetails{{ $mergedContact->id }}">
                                    <i class="fas fa-info-circle"></i> Details
                                </button>
                            </td>
                        </tr>
                        <tr class="collapse" id="mergeDetails{{ $mergedContact->id }}">
                            <td colspan="6">
                                @php $audit = $mergedContactAudits[$mergedContact->id]; @endphp
                                @if($audit && $audit->merge_summary)
                                    @php $summary = $audit->merge_summary; @endphp
                                    @if(!empty($summary['custom_fields_added']))
                                        <div class="mb-2">
                                            <strong class="text-success">
                                                <i class="fas fa-plus-circle"></i> Values Added to Master:
                                            </strong>
                                            <ul>
                                                @foreach($summary['custom_fields_added'] as $added)
                                                    <li>{{ $added['field_name'] ?? 'Field' }}: <span class="text-muted">{{ $added['value'] }}</span></li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                    @if(!empty($summary['custom_fields_conflicted']))
                                        <div class="mb-2">
                                            <strong class="text-warning">
                                                <i class="fas fa-exchange-alt"></i> Conflicting Values (Master Kept):
                                            </strong>
                                            <ul>
                                                @foreach($summary['custom_fields_conflicted'] as $conflict)
                                                    <li>
                                                        <i class="fas fa-user-shield text-primary"></i> Master: <span class="text-muted">{{ $conflict['master_value'] }}</span>
                                                        <i class="fas fa-user-friends text-danger ms-2"></i> Secondary: <span class="text-muted">{{ $conflict['merged_value'] }}</span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                    @if(!empty($summary['emails_merged']) || !empty($summary['phones_merged']))
                                        <div class="mb-2">
                                            <strong class="text-info">Other Merged Fields:</strong>
                                            <ul>
                                                @if(!empty($summary['emails_merged']))
                                                    <li>Email was filled from secondary contact.</li>
                                                @endif
                                                @if(!empty($summary['phones_merged']))
                                                    <li>Phone was filled from secondary contact.</li>
                                                @endif
                                            </ul>
                                        </div>
                                    @endif
                                    @if(!empty($summary['files_merged']) && in_array('profile_image', $summary['files_merged']) && $audit->merged_combined_profile_image)
                                        <div class="mb-2">
                                            <strong class="text-info"><i class="fas fa-image"></i> Merged Profile Image:</strong><br>
                                            <img src="{{ asset('storage/profile_images/' . $audit->merged_combined_profile_image) }}" alt="Merged Profile Image" class="img-thumbnail" style="max-width: 100px;">
                                        </div>
                                    @endif
                                    @if(!empty($summary['files_merged']) && in_array('additional_file', $summary['files_merged']) && $audit->merged_combined_additional_file)
                                        <div class="mb-2">
                                            <strong class="text-info"><i class="fas fa-file"></i> Merged Additional File:</strong><br>
                                            <a href="{{ asset('storage/additional_files/' . $audit->merged_combined_additional_file) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-download"></i> Download File
                                            </a>
                                        </div>
                                    @endif
                                @else
                                    <div class="text-muted">No detailed merge data available.</div>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
        
        <div class="d-flex justify-content-end gap-2 mt-4">
            @if(!$masterContact)
            <a href="{{ route('contacts.edit', $contact) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Edit
            </a>
            <button type="button" class="btn btn-danger" onclick="confirmDelete('{{ route('contacts.destroy', $contact) }}', 'Are you sure you want to delete this contact?')">
                <i class="fas fa-trash"></i> Delete
            </button>
            @else
            <a href="{{ route('contacts.merged.view', $contact) }}" class="btn btn-secondary">
                <i class="fas fa-user-clock"></i> View Merged Data
            </a>
            <span class="text-muted">This contact cannot be edited as it has been merged.</span>
            @endif
        </div>
    </div>
</div>
@endsection 