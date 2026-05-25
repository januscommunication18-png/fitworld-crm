@props(['host', 'member'])

@php
    $sub = $host->subdomain;
    $isHome = request()->routeIs('member.portal', 'member.portal.dashboard');
    $isBookings = request()->routeIs('member.portal.bookings');
    $isBooking = request()->routeIs('member.portal.booking', 'member.portal.schedule', 'member.portal.services', 'member.portal.memberships');
    $isPayments = request()->routeIs('member.portal.payments');
    $isProfile = request()->routeIs('member.portal.profile');
    $isHelpdesk = request()->routeIs('member.portal.helpdesk*');
    // "customer_reply" = studio replied, member hasn't responded yet → "new reply" indicator.
    $supportUnreadCount = \App\Models\HelpdeskTicket::where('host_id', $host->id)
        ->where('client_id', $member->id)
        ->where('status', 'customer_reply')
        ->count();
@endphp

<div class="min-h-screen flex flex-col bg-base-200">
    {{-- Top bar --}}
    <header class="bg-base-100 border-b border-base-200 sticky top-0 z-30" style="height: 64px;">
        <div class="h-full w-full px-4 sm:px-6 lg:px-8 flex items-center justify-between">
            {{-- Mobile sidebar toggle + logo --}}
            <div class="flex items-center gap-3">
                <button type="button" class="md:hidden btn btn-ghost btn-sm btn-square" aria-label="Open menu" onclick="togglePortalSidebar()">
                    <span class="icon-[tabler--menu-2] size-5"></span>
                </button>
                @if($host->logo_url)
                    <a href="{{ route('subdomain.home', ['subdomain' => $sub]) }}" class="flex items-center">
                        <img src="{{ $host->logo_url }}" alt="{{ $host->studio_name }}" class="h-10 w-auto max-w-[160px] object-contain">
                    </a>
                @else
                    <a href="{{ route('subdomain.home', ['subdomain' => $sub]) }}" class="flex items-center gap-2">
                        <div class="w-9 h-9 rounded-lg bg-primary flex items-center justify-center">
                            <span class="text-sm font-bold text-primary-content">{{ strtoupper(substr($host->studio_name, 0, 1)) }}</span>
                        </div>
                        <span class="font-bold hidden sm:inline">{{ $host->studio_name }}</span>
                    </a>
                @endif
            </div>

            {{-- User dropdown --}}
            <div class="dropdown dropdown-end">
                <label tabindex="0" class="btn btn-ghost btn-sm">
                    <div class="avatar avatar-placeholder">
                        <div class="bg-primary text-primary-content w-8 h-8 rounded-full font-bold flex items-center justify-center">
                            <span class="text-sm">{{ $member->initials }}</span>
                        </div>
                    </div>
                    <span class="hidden sm:inline ml-2">{{ $member->first_name }}</span>
                    <span class="icon-[tabler--chevron-down] size-4"></span>
                </label>
                <ul tabindex="0" class="dropdown-menu dropdown-content z-50 p-2 shadow bg-base-100 rounded-box w-56">
                    <li class="px-4 py-2 border-b border-base-200">
                        <p class="font-medium">{{ $member->full_name }}</p>
                        <p class="text-xs text-base-content/60 truncate">{{ $member->email }}</p>
                    </li>
                    <li>
                        <a href="{{ route('member.portal.profile', ['subdomain' => $sub]) }}" class="menu-item flex items-center gap-2">
                            <span class="icon-[tabler--user] size-4"></span>
                            {{ $trans['member.portal.my_profile'] ?? 'My Profile' }}
                        </a>
                    </li>
                    <li class="border-t border-base-200 mt-1 pt-1">
                        <form action="{{ route('member.logout', ['subdomain' => $sub]) }}" method="POST">
                            @csrf
                            <button type="submit" class="menu-item text-error w-full flex items-center gap-2">
                                <span class="icon-[tabler--logout] size-4"></span>
                                {{ $trans['member.portal.logout'] ?? 'Logoff' }}
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </header>

    {{-- Body: sidebar + content --}}
    <div class="flex-1 flex">
        {{-- Sidebar --}}
        <aside id="portal-sidebar" class="hidden md:flex md:flex-col w-60 shrink-0 bg-base-100 border-r border-base-200 fixed md:sticky top-[64px] md:top-[64px] left-0 z-20 h-[calc(100vh-64px)] overflow-y-auto">
            <nav class="p-3 space-y-1">
                @php
                    $linkBase = 'flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-colors';
                    $linkInactive = 'text-base-content/70 hover:bg-base-200 hover:text-base-content';
                    $linkActive = 'bg-primary/10 text-primary font-medium';
                @endphp

                <a href="{{ route('member.portal.dashboard', ['subdomain' => $sub]) }}"
                   class="{{ $linkBase }} {{ $isHome ? $linkActive : $linkInactive }}">
                    <span class="icon-[tabler--home] size-5 shrink-0"></span>
                    <span>{{ $trans['nav.home'] ?? 'Home' }}</span>
                </a>

                <a href="{{ route('member.portal.bookings', ['subdomain' => $sub]) }}"
                   class="{{ $linkBase }} {{ $isBookings ? $linkActive : $linkInactive }}">
                    <span class="icon-[tabler--calendar-check] size-5 shrink-0"></span>
                    <span>{{ $trans['member.portal.my_bookings'] ?? 'My Schedule' }}</span>
                </a>

                <a href="{{ route('member.portal.booking', ['subdomain' => $sub]) }}"
                   class="{{ $linkBase }} {{ $isBooking ? $linkActive : $linkInactive }}">
                    <span class="icon-[tabler--calendar-plus] size-5 shrink-0"></span>
                    <span>{{ $trans['nav.bookings'] ?? 'Booking' }}</span>
                </a>

                <a href="{{ route('member.portal.payments', ['subdomain' => $sub]) }}"
                   class="{{ $linkBase }} {{ $isPayments ? $linkActive : $linkInactive }}">
                    <span class="icon-[tabler--receipt] size-5 shrink-0"></span>
                    <span>{{ $trans['member.portal.my_payments'] ?? 'Payments' }}</span>
                </a>

                <a href="{{ route('member.portal.profile', ['subdomain' => $sub]) }}"
                   class="{{ $linkBase }} {{ $isProfile ? $linkActive : $linkInactive }}">
                    <span class="icon-[tabler--user] size-5 shrink-0"></span>
                    <span>{{ $trans['member.portal.my_profile'] ?? 'My Profile' }}</span>
                </a>

                <a href="{{ route('member.portal.helpdesk', ['subdomain' => $sub]) }}"
                   class="{{ $linkBase }} {{ $isHelpdesk ? $linkActive : $linkInactive }}">
                    <span class="icon-[tabler--lifebuoy] size-5 shrink-0"></span>
                    <span class="flex-1">{{ $trans['member.portal.support'] ?? 'Support' }}</span>
                    @if($supportUnreadCount > 0)
                        <span class="badge badge-xs badge-primary" title="{{ $supportUnreadCount }} new repl{{ $supportUnreadCount === 1 ? 'y' : 'ies' }}">{{ $supportUnreadCount }}</span>
                    @endif
                </a>

                <div class="border-t border-base-200 mt-2 pt-2">
                    <form action="{{ route('member.logout', ['subdomain' => $sub]) }}" method="POST">
                        @csrf
                        <button type="submit" class="{{ $linkBase }} w-full text-error hover:bg-error/10">
                            <span class="icon-[tabler--logout] size-5 shrink-0"></span>
                            <span>{{ $trans['member.portal.logout'] ?? 'Logoff' }}</span>
                        </button>
                    </form>
                </div>
            </nav>
        </aside>

        {{-- Mobile sidebar backdrop --}}
        <div id="portal-sidebar-backdrop" class="hidden fixed inset-0 bg-black/40 z-10 md:hidden" onclick="togglePortalSidebar()"></div>

        {{-- Main content --}}
        <main class="flex-1 min-w-0">
            {{-- Flash messages --}}
            @if(session('success'))
                <div class="px-4 sm:px-6 lg:px-8 pt-4">
                    <div class="alert alert-success">
                        <span class="icon-[tabler--check] size-5"></span>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif
            @if(session('error'))
                <div class="px-4 sm:px-6 lg:px-8 pt-4">
                    <div class="alert alert-error">
                        <span class="icon-[tabler--alert-circle] size-5"></span>
                        <span>{{ session('error') }}</span>
                    </div>
                </div>
            @endif

            <div class="px-4 sm:px-6 lg:px-8 py-6">
                {{ $slot }}
            </div>
        </main>
    </div>
</div>

@once
    @push('scripts')
    <script>
    function togglePortalSidebar() {
        var bar = document.getElementById('portal-sidebar');
        var bd = document.getElementById('portal-sidebar-backdrop');
        if (!bar || !bd) return;
        var open = !bar.classList.contains('hidden');
        if (open) {
            bar.classList.add('hidden');
            bd.classList.add('hidden');
            document.body.style.overflow = '';
        } else {
            bar.classList.remove('hidden');
            bar.classList.add('flex', 'flex-col');
            bd.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
    }
    </script>
    @endpush
@endonce
