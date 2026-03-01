<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Thank You - FitCRM</title>
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
        }

        .success-container {
            width: 100%;
            background: #fff;
            border-radius: 16px;
            padding: 3rem 2rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.1);
            border: 1px solid #E8E4DD;
            text-align: center;
        }

        .success-icon {
            width: 80px;
            height: 80px;
            background: rgba(34, 197, 94, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
        }

        .success-icon svg {
            width: 40px;
            height: 40px;
            color: #22C55E;
        }

        .success-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: #2D2A26;
            margin-bottom: 0.75rem;
        }

        .success-message {
            font-size: 1rem;
            color: #6B6660;
            margin-bottom: 1.5rem;
            max-width: 400px;
            margin-left: auto;
            margin-right: auto;
        }

        .success-note {
            background: #F5F5F5;
            border-radius: 8px;
            padding: 1rem;
            font-size: 0.875rem;
            color: #6B6660;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .success-note svg {
            width: 18px;
            height: 18px;
            color: #E8553D;
            flex-shrink: 0;
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
    <div class="success-container">
        <div class="success-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
        </div>

        <h1 class="success-title">You're on the list!</h1>

        <p class="success-message">
            Thank you for joining the FitCRM waitlist. We'll notify you as soon as we launch with exclusive founding member pricing.
        </p>

        <div class="success-note">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                <polyline points="22,6 12,13 2,6"></polyline>
            </svg>
            <span>Check your inbox for a confirmation email.</span>
        </div>
    </div>

    <p class="powered-by">
        Powered by <a href="https://fitcrm.biz" target="_blank">FitCRM</a>
    </p>
</body>
</html>
