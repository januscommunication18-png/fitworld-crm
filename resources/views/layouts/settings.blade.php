@extends('layouts.dashboard')

@section('content')
@php
    $user = auth()->user();
    $canEditStudio = $user->hasPermission('studio.profile');
    $canManageLocations = $user->hasPermission('studio.locations');
    $canManageBookingPage = $user->hasPermission('studio.booking_page');
    $canManagePolicies = $user->hasPermission('studio.policies');
    $canViewTeam = $user->hasPermission('team.view');
    $canManageTeam = $user->hasPermission('team.manage');
    $canManageInstructors = $user->hasPermission('team.instructors');
    $canChangePermissions = $user->hasPermission('team.permissions');
    $canManageClients = $user->hasPermission('students.edit'); // Uses students.edit for backward compatibility
    $canManagePaymentSettings = $user->hasPermission('payments.stripe');
    $canManageBilling = $user->hasPermission('billing.plan');
    $canViewInvoices = $user->hasPermission('billing.invoices');
    $canUpdatePaymentMethod = $user->hasPermission('billing.payment');
@endphp
<div class="space-y-6">
    <div class="flex items-center gap-3">
        <span class="icon-[tabler--settings] size-6"></span>
        <h1 class="text-2xl font-bold">Settings</h1>
    </div>

    <div class="flex flex-col lg:flex-row gap-6">
        {{-- Settings navigation sidebar --}}
        <div class="lg:w-64 shrink-0">
            <div class="bg-base-100 rounded-box p-3 space-y-4 sticky top-6">

                {{-- My Profile - Always visible to all users --}}
                <div>
                    <div class="text-xs font-semibold text-base-content/50 uppercase tracking-wider px-3 mb-1">Account</div>
                    <ul class="menu menu-sm p-0">
                        <li><a href="{{ route('settings.profile') }}" class="{{ request()->routeIs('settings.profile') ? 'active' : '' }}">
                            <span class="icon-[tabler--user-circle] size-4"></span> My Profile
                        </a></li>
                    </ul>
                </div>

                {{-- Studio - Requires studio.profile --}}
                @if($canEditStudio)
                <div>
                    <div class="text-xs font-semibold text-base-content/50 uppercase tracking-wider px-3 mb-1">Studio</div>
                    <ul class="menu menu-sm p-0">
                        <li><a href="{{ route('settings.studio.profile') }}" class="{{ request()->routeIs('settings.studio.profile') ? 'active' : '' }}">
                            <span class="icon-[tabler--building-store] size-4"></span> Studio Profile
                        </a></li>
                    </ul>
                </div>
                @endif

                {{-- Locations - Requires studio.locations or related --}}
                @if($canManageLocations || $canManageBookingPage || $canManagePolicies)
                <div>
                    <div class="text-xs font-semibold text-base-content/50 uppercase tracking-wider px-3 mb-1">Locations</div>
                    <ul class="menu menu-sm p-0">
                        @if($canManageLocations)
                        <li><a href="{{ route('settings.locations.index') }}" class="{{ request()->routeIs('settings.locations.index') ? 'active' : '' }}">
                            <span class="icon-[tabler--map-pin] size-4"></span> Locations
                        </a></li>
                        <li><a href="{{ route('settings.locations.rooms') }}" class="{{ request()->routeIs('settings.locations.rooms') ? 'active' : '' }}">
                            <span class="icon-[tabler--door] size-4"></span> Rooms
                        </a></li>
                        @endif
                        @if($canManageBookingPage)
                        <li><a href="{{ route('settings.locations.booking-page') }}" class="{{ request()->routeIs('settings.locations.booking-page') ? 'active' : '' }}">
                            <span class="icon-[tabler--calendar-event] size-4"></span> Booking Page
                        </a></li>
                        @endif
                        @if($canManagePolicies)
                        <li><a href="{{ route('settings.locations.policies') }}" class="{{ request()->routeIs('settings.locations.policies') ? 'active' : '' }}">
                            <span class="icon-[tabler--file-text] size-4"></span> Policies
                        </a></li>
                        @endif
                    </ul>
                </div>
                @endif

                {{-- Team - Requires team.view or team.manage or team.instructors --}}
                @if($canViewTeam || $canManageTeam || $canManageInstructors || $canChangePermissions)
                <div>
                    <div class="text-xs font-semibold text-base-content/50 uppercase tracking-wider px-3 mb-1">Team</div>
                    <ul class="menu menu-sm p-0">
                        @if($canViewTeam || $canManageTeam)
                        <li><a href="{{ route('settings.team.users') }}" class="{{ request()->routeIs('settings.team.users') ? 'active' : '' }}">
                            <span class="icon-[tabler--users] size-4"></span> Users & Roles
                        </a></li>
                        @endif
                        @if($canManageInstructors)
                        <li><a href="{{ route('settings.team.instructors') }}" class="{{ request()->routeIs('settings.team.instructors') ? 'active' : '' }}">
                            <span class="icon-[tabler--user-star] size-4"></span> Instructors
                        </a></li>
                        @endif
                        @if($canChangePermissions)
                        <li><a href="{{ route('settings.team.permissions') }}" class="{{ request()->routeIs('settings.team.permissions') ? 'active' : '' }}">
                            <span class="icon-[tabler--lock] size-4"></span> Permissions
                        </a></li>
                        @endif
                    </ul>
                </div>
                @endif

                {{-- Clients - Requires students.edit --}}
                @if($canManageClients)
                <div>
                    <div class="text-xs font-semibold text-base-content/50 uppercase tracking-wider px-3 mb-1">Clients</div>
                    <ul class="menu menu-sm p-0">
                        <li><a href="{{ route('settings.clients') }}" class="{{ request()->routeIs('settings.clients') ? 'active' : '' }}">
                            <span class="icon-[tabler--users-cog] size-4"></span> Client Settings
                        </a></li>
                    </ul>
                </div>
                @endif

                {{-- Payments - Requires payments.stripe --}}
                @if($canManagePaymentSettings)
                <div>
                    <div class="text-xs font-semibold text-base-content/50 uppercase tracking-wider px-3 mb-1">Payments</div>
                    <ul class="menu menu-sm p-0">
                        <li><a href="{{ route('settings.payments.settings') }}" class="{{ request()->routeIs('settings.payments.settings') ? 'active' : '' }}">
                            <span class="icon-[tabler--credit-card] size-4"></span> Payment Settings
                        </a></li>
                        <li><a href="{{ route('settings.payments.tax') }}" class="{{ request()->routeIs('settings.payments.tax') ? 'active' : '' }}">
                            <span class="icon-[tabler--receipt-tax] size-4"></span> Tax Settings
                        </a></li>
                        <li><a href="{{ route('settings.payments.payouts') }}" class="{{ request()->routeIs('settings.payments.payouts') ? 'active' : '' }}">
                            <span class="icon-[tabler--cash] size-4"></span> Payout Preferences
                        </a></li>
                    </ul>
                </div>

                {{-- Notifications --}}
                <div>
                    <div class="text-xs font-semibold text-base-content/50 uppercase tracking-wider px-3 mb-1">Notifications</div>
                    <ul class="menu menu-sm p-0">
                        <li><a href="{{ route('settings.notifications.email') }}" class="{{ request()->routeIs('settings.notifications.email') ? 'active' : '' }}">
                            <span class="icon-[tabler--mail] size-4"></span> Email Notifications
                        </a></li>
                        <li><a href="{{ route('settings.notifications.sms') }}" class="{{ request()->routeIs('settings.notifications.sms') ? 'active' : '' }}">
                            <span class="icon-[tabler--message] size-4"></span> SMS
                            <span class="badge badge-soft badge-xs">Add-on</span>
                        </a></li>
                        <li><a href="{{ route('settings.notifications.automation') }}" class="{{ request()->routeIs('settings.notifications.automation') ? 'active' : '' }}">
                            <span class="icon-[tabler--robot] size-4"></span> Automation Rules
                        </a></li>
                    </ul>
                </div>

                {{-- Integrations --}}
                <div>
                    <div class="text-xs font-semibold text-base-content/50 uppercase tracking-wider px-3 mb-1">Integrations</div>
                    <ul class="menu menu-sm p-0">
                        <li><a href="{{ route('settings.integrations.stripe') }}" class="{{ request()->routeIs('settings.integrations.stripe') ? 'active' : '' }}">
                            <span class="icon-[tabler--brand-stripe] size-4"></span> Stripe
                        </a></li>
                        <li><a href="{{ route('settings.integrations.fitnearyou') }}" class="{{ request()->routeIs('settings.integrations.fitnearyou') ? 'active' : '' }}">
                            <span class="icon-[tabler--map-search] size-4"></span> FitNearYou
                        </a></li>
                        <li><a href="{{ route('settings.integrations.calendar') }}" class="{{ request()->routeIs('settings.integrations.calendar') ? 'active' : '' }}">
                            <span class="icon-[tabler--calendar] size-4"></span> Calendar Sync
                        </a></li>
                        <li><a href="{{ route('settings.integrations.paypal') }}" class="{{ request()->routeIs('settings.integrations.paypal') ? 'active' : '' }}">
                            <span class="icon-[tabler--brand-paypal] size-4"></span> PayPal
                        </a></li>
                        <li><a href="{{ route('settings.integrations.cashapp') }}" class="{{ request()->routeIs('settings.integrations.cashapp') ? 'active' : '' }}">
                            <span class="icon-[tabler--currency-dollar] size-4"></span> Cash App
                        </a></li>
                        <li><a href="{{ route('settings.integrations.venmo') }}" class="{{ request()->routeIs('settings.integrations.venmo') ? 'active' : '' }}">
                            <span class="icon-[tabler--brand-venmo] size-4"></span> Venmo
                        </a></li>
                    </ul>
                </div>
                @endif

                {{-- Plans & Billing - Requires billing permissions --}}
                @if($canManageBilling || $canViewInvoices || $canUpdatePaymentMethod)
                <div>
                    <div class="text-xs font-semibold text-base-content/50 uppercase tracking-wider px-3 mb-1">Plans & Billing</div>
                    <ul class="menu menu-sm p-0">
                        @if($canManageBilling)
                        <li><a href="{{ route('settings.billing.plan') }}" class="{{ request()->routeIs('settings.billing.plan') ? 'active' : '' }}">
                            <span class="icon-[tabler--package] size-4"></span> Current Plan
                        </a></li>
                        <li><a href="{{ route('settings.billing.usage') }}" class="{{ request()->routeIs('settings.billing.usage') ? 'active' : '' }}">
                            <span class="icon-[tabler--chart-bar] size-4"></span> Usage
                        </a></li>
                        @endif
                        @if($canViewInvoices)
                        <li><a href="{{ route('settings.billing.invoices') }}" class="{{ request()->routeIs('settings.billing.invoices') ? 'active' : '' }}">
                            <span class="icon-[tabler--file-invoice] size-4"></span> Invoices
                        </a></li>
                        @endif
                    </ul>
                </div>
                @endif

                {{-- Advanced - Owner only --}}
                @if($user->isOwner())
                <div>
                    <div class="text-xs font-semibold text-base-content/50 uppercase tracking-wider px-3 mb-1">Advanced</div>
                    <ul class="menu menu-sm p-0">
                        <li><a href="{{ route('settings.advanced.export') }}" class="{{ request()->routeIs('settings.advanced.export') ? 'active' : '' }}">
                            <span class="icon-[tabler--download] size-4"></span> Data Export
                        </a></li>
                        <li><a href="{{ route('settings.advanced.audit') }}" class="{{ request()->routeIs('settings.advanced.audit') ? 'active' : '' }}">
                            <span class="icon-[tabler--list-details] size-4"></span> Audit Logs
                        </a></li>
                        <li><a href="{{ route('settings.advanced.danger') }}" class="{{ request()->routeIs('settings.advanced.danger') ? 'active' : '' }}">
                            <span class="icon-[tabler--alert-triangle] size-4 text-error"></span> <span class="text-error">Danger Zone</span>
                        </a></li>
                    </ul>
                </div>
                @endif

                {{-- Developer Tools (local only) --}}
                @if(app()->environment('local'))
                <div>
                    <div class="text-xs font-semibold text-warning uppercase tracking-wider px-3 mb-1">Dev Tools</div>
                    <ul class="menu menu-sm p-0">
                        <li><a href="{{ route('settings.dev.email-logs') }}" class="{{ request()->routeIs('settings.dev.email-logs') ? 'active' : '' }}">
                            <span class="icon-[tabler--mail-code] size-4"></span> Email Logs
                        </a></li>
                    </ul>
                </div>
                @endif

            </div>
        </div>

        {{-- Settings content area --}}
        <div class="flex-1 min-w-0">
            @yield('settings-content')
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Close all details dropdowns when clicking outside
document.addEventListener('click', function(e) {
    var allDetails = document.querySelectorAll('details.dropdown[open]');
    allDetails.forEach(function(details) {
        if (!details.contains(e.target)) {
            details.removeAttribute('open');
        }
    });
});
</script>
@endpush
