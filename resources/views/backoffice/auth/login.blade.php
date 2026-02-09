@extends('backoffice.layouts.auth')

@section('title', 'Login')

@section('content')
<div class="card bg-base-100">
    <div class="card-body">
        <div class="text-center mb-6">
            <div class="flex items-center justify-center mb-4">
                <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center">
                    <span class="icon-[tabler--login] size-8 text-primary"></span>
                </div>
            </div>
            <h1 class="text-xl font-bold">Admin Login</h1>
            <p class="text-base-content/60 text-sm mt-1">Enter your credentials to continue</p>
        </div>

        <form action="{{ route('backoffice.login') }}" method="POST">
            @csrf

            <div class="space-y-4">
                <div>
                    <label class="label-text" for="email">Email Address</label>
                    <input type="email" id="email" name="email" value="{{ old('email', $email ?? '') }}"
                        class="input w-full @error('email') input-error @enderror"
                        placeholder="admin@example.com" required autofocus />
                    @error('email')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="label-text" for="password">Password</label>
                    <input type="password" id="password" name="password"
                        class="input w-full @error('password') input-error @enderror"
                        placeholder="Enter your password" required />
                    @error('password')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="remember" class="checkbox checkbox-sm checkbox-primary" />
                        <span class="text-sm">Remember me</span>
                    </label>

                    <button type="button" class="text-sm text-primary hover:underline" onclick="document.getElementById('forgot-modal').showModal()">
                        Forgot password?
                    </button>
                </div>

                <button type="submit" class="btn btn-primary w-full">
                    <span class="icon-[tabler--login] size-4"></span>
                    Log In
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Forgot Password Modal --}}
<dialog id="forgot-modal" class="modal">
    <div class="modal-box">
        <form method="dialog">
            <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">
                <span class="icon-[tabler--x] size-5"></span>
            </button>
        </form>
        <h3 class="font-bold text-lg mb-4">Reset Password</h3>
        <p class="text-base-content/60 text-sm mb-4">
            Enter your email address and we'll send you a new temporary password.
        </p>
        <form action="{{ route('backoffice.password.forgot') }}" method="POST">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="label-text" for="forgot-email">Email Address</label>
                    <input type="email" id="forgot-email" name="email"
                        class="input w-full" placeholder="admin@example.com" required />
                </div>
                <button type="submit" class="btn btn-primary w-full">
                    <span class="icon-[tabler--mail] size-4"></span>
                    Send New Password
                </button>
            </div>
        </form>
    </div>
    <form method="dialog" class="modal-backdrop">
        <button>close</button>
    </form>
</dialog>
@endsection
