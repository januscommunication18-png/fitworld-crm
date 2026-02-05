<div id="alerts-drawer" class="overlay overlay-open:translate-x-0 drawer drawer-end hidden" role="dialog" tabindex="-1">
    <div class="drawer-header">
        <h3 class="drawer-title">Notifications</h3>
        <button type="button" class="btn btn-text btn-circle btn-sm absolute end-3 top-3" aria-label="Close" data-overlay="#alerts-drawer">
            <span class="icon-[tabler--x] size-5"></span>
        </button>
    </div>
    <div class="drawer-body">
        {{-- Notification list with skeleton placeholders --}}
        <div class="space-y-4">
            {{-- Skeleton items (shown while loading) --}}
            <div class="animate-pulse flex items-start gap-3 p-3 rounded-lg bg-base-200/50">
                <div class="bg-base-300 rounded-full size-10 shrink-0"></div>
                <div class="flex-1 space-y-2">
                    <div class="h-3 bg-base-300 rounded w-3/4"></div>
                    <div class="h-3 bg-base-300 rounded w-full"></div>
                    <div class="h-2 bg-base-300 rounded w-1/4"></div>
                </div>
            </div>
            <div class="animate-pulse flex items-start gap-3 p-3 rounded-lg bg-base-200/50">
                <div class="bg-base-300 rounded-full size-10 shrink-0"></div>
                <div class="flex-1 space-y-2">
                    <div class="h-3 bg-base-300 rounded w-2/3"></div>
                    <div class="h-3 bg-base-300 rounded w-full"></div>
                    <div class="h-2 bg-base-300 rounded w-1/3"></div>
                </div>
            </div>
            <div class="animate-pulse flex items-start gap-3 p-3 rounded-lg bg-base-200/50">
                <div class="bg-base-300 rounded-full size-10 shrink-0"></div>
                <div class="flex-1 space-y-2">
                    <div class="h-3 bg-base-300 rounded w-1/2"></div>
                    <div class="h-3 bg-base-300 rounded w-3/4"></div>
                    <div class="h-2 bg-base-300 rounded w-1/5"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="drawer-footer justify-center">
        <a href="{{ url('/notifications') }}" class="link link-primary text-sm no-underline">View All Notifications</a>
    </div>
</div>
