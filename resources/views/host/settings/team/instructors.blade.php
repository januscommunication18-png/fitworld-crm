@extends('layouts.settings')

@section('title', 'Instructors — Settings')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ url('/dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('settings.index') }}"><span class="icon-[tabler--settings] me-1 size-4"></span> Settings</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">Instructors</li>
    </ol>
@endsection

@section('settings-content')
<div class="space-y-6">
    {{-- Flash Messages --}}
    @if(session('success'))
    <div class="alert alert-soft alert-success">
        <span class="icon-[tabler--check] size-5"></span>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-soft alert-error">
        <span class="icon-[tabler--alert-circle] size-5"></span>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    {{-- Instructors List --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-lg font-semibold">Instructors</h2>
                    <p class="text-base-content/60 text-sm">Manage your teaching staff and their profiles</p>
                </div>
                <button class="btn btn-primary btn-sm" onclick="openAddModal()">
                    <span class="icon-[tabler--plus] size-4"></span> Add Instructor
                </button>
            </div>

            @if($instructors->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($instructors as $instructor)
                <div class="p-4 border border-base-content/10 rounded-lg {{ !$instructor->is_active ? 'opacity-60' : '' }}">
                    <div class="flex items-start gap-4">
                        <div class="avatar {{ $instructor->photo_url ? '' : 'placeholder' }}">
                            @if($instructor->photo_url)
                            <div class="w-14 rounded-full">
                                <img src="{{ $instructor->photo_url }}" alt="{{ $instructor->name }}" />
                            </div>
                            @else
                            <div class="bg-primary text-primary-content w-14 rounded-full">
                                <span class="text-lg">{{ strtoupper(substr($instructor->name, 0, 2)) }}</span>
                            </div>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-2">
                                <div class="font-medium truncate">{{ $instructor->name }}</div>
                                <div class="flex items-center gap-1 shrink-0">
                                    @if($instructor->hasAccount())
                                        <span class="badge badge-success badge-soft badge-xs" title="Has login account">
                                            <span class="icon-[tabler--user-check] size-3"></span>
                                        </span>
                                    @endif
                                    @if(!$instructor->is_visible)
                                        <span class="badge badge-warning badge-soft badge-xs" title="Hidden from booking page">
                                            <span class="icon-[tabler--eye-off] size-3"></span>
                                        </span>
                                    @endif
                                    <div class="relative">
                                        <details class="dropdown dropdown-bottom dropdown-end">
                                            <summary class="btn btn-ghost btn-xs btn-square list-none cursor-pointer">
                                                <span class="icon-[tabler--dots-vertical] size-4"></span>
                                            </summary>
                                            <ul class="dropdown-content menu bg-base-100 rounded-box w-48 p-2 shadow-lg border border-base-300" style="z-index: 9999; position: absolute; right: 0; top: 100%;">
                                                <li>
                                                    <a href="javascript:void(0)" onclick="openEditModal({{ $instructor->id }})">
                                                        <span class="icon-[tabler--edit] size-4"></span> Edit Profile
                                                    </a>
                                                </li>
                                                @if(!$instructor->hasAccount() && $instructor->email)
                                                    @if($instructor->hasPendingInvitation())
                                                    <li>
                                                        <span class="text-base-content/50">
                                                            <span class="icon-[tabler--clock] size-4"></span> Invite Pending
                                                        </span>
                                                    </li>
                                                    @else
                                                    <li>
                                                        <form action="{{ route('settings.team.instructors.invite', $instructor) }}" method="POST" class="m-0">
                                                            @csrf
                                                            <button type="submit" class="w-full text-left flex items-center gap-2">
                                                                <span class="icon-[tabler--send] size-4"></span> Send Login Invite
                                                            </button>
                                                        </form>
                                                    </li>
                                                    @endif
                                                @endif
                                                <li>
                                                    <form action="{{ route('settings.team.instructors.delete', $instructor) }}" method="POST" class="m-0" onsubmit="return confirm('Are you sure you want to delete {{ $instructor->name }}? This cannot be undone.')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="w-full text-left flex items-center gap-2 text-error">
                                                            <span class="icon-[tabler--trash] size-4"></span> Delete
                                                        </button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </details>
                                    </div>
                                </div>
                            </div>
                            @if($instructor->email)
                            <div class="text-sm text-base-content/60 truncate">{{ $instructor->email }}</div>
                            @endif
                            @if($instructor->specialties && count($instructor->specialties) > 0)
                            <div class="flex flex-wrap gap-1 mt-2">
                                @foreach(array_slice($instructor->specialties, 0, 3) as $specialty)
                                <span class="badge badge-primary badge-soft badge-xs">{{ $specialty }}</span>
                                @endforeach
                                @if(count($instructor->specialties) > 3)
                                <span class="badge badge-soft badge-xs">+{{ count($instructor->specialties) - 3 }}</span>
                                @endif
                            </div>
                            @endif
                            <div class="flex items-center gap-3 mt-2 text-xs text-base-content/50">
                                @if(!$instructor->is_active)
                                <span class="text-warning">Inactive</span>
                                @else
                                <span class="text-success">Active</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach

                {{-- Add Instructor Card --}}
                <div class="p-4 border border-dashed border-base-content/20 rounded-lg flex items-center justify-center min-h-[120px]">
                    <button class="btn btn-ghost btn-sm" onclick="openAddModal()">
                        <span class="icon-[tabler--plus] size-4"></span> Add Instructor
                    </button>
                </div>
            </div>
            @else
            <div class="text-center py-12">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-base-200 rounded-full mb-4">
                    <span class="icon-[tabler--yoga] size-8 text-base-content/40"></span>
                </div>
                <h3 class="text-lg font-medium mb-2">No instructors yet</h3>
                <p class="text-base-content/60 mb-4">Add your first instructor to get started</p>
                <button class="btn btn-primary btn-sm" onclick="openAddModal()">
                    <span class="icon-[tabler--plus] size-4"></span> Add Instructor
                </button>
            </div>
            @endif
        </div>
    </div>

    {{-- Role Explanation --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <h2 class="text-lg font-semibold mb-4">Instructor Profiles vs. Login Accounts</h2>
            <div class="space-y-4">
                <div class="p-4 border border-base-content/10 rounded-lg">
                    <div class="flex items-start gap-3">
                        <span class="icon-[tabler--user] size-5 text-primary mt-0.5"></span>
                        <div>
                            <div class="font-medium">Profile Only</div>
                            <div class="text-sm text-base-content/60">
                                Instructor appears on your booking page and can be assigned to classes.
                                They don't have login access - you manage everything for them.
                            </div>
                        </div>
                    </div>
                </div>
                <div class="p-4 border border-base-content/10 rounded-lg">
                    <div class="flex items-start gap-3">
                        <span class="icon-[tabler--user-check] size-5 text-success mt-0.5"></span>
                        <div>
                            <div class="font-medium">Profile + Login Account</div>
                            <div class="text-sm text-base-content/60">
                                Instructor has their own login to view their schedule and mark attendance.
                                Send an invite to give them access.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Add Instructor Modal --}}
<div id="add-instructor-modal" class="fixed inset-0 z-[60] flex items-center justify-center bg-black/50 opacity-0 pointer-events-none transition-opacity duration-200 overflow-y-auto py-8">
    <div class="card bg-base-100 w-full max-w-2xl mx-4 transform scale-95 transition-transform duration-200">
        <div class="card-body">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold">Add Instructor</h3>
                <button type="button" class="btn btn-ghost btn-sm btn-square" onclick="closeAddModal()">
                    <span class="icon-[tabler--x] size-5"></span>
                </button>
            </div>
            <form action="{{ route('settings.team.instructors.store') }}" method="POST">
            @csrf
            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="label-text" for="add-name">Name <span class="text-error">*</span></label>
                        <input type="text" id="add-name" name="name" class="input w-full" required placeholder="Jane Smith" />
                    </div>
                    <div>
                        <label class="label-text" for="add-email">Email</label>
                        <input type="email" id="add-email" name="email" class="input w-full" placeholder="jane@example.com" />
                        <p class="text-xs text-base-content/60 mt-1">Required to send login invite</p>
                    </div>
                </div>

                <div>
                    <label class="label-text" for="add-phone">Phone</label>
                    <input type="text" id="add-phone" name="phone" class="input w-full" placeholder="+1 (555) 123-4567" />
                </div>

                <div>
                    <label class="label-text" for="add-bio">Bio</label>
                    <textarea id="add-bio" name="bio" class="textarea w-full" rows="3" placeholder="A brief introduction about this instructor..."></textarea>
                </div>

                <div>
                    <label class="label-text mb-2 block">Specialties</label>
                    <div class="flex flex-wrap gap-2">
                        @foreach($specialties as $specialty)
                        <label class="cursor-pointer">
                            <input type="checkbox" name="specialties[]" value="{{ $specialty }}" class="peer hidden" />
                            <span class="badge badge-soft peer-checked:badge-primary peer-checked:badge-solid">{{ $specialty }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                <div>
                    <label class="label-text" for="add-certifications">Certifications</label>
                    <textarea id="add-certifications" name="certifications" class="textarea w-full" rows="2" placeholder="RYT-200, ACE Certified, etc."></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <label class="custom-option w-full">
                        <input type="checkbox" name="is_visible" value="1" checked class="custom-option-input peer" />
                        <span class="custom-option-label w-full flex items-center gap-3">
                            <span class="icon-[tabler--eye] size-5 text-base-content/60"></span>
                            <span>
                                <span class="block font-medium">Visible on Booking Page</span>
                                <span class="text-xs text-base-content/60">Show on public booking page</span>
                            </span>
                        </span>
                    </label>
                    <label class="custom-option w-full">
                        <input type="checkbox" name="is_active" value="1" checked class="custom-option-input peer" />
                        <span class="custom-option-label w-full flex items-center gap-3">
                            <span class="icon-[tabler--check] size-5 text-base-content/60"></span>
                            <span>
                                <span class="block font-medium">Active</span>
                                <span class="text-xs text-base-content/60">Can be assigned to classes</span>
                            </span>
                        </span>
                    </label>
                </div>
            </div>
            <div class="flex justify-start gap-2 mt-6">
                <button type="submit" class="btn btn-primary">
                    <span class="icon-[tabler--plus] size-4"></span> Add Instructor
                </button>
                <button type="button" class="btn btn-ghost" onclick="closeAddModal()">Cancel</button>
            </div>
        </form>
        </div>
    </div>
</div>

{{-- Edit Instructor Modal --}}
<div id="edit-instructor-modal" class="fixed inset-0 z-[60] flex items-center justify-center bg-black/50 opacity-0 pointer-events-none transition-opacity duration-200 overflow-y-auto py-8">
    <div class="card bg-base-100 w-full max-w-2xl mx-4 transform scale-95 transition-transform duration-200">
        <div class="card-body">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold">Edit Instructor</h3>
                <button type="button" class="btn btn-ghost btn-sm btn-square" onclick="closeEditModal()">
                    <span class="icon-[tabler--x] size-5"></span>
                </button>
            </div>
            <form id="edit-instructor-form" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="space-y-4">
                {{-- Photo Upload --}}
                <div class="flex items-center gap-4">
                    <div id="edit-photo-preview" class="avatar placeholder">
                        <div class="bg-primary text-primary-content w-16 rounded-full">
                            <span id="edit-photo-initials" class="text-xl">--</span>
                        </div>
                    </div>
                    <div>
                        <input type="file" id="edit-photo-input" accept="image/jpeg,image/png,image/webp" class="hidden" />
                        <button type="button" class="btn btn-sm btn-outline" onclick="document.getElementById('edit-photo-input').click()">
                            <span class="icon-[tabler--upload] size-4"></span> Upload Photo
                        </button>
                        <button type="button" id="edit-photo-remove" class="btn btn-sm btn-ghost text-error hidden">
                            <span class="icon-[tabler--trash] size-4"></span>
                        </button>
                        <p class="text-xs text-base-content/60 mt-1">JPG, PNG or WebP. Max 2MB.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="label-text" for="edit-name">Name <span class="text-error">*</span></label>
                        <input type="text" id="edit-name" name="name" class="input w-full" required />
                    </div>
                    <div>
                        <label class="label-text" for="edit-email">Email</label>
                        <input type="email" id="edit-email" name="email" class="input w-full" />
                    </div>
                </div>

                <div>
                    <label class="label-text" for="edit-phone">Phone</label>
                    <input type="text" id="edit-phone" name="phone" class="input w-full" />
                </div>

                <div>
                    <label class="label-text" for="edit-bio">Bio</label>
                    <textarea id="edit-bio" name="bio" class="textarea w-full" rows="3"></textarea>
                </div>

                <div>
                    <label class="label-text mb-2 block">Specialties</label>
                    <div id="edit-specialties" class="flex flex-wrap gap-2">
                        @foreach($specialties as $specialty)
                        <label class="cursor-pointer">
                            <input type="checkbox" name="specialties[]" value="{{ $specialty }}" class="peer hidden specialty-checkbox" data-specialty="{{ $specialty }}" />
                            <span class="badge badge-soft peer-checked:badge-primary peer-checked:badge-solid">{{ $specialty }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                <div>
                    <label class="label-text" for="edit-certifications">Certifications</label>
                    <textarea id="edit-certifications" name="certifications" class="textarea w-full" rows="2"></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <label class="custom-option w-full">
                        <input type="checkbox" id="edit-is-visible" name="is_visible" value="1" class="custom-option-input peer" />
                        <span class="custom-option-label w-full flex items-center gap-3">
                            <span class="icon-[tabler--eye] size-5 text-base-content/60"></span>
                            <span>
                                <span class="block font-medium">Visible on Booking Page</span>
                                <span class="text-xs text-base-content/60">Show on public booking page</span>
                            </span>
                        </span>
                    </label>
                    <label class="custom-option w-full">
                        <input type="checkbox" id="edit-is-active" name="is_active" value="1" class="custom-option-input peer" />
                        <span class="custom-option-label w-full flex items-center gap-3">
                            <span class="icon-[tabler--check] size-5 text-base-content/60"></span>
                            <span>
                                <span class="block font-medium">Active</span>
                                <span class="text-xs text-base-content/60">Can be assigned to classes</span>
                            </span>
                        </span>
                    </label>
                </div>
            </div>
            <div class="flex justify-start gap-2 mt-6">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <button type="button" class="btn btn-ghost" onclick="closeEditModal()">Cancel</button>
            </div>
        </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Store instructor data for editing
const instructorsData = @json($instructors->keyBy('id'));

// Add Modal
function openAddModal() {
    var modal = document.getElementById('add-instructor-modal');
    modal.classList.remove('opacity-0', 'pointer-events-none');
    modal.classList.add('opacity-100', 'pointer-events-auto');
    modal.querySelector('.card').classList.remove('scale-95');
    modal.querySelector('.card').classList.add('scale-100');
}

function closeAddModal() {
    var modal = document.getElementById('add-instructor-modal');
    modal.classList.add('opacity-0', 'pointer-events-none');
    modal.classList.remove('opacity-100', 'pointer-events-auto');
    modal.querySelector('.card').classList.add('scale-95');
    modal.querySelector('.card').classList.remove('scale-100');
}

// Edit Modal
function openEditModal(instructorId) {
    const instructor = instructorsData[instructorId];
    if (!instructor) return;

    // Set form action
    document.getElementById('edit-instructor-form').action = '/settings/team/instructors/' + instructorId;

    // Fill form fields
    document.getElementById('edit-name').value = instructor.name || '';
    document.getElementById('edit-email').value = instructor.email || '';
    document.getElementById('edit-phone').value = instructor.phone || '';
    document.getElementById('edit-bio').value = instructor.bio || '';
    document.getElementById('edit-certifications').value = instructor.certifications || '';
    document.getElementById('edit-is-visible').checked = instructor.is_visible;
    document.getElementById('edit-is-active').checked = instructor.is_active;

    // Update initials
    const initials = (instructor.name || '--').substring(0, 2).toUpperCase();
    document.getElementById('edit-photo-initials').textContent = initials;

    // Update photo preview
    const photoPreview = document.getElementById('edit-photo-preview');
    if (instructor.photo_url) {
        photoPreview.innerHTML = '<div class="w-16 rounded-full"><img src="' + instructor.photo_url + '" alt="' + instructor.name + '" /></div>';
        document.getElementById('edit-photo-remove').classList.remove('hidden');
    } else {
        photoPreview.innerHTML = '<div class="bg-primary text-primary-content w-16 rounded-full"><span id="edit-photo-initials" class="text-xl">' + initials + '</span></div>';
        document.getElementById('edit-photo-remove').classList.add('hidden');
    }

    // Update specialties
    document.querySelectorAll('.specialty-checkbox').forEach(checkbox => {
        const specialties = instructor.specialties || [];
        checkbox.checked = specialties.includes(checkbox.dataset.specialty);
    });

    // Show modal
    var modal = document.getElementById('edit-instructor-modal');
    modal.classList.remove('opacity-0', 'pointer-events-none');
    modal.classList.add('opacity-100', 'pointer-events-auto');
    modal.querySelector('.card').classList.remove('scale-95');
    modal.querySelector('.card').classList.add('scale-100');
}

function closeEditModal() {
    var modal = document.getElementById('edit-instructor-modal');
    modal.classList.add('opacity-0', 'pointer-events-none');
    modal.classList.remove('opacity-100', 'pointer-events-auto');
    modal.querySelector('.card').classList.add('scale-95');
    modal.querySelector('.card').classList.remove('scale-100');
}

// Close modals on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeAddModal();
        closeEditModal();
    }
});

// Close modals when clicking backdrop
document.getElementById('add-instructor-modal').addEventListener('click', function(e) {
    if (e.target === this) closeAddModal();
});
document.getElementById('edit-instructor-modal').addEventListener('click', function(e) {
    if (e.target === this) closeEditModal();
});

// Photo upload handling
document.getElementById('edit-photo-input').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (!file) return;

    // Get current instructor ID from form action
    const formAction = document.getElementById('edit-instructor-form').action;
    const instructorId = formAction.split('/').pop();

    const formData = new FormData();
    formData.append('photo', file);

    fetch('/settings/team/instructors/' + instructorId + '/photo', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const photoPreview = document.getElementById('edit-photo-preview');
            photoPreview.innerHTML = '<div class="w-16 rounded-full"><img src="' + data.path + '" /></div>';
            document.getElementById('edit-photo-remove').classList.remove('hidden');
        }
    })
    .catch(error => console.error('Upload failed:', error));
});

// Photo remove handling
document.getElementById('edit-photo-remove').addEventListener('click', function() {
    const formAction = document.getElementById('edit-instructor-form').action;
    const instructorId = formAction.split('/').pop();

    fetch('/settings/team/instructors/' + instructorId + '/photo', {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const name = document.getElementById('edit-name').value || '--';
            const initials = name.substring(0, 2).toUpperCase();
            const photoPreview = document.getElementById('edit-photo-preview');
            photoPreview.innerHTML = '<div class="bg-primary text-primary-content w-16 rounded-full"><span class="text-xl">' + initials + '</span></div>';
            document.getElementById('edit-photo-remove').classList.add('hidden');
        }
    })
    .catch(error => console.error('Remove failed:', error));
});
</script>
@endpush
