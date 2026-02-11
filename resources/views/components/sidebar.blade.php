@php
    $user = auth()->user();
    $canViewSchedule = $user->hasPermission('schedule.view') || $user->hasPermission('schedule.view_own');
    $canManageSchedule = $user->hasPermission('schedule.create') || $user->hasPermission('schedule.edit');
    $canViewBookings = $user->hasPermission('bookings.view') || $user->hasPermission('bookings.view_own');
    $canViewClients = $user->hasPermission('students.view'); // Permission key kept as students.view for backward compatibility
    $canViewTeam = $user->hasPermission('team.view') || $user->hasPermission('team.instructors');
    $canViewOffers = $user->hasPermission('offers.intro') || $user->hasPermission('offers.packs') || $user->hasPermission('offers.memberships') || $user->hasPermission('offers.promos');
    $canViewInsights = $user->hasPermission('insights.attendance') || $user->hasPermission('insights.revenue');
    $canViewPayments = $user->hasPermission('payments.view');
    $canAccessSettings = $user->hasPermission('studio.profile') || $user->hasPermission('team.view') || $user->hasPermission('billing.plan');
@endphp
<aside id="main-sidebar" class="sticky top-0 h-screen bg-base-100 border-e border-base-content/10 flex flex-col">

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

            {{-- Dashboard - Everyone can see --}}
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
                    @if($canViewSchedule)
                    <li><a href="#" class="block px-3 py-1.5 rounded-md text-sm text-base-content/70 hover:bg-base-content/5 hover:text-base-content">
                        <span class="icon-[tabler--calendar-event] size-4 mr-2"></span>Today's Classes
                    </a></li>
                    @endif
                    @if($canViewBookings)
                    <li><a href="#" class="block px-3 py-1.5 rounded-md text-sm text-base-content/70 hover:bg-base-content/5 hover:text-base-content">
                        <span class="icon-[tabler--book] size-4 mr-2"></span>Upcoming Bookings
                    </a></li>
                    @endif
                    <li><a href="#" class="block px-3 py-1.5 rounded-md text-sm text-base-content/70 hover:bg-base-content/5 hover:text-base-content">
                        <span class="icon-[tabler--bell] size-4 mr-2"></span>Alerts &amp; Reminders
                    </a></li>
                </ul>
            </li>

            {{-- Catalog - Requires schedule permissions --}}
            @if($canManageSchedule)
            <li class="nav-item {{ request()->is('catalog*') || request()->is('class-plans*') || request()->is('service-plans*') ? 'active' : '' }}" data-nav="catalog">
                <a href="{{ url('/catalog') }}" class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm hover:bg-base-content/5 transition-colors">
                    <span class="icon-[tabler--layout-grid] size-5 shrink-0"></span>
                    <span class="sidebar-label">Catalog</span>
                </a>
            </li>
            @endif

            {{-- Schedule - Requires schedule.view or schedule.view_own --}}
            @if($canViewSchedule)
            <li class="nav-item {{ request()->is('schedule*') || request()->is('service-slots*') || request()->is('class-sessions*') || request()->is('class-requests*') ? 'active' : '' }}" data-nav="schedule">
                <button type="button" class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm hover:bg-base-content/5 transition-colors" onclick="window.FitCRM.toggleSubmenu(this)">
                    <span class="icon-[tabler--calendar] size-5 shrink-0"></span>
                    <span class="sidebar-label flex-1 text-left">Schedule</span>
                    <span class="icon-[tabler--chevron-down] size-4 sidebar-chevron transition-transform duration-200"></span>
                </button>
                <ul class="sidebar-submenu {{ request()->is('schedule*') || request()->is('service-slots*') || request()->is('class-sessions*') || request()->is('class-requests*') ? 'open' : '' }} pl-8 space-y-0.5 mt-0.5">
                    <li><a href="{{ url('/schedule/calendar') }}" class="block px-3 py-1.5 rounded-md text-sm text-base-content/70 hover:bg-base-content/5 hover:text-base-content {{ request()->is('schedule/calendar') ? 'bg-primary/10 text-primary' : '' }}">
                        <span class="icon-[tabler--calendar-month] size-4 mr-2"></span>Calendar View
                    </a></li>
                    <li><a href="{{ url('/class-sessions') }}" class="block px-3 py-1.5 rounded-md text-sm text-base-content/70 hover:bg-base-content/5 hover:text-base-content {{ request()->is('class-sessions*') ? 'bg-primary/10 text-primary' : '' }}">
                        <span class="icon-[tabler--calendar-event] size-4 mr-2"></span>Class Sessions
                    </a></li>
                    @if($user->hasPermission('schedule.view'))
                    <li><a href="{{ url('/service-slots') }}" class="block px-3 py-1.5 rounded-md text-sm text-base-content/70 hover:bg-base-content/5 hover:text-base-content {{ request()->is('service-slots*') ? 'bg-primary/10 text-primary' : '' }}">
                        <span class="icon-[tabler--clock] size-4 mr-2"></span>Service Slots
                    </a></li>
                    <li><a href="{{ url('/class-requests') }}" class="block px-3 py-1.5 rounded-md text-sm text-base-content/70 hover:bg-base-content/5 hover:text-base-content {{ request()->is('class-requests*') ? 'bg-primary/10 text-primary' : '' }}">
                        <span class="icon-[tabler--message-circle-question] size-4 mr-2"></span>Requests
                        @php $pendingRequests = auth()->user()?->host?->classRequests()->pending()->count() ?? 0; @endphp
                        @if($pendingRequests > 0)
                        <span class="badge badge-xs badge-warning ml-1">{{ $pendingRequests }}</span>
                        @endif
                    </a></li>
                    @endif
                    @if($user->hasPermission('bookings.waitlist'))
                    <li><a href="#" class="block px-3 py-1.5 rounded-md text-sm text-base-content/70 hover:bg-base-content/5 hover:text-base-content">
                        <span class="icon-[tabler--hourglass] size-4 mr-2"></span>Waitlist
                    </a></li>
                    @endif
                </ul>
            </li>
            @endif

            {{-- Bookings - Requires bookings.view or bookings.view_own --}}
            @if($canViewBookings)
            <li class="nav-item {{ request()->is('bookings*') ? 'active' : '' }}" data-nav="bookings">
                <button type="button" class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm hover:bg-base-content/5 transition-colors" onclick="window.FitCRM.toggleSubmenu(this)">
                    <span class="icon-[tabler--book] size-5 shrink-0"></span>
                    <span class="sidebar-label flex-1 text-left">Bookings</span>
                    <span class="icon-[tabler--chevron-down] size-4 sidebar-chevron transition-transform duration-200"></span>
                </button>
                <ul class="sidebar-submenu {{ request()->is('bookings*') ? 'open' : '' }} pl-8 space-y-0.5 mt-0.5">
                    <li><a href="#" class="block px-3 py-1.5 rounded-md text-sm text-base-content/70 hover:bg-base-content/5 hover:text-base-content">
                        <span class="icon-[tabler--clipboard-list] size-4 mr-2"></span>{{ $user->hasPermission('bookings.view') ? 'All Bookings' : 'My Class Bookings' }}
                    </a></li>
                    <li><a href="#" class="block px-3 py-1.5 rounded-md text-sm text-base-content/70 hover:bg-base-content/5 hover:text-base-content">
                        <span class="icon-[tabler--clock] size-4 mr-2"></span>Upcoming
                    </a></li>
                    @if($user->hasPermission('bookings.view'))
                    <li><a href="#" class="block px-3 py-1.5 rounded-md text-sm text-base-content/70 hover:bg-base-content/5 hover:text-base-content">
                        <span class="icon-[tabler--circle-x] size-4 mr-2"></span>Cancellations
                    </a></li>
                    <li><a href="#" class="block px-3 py-1.5 rounded-md text-sm text-base-content/70 hover:bg-base-content/5 hover:text-base-content">
                        <span class="icon-[tabler--user-x] size-4 mr-2"></span>No-Shows
                    </a></li>
                    @endif
                </ul>
            </li>
            @endif

            {{-- Clients - Requires students.view (renamed from Students) --}}
            @if($canViewClients)
            <li class="nav-item {{ request()->is('clients*') ? 'active' : '' }}" data-nav="clients">
                <button type="button" class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm hover:bg-base-content/5 transition-colors" onclick="window.FitCRM.toggleSubmenu(this)">
                    <span class="icon-[tabler--users] size-5 shrink-0"></span>
                    <span class="sidebar-label flex-1 text-left">Clients</span>
                    <span class="icon-[tabler--chevron-down] size-4 sidebar-chevron transition-transform duration-200"></span>
                </button>
                <ul class="sidebar-submenu {{ request()->is('clients*') ? 'open' : '' }} pl-8 space-y-0.5 mt-0.5">
                    <li><a href="{{ url('/clients') }}" class="block px-3 py-1.5 rounded-md text-sm text-base-content/70 hover:bg-base-content/5 hover:text-base-content {{ request()->is('clients') && !request()->is('clients/*') ? 'bg-primary/10 text-primary' : '' }}">
                        <span class="icon-[tabler--users] size-4 mr-2"></span>All Clients
                    </a></li>
                    <li><a href="{{ url('/clients/leads') }}" class="block px-3 py-1.5 rounded-md text-sm text-base-content/70 hover:bg-base-content/5 hover:text-base-content {{ request()->is('clients/leads*') ? 'bg-primary/10 text-primary' : '' }}">
                        <span class="icon-[tabler--target] size-4 mr-2"></span>Leads
                    </a></li>
                    <li><a href="{{ url('/clients/members') }}" class="block px-3 py-1.5 rounded-md text-sm text-base-content/70 hover:bg-base-content/5 hover:text-base-content {{ request()->is('clients/members*') ? 'bg-primary/10 text-primary' : '' }}">
                        <span class="icon-[tabler--user-check] size-4 mr-2"></span>Members
                    </a></li>
                    <li><a href="{{ url('/clients/at-risk') }}" class="block px-3 py-1.5 rounded-md text-sm text-base-content/70 hover:bg-base-content/5 hover:text-base-content {{ request()->is('clients/at-risk*') ? 'bg-primary/10 text-primary' : '' }}">
                        <span class="icon-[tabler--alert-triangle] size-4 mr-2"></span>At-Risk
                    </a></li>
                    <li><a href="{{ url('/clients/tags') }}" class="block px-3 py-1.5 rounded-md text-sm text-base-content/70 hover:bg-base-content/5 hover:text-base-content {{ request()->is('clients/tags*') ? 'bg-primary/10 text-primary' : '' }}">
                        <span class="icon-[tabler--tags] size-4 mr-2"></span>Tags
                    </a></li>
                    <li><a href="{{ url('/clients/lead-magnet') }}" class="block px-3 py-1.5 rounded-md text-sm text-base-content/70 hover:bg-base-content/5 hover:text-base-content {{ request()->is('clients/lead-magnet*') ? 'bg-primary/10 text-primary' : '' }}">
                        <span class="icon-[tabler--magnet] size-4 mr-2"></span>Lead Magnet
                        <span class="badge badge-xs badge-soft badge-neutral ml-1">Soon</span>
                    </a></li>
                </ul>
            </li>
            @endif

            {{-- Help Desk - Requires students.view permission (same as clients) --}}
            @if($canViewClients)
            @php
                $openTicketCount = auth()->user()?->host?->helpdeskTickets()->unresolved()->count() ?? 0;
            @endphp
            <li class="nav-item {{ request()->is('helpdesk*') ? 'active' : '' }}" data-nav="helpdesk">
                <a href="{{ url('/helpdesk') }}" class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm hover:bg-base-content/5 transition-colors">
                    <span class="icon-[tabler--help] size-5 shrink-0"></span>
                    <span class="sidebar-label flex-1 text-left">Help Desk</span>
                    @if($openTicketCount > 0)
                        <span class="badge badge-xs badge-primary">{{ $openTicketCount }}</span>
                    @endif
                </a>
            </li>
            @endif

            {{-- Instructors - Requires team.view or team.instructors --}}
            @if($canViewTeam)
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
            @endif

            {{-- Section: Commerce - Only show if user has any commerce permissions --}}
            @if($canViewOffers || $canViewInsights || $canViewPayments)
            <li class="menu-title sidebar-section-label pt-4">
                <span class="text-xs font-semibold text-base-content/40 uppercase tracking-wider">Commerce</span>
            </li>

            {{-- Offers - Requires any offer permission --}}
            @if($canViewOffers)
            <li class="nav-item {{ request()->is('offers*') ? 'active' : '' }}" data-nav="offers">
                <button type="button" class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm hover:bg-base-content/5 transition-colors" onclick="window.FitCRM.toggleSubmenu(this)">
                    <span class="icon-[tabler--gift] size-5 shrink-0"></span>
                    <span class="sidebar-label flex-1 text-left">Offers</span>
                    <span class="icon-[tabler--chevron-down] size-4 sidebar-chevron transition-transform duration-200"></span>
                </button>
                <ul class="sidebar-submenu {{ request()->is('offers*') ? 'open' : '' }} pl-8 space-y-0.5 mt-0.5">
                    @if($user->hasPermission('offers.intro'))
                    <li><a href="#" class="block px-3 py-1.5 rounded-md text-sm text-base-content/70 hover:bg-base-content/5 hover:text-base-content">
                        <span class="icon-[tabler--star] size-4 mr-2"></span>Intro Offers
                    </a></li>
                    @endif
                    @if($user->hasPermission('offers.packs'))
                    <li><a href="#" class="block px-3 py-1.5 rounded-md text-sm text-base-content/70 hover:bg-base-content/5 hover:text-base-content">
                        <span class="icon-[tabler--package] size-4 mr-2"></span>Class Packs
                    </a></li>
                    @endif
                    @if($user->hasPermission('offers.memberships'))
                    <li><a href="#" class="block px-3 py-1.5 rounded-md text-sm text-base-content/70 hover:bg-base-content/5 hover:text-base-content">
                        <span class="icon-[tabler--id-badge] size-4 mr-2"></span>Memberships
                    </a></li>
                    @endif
                    @if($user->hasPermission('offers.promos'))
                    <li><a href="#" class="block px-3 py-1.5 rounded-md text-sm text-base-content/70 hover:bg-base-content/5 hover:text-base-content">
                        <span class="icon-[tabler--percentage] size-4 mr-2"></span>Promo Codes
                    </a></li>
                    @endif
                </ul>
            </li>
            @endif

            {{-- Insights - Requires insights permissions --}}
            @if($canViewInsights)
            <li class="nav-item {{ request()->is('insights*') || request()->is('reports*') ? 'active' : '' }}" data-nav="insights">
                <button type="button" class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm hover:bg-base-content/5 transition-colors" onclick="window.FitCRM.toggleSubmenu(this)">
                    <span class="icon-[tabler--chart-bar] size-5 shrink-0"></span>
                    <span class="sidebar-label flex-1 text-left">Insights</span>
                    <span class="icon-[tabler--chevron-down] size-4 sidebar-chevron transition-transform duration-200"></span>
                </button>
                <ul class="sidebar-submenu {{ request()->is('insights*') || request()->is('reports*') ? 'open' : '' }} pl-8 space-y-0.5 mt-0.5">
                    @if($user->hasPermission('insights.attendance'))
                    <li><a href="{{ url('/reports') }}" class="block px-3 py-1.5 rounded-md text-sm text-base-content/70 hover:bg-base-content/5 hover:text-base-content">
                        <span class="icon-[tabler--checks] size-4 mr-2"></span>Attendance
                    </a></li>
                    @endif
                    @if($user->hasPermission('insights.revenue'))
                    <li><a href="#" class="block px-3 py-1.5 rounded-md text-sm text-base-content/70 hover:bg-base-content/5 hover:text-base-content">
                        <span class="icon-[tabler--coin] size-4 mr-2"></span>Revenue
                    </a></li>
                    @endif
                    <li><a href="#" class="block px-3 py-1.5 rounded-md text-sm text-base-content/70 hover:bg-base-content/5 hover:text-base-content">
                        <span class="icon-[tabler--trophy] size-4 mr-2"></span>Class Performance
                    </a></li>
                    <li><a href="#" class="block px-3 py-1.5 rounded-md text-sm text-base-content/70 hover:bg-base-content/5 hover:text-base-content">
                        <span class="icon-[tabler--trending-up] size-4 mr-2"></span>Retention
                    </a></li>
                </ul>
            </li>
            @endif

            {{-- Payments - Requires payments.view --}}
            @if($canViewPayments)
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
                    @if($user->hasPermission('payments.payouts'))
                    <li><a href="#" class="block px-3 py-1.5 rounded-md text-sm text-base-content/70 hover:bg-base-content/5 hover:text-base-content">
                        <span class="icon-[tabler--wallet] size-4 mr-2"></span>Payouts
                    </a></li>
                    @endif
                    @if($user->hasPermission('payments.refunds'))
                    <li><a href="#" class="block px-3 py-1.5 rounded-md text-sm text-base-content/70 hover:bg-base-content/5 hover:text-base-content">
                        <span class="icon-[tabler--arrow-back-up] size-4 mr-2"></span>Refunds
                    </a></li>
                    @endif
                </ul>
            </li>
            @endif
            @endif

            {{-- Section: System - Always visible (My Profile is accessible to all) --}}
            <li class="menu-title sidebar-section-label pt-4">
                <span class="text-xs font-semibold text-base-content/40 uppercase tracking-wider">System</span>
            </li>

            {{-- Settings - Always visible since My Profile is accessible to all --}}
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
        @php
            $currentHost = Auth::user()->currentHost() ?? Auth::user()->host;
            $hasMultipleHosts = Auth::user()->hosts()->count() > 1;
        @endphp

        <div class="flex items-center gap-3 px-2">
            <div class="avatar avatar-placeholder">
                <div class="bg-primary text-primary-content size-9 rounded-full text-sm font-bold">
                    {{ strtoupper(substr($currentHost->studio_name ?? 'S', 0, 1)) }}
                </div>
            </div>
            <div class="sidebar-footer-detail flex-1 min-w-0">
                <div class="text-sm font-semibold truncate">{{ $currentHost->studio_name ?? 'My Studio' }}</div>
                <div class="text-xs text-base-content/50 truncate">{{ $currentHost->subdomain ?? 'my-studio' }}.fitcrm.app</div>
            </div>
            @if($hasMultipleHosts)
                <a href="{{ route('select-studio') }}" class="btn btn-ghost btn-sm btn-square" title="Switch Studio">
                    <span class="icon-[tabler--switch-horizontal] size-5"></span>
                </a>
            @endif
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
