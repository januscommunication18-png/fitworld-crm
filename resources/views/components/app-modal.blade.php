<div id="app-modal" class="overlay modal overlay-open:opacity-100 overlay-open:duration-300 hidden" role="dialog" tabindex="-1">
    <div class="modal-dialog overlay-open:opacity-100 overlay-open:duration-300 overlay-open:translate-y-0">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Apps</h3>
                <button type="button" class="btn btn-text btn-circle btn-sm absolute end-3 top-3" aria-label="Close" data-overlay="#app-modal">
                    <span class="icon-[tabler--x] size-4"></span>
                </button>
            </div>
            <div class="modal-body pt-0">
                <div class="grid grid-cols-3 gap-4">
                    {{-- Active apps --}}
                    <a href="{{ url('/schedule') }}" class="flex flex-col items-center gap-2 p-4 rounded-lg hover:bg-base-200 transition-colors">
                        <span class="icon-[tabler--calendar] size-8 text-primary"></span>
                        <span class="text-xs font-medium">Schedule</span>
                    </a>
                    <a href="{{ url('/students') }}" class="flex flex-col items-center gap-2 p-4 rounded-lg hover:bg-base-200 transition-colors">
                        <span class="icon-[tabler--users] size-8 text-primary"></span>
                        <span class="text-xs font-medium">CRM</span>
                    </a>
                    <a href="{{ url('/payments') }}" class="flex flex-col items-center gap-2 p-4 rounded-lg hover:bg-base-200 transition-colors">
                        <span class="icon-[tabler--credit-card] size-8 text-primary"></span>
                        <span class="text-xs font-medium">Payments</span>
                    </a>
                    <a href="{{ url('/reports') }}" class="flex flex-col items-center gap-2 p-4 rounded-lg hover:bg-base-200 transition-colors">
                        <span class="icon-[tabler--chart-bar] size-8 text-primary"></span>
                        <span class="text-xs font-medium">Reports</span>
                    </a>

                    {{-- Coming soon apps --}}
                    <div class="flex flex-col items-center gap-2 p-4 rounded-lg opacity-50 pointer-events-none">
                        <span class="icon-[tabler--speakerphone] size-8 text-base-content/40"></span>
                        <span class="text-xs font-medium">Marketing</span>
                        <span class="badge badge-xs badge-soft">Coming Soon</span>
                    </div>
                    <div class="flex flex-col items-center gap-2 p-4 rounded-lg opacity-50 pointer-events-none">
                        <span class="icon-[tabler--world] size-8 text-base-content/40"></span>
                        <span class="text-xs font-medium">Website</span>
                        <span class="badge badge-xs badge-soft">Coming Soon</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
