<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Your Intake Form - {{ $host->studio_name }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f5;
            margin: 0;
            padding: 0;
        }
        .email-wrapper {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .email-container {
            background: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        .email-header {
            background: #6366f1;
            color: #ffffff;
            padding: 30px;
            text-align: center;
        }
        .email-header .icon {
            font-size: 48px;
            margin-bottom: 12px;
        }
        .email-header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .email-header .studio-name {
            margin-top: 8px;
            opacity: 0.9;
            font-size: 14px;
        }
        .email-body {
            padding: 30px;
        }
        .greeting {
            font-size: 16px;
            margin-bottom: 20px;
        }
        .info-box {
            background: #f0f9ff;
            border-left: 4px solid #3b82f6;
            padding: 16px;
            border-radius: 0 6px 6px 0;
            margin-bottom: 24px;
        }
        .info-box strong {
            color: #1e40af;
        }
        .form-list {
            margin: 24px 0;
        }
        .form-item {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 12px;
        }
        .form-item-title {
            font-weight: 600;
            font-size: 16px;
            color: #1a1a1a;
            margin-bottom: 12px;
        }
        .form-item-badge {
            display: inline-block;
            background: #fee2e2;
            color: #991b1b;
            font-size: 11px;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 4px;
            margin-left: 8px;
            text-transform: uppercase;
        }
        .cta-button {
            display: inline-block;
            background: #4f46e5;
            color: #ffffff !important;
            padding: 12px 24px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
        }
        .cta-button:hover {
            background: #4338ca;
        }
        .note-box {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 16px;
            border-radius: 0 6px 6px 0;
            margin: 24px 0;
            font-size: 14px;
        }
        .email-footer {
            padding: 24px 30px;
            background: #f8fafc;
            text-align: center;
            font-size: 13px;
            color: #64748b;
        }
        .email-footer a {
            color: #4f46e5;
            text-decoration: none;
        }
        @media only screen and (max-width: 600px) {
            .email-wrapper {
                padding: 10px;
            }
            .email-header, .email-body {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-container">
            <div class="email-header">
                <div class="icon">&#128203;</div>
                <h1>Intake Form Required</h1>
                <div class="studio-name">{{ $host->studio_name }}</div>
            </div>

            <div class="email-body">
                <p class="greeting">
                    Hi {{ $client->first_name }},
                </p>

                <p>
                    Thank you for booking <strong>{{ $itemName }}</strong>!
                    @if($itemDatetime)
                    Your session is scheduled for <strong>{{ $itemDatetime }}</strong>.
                    @endif
                </p>

                <div class="info-box">
                    <strong>Action Required:</strong> Please complete the following intake form(s) before your appointment.
                    This helps us provide you with the best possible experience.
                </div>

                <div class="form-list">
                    @foreach($forms as $form)
                    <div class="form-item">
                        <div class="form-item-title">
                            {{ $form['name'] }}
                            @if($form['required'])
                            <span class="form-item-badge">Required</span>
                            @endif
                        </div>
                        <a href="{{ $form['url'] }}" class="cta-button">
                            Complete Form
                        </a>
                    </div>
                    @endforeach
                </div>

                <div class="note-box">
                    <strong>Why is this important?</strong><br>
                    Completing your intake form helps us understand your needs, health considerations,
                    and goals so we can tailor your experience accordingly.
                </div>

                <p style="text-align: center; color: #64748b; font-size: 13px;">
                    Having trouble? Copy and paste this link into your browser:<br>
                    <code style="font-size: 11px;">{{ $forms[0]['url'] ?? '' }}</code>
                </p>
            </div>

            <div class="email-footer">
                <p>
                    This email was sent by <strong>{{ $host->studio_name }}</strong>
                </p>
                @if($host->email)
                <p>
                    Questions? Contact us at <a href="mailto:{{ $host->email }}">{{ $host->email }}</a>
                </p>
                @endif
            </div>
        </div>
    </div>
</body>
</html>
