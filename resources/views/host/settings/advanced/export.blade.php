@extends('layouts.settings')

@section('title', 'Data Export — Settings')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ url('/dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('settings.index') }}"><span class="icon-[tabler--settings] me-1 size-4"></span> Settings</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">Data Export</li>
    </ol>
@endsection

@section('settings-content')
<div class="space-y-6">
    <div class="card bg-base-100">
        <div class="card-body">
            <h2 class="text-lg font-semibold mb-2">Export Your Data</h2>
            <p class="text-base-content/60 text-sm mb-6">Download your studio data in CSV format for backup, reporting, or migration purposes.</p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Clients -->
                <div class="flex items-center justify-between p-4 border border-base-content/10 rounded-lg hover:border-primary/30 transition-colors">
                    <div class="flex items-center gap-4">
                        <span class="icon-[tabler--users] size-8 text-primary"></span>
                        <div>
                            <div class="font-medium">Clients / Students</div>
                            <div class="text-sm text-base-content/60">All client profiles, contact info, and membership status</div>
                        </div>
                    </div>
                    <a href="{{ route('export.clients') }}" class="btn btn-soft btn-sm">
                        <span class="icon-[tabler--download] size-4"></span> Export
                    </a>
                </div>

                <!-- Transactions -->
                <div class="flex items-center justify-between p-4 border border-base-content/10 rounded-lg hover:border-primary/30 transition-colors">
                    <div class="flex items-center gap-4">
                        <span class="icon-[tabler--receipt] size-8 text-success"></span>
                        <div>
                            <div class="font-medium">Transactions</div>
                            <div class="text-sm text-base-content/60">All payment and transaction history</div>
                        </div>
                    </div>
                    <a href="{{ route('export.transactions') }}" class="btn btn-soft btn-sm">
                        <span class="icon-[tabler--download] size-4"></span> Export
                    </a>
                </div>

                <!-- Bookings -->
                <div class="flex items-center justify-between p-4 border border-base-content/10 rounded-lg hover:border-primary/30 transition-colors">
                    <div class="flex items-center gap-4">
                        <span class="icon-[tabler--ticket] size-8 text-info"></span>
                        <div>
                            <div class="font-medium">Bookings</div>
                            <div class="text-sm text-base-content/60">All booking and attendance records</div>
                        </div>
                    </div>
                    <a href="{{ route('export.bookings') }}" class="btn btn-soft btn-sm">
                        <span class="icon-[tabler--download] size-4"></span> Export
                    </a>
                </div>

                <!-- Class Sessions -->
                <div class="flex items-center justify-between p-4 border border-base-content/10 rounded-lg hover:border-primary/30 transition-colors">
                    <div class="flex items-center gap-4">
                        <span class="icon-[tabler--calendar] size-8 text-secondary"></span>
                        <div>
                            <div class="font-medium">Class Sessions</div>
                            <div class="text-sm text-base-content/60">All class sessions with attendance data</div>
                        </div>
                    </div>
                    <a href="{{ route('export.classes') }}" class="btn btn-soft btn-sm">
                        <span class="icon-[tabler--download] size-4"></span> Export
                    </a>
                </div>

                <!-- Memberships -->
                <div class="flex items-center justify-between p-4 border border-base-content/10 rounded-lg hover:border-primary/30 transition-colors">
                    <div class="flex items-center gap-4">
                        <span class="icon-[tabler--id-badge] size-8 text-accent"></span>
                        <div>
                            <div class="font-medium">Memberships</div>
                            <div class="text-sm text-base-content/60">All active and past membership records</div>
                        </div>
                    </div>
                    <a href="{{ route('export.memberships') }}" class="btn btn-soft btn-sm">
                        <span class="icon-[tabler--download] size-4"></span> Export
                    </a>
                </div>

                <!-- Instructors -->
                <div class="flex items-center justify-between p-4 border border-base-content/10 rounded-lg hover:border-primary/30 transition-colors">
                    <div class="flex items-center gap-4">
                        <span class="icon-[tabler--user-star] size-8 text-warning"></span>
                        <div>
                            <div class="font-medium">Instructors</div>
                            <div class="text-sm text-base-content/60">All instructor profiles and assignments</div>
                        </div>
                    </div>
                    <a href="{{ route('export.instructors') }}" class="btn btn-soft btn-sm">
                        <span class="icon-[tabler--download] size-4"></span> Export
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Audit & Security Exports -->
    <div class="card bg-base-100">
        <div class="card-body">
            <h2 class="text-lg font-semibold mb-2">Audit & Security Logs</h2>
            <p class="text-base-content/60 text-sm mb-6">Export security and activity logs for compliance and record keeping.</p>

            <div class="space-y-4">
                <!-- Audit Logs -->
                <div class="flex items-center justify-between p-4 border border-base-content/10 rounded-lg hover:border-primary/30 transition-colors">
                    <div class="flex items-center gap-4">
                        <span class="icon-[tabler--history] size-8 text-neutral"></span>
                        <div>
                            <div class="font-medium">Activity Logs</div>
                            <div class="text-sm text-base-content/60">All system activity and audit trail</div>
                        </div>
                    </div>
                    <a href="{{ route('export.audit-logs') }}" class="btn btn-soft btn-sm">
                        <span class="icon-[tabler--download] size-4"></span> Export
                    </a>
                </div>

                <!-- User Sessions -->
                <div class="flex items-center justify-between p-4 border border-base-content/10 rounded-lg hover:border-primary/30 transition-colors">
                    <div class="flex items-center gap-4">
                        <span class="icon-[tabler--device-desktop] size-8 text-neutral"></span>
                        <div>
                            <div class="font-medium">User Sessions</div>
                            <div class="text-sm text-base-content/60">Login history, IP addresses, device info</div>
                        </div>
                    </div>
                    <a href="{{ route('export.user-sessions') }}" class="btn btn-soft btn-sm">
                        <span class="icon-[tabler--download] size-4"></span> Export
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Info Box -->
    <div class="alert alert-info">
        <span class="icon-[tabler--info-circle] size-5"></span>
        <div>
            <div class="font-medium">Data Export Format</div>
            <div class="text-sm">All exports are generated as CSV files that can be opened in Excel, Google Sheets, or any spreadsheet application. Large datasets may take a moment to generate.</div>
        </div>
    </div>
</div>
@endsection
