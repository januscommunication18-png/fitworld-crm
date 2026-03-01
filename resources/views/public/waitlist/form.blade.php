<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Join the Waitlist - FitCRM</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
    </style>
</head>
<body class="bg-base-200">
    <div class="w-full max-w-lg">
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <h2 class="card-title text-2xl mb-2">Join the Waitlist</h2>
                <p class="text-base-content/60 mb-6">Be the first to know when FitCRM launches. Get exclusive founding member pricing!</p>

                @if($errors->any())
                    <div class="alert alert-error mb-4">
                        <span class="icon-[tabler--alert-circle] size-5"></span>
                        <div>
                            <ul class="list-disc list-inside">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                <form action="{{ route('public.waitlist.store') }}" method="POST" id="waitlist-form">
                    @csrf

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- First Name --}}
                        <div>
                            <label class="label-text" for="first_name">First Name <span class="text-error">*</span></label>
                            <input type="text" id="first_name" name="first_name"
                                value="{{ old('first_name') }}"
                                class="input w-full @error('first_name') input-error @enderror"
                                placeholder="John"
                                required>
                        </div>

                        {{-- Last Name --}}
                        <div>
                            <label class="label-text" for="last_name">Last Name</label>
                            <input type="text" id="last_name" name="last_name"
                                value="{{ old('last_name') }}"
                                class="input w-full @error('last_name') input-error @enderror"
                                placeholder="Doe">
                        </div>
                    </div>

                    {{-- Email --}}
                    <div class="mt-4">
                        <label class="label-text" for="email">Email <span class="text-error">*</span></label>
                        <input type="email" id="email" name="email"
                            value="{{ old('email') }}"
                            class="input w-full @error('email') input-error @enderror"
                            placeholder="john@example.com"
                            required>
                    </div>

                    {{-- Studio Name --}}
                    <div class="mt-4">
                        <label class="label-text" for="studio_name">Studio Name</label>
                        <input type="text" id="studio_name" name="studio_name"
                            value="{{ old('studio_name') }}"
                            class="input w-full @error('studio_name') input-error @enderror"
                            placeholder="Zen Yoga Studio">
                    </div>

                    {{-- Studio Type (Custom searchable multi-select) --}}
                    <div class="mt-4">
                        <label class="label-text mb-1">Studio Type</label>

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
                            <div id="studio-type-panel" class="absolute left-0 right-0 top-full mt-1 bg-base-100 border border-base-content/20 rounded-lg shadow-lg z-50 hidden">
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
                    </div>

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

                    {{-- Member Size --}}
                    <div class="mt-4">
                        <label class="label-text" for="member_size">How many members?</label>
                        <select id="member_size" name="member_size" class="select w-full @error('member_size') select-error @enderror">
                            <option value="">How many members?</option>
                            @foreach(\App\Models\ProspectWaitlist::getMemberSizes() as $value => $label)
                                <option value="{{ $value }}" {{ old('member_size') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary w-full mt-6">
                        <span class="icon-[tabler--send] size-5"></span>
                        Join Waitlist
                    </button>
                </form>

                <p class="text-center text-sm text-base-content/50 mt-4">
                    We respect your privacy. No spam, ever.
                </p>
            </div>
        </div>

        <p class="text-center text-sm text-base-content/40 mt-4">
            Powered by <a href="{{ url('/') }}" class="link link-hover" target="_blank">FitCRM</a>
        </p>
    </div>
</body>
</html>
