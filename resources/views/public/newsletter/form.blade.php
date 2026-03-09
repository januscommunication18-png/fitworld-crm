<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Subscribe to Newsletter - FitCRM</title>
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
            overflow: hidden;
        }

        body {
            font-family: 'DM Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: transparent;
            color: #2D2A26;
            line-height: 1.6;
            padding: 0;
            margin: 0;
            overflow: hidden;
        }

        .newsletter__container {
            width: 100%;
            max-width: 900px;
            background: #fff;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.1);
            border: 1px solid #E8E4DD;
            box-sizing: border-box;
        }

        .newsletter__header {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .newsletter__icon {
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, #E8553D 0%, #FF7A5A 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
        }

        .newsletter__icon svg {
            width: 28px;
            height: 28px;
            color: #fff;
        }

        .newsletter__title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #2D2A26;
            margin-bottom: 0.5rem;
        }

        .newsletter__subtitle {
            font-size: 0.9375rem;
            color: #6B6660;
        }

        .newsletter__form {
            max-width: 100%;
        }

        .newsletter__form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        @media (max-width: 400px) {
            .newsletter__form-row {
                grid-template-columns: 1fr;
            }
        }

        .newsletter__field {
            margin-bottom: 1rem;
        }

        .newsletter__field label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: #2D2A26;
            margin-bottom: 0.5rem;
        }

        .newsletter__field .required {
            color: #E8553D;
        }

        .newsletter__field input {
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

        .newsletter__field input:focus {
            outline: none;
            border-color: #E8553D;
            box-shadow: 0 0 0 3px rgba(232, 85, 61, 0.1);
        }

        .newsletter__field input::placeholder {
            color: #9A9590;
        }

        .newsletter__field input.error {
            border-color: #E8553D;
        }

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

        .btn--block {
            width: 100%;
        }

        .btn svg {
            width: 18px;
            height: 18px;
        }

        .newsletter__disclaimer {
            text-align: center;
            font-size: 0.8125rem;
            color: #9A9590;
            margin-top: 1rem;
        }

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
    <div class="newsletter__container">
        @if($errors->any())
            <div class="error-list">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form class="newsletter__form" action="{{ route('public.newsletter.store') }}" method="POST" novalidate>
            @csrf
            <div class="newsletter__form-row">
                <div class="newsletter__field">
                    <label for="first_name">First Name <span class="required">*</span></label>
                    <input type="text" id="first_name" name="first_name"
                        value="{{ old('first_name') }}"
                        class="@error('first_name') error @enderror"
                        placeholder="Jane"
                        required
                        maxlength="50"
                        autocomplete="given-name">
                </div>
                <div class="newsletter__field">
                    <label for="last_name">Last Name</label>
                    <input type="text" id="last_name" name="last_name"
                        value="{{ old('last_name') }}"
                        class="@error('last_name') error @enderror"
                        placeholder="Smith"
                        maxlength="50"
                        autocomplete="family-name">
                </div>
            </div>

            <div class="newsletter__field">
                <label for="email">Email Address <span class="required">*</span></label>
                <input type="email" id="email" name="email"
                    value="{{ old('email') }}"
                    class="@error('email') error @enderror"
                    placeholder="jane@example.com"
                    required
                    autocomplete="email">
            </div>

            <button type="submit" class="btn btn--primary btn--block">
                Subscribe
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5 12h14"/>
                    <path d="m12 5 7 7-7 7"/>
                </svg>
            </button>
            <p class="newsletter__disclaimer">No spam, ever. Unsubscribe anytime.</p>
        </form>

        <p class="powered-by">
            Powered by <a href="https://fitcrm.biz" target="_blank">FitCRM</a>
        </p>
    </div>
</body>
</html>
