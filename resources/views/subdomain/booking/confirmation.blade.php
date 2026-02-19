@extends('layouts.subdomain')

@section('title', 'Booking Confirmed — ' . $host->studio_name)

@section('content')
@php
    $metadata = $transaction->metadata ?? [];
@endphp

<div class="min-h-screen flex flex-col bg-gradient-to-br from-base-200 via-base-100 to-base-200">
    {{-- Header --}}
    <nav class="bg-base-100/80 backdrop-blur-sm border-b border-base-200 sticky top-0 z-50" style="height: 70px;">
        <div class="container-fixed h-full">
            <div class="flex items-center justify-between h-full">
                {{-- Logo --}}
                <div class="flex items-center">
                    @if($host->logo_url)
                        <a href="{{ route('subdomain.home', ['subdomain' => $host->subdomain]) }}" class="flex items-center">
                            <img src="{{ $host->logo_url }}" alt="{{ $host->studio_name }}" class="h-10 w-auto max-w-[160px] object-contain">
                        </a>
                    @else
                        <a href="{{ route('subdomain.home', ['subdomain' => $host->subdomain]) }}" class="flex items-center gap-2">
                            <div class="w-9 h-9 rounded-xl bg-primary flex items-center justify-center">
                                <span class="text-base font-bold text-primary-content">{{ strtoupper(substr($host->studio_name, 0, 1)) }}</span>
                            </div>
                            <span class="font-bold text-base hidden sm:inline">{{ $host->studio_name }}</span>
                        </a>
                    @endif
                </div>

                {{-- Progress Indicator --}}
                <div class="hidden md:flex items-center gap-2 text-sm">
                    <span class="flex items-center gap-1.5 text-success">
                        <span class="w-6 h-6 rounded-full bg-success text-success-content flex items-center justify-center text-xs">
                            <span class="icon-[tabler--check] size-4"></span>
                        </span>
                        Details
                    </span>
                    <span class="icon-[tabler--chevron-right] size-4 text-base-content/30"></span>
                    <span class="flex items-center gap-1.5 text-success">
                        <span class="w-6 h-6 rounded-full bg-success text-success-content flex items-center justify-center text-xs">
                            <span class="icon-[tabler--check] size-4"></span>
                        </span>
                        Payment
                    </span>
                    <span class="icon-[tabler--chevron-right] size-4 text-base-content/30"></span>
                    <span class="flex items-center gap-1.5 text-success font-medium">
                        <span class="w-6 h-6 rounded-full bg-success text-success-content flex items-center justify-center text-xs">
                            <span class="icon-[tabler--check] size-4"></span>
                        </span>
                        Done
                    </span>
                </div>

                {{-- Back to Home --}}
                <a href="{{ route('subdomain.home', ['subdomain' => $host->subdomain]) }}" class="btn btn-ghost btn-sm">
                    <span class="icon-[tabler--home] size-4"></span>
                    <span class="hidden sm:inline">Home</span>
                </a>
            </div>
        </div>
    </nav>

    {{-- Main Content --}}
    <div class="flex-1 py-8 md:py-12">
        <div class="container-fixed">
            <div class="max-w-3xl mx-auto">

                {{-- Success/Pending Hero --}}
                <div class="text-center mb-8">
                    @if($transaction->is_paid)
                        <div class="relative inline-block mb-6">
                            <div class="w-24 h-24 rounded-full bg-success/20 flex items-center justify-center animate-pulse">
                                <div class="w-20 h-20 rounded-full bg-success flex items-center justify-center">
                                    <span class="icon-[tabler--check] size-10 text-success-content"></span>
                                </div>
                            </div>
                            <div class="absolute -right-1 -bottom-1 w-8 h-8 rounded-full bg-base-100 shadow-lg flex items-center justify-center">
                                <span class="icon-[tabler--confetti] size-5 text-warning"></span>
                            </div>
                        </div>
                        <h1 class="text-3xl md:text-4xl font-bold text-success mb-2">Booking Confirmed!</h1>
                        <p class="text-base-content/60 text-lg">Your payment was successful. See you soon!</p>
                    @else
                        <div class="relative inline-block mb-6">
                            <div class="w-24 h-24 rounded-full bg-warning/20 flex items-center justify-center">
                                <div class="w-20 h-20 rounded-full bg-warning flex items-center justify-center">
                                    <span class="icon-[tabler--clock] size-10 text-warning-content"></span>
                                </div>
                            </div>
                        </div>
                        <h1 class="text-3xl md:text-4xl font-bold mb-2">Booking Received!</h1>
                        <p class="text-base-content/60 text-lg">Please complete payment to confirm your spot.</p>
                    @endif
                </div>

                {{-- Booking Summary Card --}}
                @php
                    $isMembershipBooking = $transaction->payment_method === 'membership';
                    $membershipName = $metadata['membership_name'] ?? null;
                @endphp
                <div class="card bg-gradient-to-r {{ $isMembershipBooking ? 'from-success/10 via-success/5' : 'from-primary/10 via-primary/5' }} to-transparent border {{ $isMembershipBooking ? 'border-success/20' : 'border-primary/20' }} mb-6 shadow-lg">
                    <div class="card-body">
                        <div class="flex flex-col md:flex-row md:items-center gap-4">
                            <div class="w-16 h-16 rounded-2xl {{ $isMembershipBooking ? 'bg-success/20' : 'bg-primary/20' }} flex items-center justify-center shrink-0">
                                @if($transaction->type === 'class_booking')
                                    <span class="icon-[tabler--yoga] size-8 {{ $isMembershipBooking ? 'text-success' : 'text-primary' }}"></span>
                                @elseif($transaction->type === 'service_booking')
                                    <span class="icon-[tabler--sparkles] size-8 {{ $isMembershipBooking ? 'text-success' : 'text-primary' }}"></span>
                                @elseif($transaction->type === 'membership_purchase')
                                    <span class="icon-[tabler--id-badge-2] size-8 {{ $isMembershipBooking ? 'text-success' : 'text-primary' }}"></span>
                                @else
                                    <span class="icon-[tabler--calendar-check] size-8 {{ $isMembershipBooking ? 'text-success' : 'text-primary' }}"></span>
                                @endif
                            </div>
                            <div class="flex-1">
                                <h2 class="text-xl font-bold">{{ $metadata['item_name'] ?? 'Your Booking' }}</h2>
                                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-2 text-sm text-base-content/70">
                                    @if(!empty($metadata['item_datetime']))
                                        <span class="flex items-center gap-1.5">
                                            <span class="icon-[tabler--calendar] size-4"></span>
                                            {{ $metadata['item_datetime'] }}
                                        </span>
                                    @endif
                                    @if(!empty($metadata['item_instructor']))
                                        <span class="flex items-center gap-1.5">
                                            <span class="icon-[tabler--user] size-4"></span>
                                            {{ $metadata['item_instructor'] }}
                                        </span>
                                    @endif
                                    @if(!empty($metadata['item_location']))
                                        <span class="flex items-center gap-1.5">
                                            <span class="icon-[tabler--map-pin] size-4"></span>
                                            {{ $metadata['item_location'] }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="text-right md:text-right">
                                @if($isMembershipBooking)
                                    <span class="text-2xl font-bold text-success">Included</span>
                                    <div class="mt-1">
                                        <span class="badge badge-success gap-1">
                                            <span class="icon-[tabler--id-badge-2] size-3"></span>
                                            Membership
                                        </span>
                                    </div>
                                @else
                                    <span class="text-3xl font-bold text-primary">{{ $transaction->formatted_total }}</span>
                                    <div class="mt-1">
                                        <span class="badge {{ $transaction->is_paid ? 'badge-success' : 'badge-warning' }} gap-1">
                                            <span class="icon-[tabler--{{ $transaction->is_paid ? 'check' : 'clock' }}] size-3"></span>
                                            {{ $transaction->is_paid ? 'Paid' : 'Pending' }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        @if($isMembershipBooking && $membershipName)
                        <div class="alert bg-success/10 text-success border-success/20 mt-4">
                            <span class="icon-[tabler--id-badge-2] size-5"></span>
                            <span>This class was booked using your <strong>{{ $membershipName }}</strong> membership.</span>
                        </div>
                        @endif

                        @if(!empty($metadata['is_waitlist']))
                        <div class="alert alert-warning mt-4">
                            <span class="icon-[tabler--clock] size-5"></span>
                            <span>You've been added to the waitlist. We'll notify you when a spot opens up.</span>
                        </div>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    {{-- Transaction Details --}}
                    <div class="card bg-base-100 shadow-xl">
                        <div class="card-body">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-10 h-10 rounded-xl {{ $isMembershipBooking ? 'bg-success/10' : 'bg-primary/10' }} flex items-center justify-center">
                                    <span class="icon-[tabler--{{ $isMembershipBooking ? 'id-badge-2' : 'receipt' }}] size-5 {{ $isMembershipBooking ? 'text-success' : 'text-primary' }}"></span>
                                </div>
                                <h3 class="font-bold text-lg">{{ $isMembershipBooking ? 'Membership Booking' : 'Transaction' }}</h3>
                            </div>

                            <div class="space-y-3 text-sm">
                                <div class="flex justify-between items-center">
                                    <span class="text-base-content/60">Confirmation #</span>
                                    <span class="font-mono text-xs bg-base-200 px-2 py-1 rounded">{{ $transaction->transaction_id }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-base-content/60">Payment</span>
                                    <span class="font-medium flex items-center gap-1">
                                        @if($isMembershipBooking)
                                            <span class="icon-[tabler--id-badge-2] size-4 text-success"></span>
                                        @endif
                                        {{ $transaction->payment_method_label }}
                                    </span>
                                </div>
                                @if($isMembershipBooking && $membershipName)
                                <div class="flex justify-between items-center">
                                    <span class="text-base-content/60">Membership</span>
                                    <span class="font-medium text-success">{{ $membershipName }}</span>
                                </div>
                                @endif
                                <div class="flex justify-between items-center">
                                    <span class="text-base-content/60">Date</span>
                                    <span>{{ $transaction->created_at->format('M j, Y') }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-base-content/60">Time</span>
                                    <span>{{ $transaction->created_at->format('g:i A') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Contact Info --}}
                    <div class="card bg-base-100 shadow-xl">
                        <div class="card-body">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-10 h-10 rounded-xl bg-info/10 flex items-center justify-center">
                                    <span class="icon-[tabler--user] size-5 text-info"></span>
                                </div>
                                <h3 class="font-bold text-lg">Contact</h3>
                            </div>

                            <div class="space-y-3">
                                <div class="flex items-center gap-3">
                                    <span class="icon-[tabler--user] size-5 text-base-content/40"></span>
                                    <span class="font-medium">{{ $transaction->client?->full_name ?? 'N/A' }}</span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="icon-[tabler--mail] size-5 text-base-content/40"></span>
                                    <span>{{ $transaction->client?->email ?? 'N/A' }}</span>
                                </div>
                                @if($transaction->client?->phone)
                                <div class="flex items-center gap-3">
                                    <span class="icon-[tabler--phone] size-5 text-base-content/40"></span>
                                    <span>{{ $transaction->client->phone }}</span>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Manual Payment Instructions --}}
                @if($transaction->payment_method === 'manual' && $transaction->status === 'pending')
                <div class="card bg-gradient-to-r from-warning/10 to-warning/5 border-2 border-warning/30 mb-6 shadow-lg">
                    <div class="card-body">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-xl bg-warning/20 flex items-center justify-center shrink-0">
                                <span class="icon-[tabler--alert-triangle] size-6 text-warning"></span>
                            </div>
                            <div class="flex-1">
                                <h3 class="font-bold text-lg mb-2">Payment Required</h3>
                                <p class="text-base-content/70 mb-4">
                                    Complete your payment using <strong>{{ $transaction->payment_method_label }}</strong> to secure your booking.
                                </p>

                                @if($paymentInstructions)
                                <div class="bg-base-100 rounded-xl p-4 border border-base-200">
                                    <p class="whitespace-pre-line text-sm">{{ $paymentInstructions }}</p>
                                </div>
                                @else
                                <p class="text-sm text-base-content/60">
                                    Please contact the studio for payment details.
                                </p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Email Confirmation --}}
                <div class="card bg-base-100 shadow-lg mb-6">
                    <div class="card-body py-4">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-full bg-success/10 flex items-center justify-center shrink-0">
                                <span class="icon-[tabler--mail-check] size-6 text-success"></span>
                            </div>
                            <div>
                                <h4 class="font-semibold">Confirmation Sent</h4>
                                <p class="text-sm text-base-content/60">
                                    We've sent a confirmation email to <strong>{{ $transaction->client?->email }}</strong>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex flex-col sm:flex-row gap-3 justify-center mb-8">
                    <a href="{{ route('subdomain.home', ['subdomain' => $host->subdomain]) }}" class="btn btn-outline btn-lg gap-2">
                        <span class="icon-[tabler--home] size-5"></span>
                        Back to Home
                    </a>
                    <a href="{{ route('member.portal.booking', ['subdomain' => $host->subdomain]) }}" class="btn btn-primary btn-lg gap-2">
                        <span class="icon-[tabler--plus] size-5"></span>
                        Book Another
                    </a>
                </div>

                {{-- Member Portal CTA --}}
                @if($host->isMemberPortalEnabled())
                <div class="card bg-gradient-to-r from-base-100 to-base-200 border border-base-300 shadow-lg">
                    <div class="card-body text-center py-8">
                        <div class="w-16 h-16 rounded-2xl bg-primary/10 flex items-center justify-center mx-auto mb-4">
                            <span class="icon-[tabler--layout-dashboard] size-8 text-primary"></span>
                        </div>
                        <h3 class="text-xl font-bold mb-2">Manage Your Bookings</h3>
                        <p class="text-base-content/60 mb-4 max-w-md mx-auto">
                            Access your schedule, view payment history, and manage your profile in one place.
                        </p>
                        <a href="{{ route('member.portal.dashboard', ['subdomain' => $host->subdomain]) }}" class="btn btn-primary gap-2">
                            <span class="icon-[tabler--login] size-5"></span>
                            Go to Member Portal
                        </a>
                    </div>
                </div>
                @endif

            </div>
        </div>
    </div>
</div>

<style>
@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.7; }
}
.animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>
@endsection
