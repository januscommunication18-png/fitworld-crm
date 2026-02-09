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
                    <label class="label-text" for="code">Verification Code</label>
                    <input type="text" id="code" name="code" value="{{ old('code') }}"
                        class="input w-full text-center text-2xl tracking-widest font-mono @error('code') input-error @enderror"
                        placeholder="000000" maxlength="6" required autofocus
                        pattern="[0-9]{6}" inputmode="numeric" />
                    @error('code')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
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
