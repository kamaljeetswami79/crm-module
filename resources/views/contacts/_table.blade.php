<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
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
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center">No contacts found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
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