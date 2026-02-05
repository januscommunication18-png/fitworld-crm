<div id="search-modal" class="overlay modal overlay-open:opacity-100 overlay-open:duration-300 hidden" role="dialog" tabindex="-1">
    <div class="modal-dialog modal-dialog-lg overlay-open:opacity-100 overlay-open:duration-300 overlay-open:translate-y-0">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Search</h3>
                <button type="button" class="btn btn-text btn-circle btn-sm absolute end-3 top-3" aria-label="Close" data-overlay="#search-modal">
                    <span class="icon-[tabler--x] size-4"></span>
                </button>
            </div>
            <div class="modal-body pt-0">
                {{-- Search input --}}
                <div class="input-group mb-4">
                    <span class="input-group-text">
                        <span class="icon-[tabler--search] size-5 text-base-content/60"></span>
                    </span>
                    <input type="text" class="input grow" placeholder="Search people, bookings, classes, payments..." autofocus />
                </div>

                {{-- Filter tabs --}}
                <div class="flex gap-2 mb-4">
                    <button type="button" class="badge badge-primary badge-sm">All</button>
                    <button type="button" class="badge badge-soft badge-sm">People</button>
                    <button type="button" class="badge badge-soft badge-sm">Bookings</button>
                    <button type="button" class="badge badge-soft badge-sm">Classes</button>
                    <button type="button" class="badge badge-soft badge-sm">Payments</button>
                </div>

                {{-- Results area with skeleton placeholder --}}
                <div class="min-h-40">
                    <div class="animate-pulse space-y-3">
                        <div class="flex items-center gap-3">
                            <div class="bg-base-300 rounded-full size-10"></div>
                            <div class="flex-1 space-y-2">
                                <div class="h-3 bg-base-300 rounded w-3/4"></div>
                                <div class="h-3 bg-base-300 rounded w-1/2"></div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="bg-base-300 rounded-full size-10"></div>
                            <div class="flex-1 space-y-2">
                                <div class="h-3 bg-base-300 rounded w-2/3"></div>
                                <div class="h-3 bg-base-300 rounded w-1/3"></div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="bg-base-300 rounded-full size-10"></div>
                            <div class="flex-1 space-y-2">
                                <div class="h-3 bg-base-300 rounded w-1/2"></div>
                                <div class="h-3 bg-base-300 rounded w-1/4"></div>
                            </div>
                        </div>
                    </div>
                    <p class="text-center text-base-content/50 mt-4 text-sm">Type to start searching...</p>
                </div>
            </div>
        </div>
    </div>
</div>
