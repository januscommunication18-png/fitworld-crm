@extends('layouts.subdomain')

@section('title', 'Reset Password — ' . $host->studio_name)

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

    {{-- Reset Password Content --}}
    <div class="flex-1 flex items-center justify-center py-12 px-4">
        <div class="w-full max-w-md">
            <div class="text-center mb-8">
                <div class="w-16 h-16 rounded-full bg-primary/10 flex items-center justify-center mx-auto mb-4">
                    <span class="icon-[tabler--lock-check] size-8 text-primary"></span>
                </div>
                <h1 class="text-2xl font-bold">Reset Your Password</h1>
                <p class="text-base-content/60 mt-2">
                    Enter a new password for<br>
                    <span class="font-medium text-base-content">{{ $email }}</span>
                </p>
            </div>

            @if($errors->any())
                <div class="alert alert-error mb-6">
                    <span class="icon-[tabler--alert-circle] size-5"></span>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <div class="card bg-base-100 shadow-xl">
                <div class="card-body">
                    <form action="{{ route('member.reset-password.post', ['subdomain' => $host->subdomain]) }}" method="POST">
                        @csrf
                        <input type="hidden" name="token" value="{{ $token }}">

                        <div class="space-y-4">
                            <div>
                                <label class="label-text" for="password">New Password</label>
                                <input type="password" id="password" name="password"
                                       required autofocus
                                       placeholder="Enter new password"
                                       class="input input-bordered w-full mt-1 @error('password') input-error @enderror">
                                <p class="text-xs text-base-content/50 mt-1">Minimum 8 characters</p>
                            </div>

                            <div>
                                <label class="label-text" for="password_confirmation">Confirm Password</label>
                                <input type="password" id="password_confirmation" name="password_confirmation"
                                       required
                                       placeholder="Confirm new password"
                                       class="input input-bordered w-full mt-1">
                            </div>

                            <button type="submit" class="btn btn-primary w-full">
                                <span class="icon-[tabler--check] size-5"></span>
                                Reset Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
