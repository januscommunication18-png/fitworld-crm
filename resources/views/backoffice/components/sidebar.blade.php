<aside id="admin-sidebar" class="sticky top-0 h-screen z-40 w-64 bg-base-100 border-e border-base-content/10 flex flex-col shrink-0">

    {{-- Sidebar header --}}
    <div class="flex items-center gap-2 px-4 h-16 border-b border-base-content/10 shrink-0">
        <a href="{{ route('backoffice.dashboard') }}" class="flex items-center gap-2 no-underline">
            <div class="w-8 h-8 bg-primary rounded-lg flex items-center justify-center">
                <span class="icon-[tabler--building-store] size-5 text-primary-content"></span>
            </div>
            <span class="text-lg font-bold text-base-content">FitCRM</span>
            <span class="badge badge-soft badge-neutral badge-sm">Admin</span>
        </a>
    </div>

    {{-- Sidebar body --}}
    <div class="flex-1 overflow-y-auto px-3 py-4">
        <ul class="menu space-y-0.5 p-0">

            {{-- Dashboard --}}
            <li>
                <a href="{{ route('backoffice.dashboard') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-colors {{ request()->routeIs('backoffice.dashboard') ? 'bg-primary/10 text-primary font-medium' : 'hover:bg-base-content/5' }}">
                    <span class="icon-[tabler--layout-dashboard] size-5 shrink-0"></span>
                    <span>Dashboard</span>
                </a>
            </li>

            {{-- Section: Management --}}
            <li class="menu-title pt-4">
                <span class="text-xs font-semibold text-base-content/40 uppercase tracking-wider">Management</span>
            </li>

            {{-- Clients --}}
            <li>
                <a href="{{ route('backoffice.clients.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-colors {{ request()->routeIs('backoffice.clients.*') ? 'bg-primary/10 text-primary font-medium' : 'hover:bg-base-content/5' }}">
                    <span class="icon-[tabler--building] size-5 shrink-0"></span>
                    <span>Clients</span>
                </a>
            </li>

            {{-- Class (Coming Soon) --}}
            <li>
                <a href="{{ route('backoffice.class.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-base-content/50 hover:bg-base-content/5 transition-colors">
                    <span class="icon-[tabler--yoga] size-5 shrink-0"></span>
                    <span>Class</span>
                    <span class="badge badge-soft badge-neutral badge-xs ml-auto">Soon</span>
                </a>
            </li>

            {{-- Bookings (Coming Soon) --}}
            <li>
                <a href="{{ route('backoffice.bookings.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-base-content/50 hover:bg-base-content/5 transition-colors">
                    <span class="icon-[tabler--calendar-check] size-5 shrink-0"></span>
                    <span>Bookings</span>
                    <span class="badge badge-soft badge-neutral badge-xs ml-auto">Soon</span>
                </a>
            </li>

            {{-- Schedule (Coming Soon) --}}
            <li>
                <a href="{{ route('backoffice.schedule.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-base-content/50 hover:bg-base-content/5 transition-colors">
                    <span class="icon-[tabler--calendar] size-5 shrink-0"></span>
                    <span>Schedule</span>
                    <span class="badge badge-soft badge-neutral badge-xs ml-auto">Soon</span>
                </a>
            </li>

            {{-- Members --}}
            <li>
                <a href="{{ route('backoffice.members.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-base-content/50 hover:bg-base-content/5 transition-colors">
                    <span class="icon-[tabler--users-group] size-5 shrink-0"></span>
                    <span>Members</span>
                    <span class="badge badge-soft badge-neutral badge-xs ml-auto">Soon</span>
                </a>
            </li>

            {{-- Section: Content --}}
            <li class="menu-title pt-4">
                <span class="text-xs font-semibold text-base-content/40 uppercase tracking-wider">Content</span>
            </li>

            {{-- Email Templates --}}
            <li>
                <a href="{{ route('backoffice.email-templates.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-colors {{ request()->routeIs('backoffice.email-templates.*') ? 'bg-primary/10 text-primary font-medium' : 'hover:bg-base-content/5' }}">
                    <span class="icon-[tabler--mail] size-5 shrink-0"></span>
                    <span>Email Templates</span>
                </a>
            </li>

            {{-- Translations --}}
            <li>
                <a href="{{ route('backoffice.translations.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-colors {{ request()->routeIs('backoffice.translations.*') ? 'bg-primary/10 text-primary font-medium' : 'hover:bg-base-content/5' }}">
                    <span class="icon-[tabler--language] size-5 shrink-0"></span>
                    <span>Translations</span>
                </a>
            </li>

            {{-- Section: Finance --}}
            <li class="menu-title pt-4">
                <span class="text-xs font-semibold text-base-content/40 uppercase tracking-wider">Finance</span>
            </li>

            {{-- Invoice (Coming Soon) --}}
            <li>
                <a href="{{ route('backoffice.invoice.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-base-content/50 hover:bg-base-content/5 transition-colors">
                    <span class="icon-[tabler--file-invoice] size-5 shrink-0"></span>
                    <span>Invoice</span>
                    <span class="badge badge-soft badge-neutral badge-xs ml-auto">Soon</span>
                </a>
            </li>

            {{-- Plans --}}
            <li>
                <a href="{{ route('backoffice.plans.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-colors {{ request()->routeIs('backoffice.plans.*') ? 'bg-primary/10 text-primary font-medium' : 'hover:bg-base-content/5' }}">
                    <span class="icon-[tabler--license] size-5 shrink-0"></span>
                    <span>Plans</span>
                </a>
            </li>

            {{-- Section: System --}}
            <li class="menu-title pt-4">
                <span class="text-xs font-semibold text-base-content/40 uppercase tracking-wider">System</span>
            </li>

            {{-- Settings --}}
            <li>
                <a href="{{ route('backoffice.settings.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-colors {{ request()->routeIs('backoffice.settings.*') ? 'bg-primary/10 text-primary font-medium' : 'hover:bg-base-content/5' }}">
                    <span class="icon-[tabler--settings] size-5 shrink-0"></span>
                    <span>Settings</span>
                </a>
            </li>

            {{-- Admin Members --}}
            <li>
                <a href="{{ route('backoffice.admin-members.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-colors {{ request()->routeIs('backoffice.admin-members.*') ? 'bg-primary/10 text-primary font-medium' : 'hover:bg-base-content/5' }}">
                    <span class="icon-[tabler--shield-check] size-5 shrink-0"></span>
                    <span>Admin Members</span>
                </a>
            </li>

            {{-- Email Logs --}}
            <li>
                <a href="{{ route('backoffice.email-logs.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-colors {{ request()->routeIs('backoffice.email-logs.*') ? 'bg-primary/10 text-primary font-medium' : 'hover:bg-base-content/5' }}">
                    <span class="icon-[tabler--mail-opened] size-5 shrink-0"></span>
                    <span>Email Logs</span>
                </a>
            </li>
        </ul>
    </div>

    {{-- Sidebar footer --}}
    <div class="border-t border-base-content/10 p-3">
        <div class="flex items-center gap-3 px-2 mb-2">
            <div class="avatar avatar-placeholder">
                <div class="bg-primary text-primary-content size-9 rounded-full text-sm font-bold">
                    {{ strtoupper(substr(Auth::guard('admin')->user()->first_name ?? 'A', 0, 1) . substr(Auth::guard('admin')->user()->last_name ?? 'D', 0, 1)) }}
                </div>
            </div>
            <div class="flex-1 min-w-0">
                <div class="text-sm font-semibold truncate">{{ Auth::guard('admin')->user()->first_name ?? 'Admin' }} {{ Auth::guard('admin')->user()->last_name ?? 'User' }}</div>
                <div class="text-xs text-base-content/50 truncate capitalize">{{ Auth::guard('admin')->user()->role ?? 'Administrator' }}</div>
            </div>
        </div>
        <form method="POST" action="{{ route('backoffice.logout') }}">
            @csrf
            <button type="submit" class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-error hover:bg-error/10 transition-colors">
                <span class="icon-[tabler--logout] size-5 shrink-0"></span>
                <span>Log Out</span>
            </button>
        </form>
    </div>
</aside>
