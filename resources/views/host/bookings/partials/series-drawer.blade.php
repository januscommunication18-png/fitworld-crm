{{-- Series Sessions Drawer --}}
{{-- Lists every Booking in a series (one row per scheduled session) so the
     host can see which dates are booked, who the instructor is, and the
     check-in / status of each session inside one series purchase. --}}
{{-- Required vars: $seriesId, $sessions (Collection of Booking), $aggregate (array|null) --}}

@php
    $statuses = $statuses ?? \App\Models\Booking::getStatuses();
    $firstSession = $sessions->first();
    $client = $firstSession?->client;
    $planName = optional(optional($firstSession?->bookable)->classPlan)->name
        ?? optional($firstSession?->bookable)->display_title
        ?? 'Series';
@endphp

<x-detail-drawer id="series-{{ $seriesId }}" title="Series · {{ $planName }}" size="4xl">
    {{-- Summary Hero --}}
    <div class="bg-gradient-to-r from-accent/10 to-accent/5 rounded-xl p-4 mb-5 -mt-1">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-full bg-accent/20 flex items-center justify-center">
                    <span class="icon-[tabler--calendar-repeat] size-6 text-accent"></span>
                </div>
                <div>
                    <div class="text-xs text-base-content/60 uppercase tracking-wide">Series Purchase</div>
                    <div class="font-semibold">{{ $planName }}</div>
                </div>
            </div>
            @if($aggregate)
                <div class="flex flex-wrap items-center gap-4 text-sm">
                    <div>
                        <div class="text-xs text-base-content/60 uppercase">Total</div>
                        <div class="font-semibold">{{ $aggregate['total'] }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-base-content/60 uppercase">Confirmed</div>
                        <div class="font-semibold text-success">{{ $aggregate['confirmed'] }}</div>
                    </div>
                    @if($aggregate['cancelled'] > 0)
                        <div>
                            <div class="text-xs text-base-content/60 uppercase">Cancelled</div>
                            <div class="font-semibold text-base-content/60">{{ $aggregate['cancelled'] }}</div>
                        </div>
                    @endif
                    @if($aggregate['total_paid'] > 0)
                        <div>
                            <div class="text-xs text-base-content/60 uppercase">Paid</div>
                            <div class="font-semibold">${{ number_format($aggregate['total_paid'], 2) }}</div>
                        </div>
                    @endif
                </div>
            @endif
        </div>
        @if($aggregate && $aggregate['first_session_at'])
            <div class="mt-3 text-sm text-base-content/70">
                <span class="icon-[tabler--calendar] size-4 inline-block align-middle"></span>
                {{ \Carbon\Carbon::parse($aggregate['first_session_at'])->format('M j, Y') }} – {{ \Carbon\Carbon::parse($aggregate['last_session_at'])->format('M j, Y') }}
            </div>
        @endif
    </div>

    {{-- Client --}}
    @if($client)
        <div class="bg-base-200/50 rounded-xl p-4 mb-4">
            <div class="flex items-center gap-3">
                <x-avatar :src="$client->avatar_url" :initials="$client->initials" :alt="$client->full_name" size="sm" />
                <div class="flex-1 min-w-0">
                    <div class="font-medium">{{ $client->full_name }}</div>
                    <div class="text-sm text-base-content/60">{{ $client->email ?: $client->phone ?: '-' }}</div>
                </div>
                <a href="{{ route('clients.show', $client) }}" class="btn btn-ghost btn-xs">
                    <span class="icon-[tabler--external-link] size-4"></span> View Client
                </a>
            </div>
        </div>
    @endif

    {{-- Sessions List --}}
    <div class="bg-base-100 rounded-xl border border-base-200">
        <div class="px-4 py-3 border-b border-base-200 flex items-center justify-between">
            <h4 class="text-sm font-semibold uppercase tracking-wide">Sessions ({{ $sessions->count() }})</h4>
            <span class="text-xs text-base-content/50">{{ $seriesId }}</span>
        </div>
        <div class="overflow-x-auto">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Instructor</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th class="w-16 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sessions as $session)
                        @php $cs = $session->bookable; @endphp
                        <tr class="hover:bg-base-200/30">
                            <td>
                                @if($cs?->start_time)
                                    <span class="font-medium">{{ $cs->start_time->format('M j, Y') }}</span>
                                    <div class="text-xs text-base-content/60">{{ $cs->start_time->format('D') }}</div>
                                @else
                                    <span class="text-base-content/50">—</span>
                                @endif
                            </td>
                            <td>
                                @if($cs?->start_time)
                                    {{ $cs->start_time->format('g:i A') }}
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                @if($cs?->primaryInstructor)
                                    {{ $cs->primaryInstructor->name }}
                                @else
                                    <span class="text-base-content/40">TBD</span>
                                @endif
                            </td>
                            <td>
                                {{ optional(optional($cs?->room)->location)->name ?? '—' }}
                            </td>
                            <td>
                                <span class="badge badge-sm {{ $session->status_badge_class }} badge-soft">
                                    {{ $statuses[$session->status] ?? $session->status }}
                                </span>
                                @if($session->isCheckedIn())
                                    <div class="text-xs text-success mt-0.5">
                                        <span class="icon-[tabler--check] size-3"></span> Checked in
                                    </div>
                                @endif
                            </td>
                            <td>
                                <x-actions-dropdown size="xs">
                                    <li>
                                        <a href="{{ route('bookings.show', $session) }}">
                                            <span class="icon-[tabler--external-link] size-4"></span> View Booking
                                        </a>
                                    </li>
                                    @if($cs)
                                        <li>
                                            <a href="{{ route('class-sessions.show', $cs->id) }}">
                                                <span class="icon-[tabler--calendar-event] size-4"></span> View Session
                                            </a>
                                        </li>
                                    @endif
                                    @if($session->canBeCancelled() && auth()->user()->hasPermission('bookings.cancel'))
                                        <li>
                                            <button type="button" class="w-full text-left flex items-center gap-2 text-error"
                                                    onclick="openCancelModal({{ $session->id }}, {{ $session->isLateCancellation() ? 'true' : 'false' }})">
                                                <span class="icon-[tabler--x] size-4"></span> Cancel This Session
                                            </button>
                                        </li>
                                    @endif
                                </x-actions-dropdown>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-detail-drawer>
