@php
    $appHost = auth()->user()->currentHost() ?? auth()->user()->host;
    $appSetupIncomplete = auth()->user()->isOwner($appHost) && !$appHost->setup_completed_at;
    $disabledClass = $appSetupIncomplete ? 'opacity-40 pointer-events-none' : '';
@endphp
<x-detail-drawer id="apps" title="Apps" :showFooter="false" size="sm">
    <div class="space-y-5">
        {{-- Main --}}
        <div>
            <h4 class="text-xs font-semibold text-base-content/50 uppercase tracking-wider mb-3">Main</h4>
            <div class="grid grid-cols-3 gap-2">
                <a href="{{ url('/dashboard') }}" class="flex flex-col items-center gap-1.5 p-3 rounded-lg hover:bg-base-200 transition-colors {{ $disabledClass }}">
                    <span class="icon-[tabler--home] size-7 text-primary"></span>
                    <span class="text-xs font-medium">Dashboard</span>
                </a>
                <a href="{{ url('/schedule/calendar') }}" class="flex flex-col items-center gap-1.5 p-3 rounded-lg hover:bg-base-200 transition-colors {{ $disabledClass }}">
                    <span class="icon-[tabler--calendar] size-7 text-primary"></span>
                    <span class="text-xs font-medium">Schedule</span>
                </a>
                <a href="{{ url('/bookings') }}" class="flex flex-col items-center gap-1.5 p-3 rounded-lg hover:bg-base-200 transition-colors {{ $disabledClass }}">
                    <span class="icon-[tabler--book] size-7 text-primary"></span>
                    <span class="text-xs font-medium">Bookings</span>
                </a>
                <a href="{{ url('/clients') }}" class="flex flex-col items-center gap-1.5 p-3 rounded-lg hover:bg-base-200 transition-colors {{ $disabledClass }}">
                    <span class="icon-[tabler--users] size-7 text-primary"></span>
                    <span class="text-xs font-medium">Clients</span>
                </a>
                <a href="{{ url('/instructors') }}" class="flex flex-col items-center gap-1.5 p-3 rounded-lg hover:bg-base-200 transition-colors {{ $disabledClass }}">
                    <span class="icon-[tabler--user-star] size-7 text-primary"></span>
                    <span class="text-xs font-medium">Instructors</span>
                </a>
                <a href="{{ url('/catalog') }}" class="flex flex-col items-center gap-1.5 p-3 rounded-lg hover:bg-base-200 transition-colors {{ $disabledClass }}">
                    <span class="icon-[tabler--layout-grid] size-7 text-primary"></span>
                    <span class="text-xs font-medium">Catalog</span>
                </a>
            </div>
        </div>

        {{-- Commerce --}}
        <div>
            <h4 class="text-xs font-semibold text-base-content/50 uppercase tracking-wider mb-3">Commerce</h4>
            <div class="grid grid-cols-3 gap-2">
                <a href="{{ url('/payments/transactions') }}" class="flex flex-col items-center gap-1.5 p-3 rounded-lg hover:bg-base-200 transition-colors {{ $disabledClass }}">
                    <span class="icon-[tabler--credit-card] size-7 text-primary"></span>
                    <span class="text-xs font-medium">Payments</span>
                </a>
                <a href="{{ url('/reports') }}" class="flex flex-col items-center gap-1.5 p-3 rounded-lg hover:bg-base-200 transition-colors {{ $disabledClass }}">
                    <span class="icon-[tabler--chart-bar] size-7 text-primary"></span>
                    <span class="text-xs font-medium">Insights</span>
                </a>
                <a href="{{ url('/offers') }}" class="flex flex-col items-center gap-1.5 p-3 rounded-lg hover:bg-base-200 transition-colors {{ $disabledClass }}">
                    <span class="icon-[tabler--speakerphone] size-7 text-primary"></span>
                    <span class="text-xs font-medium">Marketing</span>
                </a>
            </div>
        </div>

        {{-- Tools --}}
        <div>
            <h4 class="text-xs font-semibold text-base-content/50 uppercase tracking-wider mb-3">Tools</h4>
            <div class="grid grid-cols-3 gap-2">
                <a href="{{ url('/helpdesk') }}" class="flex flex-col items-center gap-1.5 p-3 rounded-lg hover:bg-base-200 transition-colors {{ $disabledClass }}">
                    <span class="icon-[tabler--help] size-7 text-primary"></span>
                    <span class="text-xs font-medium">Help Desk</span>
                </a>
                <a href="{{ url('/marketplace') }}" class="flex flex-col items-center gap-1.5 p-3 rounded-lg hover:bg-base-200 transition-colors {{ $disabledClass }}">
                    <span class="icon-[tabler--apps] size-7 text-primary"></span>
                    <span class="text-xs font-medium">Marketplace</span>
                </a>
                <a href="{{ url('/settings') }}" class="flex flex-col items-center gap-1.5 p-3 rounded-lg hover:bg-base-200 transition-colors">
                    <span class="icon-[tabler--settings] size-7 text-primary"></span>
                    <span class="text-xs font-medium">Settings</span>
                </a>
                <a href="{{ url('/one-on-one') }}" class="flex flex-col items-center gap-1.5 p-3 rounded-lg hover:bg-base-200 transition-colors {{ $disabledClass }}">
                    <span class="icon-[tabler--video] size-7 text-primary"></span>
                    <span class="text-xs font-medium">1:1 Meetings</span>
                </a>
                <a href="{{ url('/segments') }}" class="flex flex-col items-center gap-1.5 p-3 rounded-lg hover:bg-base-200 transition-colors {{ $disabledClass }}">
                    <span class="icon-[tabler--filter] size-7 text-primary"></span>
                    <span class="text-xs font-medium">Segments</span>
                </a>
                <a href="{{ url('/waitlist') }}" class="flex flex-col items-center gap-1.5 p-3 rounded-lg hover:bg-base-200 transition-colors {{ $disabledClass }}">
                    <span class="icon-[tabler--clock] size-7 text-primary"></span>
                    <span class="text-xs font-medium">Waitlist</span>
                </a>
            </div>
        </div>
    </div>
</x-detail-drawer>
