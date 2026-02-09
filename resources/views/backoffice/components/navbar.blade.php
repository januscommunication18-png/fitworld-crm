<header class="h-16 bg-base-100 border-b border-base-content/10 flex items-center justify-between px-6 sticky top-0 z-20">
    {{-- Left side --}}
    <div class="flex items-center gap-4">
        {{-- Mobile menu button --}}
        <button type="button" id="mobile-menu-btn" class="btn btn-ghost btn-sm btn-square lg:hidden">
            <span class="icon-[tabler--menu-2] size-5"></span>
        </button>

        {{-- Page title --}}
        <h1 class="text-lg font-semibold">@yield('page-title', 'Dashboard')</h1>
    </div>

    {{-- Right side --}}
    <div class="flex items-center gap-3">
        {{-- Quick stats --}}
        <div class="hidden md:flex items-center gap-4 text-sm text-base-content/60 mr-4">
            <span class="flex items-center gap-1">
                <span class="icon-[tabler--building] size-4"></span>
                <span>{{ \App\Models\Host::count() }} Clients</span>
            </span>
            <span class="flex items-center gap-1">
                <span class="icon-[tabler--clock] size-4"></span>
                <span>{{ now()->format('M d, Y') }}</span>
            </span>
        </div>

        {{-- User dropdown --}}
        <div class="dropdown dropdown-end">
            <button type="button" class="btn btn-ghost btn-sm flex items-center gap-2" tabindex="0">
                <div class="avatar avatar-placeholder">
                    <div class="bg-primary text-primary-content size-8 rounded-full text-xs font-bold">
                        {{ strtoupper(substr(Auth::guard('admin')->user()->first_name ?? 'A', 0, 1) . substr(Auth::guard('admin')->user()->last_name ?? 'D', 0, 1)) }}
                    </div>
                </div>
                <span class="hidden sm:inline">{{ Auth::guard('admin')->user()->first_name ?? 'Admin' }}</span>
                <span class="icon-[tabler--chevron-down] size-4"></span>
            </button>
            <ul class="dropdown-menu dropdown-open:opacity-100 mt-2 w-48" role="menu" tabindex="0">
                <li class="dropdown-header">
                    <span class="text-base-content/60 text-xs">Signed in as</span>
                    <span class="block text-sm font-medium truncate">{{ Auth::guard('admin')->user()->email ?? 'admin@fitcrm.com' }}</span>
                </li>
                <li class="dropdown-divider"></li>
                <li>
                    <a href="{{ route('backoffice.password.change') }}" class="dropdown-item">
                        <span class="icon-[tabler--key] size-4"></span>
                        Change Password
                    </a>
                </li>
                <li class="dropdown-divider"></li>
                <li>
                    <form method="POST" action="{{ route('backoffice.logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item text-error">
                            <span class="icon-[tabler--logout] size-4"></span>
                            Log Out
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</header>
