<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Subscribed - FitCRM Newsletter</title>
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
            line-height: 1.5;
            padding: 0;
            margin: 0;
            overflow: hidden;
        }

        .success__container {
            width: 100%;
            max-width: 900px;
            background: #fff;
            border-radius: 16px;
            padding: 1.5rem 2rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.1);
            border: 1px solid #E8E4DD;
            box-sizing: border-box;
            display: flex;
            align-items: center;
            gap: 2rem;
        }

        .success__icon {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, #10B981 0%, #34D399 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .success__icon svg {
            width: 32px;
            height: 32px;
            color: #fff;
        }

        .success__content {
            flex: 1;
        }

        .success__title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #2D2A26;
            margin-bottom: 0.25rem;
        }

        .success__message {
            font-size: 0.9375rem;
            color: #6B6660;
            margin-bottom: 0.75rem;
        }

        .success__check-items {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem 1.5rem;
        }

        .success__check-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.8125rem;
            color: #166534;
        }

        .success__check-item svg {
            width: 16px;
            height: 16px;
            flex-shrink: 0;
            color: #10B981;
        }

        .powered-by {
            font-size: 0.75rem;
            color: #9A9590;
            flex-shrink: 0;
        }

        .powered-by a {
            color: #E8553D;
            text-decoration: none;
        }

        .powered-by a:hover {
            text-decoration: underline;
        }

        @media (max-width: 600px) {
            .success__container {
                flex-direction: column;
                text-align: center;
                gap: 1rem;
            }
            .success__check-items {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="success__container">
        <div class="success__icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12"/>
            </svg>
        </div>

        <div class="success__content">
            @if(session('already_subscribed'))
                <h1 class="success__title">Already Subscribed!</h1>
                <p class="success__message">You're already on our mailing list. We'll keep you updated with the latest news.</p>
            @else
                <h1 class="success__title">You're Subscribed!</h1>
                <p class="success__message">Thank you for subscribing! You'll receive updates directly in your inbox.</p>
            @endif

            <div class="success__check-items">
                <div class="success__check-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                    <span>Product updates</span>
                </div>
                <div class="success__check-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                    <span>Tips & best practices</span>
                </div>
                <div class="success__check-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                    <span>Early access</span>
                </div>
            </div>
        </div>

        <p class="powered-by">
            Powered by <a href="https://fitcrm.biz" target="_blank">FitCRM</a>
        </p>
    </div>
</body>
</html>
