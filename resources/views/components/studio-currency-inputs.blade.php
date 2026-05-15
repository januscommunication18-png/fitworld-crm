{{--
    Multi-Currency Amount Input Component

    Renders an amount input for each of the studio's accepted currencies,
    with the default currency tagged.

    Usage:
    <x-currency-amounts
        name="cancellation_fees"
        :values="$policies['cancellation_fees'] ?? []"
    />

    <x-currency-amounts
        name="prices"
        :values="$plan->prices ?? []"
        :required="true"
        label="Price"
        placeholder="0.00"
        help="Set a price for each currency."
        size="sm"
    />

    Props:
    - name:        Input field name. Submitted as name[USD], name[EUR], etc. (required)
    - values:      Associative array of currency => amount (default: [])
    - required:    Whether fields are required (default: false)
    - label:       Optional label text above the inputs
    - help:        Optional help text below the inputs
    - placeholder: Placeholder for inputs (default: '0.00')
    - size:        Input size: 'sm', 'md', 'lg' (default: 'sm')
    - min:         Minimum value (default: '0')
    - step:        Step value (default: '0.01')
--}}

@props([
    'name',
    'values' => [],
    'required' => false,
    'label' => null,
    'help' => null,
    'placeholder' => '0.00',
    'size' => 'sm',
    'min' => '0',
    'step' => '0.01',
])

@php
    $currencySymbols = ['USD' => '$', 'CAD' => 'C$', 'GBP' => '£', 'EUR' => '€', 'AUD' => 'A$', 'INR' => '₹'];
    $studio = $host ?? $currentHost ?? auth()->user()->host;
    $acceptedCurrencies = $studio->currencies ?? [$studio->default_currency ?? 'USD'];
    $defaultCurrency = $studio->default_currency ?? 'USD';
    $inputClass = 'input input-' . $size . ' w-28';
    $btnClass = 'btn btn-' . $size . ' btn-soft pointer-events-none w-20 justify-start font-medium';
@endphp

<div>
    @if($label)
        <label class="label-text mb-2 block">{{ $label }}</label>
    @endif
    <div class="space-y-2">
        @foreach($acceptedCurrencies as $code)
            <div class="flex items-center gap-2">
                <div class="input-group w-fit">
                    <span class="{{ $btnClass }}">{{ $currencySymbols[$code] ?? $code }} {{ $code }}</span>
                    <input
                        name="{{ $name }}[{{ $code }}]"
                        type="number"
                        class="{{ $inputClass }} @error($name . '.' . $code) input-error @enderror"
                        min="{{ $min }}"
                        step="{{ $step }}"
                        placeholder="{{ $placeholder }}"
                        value="{{ old($name . '.' . $code, $values[$code] ?? '') }}"
                        {{ $required ? 'required' : '' }}
                    />
                </div>
                @if($code === $defaultCurrency)
                    <span class="badge badge-primary badge-sm">Default</span>
                @endif
            </div>
            @error($name . '.' . $code)
                <span class="text-error text-xs">{{ $message }}</span>
            @enderror
        @endforeach
    </div>
    @if($help)
        <p class="text-xs text-base-content/60 mt-2">{{ $help }}</p>
    @endif
</div>
