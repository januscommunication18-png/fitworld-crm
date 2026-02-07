{{-- Email Verified Success Message --}}
@if(session('verified'))
<div id="email-verified-alert" class="bg-gradient-to-r from-success/10 via-success/5 to-success/10 border-b border-success/20 px-6 py-2.5" role="alert">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3 flex-1 justify-center">
            <div class="flex items-center justify-center size-8 rounded-full bg-success/20">
                <span class="icon-[tabler--mail-check] size-4 text-success"></span>
            </div>
            <span class="text-sm font-medium text-success">Your email has been verified successfully!</span>
        </div>
        <button type="button" class="btn btn-ghost btn-sm btn-circle text-success/70 hover:text-success hover:bg-success/10" onclick="document.getElementById('email-verified-alert').remove()" aria-label="Dismiss">
            <span class="icon-[tabler--x] size-5"></span>
        </button>
    </div>
</div>
<script>sessionStorage.removeItem('email_alert_dismissed');</script>
@endif

{{-- Email Verification Resent Message --}}
@if(session('message'))
<div id="email-resent-alert" class="bg-gradient-to-r from-success/10 via-success/5 to-success/10 border-b border-success/20 px-6 py-2.5" role="alert">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3 flex-1 justify-center">
            <div class="flex items-center justify-center size-8 rounded-full bg-success/20">
                <span class="icon-[tabler--check] size-4 text-success"></span>
            </div>
            <span class="text-sm font-medium text-success">{{ session('message') }}</span>
        </div>
        <button type="button" class="btn btn-ghost btn-sm btn-circle text-success/70 hover:text-success hover:bg-success/10" onclick="document.getElementById('email-resent-alert').remove()" aria-label="Dismiss">
            <span class="icon-[tabler--x] size-5"></span>
        </button>
    </div>
</div>
@endif

{{-- Email Verification Alert (above navbar) --}}
@auth
    @if(!Auth::user()->hasVerifiedEmail())
    <div id="email-verification-alert" class="bg-gradient-to-r from-error/10 via-error/5 to-error/10 border-b border-error/20 px-6 py-2.5" role="alert">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3 flex-1 justify-center">
                <div class="flex items-center justify-center size-8 rounded-full bg-error/20">
                    <span class="icon-[tabler--mail-exclamation] size-4 text-error"></span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-sm font-medium text-error">Email not verified</span>
                    <span class="text-sm text-base-content/70 hidden sm:inline">— Please check your inbox and verify your email address</span>
                </div>
                <form method="POST" action="{{ route('verification.send') }}" class="inline" id="resend-verification-form">
                    @csrf
                    <button type="submit" class="btn btn-error btn-sm gap-1" id="resend-verification-btn">
                        <span class="icon-[tabler--send] size-4"></span>
                        <span class="hidden sm:inline">Resend</span>
                    </button>
                </form>
            </div>
            <button type="button" class="btn btn-ghost btn-sm btn-circle text-error/70 hover:text-error hover:bg-error/10" onclick="this.closest('#email-verification-alert').style.display='none'; sessionStorage.setItem('email_alert_dismissed', '1')" aria-label="Dismiss">
                <span class="icon-[tabler--x] size-5"></span>
            </button>
        </div>
    </div>
    <script>
        // Hide alert if dismissed in this session
        if (sessionStorage.getItem('email_alert_dismissed') === '1') {
            document.getElementById('email-verification-alert').style.display = 'none';
        }
    </script>
    @endif
@endauth

<div id="toolbar" class="flex items-center justify-between bg-base-100 border-b border-base-content/10 px-6 h-16 shrink-0">
    {{-- Left: mobile sidebar toggle + search --}}
    <div class="flex items-center gap-3">
        <button type="button" class="btn btn-ghost btn-sm btn-square lg:hidden" id="mobile-sidebar-toggle" aria-label="Toggle sidebar">
            <span class="icon-[tabler--menu-2] size-5"></span>
        </button>

        {{-- Search input (visible on sm+) --}}
        <div class="w-72 hidden sm:block">
            <div class="relative">
                <input class="input input-sm ps-8 w-full" type="text" placeholder="Search students, classes, bookings..." />
                <span class="icon-[tabler--search] text-base-content/50 absolute start-3 top-1/2 size-4 shrink-0 -translate-y-1/2"></span>
            </div>
        </div>

        {{-- Mobile search icon --}}
        <button type="button" class="btn btn-ghost btn-sm btn-square sm:hidden" aria-label="Search"
            aria-haspopup="dialog" aria-expanded="false" aria-controls="search-modal" data-overlay="#search-modal">
            <span class="icon-[tabler--search] size-5"></span>
        </button>
    </div>

    {{-- Right: action icons + profile --}}
    <div class="flex items-center gap-1">
        {{-- Alerts bell --}}
        <button type="button" class="btn btn-ghost btn-sm btn-square indicator"
            aria-haspopup="dialog" aria-expanded="false" aria-controls="alerts-drawer" data-overlay="#alerts-drawer">
            <span class="indicator-item badge badge-error badge-xs"></span>
            <span class="icon-[tabler--bell] size-5"></span>
        </button>

        {{-- App grid --}}
        <button type="button" class="btn btn-ghost btn-sm btn-square"
            aria-haspopup="dialog" aria-expanded="false" aria-controls="app-modal" data-overlay="#app-modal">
            <span class="icon-[tabler--grid-dots] size-5"></span>
        </button>

        {{-- Chat --}}
        <a href="#" class="btn btn-ghost btn-sm btn-square" aria-label="Chat">
            <span class="icon-[tabler--message-circle] size-5"></span>
        </a>

        {{-- Profile dropdown --}}
        <div class="dropdown relative inline-flex [--auto-close:inside] [--placement:bottom-end]">
            <button id="profile-dropdown" type="button" class="dropdown-toggle btn btn-ghost btn-circle btn-sm"
                aria-haspopup="menu" aria-expanded="false" aria-label="Profile menu">
                <div class="avatar avatar-placeholder">
                    <div class="bg-neutral text-neutral-content size-8 rounded-full">
                        <span class="icon-[tabler--user] size-4"></span>
                    </div>
                </div>
            </button>
            <div class="dropdown-menu dropdown-open:opacity-100 hidden min-w-52"
                role="menu" aria-orientation="vertical" aria-labelledby="profile-dropdown">
                <div class="dropdown-header">
                    <div class="text-sm font-semibold text-base-content">{{ Auth::user()->full_name }}</div>
                    <div class="text-xs text-base-content/60">{{ Auth::user()->email }}</div>
                </div>
                <div><a class="dropdown-item" href="#">
                    <span class="icon-[tabler--user] size-4"></span> My Profile
                </a></div>
                <div><a class="dropdown-item" href="#">
                    <span class="icon-[tabler--lock] size-4"></span> Change Password
                </a></div>
                <div><a class="dropdown-item" href="#">
                    <span class="icon-[tabler--activity] size-4"></span> My Activity
                </a></div>
                <div><a class="dropdown-item" href="#">
                    <span class="icon-[tabler--settings] size-4"></span> Preferences
                </a></div>
                <div class="divider my-1"></div>
                <div>
                    <form method="POST" action="{{ url('/logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item text-error w-full text-start">
                            <span class="icon-[tabler--logout] size-4"></span> Log Off
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
