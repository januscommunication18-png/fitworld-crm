{{--
    Studio Pricing Table Component

    Renders a multi-currency pricing table with configurable rows grouped by sections.

    Usage:
    <x-studio-pricing-table :rows="[
        ['section' => 'New Member Pricing', 'section_icon' => 'icon-[tabler--user-plus]', 'section_badge' => 'Public Booking', 'section_bg' => 'bg-info/5'],
        ['name' => 'new_member_prices', 'label' => 'Price', 'values' => $plan->new_member_prices ?? []],
        ['name' => 'new_member_drop_in_prices', 'label' => 'Drop-in Price', 'values' => $plan->new_member_drop_in_prices ?? []],
        ['section' => 'Existing Member Pricing', 'section_icon' => 'icon-[tabler--users]', 'section_bg' => 'bg-base-200/50'],
        ['name' => 'prices', 'label' => 'Price', 'values' => $plan->prices ?? []],
        ['name' => 'drop_in_prices', 'label' => 'Drop-in Price', 'values' => $plan->drop_in_prices ?? []],
    ]" />

    With custom help text:
    <x-studio-pricing-table
        :rows="$rows"
        title="Pricing"
        help="Leave empty for free. New member prices shown on public booking page."
    />

    Props:
    - rows:      Array of row configs (see above). Each row is either a section header or a price row.
    - title:     Card title (default: 'Pricing')
    - help:      Help text below title (default: null)
    - no-card:   Render without card wrapper (default: false)
--}}

@props([
    'rows' => [],
    'title' => 'Pricing',
    'help' => null,
    'noCard' => false,
])

@php
    $studio = $host ?? $currentHost ?? auth()->user()->host;
    $currencies = $studio->currencies ?? [$studio->default_currency ?? 'USD'];
    $defaultCurrency = $studio->default_currency ?? 'USD';
    $symbols = ['USD' => '$', 'CAD' => 'C$', 'GBP' => '£', 'EUR' => '€', 'AUD' => 'A$', 'INR' => '₹'];
@endphp

@if(!$noCard)
<div class="card bg-base-100">
    <div class="card-header">
        <h3 class="card-title">{{ $title }}</h3>
    </div>
    <div class="card-body">
@else
<div>
@endif

    @if($help)
        <p class="text-sm text-base-content/60 mb-4">{{ $help }}</p>
    @endif

    <div class="overflow-x-auto">
        <table class="table table-zebra">
            <thead>
                <tr>
                    <th class="w-48">Price Type</th>
                    @foreach($currencies as $currency)
                        <th class="text-center">
                            {{ $currency }}
                            @if($currency === $defaultCurrency)
                                <span class="badge badge-primary badge-xs ms-1">Default</span>
                            @endif
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $row)
                    @if(isset($row['section']))
                        {{-- Section Header --}}
                        <tr class="{{ $row['section_bg'] ?? 'bg-base-200/50' }}">
                            <td colspan="{{ count($currencies) + 1 }}" class="font-semibold">
                                @if(isset($row['section_icon']))
                                    <span class="{{ $row['section_icon'] }} size-4 me-1 align-middle"></span>
                                @endif
                                {{ $row['section'] }}
                                @if(isset($row['section_badge']))
                                    <span class="badge badge-soft badge-info badge-sm ms-2">{{ $row['section_badge'] }}</span>
                                @endif
                            </td>
                        </tr>
                    @else
                        {{-- Price Row --}}
                        <tr>
                            <td>
                                <label class="label-text">{{ $row['label'] ?? 'Price' }}</label>
                            </td>
                            @foreach($currencies as $currency)
                                <td>
                                    <label class="input input-bordered input-sm flex items-center gap-1">
                                        <span class="text-base-content/60 text-sm">{{ $symbols[$currency] ?? $currency }}</span>
                                        <input type="number"
                                               name="{{ $row['name'] }}[{{ $currency }}]"
                                               step="{{ $row['step'] ?? '0.01' }}"
                                               min="{{ $row['min'] ?? '0' }}"
                                               value="{{ old($row['name'] . '.' . $currency, $row['values'][$currency] ?? '') }}"
                                               class="grow w-full min-w-20"
                                               placeholder="{{ $row['placeholder'] ?? '0.00' }}"
                                               {{ isset($row['required']) && $row['required'] ? 'required' : '' }}>
                                    </label>
                                </td>
                            @endforeach
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>

    {{ $slot ?? '' }}

@if(!$noCard)
    </div>
</div>
@else
</div>
@endif
