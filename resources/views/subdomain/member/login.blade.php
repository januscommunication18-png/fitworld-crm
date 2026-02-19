@extends('layouts.subdomain')

@section('title', 'Member Login — ' . $host->studio_name)

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

                {{-- Back to Booking --}}
                <a href="{{ route('subdomain.home', ['subdomain' => $host->subdomain]) }}" class="btn btn-ghost btn-sm">
                    <span class="icon-[tabler--arrow-left] size-5"></span>
                    Back to Schedule
                </a>
            </div>
        </div>
    </nav>

    {{-- Login Content --}}
    <div class="flex-1 flex items-center justify-center py-12 px-4">
        <div class="w-full max-w-md">
            <div class="text-center mb-8">
                <h1 class="text-2xl font-bold">Welcome Back</h1>
                <p class="text-base-content/60 mt-2">Sign in to access your member portal</p>
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
                    @if($loginMethod === 'otp')
                        {{-- OTP Login --}}
                        <form action="{{ route('member.send-otp', ['subdomain' => $host->subdomain]) }}" method="POST">
                            @csrf
                            <div class="space-y-4">
                                <div>
                                    <label class="label-text" for="email">Email Address</label>
                                    <input type="email" id="email" name="email"
                                           value="{{ old('email', session('email')) }}"
                                           required autofocus
                                           placeholder="Enter your email"
                                           class="input input-bordered w-full mt-1 @error('email') input-error @enderror">
                                </div>

                                <button type="submit" class="btn btn-primary w-full">
                                    <span class="icon-[tabler--mail] size-5"></span>
                                    Send Verification Code
                                </button>
                            </div>
                        </form>

                        <div class="text-center mt-4">
                            <p class="text-sm text-base-content/60">
                                We'll send a 6-digit code to your email to verify your identity.
                            </p>
                        </div>
                    @else
                        {{-- Password Login --}}
                        <form action="{{ route('member.login.post', ['subdomain' => $host->subdomain]) }}" method="POST">
                            @csrf
                            <div class="space-y-4">
                                <div>
                                    <label class="label-text" for="email">Email Address</label>
                                    <input type="email" id="email" name="email"
                                           value="{{ old('email') }}"
                                           required autofocus
                                           placeholder="Enter your email"
                                           class="input input-bordered w-full mt-1 @error('email') input-error @enderror">
                                </div>

                                <div>
                                    <label class="label-text" for="password">Password</label>
                                    <input type="password" id="password" name="password"
                                           required
                                           placeholder="Enter your password"
                                           class="input input-bordered w-full mt-1">
                                </div>

                                <div class="flex items-center justify-between">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" name="remember" value="1" class="checkbox checkbox-sm checkbox-primary">
                                        <span class="text-sm">Remember me</span>
                                    </label>
                                    <a href="{{ route('member.forgot-password', ['subdomain' => $host->subdomain]) }}"
                                       class="text-sm text-primary hover:underline">
                                        Forgot password?
                                    </a>
                                </div>

                                <button type="submit" class="btn btn-primary w-full">
                                    <span class="icon-[tabler--login] size-5"></span>
                                    Sign In
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>

            <p class="text-center text-sm text-base-content/60 mt-6">
                Don't have an account?
                <a href="{{ route('member.signup', ['subdomain' => $host->subdomain]) }}"
                   class="text-primary hover:underline font-medium">
                    Create Account
                </a>
            </p>
        </div>
    </div>
</div>
@endsection
