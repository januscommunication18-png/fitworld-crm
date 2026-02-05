@extends('layouts.dashboard')

@section('title', 'Settings')

@section('breadcrumbs')
    <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
    <li aria-current="page"><span class="icon-[tabler--settings] me-1 size-4"></span> Settings</li>
@endsection

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold">Settings</h1>

    <div class="flex flex-col lg:flex-row gap-6">
        {{-- Settings navigation sidebar --}}
        <div class="lg:w-56 shrink-0">
            <ul class="menu bg-base-100 rounded-box p-2 space-y-0.5">
                <li><a class="active" href="#studio-profile"><span class="icon-[tabler--building-store] size-4"></span> Studio Profile</a></li>
                <li><a href="#location"><span class="icon-[tabler--map-pin] size-4"></span> Location & Space</a></li>
                <li><a href="#payments"><span class="icon-[tabler--credit-card] size-4"></span> Payments</a></li>
                <li><a href="#notifications"><span class="icon-[tabler--bell] size-4"></span> Notifications</a></li>
                <li><a href="#account"><span class="icon-[tabler--user] size-4"></span> Account</a></li>
                <li><a href="#branding"><span class="icon-[tabler--palette] size-4"></span> Branding</a></li>
                <li><a href="#integrations"><span class="icon-[tabler--plug] size-4"></span> Integrations</a></li>
            </ul>
        </div>

        {{-- Settings content panels --}}
        <div class="flex-1 space-y-6">

            {{-- Studio Profile --}}
            <div id="studio-profile" class="card bg-base-100">
                <div class="card-body">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h2 class="text-lg font-semibold">Studio Profile</h2>
                            <p class="text-base-content/60 text-sm">Your studio name, types, and subdomain</p>
                        </div>
                        <button class="btn btn-soft btn-sm"><span class="icon-[tabler--edit] size-4 mr-1"></span>Edit</button>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-base-content/60">Studio Name</span>
                            <p class="font-medium">Zen Yoga Studio</p>
                        </div>
                        <div>
                            <span class="text-base-content/60">Subdomain</span>
                            <p class="font-medium">zen-yoga.fitcrm.app</p>
                        </div>
                        <div>
                            <span class="text-base-content/60">City</span>
                            <p class="font-medium">Austin, TX</p>
                        </div>
                        <div>
                            <span class="text-base-content/60">Timezone</span>
                            <p class="font-medium">America/Chicago (CST)</p>
                        </div>
                        <div>
                            <span class="text-base-content/60">Studio Types</span>
                            <div class="flex flex-wrap gap-1 mt-1">
                                <span class="badge badge-primary badge-soft badge-sm">Yoga</span>
                                <span class="badge badge-primary badge-soft badge-sm">Pilates</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Location & Space --}}
            <div id="location" class="card bg-base-100">
                <div class="card-body">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h2 class="text-lg font-semibold">Location & Space</h2>
                            <p class="text-base-content/60 text-sm">Address, rooms, and capacity settings</p>
                        </div>
                        <button class="btn btn-soft btn-sm"><span class="icon-[tabler--edit] size-4 mr-1"></span>Edit</button>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-base-content/60">Address</span>
                            <p class="font-medium">123 Main Street, Austin, TX 78701</p>
                        </div>
                        <div>
                            <span class="text-base-content/60">Rooms</span>
                            <p class="font-medium">2 rooms</p>
                        </div>
                        <div>
                            <span class="text-base-content/60">Default Capacity</span>
                            <p class="font-medium">20 per room</p>
                        </div>
                        <div>
                            <span class="text-base-content/60">Amenities</span>
                            <div class="flex flex-wrap gap-1 mt-1">
                                <span class="badge badge-soft badge-sm">Mats Provided</span>
                                <span class="badge badge-soft badge-sm">Showers</span>
                                <span class="badge badge-soft badge-sm">Parking</span>
                                <span class="badge badge-soft badge-sm">WiFi</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Payment Settings --}}
            <div id="payments" class="card bg-base-100">
                <div class="card-body">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h2 class="text-lg font-semibold">Payment Settings</h2>
                            <p class="text-base-content/60 text-sm">Stripe connection and payment preferences</p>
                        </div>
                        <button class="btn btn-soft btn-sm"><span class="icon-[tabler--edit] size-4 mr-1"></span>Edit</button>
                    </div>
                    <div class="flex items-center gap-3 text-sm mb-4">
                        <span class="badge badge-success badge-soft"><span class="icon-[tabler--check] size-3 mr-1"></span>Connected</span>
                        <span>Stripe is connected</span>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-base-content/60">Currency</span>
                            <p class="font-medium">USD ($)</p>
                        </div>
                        <div>
                            <span class="text-base-content/60">Tax Rate</span>
                            <p class="font-medium">8.25%</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Notification Preferences --}}
            <div id="notifications" class="card bg-base-100">
                <div class="card-body">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h2 class="text-lg font-semibold">Notification Preferences</h2>
                            <p class="text-base-content/60 text-sm">Email and SMS notification settings</p>
                        </div>
                        <button class="btn btn-soft btn-sm"><span class="icon-[tabler--edit] size-4 mr-1"></span>Edit</button>
                    </div>
                    <div class="space-y-3 text-sm">
                        <div class="flex items-center justify-between">
                            <div><div class="font-medium">New Booking</div><div class="text-xs text-base-content/50">When a student books a class</div></div>
                            <input type="checkbox" class="toggle toggle-primary toggle-sm" checked />
                        </div>
                        <div class="flex items-center justify-between">
                            <div><div class="font-medium">Cancellation</div><div class="text-xs text-base-content/50">When a student cancels a booking</div></div>
                            <input type="checkbox" class="toggle toggle-primary toggle-sm" checked />
                        </div>
                        <div class="flex items-center justify-between">
                            <div><div class="font-medium">Payment Received</div><div class="text-xs text-base-content/50">When a payment is processed</div></div>
                            <input type="checkbox" class="toggle toggle-primary toggle-sm" checked />
                        </div>
                        <div class="flex items-center justify-between">
                            <div><div class="font-medium">Daily Summary</div><div class="text-xs text-base-content/50">Recap of the day's activity at 9pm</div></div>
                            <input type="checkbox" class="toggle toggle-primary toggle-sm" />
                        </div>
                    </div>
                </div>
            </div>

            {{-- Account & Security --}}
            <div id="account" class="card bg-base-100">
                <div class="card-body">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h2 class="text-lg font-semibold">Account & Security</h2>
                            <p class="text-base-content/60 text-sm">Password, email, and account management</p>
                        </div>
                        <button class="btn btn-soft btn-sm"><span class="icon-[tabler--edit] size-4 mr-1"></span>Edit</button>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-base-content/60">Name</span>
                            <p class="font-medium">Jane Smith</p>
                        </div>
                        <div>
                            <span class="text-base-content/60">Email</span>
                            <p class="font-medium">jane@zenyoga.com</p>
                        </div>
                        <div>
                            <span class="text-base-content/60">Password</span>
                            <p class="font-medium">Last changed 30 days ago</p>
                        </div>
                        <div>
                            <span class="text-base-content/60">Two-Factor Auth</span>
                            <p class="font-medium"><span class="badge badge-warning badge-soft badge-sm">Not enabled</span></p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Branding --}}
            <div id="branding" class="card bg-base-100">
                <div class="card-body">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h2 class="text-lg font-semibold">Branding</h2>
                            <p class="text-base-content/60 text-sm">Logo, colors, and booking page appearance</p>
                        </div>
                        <button class="btn btn-soft btn-sm"><span class="icon-[tabler--edit] size-4 mr-1"></span>Edit</button>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
                        <div>
                            <span class="text-base-content/60">Logo</span>
                            <div class="mt-2 w-16 h-16 bg-base-200 rounded-lg flex items-center justify-center">
                                <span class="icon-[tabler--photo] size-8 text-base-content/30"></span>
                            </div>
                        </div>
                        <div>
                            <span class="text-base-content/60">Primary Color</span>
                            <div class="flex items-center gap-2 mt-2">
                                <div class="w-8 h-8 rounded-full bg-primary"></div>
                                <span class="font-medium">#6366f1</span>
                            </div>
                        </div>
                        <div>
                            <span class="text-base-content/60">Theme</span>
                            <p class="font-medium mt-2">Light</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Integrations --}}
            <div id="integrations" class="card bg-base-100">
                <div class="card-body">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h2 class="text-lg font-semibold">Integrations</h2>
                            <p class="text-base-content/60 text-sm">Third-party service connections</p>
                        </div>
                    </div>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-3 border border-base-content/10 rounded-lg">
                            <div class="flex items-center gap-3">
                                <span class="icon-[tabler--brand-stripe] size-8 text-[#635BFF]"></span>
                                <div><div class="font-medium text-sm">Stripe</div><div class="text-xs text-base-content/50">Payment processing</div></div>
                            </div>
                            <span class="badge badge-success badge-soft badge-sm">Connected</span>
                        </div>
                        <div class="flex items-center justify-between p-3 border border-base-content/10 rounded-lg">
                            <div class="flex items-center gap-3">
                                <span class="icon-[tabler--brand-google] size-8 text-[#4285F4]"></span>
                                <div><div class="font-medium text-sm">Google Calendar</div><div class="text-xs text-base-content/50">Calendar sync</div></div>
                            </div>
                            <button class="btn btn-soft btn-xs">Connect</button>
                        </div>
                        <div class="flex items-center justify-between p-3 border border-base-content/10 rounded-lg">
                            <div class="flex items-center gap-3">
                                <span class="icon-[tabler--mail] size-8 text-[#EA4335]"></span>
                                <div><div class="font-medium text-sm">Mailchimp</div><div class="text-xs text-base-content/50">Email marketing</div></div>
                            </div>
                            <button class="btn btn-soft btn-xs">Connect</button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
