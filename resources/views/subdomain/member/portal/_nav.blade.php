@php
    $selectedLang = session("language_{$host->id}", $host->default_language_booking ?? 'en');
    $t = \App\Services\TranslationService::make($host, $selectedLang);
    $trans = $t->all();
@endphp

{{-- Member Portal Navigation --}}
{{-- Header --}}
<nav class="bg-base-100 border-b border-base-200" style="height: 75px;">
    <div class="container-fixed h-full">
        <div class="flex items-center justify-between h-full">
            {{-- Logo --}}
            <div class="flex items-center">
                @if($host->logo_url)
                    <a href="{{ route('subdomain.home', ['subdomain' => $host->subdomain]) }}" class="flex items-center">
                        <img src="{{ $host->logo_url }}" alt="{{ $host->studio_name }}" class="h-12 w-auto max-w-[180px] object-contain">
                    </a>
                @else
                    <a href="{{ route('subdomain.home', ['subdomain' => $host->subdomain]) }}" class="flex items-center gap-2">
                        <div class="w-10 h-10 rounded-xl bg-primary flex items-center justify-center">
                            <span class="text-lg font-bold text-primary-content">{{ strtoupper(substr($host->studio_name, 0, 1)) }}</span>
                        </div>
                        <span class="font-bold text-lg">{{ $host->studio_name }}</span>
                    </a>
                @endif
            </div>

            {{-- User Menu --}}
            <div class="flex items-center gap-4">
                <div class="dropdown dropdown-end">
                    <label tabindex="0" class="btn btn-ghost btn-sm">
                        <div class="avatar placeholder">
                            <div class="bg-primary text-primary-content w-8 rounded-full">
                                <span class="text-sm">{{ $member->initials }}</span>
                            </div>
                        </div>
                        <span class="hidden sm:inline ml-2">{{ $member->first_name }}</span>
                        <span class="icon-[tabler--chevron-down] size-4"></span>
                    </label>
                    <ul tabindex="0" class="dropdown-menu dropdown-content z-[1] p-2 shadow bg-base-100 rounded-box w-52">
                        <li class="px-4 py-2 border-b border-base-200">
                            <p class="font-medium">{{ $member->full_name }}</p>
                            <p class="text-xs text-base-content/60">{{ $member->email }}</p>
                        </li>
                        <li><a href="{{ route('member.portal.dashboard', ['subdomain' => $host->subdomain]) }}" class="menu-item">
                            <span class="icon-[tabler--home] size-4"></span>
                            {{ $trans['nav.dashboard'] ?? 'Home' }}
                        </a></li>
                        <li><a href="{{ route('member.portal.bookings', ['subdomain' => $host->subdomain]) }}" class="menu-item">
                            <span class="icon-[tabler--calendar-check] size-4"></span>
                            {{ $trans['member.portal.my_bookings'] ?? 'My Schedule' }}
                        </a></li>
                        <li><a href="{{ route('member.portal.profile', ['subdomain' => $host->subdomain]) }}" class="menu-item">
                            <span class="icon-[tabler--user] size-4"></span>
                            {{ $trans['member.portal.my_profile'] ?? 'My Profile' }}
                        </a></li>
                        <li class="border-t border-base-200 mt-1 pt-1">
                            <form action="{{ route('member.logout', ['subdomain' => $host->subdomain]) }}" method="POST">
                                @csrf
                                <button type="submit" class="menu-item text-error w-full">
                                    <span class="icon-[tabler--logout] size-4"></span>
                                    {{ $trans['member.portal.logout'] ?? 'Logoff' }}
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</nav>

{{-- Portal Navigation Tabs --}}
<div class="bg-base-100 border-b border-base-200">
    <div class="container-fixed">
        <div class="tabs tabs-bordered overflow-x-auto">
            <a href="{{ route('member.portal.dashboard', ['subdomain' => $host->subdomain]) }}"
               class="tab {{ request()->routeIs('member.portal', 'member.portal.dashboard') ? 'tab-active' : '' }}">
                <span class="icon-[tabler--home] size-4 mr-2"></span>
                {{ $trans['nav.dashboard'] ?? 'Home' }}
            </a>
            <a href="{{ route('member.portal.bookings', ['subdomain' => $host->subdomain]) }}"
               class="tab {{ request()->routeIs('member.portal.bookings') ? 'tab-active' : '' }}">
                <span class="icon-[tabler--calendar-check] size-4 mr-2"></span>
                {{ $trans['member.portal.my_bookings'] ?? 'My Schedule' }}
            </a>
            <a href="{{ route('member.portal.booking', ['subdomain' => $host->subdomain]) }}"
               class="tab {{ request()->routeIs('member.portal.booking', 'member.portal.schedule', 'member.portal.services', 'member.portal.memberships') ? 'tab-active' : '' }}">
                <span class="icon-[tabler--calendar-plus] size-4 mr-2"></span>
                {{ $trans['nav.bookings'] ?? 'Booking' }}
            </a>
            <a href="{{ route('member.portal.payments', ['subdomain' => $host->subdomain]) }}"
               class="tab {{ request()->routeIs('member.portal.payments') ? 'tab-active' : '' }}">
                <span class="icon-[tabler--receipt] size-4 mr-2"></span>
                {{ $trans['member.portal.my_payments'] ?? 'Payments' }}
            </a>
            <a href="{{ route('member.portal.profile', ['subdomain' => $host->subdomain]) }}"
               class="tab {{ request()->routeIs('member.portal.profile') ? 'tab-active' : '' }}">
                <span class="icon-[tabler--user] size-4 mr-2"></span>
                {{ $trans['member.portal.my_profile'] ?? 'My Profile' }}
            </a>
            <form action="{{ route('member.logout', ['subdomain' => $host->subdomain]) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="tab text-error hover:text-error">
                    <span class="icon-[tabler--logout] size-4 mr-2"></span>
                    {{ $trans['member.portal.logout'] ?? 'Logoff' }}
                </button>
            </form>
        </div>
    </div>
</div>

{{-- Flash Messages --}}
@if(session('success'))
<div class="bg-base-200 pt-4">
    <div class="container-fixed">
        <div class="alert alert-success">
            <span class="icon-[tabler--check] size-5"></span>
            <span>{{ session('success') }}</span>
        </div>
    </div>
</div>
@endif

@if(session('error'))
<div class="bg-base-200 pt-4">
    <div class="container-fixed">
        <div class="alert alert-error">
            <span class="icon-[tabler--alert-circle] size-5"></span>
            <span>{{ session('error') }}</span>
        </div>
    </div>
</div>
@endif
