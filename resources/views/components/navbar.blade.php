<div id="toolbar" class="flex items-center justify-between bg-base-100 border-b border-base-content/10 px-6 h-16 shrink-0">
    {{-- Left: mobile sidebar toggle + search --}}
    <div class="flex items-center gap-2">
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
