@extends('layouts.subdomain')

@section('title', $classPass->name . ' — ' . $host->studio_name)

@section('content')
@php
    $color = $classPass->color ?? '#06b6d4';
    $currencySymbol = \App\Models\MembershipPlan::getCurrencySymbol($selectedCurrency);
    $price = $classPass->getPriceForCurrency($selectedCurrency);
    $reactivationFee = $classPass->getReactivationFeeForCurrency($selectedCurrency);

    $regFee = is_array($classPass->registration_fees ?? null)
        ? ($classPass->registration_fees[$selectedCurrency] ?? null)
        : null;
    $regFee = $regFee ?? ($classPass->registration_fee !== null ? (float) $classPass->registration_fee : null);

    $cancelFee = is_array($classPass->cancellation_fees ?? null)
        ? ($classPass->cancellation_fees[$selectedCurrency] ?? null)
        : null;
    $cancelFee = $cancelFee ?? ($classPass->cancellation_fee !== null ? (float) $classPass->cancellation_fee : null);

    $pricePerClass = ($price !== null && $classPass->class_count > 0) ? $price / $classPass->class_count : null;
    $files = $classPass->file_attachments ?? [];
    $renewalLabels = \App\Models\ClassPass::getRenewalIntervals();
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
        {{-- Left: name, description, what's covered --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="flex items-start gap-4">
                @if($classPass->image_url)
                    <div class="w-16 h-16 rounded-2xl overflow-hidden shrink-0 border border-base-300">
                        <img src="{{ $classPass->image_url }}" alt="{{ $classPass->name }}" class="w-full h-full object-cover">
                    </div>
                @else
                    <div class="w-16 h-16 rounded-2xl flex items-center justify-center shrink-0"
                         style="background-color: {{ $color }}15;">
                        <span class="icon-[tabler--ticket] size-9" style="color: {{ $color }};"></span>
                    </div>
                @endif
                <div class="flex-1 min-w-0">
                    <h1 class="text-3xl md:text-4xl font-bold leading-tight">{{ $classPass->name }}</h1>
                    <div class="flex flex-wrap gap-1.5 mt-2">
                        <span class="badge badge-soft badge-info">{{ $classPass->class_count }} {{ $classPass->class_count == 1 ? 'class' : 'classes' }}</span>
                        @if($classPass->is_recurring)
                            <span class="badge badge-soft badge-secondary">Recurring</span>
                        @endif
                        @if($classPass->covers_all_classes)
                            <span class="badge badge-soft badge-accent">All classes</span>
                        @else
                            <span class="badge badge-ghost">{{ $classPass->eligibility_display }}</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Quick facts --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div class="bg-base-100 border border-base-300 rounded-xl p-3">
                    <div class="flex items-center gap-2 text-base-content/60 text-xs">
                        <span class="icon-[tabler--ticket] size-4"></span> Credits
                    </div>
                    <div class="font-semibold mt-0.5">{{ $classPass->class_count }}</div>
                </div>
                <div class="bg-base-100 border border-base-300 rounded-xl p-3">
                    <div class="flex items-center gap-2 text-base-content/60 text-xs">
                        <span class="icon-[tabler--clock] size-4"></span> Validity
                    </div>
                    <div class="font-semibold mt-0.5">{{ $classPass->formatted_validity }}</div>
                </div>
                @if($pricePerClass !== null)
                    <div class="bg-base-100 border border-base-300 rounded-xl p-3">
                        <div class="flex items-center gap-2 text-base-content/60 text-xs">
                            <span class="icon-[tabler--calculator] size-4"></span> Per class
                        </div>
                        <div class="font-semibold mt-0.5">{{ $currencySymbol }}{{ number_format($pricePerClass, 2) }}</div>
                    </div>
                @endif
                <div class="bg-base-100 border border-base-300 rounded-xl p-3">
                    <div class="flex items-center gap-2 text-base-content/60 text-xs">
                        <span class="icon-[tabler--player-play] size-4"></span> Activation
                    </div>
                    <div class="font-semibold mt-0.5 text-sm">{{ $classPass->activation_type_display }}</div>
                </div>
            </div>

            {{-- Description --}}
            @if($classPass->description)
            <section>
                <h2 class="text-xl font-semibold mb-2">About this pass</h2>
                <div class="prose prose-sm max-w-none text-base-content/80 whitespace-pre-line">{{ $classPass->description }}</div>
            </section>
            @endif

            {{-- What's covered --}}
            @if($classPass->covers_all_classes || $eligibleClassPlans->isNotEmpty() || $eligibleServicePlans->isNotEmpty() || $eligibleInstructors->isNotEmpty() || $eligibleLocations->isNotEmpty() || !empty($classPass->eligible_categories))
            <section>
                <h2 class="text-xl font-semibold mb-3 flex items-center gap-2">
                    <span class="icon-[tabler--checks] size-5 text-base-content/60"></span>
                    What this pass covers
                </h2>
                <div class="bg-base-100 border border-base-300 rounded-xl p-4 space-y-3">
                    @if($classPass->covers_all_classes)
                        <div class="flex items-center gap-2 text-sm">
                            <span class="icon-[tabler--circle-check] size-5 text-success shrink-0"></span>
                            <span>{{ $classPass->eligibility_type === \App\Models\ClassPass::ELIGIBILITY_ALL_CLASSES_AND_SERVICES ? 'All classes and services' : 'All classes' }}</span>
                        </div>
                    @else
                        @if($eligibleClassPlans->isNotEmpty())
                            <div>
                                <div class="text-xs uppercase tracking-wider text-base-content/50 mb-1.5">Classes</div>
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach($eligibleClassPlans as $plan)
                                        <span class="badge badge-soft badge-primary">{{ $plan->name }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        @if($eligibleServicePlans->isNotEmpty())
                            <div>
                                <div class="text-xs uppercase tracking-wider text-base-content/50 mb-1.5">Services</div>
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach($eligibleServicePlans as $plan)
                                        <span class="badge badge-soft badge-accent">{{ $plan->name }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        @if(!empty($classPass->eligible_categories))
                            <div>
                                <div class="text-xs uppercase tracking-wider text-base-content/50 mb-1.5">Categories</div>
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach($classPass->eligible_categories as $category)
                                        <span class="badge badge-ghost">{{ $category }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        @if($eligibleInstructors->isNotEmpty())
                            <div>
                                <div class="text-xs uppercase tracking-wider text-base-content/50 mb-1.5">Instructors</div>
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach($eligibleInstructors as $instructor)
                                        <span class="badge badge-soft badge-secondary">{{ $instructor->name }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
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
                    @endif
                </div>
            </section>
            @endif

            {{-- Perks --}}
            @if($classPass->allow_freeze || $classPass->allow_transfer || $classPass->allow_family_sharing || $classPass->allow_gifting || $classPass->rollover_enabled)
            <section>
                <h2 class="text-xl font-semibold mb-3 flex items-center gap-2">
                    <span class="icon-[tabler--gift] size-5 text-base-content/60"></span>
                    Perks & flexibility
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @if($classPass->allow_freeze)
                        <div class="flex items-start gap-2.5 bg-base-100 border border-base-300 rounded-xl p-3">
                            <span class="icon-[tabler--snowflake] size-5 text-info shrink-0 mt-0.5"></span>
                            <div>
                                <div class="font-medium text-sm">Freeze available</div>
                                <div class="text-xs text-base-content/60">Pause for up to {{ $classPass->max_freeze_days ?? 0 }} days</div>
                            </div>
                        </div>
                    @endif
                    @if($classPass->rollover_enabled)
                        <div class="flex items-start gap-2.5 bg-base-100 border border-base-300 rounded-xl p-3">
                            <span class="icon-[tabler--refresh] size-5 text-info shrink-0 mt-0.5"></span>
                            <div>
                                <div class="font-medium text-sm">Credit rollover</div>
                                <div class="text-xs text-base-content/60">@if($classPass->max_rollover_credits) Up to {{ $classPass->max_rollover_credits }} credits roll over @else Unused credits roll over @endif</div>
                            </div>
                        </div>
                    @endif
                    @if($classPass->allow_transfer)
                        <div class="flex items-start gap-2.5 bg-base-100 border border-base-300 rounded-xl p-3">
                            <span class="icon-[tabler--transfer] size-5 text-info shrink-0 mt-0.5"></span>
                            <div>
                                <div class="font-medium text-sm">Transferable</div>
                                <div class="text-xs text-base-content/60">Transfer credits to another member</div>
                            </div>
                        </div>
                    @endif
                    @if($classPass->allow_family_sharing)
                        <div class="flex items-start gap-2.5 bg-base-100 border border-base-300 rounded-xl p-3">
                            <span class="icon-[tabler--users-group] size-5 text-info shrink-0 mt-0.5"></span>
                            <div>
                                <div class="font-medium text-sm">Family sharing</div>
                                <div class="text-xs text-base-content/60">Share with up to {{ $classPass->max_family_members ?? 0 }} family members</div>
                            </div>
                        </div>
                    @endif
                    @if($classPass->allow_gifting)
                        <div class="flex items-start gap-2.5 bg-base-100 border border-base-300 rounded-xl p-3">
                            <span class="icon-[tabler--gift] size-5 text-info shrink-0 mt-0.5"></span>
                            <div>
                                <div class="font-medium text-sm">Giftable</div>
                                <div class="text-xs text-base-content/60">Buy this pass as a gift</div>
                            </div>
                        </div>
                    @endif
                </div>
            </section>
            @endif

            {{-- Policies & fees --}}
            @if($regFee !== null || ($cancelFee !== null && $cancelFee > 0) || $classPass->cancellation_grace_hours !== null || ($reactivationFee !== null && $reactivationFee > 0))
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
                                <div class="text-xs text-base-content/60 mt-0.5">One-time fee on first purchase</div>
                            </div>
                            <div class="text-lg font-semibold" style="color: {{ $color }};">
                                {{ $currencySymbol }}{{ number_format((float) $regFee, 2) }}
                            </div>
                        </div>
                    @endif
                    @if($classPass->cancellation_grace_hours !== null)
                        <div class="flex items-center justify-between p-4">
                            <div>
                                <div class="font-medium">Free cancellation window</div>
                                <div class="text-xs text-base-content/60 mt-0.5">Cancel a booking without losing a credit</div>
                            </div>
                            <div class="font-semibold">{{ $classPass->cancellation_grace_hours }} hours</div>
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
                    @if($reactivationFee !== null && $reactivationFee > 0)
                        <div class="flex items-center justify-between p-4">
                            <div>
                                <div class="font-medium">Reactivation fee</div>
                                <div class="text-xs text-base-content/60 mt-0.5">To reactivate an expired pass</div>
                            </div>
                            <div class="text-lg font-semibold text-warning">
                                {{ $currencySymbol }}{{ number_format((float) $reactivationFee, 2) }}
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

        {{-- Right: price + buy card --}}
        <aside class="lg:col-span-1">
            <div class="bg-base-100 border border-base-300 rounded-2xl p-5 sticky top-6 space-y-4">
                @if($price !== null)
                    <div>
                        <div class="text-3xl font-bold" style="color: {{ $color }};">{{ $currencySymbol }}{{ number_format($price, 0) }}</div>
                        <div class="text-xs uppercase tracking-wider text-base-content/50 mt-1">
                            {{ $classPass->class_count }} {{ $classPass->class_count == 1 ? 'class' : 'classes' }}
                            @if($pricePerClass !== null) · {{ $currencySymbol }}{{ number_format($pricePerClass, 2) }}/class @endif
                        </div>
                    </div>
                @endif

                <div class="space-y-2 text-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-base-content/60">Validity</span>
                        <span class="font-medium">{{ $classPass->formatted_validity }}</span>
                    </div>
                    @if($classPass->is_recurring && $classPass->renewal_interval)
                        <div class="flex items-center justify-between">
                            <span class="text-base-content/60">Renews</span>
                            <span class="font-medium">{{ $renewalLabels[$classPass->renewal_interval] ?? ucfirst($classPass->renewal_interval) }}</span>
                        </div>
                    @endif
                    <div class="flex items-center justify-between">
                        <span class="text-base-content/60">Activation</span>
                        <span class="font-medium text-right">{{ $classPass->activation_type_display }}</span>
                    </div>
                </div>

                <form action="{{ route('booking.select-class-pack', ['subdomain' => $host->subdomain, 'pack' => $classPass->id]) }}" method="POST">
                    @csrf
                    <input type="hidden" name="currency" value="{{ $selectedCurrency }}">
                    <button type="submit" class="btn btn-info btn-block">
                        <span class="icon-[tabler--calendar-plus] size-4"></span>
                        {{ $trans['btn.book_now'] ?? 'Book Now' }}
                    </button>
                </form>

                <a href="{{ route('subdomain.service-request', ['subdomain' => $host->subdomain, 'type' => 'class_pass', 'id' => $classPass->id]) }}"
                   class="btn btn-soft btn-secondary btn-block">
                    <span class="icon-[tabler--info-circle] size-4"></span>
                    {{ $trans['subdomain.service_request.request_info'] ?? 'Request Info' }}
                </a>
            </div>
        </aside>
    </div>
</div>

@endsection
