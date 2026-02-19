@extends('layouts.subdomain')

@section('title', 'Create Account — ' . $host->studio_name)

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

                {{-- Back to Schedule --}}
                <a href="{{ route('subdomain.home', ['subdomain' => $host->subdomain]) }}" class="btn btn-ghost btn-sm">
                    <span class="icon-[tabler--arrow-left] size-5"></span>
                    Back to Schedule
                </a>
            </div>
        </div>
    </nav>

    {{-- Sign Up Content --}}
    <div class="flex-1 flex items-center justify-center py-12 px-4">
        <div class="w-full max-w-md">
            <div class="text-center mb-8">
                <h1 class="text-2xl font-bold">Create Your Account</h1>
                <p class="text-base-content/60 mt-2">Join {{ $host->studio_name }} to book classes and manage your membership</p>
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
                    <form action="{{ route('member.signup.post', ['subdomain' => $host->subdomain]) }}" method="POST">
                        @csrf
                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="label-text" for="first_name">First Name <span class="text-error">*</span></label>
                                    <input type="text" id="first_name" name="first_name"
                                           value="{{ old('first_name') }}"
                                           required autofocus
                                           placeholder="First name"
                                           class="input input-bordered w-full mt-1 @error('first_name') input-error @enderror">
                                    @error('first_name')
                                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="label-text" for="last_name">Last Name <span class="text-error">*</span></label>
                                    <input type="text" id="last_name" name="last_name"
                                           value="{{ old('last_name') }}"
                                           required
                                           placeholder="Last name"
                                           class="input input-bordered w-full mt-1 @error('last_name') input-error @enderror">
                                    @error('last_name')
                                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div>
                                <label class="label-text" for="email">Email Address <span class="text-error">*</span></label>
                                <input type="email" id="email" name="email"
                                       value="{{ old('email') }}"
                                       required
                                       placeholder="Enter your email"
                                       class="input input-bordered w-full mt-1 @error('email') input-error @enderror">
                                @error('email')
                                    <p class="text-error text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="label-text" for="phone">Phone Number</label>
                                <input type="tel" id="phone" name="phone"
                                       value="{{ old('phone') }}"
                                       placeholder="(optional)"
                                       class="input input-bordered w-full mt-1 @error('phone') input-error @enderror">
                                @error('phone')
                                    <p class="text-error text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <button type="submit" class="btn btn-primary w-full">
                                <span class="icon-[tabler--user-plus] size-5"></span>
                                Create Account
                            </button>
                        </div>
                    </form>

                    <div class="text-center mt-4">
                        <p class="text-sm text-base-content/60">
                            We'll send a verification code to your email to confirm your account.
                        </p>
                    </div>
                </div>
            </div>

            <p class="text-center text-sm text-base-content/60 mt-6">
                Already have an account?
                <a href="{{ route('member.login', ['subdomain' => $host->subdomain]) }}"
                   class="text-primary hover:underline font-medium">
                    Sign In
                </a>
            </p>
        </div>
    </div>
</div>
@endsection
