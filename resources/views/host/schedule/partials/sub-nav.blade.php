{{-- Schedule Sub-Navigation --}}
<div class="tabs tabs-bordered mb-6 overflow-x-auto">
    <a href="{{ route('schedule.index') }}" class="tab whitespace-nowrap {{ request()->routeIs('schedule.index', 'schedule.calendar') ? 'tab-active' : '' }}">
        <span class="icon-[tabler--calendar] size-4 mr-2"></span>
        Studio Calendar
    </a>
    <a href="{{ route('schedule.list') }}" class="tab whitespace-nowrap {{ request()->routeIs('schedule.list') ? 'tab-active' : '' }}">
        <span class="icon-[tabler--list] size-4 mr-2"></span>
        List
    </a>
    <a href="{{ route('class-sessions.index') }}" class="tab whitespace-nowrap {{ request()->routeIs('class-sessions.*') ? 'tab-active' : '' }}">
        <span class="icon-[tabler--yoga] size-4 mr-2"></span>
        Classes
    </a>
    <a href="{{ route('service-slots.index') }}" class="tab whitespace-nowrap {{ request()->routeIs('service-slots.*') ? 'tab-active' : '' }}">
        <span class="icon-[tabler--massage] size-4 mr-2"></span>
        Services
    </a>
    <a href="{{ route('schedule.requests') }}" class="tab whitespace-nowrap {{ request()->routeIs('schedule.requests') ? 'tab-active' : '' }}">
        <span class="icon-[tabler--message-question] size-4 mr-2"></span>
        Requests
        <span class="badge badge-xs badge-ghost ml-1">Soon</span>
    </a>
    <a href="{{ route('schedule.waitlist') }}" class="tab whitespace-nowrap {{ request()->routeIs('schedule.waitlist') ? 'tab-active' : '' }}">
        <span class="icon-[tabler--clock] size-4 mr-2"></span>
        Waitlist
        <span class="badge badge-xs badge-ghost ml-1">Soon</span>
    </a>
</div>
