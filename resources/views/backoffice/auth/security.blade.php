@extends('backoffice.layouts.auth')

@section('title', 'Security Verification')

@section('content')
<div class="card bg-base-100">
    <div class="card-body">
        <div class="text-center mb-6">
            <div class="flex items-center justify-center mb-4">
                <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center">
                    <span class="icon-[tabler--shield-lock] size-8 text-primary"></span>
                </div>
            </div>
            <h1 class="text-xl font-bold">Security Verification</h1>
            <p class="text-base-content/60 text-sm mt-1">Enter your admin email to receive a verification code</p>
        </div>

        <form action="{{ route('backoffice.security.send') }}" method="POST">
            @csrf

            <div class="space-y-4">
                <div>
                    <label class="label-text" for="email">Email Address</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}"
                        class="input w-full @error('email') input-error @enderror"
                        placeholder="admin@example.com" required autofocus />
                    @error('email')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary w-full">
                    <span class="icon-[tabler--send] size-4"></span>
                    Send Verification Code
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
