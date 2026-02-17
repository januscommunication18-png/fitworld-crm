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

    {{-- Booking Stats --}}
    @php
        $allBookings = \App\Models\Booking::forClient($client->id)->get();
        $bookingStats = [
            'total' => $allBookings->count(),
            'attended' => $allBookings->whereNotNull('checked_in_at')->count(),
            'cancelled' => $allBookings->where('status', 'cancelled')->count(),
            'no_show' => $allBookings->where('status', 'no_show')->count(),
        ];
    @endphp
    <div class="bg-base-200/50 rounded-xl p-4 mb-4">
        <div class="flex items-center gap-2 mb-3">
            <span class="icon-[tabler--chart-bar] size-4 text-primary"></span>
            <h4 class="text-sm font-semibold uppercase tracking-wide">Booking Stats</h4>
        </div>
        <div class="grid grid-cols-4 gap-2">
            <div class="bg-base-100 rounded-lg p-2 text-center">
                <div class="text-lg font-bold">{{ $bookingStats['total'] }}</div>
                <div class="text-xs text-base-content/60">Total</div>
            </div>
            <div class="bg-base-100 rounded-lg p-2 text-center">
                <div class="text-lg font-bold text-success">{{ $bookingStats['attended'] }}</div>
                <div class="text-xs text-base-content/60">Attended</div>
            </div>
            <div class="bg-base-100 rounded-lg p-2 text-center">
                <div class="text-lg font-bold text-warning">{{ $bookingStats['cancelled'] }}</div>
                <div class="text-xs text-base-content/60">Cancelled</div>
            </div>
            <div class="bg-base-100 rounded-lg p-2 text-center">
                <div class="text-lg font-bold text-error">{{ $bookingStats['no_show'] }}</div>
                <div class="text-xs text-base-content/60">No Show</div>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-3 mt-3">
            <div class="flex items-center justify-between text-sm">
                <span class="text-base-content/60">Last Visit</span>
                <span class="font-medium">{{ $client->last_visit_at?->diffForHumans() ?? 'Never' }}</span>
            </div>
            <div class="flex items-center justify-between text-sm">
                <span class="text-base-content/60">Client Since</span>
                <span class="font-medium">{{ $client->created_at->format('M Y') }}</span>
            </div>
        </div>
    </div>

    {{-- Recent Bookings --}}
    @php
        $recentBookings = \App\Models\Booking::forClient($client->id)
            ->with(['bookable.location'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Eager load the correct relationships based on bookable type
        $recentBookings->each(function ($booking) {
            if ($booking->bookable instanceof \App\Models\ClassSession) {
                $booking->bookable->load(['primaryInstructor', 'classPlan']);
            } elseif ($booking->bookable instanceof \App\Models\ServiceSlot) {
                $booking->bookable->load(['instructor', 'servicePlan']);
            }
        });
    @endphp
    @if($recentBookings->count() > 0)
        <div class="bg-base-200/50 rounded-xl p-4 mb-4">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-2">
                    <span class="icon-[tabler--calendar-event] size-4 text-primary"></span>
                    <h4 class="text-sm font-semibold uppercase tracking-wide">Recent Bookings</h4>
                </div>
                <a href="{{ route('clients.show', $client) }}?tab=bookings" class="text-xs text-primary hover:underline">View All</a>
            </div>
            <div class="space-y-2">
                @foreach($recentBookings as $booking)
                    @php
                        $isServiceSlot = $booking->bookable instanceof \App\Models\ServiceSlot;
                        $instructor = $isServiceSlot
                            ? ($booking->bookable->instructor ?? null)
                            : ($booking->bookable->primaryInstructor ?? null);
                        $icon = $isServiceSlot ? 'icon-[tabler--massage]' : 'icon-[tabler--yoga]';
                        $title = $isServiceSlot
                            ? ($booking->bookable->servicePlan->name ?? 'Service')
                            : ($booking->bookable->display_title ?? $booking->bookable->classPlan->name ?? 'Class');
                        $statusBadge = match($booking->status) {
                            'confirmed' => 'badge-success',
                            'cancelled' => 'badge-error',
                            'no_show' => 'badge-warning',
                            default => 'badge-neutral'
                        };
                    @endphp
                    <div class="bg-base-100 rounded-lg p-3 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg flex items-center justify-center {{ $isServiceSlot ? 'bg-secondary/10' : 'bg-primary/10' }}">
                                <span class="{{ $icon }} size-4 {{ $isServiceSlot ? 'text-secondary' : 'text-primary' }}"></span>
                            </div>
                            <div>
                                <div class="font-medium text-sm">{{ $title }}</div>
                                <div class="text-xs text-base-content/60">
                                    @if($booking->bookable && $booking->bookable->start_time)
                                        {{ $booking->bookable->start_time->format('M j, g:i A') }}
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-1">
                            @if($booking->checked_in_at)
                                <span class="badge badge-xs badge-success">Attended</span>
                            @else
                                <span class="badge badge-sm {{ $statusBadge }} badge-soft">{{ ucfirst($booking->status) }}</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Questionnaire Responses --}}
    @php
        $questionnaireResponses = $client->questionnaireResponses()
            ->with(['version.questionnaire', 'booking.bookable'])
            ->latest()
            ->take(3)
            ->get();

        $questionnaireResponses->each(function ($response) {
            if ($response->booking && $response->booking->bookable) {
                if ($response->booking->bookable instanceof \App\Models\ClassSession) {
                    $response->booking->bookable->load('classPlan');
                } elseif ($response->booking->bookable instanceof \App\Models\ServiceSlot) {
                    $response->booking->bookable->load('servicePlan');
                }
            }
        });
    @endphp
    @if($questionnaireResponses->count() > 0)
        <div class="bg-base-200/50 rounded-xl p-4 mb-4">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-2">
                    <span class="icon-[tabler--forms] size-4 text-primary"></span>
                    <h4 class="text-sm font-semibold uppercase tracking-wide">Questionnaires</h4>
                </div>
                <a href="{{ route('clients.show', $client) }}?tab=questionnaires" class="text-xs text-primary hover:underline">View All</a>
            </div>
            <div class="space-y-2">
                @foreach($questionnaireResponses as $response)
                    @php
                        $bookingInfo = null;
                        $bookingIcon = 'icon-[tabler--calendar]';
                        if ($response->booking && $response->booking->bookable) {
                            $bookable = $response->booking->bookable;
                            if ($bookable instanceof \App\Models\ServiceSlot) {
                                $bookingInfo = $bookable->servicePlan->name ?? 'Service';
                                $bookingIcon = 'icon-[tabler--massage]';
                            } elseif ($bookable instanceof \App\Models\ClassSession) {
                                $bookingInfo = $bookable->classPlan->name ?? $bookable->display_title ?? 'Class';
                                $bookingIcon = 'icon-[tabler--yoga]';
                            }
                        }
                    @endphp
                    <div class="bg-base-100 rounded-lg p-3">
                        <div class="flex items-center justify-between">
                            <div class="font-medium text-sm">{{ $response->version?->questionnaire?->name ?? 'Unknown' }}</div>
                            <span class="badge badge-xs {{ \App\Models\QuestionnaireResponse::getStatusBadgeClass($response->status) }}">
                                {{ \App\Models\QuestionnaireResponse::getStatuses()[$response->status] ?? $response->status }}
                            </span>
                        </div>
                        @if($bookingInfo)
                            <div class="flex items-center gap-1 mt-1 text-xs text-base-content/60">
                                <span class="{{ $bookingIcon }} size-3"></span>
                                <span>{{ $bookingInfo }}</span>
                                @if($response->booking->bookable->start_time)
                                    <span class="mx-1">·</span>
                                    <span>{{ $response->booking->bookable->start_time->format('M j') }}</span>
                                @endif
                            </div>
                        @endif
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
