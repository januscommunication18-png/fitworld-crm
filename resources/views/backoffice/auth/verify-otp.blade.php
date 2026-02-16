@extends('backoffice.layouts.auth')

@section('title', 'Enter Verification Code')

@section('content')
<div class="card bg-base-100">
    <div class="card-body">
        <div class="text-center mb-6">
            <div class="flex items-center justify-center mb-4">
                <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center">
                    <span class="icon-[tabler--mail-check] size-8 text-primary"></span>
                </div>
            </div>
            <h1 class="text-xl font-bold">Enter Verification Code</h1>
            <p class="text-base-content/60 text-sm mt-1">
                We sent a 6-digit code to<br>
                <strong class="text-base-content">{{ $email }}</strong>
            </p>
        </div>

        <form action="{{ route('backoffice.security.verify.submit') }}" method="POST">
            @csrf
            <input type="hidden" name="email" value="{{ $email }}">

            <div class="space-y-4">
                <div>
                    <label class="label-text mb-2 block text-center" for="pin-input">Verification Code</label>
                    <div class="flex justify-center gap-2" data-pin-input='{"availableCharsRE": "^[0-9]+$"}' id="pin-input">
                        <input type="tel" class="pin-input pin-input-lg @error('code') input-error @enderror" data-pin-input-item autofocus />
                        <input type="tel" class="pin-input pin-input-lg @error('code') input-error @enderror" data-pin-input-item />
                        <input type="tel" class="pin-input pin-input-lg @error('code') input-error @enderror" data-pin-input-item />
                        <input type="tel" class="pin-input pin-input-lg @error('code') input-error @enderror" data-pin-input-item />
                        <input type="tel" class="pin-input pin-input-lg @error('code') input-error @enderror" data-pin-input-item />
                        <input type="tel" class="pin-input pin-input-lg @error('code') input-error @enderror" data-pin-input-item />
                    </div>
                    <input type="hidden" id="code" name="code" value="{{ old('code') }}" />
                    @error('code')
                        <p class="text-error text-sm mt-1 text-center">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary w-full">
                    <span class="icon-[tabler--check] size-4"></span>
                    Verify Code
                </button>

                <div class="text-center">
                    <a href="{{ route('backoffice.security') }}" class="text-sm text-primary hover:underline">
                        <span class="icon-[tabler--arrow-left] size-3"></span>
                        Use a different email
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="text-center mt-4">
    <p class="text-base-content/60 text-sm">
        Didn't receive the code?
        <form action="{{ route('backoffice.security.send') }}" method="POST" class="inline">
            @csrf
            <input type="hidden" name="email" value="{{ $email }}">
            <button type="submit" class="text-primary hover:underline">Resend</button>
        </form>
    </p>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const pinContainer = document.getElementById('pin-input');
    const codeInput = document.getElementById('code');
    const form = pinContainer.closest('form');
    const pinInputs = Array.from(pinContainer.querySelectorAll('[data-pin-input-item]'));

    // Collect pin values into hidden field
    function updateCodeValue() {
        const code = pinInputs.map(input => input.value).join('');
        codeInput.value = code;
        return code;
    }

    // Handle paste event - distribute digits across inputs
    pinInputs.forEach((input, index) => {
        input.addEventListener('paste', function(e) {
            e.preventDefault();
            const pastedData = (e.clipboardData || window.clipboardData).getData('text');
            const digits = pastedData.replace(/\D/g, '').slice(0, 6);

            if (digits.length > 0) {
                digits.split('').forEach((digit, i) => {
                    if (pinInputs[i]) {
                        pinInputs[i].value = digit;
                    }
                });

                // Focus last filled input or last input
                const focusIndex = Math.min(digits.length, pinInputs.length) - 1;
                pinInputs[focusIndex].focus();

                // Update hidden field and auto-submit if complete
                const code = updateCodeValue();
                if (code.length === 6) {
                    setTimeout(() => form.submit(), 100);
                }
            }
        });

        input.addEventListener('input', updateCodeValue);
    });

    // Auto-submit when all 6 digits are entered via typing
    pinInputs[pinInputs.length - 1].addEventListener('input', function() {
        const code = updateCodeValue();
        if (code.length === 6) {
            setTimeout(() => form.submit(), 100);
        }
    });

    // Update hidden field before form submit
    form.addEventListener('submit', function(e) {
        updateCodeValue();
    });
});
</script>
@endsection
