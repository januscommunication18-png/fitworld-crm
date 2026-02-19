@extends('layouts.subdomain')

@section('title', 'Verify Code — ' . $host->studio_name)

@section('content')
<div class="min-h-screen flex flex-col">
    {{-- Header --}}
    <nav class="bg-base-100 border-b border-base-200" style="height: 75px;">
        <div class="container-fixed h-full">
            <div class="flex items-center justify-between h-full">
                {{-- Logo --}}
                <div class="flex items-center">
                    @if($host->logo_url)
                        <a href="{{ route('subdomain.home', ['subdomain' => $host->subdomain]) }}" class="flex items-center">
                            <img src="{{ $host->logo_url }}" alt="{{ $host->studio_name }}" class="h-12 w-auto max-w-[180px] object-contain">
                        </a>
                    @else
                        <a href="{{ route('subdomain.home', ['subdomain' => $host->subdomain]) }}" class="flex items-center gap-2">
                            <div class="w-10 h-10 rounded-xl bg-primary flex items-center justify-center">
                                <span class="text-lg font-bold text-primary-content">{{ strtoupper(substr($host->studio_name, 0, 1)) }}</span>
                            </div>
                            <span class="font-bold text-lg">{{ $host->studio_name }}</span>
                        </a>
                    @endif
                </div>

                {{-- Back to Login --}}
                <a href="{{ route('member.login', ['subdomain' => $host->subdomain]) }}" class="btn btn-ghost btn-sm">
                    <span class="icon-[tabler--arrow-left] size-5"></span>
                    Back
                </a>
            </div>
        </div>
    </nav>

    {{-- Verify Content --}}
    <div class="flex-1 flex items-center justify-center py-12 px-4">
        <div class="w-full max-w-md">
            <div class="text-center mb-8">
                <div class="w-16 h-16 rounded-full bg-primary/10 flex items-center justify-center mx-auto mb-4">
                    <span class="icon-[tabler--mail-check] size-8 text-primary"></span>
                </div>
                <h1 class="text-2xl font-bold">Check Your Email</h1>
                <p class="text-base-content/60 mt-2">
                    We sent a verification code to<br>
                    <span class="font-medium text-base-content">{{ $email }}</span>
                </p>
            </div>

            @if(session('status'))
                <div class="alert alert-success mb-6">
                    <span class="icon-[tabler--check] size-5"></span>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-error mb-6">
                    <span class="icon-[tabler--alert-circle] size-5"></span>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <div class="card bg-base-100 shadow-xl">
                <div class="card-body">
                    <form action="{{ route('member.verify-otp.post', ['subdomain' => $host->subdomain]) }}" method="POST">
                        @csrf
                        <input type="hidden" name="email" value="{{ $email }}">

                        <div class="space-y-4">
                            <div>
                                <label class="label-text" for="code">Verification Code</label>
                                <input type="text" id="code" name="code"
                                       maxlength="6"
                                       pattern="[0-9]{6}"
                                       inputmode="numeric"
                                       autocomplete="one-time-code"
                                       required autofocus
                                       placeholder="Enter 6-digit code"
                                       class="input input-bordered w-full mt-1 text-center text-2xl tracking-[0.5em] font-mono @error('code') input-error @enderror">
                                <p class="text-xs text-base-content/50 mt-1 text-center">
                                    Code expires in {{ $settings['activation_code_expiry_minutes'] ?? 10 }} minutes
                                </p>
                            </div>

                            <button type="submit" class="btn btn-primary w-full">
                                <span class="icon-[tabler--check] size-5"></span>
                                Verify & Sign In
                            </button>
                        </div>
                    </form>

                    <div class="divider text-sm text-base-content/50">Didn't receive the code?</div>

                    <form action="{{ route('member.send-otp', ['subdomain' => $host->subdomain]) }}" method="POST">
                        @csrf
                        <input type="hidden" name="email" value="{{ $email }}">
                        <button type="submit" class="btn btn-ghost btn-sm w-full">
                            <span class="icon-[tabler--refresh] size-4"></span>
                            Resend Code
                        </button>
                    </form>
                </div>
            </div>

            <p class="text-center text-sm text-base-content/60 mt-6">
                Wrong email?
                <a href="{{ route('member.login', ['subdomain' => $host->subdomain]) }}"
                   class="text-primary hover:underline">
                    Try a different email
                </a>
            </p>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Auto-format OTP input
document.getElementById('code').addEventListener('input', function(e) {
    this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6);
});
</script>
@endpush
@endsection
