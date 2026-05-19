@php
    $user = auth()->user();
    $canEditStudio = $user->hasPermission('studio.profile');
    $canManageLocations = $user->hasPermission('studio.locations');
    $canManageBookingPage = $user->hasPermission('studio.booking_page');
    $canManagePolicies = $user->hasPermission('studio.policies');
    $canViewTeam = $user->hasPermission('team.view');
    $canManageTeam = $user->hasPermission('team.manage');
    $canChangePermissions = $user->hasPermission('team.permissions');
    $canManageClients = $user->hasPermission('students.edit');
    $canManageQuestionnaires = $user->hasPermission('schedule.create') || $user->hasPermission('schedule.edit');
    $canManagePaymentSettings = $user->hasPermission('payments.stripe');
    $canManageBilling = $user->hasPermission('billing.plan');
    $canViewInvoices = $user->hasPermission('billing.invoices');
@endphp

{{-- Back button --}}
<a href="{{ url('/dashboard') }}" class="flex items-center gap-2 px-3 py-2 mb-3 rounded-lg text-sm font-medium text-base-content/60 hover:bg-base-content/5 hover:text-base-content transition-colors">
    <span class="icon-[tabler--arrow-left] size-4 shrink-0"></span>
    <span class="sidebar-label">Back to Menu</span>
</a>

{{-- Settings title --}}
<div class="flex items-center gap-2 px-3 mb-4">
    <span class="icon-[tabler--settings] size-5 text-primary"></span>
    <span class="sidebar-label text-lg font-bold">Settings</span>
</div>

<ul class="menu menu-sm space-y-0.5 p-0">

    {{-- Account --}}
    <li class="menu-title sidebar-section-label">
        <span class="text-xs font-semibold text-base-content/40 uppercase tracking-wider">Account</span>
    </li>
    <li><a href="{{ route('settings.profile') }}" class="{{ request()->routeIs('settings.profile') ? 'active' : '' }}">
        <span class="icon-[tabler--user-circle] size-4"></span> <span class="sidebar-label">My Profile</span>
    </a></li>

    {{-- Studio --}}
    @if($canEditStudio)
    <li class="menu-title sidebar-section-label pt-3">
        <span class="text-xs font-semibold text-base-content/40 uppercase tracking-wider">Studio</span>
    </li>
    <li><a href="{{ route('settings.studio.profile') }}" class="{{ request()->routeIs('settings.studio.profile') ? 'active' : '' }}">
        <span class="icon-[tabler--building-store] size-4"></span> <span class="sidebar-label">Studio Profile</span>
    </a></li>
    @endif

    {{-- Locations --}}
    @if($canManageLocations || $canManageBookingPage || $canManagePolicies)
    <li class="menu-title sidebar-section-label pt-3">
        <span class="text-xs font-semibold text-base-content/40 uppercase tracking-wider">Locations</span>
    </li>
    @if($canManageLocations)
    <li><a href="{{ route('settings.locations.index') }}" class="{{ request()->routeIs('settings.locations.index') ? 'active' : '' }}">
        <span class="icon-[tabler--map-pin] size-4"></span> <span class="sidebar-label">Locations</span>
    </a></li>
    <li><a href="{{ route('settings.locations.rooms') }}" class="{{ request()->routeIs('settings.locations.rooms') ? 'active' : '' }}">
        <span class="icon-[tabler--door] size-4"></span> <span class="sidebar-label">Rooms</span>
    </a></li>
    @endif
    @if($canManageBookingPage)
    <li><a href="{{ route('settings.locations.booking-page') }}" class="{{ request()->routeIs('settings.locations.booking-page') ? 'active' : '' }}">
        <span class="icon-[tabler--calendar-event] size-4"></span> <span class="sidebar-label">Booking Page</span>
    </a></li>
    @endif
    @if($canManageQuestionnaires)
    <li><a href="{{ route('questionnaires.index') }}" class="{{ request()->is('questionnaires*') ? 'active' : '' }}">
        <span class="icon-[tabler--forms] size-4"></span> <span class="sidebar-label">Questionnaires</span>
    </a></li>
    @endif
    @if($canManagePolicies)
    <li><a href="{{ route('settings.locations.policies') }}" class="{{ request()->routeIs('settings.locations.policies') ? 'active' : '' }}">
        <span class="icon-[tabler--file-text] size-4"></span> <span class="sidebar-label">Policies</span>
    </a></li>
    @endif
    @endif

    {{-- Team --}}
    @if($canViewTeam || $canManageTeam || $canChangePermissions)
    <li class="menu-title sidebar-section-label pt-3">
        <span class="text-xs font-semibold text-base-content/40 uppercase tracking-wider">Team</span>
    </li>
    @if($canViewTeam || $canManageTeam)
    <li><a href="{{ route('settings.team.users') }}" class="{{ request()->routeIs('settings.team.users') ? 'active' : '' }}">
        <span class="icon-[tabler--users] size-4"></span> <span class="sidebar-label">Users & Roles</span>
    </a></li>
    @endif
    @if($canChangePermissions)
    <li><a href="{{ route('settings.team.permissions') }}" class="{{ request()->routeIs('settings.team.permissions') ? 'active' : '' }}">
        <span class="icon-[tabler--lock] size-4"></span> <span class="sidebar-label">Permissions</span>
    </a></li>
    @endif
    @endif

    {{-- Clients --}}
    @if($canManageClients)
    <li class="menu-title sidebar-section-label pt-3">
        <span class="text-xs font-semibold text-base-content/40 uppercase tracking-wider">Clients</span>
    </li>
    <li><a href="{{ route('settings.member-portal') }}" class="{{ request()->routeIs('settings.member-portal') ? 'active' : '' }}">
        <span class="icon-[tabler--users-cog] size-4"></span> <span class="sidebar-label">Client & Portal Settings</span>
    </a></li>
    @endif

    {{-- Payments --}}
    @if($canManagePaymentSettings)
    <li class="menu-title sidebar-section-label pt-3">
        <span class="text-xs font-semibold text-base-content/40 uppercase tracking-wider">Payments</span>
    </li>
    <li><a href="{{ route('settings.payments.settings') }}" class="{{ request()->routeIs('settings.payments.settings') ? 'active' : '' }}">
        <span class="icon-[tabler--credit-card] size-4"></span> <span class="sidebar-label">Payment Settings</span>
    </a></li>
    <li><a href="{{ route('settings.payments.tax') }}" class="{{ request()->routeIs('settings.payments.tax') ? 'active' : '' }}">
        <span class="icon-[tabler--receipt-tax] size-4"></span> <span class="sidebar-label">Tax Settings</span>
    </a></li>
    <li><a href="{{ route('settings.payments.payouts') }}" class="{{ request()->routeIs('settings.payments.payouts') ? 'active' : '' }}">
        <span class="icon-[tabler--cash] size-4"></span> <span class="sidebar-label">Payout Preferences</span>
    </a></li>

    {{-- Communication --}}
    <li class="menu-title sidebar-section-label pt-3">
        <span class="text-xs font-semibold text-base-content/40 uppercase tracking-wider">Communication</span>
    </li>
    <li><a href="{{ route('settings.communication.email-templates') }}" class="{{ request()->routeIs('settings.communication.email-templates*') ? 'active' : '' }}">
        <span class="icon-[tabler--mail-cog] size-4"></span> <span class="sidebar-label">Email Templates</span>
    </a></li>
    <li><a href="{{ route('settings.notifications.email') }}" class="{{ request()->routeIs('settings.notifications.email') ? 'active' : '' }}">
        <span class="icon-[tabler--mail] size-4"></span> <span class="sidebar-label">Email Notifications</span>
    </a></li>
    <li><a href="{{ route('settings.notifications.sms') }}" class="{{ request()->routeIs('settings.notifications.sms') ? 'active' : '' }}">
        <span class="icon-[tabler--message] size-4"></span> <span class="sidebar-label">SMS</span>
        <span class="badge badge-soft badge-xs">Add-on</span>
    </a></li>
    <li><a href="{{ route('settings.notifications.automation') }}" class="{{ request()->routeIs('settings.notifications.automation') ? 'active' : '' }}">
        <span class="icon-[tabler--robot] size-4"></span> <span class="sidebar-label">Automation Rules</span>
    </a></li>

    {{-- Integrations --}}
    <li class="menu-title sidebar-section-label pt-3">
        <span class="text-xs font-semibold text-base-content/40 uppercase tracking-wider">Integrations</span>
    </li>
    <li><a href="{{ route('settings.integrations.stripe') }}" class="{{ request()->routeIs('settings.integrations.stripe') ? 'active' : '' }}">
        <span class="icon-[tabler--brand-stripe] size-4"></span> <span class="sidebar-label">Stripe</span>
    </a></li>
    <li><a href="{{ route('settings.integrations.fitnearyou') }}" class="{{ request()->routeIs('settings.integrations.fitnearyou') ? 'active' : '' }}">
        <span class="icon-[tabler--map-search] size-4"></span> <span class="sidebar-label">FitNearYou</span>
    </a></li>
    <li><a href="{{ route('settings.integrations.calendar') }}" class="{{ request()->routeIs('settings.integrations.calendar') ? 'active' : '' }}">
        <span class="icon-[tabler--calendar] size-4"></span> <span class="sidebar-label">Calendar Sync</span>
    </a></li>
    <li><a href="{{ route('settings.integrations.paypal') }}" class="{{ request()->routeIs('settings.integrations.paypal') ? 'active' : '' }}">
        <span class="icon-[tabler--brand-paypal] size-4"></span> <span class="sidebar-label">PayPal</span>
    </a></li>
    <li><a href="{{ route('settings.integrations.cashapp') }}" class="{{ request()->routeIs('settings.integrations.cashapp') ? 'active' : '' }}">
        <span class="icon-[tabler--currency-dollar] size-4"></span> <span class="sidebar-label">Cash App</span>
    </a></li>
    <li><a href="{{ route('settings.integrations.venmo') }}" class="{{ request()->routeIs('settings.integrations.venmo') ? 'active' : '' }}">
        <span class="icon-[tabler--brand-venmo] size-4"></span> <span class="sidebar-label">Venmo</span>
    </a></li>
    @endif

    {{-- Plans & Billing --}}
    @if($canManageBilling || $canViewInvoices)
    <li class="menu-title sidebar-section-label pt-3">
        <span class="text-xs font-semibold text-base-content/40 uppercase tracking-wider">Plans & Billing</span>
    </li>
    @if($canManageBilling)
    <li><a href="{{ route('settings.billing.plan') }}" class="{{ request()->routeIs('settings.billing.plan') ? 'active' : '' }}">
        <span class="icon-[tabler--package] size-4"></span> <span class="sidebar-label">Current Plan</span>
    </a></li>
    <li><a href="{{ route('settings.billing.usage') }}" class="{{ request()->routeIs('settings.billing.usage') ? 'active' : '' }}">
        <span class="icon-[tabler--chart-bar] size-4"></span> <span class="sidebar-label">Usage</span>
    </a></li>
    @endif
    @if($canViewInvoices)
    <li><a href="{{ route('settings.billing.invoices') }}" class="{{ request()->routeIs('settings.billing.invoices') ? 'active' : '' }}">
        <span class="icon-[tabler--file-invoice] size-4"></span> <span class="sidebar-label">Invoices</span>
    </a></li>
    @endif
    @endif

    {{-- Advanced --}}
    @if($user->isOwner())
    <li class="menu-title sidebar-section-label pt-3">
        <span class="text-xs font-semibold text-base-content/40 uppercase tracking-wider">Advanced</span>
    </li>
    <li><a href="{{ route('settings.advanced.export') }}" class="{{ request()->routeIs('settings.advanced.export') ? 'active' : '' }}">
        <span class="icon-[tabler--download] size-4"></span> <span class="sidebar-label">Data Export</span>
    </a></li>
    <li><a href="{{ route('settings.audit') }}" class="{{ request()->routeIs('settings.audit') ? 'active' : '' }}">
        <span class="icon-[tabler--list-details] size-4"></span> <span class="sidebar-label">Audit Logs</span>
    </a></li>
    <li><a href="{{ route('settings.advanced.danger') }}" class="{{ request()->routeIs('settings.advanced.danger') ? 'active' : '' }}">
        <span class="icon-[tabler--alert-triangle] size-4 text-error"></span> <span class="sidebar-label text-error">Danger Zone</span>
    </a></li>
    @endif

    {{-- Dev Tools --}}
    @if(app()->environment('local'))
    <li class="menu-title sidebar-section-label pt-3">
        <span class="text-xs font-semibold text-warning uppercase tracking-wider">Dev Tools</span>
    </li>
    <li><a href="{{ route('settings.dev.email-logs') }}" class="{{ request()->routeIs('settings.dev.email-logs') ? 'active' : '' }}">
        <span class="icon-[tabler--mail-code] size-4"></span> <span class="sidebar-label">Email Logs</span>
    </a></li>
    @endif

</ul>
