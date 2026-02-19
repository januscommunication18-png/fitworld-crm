@extends('layouts.subdomain')

@section('title', 'Forgot Password — ' . $host->studio_name)

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
                    Back to Login
                </a>
            </div>
        </div>
    </nav>

    {{-- Forgot Password Content --}}
    <div class="flex-1 flex items-center justify-center py-12 px-4">
        <div class="w-full max-w-md">
            <div class="text-center mb-8">
                <div class="w-16 h-16 rounded-full bg-primary/10 flex items-center justify-center mx-auto mb-4">
                    <span class="icon-[tabler--lock-question] size-8 text-primary"></span>
                </div>
                <h1 class="text-2xl font-bold">Forgot Password?</h1>
                <p class="text-base-content/60 mt-2">
                    Enter your email and we'll send you a link to reset your password.
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
                    <form action="{{ route('member.forgot-password.post', ['subdomain' => $host->subdomain]) }}" method="POST">
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

                            <button type="submit" class="btn btn-primary w-full">
                                <span class="icon-[tabler--mail] size-5"></span>
                                Send Reset Link
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <p class="text-center text-sm text-base-content/60 mt-6">
                Remember your password?
                <a href="{{ route('member.login', ['subdomain' => $host->subdomain]) }}"
                   class="text-primary hover:underline">
                    Sign in
                </a>
            </p>
        </div>
    </div>
</div>
@endsection
