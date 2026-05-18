{{--
    Time Picker Component (using Flatpickr in time-only mode)

    Respects the host's time_format setting (12h or 24h) from studio profile.

    Usage:
    <x-time-picker name="start_time" value="09:00" label="Start Time" />
    <x-time-picker name="end_time" value="17:00" label="End Time" placeholder="Select end time..." />
    <x-time-picker name="break_time" label="Break Time" :required="true" size="sm" />

    Props:
    - name:        Input field name (required)
    - value:       Current time value in H:i format, e.g. '09:00' (default: '')
    - label:       Label text (default: '')
    - placeholder: Placeholder text (default: 'Select time...')
    - required:    Whether field is required (default: false)
    - size:        Input size: 'sm', 'md' (default: 'md')
    - increment:   Minute increment (default: 15)
--}}

@props([
    'name',
    'value' => '',
    'label' => '',
    'placeholder' => 'Select time...',
    'required' => false,
    'size' => 'md',
    'increment' => 15,
])

@php
    $uid = 'tp_' . str_replace(['-', '[', ']', '.'], '_', $name) . '_' . substr(uniqid(), -4);
    $inputClass = $size === 'sm' ? 'input input-sm w-full' : 'input w-full';
    $hostTimeFormat = auth()->user()?->host?->time_format ?? '12h';
@endphp

@once
@push('styles')
<style>
    .flatpickr-calendar { z-index: 9999 !important; }
    .flatpickr-calendar.hasTime.noCalendar { width: auto !important; min-width: 200px; }
    .flatpickr-time { display: flex !important; align-items: center !important; justify-content: center !important; gap: 4px; max-height: none !important; height: auto !important; padding: 10px !important; }
    .flatpickr-time .numInputWrapper { width: 50px !important; height: 40px !important; }
    .flatpickr-time .numInputWrapper input { font-size: 1.25rem !important; }
    .flatpickr-time .flatpickr-time-separator { font-size: 1.25rem !important; line-height: 40px !important; }
    .flatpickr-time .flatpickr-am-pm { width: 50px !important; height: 40px !important; line-height: 40px !important; font-size: 0.875rem !important; }
</style>
@endpush
@endonce

<div>
    @if($label)
        <label class="label-text" for="{{ $uid }}">{{ $label }} @if($required)<span class="text-error">*</span>@endif</label>
    @endif
    <input
        type="text"
        id="{{ $uid }}"
        name="{{ $name }}"
        value="{{ old($name, $value) }}"
        class="{{ $inputClass }}"
        placeholder="{{ $placeholder }}"
        {{ $required ? 'required' : '' }}
        data-time-picker
        data-increment="{{ $increment }}"
    />
    @error($name)
        <p class="text-error text-sm mt-1">{{ $message }}</p>
    @enderror
</div>

@once
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var is24h = {{ $hostTimeFormat === '24h' ? 'true' : 'false' }};
    document.querySelectorAll('[data-time-picker]').forEach(function(el) {
        flatpickr(el, {
            enableTime: true,
            noCalendar: true,
            dateFormat: is24h ? 'H:i' : 'h:i K',
            time_24hr: is24h,
            minuteIncrement: parseInt(el.dataset.increment) || 15,
            allowInput: true,
            appendTo: document.body,
            onReady: function(selectedDates, dateStr, instance) {
                // Convert existing H:i value to display format on load
                if (!is24h && el.value && /^\d{2}:\d{2}$/.test(el.value)) {
                    var parts = el.value.split(':');
                    var h = parseInt(parts[0]);
                    var m = parts[1];
                    var ampm = h >= 12 ? 'PM' : 'AM';
                    h = h % 12 || 12;
                    instance.setDate(el.value, false);
                }
            }
        });

        // Store as H:i in a hidden input for form submission
        if (!is24h) {
            var hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = el.name;
            el.name = '';
            el.insertAdjacentElement('afterend', hidden);

            var fp = el._flatpickr;
            if (fp) {
                // Set hidden value from current selection
                var updateHidden = function() {
                    var d = fp.selectedDates[0];
                    if (d) {
                        var hh = String(d.getHours()).padStart(2, '0');
                        var mm = String(d.getMinutes()).padStart(2, '0');
                        hidden.value = hh + ':' + mm;
                    } else {
                        hidden.value = '';
                    }
                };
                fp.config.onChange.push(updateHidden);
                updateHidden();
            }
        }
    });
});
</script>
@endpush
@endonce
