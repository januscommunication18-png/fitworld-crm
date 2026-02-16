<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Private Access - {{ config('app.name', 'FitCRM') }}</title>

    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-base-200">
    <div class="min-h-screen flex flex-col items-center justify-center p-4">
        <!-- Logo -->
        <div class="mb-8">
            <a href="/" class="flex items-center gap-2">
                <span class="icon-[tabler--shield-lock] size-10 text-primary"></span>
                <span class="text-xl font-bold text-primary">{{ config('app.name', 'FitCRM') }}</span>
            </a>
        </div>

        <!-- Security Code Card -->
        <div class="card bg-base-100 shadow-xl w-full max-w-md">
            <div class="card-body">
                <div class="text-center mb-6">
                    <div class="size-16 rounded-full bg-primary/10 flex items-center justify-center mx-auto mb-4">
                        <span class="icon-[tabler--lock] size-8 text-primary"></span>
                    </div>
                    <h1 class="text-2xl font-bold text-base-content">Private Access</h1>
                    <p class="text-base-content/60 mt-2">Please enter the security code to access this site.</p>
                </div>

                @if ($errors->any())
                <div class="alert alert-error mb-4">
                    <span class="icon-[tabler--alert-circle] size-5"></span>
                    <span>{{ $errors->first('security_code') }}</span>
                </div>
                @endif

                <form action="{{ route('security-code.verify') }}" method="POST" id="otp-form">
                    @csrf
                    <input type="hidden" name="security_code" id="security_code">

                    <div>
                        <label class="label-text mb-2 block text-center" for="otp-1">Security Code</label>
                        <div class="flex justify-center gap-3">
                            <input
                                type="text"
                                id="otp-1"
                                class="input input-lg w-14 h-14 text-center text-2xl font-semibold @error('security_code') input-error @enderror"
                                maxlength="1"
                                inputmode="numeric"
                                pattern="[0-9]*"
                                autocomplete="off"
                                autofocus
                                data-otp-input
                            >
                            <input
                                type="text"
                                id="otp-2"
                                class="input input-lg w-14 h-14 text-center text-2xl font-semibold @error('security_code') input-error @enderror"
                                maxlength="1"
                                inputmode="numeric"
                                pattern="[0-9]*"
                                autocomplete="off"
                                data-otp-input
                            >
                            <input
                                type="text"
                                id="otp-3"
                                class="input input-lg w-14 h-14 text-center text-2xl font-semibold @error('security_code') input-error @enderror"
                                maxlength="1"
                                inputmode="numeric"
                                pattern="[0-9]*"
                                autocomplete="off"
                                data-otp-input
                            >
                            <input
                                type="text"
                                id="otp-4"
                                class="input input-lg w-14 h-14 text-center text-2xl font-semibold @error('security_code') input-error @enderror"
                                maxlength="1"
                                inputmode="numeric"
                                pattern="[0-9]*"
                                autocomplete="off"
                                data-otp-input
                            >
                        </div>
                    </div>

                    <div class="mt-6">
                        <button type="submit" class="btn btn-primary w-full">
                            <span class="icon-[tabler--lock-open] size-5"></span>
                            Verify Access
                        </button>
                    </div>
                </form>

                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const inputs = document.querySelectorAll('[data-otp-input]');
                        const form = document.getElementById('otp-form');
                        const hiddenInput = document.getElementById('security_code');

                        function updateHiddenInput() {
                            let code = '';
                            inputs.forEach(input => {
                                code += input.value;
                            });
                            hiddenInput.value = code;
                        }

                        inputs.forEach((input, index) => {
                            // Handle input
                            input.addEventListener('input', function(e) {
                                // Only allow digits
                                this.value = this.value.replace(/[^0-9]/g, '');

                                updateHiddenInput();

                                // Auto-advance to next input
                                if (this.value.length === 1 && index < inputs.length - 1) {
                                    inputs[index + 1].focus();
                                }

                                // Auto-submit when all fields are filled
                                if (index === inputs.length - 1 && this.value.length === 1) {
                                    let allFilled = true;
                                    inputs.forEach(inp => {
                                        if (!inp.value) allFilled = false;
                                    });
                                    if (allFilled) {
                                        form.submit();
                                    }
                                }
                            });

                            // Handle backspace
                            input.addEventListener('keydown', function(e) {
                                if (e.key === 'Backspace' && !this.value && index > 0) {
                                    inputs[index - 1].focus();
                                }
                            });

                            // Handle paste
                            input.addEventListener('paste', function(e) {
                                e.preventDefault();
                                const pastedData = e.clipboardData.getData('text').replace(/[^0-9]/g, '');

                                if (pastedData.length > 0) {
                                    for (let i = 0; i < Math.min(pastedData.length, inputs.length); i++) {
                                        inputs[i].value = pastedData[i];
                                    }
                                    updateHiddenInput();

                                    // Focus last filled input or submit if complete
                                    const lastIndex = Math.min(pastedData.length, inputs.length) - 1;
                                    if (lastIndex === inputs.length - 1) {
                                        form.submit();
                                    } else {
                                        inputs[lastIndex + 1].focus();
                                    }
                                }
                            });

                            // Select content on focus
                            input.addEventListener('focus', function() {
                                this.select();
                            });
                        });

                        // Update hidden input on form submit
                        form.addEventListener('submit', function() {
                            updateHiddenInput();
                        });
                    });
                </script>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-8 text-center text-sm text-base-content/50">
            <p>&copy; {{ date('Y') }} {{ config('app.name', 'FitCRM') }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
