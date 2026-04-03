@extends('layouts.subdomain')

@section('title', 'Book ' . $classPlan->name . ' — ' . $host->studio_name)

@section('content')
<div class="min-h-screen bg-gradient-to-br from-base-200 via-base-100 to-base-200">
    {{-- Header --}}
    <nav class="bg-base-100/80 backdrop-blur-sm border-b border-base-200 sticky top-0 z-50" style="height: 70px;">
        <div class="container-fixed h-full">
            <div class="flex items-center justify-between h-full">
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

                <a href="{{ route('subdomain.home', ['subdomain' => $host->subdomain]) }}" class="btn btn-ghost btn-sm">
                    <span class="icon-[tabler--arrow-left] size-4"></span> Back
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fixed py-10">
        <div class="max-w-2xl mx-auto">
            {{-- Class Plan Header --}}
            <div class="text-center mb-8">
                <div class="w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-4" style="background-color: {{ $classPlan->color ?? '#6366f1' }}15;">
                    <span class="icon-[tabler--yoga] size-8" style="color: {{ $classPlan->color ?? '#6366f1' }};"></span>
                </div>
                <h1 class="text-2xl font-bold">{{ $classPlan->name }}</h1>
                @if($classPlan->description)
                    <p class="text-base-content/60 mt-2 max-w-md mx-auto">{{ $classPlan->description }}</p>
                @endif
                <div class="flex justify-center gap-2 mt-3">
                    @if($classPlan->category)
                        <span class="badge badge-ghost badge-sm">{{ $classPlan->category }}</span>
                    @endif
                    @if($classPlan->default_duration_minutes)
                        <span class="badge badge-ghost badge-sm"><span class="icon-[tabler--clock] size-3 me-1"></span>{{ $classPlan->formatted_duration }}</span>
                    @endif
                    @if($classPlan->difficulty_level)
                        <span class="badge badge-sm {{ $classPlan->getDifficultyBadgeClass() }}">{{ ucfirst($classPlan->difficulty_level) }}</span>
                    @endif
                </div>
            </div>

            <form id="booking-type-form" action="{{ route('booking.process-class-plan-type', ['subdomain' => $host->subdomain, 'classPlan' => $classPlan->id]) }}" method="POST">
                @csrf

                <h2 class="text-lg font-semibold mb-4">How would you like to book?</h2>

                <div class="space-y-3 mb-6">
                    {{-- Single Class Option --}}
                    <label class="flex items-center gap-4 p-4 bg-base-100 border border-base-200 rounded-xl cursor-pointer hover:border-primary/50 has-[:checked]:border-primary has-[:checked]:bg-primary/5 transition-all shadow-sm">
                        <input type="radio" name="class_booking_type" value="single" class="radio radio-primary" checked>
                        <span class="icon-[tabler--calendar-event] size-6 text-primary shrink-0"></span>
                        <div class="flex-1">
                            <div class="font-semibold">Single Class</div>
                            <p class="text-sm text-base-content/60">Book one session</p>
                        </div>
                        @php $singlePrice = $classPlan->getDropInPriceForCurrency($selectedCurrency) ?? $classPlan->getPriceForCurrency($selectedCurrency); @endphp
                        @if($singlePrice)
                            <span class="text-lg font-bold text-primary">{{ $currencySymbol }}{{ number_format($singlePrice, 0) }}</span>
                        @endif
                    </label>

                    {{-- Series Class Option --}}
                    @if($hasSeriesOption)
                    <label class="flex items-center gap-4 p-4 bg-base-100 border border-base-200 rounded-xl cursor-pointer hover:border-success/50 has-[:checked]:border-success has-[:checked]:bg-success/5 transition-all shadow-sm">
                        <input type="radio" name="class_booking_type" value="series" class="radio radio-success">
                        <span class="icon-[tabler--calendar-repeat] size-6 text-success shrink-0"></span>
                        <div class="flex-1">
                            <div class="font-semibold">Series Class</div>
                            <p class="text-sm text-base-content/60">Prepay for a billing period</p>
                        </div>
                        <span class="badge badge-success badge-sm">Save more</span>
                    </label>
                    @endif

                    {{-- Trial Class Option --}}
                    <label class="flex items-center gap-4 p-4 bg-base-100 border border-base-200 rounded-xl cursor-pointer hover:border-warning/50 has-[:checked]:border-warning has-[:checked]:bg-warning/5 transition-all shadow-sm">
                        <input type="radio" name="class_booking_type" value="trial" class="radio radio-warning">
                        <span class="icon-[tabler--star] size-6 text-warning shrink-0"></span>
                        <div class="flex-1">
                            <div class="font-semibold">Trial Class</div>
                            <p class="text-sm text-base-content/60">Try your first class free</p>
                        </div>
                        <span class="badge badge-warning badge-sm">Free</span>
                    </label>
                </div>

                {{-- Single Class — Session Picker --}}
                <div id="section-single" class="mb-6">
                    @if($upcomingSessions->isEmpty())
                        <div class="text-center py-8 bg-base-100 rounded-xl border border-base-200">
                            <span class="icon-[tabler--calendar-off] size-10 text-base-content/20"></span>
                            <p class="text-base-content/60 mt-2">No upcoming sessions available for this class.</p>
                        </div>
                    @else
                        <h3 class="text-sm font-medium text-base-content/70 mb-3">Select a session</h3>
                        <div class="space-y-2 max-h-80 overflow-y-auto">
                            @foreach($upcomingSessions as $session)
                            <label class="flex items-center gap-3 p-3 bg-base-100 border border-base-200 rounded-lg cursor-pointer hover:border-primary/50 has-[:checked]:border-primary has-[:checked]:bg-primary/5 transition-all">
                                <input type="radio" name="session_id" value="{{ $session->id }}" class="radio radio-primary radio-sm">
                                <div class="flex flex-col items-center justify-center w-10 h-10 rounded-lg bg-primary/10 text-primary shrink-0">
                                    <span class="text-[9px] font-semibold uppercase leading-none">{{ $session->start_time->format('M') }}</span>
                                    <span class="text-base font-bold leading-none">{{ $session->start_time->format('j') }}</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="font-medium text-sm">{{ $session->start_time->format('l, M j') }}</div>
                                    <div class="text-xs text-base-content/60">
                                        {{ $session->start_time->format('g:i A') }} - {{ $session->end_time->format('g:i A') }}
                                        @if($session->primaryInstructor)
                                            &bull; {{ $session->primaryInstructor->name }}
                                        @endif
                                    </div>
                                </div>
                                @php $spots = $session->capacity - ($session->confirmed_bookings_count ?? 0); @endphp
                                @if($spots <= 3 && $spots > 0)
                                    <span class="badge badge-warning badge-xs">{{ $spots }} left</span>
                                @endif
                            </label>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Series Class — Billing Period Picker --}}
                @if($hasSeriesOption)
                <div id="section-series" class="mb-6 hidden">
                    <h3 class="text-sm font-medium text-base-content/70 mb-3">Select a billing period</h3>
                    @php
                        $basePrice = $classPlan->getPriceForCurrency($selectedCurrency) ?? 0;
                        $periods = ['1' => '1 Month', '3' => '3 Months', '6' => '6 Months', '9' => '9 Months', '12' => '12 Months'];
                    @endphp
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        @foreach($periods as $months => $label)
                            @php
                                $periodTotal = floatval($billingDiscounts[$months] ?? 0);
                                if ($periodTotal <= 0) continue;
                                $m = (int) $months;
                                $monthlyRate = $m > 0 ? $periodTotal / $m : 0;
                                $totalWithout = $basePrice * $m;
                                $savings = $totalWithout - $periodTotal;
                            @endphp
                            <label class="flex flex-col items-center p-4 bg-base-100 border border-base-200 rounded-xl cursor-pointer hover:border-success/50 has-[:checked]:border-success has-[:checked]:bg-success/5 transition-all text-center">
                                <input type="radio" name="billing_period" value="{{ $months }}" class="radio radio-success radio-sm mb-2">
                                <div class="text-sm font-medium text-base-content/70">{{ $label }}</div>
                                <div class="text-xl font-bold text-success mt-1">{{ $currencySymbol }}{{ number_format($periodTotal, 0) }}</div>
                                <div class="text-xs text-base-content/50">{{ $currencySymbol }}{{ number_format($monthlyRate, 2) }}/mo</div>
                                @if($savings > 0)
                                    <div class="text-xs text-success mt-1">Save {{ $currencySymbol }}{{ number_format($savings, 0) }}</div>
                                @endif
                            </label>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Trial Class — Info --}}
                <div id="section-trial" class="mb-6 hidden">
                    <div class="flex items-center gap-4 p-4 bg-warning/5 border border-warning/20 rounded-xl">
                        <span class="icon-[tabler--gift] size-8 text-warning"></span>
                        <div>
                            <p class="font-medium">Your first class is on us!</p>
                            <p class="text-sm text-base-content/60">No payment required. Just fill in your details on the next step.</p>
                        </div>
                    </div>
                </div>

                {{-- Submit --}}
                <button type="submit" id="continue-btn" class="btn btn-primary w-full btn-lg">
                    Continue <span class="icon-[tabler--arrow-right] size-5 ms-1"></span>
                </button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var radios = document.querySelectorAll('input[name="class_booking_type"]');
    var singleSection = document.getElementById('section-single');
    var seriesSection = document.getElementById('section-series');
    var trialSection = document.getElementById('section-trial');

    function updateSections() {
        var selected = document.querySelector('input[name="class_booking_type"]:checked').value;

        singleSection.classList.toggle('hidden', selected !== 'single');
        if (seriesSection) seriesSection.classList.toggle('hidden', selected !== 'series');
        trialSection.classList.toggle('hidden', selected !== 'trial');
    }

    radios.forEach(function(radio) {
        radio.addEventListener('change', updateSections);
    });

    updateSections();
});
</script>
@endpush
@endsection
