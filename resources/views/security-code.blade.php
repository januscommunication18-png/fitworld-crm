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

                <form action="{{ route('security-code.verify') }}" method="POST">
                    @csrf
                    <div>
                        <label class="label-text" for="security_code">Security Code</label>
                        <input
                            type="text"
                            id="security_code"
                            name="security_code"
                            class="input w-full @error('security_code') input-error @enderror"
                            placeholder="Enter security code"
                            value="{{ old('security_code') }}"
                            autocomplete="off"
                            autofocus
                        >
                    </div>

                    <div class="mt-6">
                        <button type="submit" class="btn btn-primary w-full">
                            <span class="icon-[tabler--lock-open] size-5"></span>
                            Verify Access
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-8 text-center text-sm text-base-content/50">
            <p>&copy; {{ date('Y') }} {{ config('app.name', 'FitCRM') }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
