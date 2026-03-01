@extends('backoffice.layouts.app')

@section('title', 'Add Waitlist Entry')
@section('page-title', 'Add Waitlist Entry')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="card bg-base-100 shadow-lg border border-base-content/10">
        <div class="card-body">
            <form action="{{ route('backoffice.waitlist.store') }}" method="POST">
                @csrf
                <div class="space-y-4">
                    {{-- First Name & Last Name - Two Column --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="label-text" for="first_name">First Name <span class="text-error">*</span></label>
                            <input type="text" id="first_name" name="first_name"
                                value="{{ old('first_name') }}"
                                class="input w-full @error('first_name') input-error @enderror"
                                placeholder="Enter first name"
                                required>
                            @error('first_name')
                                <p class="text-error text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="label-text" for="last_name">Last Name</label>
                            <input type="text" id="last_name" name="last_name"
                                value="{{ old('last_name') }}"
                                class="input w-full @error('last_name') input-error @enderror"
                                placeholder="Enter last name">
                            @error('last_name')
                                <p class="text-error text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Email --}}
                    <div>
                        <label class="label-text" for="email">Email Address <span class="text-error">*</span></label>
                        <input type="email" id="email" name="email"
                            value="{{ old('email') }}"
                            class="input w-full @error('email') input-error @enderror"
                            placeholder="you@yourstudio.com"
                            required>
                        @error('email')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Studio Name --}}
                    <div>
                        <label class="label-text" for="studio_name">Studio Name</label>
                        <input type="text" id="studio_name" name="studio_name"
                            value="{{ old('studio_name') }}"
                            class="input w-full @error('studio_name') input-error @enderror"
                            placeholder="Your Studio Name">
                        @error('studio_name')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Studio Type (Custom searchable multi-select) --}}
                    <div>
                        <label class="label-text mb-1">What type of studio do you run?</label>

                        {{-- Custom Dropdown --}}
                        <div class="relative" id="studio-type-dropdown">
                            {{-- Toggle Button --}}
                            <button type="button" id="studio-type-toggle"
                                class="input w-full text-left flex items-center justify-between min-h-[42px] cursor-pointer"
                                onclick="toggleStudioDropdown()">
                                <span id="studio-type-display" class="text-base-content/50">Select studio types...</span>
                                <span class="icon-[tabler--caret-up-down] size-4 text-base-content/50"></span>
                            </button>

                            {{-- Dropdown Panel --}}
                            <div id="studio-type-panel" class="absolute left-0 right-0 top-full mt-1 bg-base-100 border border-base-content/20 rounded-lg shadow-lg z-[9999] hidden">
                                {{-- Search Input --}}
                                <div class="p-2 border-b border-base-content/10">
                                    <input type="text" id="studio-type-search"
                                        class="input input-sm w-full"
                                        placeholder="Search studio types..."
                                        onclick="event.stopPropagation()"
                                        oninput="filterStudioTypes()">
                                </div>

                                {{-- Options List --}}
                                <div class="max-h-48 overflow-y-auto p-2" id="studio-type-options">
                                    @foreach(\App\Models\ProspectWaitlist::getStudioTypes() as $value => $label)
                                    <label class="flex items-center gap-2 px-2 py-1.5 rounded hover:bg-base-200 cursor-pointer studio-option" data-value="{{ $value }}" data-label="{{ $label }}">
                                        <input type="checkbox" name="studio_type[]" value="{{ $value }}"
                                            class="checkbox checkbox-sm checkbox-primary"
                                            {{ in_array($value, old('studio_type', [])) ? 'checked' : '' }}
                                            onchange="updateStudioDisplay()">
                                        <span class="text-sm">{{ $label }}</span>
                                    </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        @error('studio_type')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                        @error('studio_type.*')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Member Size --}}
                    <div>
                        <label class="label-text" for="member_size">How many members?</label>
                        <select id="member_size" name="member_size" class="select w-full @error('member_size') select-error @enderror">
                            <option value="">Select member count...</option>
                            @foreach(\App\Models\ProspectWaitlist::getMemberSizes() as $value => $label)
                                <option value="{{ $value }}" {{ old('member_size') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('member_size')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Footer --}}
                <div class="flex items-center justify-end gap-2 mt-6 pt-4 border-t border-base-content/10">
                    <a href="{{ route('backoffice.waitlist.index') }}" class="btn btn-ghost">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        Add to Waitlist
                        <span class="icon-[tabler--arrow-right] size-4"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function toggleStudioDropdown() {
        const panel = document.getElementById('studio-type-panel');
        panel.classList.toggle('hidden');
        if (!panel.classList.contains('hidden')) {
            document.getElementById('studio-type-search').focus();
        }
    }

    function filterStudioTypes() {
        const search = document.getElementById('studio-type-search').value.toLowerCase();
        const options = document.querySelectorAll('.studio-option');
        options.forEach(function(option) {
            const label = option.dataset.label.toLowerCase();
            if (label.includes(search)) {
                option.style.display = '';
            } else {
                option.style.display = 'none';
            }
        });
    }

    function updateStudioDisplay() {
        const checked = document.querySelectorAll('.studio-option input:checked');
        const display = document.getElementById('studio-type-display');
        if (checked.length === 0) {
            display.textContent = 'Select studio types...';
            display.classList.add('text-base-content/50');
        } else if (checked.length <= 2) {
            const labels = Array.from(checked).map(function(cb) {
                return cb.closest('.studio-option').dataset.label;
            });
            display.textContent = labels.join(', ');
            display.classList.remove('text-base-content/50');
        } else {
            display.textContent = checked.length + ' studio types selected';
            display.classList.remove('text-base-content/50');
        }
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        const dropdown = document.getElementById('studio-type-dropdown');
        const panel = document.getElementById('studio-type-panel');
        if (dropdown && !dropdown.contains(e.target)) {
            panel.classList.add('hidden');
        }
    });

    // Initialize display on load
    document.addEventListener('DOMContentLoaded', updateStudioDisplay);
</script>
@endpush
