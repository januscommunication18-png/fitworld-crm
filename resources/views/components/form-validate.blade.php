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
    </x-form-validate>

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

        function findErrorMsg(field) {
            // Look for .error-message in same parent div
            var parent = field.parentElement;
            var msg = parent ? parent.querySelector('.error-message') : null;
            if (!msg) {
                var wrapper = field.closest('div');
                msg = wrapper ? wrapper.querySelector('.error-message') : null;
            }
            return msg;
        }

        function validateField(field) {
            var errorMsg = findErrorMsg(field);
            var isEmpty = !field.value || !field.value.trim();
            var isInvalid = !field.checkValidity();

            if (field.hasAttribute('required') && isEmpty) {
                field.classList.add('is-invalid');
                field.classList.remove('is-valid');
                if (errorMsg) errorMsg.classList.remove('hidden');
                return false;
            }

            if (!isEmpty && isInvalid) {
                field.classList.add('is-invalid');
                field.classList.remove('is-valid');
                if (errorMsg) errorMsg.classList.remove('hidden');
                return false;
            }

            if (!isEmpty) {
                field.classList.remove('is-invalid');
                field.classList.add('is-valid');
            } else {
                field.classList.remove('is-invalid', 'is-valid');
            }
            if (errorMsg) errorMsg.classList.add('hidden');
            return true;
        }

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
                var firstError = form.querySelector('.is-invalid');
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

            // Clear error on input
            if (field.tagName !== 'SELECT') {
                field.addEventListener('input', function() {
                    if (field.classList.contains('is-invalid') && field.value.trim() && field.checkValidity()) {
                        field.classList.remove('is-invalid');
                        field.classList.add('is-valid');
                        var errorMsg = findErrorMsg(field);
                        if (errorMsg) errorMsg.classList.add('hidden');
                    }
                });
            }
        });
    });
});
</script>
@endpush
@endonce
