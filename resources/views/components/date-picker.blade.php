{{--
    Date Picker Component (using daterangepicker in single date mode)

    Usage:
    <x-date-picker
        name="expire_date"
        :value="$cert->expire_date"
        label="Expiration Date"
        placeholder="Select date..."
        :required="false"
        :min-date="now()"
        id-suffix="cert"
    />

    Props:
    - name: Input field name (default: 'date')
    - value: Current date value (default: '')
    - label: Label text (default: '')
    - placeholder: Placeholder text (default: 'Select date...')
    - required: Whether field is required (default: false)
    - minDate: Minimum selectable date (default: null)
    - maxDate: Maximum selectable date (default: null)
    - idSuffix: Suffix for element IDs to avoid conflicts (default: auto-generated)
    - format: Display format (default: 'MM/DD/YYYY')
--}}

@props([
    'name' => 'date',
    'value' => '',
    'label' => '',
    'placeholder' => 'Select date...',
    'required' => false,
    'minDate' => null,
    'maxDate' => null,
    'idSuffix' => null,
    'format' => 'MM/DD/YYYY',
])

@php
    $uid = str_replace('-', '_', $idSuffix ?? 'dp_' . substr(uniqid(), -5));
    // Convert date to display format if it's a raw date
    $displayValue = $value;
    if ($value && $value instanceof \Carbon\Carbon) {
        $displayValue = $value->format('m/d/Y');
    } elseif ($value && is_string($value) && strlen($value) === 10) {
        try {
            $displayValue = \Carbon\Carbon::parse($value)->format('m/d/Y');
        } catch (\Exception $e) {
            $displayValue = $value;
        }
    }
@endphp

@once
@push('styles')
<link rel="stylesheet" href="{{ asset('vendor/daterangepicker/daterangepicker.css') }}">
<style>
    .daterangepicker select.yearselect,
    .daterangepicker select.monthselect {
        max-height: 200px;
        overflow-y: auto;
    }
    .daterangepicker select.yearselect option,
    .daterangepicker select.monthselect option {
        padding: 2px 4px;
    }
</style>
@endpush
@push('head')
<script src="{{ asset('vendor/daterangepicker/jquery.min.js') }}"></script>
<script src="{{ asset('vendor/daterangepicker/moment.min.js') }}"></script>
<script src="{{ asset('vendor/daterangepicker/daterangepicker.min.js') }}"></script>
@endpush
@endonce

<div>
    @if($label)
        <label class="label-text font-medium" for="datepicker_{{ $uid }}">{{ $label }} @if($required)<span class="text-error">*</span>@endif</label>
    @endif
    <div class="relative">
        <input
            type="text"
            id="datepicker_{{ $uid }}"
            class="input w-full pr-10 cursor-pointer"
            placeholder="{{ $placeholder }}"
            value="{{ $displayValue }}"
            {{ $required ? 'required' : '' }}
            readonly
        />
        <input type="hidden" id="datepicker_value_{{ $uid }}" name="{{ $name }}" value="{{ $value instanceof \Carbon\Carbon ? $value->format('Y-m-d') : $value }}" />
        <span class="icon-[tabler--calendar] size-5 text-base-content/40 absolute top-1/2 right-3 -translate-y-1/2 pointer-events-none"></span>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof jQuery === 'undefined') return;

    var $input = jQuery('#datepicker_{{ $uid }}');
    var $hidden = jQuery('#datepicker_value_{{ $uid }}');

    var options = {
        singleDatePicker: true,
        showDropdowns: true,
        autoUpdateInput: false,
        minYear: moment().year() - 5,
        maxYear: moment().year() + 10,
        locale: {
            format: '{{ $format }}',
            cancelLabel: 'Clear'
        }
    };

    @if($minDate)
    options.minDate = moment('{{ $minDate instanceof \Carbon\Carbon ? $minDate->format("Y-m-d") : $minDate }}');
    @endif

    @if($maxDate)
    options.maxDate = moment('{{ $maxDate instanceof \Carbon\Carbon ? $maxDate->format("Y-m-d") : $maxDate }}');
    @endif

    // Set start date if value exists
    @if($value)
    options.startDate = moment('{{ $value instanceof \Carbon\Carbon ? $value->format("Y-m-d") : $value }}');
    options.autoUpdateInput = true;
    @endif

    $input.daterangepicker(options);

    $input.on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('{{ $format }}'));
        $hidden.val(picker.startDate.format('YYYY-MM-DD'));
        // Trigger change for drawer change tracking
        this.dispatchEvent(new Event('change', { bubbles: true }));
    });

    $input.on('cancel.daterangepicker', function() {
        $(this).val('');
        $hidden.val('');
        this.dispatchEvent(new Event('change', { bubbles: true }));
    });
});
</script>
