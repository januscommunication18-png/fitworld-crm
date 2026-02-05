<nav class="navbar bg-base-100 shadow-base-300/20 shadow-sm sticky top-0 z-50">
    <div class="navbar-start gap-2">
        {{-- Sidebar toggle --}}
        <button type="button" class="btn btn-text btn-square"
            aria-haspopup="dialog" aria-expanded="false"
            aria-controls="dashboard-sidebar"
            data-overlay="#dashboard-sidebar">
            <span class="icon-[tabler--menu-2] size-5"></span>
        </button>

        {{-- Logo --}}
        <a href="{{ url('/dashboard') }}" class="link text-base-content link-neutral text-xl font-bold no-underline">
            FitCRM
        </a>

        {{-- Primary nav links (hidden on mobile) --}}
        <ul class="menu menu-horizontal gap-1 p-0 hidden lg:flex">
            <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
            <li><a href="{{ url('/schedule') }}">Schedule</a></li>
            <li><a href="{{ url('/students') }}">Students</a></li>
            <li><a href="{{ url('/payments') }}">Payments</a></li>
        </ul>
    </div>

    <div class="navbar-end gap-1">
        {{-- Search --}}
        <button type="button" class="btn btn-text btn-square"
            aria-haspopup="dialog" aria-expanded="false"
            aria-controls="search-modal"
            data-overlay="#search-modal">
            <span class="icon-[tabler--search] size-5"></span>
        </button>

        {{-- Alerts bell --}}
        <button type="button" class="btn btn-text btn-square indicator"
            aria-haspopup="dialog" aria-expanded="false"
            aria-controls="alerts-drawer"
            data-overlay="#alerts-drawer">
            <span class="indicator-item badge badge-error badge-xs"></span>
            <span class="icon-[tabler--bell] size-5"></span>
        </button>

        {{-- App grid --}}
        <button type="button" class="btn btn-text btn-square"
            aria-haspopup="dialog" aria-expanded="false"
            aria-controls="app-modal"
            data-overlay="#app-modal">
            <span class="icon-[tabler--grid-dots] size-5"></span>
        </button>

        {{-- Chat --}}
        <a href="{{ url('/chat') }}" class="btn btn-text btn-square">
            <span class="icon-[tabler--message-circle] size-5"></span>
        </a>

        {{-- Profile dropdown --}}
        <div class="dropdown relative inline-flex [--auto-close:inside] [--placement:bottom-end]">
            <button id="profile-dropdown" type="button" class="dropdown-toggle btn btn-text btn-circle"
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
                    <div class="text-sm font-semibold text-base-content">Studio Owner</div>
                    <div class="text-xs text-base-content/60">owner@studio.com</div>
                </div>
                <div><a class="dropdown-item" href="{{ url('/profile') }}">
                    <span class="icon-[tabler--user] size-4"></span> My Profile
                </a></div>
                <div><a class="dropdown-item" href="{{ url('/password') }}">
                    <span class="icon-[tabler--lock] size-4"></span> Change Password
                </a></div>
                <div><a class="dropdown-item" href="{{ url('/activity') }}">
                    <span class="icon-[tabler--activity] size-4"></span> My Activity
                </a></div>
                <div><a class="dropdown-item" href="{{ url('/preferences') }}">
                    <span class="icon-[tabler--settings] size-4"></span> Preferences
                </a></div>
                <div class="divider my-1"></div>
                <div><a class="dropdown-item text-error" href="{{ url('/logout') }}">
                    <span class="icon-[tabler--logout] size-4"></span> Log Off
                </a></div>
            </div>
        </div>
    </div>
</nav>
