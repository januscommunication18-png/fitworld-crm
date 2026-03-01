<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Join the Waitlist - FitCRM</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html, body {
            margin: 0;
            padding: 0;
            background: transparent;
        }

        body {
            font-family: 'DM Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: transparent;
            color: #2D2A26;
            line-height: 1.6;
            padding: 0;
            margin: 0;
        }

        .waitlist__container {
            width: 100%;
            background: #fff;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.1);
            border: 1px solid #E8E4DD;
            box-sizing: border-box;
        }

        .waitlist__form {
            max-width: 100%;
        }

        .waitlist__form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        @media (max-width: 480px) {
            .waitlist__form-row {
                grid-template-columns: 1fr;
            }
        }

        .waitlist__field {
            margin-bottom: 1rem;
        }

        .waitlist__field label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: #2D2A26;
            margin-bottom: 0.5rem;
        }

        .waitlist__field .required {
            color: #E8553D;
        }

        .waitlist__field input,
        .waitlist__field select {
            width: 100%;
            padding: 0.875rem 1rem;
            font-size: 1rem;
            font-family: inherit;
            border: 1px solid #E8E4DD;
            border-radius: 8px;
            background: #fff;
            color: #2D2A26;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .waitlist__field input:focus,
        .waitlist__field select:focus {
            outline: none;
            border-color: #E8553D;
            box-shadow: 0 0 0 3px rgba(232, 85, 61, 0.1);
        }

        .waitlist__field input::placeholder {
            color: #9A9590;
        }

        .waitlist__field input.error,
        .waitlist__field select.error {
            border-color: #E8553D;
        }

        /* Custom dropdown for studio types */
        .custom-dropdown {
            position: relative;
        }

        .custom-dropdown__toggle {
            width: 100%;
            padding: 0.875rem 1rem;
            font-size: 1rem;
            font-family: inherit;
            border: 1px solid #E8E4DD;
            border-radius: 8px;
            background: #fff;
            color: #2D2A26;
            text-align: left;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .custom-dropdown__toggle:focus {
            outline: none;
            border-color: #E8553D;
            box-shadow: 0 0 0 3px rgba(232, 85, 61, 0.1);
        }

        .custom-dropdown__toggle .placeholder {
            color: #9A9590;
        }

        .custom-dropdown__toggle svg {
            width: 16px;
            height: 16px;
            color: #9A9590;
            flex-shrink: 0;
        }

        .custom-dropdown__panel {
            position: absolute;
            left: 0;
            right: 0;
            top: calc(100% + 4px);
            background: #fff;
            border: 1px solid #E8E4DD;
            border-radius: 8px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.12);
            z-index: 100;
            display: none;
        }

        .custom-dropdown__panel.open {
            display: block;
        }

        .custom-dropdown__search {
            padding: 0.75rem;
            border-bottom: 1px solid #E8E4DD;
        }

        .custom-dropdown__search input {
            width: 100%;
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
            border: 1px solid #E8E4DD;
            border-radius: 6px;
            background: #F9F9F9;
        }

        .custom-dropdown__search input:focus {
            outline: none;
            border-color: #E8553D;
        }

        .custom-dropdown__options {
            max-height: 200px;
            overflow-y: auto;
            padding: 0.5rem;
        }

        .custom-dropdown__option {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 0.75rem;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.875rem;
            transition: background 0.15s;
        }

        .custom-dropdown__option:hover {
            background: #F5F5F5;
        }

        .custom-dropdown__option input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: #E8553D;
            cursor: pointer;
        }

        /* Submit button */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 1rem 2rem;
            font-size: 1rem;
            font-weight: 600;
            font-family: inherit;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
        }

        .btn--primary {
            background: #E8553D;
            color: #fff;
        }

        .btn--primary:hover {
            background: #D4472F;
            transform: translateY(-1px);
        }

        .btn--lg {
            padding: 1rem 2rem;
            font-size: 1.0625rem;
        }

        .btn--block {
            width: 100%;
        }

        .btn svg {
            width: 18px;
            height: 18px;
        }

        .waitlist__disclaimer {
            text-align: center;
            font-size: 0.8125rem;
            color: #9A9590;
            margin-top: 1rem;
        }

        /* Error messages */
        .error-list {
            background: #FEF2F2;
            border: 1px solid #FECACA;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }

        .error-list ul {
            list-style: disc;
            margin-left: 1.25rem;
            color: #DC2626;
            font-size: 0.875rem;
        }

        /* Powered by */
        .powered-by {
            text-align: center;
            font-size: 0.75rem;
            color: #9A9590;
            margin-top: 1.5rem;
        }

        .powered-by a {
            color: #E8553D;
            text-decoration: none;
        }

        .powered-by a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="waitlist__container">
        @if($errors->any())
            <div class="error-list">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form class="waitlist__form" id="waitlist-form" action="{{ route('public.waitlist.store') }}" method="POST" novalidate>
        <div class="waitlist__form-row">
            <div class="waitlist__field">
                <label for="first_name">First Name <span class="required">*</span></label>
                <input type="text" id="first_name" name="first_name"
                    value="{{ old('first_name') }}"
                    class="@error('first_name') error @enderror"
                    placeholder="Enter first name"
                    required
                    autocomplete="given-name">
            </div>
            <div class="waitlist__field">
                <label for="last_name">Last Name</label>
                <input type="text" id="last_name" name="last_name"
                    value="{{ old('last_name') }}"
                    class="@error('last_name') error @enderror"
                    placeholder="Enter last name"
                    autocomplete="family-name">
            </div>
        </div>

        <div class="waitlist__field">
            <label for="email">Email Address <span class="required">*</span></label>
            <input type="email" id="email" name="email"
                value="{{ old('email') }}"
                class="@error('email') error @enderror"
                placeholder="you@yourstudio.com"
                required
                autocomplete="email">
        </div>

        <div class="waitlist__field">
            <label for="studio_name">Studio Name</label>
            <input type="text" id="studio_name" name="studio_name"
                value="{{ old('studio_name') }}"
                class="@error('studio_name') error @enderror"
                placeholder="Your Studio Name"
                autocomplete="organization">
        </div>

        <div class="waitlist__field">
            <label for="studio_type">What type of studio do you run?</label>
            <div class="custom-dropdown" id="studio-type-dropdown">
                <button type="button" class="custom-dropdown__toggle" id="studio-type-toggle" onclick="toggleStudioDropdown()">
                    <span id="studio-type-display" class="placeholder">Select studio types...</span>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M6 9l6 6 6-6"/>
                    </svg>
                </button>
                <div class="custom-dropdown__panel" id="studio-type-panel">
                    <div class="custom-dropdown__search">
                        <input type="text" id="studio-type-search"
                            placeholder="Search studio types..."
                            onclick="event.stopPropagation()"
                            oninput="filterStudioTypes()">
                    </div>
                    <div class="custom-dropdown__options" id="studio-type-options">
                        @foreach(\App\Models\ProspectWaitlist::getStudioTypes() as $value => $label)
                        <label class="custom-dropdown__option studio-option" data-value="{{ $value }}" data-label="{{ $label }}">
                            <input type="checkbox" name="studio_type[]" value="{{ $value }}"
                                {{ in_array($value, old('studio_type', [])) ? 'checked' : '' }}
                                onchange="updateStudioDisplay()">
                            <span>{{ $label }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="waitlist__field">
            <label for="member_size">How many members?</label>
            <select id="member_size" name="member_size" class="@error('member_size') error @enderror">
                <option value="">Select member count...</option>
                @foreach(\App\Models\ProspectWaitlist::getMemberSizes() as $value => $label)
                    <option value="{{ $value }}" {{ old('member_size') === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="btn btn--primary btn--lg btn--block" id="waitlist-submit">
            Join the Waitlist — It's Free
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M5 12h14"/>
                <path d="m12 5 7 7-7 7"/>
            </svg>
        </button>
        <p class="waitlist__disclaimer">No spam, no tricks. We'll only email you about FitCRM updates.</p>
    </form>

    <p class="powered-by">
        Powered by <a href="https://fitcrm.biz" target="_blank">FitCRM</a>
    </p>
    </div>

    <script>
        function toggleStudioDropdown() {
            const panel = document.getElementById('studio-type-panel');
            panel.classList.toggle('open');
            if (panel.classList.contains('open')) {
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
                display.classList.add('placeholder');
            } else if (checked.length <= 2) {
                const labels = Array.from(checked).map(function(cb) {
                    return cb.closest('.studio-option').dataset.label;
                });
                display.textContent = labels.join(', ');
                display.classList.remove('placeholder');
            } else {
                display.textContent = checked.length + ' studio types selected';
                display.classList.remove('placeholder');
            }
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            const dropdown = document.getElementById('studio-type-dropdown');
            const panel = document.getElementById('studio-type-panel');
            if (dropdown && !dropdown.contains(e.target)) {
                panel.classList.remove('open');
            }
        });

        // Initialize display on load
        document.addEventListener('DOMContentLoaded', updateStudioDisplay);
    </script>
</body>
</html>
