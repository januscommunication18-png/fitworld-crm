{{--
    Form Validation Component

    Adds inline validation to any form. Wrap your form content with this component.
    It auto-discovers all required fields and validates on blur/input/submit.

    Usage:
    <x-form-validate action="/submit" method="POST">
        <div>
            <label class="label-text">Name <span class="text-error">*</span></label>
            <input type="text" name="name" class="input w-full" required minlength="2">
            <span class="error-message text-error text-sm mt-1 hidden">Please enter a name</span>
        </div>
        <button type="submit" class="btn btn-primary">Save</button>
    </x-form-validate>

    With file upload:
    <x-form-validate action="/submit" method="POST" :has-files="true">
        ...
    </x-form-validate>11

    With PUT method:
    <x-form-validate action="/submit" method="PUT">
        ...
    </x-form-validate>

    Validation rules (via HTML attributes):
    - required          — field must have a value
    - minlength="2"     — minimum character length
    - maxlength="255"   — maximum character length
    - min="1"           — minimum number value
    - max="500"         — maximum number value
    - pattern="..."     — regex pattern
    - type="email"      — email format
    - type="url"        — URL format

    Error messages:
    Add a sibling element with class="error-message" and class="hidden".
    If no error-message element exists, the field just gets a red border.

    Props:
    - action:    Form action URL (required)
    - method:    HTTP method — GET, POST, PUT, PATCH, DELETE (default: POST)
    - has-files: Enable multipart/form-data (default: false)
    - class:     Additional CSS classes on the form
    - id:        Optional form ID
--}}

@props([
    'action',
    'method' => 'POST',
    'hasFiles' => false,
    'id' => null,
])

@php
    $formMethod = strtoupper($method);
    $htmlMethod = in_array($formMethod, ['GET', 'POST']) ? $formMethod : 'POST';
    $needsSpoofing = !in_array($formMethod, ['GET', 'POST']);
@endphp

<form
    action="{{ $action }}"
    method="{{ $htmlMethod }}"
    {{ $id ? 'id=' . $id : '' }}
    {{ $hasFiles ? 'enctype=multipart/form-data' : '' }}
    {{ $attributes->merge(['class' => '']) }}
    novalidate
    data-validate
>
    @csrf
    @if($needsSpoofing)
        @method($formMethod)
    @endif

    {{ $slot }}
</form>

@once
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('form[data-validate]').forEach(function(form) {

        function getFieldLabel(field) {
            if (field.id) {
                var label = form.querySelector('label[for="' + field.id + '"]');
                if (label) return label.textContent.replace(/\*/, '').trim();
            }
            var div = field.closest('div');
            if (div) {
                var lt = div.querySelector('.label-text');
                if (lt) return lt.textContent.replace(/\*/, '').trim();
            }
            var name = field.getAttribute('name') || 'Field';
            return name.replace(/[_\[\]]/g, ' ').replace(/\b\w/g, function(c) { return c.toUpperCase(); }).trim();
        }

        function buildErrorText(field) {
            var label = getFieldLabel(field);
            var val = field.value ? field.value.trim() : '';

            if (field.hasAttribute('required') && !val) {
                return label + ' is required';
            }
            if (field.type === 'email' && !field.checkValidity()) {
                return 'Please enter a valid email address';
            }
            if (field.type === 'url' && !field.checkValidity()) {
                return 'Please enter a valid URL';
            }
            if (field.hasAttribute('minlength') && val.length < parseInt(field.getAttribute('minlength'))) {
                return label + ' must be at least ' + field.getAttribute('minlength') + ' characters';
            }
            if (field.hasAttribute('maxlength') && val.length > parseInt(field.getAttribute('maxlength'))) {
                return label + ' must be no more than ' + field.getAttribute('maxlength') + ' characters';
            }
            if (field.hasAttribute('min') && parseFloat(val) < parseFloat(field.getAttribute('min'))) {
                return label + ' must be at least ' + field.getAttribute('min');
            }
            if (field.hasAttribute('max') && parseFloat(val) > parseFloat(field.getAttribute('max'))) {
                return label + ' must be no more than ' + field.getAttribute('max');
            }
            if (field.hasAttribute('pattern') && !field.checkValidity()) {
                return label + ' format is invalid';
            }
            return label + ' is invalid';
        }

        // Each field gets its own error <p> keyed by field name/id
        var errorElements = {};

        function getErrorEl(field) {
            var key = field.id || field.name;
            if (errorElements[key]) return errorElements[key];

            // Create a new error element
            var el = document.createElement('p');
            el.className = 'text-error text-sm mt-1';
            el.style.display = 'none';

            // For inputs inside table cells, append error to the td
            var td = field.closest('td');
            if (td) {
                td.appendChild(el);
                errorElements[key] = el;
                return el;
            }

            // For selects with data-select (advance-select), find the outer div that has the label
            var parent = field.closest('div');
            if (field.tagName === 'SELECT' && field.hasAttribute('data-select')) {
                var outer = field.closest('div:has(> .label-text)') || field.closest('div:has(> label)');
                if (outer) parent = outer;
            }

            if (parent) {
                parent.appendChild(el);
            } else {
                field.parentNode.insertBefore(el, field.nextSibling);
            }

            errorElements[key] = el;
            return el;
        }

        function showFieldError(field, text) {
            field.setAttribute('data-has-error', '');
            var el = getErrorEl(field);
            el.textContent = text;
            el.style.display = 'block';
        }

        function clearFieldError(field) {
            field.removeAttribute('data-has-error');
            var el = getErrorEl(field);
            el.textContent = '';
            el.style.display = 'none';
        }

        function validateField(field) {
            var val = field.value ? field.value.trim() : '';
            var isEmpty = !val;
            var isInvalid = !field.checkValidity();

            if ((field.hasAttribute('required') && isEmpty) || (!isEmpty && isInvalid)) {
                showFieldError(field, buildErrorText(field));
                return false;
            }

            clearFieldError(field);
            return true;
        }

        // Hide old hardcoded .error-message spans (we use our own now)
        form.querySelectorAll('.error-message').forEach(function(el) {
            el.style.display = 'none';
        });

        // Submit validation
        form.addEventListener('submit', function(e) {
            var isValid = true;

            form.querySelectorAll('[required]').forEach(function(field) {
                if (!validateField(field)) {
                    isValid = false;
                }
            });

            if (!isValid) {
                e.preventDefault();
                // Scroll to first field with a visible error
                var firstError = form.querySelector('[data-has-error]');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstError.focus();
                }
            }
        });

        // Real-time validation on blur/change
        form.querySelectorAll('input, textarea, select').forEach(function(field) {
            if (!field.hasAttribute('required') && !field.hasAttribute('min') &&
                !field.hasAttribute('minlength') && !field.hasAttribute('pattern')) return;

            var events = field.tagName === 'SELECT' ? ['change'] : ['blur'];
            events.forEach(function(evt) {
                field.addEventListener(evt, function() {
                    validateField(field);
                });
            });

            // Clear error as user types valid input
            if (field.tagName !== 'SELECT') {
                field.addEventListener('input', function() {
                    if (field.hasAttribute('data-has-error') && field.value.trim() && field.checkValidity()) {
                        clearFieldError(field);
                    }
                });
            }
        });
    });
});
</script>
@endpush
@endonce
