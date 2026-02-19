{{-- Navigation Bar - 75px height --}}
<nav class="bg-base-100 border-b border-base-200 sticky top-0 z-40" style="height: 75px;">
    <div class="container-fixed h-full">
        <div class="flex items-center justify-between h-full">
            {{-- Left: Logo --}}
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
                        <span class="font-bold text-lg hidden sm:inline">{{ $host->studio_name }}</span>
                    </a>
                @endif
            </div>

            {{-- Right: Request Booking + Member Login --}}
            <div class="flex items-center gap-3">
                {{-- Request Booking Button --}}
                <a href="{{ route('subdomain.service-request', ['subdomain' => $host->subdomain]) }}"
                   class="btn btn-primary btn-sm sm:btn-md">
                    <span class="icon-[tabler--calendar-plus] size-5 hidden sm:inline"></span>
                    Request Booking
                </a>

                {{-- Member Login --}}
                @if($host->isMemberPortalEnabled())
                    @if(auth('member')->check())
                        {{-- Already logged in --}}
                        <a href="{{ route('member.portal', ['subdomain' => $host->subdomain]) }}"
                           class="btn btn-ghost btn-sm sm:btn-md">
                            <span class="icon-[tabler--user] size-5"></span>
                            <span class="hidden sm:inline">My Portal</span>
                        </a>
                    @else
                        <a href="{{ route('member.login', ['subdomain' => $host->subdomain]) }}"
                           class="btn btn-ghost btn-sm sm:btn-md">
                            <span class="icon-[tabler--login] size-5"></span>
                            <span class="hidden sm:inline">Member Login</span>
                        </a>
                    @endif
                @else
                    <div class="relative group">
                        <button class="btn btn-ghost btn-sm sm:btn-md" disabled>
                            <span class="icon-[tabler--login] size-5"></span>
                            <span class="hidden sm:inline">Member Login</span>
                        </button>
                        <div class="absolute top-full right-0 mt-1 opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">
                            <span class="badge badge-sm badge-neutral whitespace-nowrap">Coming Soon</span>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</nav>
