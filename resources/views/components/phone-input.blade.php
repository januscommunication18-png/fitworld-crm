{{--
    Phone Input Component with Country Code Masking

    Usage:
    <x-phone-input
        name="phone"
        :value="$user->phone"
        label="Phone Number"
        :required="false"
    />

    Props:
    - name: Input field name (default: 'phone')
    - value: Current phone value (default: '')
    - label: Label text (default: 'Phone Number')
    - required: Whether field is required (default: false)
    - id-suffix: Suffix for element IDs to avoid conflicts (default: auto-generated)
--}}

@props([
    'name' => 'phone',
    'value' => '',
    'label' => 'Phone Number',
    'required' => false,
    'idSuffix' => null,
])

@php
    $uid = str_replace('-', '_', $idSuffix ?? 'ph_' . substr(uniqid(), -5));
    $host = auth()->user()->currentHost() ?? auth()->user()->host;
    $opCountries = $host->operating_countries ?? [$host->country ?? 'US'];
    $defaultCountry = $host->country ?? ($opCountries[0] ?? 'US');

    $countryData = [
        'US' => ['flag' => '🇺🇸', 'code' => '+1', 'label' => 'US'],
        'CA' => ['flag' => '🇨🇦', 'code' => '+1', 'label' => 'CA'],
        'GB' => ['flag' => '🇬🇧', 'code' => '+44', 'label' => 'UK'],
        'DE' => ['flag' => '🇩🇪', 'code' => '+49', 'label' => 'DE'],
        'AU' => ['flag' => '🇦🇺', 'code' => '+61', 'label' => 'AU'],
        'IN' => ['flag' => '🇮🇳', 'code' => '+91', 'label' => 'IN'],
    ];
@endphp

<div>
    @if($label)
        <label class="label-text" for="phone_input_{{ $uid }}">{{ $label }} @if($required)<span class="text-error">*</span>@endif</label>
    @endif
    <div class="join w-full">
        <select id="phone_country_{{ $uid }}" class="select join-item w-auto min-w-[120px]" onchange="PhoneInput_{{ $uid }}.onCountryChange()">
            @foreach($opCountries as $cc)
                <option value="{{ $cc }}" {{ $cc === $defaultCountry ? 'selected' : '' }}>
                    {{ $countryData[$cc]['flag'] ?? '' }} {{ $countryData[$cc]['code'] ?? '' }} {{ $countryData[$cc]['label'] ?? $cc }}
                </option>
            @endforeach
        </select>
        <input
            type="tel"
            id="phone_input_{{ $uid }}"
            name="{{ $name }}"
            value="{{ $value }}"
            class="input join-item flex-1"
            placeholder=""
            {{ $required ? 'required' : '' }}
            oninput="PhoneInput_{{ $uid }}.mask(this)"
        />
    </div>
</div>

<script>
var PhoneInput_{{ $uid }} = (function() {
    var masks = {
        US: { code: '+1', placeholder: '(555) 123-4567', format: function(d) { var f = ''; if (d.length > 0) f += '(' + d.substring(0,3); if (d.length >= 3) f += ') '; if (d.length > 3) f += d.substring(3,6); if (d.length > 6) f += '-' + d.substring(6,10); return f; }, max: 10 },
        CA: { code: '+1', placeholder: '(555) 123-4567', format: function(d) { var f = ''; if (d.length > 0) f += '(' + d.substring(0,3); if (d.length >= 3) f += ') '; if (d.length > 3) f += d.substring(3,6); if (d.length > 6) f += '-' + d.substring(6,10); return f; }, max: 10 },
        GB: { code: '+44', placeholder: '7911 123456', format: function(d) { var f = ''; if (d.length > 0) f += d.substring(0,4); if (d.length > 4) f += ' ' + d.substring(4,10); return f; }, max: 10 },
        DE: { code: '+49', placeholder: '151 12345678', format: function(d) { var f = ''; if (d.length > 0) f += d.substring(0,3); if (d.length > 3) f += ' ' + d.substring(3,11); return f; }, max: 11 },
        AU: { code: '+61', placeholder: '412 345 678', format: function(d) { var f = ''; if (d.length > 0) f += d.substring(0,3); if (d.length > 3) f += ' ' + d.substring(3,6); if (d.length > 6) f += ' ' + d.substring(6,9); return f; }, max: 9 },
        IN: { code: '+91', placeholder: '98765 43210', format: function(d) { var f = ''; if (d.length > 0) f += d.substring(0,5); if (d.length > 5) f += ' ' + d.substring(5,10); return f; }, max: 10 },
    };

    function getSelect() { return document.getElementById('phone_country_{{ $uid }}'); }
    function getInput() { return document.getElementById('phone_input_{{ $uid }}'); }
    function getActiveMask() {
        var sel = getSelect();
        return masks[sel ? sel.value : 'US'] || masks['US'];
    }

    function extractLocalDigits(phone) {
        var digits = phone.replace(/\D/g, '');
        var codes = Object.values(masks).map(function(m) { return m.code.replace(/\D/g, ''); });
        codes.sort(function(a, b) { return b.length - a.length; });
        for (var i = 0; i < codes.length; i++) {
            if (digits.startsWith(codes[i])) {
                digits = digits.substring(codes[i].length);
                break;
            }
        }
        return digits;
    }

    function maskInput(input) {
        var m = getActiveMask();
        var raw = input.value.replace(/\D/g, '').substring(0, m.max);
        input.value = raw.length > 0 ? m.format(raw) : '';
    }

    function onCountryChange() {
        var input = getInput();
        var m = getActiveMask();
        input.placeholder = m.placeholder;
        var raw = input.value.replace(/\D/g, '').substring(0, m.max);
        input.value = raw.length > 0 ? m.format(raw) : '';
    }

    // Get the full phone number with country code (for form submission)
    function getFullNumber() {
        var input = getInput();
        var m = getActiveMask();
        var digits = input.value.replace(/\D/g, '');
        return digits ? m.code + digits : '';
    }

    // Init on DOM ready
    document.addEventListener('DOMContentLoaded', function() {
        var input = getInput();
        if (input) {
            var m = getActiveMask();
            input.placeholder = m.placeholder;
            if (input.value) {
                var local = extractLocalDigits(input.value);
                local = local.substring(0, m.max);
                input.value = local.length > 0 ? m.format(local) : '';
            }
        }
    });

    return {
        mask: maskInput,
        onCountryChange: onCountryChange,
        getFullNumber: getFullNumber,
        getActiveMask: getActiveMask,
        extractLocalDigits: extractLocalDigits,
    };
})();
</script>
