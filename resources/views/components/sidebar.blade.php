<aside id="main-sidebar" class="sticky top-0 h-screen bg-base-100 border-e border-base-content/10 flex flex-col overflow-hidden">

    {{-- Sidebar header: Logo + Collapse toggle --}}
    <div class="flex items-center gap-2 px-4 h-16 border-b border-base-content/10 shrink-0">
        <a href="{{ url('/dashboard') }}" class="flex items-center gap-2 no-underline">
            <span class="icon-[tabler--activity] size-7 text-primary shrink-0"></span>
            <span class="sidebar-logo-text text-xl font-bold text-base-content">FitCRM</span>
        </a>
        <button type="button" class="btn btn-ghost btn-xs btn-square ms-auto sidebar-label" id="sidebar-toggle" aria-label="Toggle sidebar">
            <span class="icon-[tabler--chevron-left] size-4"></span>
        </button>
    </div>

    {{-- Sidebar body --}}
    <div class="flex-1 overflow-y-auto px-3 py-4">
        <ul class="menu space-y-0.5 p-0">

            {{-- Section: Main --}}
            <li class="menu-title sidebar-section-label">
                <span class="text-xs font-semibold text-base-content/40 uppercase tracking-wider">Main</span>
            </li>

            {{-- Dashboard --}}
            <li class="nav-item {{ request()->is('dashboard*') ? 'active' : '' }}" data-nav="dashboard">
                <button type="button" class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm hover:bg-base-content/5 transition-colors" onclick="window.FitCRM.toggleSubmenu(this)">
                    <span class="icon-[tabler--home] size-5 shrink-0"></span>
                    <span class="sidebar-label flex-1 text-left">Dashboard</span>
                    <span class="icon-[tabler--chevron-down] size-4 sidebar-chevron transition-transform duration-200"></span>
                </button>
                <ul class="sidebar-submenu {{ request()->is('dashboard*') ? 'open' : '' }} pl-8 space-y-0.5 mt-0.5">
                    <li><a href="{{ url('/dashboard') }}" class="block px-3 py-1.5 rounded-md text-sm text-base-content/70 hover:bg-base-content/5 hover:text-base-content">
                        <span class="icon-[tabler--layout-dashboard] size-4 mr-2"></span>Overview
                    </a></li>
                    <li><a href="#" class="block px-3 py-1.5 rounded-md text-sm text-base-content/70 hover:bg-base-content/5 hover:text-base-content">
                        <span class="icon-[tabler--calendar-event] size-4 mr-2"></span>Today's Classes
                    </a></li>
                    <li><a href="#" class="block px-3 py-1.5 rounded-md text-sm text-base-content/70 hover:bg-base-content/5 hover:text-base-content">
                        <span class="icon-[tabler--book] size-4 mr-2"></span>Upcoming Bookings
                    </a></li>
                    <li><a href="#" class="block px-3 py-1.5 rounded-md text-sm text-base-content/70 hover:bg-base-content/5 hover:text-base-content">
                        <span class="icon-[tabler--bell] size-4 mr-2"></span>Alerts &amp; Reminders
                    </a></li>
                </ul>
            </li>

            {{-- Catalog --}}
            <li class="nav-item {{ request()->is('catalog*') || request()->is('class-plans*') || request()->is('service-plans*') ? 'active' : '' }}" data-nav="catalog">
                <a href="{{ url('/catalog') }}" class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm hover:bg-base-content/5 transition-colors">
                    <span class="icon-[tabler--layout-grid] size-5 shrink-0"></span>
                    <span class="sidebar-label">Catalog</span>
                </a>
            </li>

            {{-- Schedule --}}
            <li class="nav-item {{ request()->is('schedule*') || request()->is('service-slots*') ? 'active' : '' }}" data-nav="schedule">
                <button type="button" class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm hover:bg-base-content/5 transition-colors" onclick="window.FitCRM.toggleSubmenu(this)">
                    <span class="icon-[tabler--calendar] size-5 shrink-0"></span>
                    <span class="sidebar-label flex-1 text-left">Schedule</span>
                    <span class="icon-[tabler--chevron-down] size-4 sidebar-chevron transition-transform duration-200"></span>
                </button>
                <ul class="sidebar-submenu {{ request()->is('schedule*') || request()->is('service-slots*') ? 'open' : '' }} pl-8 space-y-0.5 mt-0.5">
                    <li><a href="{{ url('/schedule/calendar') }}" class="block px-3 py-1.5 rounded-md text-sm text-base-content/70 hover:bg-base-content/5 hover:text-base-content {{ request()->is('schedule/calendar') ? 'bg-primary/10 text-primary' : '' }}">
                        <span class="icon-[tabler--calendar-month] size-4 mr-2"></span>Calendar View
                    </a></li>
                    <li><a href="{{ url('/schedule/classes') }}" class="block px-3 py-1.5 rounded-md text-sm text-base-content/70 hover:bg-base-content/5 hover:text-base-content {{ request()->is('schedule/classes') ? 'bg-primary/10 text-primary' : '' }}">
                        <span class="icon-[tabler--list] size-4 mr-2"></span>Classes
                    </a></li>
                    <li><a href="{{ url('/service-slots') }}" class="block px-3 py-1.5 rounded-md text-sm text-base-content/70 hover:bg-base-content/5 hover:text-base-content {{ request()->is('service-slots*') ? 'bg-primary/10 text-primary' : '' }}">
                        <span class="icon-[tabler--calendar-event] size-4 mr-2"></span>Service Slots
                    </a></li>
                    <li><a href="#" class="block px-3 py-1.5 rounded-md text-sm text-base-content/70 hover:bg-base-content/5 hover:text-base-content">
                        <span class="icon-[tabler--hourglass] size-4 mr-2"></span>Waitlist
                    </a></li>
                </ul>
            </li>

            {{-- Bookings --}}
            <li class="nav-item {{ request()->is('bookings*') ? 'active' : '' }}" data-nav="bookings">
                <button type="button" class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm hover:bg-base-content/5 transition-colors" onclick="window.FitCRM.toggleSubmenu(this)">
                    <span class="icon-[tabler--book] size-5 shrink-0"></span>
                    <span class="sidebar-label flex-1 text-left">Bookings</span>
                    <span class="icon-[tabler--chevron-down] size-4 sidebar-chevron transition-transform duration-200"></span>
                </button>
                <ul class="sidebar-submenu {{ request()->is('bookings*') ? 'open' : '' }} pl-8 space-y-0.5 mt-0.5">
                    <li><a href="#" class="block px-3 py-1.5 rounded-md text-sm text-base-content/70 hover:bg-base-content/5 hover:text-base-content">
                        <span class="icon-[tabler--clipboard-list] size-4 mr-2"></span>All Bookings
                    </a></li>
                    <li><a href="#" class="block px-3 py-1.5 rounded-md text-sm text-base-content/70 hover:bg-base-content/5 hover:text-base-content">
                        <span class="icon-[tabler--clock] size-4 mr-2"></span>Upcoming
                    </a></li>
                    <li><a href="#" class="block px-3 py-1.5 rounded-md text-sm text-base-content/70 hover:bg-base-content/5 hover:text-base-content">
                        <span class="icon-[tabler--circle-x] size-4 mr-2"></span>Cancellations
                    </a></li>
                    <li><a href="#" class="block px-3 py-1.5 rounded-md text-sm text-base-content/70 hover:bg-base-content/5 hover:text-base-content">
                        <span class="icon-[tabler--user-x] size-4 mr-2"></span>No-Shows
                    </a></li>
                </ul>
            </li>

            {{-- Students --}}
            <li class="nav-item {{ request()->is('students*') ? 'active' : '' }}" data-nav="students">
                <button type="button" class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm hover:bg-base-content/5 transition-colors" onclick="window.FitCRM.toggleSubmenu(this)">
                    <span class="icon-[tabler--users] size-5 shrink-0"></span>
                    <span class="sidebar-label flex-1 text-left">Students</span>
                    <span class="icon-[tabler--chevron-down] size-4 sidebar-chevron transition-transform duration-200"></span>
                </button>
                <ul class="sidebar-submenu {{ request()->is('students*') ? 'open' : '' }} pl-8 space-y-0.5 mt-0.5">
                    <li><a href="{{ url('/students') }}" class="block px-3 py-1.5 rounded-md text-sm text-base-content/70 hover:bg-base-content/5 hover:text-base-content">
                        <span class="icon-[tabler--users] size-4 mr-2"></span>All Students
                    </a></li>
                    <li><a href="#" class="block px-3 py-1.5 rounded-md text-sm text-base-content/70 hover:bg-base-content/5 hover:text-base-content">
                        <span class="icon-[tabler--target] size-4 mr-2"></span>Leads
                    </a></li>
                    <li><a href="#" class="block px-3 py-1.5 rounded-md text-sm text-base-content/70 hover:bg-base-content/5 hover:text-base-content">
                        <span class="icon-[tabler--user-check] size-4 mr-2"></span>Active Members
                    </a></li>
                    <li><a href="#" class="block px-3 py-1.5 rounded-md text-sm text-base-content/70 hover:bg-base-content/5 hover:text-base-content">
                        <span class="icon-[tabler--alert-triangle] size-4 mr-2"></span>At-Risk
                    </a></li>
                    <li><a href="#" class="block px-3 py-1.5 rounded-md text-sm text-base-content/70 hover:bg-base-content/5 hover:text-base-content">
                        <span class="icon-[tabler--tags] size-4 mr-2"></span>Tags
                    </a></li>
                </ul>
            </li>

            {{-- Instructors --}}
            <li class="nav-item {{ request()->is('instructors*') ? 'active' : '' }}" data-nav="instructors">
                <button type="button" class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm hover:bg-base-content/5 transition-colors" onclick="window.FitCRM.toggleSubmenu(this)">
                    <span class="icon-[tabler--user-star] size-5 shrink-0"></span>
                    <span class="sidebar-label flex-1 text-left">Instructors</span>
                    <span class="icon-[tabler--chevron-down] size-4 sidebar-chevron transition-transform duration-200"></span>
                </button>
                <ul class="sidebar-submenu {{ request()->is('instructors*') ? 'open' : '' }} pl-8 space-y-0.5 mt-0.5">
                    <li><a href="{{ url('/instructors') }}" class="block px-3 py-1.5 rounded-md text-sm text-base-content/70 hover:bg-base-content/5 hover:text-base-content">
                        <span class="icon-[tabler--list] size-4 mr-2"></span>Instructor List
                    </a></li>
                    <li><a href="#" class="block px-3 py-1.5 rounded-md text-sm text-base-content/70 hover:bg-base-content/5 hover:text-base-content">
                        <span class="icon-[tabler--clock] size-4 mr-2"></span>Availability
                    </a></li>
                    <li><a href="#" class="block px-3 py-1.5 rounded-md text-sm text-base-content/70 hover:bg-base-content/5 hover:text-base-content">
                        <span class="icon-[tabler--clipboard] size-4 mr-2"></span>Assignments
                    </a></li>
                    <li><a href="#" class="block px-3 py-1.5 rounded-md text-sm text-base-content/70 hover:bg-base-content/5 hover:text-base-content">
                        <span class="icon-[tabler--wallet] size-4 mr-2 opacity-50"></span><span class="opacity-50">Payouts</span> <span class="badge badge-xs badge-soft badge-neutral ml-1">Later</span>
                    </a></li>
                </ul>
            </li>

            {{-- Section: Commerce --}}
            <li class="menu-title sidebar-section-label pt-4">
                <span class="text-xs font-semibold text-base-content/40 uppercase tracking-wider">Commerce</span>
            </li>

            {{-- Offers --}}
            <li class="nav-item {{ request()->is('offers*') ? 'active' : '' }}" data-nav="offers">
                <button type="button" class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm hover:bg-base-content/5 transition-colors" onclick="window.FitCRM.toggleSubmenu(this)">
                    <span class="icon-[tabler--gift] size-5 shrink-0"></span>
                    <span class="sidebar-label flex-1 text-left">Offers</span>
                    <span class="icon-[tabler--chevron-down] size-4 sidebar-chevron transition-transform duration-200"></span>
                </button>
                <ul class="sidebar-submenu {{ request()->is('offers*') ? 'open' : '' }} pl-8 space-y-0.5 mt-0.5">
                    <li><a href="#" class="block px-3 py-1.5 rounded-md text-sm text-base-content/70 hover:bg-base-content/5 hover:text-base-content">
                        <span class="icon-[tabler--star] size-4 mr-2"></span>Intro Offers
                    </a></li>
                    <li><a href="#" class="block px-3 py-1.5 rounded-md text-sm text-base-content/70 hover:bg-base-content/5 hover:text-base-content">
                        <span class="icon-[tabler--package] size-4 mr-2"></span>Class Packs
                    </a></li>
                    <li><a href="#" class="block px-3 py-1.5 rounded-md text-sm text-base-content/70 hover:bg-base-content/5 hover:text-base-content">
                        <span class="icon-[tabler--id-badge] size-4 mr-2"></span>Memberships
                    </a></li>
                    <li><a href="#" class="block px-3 py-1.5 rounded-md text-sm text-base-content/70 hover:bg-base-content/5 hover:text-base-content">
                        <span class="icon-[tabler--percentage] size-4 mr-2"></span>Promo Codes
                    </a></li>
                </ul>
            </li>

            {{-- Insights --}}
            <li class="nav-item {{ request()->is('insights*') || request()->is('reports*') ? 'active' : '' }}" data-nav="insights">
                <button type="button" class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm hover:bg-base-content/5 transition-colors" onclick="window.FitCRM.toggleSubmenu(this)">
                    <span class="icon-[tabler--chart-bar] size-5 shrink-0"></span>
                    <span class="sidebar-label flex-1 text-left">Insights</span>
                    <span class="icon-[tabler--chevron-down] size-4 sidebar-chevron transition-transform duration-200"></span>
                </button>
                <ul class="sidebar-submenu {{ request()->is('insights*') || request()->is('reports*') ? 'open' : '' }} pl-8 space-y-0.5 mt-0.5">
                    <li><a href="{{ url('/reports') }}" class="block px-3 py-1.5 rounded-md text-sm text-base-content/70 hover:bg-base-content/5 hover:text-base-content">
                        <span class="icon-[tabler--checks] size-4 mr-2"></span>Attendance
                    </a></li>
                    <li><a href="#" class="block px-3 py-1.5 rounded-md text-sm text-base-content/70 hover:bg-base-content/5 hover:text-base-content">
                        <span class="icon-[tabler--coin] size-4 mr-2"></span>Revenue
                    </a></li>
                    <li><a href="#" class="block px-3 py-1.5 rounded-md text-sm text-base-content/70 hover:bg-base-content/5 hover:text-base-content">
                        <span class="icon-[tabler--trophy] size-4 mr-2"></span>Class Performance
                    </a></li>
                    <li><a href="#" class="block px-3 py-1.5 rounded-md text-sm text-base-content/70 hover:bg-base-content/5 hover:text-base-content">
                        <span class="icon-[tabler--trending-up] size-4 mr-2"></span>Retention
                    </a></li>
                </ul>
            </li>

            {{-- Payments --}}
            <li class="nav-item {{ request()->is('payments*') ? 'active' : '' }}" data-nav="payments">
                <button type="button" class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm hover:bg-base-content/5 transition-colors" onclick="window.FitCRM.toggleSubmenu(this)">
                    <span class="icon-[tabler--credit-card] size-5 shrink-0"></span>
                    <span class="sidebar-label flex-1 text-left">Payments</span>
                    <span class="icon-[tabler--chevron-down] size-4 sidebar-chevron transition-transform duration-200"></span>
                </button>
                <ul class="sidebar-submenu {{ request()->is('payments*') ? 'open' : '' }} pl-8 space-y-0.5 mt-0.5">
                    <li><a href="{{ url('/payments/transactions') }}" class="block px-3 py-1.5 rounded-md text-sm text-base-content/70 hover:bg-base-content/5 hover:text-base-content">
                        <span class="icon-[tabler--receipt] size-4 mr-2"></span>Transactions
                    </a></li>
                    <li><a href="#" class="block px-3 py-1.5 rounded-md text-sm text-base-content/70 hover:bg-base-content/5 hover:text-base-content">
                        <span class="icon-[tabler--repeat] size-4 mr-2"></span>Subscriptions
                    </a></li>
                    <li><a href="#" class="block px-3 py-1.5 rounded-md text-sm text-base-content/70 hover:bg-base-content/5 hover:text-base-content">
                        <span class="icon-[tabler--wallet] size-4 mr-2"></span>Payouts
                    </a></li>
                    <li><a href="#" class="block px-3 py-1.5 rounded-md text-sm text-base-content/70 hover:bg-base-content/5 hover:text-base-content">
                        <span class="icon-[tabler--arrow-back-up] size-4 mr-2"></span>Refunds
                    </a></li>
                </ul>
            </li>

            {{-- Section: System --}}
            <li class="menu-title sidebar-section-label pt-4">
                <span class="text-xs font-semibold text-base-content/40 uppercase tracking-wider">System</span>
            </li>

            {{-- Settings --}}
            <li class="nav-item {{ request()->is('settings*') ? 'active' : '' }}" data-nav="settings">
                <a href="{{ url('/settings') }}" class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm hover:bg-base-content/5 transition-colors">
                    <span class="icon-[tabler--settings] size-5 shrink-0"></span>
                    <span class="sidebar-label">Settings</span>
                </a>
            </li>
        </ul>
    </div>

    {{-- Sidebar footer --}}
    <div class="border-t border-base-content/10 p-3 space-y-2">
        <div class="flex items-center gap-3 px-2">
            <div class="avatar avatar-placeholder">
                <div class="bg-primary text-primary-content size-9 rounded-full text-sm font-bold">
                    {{ strtoupper(substr(Auth::user()->first_name ?? 'F', 0, 1) . substr(Auth::user()->last_name ?? 'C', 0, 1)) }}
                </div>
            </div>
            <div class="sidebar-footer-detail flex-1 min-w-0">
                <div class="text-sm font-semibold truncate">{{ Auth::user()->host->studio_name ?? 'My Studio' }}</div>
                <div class="text-xs text-base-content/50 truncate">{{ Auth::user()->host->subdomain ?? 'my-studio' }}.fitcrm.app</div>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-error hover:bg-error/10 transition-colors">
                <span class="icon-[tabler--logout] size-5 shrink-0"></span>
                <span class="sidebar-label">Sign Out</span>
            </button>
        </form>
    </div>
</aside>
