@extends('layouts.dashboard')

@section('title', $classSession->display_title)

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('class-sessions.index') }}"><span class="icon-[tabler--calendar-event] me-1 size-4"></span> Class Sessions</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">{{ $classSession->display_title }}</li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <a href="{{ route('class-sessions.index') }}" class="btn btn-ghost btn-sm btn-circle">
                    <span class="icon-[tabler--arrow-left] size-5"></span>
                </a>
                <div class="w-4 h-4 rounded-full" style="background-color: {{ $classSession->classPlan->color }};"></div>
                <h1 class="text-2xl font-bold">{{ $classSession->display_title }}</h1>
                <span class="badge {{ $classSession->getStatusBadgeClass() }} badge-soft capitalize">{{ $classSession->status }}</span>
            </div>
            <p class="text-base-content/60">{{ $classSession->formatted_date }} &bull; {{ $classSession->formatted_time_range }}</p>
        </div>
        <div class="flex items-center gap-2">
            @if($classSession->isDraft())
            <form action="{{ route('class-sessions.publish', $classSession) }}" method="POST" class="inline">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn btn-success">
                    <span class="icon-[tabler--send] size-5"></span>
                    Publish
                </button>
            </form>
            @elseif($classSession->isPublished())
            <form action="{{ route('class-sessions.unpublish', $classSession) }}" method="POST" class="inline">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn btn-soft btn-secondary">
                    <span class="icon-[tabler--eye-off] size-5"></span>
                    Unpublish
                </button>
            </form>
            @endif
            <a href="{{ route('class-sessions.edit', $classSession) }}" class="btn btn-primary">
                <span class="icon-[tabler--edit] size-5"></span>
                Edit
            </a>
            <details class="dropdown dropdown-bottom dropdown-end">
                <summary class="btn btn-ghost btn-square list-none cursor-pointer">
                    <span class="icon-[tabler--dots-vertical] size-5"></span>
                </summary>
                <ul class="dropdown-content menu bg-base-100 rounded-box w-44 p-2 shadow-lg border border-base-300" style="z-index: 9999; position: absolute; right: 0; top: 100%;">
                    <li>
                        <form action="{{ route('class-sessions.duplicate', $classSession) }}" method="POST" class="m-0">
                            @csrf
                            <button type="submit" class="w-full text-left flex items-center gap-2">
                                <span class="icon-[tabler--copy] size-4"></span> Duplicate
                            </button>
                        </form>
                    </li>
                    @if($classSession->hasBackupInstructor())
                    <li>
                        <form action="{{ route('class-sessions.promote-backup', $classSession) }}" method="POST" class="m-0">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="w-full text-left flex items-center gap-2">
                                <span class="icon-[tabler--arrows-exchange] size-4"></span> Promote Backup
                            </button>
                        </form>
                    </li>
                    @endif
                    @if(!$classSession->isCancelled())
                    <li>
                        <a href="#" class="text-error" data-overlay="#cancel-modal">
                            <span class="icon-[tabler--x] size-4"></span> Cancel Session
                        </a>
                    </li>
                    @endif
                    @if(!$classSession->isPublished())
                    <li>
                        <form action="{{ route('class-sessions.destroy', $classSession) }}" method="POST" class="m-0" onsubmit="return confirm('Delete this session?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full text-left flex items-center gap-2 text-error">
                                <span class="icon-[tabler--trash] size-4"></span> Delete
                            </button>
                        </form>
                    </li>
                    @endif
                </ul>
            </details>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main Content --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Session Details --}}
            <div class="card bg-base-100">
                <div class="card-header">
                    <h3 class="card-title">Session Details</h3>
                </div>
                <div class="card-body">
                    <dl class="grid grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm text-base-content/60">Class Plan</dt>
                            <dd class="font-medium">{{ $classSession->classPlan->name }}</dd>
                            <dd class="text-sm text-base-content/60">Default price: ${{ number_format($classSession->classPlan->default_price, 2) }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-base-content/60">Category</dt>
                            <dd class="capitalize">{{ $classSession->classPlan->category }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-base-content/60">Duration</dt>
                            <dd>{{ $classSession->formatted_duration }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-base-content/60">Capacity</dt>
                            <dd>{{ $classSession->capacity }} spots</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-base-content/60">Session Price</dt>
                            <dd class="font-medium">{{ $classSession->formatted_price }}</dd>
                            @if($classSession->price && $classSession->price != $classSession->classPlan->default_price)
                            <dd class="text-xs text-base-content/50"><s>Plan: ${{ number_format($classSession->classPlan->default_price, 2) }}</s></dd>
                            @endif
                        </div>
                        <div>
                            <dt class="text-sm text-base-content/60">Difficulty</dt>
                            <dd><span class="badge {{ $classSession->classPlan->getDifficultyBadgeClass() }} badge-soft badge-sm capitalize">{{ str_replace('_', ' ', $classSession->classPlan->difficulty_level) }}</span></dd>
                        </div>
                    </dl>
                </div>
            </div>

            {{-- Instructors --}}
            <div class="card bg-base-100">
                <div class="card-header">
                    <h3 class="card-title">Instructors</h3>
                </div>
                <div class="card-body">
                    <div class="space-y-4">
                        {{-- Primary Instructor --}}
                        <div class="flex items-center gap-3 p-3 bg-base-200 rounded-lg">
                            @if($classSession->primaryInstructor->photo_url)
                            <img src="{{ $classSession->primaryInstructor->photo_url }}" alt="{{ $classSession->primaryInstructor->name }}" class="w-12 h-12 rounded-full object-cover">
                            @else
                            <div class="avatar avatar-placeholder">
                                <div class="bg-primary text-primary-content w-12 h-12 rounded-full font-bold">
                                    {{ strtoupper(substr($classSession->primaryInstructor->name, 0, 1)) }}
                                </div>
                            </div>
                            @endif
                            <div>
                                <div class="font-medium">{{ $classSession->primaryInstructor->name }}</div>
                                <div class="text-sm text-base-content/60">Primary Instructor</div>
                            </div>
                        </div>

                        {{-- Backup Instructors --}}
                        @if($classSession->backupInstructors->isNotEmpty())
                        <div>
                            <div class="text-sm text-base-content/60 mb-2">Backup Instructors (in priority order)</div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                @foreach($classSession->backupInstructors as $index => $backupInstructor)
                                <div class="flex items-center gap-3 p-3 bg-base-200 rounded-lg">
                                    @if($backupInstructor->photo_url)
                                    <img src="{{ $backupInstructor->photo_url }}" alt="{{ $backupInstructor->name }}" class="w-10 h-10 rounded-full object-cover">
                                    @else
                                    <div class="avatar avatar-placeholder">
                                        <div class="bg-secondary text-secondary-content w-10 h-10 rounded-full text-sm font-bold">
                                            {{ strtoupper(substr($backupInstructor->name, 0, 1)) }}
                                        </div>
                                    </div>
                                    @endif
                                    <div>
                                        <div class="font-medium">{{ $backupInstructor->name }}</div>
                                        <div class="text-xs text-base-content/60">Backup #{{ $index + 1 }}</div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @else
                        <div class="flex items-center gap-3 p-3 bg-base-200/50 rounded-lg border border-dashed border-base-content/20">
                            <div class="w-12 h-12 rounded-full bg-base-300 flex items-center justify-center">
                                <span class="icon-[tabler--user-plus] size-6 text-base-content/30"></span>
                            </div>
                            <div>
                                <div class="text-base-content/60">No backup instructors assigned</div>
                                <a href="{{ route('class-sessions.edit', $classSession) }}" class="text-sm text-primary hover:underline">Add backup instructors</a>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Location --}}
            @if($classSession->location)
            <div class="card bg-base-100">
                <div class="card-header">
                    <h3 class="card-title">Location</h3>
                </div>
                <div class="card-body space-y-4">
                    <div class="flex items-start gap-3">
                        <span class="{{ $classSession->location->type_icon }} size-6 text-base-content/60 mt-0.5"></span>
                        <div>
                            <div class="font-medium">{{ $classSession->location->name }}</div>
                            {{-- Location Types --}}
                            @if($classSession->location->location_types && count($classSession->location->location_types) > 0)
                            <div class="flex flex-wrap gap-1 mt-1">
                                @foreach($classSession->location->location_types as $type)
                                <span class="badge badge-soft badge-xs {{ match($type) {
                                    'in_person' => 'badge-primary',
                                    'public' => 'badge-success',
                                    'virtual' => 'badge-info',
                                    'mobile' => 'badge-warning',
                                    default => 'badge-neutral',
                                } }}">{{ \App\Models\Location::getLocationTypeOptions()[$type] ?? $type }}</span>
                                @endforeach
                            </div>
                            @endif
                            {{-- Address --}}
                            @if($classSession->location->full_address && !$classSession->location->isVirtual())
                            <div class="text-sm text-base-content/60 mt-1">{{ $classSession->location->full_address }}</div>
                            @endif
                            {{-- Virtual Platform --}}
                            @if($classSession->location->isVirtual() && $classSession->location->virtual_platform)
                            <div class="text-sm text-base-content/60 mt-1">Platform: {{ $classSession->location->virtual_platform_label }}</div>
                            @endif
                        </div>
                    </div>

                    {{-- Room(s) for In-Person --}}
                    @if($classSession->room)
                    <div class="flex items-center gap-2 p-3 bg-base-200 rounded-lg">
                        <span class="icon-[tabler--door] size-5 text-base-content/60"></span>
                        <div>
                            <span class="font-medium">{{ $classSession->room->name }}</span>
                            <span class="text-sm text-base-content/60">(capacity: {{ $classSession->room->capacity }})</span>
                        </div>
                    </div>
                    @endif

                    {{-- Location Notes --}}
                    @if($classSession->location_notes)
                    <div class="p-3 bg-base-200 rounded-lg">
                        <div class="text-sm font-medium text-base-content/60 mb-1">Location Notes</div>
                        <p class="text-sm whitespace-pre-wrap">{{ $classSession->location_notes }}</p>
                    </div>
                    @endif

                    {{-- Public Location Instructions --}}
                    @if($classSession->location->isPublic() && $classSession->location->public_location_notes)
                    <div class="alert alert-soft alert-info">
                        <span class="icon-[tabler--info-circle] size-5"></span>
                        <div>
                            <div class="font-medium text-sm">Meeting Instructions</div>
                            <p class="text-sm">{{ $classSession->location->public_location_notes }}</p>
                        </div>
                    </div>
                    @endif

                    {{-- Virtual Access Notes --}}
                    @if($classSession->location->isVirtual() && $classSession->location->virtual_access_notes)
                    <div class="alert alert-soft alert-info">
                        <span class="icon-[tabler--video] size-5"></span>
                        <div>
                            <div class="font-medium text-sm">Access Notes</div>
                            <p class="text-sm">{{ $classSession->location->virtual_access_notes }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            {{-- Notes --}}
            @if($classSession->notes)
            <div class="card bg-base-100">
                <div class="card-header">
                    <h3 class="card-title">Internal Notes</h3>
                </div>
                <div class="card-body">
                    <p class="text-base-content/80 whitespace-pre-wrap">{{ $classSession->notes }}</p>
                </div>
            </div>
            @endif

            {{-- Recurring Sessions --}}
            @if($classSession->isRecurrenceParent() && $classSession->recurrenceChildren->isNotEmpty())
            <div class="card bg-base-100">
                <div class="card-header">
                    <h3 class="card-title">Recurring Sessions</h3>
                    <span class="badge badge-soft badge-primary badge-sm">{{ $classSession->recurrenceChildren->count() }} sessions</span>
                </div>
                <div class="card-body p-0">
                    <div class="overflow-x-auto">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($classSession->recurrenceChildren->take(10) as $child)
                                <tr>
                                    <td>{{ $child->start_time->format('D, M j, Y') }}</td>
                                    <td>{{ $child->formatted_time_range }}</td>
                                    <td><span class="badge {{ $child->getStatusBadgeClass() }} badge-soft badge-xs capitalize">{{ $child->status }}</span></td>
                                    <td><a href="{{ route('class-sessions.show', $child) }}" class="btn btn-ghost btn-xs">View</a></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($classSession->recurrenceChildren->count() > 10)
                    <div class="p-3 text-center text-sm text-base-content/60">
                        And {{ $classSession->recurrenceChildren->count() - 10 }} more...
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Quick Info --}}
            <div class="card bg-base-100">
                <div class="card-header">
                    <h3 class="card-title">Session Info</h3>
                </div>
                <div class="card-body">
                    <dl class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-base-content/60">Status</dt>
                            <dd><span class="badge {{ $classSession->getStatusBadgeClass() }} badge-soft badge-sm capitalize">{{ $classSession->status }}</span></dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-base-content/60">Created</dt>
                            <dd>{{ $classSession->created_at->format('M j, Y') }}</dd>
                        </div>
                        @if($classSession->isRecurring())
                        <div class="border-t border-base-content/10 pt-3 mt-3">
                            <dt class="text-base-content/60 mb-2 flex items-center gap-1">
                                <span class="icon-[tabler--repeat] size-4"></span> Recurrence
                            </dt>
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-base-content/60">Type</span>
                                    <span class="badge badge-soft badge-info badge-sm">Recurring</span>
                                </div>
                                @if($classSession->isRecurrenceParent())
                                <div class="flex justify-between">
                                    <span class="text-base-content/60">Sessions</span>
                                    <span>{{ $classSession->recurrenceChildren->count() + 1 }} total</span>
                                </div>
                                @if($classSession->recurrenceChildren->isNotEmpty())
                                <div class="flex justify-between">
                                    <span class="text-base-content/60">Ends</span>
                                    <span>{{ $classSession->recurrenceChildren->last()->start_time->format('M j, Y') }}</span>
                                </div>
                                @endif
                                @if($classSession->recurrence_rule)
                                @php
                                    $rule = is_string($classSession->recurrence_rule) ? json_decode($classSession->recurrence_rule, true) : $classSession->recurrence_rule;
                                    $days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
                                    $selectedDays = isset($rule['days']) ? array_map(fn($d) => $days[$d], $rule['days']) : [];
                                @endphp
                                @if(!empty($selectedDays))
                                <div class="flex justify-between">
                                    <span class="text-base-content/60">Days</span>
                                    <span>{{ implode(', ', $selectedDays) }}</span>
                                </div>
                                @endif
                                @endif
                                @else
                                {{-- This is a child session --}}
                                <div class="flex justify-between">
                                    <span class="text-base-content/60">Parent</span>
                                    <a href="{{ route('class-sessions.show', $classSession->recurrenceParent) }}" class="text-primary hover:underline">View series</a>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endif
                        @if($classSession->isCancelled())
                        <div class="border-t border-base-content/10 pt-3 mt-3">
                            <div class="flex justify-between">
                                <dt class="text-base-content/60">Cancelled</dt>
                                <dd>{{ $classSession->cancelled_at->format('M j, Y') }}</dd>
                            </div>
                            @if($classSession->cancellation_reason)
                            <div class="mt-2">
                                <dt class="text-base-content/60 mb-1">Reason</dt>
                                <dd class="text-error">{{ $classSession->cancellation_reason }}</dd>
                            </div>
                            @endif
                        </div>
                        @endif
                    </dl>
                </div>
            </div>

            {{-- Bookings (placeholder for future) --}}
            <div class="card bg-base-100">
                <div class="card-header">
                    <h3 class="card-title">Bookings</h3>
                </div>
                <div class="card-body text-center py-8">
                    <span class="icon-[tabler--users] size-10 text-base-content/20 mx-auto mb-2"></span>
                    <p class="text-sm text-base-content/60">0 / {{ $classSession->capacity }} booked</p>
                    <p class="text-xs text-base-content/40 mt-1">Booking management coming soon</p>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Cancel Modal --}}
<div id="cancel-modal" class="overlay modal overlay-open:opacity-100 hidden" role="dialog" tabindex="-1">
    <div class="modal-dialog modal-dialog-sm">
        <div class="modal-content">
            <form action="{{ route('class-sessions.cancel', $classSession) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-header">
                    <h3 class="modal-title">Cancel Session</h3>
                    <button type="button" class="btn btn-text btn-circle btn-sm absolute end-3 top-3" aria-label="Close" data-overlay="#cancel-modal">
                        <span class="icon-[tabler--x] size-4"></span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="mb-4">Are you sure you want to cancel this class session?</p>
                    <div>
                        <label class="label-text" for="cancellation_reason">Reason (optional)</label>
                        <textarea id="cancellation_reason" name="cancellation_reason" rows="3" class="textarea w-full" placeholder="Enter a reason for cancellation..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-ghost" data-overlay="#cancel-modal">Keep Session</button>
                    <button type="submit" class="btn btn-error">Cancel Session</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
