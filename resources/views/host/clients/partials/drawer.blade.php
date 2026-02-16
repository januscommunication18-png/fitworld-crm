{{-- Client Details Drawer --}}
{{-- Usage: @include('host.clients.partials.drawer', ['client' => $client]) --}}

@php
    $statuses = $statuses ?? \App\Models\Client::getStatuses();
    $sources = $sources ?? \App\Models\Client::getSources();
@endphp

<x-detail-drawer id="client-{{ $client->id }}" title="{{ $client->full_name }}" size="4xl">
    {{-- Status Hero Section --}}
    @php
        $statusColors = [
            'lead' => 'from-warning/10 to-warning/5',
            'client' => 'from-info/10 to-info/5',
            'member' => 'from-success/10 to-success/5',
            'at_risk' => 'from-error/10 to-error/5',
        ];
        $statusIconColors = [
            'lead' => 'bg-warning/20 text-warning',
            'client' => 'bg-info/20 text-info',
            'member' => 'bg-success/20 text-success',
            'at_risk' => 'bg-error/20 text-error',
        ];
        $statusBadges = [
            'lead' => 'badge-warning',
            'client' => 'badge-info',
            'member' => 'badge-success',
            'at_risk' => 'badge-error',
        ];
    @endphp
    <div class="bg-gradient-to-r {{ $statusColors[$client->status] ?? 'from-primary/10 to-primary/5' }} rounded-xl p-4 mb-5 -mt-1">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <x-avatar :src="$client->avatar_url" :initials="$client->initials" :alt="$client->full_name" size="xl" />
                <div>
                    <div class="font-semibold text-xl">{{ $client->full_name }}</div>
                    <span class="badge {{ $statusBadges[$client->status] ?? 'badge-neutral' }} mt-1">
                        {{ $statuses[$client->status] ?? ucfirst($client->status) }}
                    </span>
                </div>
            </div>
            @if($client->is_member)
                <div class="flex items-center gap-2 bg-success/20 text-success px-3 py-2 rounded-lg">
                    <span class="icon-[tabler--id-badge-2] size-5"></span>
                    <span class="font-medium text-sm">Member</span>
                </div>
            @endif
        </div>
    </div>

    {{-- Contact Info Card --}}
    <div class="bg-base-200/50 rounded-xl p-4 mb-4">
        <div class="flex items-center gap-2 mb-3">
            <span class="icon-[tabler--address-book] size-4 text-primary"></span>
            <h4 class="text-sm font-semibold uppercase tracking-wide">Contact Information</h4>
        </div>
        <div class="grid grid-cols-2 gap-3">
            @if($client->email)
                <div class="bg-base-100 rounded-lg p-3">
                    <div class="flex items-center gap-2 text-xs text-base-content/60 mb-1">
                        <span class="icon-[tabler--mail] size-3.5"></span>
                        Email
                    </div>
                    <div class="font-medium text-sm truncate">{{ $client->email }}</div>
                </div>
            @endif
            @if($client->phone)
                <div class="bg-base-100 rounded-lg p-3">
                    <div class="flex items-center gap-2 text-xs text-base-content/60 mb-1">
                        <span class="icon-[tabler--phone] size-3.5"></span>
                        Phone
                    </div>
                    <div class="font-medium text-sm">{{ $client->phone }}</div>
                </div>
            @endif
            @if($client->date_of_birth)
                <div class="bg-base-100 rounded-lg p-3">
                    <div class="flex items-center gap-2 text-xs text-base-content/60 mb-1">
                        <span class="icon-[tabler--cake] size-3.5"></span>
                        Birthday
                    </div>
                    <div class="font-medium text-sm">{{ $client->date_of_birth->format('M j, Y') }}</div>
                </div>
            @endif
            @if($client->gender)
                <div class="bg-base-100 rounded-lg p-3">
                    <div class="flex items-center gap-2 text-xs text-base-content/60 mb-1">
                        <span class="icon-[tabler--gender-bigender] size-3.5"></span>
                        Gender
                    </div>
                    <div class="font-medium text-sm">{{ ucfirst($client->gender) }}</div>
                </div>
            @endif
        </div>
    </div>

    {{-- Activity & Stats Grid --}}
    <div class="grid grid-cols-2 gap-4 mb-4">
        {{-- Stats Card --}}
        <div class="bg-base-200/50 rounded-xl p-4">
            <div class="flex items-center gap-2 mb-3">
                <span class="icon-[tabler--chart-bar] size-4 text-primary"></span>
                <h4 class="text-sm font-semibold uppercase tracking-wide">Stats</h4>
            </div>
            <div class="space-y-3">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-base-content/60">Total Classes</span>
                    <span class="font-semibold">{{ $client->total_classes_attended ?? 0 }}</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-base-content/60">Last Visit</span>
                    <span class="font-medium">{{ $client->last_visit_at?->diffForHumans() ?? 'Never' }}</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-base-content/60">Member Since</span>
                    <span class="font-medium">{{ $client->created_at->format('M Y') }}</span>
                </div>
            </div>
        </div>

        {{-- Source & Info Card --}}
        <div class="bg-base-200/50 rounded-xl p-4">
            <div class="flex items-center gap-2 mb-3">
                <span class="icon-[tabler--info-circle] size-4 text-primary"></span>
                <h4 class="text-sm font-semibold uppercase tracking-wide">Info</h4>
            </div>
            <div class="space-y-3">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-base-content/60">Source</span>
                    <span class="badge badge-sm badge-soft badge-neutral">
                        {{ $sources[$client->lead_source] ?? $client->lead_source ?? 'Unknown' }}
                    </span>
                </div>
                @if($client->emergency_contact_name)
                    <div>
                        <div class="text-xs text-base-content/60 mb-1">Emergency Contact</div>
                        <div class="font-medium text-sm">{{ $client->emergency_contact_name }}</div>
                        @if($client->emergency_contact_phone)
                            <div class="text-xs text-base-content/60">{{ $client->emergency_contact_phone }}</div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Active Bookings --}}
    @php
        $activeBookings = \App\Models\Booking::forClient($client->id)
            ->with(['bookable.primaryInstructor', 'bookable.location'])
            ->whereIn('status', ['confirmed'])
            ->whereHasMorph('bookable', [\App\Models\ClassSession::class], function ($q) {
                $q->where('start_time', '>=', now());
            })
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
    @endphp
    @if($activeBookings->count() > 0)
        <div class="bg-base-200/50 rounded-xl p-4 mb-4">
            <div class="flex items-center gap-2 mb-3">
                <span class="icon-[tabler--calendar-event] size-4 text-primary"></span>
                <h4 class="text-sm font-semibold uppercase tracking-wide">Upcoming Bookings</h4>
                <span class="badge badge-sm badge-primary">{{ $activeBookings->count() }}</span>
            </div>
            <div class="space-y-2">
                @foreach($activeBookings as $booking)
                    <div class="bg-base-100 rounded-lg p-3 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg flex items-center justify-center bg-primary/10">
                                <span class="icon-[tabler--yoga] size-5 text-primary"></span>
                            </div>
                            <div>
                                <div class="font-medium text-sm">{{ $booking->bookable->display_title ?? $booking->bookable->title ?? 'Class' }}</div>
                                <div class="text-xs text-base-content/60">
                                    @if($booking->bookable && $booking->bookable->start_time)
                                        {{ $booking->bookable->start_time->format('M j, g:i A') }}
                                        @if($booking->bookable->primaryInstructor)
                                            <span class="mx-1">·</span> {{ $booking->bookable->primaryInstructor->name }}
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </div>
                        <span class="badge badge-sm badge-success badge-soft">Confirmed</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Tags --}}
    @if($client->tags && $client->tags->count() > 0)
        <div class="bg-base-200/50 rounded-xl p-4 mb-4">
            <div class="flex items-center gap-2 mb-3">
                <span class="icon-[tabler--tags] size-4 text-primary"></span>
                <h4 class="text-sm font-semibold uppercase tracking-wide">Tags</h4>
            </div>
            <div class="flex flex-wrap gap-2">
                @foreach($client->tags as $tag)
                    <span class="badge" style="background-color: {{ $tag->color }}20; color: {{ $tag->color }};">
                        {{ $tag->name }}
                    </span>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Notes Preview --}}
    @if($client->notes)
        <div class="bg-base-200/50 rounded-xl p-4">
            <div class="flex items-center gap-2 mb-3">
                <span class="icon-[tabler--notes] size-4 text-primary"></span>
                <h4 class="text-sm font-semibold uppercase tracking-wide">Notes</h4>
            </div>
            <p class="text-sm text-base-content/70 line-clamp-3">{{ $client->notes }}</p>
        </div>
    @endif

    <x-slot name="footer">
        <div class="flex items-center gap-2">
            <a href="{{ route('clients.edit', $client) }}" class="btn btn-soft btn-primary">
                <span class="icon-[tabler--edit] size-4 me-1"></span>
                Edit
            </a>
        </div>
        <a href="{{ route('clients.show', $client) }}" class="btn btn-primary">
            <span class="icon-[tabler--external-link] size-4 me-1"></span>
            View Full Profile
        </a>
    </x-slot>
</x-detail-drawer>
