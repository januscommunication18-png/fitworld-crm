@extends('layouts.subdomain')

@section('title', $membershipPlan->name . ' — ' . $host->studio_name)

@section('content')
@php
    $color = $membershipPlan->color ?? '#16a34a';
    $currencySymbol = \App\Models\MembershipPlan::getCurrencySymbol($selectedCurrency);
    $price = $membershipPlan->getPriceForCurrency($selectedCurrency);
    $hasPrice = $membershipPlan->hasPriceForCurrency($selectedCurrency) || $price !== null;
    $intervalLabel = $membershipPlan->interval === \App\Models\MembershipPlan::INTERVAL_MONTHLY ? 'month' : 'year';

    $regFee = is_array($membershipPlan->registration_fees ?? null)
        ? ($membershipPlan->registration_fees[$selectedCurrency] ?? null)
        : null;
    $regFee = $regFee ?? ($membershipPlan->registration_fee !== null ? (float) $membershipPlan->registration_fee : null);

    $cancelFee = is_array($membershipPlan->cancellation_fees ?? null)
        ? ($membershipPlan->cancellation_fees[$selectedCurrency] ?? null)
        : null;
    $cancelFee = $cancelFee ?? ($membershipPlan->cancellation_fee !== null ? (float) $membershipPlan->cancellation_fee : null);

    $files = $membershipPlan->file_attachments ?? [];
    $amenities = $membershipPlan->free_amenities ?? [];

    // Billing periods (prepay options) — keyed by months in billing_discounts.
    $billingPeriods = ['3' => '3 Months', '6' => '6 Months', '9' => '9 Months', '12' => '12 Months'];
@endphp

@include('subdomain.partials.navbar')

<div class="container-fixed py-8 max-w-6xl">

    {{-- Breadcrumb / back --}}
    <a href="{{ route('subdomain.home', ['subdomain' => $host->subdomain]) }}"
       class="inline-flex items-center gap-1.5 text-sm text-base-content/60 hover:text-primary mb-6">
        <span class="icon-[tabler--arrow-left] size-4"></span>
        {{ $trans['btn.back'] ?? 'Back to home' }}
    </a>

    {{-- Hero --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
        {{-- Left: name, description, what's included --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="flex items-start gap-4">
                @if($membershipPlan->image_url)
                    <div class="w-16 h-16 rounded-2xl overflow-hidden shrink-0 border border-base-300">
                        <img src="{{ $membershipPlan->image_url }}" alt="{{ $membershipPlan->name }}" class="w-full h-full object-cover">
                    </div>
                @else
                    <div class="w-16 h-16 rounded-2xl flex items-center justify-center shrink-0"
                         style="background-color: {{ $color }}15;">
                        <span class="icon-[tabler--id-badge-2] size-9" style="color: {{ $color }};"></span>
                    </div>
                @endif
                <div class="flex-1 min-w-0">
                    <h1 class="text-3xl md:text-4xl font-bold leading-tight">{{ $membershipPlan->name }}</h1>
                    <div class="flex flex-wrap gap-1.5 mt-2">
                        <span class="badge badge-soft badge-success">Membership</span>
                        @if($membershipPlan->isUnlimited())
                            <span class="badge badge-soft badge-accent">Unlimited</span>
                        @else
                            <span class="badge badge-soft badge-info">{{ $membershipPlan->credits_per_cycle }} classes / {{ $intervalLabel }}</span>
                        @endif
                        @if($membershipPlan->coversAllClasses())
                            <span class="badge badge-ghost">All classes</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Quick facts --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div class="bg-base-100 border border-base-300 rounded-xl p-3">
                    <div class="flex items-center gap-2 text-base-content/60 text-xs">
                        <span class="icon-[tabler--ticket] size-4"></span> Classes
                    </div>
                    <div class="font-semibold mt-0.5">{{ $membershipPlan->isUnlimited() ? 'Unlimited' : $membershipPlan->credits_per_cycle . ' / ' . $intervalLabel }}</div>
                </div>
                <div class="bg-base-100 border border-base-300 rounded-xl p-3">
                    <div class="flex items-center gap-2 text-base-content/60 text-xs">
                        <span class="icon-[tabler--calendar-repeat] size-4"></span> Billing
                    </div>
                    <div class="font-semibold mt-0.5 capitalize">{{ $membershipPlan->interval }}</div>
                </div>
                @if($membershipPlan->addon_members > 0)
                    <div class="bg-base-100 border border-base-300 rounded-xl p-3">
                        <div class="flex items-center gap-2 text-base-content/60 text-xs">
                            <span class="icon-[tabler--users-plus] size-4"></span> Guests
                        </div>
                        <div class="font-semibold mt-0.5">+{{ $membershipPlan->addon_members }}</div>
                    </div>
                @endif
                @if($membershipPlan->cancellation_grace_hours)
                    <div class="bg-base-100 border border-base-300 rounded-xl p-3">
                        <div class="flex items-center gap-2 text-base-content/60 text-xs">
                            <span class="icon-[tabler--shield-check] size-4"></span> Free cancel
                        </div>
                        <div class="font-semibold mt-0.5">{{ $membershipPlan->cancellation_grace_hours }}h before</div>
                    </div>
                @endif
            </div>

            {{-- Description --}}
            @if($membershipPlan->description)
            <section>
                <h2 class="text-xl font-semibold mb-2">About this membership</h2>
                <div class="prose prose-sm max-w-none text-base-content/80 whitespace-pre-line">{{ $membershipPlan->description }}</div>
            </section>
            @endif

            {{-- Billing periods (prepay options) --}}
            @php
                $periodCards = [];
                foreach ($billingPeriods as $months => $label) {
                    $pp = $membershipPlan->billing_discounts[$months] ?? null;
                    $total = is_array($pp) ? (float) ($pp[$selectedCurrency] ?? 0) : (float) ($pp ?? 0);
                    if ($total > 0) {
                        $periodCards[] = ['months' => (int) $months, 'label' => $label, 'total' => $total];
                    }
                }
            @endphp
            @if(count($periodCards) > 0)
            <section>
                <h2 class="text-xl font-semibold mb-3 flex items-center gap-2">
                    <span class="icon-[tabler--calendar-repeat] size-5 text-base-content/60"></span>
                    Prepay & save
                </h2>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                    @foreach($periodCards as $card)
                        @php
                            $monthly = $card['months'] > 0 ? $card['total'] / $card['months'] : 0;
                            $savings = ($price !== null) ? ($price * $card['months']) - $card['total'] : 0;
                        @endphp
                        <div class="bg-base-100 border border-base-300 rounded-xl p-3 text-center">
                            <div class="text-xs text-base-content/60">{{ $card['label'] }}</div>
                            <div class="font-bold text-success mt-1">{{ $currencySymbol }}{{ number_format($card['total'], 0) }}</div>
                            <div class="text-[10px] text-base-content/50 mt-0.5">{{ $currencySymbol }}{{ number_format($monthly, 2) }}/mo</div>
                            @if($savings > 0)
                                <div class="text-[10px] text-success mt-0.5">Save {{ $currencySymbol }}{{ number_format($savings, 0) }}</div>
                            @endif
                        </div>
                    @endforeach
                </div>
                <p class="text-xs text-base-content/50 mt-2">Choose a prepay period at checkout to lock in these rates.</p>
            </section>
            @endif

            {{-- What's included --}}
            @if($membershipPlan->coversAllClasses() || $eligibleClassPlans->isNotEmpty() || $eligibleLocations->isNotEmpty() || !empty($amenities) || $freeRentals->isNotEmpty())
            <section>
                <h2 class="text-xl font-semibold mb-3 flex items-center gap-2">
                    <span class="icon-[tabler--checks] size-5 text-base-content/60"></span>
                    What's included
                </h2>
                <div class="bg-base-100 border border-base-300 rounded-xl p-4 space-y-3">
                    {{-- Classes --}}
                    @if($membershipPlan->coversAllClasses())
                        <div class="flex items-center gap-2 text-sm">
                            <span class="icon-[tabler--circle-check] size-5 text-success shrink-0"></span>
                            <span>Access to all classes</span>
                        </div>
                    @elseif($eligibleClassPlans->isNotEmpty())
                        <div>
                            <div class="text-xs uppercase tracking-wider text-base-content/50 mb-1.5">Classes</div>
                            <div class="flex flex-wrap gap-1.5">
                                @foreach($eligibleClassPlans as $plan)
                                    <span class="badge badge-soft badge-primary">{{ $plan->name }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Locations --}}
                    @if($eligibleLocations->isNotEmpty())
                        <div>
                            <div class="text-xs uppercase tracking-wider text-base-content/50 mb-1.5">Locations</div>
                            <div class="flex flex-wrap gap-1.5">
                                @foreach($eligibleLocations as $location)
                                    <span class="badge badge-ghost">{{ $location->name }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Amenities --}}
                    @if(!empty($amenities))
                        <div>
                            <div class="text-xs uppercase tracking-wider text-base-content/50 mb-1.5">Amenities</div>
                            <div class="flex flex-wrap gap-1.5">
                                @foreach($amenities as $amenity)
                                    <span class="badge badge-soft badge-accent">{{ $amenity }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Free rentals --}}
                    @if($freeRentals->isNotEmpty())
                        <div>
                            <div class="text-xs uppercase tracking-wider text-base-content/50 mb-1.5">Included rentals</div>
                            <div class="flex flex-wrap gap-1.5">
                                @foreach($freeRentals as $rental)
                                    <span class="badge badge-ghost">{{ $rental->name }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Guests --}}
                    @if($membershipPlan->addon_members > 0)
                        <div class="flex items-center gap-2 text-sm">
                            <span class="icon-[tabler--users-plus] size-5 text-primary shrink-0"></span>
                            <span>Bring up to {{ $membershipPlan->addon_members }} {{ \Illuminate\Support\Str::plural('guest', $membershipPlan->addon_members) }}</span>
                        </div>
                    @endif
                </div>
            </section>
            @endif

            {{-- Policies & fees --}}
            @if(($regFee !== null && $regFee > 0) || ($cancelFee !== null && $cancelFee > 0) || $membershipPlan->cancellation_grace_hours !== null)
            <section>
                <h2 class="text-xl font-semibold mb-3 flex items-center gap-2">
                    <span class="icon-[tabler--shield-check] size-5 text-base-content/60"></span>
                    Policies & fees
                </h2>
                <div class="bg-base-100 border border-base-300 rounded-xl divide-y divide-base-300">
                    @if($regFee !== null && $regFee > 0)
                        <div class="flex items-center justify-between p-4">
                            <div>
                                <div class="font-medium">Registration fee</div>
                                <div class="text-xs text-base-content/60 mt-0.5">One-time fee when you join</div>
                            </div>
                            <div class="text-lg font-semibold" style="color: {{ $color }};">
                                {{ $currencySymbol }}{{ number_format((float) $regFee, 2) }}
                            </div>
                        </div>
                    @endif
                    @if($membershipPlan->cancellation_grace_hours !== null)
                        <div class="flex items-center justify-between p-4">
                            <div>
                                <div class="font-medium">Free cancellation window</div>
                                <div class="text-xs text-base-content/60 mt-0.5">Cancel a booking without penalty</div>
                            </div>
                            <div class="font-semibold">{{ $membershipPlan->cancellation_grace_hours }} hours</div>
                        </div>
                    @endif
                    @if($cancelFee !== null && $cancelFee > 0)
                        <div class="flex items-center justify-between p-4">
                            <div>
                                <div class="font-medium">Late cancellation fee</div>
                                <div class="text-xs text-base-content/60 mt-0.5">Charged inside the cancellation window</div>
                            </div>
                            <div class="text-lg font-semibold text-warning">
                                {{ $currencySymbol }}{{ number_format((float) $cancelFee, 2) }}
                            </div>
                        </div>
                    @endif
                </div>
            </section>
            @endif

            {{-- File attachments --}}
            @if(!empty($files))
            @php
                $fileIcons = [
                    'pdf' => 'icon-[tabler--file-type-pdf]',
                    'doc' => 'icon-[tabler--file-type-doc]',
                    'docx' => 'icon-[tabler--file-type-doc]',
                    'jpg' => 'icon-[tabler--photo]',
                    'jpeg' => 'icon-[tabler--photo]',
                    'png' => 'icon-[tabler--photo]',
                    'webp' => 'icon-[tabler--photo]',
                ];
                $uploadsDisk = config('filesystems.uploads');
            @endphp
            <section>
                <h2 class="text-xl font-semibold mb-3 flex items-center gap-2">
                    <span class="icon-[tabler--paperclip] size-5 text-base-content/60"></span>
                    Resources
                </h2>
                <ul class="bg-base-100 border border-base-300 rounded-xl divide-y divide-base-300">
                    @foreach($files as $file)
                        @php
                            $fileName = is_array($file) ? ($file['name'] ?? $file['original_name'] ?? '') : (string) $file;
                            $filePath = is_array($file) ? ($file['path'] ?? null) : null;
                            $fileUrl = $filePath
                                ? \Illuminate\Support\Facades\Storage::disk($uploadsDisk)->url($filePath)
                                : (is_array($file) ? ($file['url'] ?? null) : null);
                            $fileSize = is_array($file) && isset($file['size']) ? round($file['size'] / 1024 / 1024, 1) : null;
                            $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                            $icon = $fileIcons[$ext] ?? 'icon-[tabler--file]';
                        @endphp
                        @if($fileName && $fileUrl)
                            <li>
                                <a href="{{ $fileUrl }}" target="_blank" rel="noopener" download="{{ $fileName }}"
                                   class="flex items-center gap-3 p-3 hover:bg-base-200/40 transition">
                                    <span class="{{ $icon }} size-5 text-base-content/60 shrink-0"></span>
                                    <div class="flex-1 min-w-0">
                                        <div class="text-sm font-medium truncate">{{ $fileName }}</div>
                                        @if($fileSize)<div class="text-xs text-base-content/50">{{ $fileSize }} MB</div>@endif
                                    </div>
                                    <span class="icon-[tabler--download] size-4 text-base-content/50 shrink-0"></span>
                                </a>
                            </li>
                        @endif
                    @endforeach
                </ul>
            </section>
            @endif
        </div>

        {{-- Right: price + join card --}}
        <aside class="lg:col-span-1">
            <div class="bg-base-100 border border-base-300 rounded-2xl p-5 sticky top-6 space-y-4">
                @if($hasPrice && $price !== null)
                    <div>
                        <div class="flex items-baseline gap-1">
                            <span class="text-3xl font-bold" style="color: {{ $color }};">{{ $currencySymbol }}{{ number_format($price, 0) }}</span>
                            <span class="text-sm text-base-content/50">/ {{ $intervalLabel }}</span>
                        </div>
                        <div class="text-xs uppercase tracking-wider text-base-content/50 mt-1">
                            {{ $membershipPlan->isUnlimited() ? 'Unlimited classes' : $membershipPlan->credits_per_cycle . ' classes per ' . $intervalLabel }}
                        </div>
                    </div>
                @else
                    <div class="text-sm text-base-content/60">
                        Not available in {{ $selectedCurrency }}.
                    </div>
                @endif

                <div class="space-y-2 text-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-base-content/60">Billing</span>
                        <span class="font-medium capitalize">{{ $membershipPlan->interval }}</span>
                    </div>
                    @if($regFee !== null && $regFee > 0)
                        <div class="flex items-center justify-between">
                            <span class="text-base-content/60">Joining fee</span>
                            <span class="font-medium">{{ $currencySymbol }}{{ number_format((float) $regFee, 2) }}</span>
                        </div>
                    @endif
                </div>

                @if($hasPrice && $price !== null)
                    <form action="{{ route('booking.select-membership-plan', ['subdomain' => $host->subdomain, 'plan' => $membershipPlan->id]) }}" method="POST">
                        @csrf
                        <input type="hidden" name="currency" value="{{ $selectedCurrency }}">
                        <button type="submit" class="btn btn-success btn-block">
                            <span class="icon-[tabler--calendar-plus] size-4"></span>
                            {{ $trans['btn.book_now'] ?? 'Book Now' }}
                        </button>
                    </form>
                @else
                    <button type="button" class="btn btn-disabled btn-block" disabled>
                        {{ $trans['subdomain.home.unavailable'] ?? 'Unavailable' }}
                    </button>
                @endif

                <a href="{{ route('subdomain.service-request', ['subdomain' => $host->subdomain, 'type' => 'membership', 'id' => $membershipPlan->id]) }}"
                   class="btn btn-soft btn-secondary btn-block">
                    <span class="icon-[tabler--info-circle] size-4"></span>
                    {{ $trans['subdomain.service_request.request_info'] ?? 'Request Info' }}
                </a>
            </div>
        </aside>
    </div>
</div>

@endsection
