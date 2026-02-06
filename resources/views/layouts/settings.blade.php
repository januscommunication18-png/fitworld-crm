@extends('layouts.dashboard')

@section('content')
<div class="space-y-6">
    <div class="flex items-center gap-3">
        <span class="icon-[tabler--settings] size-6"></span>
        <h1 class="text-2xl font-bold">Settings</h1>
    </div>

    <div class="flex flex-col lg:flex-row gap-6">
        {{-- Settings navigation sidebar --}}
        <div class="lg:w-64 shrink-0">
            <div class="bg-base-100 rounded-box p-3 space-y-4 sticky top-6">

                {{-- Studio --}}
                <div>
                    <div class="text-xs font-semibold text-base-content/50 uppercase tracking-wider px-3 mb-1">Studio</div>
                    <ul class="menu menu-sm p-0">
                        <li><a href="{{ route('settings.studio.profile') }}" class="{{ request()->routeIs('settings.studio.profile') ? 'active' : '' }}">
                            <span class="icon-[tabler--building-store] size-4"></span> Studio Profile
                        </a></li>
                    </ul>
                </div>

                {{-- Locations --}}
                <div>
                    <div class="text-xs font-semibold text-base-content/50 uppercase tracking-wider px-3 mb-1">Locations</div>
                    <ul class="menu menu-sm p-0">
                        <li><a href="{{ route('settings.locations.index') }}" class="{{ request()->routeIs('settings.locations.index') ? 'active' : '' }}">
                            <span class="icon-[tabler--map-pin] size-4"></span> Locations
                        </a></li>
                        <li><a href="{{ route('settings.locations.rooms') }}" class="{{ request()->routeIs('settings.locations.rooms') ? 'active' : '' }}">
                            <span class="icon-[tabler--door] size-4"></span> Rooms
                        </a></li>
                        <li><a href="{{ route('settings.locations.booking-page') }}" class="{{ request()->routeIs('settings.locations.booking-page') ? 'active' : '' }}">
                            <span class="icon-[tabler--calendar-event] size-4"></span> Booking Page
                        </a></li>
                        <li><a href="{{ route('settings.locations.policies') }}" class="{{ request()->routeIs('settings.locations.policies') ? 'active' : '' }}">
                            <span class="icon-[tabler--file-text] size-4"></span> Policies
                        </a></li>
                    </ul>
                </div>

                {{-- Team --}}
                <div>
                    <div class="text-xs font-semibold text-base-content/50 uppercase tracking-wider px-3 mb-1">Team</div>
                    <ul class="menu menu-sm p-0">
                        <li><a href="{{ route('settings.team.users') }}" class="{{ request()->routeIs('settings.team.users') ? 'active' : '' }}">
                            <span class="icon-[tabler--users] size-4"></span> Users & Roles
                        </a></li>
                        <li><a href="{{ route('settings.team.instructors') }}" class="{{ request()->routeIs('settings.team.instructors') ? 'active' : '' }}">
                            <span class="icon-[tabler--user-star] size-4"></span> Instructors
                        </a></li>
                        <li><a href="{{ route('settings.team.permissions') }}" class="{{ request()->routeIs('settings.team.permissions') ? 'active' : '' }}">
                            <span class="icon-[tabler--lock] size-4"></span> Permissions
                        </a></li>
                    </ul>
                </div>

                {{-- Payments --}}
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

                {{-- Plans & Billing --}}
                <div>
                    <div class="text-xs font-semibold text-base-content/50 uppercase tracking-wider px-3 mb-1">Plans & Billing</div>
                    <ul class="menu menu-sm p-0">
                        <li><a href="{{ route('settings.billing.plan') }}" class="{{ request()->routeIs('settings.billing.plan') ? 'active' : '' }}">
                            <span class="icon-[tabler--package] size-4"></span> Current Plan
                        </a></li>
                        <li><a href="{{ route('settings.billing.usage') }}" class="{{ request()->routeIs('settings.billing.usage') ? 'active' : '' }}">
                            <span class="icon-[tabler--chart-bar] size-4"></span> Usage
                        </a></li>
                        <li><a href="{{ route('settings.billing.invoices') }}" class="{{ request()->routeIs('settings.billing.invoices') ? 'active' : '' }}">
                            <span class="icon-[tabler--file-invoice] size-4"></span> Invoices
                        </a></li>
                    </ul>
                </div>

                {{-- Advanced --}}
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

            </div>
        </div>

        {{-- Settings content area --}}
        <div class="flex-1 min-w-0">
            @yield('settings-content')
        </div>
    </div>
</div>
@endsection
