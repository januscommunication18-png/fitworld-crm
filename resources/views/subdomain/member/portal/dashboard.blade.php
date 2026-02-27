@extends('layouts.subdomain')

@section('title', ($trans['member.portal.title'] ?? 'Member Portal') . ' — ' . $host->studio_name)

@section('content')
<div class="min-h-screen flex flex-col">
    @include('subdomain.member.portal._nav')

    {{-- Portal Content --}}
    <div class="flex-1 bg-base-200">
        <div class="container-fixed py-8">
    {{-- Welcome Section --}}
    <div class="mb-8">
        <h1 class="text-2xl font-bold">{{ $trans['member.dashboard.welcome'] ?? 'Welcome back' }}, {{ $member->first_name }}!</h1>
        <p class="text-base-content/60 mt-1">{{ $trans['member.dashboard.subtitle'] ?? "Here's what's happening with your account." }}</p>
    </div>

    {{-- Pending Intake Forms Alert --}}
    @if($pendingIntakeForms->count() > 0)
    <div class="alert alert-warning mb-6">
        <span class="icon-[tabler--forms] size-5"></span>
        <div>
            <h3 class="font-bold">{{ $trans['member.dashboard.intake_pending'] ?? 'Intake Forms Pending' }}</h3>
            <p class="text-sm">{{ $trans['member.dashboard.intake_count'] ?? 'You have' }} {{ $pendingIntakeForms->count() }} {{ $trans['member.dashboard.intake_to_complete'] ?? 'intake form(s) to complete.' }}</p>
        </div>
        <a href="{{ $pendingIntakeForms->first()->getResponseUrl() }}" class="btn btn-sm btn-warning">{{ $trans['btn.complete_now'] ?? 'Complete Now' }}</a>
    </div>
    @endif

    {{-- Quick Stats --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="card bg-base-100">
            <div class="card-body">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center">
                        <span class="icon-[tabler--calendar-event] size-6 text-primary"></span>
                    </div>
                    <div>
                        <p class="text-2xl font-bold">{{ $upcomingBookings->count() }}</p>
                        <p class="text-sm text-base-content/60">{{ $trans['member.dashboard.upcoming_bookings'] ?? 'Upcoming Bookings' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card bg-base-100">
            <div class="card-body">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-secondary/10 flex items-center justify-center">
                        <span class="icon-[tabler--id-badge-2] size-6 text-secondary"></span>
                    </div>
                    <div>
                        <p class="text-2xl font-bold">{{ $activeMemberships->count() }}</p>
                        <p class="text-sm text-base-content/60">{{ $trans['member.dashboard.active_memberships'] ?? 'Active Memberships' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card bg-base-100">
            <div class="card-body">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-success/10 flex items-center justify-center">
                        <span class="icon-[tabler--ticket] size-6 text-success"></span>
                    </div>
                    <div>
                        @php
                            $totalClasses = $activeClassPacks->sum('classes_remaining');
                        @endphp
                        <p class="text-2xl font-bold">{{ $totalClasses }}</p>
                        <p class="text-sm text-base-content/60">{{ $trans['member.dashboard.class_credits'] ?? 'Class Credits' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <a href="{{ route('subdomain.class-request', ['subdomain' => $host->subdomain]) }}"
           class="card bg-base-100 hover:bg-primary/5 hover:border-primary/50 border border-base-200 transition-all">
            <div class="card-body items-center text-center py-6">
                <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center mb-2">
                    <span class="icon-[tabler--yoga] size-6 text-primary"></span>
                </div>
                <span class="font-medium text-sm">{{ $trans['member.dashboard.book_class'] ?? 'Book a Class' }}</span>
            </div>
        </a>
        <a href="{{ route('member.portal.services', ['subdomain' => $host->subdomain]) }}"
           class="card bg-base-100 hover:bg-secondary/5 hover:border-secondary/50 border border-base-200 transition-all">
            <div class="card-body items-center text-center py-6">
                <div class="w-12 h-12 rounded-xl bg-secondary/10 flex items-center justify-center mb-2">
                    <span class="icon-[tabler--sparkles] size-6 text-secondary"></span>
                </div>
                <span class="font-medium text-sm">{{ $trans['member.dashboard.browse_services'] ?? 'Browse Services' }}</span>
            </div>
        </a>
        <a href="{{ route('member.portal.schedule', ['subdomain' => $host->subdomain]) }}"
           class="card bg-base-100 hover:bg-success/5 hover:border-success/50 border border-base-200 transition-all">
            <div class="card-body items-center text-center py-6">
                <div class="w-12 h-12 rounded-xl bg-success/10 flex items-center justify-center mb-2">
                    <span class="icon-[tabler--calendar] size-6 text-success"></span>
                </div>
                <span class="font-medium text-sm">{{ $trans['member.dashboard.view_schedule'] ?? 'View Schedule' }}</span>
            </div>
        </a>
        <a href="{{ route('member.portal.profile', ['subdomain' => $host->subdomain]) }}"
           class="card bg-base-100 hover:bg-info/5 hover:border-info/50 border border-base-200 transition-all">
            <div class="card-body items-center text-center py-6">
                <div class="w-12 h-12 rounded-xl bg-info/10 flex items-center justify-center mb-2">
                    <span class="icon-[tabler--user-cog] size-6 text-info"></span>
                </div>
                <span class="font-medium text-sm">{{ $trans['member.dashboard.edit_profile'] ?? 'Edit Profile' }}</span>
            </div>
        </a>
    </div>

    {{-- Main Content Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Upcoming Classes --}}
        <div class="card bg-base-100">
            <div class="card-body">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold">{{ $trans['member.dashboard.upcoming_bookings'] ?? 'Upcoming Bookings' }}</h2>
                    <a href="{{ route('member.portal.bookings', ['subdomain' => $host->subdomain]) }}" class="btn btn-ghost btn-sm">
                        {{ $trans['btn.view_all'] ?? 'View All' }}
                        <span class="icon-[tabler--arrow-right] size-4"></span>
                    </a>
                </div>

                @if($upcomingBookings->count() > 0)
                    <div class="space-y-3">
                        @foreach($upcomingBookings as $booking)
                            @php $bookable = $booking->bookable; @endphp
                            @if($bookable)
                            <div class="flex items-center gap-4 p-3 rounded-lg bg-base-200/50">
                                <div class="text-center min-w-[50px]">
                                    <p class="text-lg font-bold">{{ $bookable->start_time->format('j') }}</p>
                                    <p class="text-xs text-base-content/60 uppercase">{{ $bookable->start_time->format('M') }}</p>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium truncate">{{ $bookable->display_title ?? $bookable->classPlan?->name ?? $bookable->servicePlan?->name ?? ($trans['common.booking'] ?? 'Booking') }}</p>
                                    <p class="text-sm text-base-content/60">
                                        {{ $bookable->start_time->format('g:i A') }}
                                        @if($bookable->primaryInstructor)
                                            • {{ $bookable->primaryInstructor->name }}
                                        @endif
                                    </p>
                                </div>
                                <span class="badge badge-sm {{ $booking->status === 'confirmed' ? 'badge-success' : ($booking->status === 'waitlisted' ? 'badge-warning' : 'badge-neutral') }}">
                                    {{ ucfirst($booking->status) }}
                                </span>
                            </div>
                            @endif
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <span class="icon-[tabler--calendar-off] size-12 text-base-content/20 mx-auto"></span>
                        <p class="text-base-content/60 mt-2">{{ $trans['member.dashboard.no_upcoming'] ?? 'No upcoming bookings' }}</p>
                        <a href="{{ route('subdomain.class-request', ['subdomain' => $host->subdomain]) }}"
                           class="btn btn-primary btn-sm mt-4">
                            {{ $trans['member.dashboard.book_class'] ?? 'Book a Class' }}
                        </a>
                    </div>
                @endif
            </div>
        </div>

        {{-- Recent Transactions --}}
        <div class="card bg-base-100">
            <div class="card-body">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold">{{ $trans['member.dashboard.recent_transactions'] ?? 'Recent Transactions' }}</h2>
                    <a href="{{ route('member.portal.payments', ['subdomain' => $host->subdomain]) }}" class="btn btn-ghost btn-sm">
                        {{ $trans['btn.view_all'] ?? 'View All' }}
                        <span class="icon-[tabler--arrow-right] size-4"></span>
                    </a>
                </div>

                @if($recentTransactions->count() > 0)
                    <div class="space-y-3">
                        @foreach($recentTransactions as $transaction)
                        <div class="flex items-center justify-between p-3 rounded-lg bg-base-200/50">
                            <div class="flex-1 min-w-0">
                                <p class="font-medium truncate">{{ $transaction->metadata['item_name'] ?? $transaction->type_label }}</p>
                                <p class="text-sm text-base-content/60">{{ $transaction->created_at->format('M j, Y') }}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold">{{ $transaction->formatted_total }}</p>
                                <span class="badge badge-sm {{ $transaction->status_badge_class }}">{{ ucfirst($transaction->status) }}</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <span class="icon-[tabler--receipt-off] size-12 text-base-content/20 mx-auto"></span>
                        <p class="text-base-content/60 mt-2">{{ $trans['member.dashboard.no_transactions'] ?? 'No recent transactions' }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Active Memberships & Class Packs --}}
    @if($activeMemberships->count() > 0 || $activeClassPacks->count() > 0)
    <div class="mt-6">
        <h2 class="text-lg font-semibold mb-4">{{ $trans['member.dashboard.active_plans'] ?? 'Your Active Plans' }}</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($activeMemberships as $membership)
            <div class="card bg-base-100">
                <div class="card-body">
                    <div class="flex items-start justify-between">
                        <div>
                            <span class="badge badge-primary badge-sm mb-2">{{ $trans['page.memberships'] ?? 'Membership' }}</span>
                            <h3 class="font-semibold">{{ $membership->membershipPlan?->name ?? ($trans['page.memberships'] ?? 'Membership') }}</h3>
                        </div>
                        <span class="badge badge-success">{{ $trans['common.active'] ?? 'Active' }}</span>
                    </div>
                    @if($membership->classes_remaining !== null)
                    <p class="text-sm text-base-content/60 mt-2">
                        <span class="font-medium">{{ $membership->classes_remaining }}</span> {{ $trans['member.dashboard.classes_remaining'] ?? 'classes remaining' }}
                    </p>
                    @endif
                    @if($membership->end_date)
                    <p class="text-sm text-base-content/60">
                        {{ $trans['member.dashboard.renews'] ?? 'Renews' }} {{ $membership->end_date->format('M j, Y') }}
                    </p>
                    @endif
                </div>
            </div>
            @endforeach

            @foreach($activeClassPacks as $pack)
            <div class="card bg-base-100">
                <div class="card-body">
                    <div class="flex items-start justify-between">
                        <div>
                            <span class="badge badge-secondary badge-sm mb-2">{{ $trans['member.dashboard.class_pack'] ?? 'Class Pack' }}</span>
                            <h3 class="font-semibold">{{ $pack->classPack?->name ?? ($trans['member.dashboard.class_pack'] ?? 'Class Pack') }}</h3>
                        </div>
                        <span class="badge badge-success">{{ $trans['common.active'] ?? 'Active' }}</span>
                    </div>
                    <p class="text-sm text-base-content/60 mt-2">
                        <span class="font-medium text-lg">{{ $pack->classes_remaining }}</span> / {{ $pack->classPack?->class_count ?? '?' }} {{ $trans['member.dashboard.classes_remaining'] ?? 'classes remaining' }}
                    </p>
                    @if($pack->expires_at)
                    <p class="text-sm text-base-content/60">
                        {{ $trans['member.dashboard.expires'] ?? 'Expires' }} {{ $pack->expires_at->format('M j, Y') }}
                    </p>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Auto-dismiss alerts after 5 seconds
    document.querySelectorAll('.alert').forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s ease-out';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    });
</script>
@endpush
