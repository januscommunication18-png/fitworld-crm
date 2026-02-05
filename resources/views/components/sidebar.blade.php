<aside id="dashboard-sidebar"
    class="overlay overlay-minified:w-17 [--body-scroll:true] border-base-content/20 overlay-open:translate-x-0 drawer drawer-start sm:overlay-layout-open:translate-x-0 hidden w-64 border-e [--auto-close:sm] [--is-layout-affect:true] [--opened:lg] sm:absolute sm:z-0 sm:flex sm:shadow-none lg:[--overlay-backdrop:false]"
    role="dialog" tabindex="-1">

    {{-- Sidebar header --}}
    <div class="drawer-header overlay-minified:px-3.75 py-2 w-full flex items-center justify-between gap-3">
        <a href="{{ url('/dashboard') }}" class="link link-neutral text-xl font-bold no-underline overlay-minified:hidden">
            FitCRM
        </a>
        <div class="hidden sm:block">
            <button type="button" class="btn btn-circle btn-text"
                aria-haspopup="dialog" aria-expanded="false"
                aria-controls="dashboard-sidebar"
                aria-label="Toggle sidebar"
                data-overlay-minifier="#dashboard-sidebar">
                <span class="icon-[tabler--menu-2] size-5"></span>
            </button>
        </div>
    </div>

    {{-- Sidebar body --}}
    <div class="drawer-body px-2 pt-2">
        <ul class="menu space-y-0.5 p-0">

            {{-- Dashboard --}}
            <li>
                <a href="{{ url('/dashboard') }}" class="{{ request()->is('dashboard') ? 'bg-base-content/10' : '' }}">
                    <span class="icon-[tabler--home] size-5"></span>
                    <span class="overlay-minified:hidden">Dashboard</span>
                </a>
            </li>

            {{-- Schedule (collapse submenu) --}}
            <li class="space-y-0.5 dropdown relative [--adaptive:none] [--strategy:static] overlay-minified:[--adaptive:adaptive] overlay-minified:[--strategy:fixed] overlay-minified:[--offset:15] overlay-minified:[--trigger:hover] overlay-minified:[--placement:right-start]">
                <button id="sidebar-schedule" type="button"
                    class="dropdown-toggle collapse-toggle collapse-open:bg-base-content/10 overlay-minified:collapse-open:bg-transparent"
                    data-collapse="#sidebar-schedule-collapse"
                    aria-haspopup="menu" aria-expanded="false">
                    <span class="icon-[tabler--calendar] size-5"></span>
                    <span class="overlay-minified:hidden">Schedule</span>
                    <span class="icon-[tabler--chevron-down] collapse-open:rotate-180 size-4 overlay-minified:hidden transition-all duration-300"></span>
                </button>
                <ul id="sidebar-schedule-collapse"
                    class="dropdown-menu mt-0 shadow-none overlay-minified:shadow-md overlay-minified:shadow-base-300/20 dropdown-open:opacity-100 hidden min-w-48 collapse w-auto space-y-0.5 overflow-hidden transition-[height] duration-300 overlay-minified:before:absolute overlay-minified:before:-start-4 overlay-minified:before:top-0 overlay-minified:before:h-full overlay-minified:before:w-4 before:bg-transparent"
                    role="menu" aria-orientation="vertical" aria-labelledby="sidebar-schedule">
                    <li><a href="{{ url('/schedule/classes') }}">
                        <span class="icon-[tabler--calendar-event] size-5"></span>
                        <span>Class Schedule</span>
                    </a></li>
                    <li><a href="{{ url('/schedule/appointments') }}">
                        <span class="icon-[tabler--calendar-check] size-5"></span>
                        <span>Appointments</span>
                    </a></li>
                    <li><a href="{{ url('/schedule/calendar') }}">
                        <span class="icon-[tabler--calendar-month] size-5"></span>
                        <span>Calendar View</span>
                    </a></li>
                </ul>
            </li>

            {{-- Students --}}
            <li>
                <a href="{{ url('/students') }}" class="{{ request()->is('students*') ? 'bg-base-content/10' : '' }}">
                    <span class="icon-[tabler--users] size-5"></span>
                    <span class="overlay-minified:hidden">Students</span>
                </a>
            </li>

            {{-- Instructors --}}
            <li>
                <a href="{{ url('/instructors') }}" class="{{ request()->is('instructors*') ? 'bg-base-content/10' : '' }}">
                    <span class="icon-[tabler--user-star] size-5"></span>
                    <span class="overlay-minified:hidden">Instructors</span>
                </a>
            </li>

            {{-- Payments (collapse submenu) --}}
            <li class="space-y-0.5 dropdown relative [--adaptive:none] [--strategy:static] overlay-minified:[--adaptive:adaptive] overlay-minified:[--strategy:fixed] overlay-minified:[--offset:15] overlay-minified:[--trigger:hover] overlay-minified:[--placement:right-start]">
                <button id="sidebar-payments" type="button"
                    class="dropdown-toggle collapse-toggle collapse-open:bg-base-content/10 overlay-minified:collapse-open:bg-transparent"
                    data-collapse="#sidebar-payments-collapse"
                    aria-haspopup="menu" aria-expanded="false">
                    <span class="icon-[tabler--credit-card] size-5"></span>
                    <span class="overlay-minified:hidden">Payments</span>
                    <span class="icon-[tabler--chevron-down] collapse-open:rotate-180 size-4 overlay-minified:hidden transition-all duration-300"></span>
                </button>
                <ul id="sidebar-payments-collapse"
                    class="dropdown-menu mt-0 shadow-none overlay-minified:shadow-md overlay-minified:shadow-base-300/20 dropdown-open:opacity-100 hidden min-w-48 collapse w-auto space-y-0.5 overflow-hidden transition-[height] duration-300 overlay-minified:before:absolute overlay-minified:before:-start-4 overlay-minified:before:top-0 overlay-minified:before:h-full overlay-minified:before:w-4 before:bg-transparent"
                    role="menu" aria-orientation="vertical" aria-labelledby="sidebar-payments">
                    <li><a href="{{ url('/payments/transactions') }}">
                        <span class="icon-[tabler--receipt] size-5"></span>
                        <span>Transactions</span>
                    </a></li>
                    <li><a href="{{ url('/payments/memberships') }}">
                        <span class="icon-[tabler--id-badge] size-5"></span>
                        <span>Memberships</span>
                    </a></li>
                    <li><a href="{{ url('/payments/class-packs') }}">
                        <span class="icon-[tabler--package] size-5"></span>
                        <span>Class Packs</span>
                    </a></li>
                </ul>
            </li>

            {{-- Reports --}}
            <li>
                <a href="{{ url('/reports') }}" class="{{ request()->is('reports*') ? 'bg-base-content/10' : '' }}">
                    <span class="icon-[tabler--chart-bar] size-5"></span>
                    <span class="overlay-minified:hidden">Reports</span>
                </a>
            </li>

            {{-- Settings --}}
            <li>
                <a href="{{ url('/settings') }}" class="{{ request()->is('settings*') ? 'bg-base-content/10' : '' }}">
                    <span class="icon-[tabler--settings] size-5"></span>
                    <span class="overlay-minified:hidden">Settings</span>
                </a>
            </li>
        </ul>
    </div>
</aside>
