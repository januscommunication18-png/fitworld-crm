{{-- Client Selector Component --}}
<div class="space-y-4">
    {{-- Search Input --}}
    <div class="form-control">
        <label class="label" for="client-search">
            <span class="label-text font-medium">Search Client</span>
        </label>
        <div class="relative">
            <span class="icon-[tabler--search] size-5 text-base-content/50 absolute left-3 top-1/2 -translate-y-1/2"></span>
            <input type="text"
                   id="client-search"
                   class="input input-bordered w-full pl-10"
                   placeholder="Search by name, email, or phone..."
                   autocomplete="off"
                   oninput="searchClients(this.value)">
            <div id="client-search-loading" class="absolute right-3 top-1/2 -translate-y-1/2 hidden">
                <span class="loading loading-spinner loading-sm"></span>
            </div>
        </div>
    </div>

    {{-- Search Results --}}
    <div id="client-search-results" class="hidden">
        <div class="text-sm text-base-content/60 mb-2">Search Results</div>
        <div id="client-results-list" class="space-y-2 max-h-48 overflow-y-auto">
            {{-- Results will be populated by JS --}}
        </div>
    </div>

    {{-- Recent Clients --}}
    <div id="client-recent-section">
        <div class="text-sm text-base-content/60 mb-2">Recent Clients</div>
        <div id="client-recent-list" class="space-y-2">
            <div class="flex items-center justify-center py-4 text-base-content/40">
                <span class="loading loading-spinner loading-sm mr-2"></span>
                Loading recent clients...
            </div>
        </div>
    </div>

    {{-- Divider --}}
    <div class="divider text-sm text-base-content/50">OR</div>

    {{-- Quick Add Form --}}
    <div id="client-quick-add-section">
        <button type="button"
                class="btn btn-soft btn-primary btn-block"
                id="client-quick-add-toggle"
                onclick="toggleQuickAddForm()">
            <span class="icon-[tabler--user-plus] size-5"></span>
            Add New Client
        </button>

        <div id="client-quick-add-form" class="hidden mt-4 space-y-3 p-4 border border-base-300 rounded-lg bg-base-200/30">
            <div class="flex items-center justify-between mb-2">
                <span class="font-medium">New Client</span>
                <button type="button" class="btn btn-ghost btn-xs btn-circle" onclick="toggleQuickAddForm()">
                    <span class="icon-[tabler--x] size-4"></span>
                </button>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div class="form-control">
                    <label class="label py-1" for="quick-add-first-name">
                        <span class="label-text text-sm">First Name <span class="text-error">*</span></span>
                    </label>
                    <input type="text"
                           id="quick-add-first-name"
                           class="input input-bordered input-sm"
                           placeholder="John"
                           required>
                </div>
                <div class="form-control">
                    <label class="label py-1" for="quick-add-last-name">
                        <span class="label-text text-sm">Last Name <span class="text-error">*</span></span>
                    </label>
                    <input type="text"
                           id="quick-add-last-name"
                           class="input input-bordered input-sm"
                           placeholder="Doe"
                           required>
                </div>
            </div>

            <div class="form-control">
                <label class="label py-1" for="quick-add-phone">
                    <span class="label-text text-sm">Phone</span>
                </label>
                <input type="tel"
                       id="quick-add-phone"
                       class="input input-bordered input-sm"
                       placeholder="(555) 123-4567">
            </div>

            <div class="form-control">
                <label class="label py-1" for="quick-add-email">
                    <span class="label-text text-sm">Email</span>
                </label>
                <input type="email"
                       id="quick-add-email"
                       class="input input-bordered input-sm"
                       placeholder="john@example.com">
            </div>

            <div class="form-control">
                <label class="label cursor-pointer justify-start gap-3 py-1">
                    <input type="checkbox" id="quick-add-send-emails" class="checkbox checkbox-sm checkbox-primary">
                    <span class="label-text text-sm">Send booking confirmation emails</span>
                </label>
            </div>

            <button type="button"
                    class="btn btn-primary btn-sm btn-block mt-2"
                    onclick="createQuickAddClient()">
                <span class="icon-[tabler--check] size-4"></span>
                Create & Select Client
            </button>
        </div>
    </div>

    {{-- Selected Client Display --}}
    <div id="selected-client-display" class="hidden">
        <div class="text-sm text-base-content/60 mb-2">Selected Client</div>
        <div class="card bg-primary/5 border border-primary/20">
            <div class="card-body p-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="avatar placeholder">
                            <div class="bg-primary text-primary-content rounded-full w-10">
                                <span id="selected-client-initials" class="text-sm font-medium">JD</span>
                            </div>
                        </div>
                        <div>
                            <div class="font-semibold" id="selected-client-name">John Doe</div>
                            <div class="text-sm text-base-content/60" id="selected-client-contact">john@example.com</div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-ghost btn-sm btn-circle" onclick="clearSelectedClient()">
                        <span class="icon-[tabler--x] size-4"></span>
                    </button>
                </div>
                {{-- Membership/Pack badges --}}
                <div id="selected-client-badges" class="flex flex-wrap gap-2 mt-2 hidden">
                    {{-- Will be populated by JS --}}
                </div>
            </div>
        </div>
    </div>
</div>
