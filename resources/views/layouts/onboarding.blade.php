<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Setup Your Studio') — {{ config('app.name', 'FitCRM') }}</title>

    @vite(['resources/css/app.css'])
    @stack('styles')
    @stack('head')
</head>
<body class="bg-base-200 min-h-screen">

    {{-- Minimal header --}}
    <header class="bg-base-100 border-b border-base-300">
        <div class="max-w-5xl mx-auto px-4 py-4 flex items-center justify-between">
            {{-- Logo --}}
            <a href="/" class="flex items-center gap-2">
                <img src="{{ asset('images/logo.svg') }}" alt="{{ config('app.name') }}" class="h-8">
                <span class="font-semibold text-lg hidden sm:inline">{{ config('app.name') }}</span>
            </a>

            {{-- Actions --}}
            <div class="flex items-center gap-3">
                {{-- Technical Support Button --}}
                <button type="button"
                    id="support-trigger"
                    class="btn btn-ghost btn-sm gap-2"
                    aria-haspopup="dialog"
                    aria-expanded="false"
                    aria-controls="support-modal"
                    data-overlay="#support-modal">
                    <span class="icon-[tabler--headset] size-4"></span>
                    <span class="hidden sm:inline">Request Support</span>
                </button>

                {{-- Logout --}}
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-ghost btn-sm gap-2">
                        <span class="icon-[tabler--logout] size-4"></span>
                        <span class="hidden sm:inline">Logout</span>
                    </button>
                </form>
            </div>
        </div>
    </header>

    {{-- Main content --}}
    <main class="flex-1 py-8">
        <div class="max-w-3xl mx-auto px-4">
            @yield('content')
        </div>
    </main>

    {{-- Technical Support Modal --}}
    <div id="support-modal" class="overlay modal overlay-open:opacity-100 overlay-open:duration-300 modal-middle hidden" role="dialog" tabindex="-1">
        <div class="modal-dialog max-w-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">Request Technical Support</h3>
                    <button type="button" class="btn btn-text btn-circle btn-sm absolute end-3 top-3" aria-label="Close" data-overlay="#support-modal">
                        <span class="icon-[tabler--x] size-4"></span>
                    </button>
                </div>
                <form id="support-form" method="POST">
                    <div class="modal-body">
                        <p class="text-base-content/70 text-sm mb-4">Need help setting up your studio? Our team will reach out to assist you.</p>

                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="support-first-name" class="label label-text">First Name</label>
                                    <input type="text" id="support-first-name" name="first_name" class="input" required value="{{ auth()->user()?->first_name }}">
                                </div>
                                <div>
                                    <label for="support-last-name" class="label label-text">Last Name</label>
                                    <input type="text" id="support-last-name" name="last_name" class="input" required value="{{ auth()->user()?->last_name }}">
                                </div>
                            </div>

                            <div>
                                <label for="support-email" class="label label-text">Email</label>
                                <input type="email" id="support-email" name="email" class="input" required value="{{ auth()->user()?->email }}">
                            </div>

                            <div>
                                <label for="support-phone" class="label label-text">Phone (Optional)</label>
                                <input type="tel" id="support-phone" name="phone" class="input" value="{{ auth()->user()?->getFullPhoneNumber() }}">
                            </div>

                            <div>
                                <label for="support-note" class="label label-text">How can we help?</label>
                                <textarea id="support-note" name="note" class="textarea" rows="3" placeholder="Describe what you need help with..."></textarea>
                            </div>
                        </div>

                        <div id="support-error" class="alert alert-error mt-4 hidden">
                            <span class="alert-icon icon-[tabler--alert-circle]"></span>
                            <p id="support-error-message"></p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-soft btn-secondary" data-overlay="#support-modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="support-submit-btn">
                            <span class="icon-[tabler--send] size-4 mr-2"></span>
                            Request Support
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Support form submission script --}}
    <script>
        document.getElementById('support-form')?.addEventListener('submit', async function(e) {
            e.preventDefault();

            const form = this;
            const submitBtn = document.getElementById('support-submit-btn');
            const errorDiv = document.getElementById('support-error');
            const errorMessage = document.getElementById('support-error-message');

            // Disable button
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="loading loading-spinner loading-sm"></span> Submitting...';
            errorDiv.classList.add('hidden');

            const formData = {
                first_name: document.getElementById('support-first-name').value,
                last_name: document.getElementById('support-last-name').value,
                email: document.getElementById('support-email').value,
                phone: document.getElementById('support-phone').value,
                note: document.getElementById('support-note').value,
            };

            try {
                const response = await fetch('/api/v1/onboarding/support', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(formData),
                });

                const data = await response.json();

                if (response.ok && data.data?.redirect_url) {
                    window.location.href = data.data.redirect_url;
                } else {
                    throw new Error(data.meta?.message || 'Failed to submit support request');
                }
            } catch (error) {
                errorDiv.classList.remove('hidden');
                errorMessage.textContent = error.message;
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<span class="icon-[tabler--send] size-4 mr-2"></span> Request Support';
            }
        });
    </script>

    @stack('scripts')
</body>
</html>
