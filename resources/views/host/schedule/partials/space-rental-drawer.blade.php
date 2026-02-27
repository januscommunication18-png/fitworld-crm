{{-- Space Rental Drawer --}}
{{-- Usage: @include('host.schedule.partials.space-rental-drawer', ['spaceRental' => $spaceRental]) --}}

@php
    $statuses = \App\Models\SpaceRental::getStatuses();
    $statusColors = [
        'draft' => 'from-warning/10 to-warning/5',
        'pending' => 'from-warning/10 to-warning/5',
        'confirmed' => 'from-success/10 to-success/5',
        'in_progress' => 'from-info/10 to-info/5',
        'completed' => 'from-neutral/10 to-neutral/5',
        'cancelled' => 'from-error/10 to-error/5',
    ];
    $statusIconColors = [
        'draft' => 'bg-warning/20 text-warning',
        'pending' => 'bg-warning/20 text-warning',
        'confirmed' => 'bg-success/20 text-success',
        'in_progress' => 'bg-info/20 text-info',
        'completed' => 'bg-neutral/20 text-neutral',
        'cancelled' => 'bg-error/20 text-error',
    ];
    $purposeIcons = [
        'photo_shoot' => 'camera',
        'video_production' => 'video',
        'workshop' => 'users',
        'training' => 'school',
        'other' => 'calendar-event',
    ];
@endphp

<x-detail-drawer id="space-rental-{{ $spaceRental->id }}" title="{{ $spaceRental->config?->name ?? 'Space Rental' }}" size="4xl">
    {{-- Status Hero Section --}}
    <div class="bg-gradient-to-r {{ $statusColors[$spaceRental->status] ?? 'from-primary/10 to-primary/5' }} rounded-xl p-4 mb-5 -mt-1">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-full {{ $statusIconColors[$spaceRental->status] ?? 'bg-primary/20 text-primary' }} flex items-center justify-center">
                    <span class="icon-[tabler--{{ $purposeIcons[$spaceRental->purpose] ?? 'building' }}] size-7"></span>
                </div>
                <div>
                    <div class="font-semibold text-lg">{{ $spaceRental->start_time->format('l, M j') }}</div>
                    <div class="text-base-content/70">{{ $spaceRental->formatted_time_range }}</div>
                    <span class="badge {{ $spaceRental->status_badge_class }} mt-1">
                        {{ $spaceRental->formatted_status }}
                    </span>
                </div>
            </div>
            <div class="text-right">
                <div class="text-2xl font-bold">{{ $spaceRental->formatted_total }}</div>
                <div class="text-sm text-base-content/60">{{ $trans['field.total'] ?? 'total' }}</div>
            </div>
        </div>
    </div>

    {{-- Stats Row --}}
    <div class="grid grid-cols-4 gap-3 mb-4">
        <div class="bg-base-200/50 rounded-lg p-3 text-center">
            <div class="text-lg font-bold text-primary">{{ number_format($spaceRental->hours_booked, 1) }}</div>
            <div class="text-xs text-base-content/60">{{ $trans['common.hours'] ?? 'Hours' }}</div>
        </div>
        <div class="bg-base-200/50 rounded-lg p-3 text-center">
            <div class="text-lg font-bold text-info">{{ $spaceRental->formatted_hourly_rate }}</div>
            <div class="text-xs text-base-content/60">{{ $trans['field.hourly_rate'] ?? 'Hourly Rate' }}</div>
        </div>
        <div class="bg-base-200/50 rounded-lg p-3 text-center">
            <div class="text-lg font-bold text-success">{{ $spaceRental->formatted_subtotal }}</div>
            <div class="text-xs text-base-content/60">{{ $trans['field.subtotal'] ?? 'Subtotal' }}</div>
        </div>
        <div class="bg-base-200/50 rounded-lg p-3 text-center">
            @if($spaceRental->deposit_amount > 0)
                <div class="text-lg font-bold text-warning">{{ $spaceRental->formatted_deposit }}</div>
                <div class="text-xs text-base-content/60">{{ $trans['field.deposit'] ?? 'Deposit' }}</div>
            @else
                <div class="text-lg font-bold text-base-content/30">--</div>
                <div class="text-xs text-base-content/60">{{ $trans['field.deposit'] ?? 'Deposit' }}</div>
            @endif
        </div>
    </div>

    {{-- Rental Details Card --}}
    <div class="bg-base-200/50 rounded-xl p-4 mb-4">
        <div class="flex items-center gap-2 mb-3">
            <span class="icon-[tabler--info-circle] size-4 text-primary"></span>
            <h4 class="text-sm font-semibold uppercase tracking-wide">{{ $trans['space_rentals.rental_details'] ?? 'Rental Details' }}</h4>
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div class="bg-base-100 rounded-lg p-3">
                <div class="flex items-center gap-2 text-xs text-base-content/60 mb-1">
                    <span class="icon-[tabler--building] size-3.5"></span>
                    {{ $trans['space_rentals.space'] ?? 'Space' }}
                </div>
                <div class="font-medium text-sm">{{ $spaceRental->config?->name ?? 'Unknown' }}</div>
                @if($spaceRental->config?->room)
                    <div class="text-xs text-base-content/60">{{ $spaceRental->config->room->name }}</div>
                @endif
            </div>
            <div class="bg-base-100 rounded-lg p-3">
                <div class="flex items-center gap-2 text-xs text-base-content/60 mb-1">
                    <span class="icon-[tabler--map-pin] size-3.5"></span>
                    {{ $trans['field.location'] ?? 'Location' }}
                </div>
                <div class="font-medium text-sm">{{ $spaceRental->config?->location?->name ?? 'TBD' }}</div>
            </div>
            <div class="bg-base-100 rounded-lg p-3">
                <div class="flex items-center gap-2 text-xs text-base-content/60 mb-1">
                    <span class="icon-[tabler--{{ $purposeIcons[$spaceRental->purpose] ?? 'tag' }}] size-3.5"></span>
                    {{ $trans['space_rentals.purpose'] ?? 'Purpose' }}
                </div>
                <div class="font-medium text-sm">{{ $spaceRental->formatted_purpose }}</div>
            </div>
            <div class="bg-base-100 rounded-lg p-3">
                <div class="flex items-center gap-2 text-xs text-base-content/60 mb-1">
                    <span class="icon-[tabler--clock] size-3.5"></span>
                    {{ $trans['schedule.duration'] ?? 'Duration' }}
                </div>
                <div class="font-medium text-sm">{{ number_format($spaceRental->hours_booked, 1) }} {{ $trans['common.hours'] ?? 'hours' }}</div>
            </div>
        </div>
    </div>

    {{-- Client Card --}}
    <div class="bg-base-200/50 rounded-xl p-4 mb-4">
        <div class="flex items-center gap-2 mb-3">
            <span class="icon-[tabler--user] size-4 text-primary"></span>
            <h4 class="text-sm font-semibold uppercase tracking-wide">{{ $trans['field.client'] ?? 'Client' }}</h4>
        </div>
        <div class="bg-base-100 rounded-lg p-3">
            <div class="flex items-center gap-3">
                @if($spaceRental->client)
                    <x-avatar
                        :src="$spaceRental->client->avatar_url ?? null"
                        :initials="$spaceRental->client->initials ?? '?'"
                        :alt="$spaceRental->client->full_name"
                        size="sm"
                    />
                @else
                    <div class="avatar placeholder">
                        <div class="bg-secondary text-secondary-content size-10 rounded-full">
                            <span class="icon-[tabler--user] size-5"></span>
                        </div>
                    </div>
                @endif
                <div>
                    <div class="font-medium">{{ $spaceRental->client_name }}</div>
                    @if($spaceRental->client_company)
                        <div class="text-sm text-base-content/60">{{ $spaceRental->client_company }}</div>
                    @endif
                    @if($spaceRental->client_email)
                        <div class="text-xs text-base-content/60">{{ $spaceRental->client_email }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Actions --}}
    @if(!in_array($spaceRental->status, ['completed', 'cancelled']))
    <div class="bg-base-200/50 rounded-xl p-4 mb-4">
        <div class="flex items-center gap-2 mb-3">
            <span class="icon-[tabler--bolt] size-4 text-primary"></span>
            <h4 class="text-sm font-semibold uppercase tracking-wide">{{ $trans['common.quick_actions'] ?? 'Quick Actions' }}</h4>
        </div>
        <div class="flex flex-wrap gap-2">
            @if(in_array($spaceRental->status, ['draft', 'pending']))
                <form action="{{ route('space-rentals.confirm', $spaceRental) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="btn btn-success btn-sm">
                        <span class="icon-[tabler--check] size-4"></span>
                        {{ $trans['btn.confirm'] ?? 'Confirm' }}
                    </button>
                </form>
            @endif

            @if($spaceRental->status === 'confirmed')
                <form action="{{ route('space-rentals.start', $spaceRental) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="btn btn-info btn-sm">
                        <span class="icon-[tabler--player-play] size-4"></span>
                        {{ $trans['btn.start'] ?? 'Start' }}
                    </button>
                </form>
            @endif

            @if($spaceRental->status === 'in_progress')
                <a href="{{ route('space-rentals.show', $spaceRental) }}?action=complete" class="btn btn-primary btn-sm">
                    <span class="icon-[tabler--circle-check] size-4"></span>
                    {{ $trans['btn.complete'] ?? 'Complete' }}
                </a>
            @endif

            @if(in_array($spaceRental->status, ['draft', 'pending', 'confirmed']))
                <a href="{{ route('space-rentals.show', $spaceRental) }}?action=cancel" class="btn btn-soft btn-error btn-sm">
                    <span class="icon-[tabler--x] size-4"></span>
                    {{ $trans['btn.cancel'] ?? 'Cancel' }}
                </a>
            @endif
        </div>
    </div>
    @endif

    {{-- Pending Actions / Checklist --}}
    @if($spaceRental->config?->requires_waiver || $spaceRental->deposit_amount > 0)
    <div class="bg-base-200/50 rounded-xl p-4 mb-4">
        <div class="flex items-center gap-2 mb-3">
            <span class="icon-[tabler--checklist] size-4 text-primary"></span>
            <h4 class="text-sm font-semibold uppercase tracking-wide">{{ $trans['space_rentals.requirements'] ?? 'Requirements' }}</h4>
        </div>
        <div class="space-y-2">
            @if($spaceRental->config?->requires_waiver)
            <div class="flex items-center gap-3 bg-base-100 rounded-lg p-3">
                @if($spaceRental->waiver_signed)
                    <span class="icon-[tabler--circle-check-filled] size-5 text-success"></span>
                    <div class="flex-1">
                        <div class="text-sm font-medium">{{ $trans['space_rentals.waiver_signed'] ?? 'Waiver Signed' }}</div>
                        <div class="text-xs text-base-content/60">{{ $spaceRental->waiver_signer_name }} · {{ $spaceRental->waiver_signed_at?->format('M j, g:i A') }}</div>
                    </div>
                @else
                    <span class="icon-[tabler--circle-dashed] size-5 text-warning"></span>
                    <div class="flex-1">
                        <div class="text-sm font-medium text-warning">{{ $trans['space_rentals.waiver_pending'] ?? 'Waiver Pending' }}</div>
                        <div class="text-xs text-base-content/60">{{ $trans['space_rentals.waiver_required_notice'] ?? 'Required before rental starts' }}</div>
                    </div>
                @endif
            </div>
            @endif

            @if($spaceRental->deposit_amount > 0)
            <div class="flex items-center gap-3 bg-base-100 rounded-lg p-3">
                @if($spaceRental->deposit_status === 'paid')
                    <span class="icon-[tabler--circle-check-filled] size-5 text-success"></span>
                    <div class="flex-1">
                        <div class="text-sm font-medium">{{ $trans['space_rentals.deposit_paid'] ?? 'Deposit Paid' }}</div>
                        <div class="text-xs text-base-content/60">{{ $spaceRental->formatted_deposit }} · {{ $spaceRental->deposit_paid_at?->format('M j, g:i A') }}</div>
                    </div>
                @elseif(in_array($spaceRental->deposit_status, ['refunded', 'partially_refunded']))
                    <span class="icon-[tabler--circle-check-filled] size-5 text-info"></span>
                    <div class="flex-1">
                        <div class="text-sm font-medium">{{ $trans['space_rentals.deposit_refunded'] ?? 'Deposit Refunded' }}</div>
                        <div class="text-xs text-base-content/60">{{ $spaceRental->deposit_refund_reason }}</div>
                    </div>
                @elseif($spaceRental->deposit_status === 'forfeited')
                    <span class="icon-[tabler--circle-x-filled] size-5 text-error"></span>
                    <div class="flex-1">
                        <div class="text-sm font-medium text-error">{{ $trans['space_rentals.deposit_forfeited'] ?? 'Deposit Forfeited' }}</div>
                        <div class="text-xs text-base-content/60">{{ $spaceRental->damage_notes }}</div>
                    </div>
                @else
                    <span class="icon-[tabler--circle-dashed] size-5 text-warning"></span>
                    <div class="flex-1">
                        <div class="text-sm font-medium text-warning">{{ $trans['space_rentals.deposit_pending'] ?? 'Deposit Pending' }}</div>
                        <div class="text-xs text-base-content/60">{{ $spaceRental->formatted_deposit }} {{ $trans['space_rentals.required'] ?? 'required' }}</div>
                    </div>
                @endif
            </div>
            @endif
        </div>
    </div>
    @endif

    {{-- Purpose Notes --}}
    @if($spaceRental->purpose_notes)
    <div class="bg-base-200/50 rounded-xl p-4 mb-4">
        <div class="flex items-center gap-2 mb-3">
            <span class="icon-[tabler--notes] size-4 text-primary"></span>
            <h4 class="text-sm font-semibold uppercase tracking-wide">{{ $trans['space_rentals.purpose_notes'] ?? 'Purpose Notes' }}</h4>
        </div>
        <div class="bg-base-100 rounded-lg p-3">
            <p class="text-sm text-base-content/80 whitespace-pre-line">{{ $spaceRental->purpose_notes }}</p>
        </div>
    </div>
    @endif

    <x-slot name="footer">
        <a href="{{ route('space-rentals.edit', $spaceRental) }}" class="btn btn-soft btn-primary {{ in_array($spaceRental->status, ['completed', 'cancelled', 'in_progress']) ? 'btn-disabled' : '' }}">
            <span class="icon-[tabler--edit] size-4 me-1"></span>
            {{ $trans['btn.edit'] ?? 'Edit' }}
        </a>
        <a href="{{ route('space-rentals.show', $spaceRental) }}" class="btn btn-primary">
            <span class="icon-[tabler--external-link] size-4 me-1"></span>
            {{ $trans['btn.view_full_details'] ?? 'View Full Details' }}
        </a>
    </x-slot>
</x-detail-drawer>
