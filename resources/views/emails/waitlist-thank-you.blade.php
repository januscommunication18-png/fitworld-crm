<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>You're on the FitCRM Waitlist!</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #2D2A26;
            background-color: #f5f5f5;
            margin: 0;
            padding: 20px;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .email-header {
            background: #E8553D;
            color: #ffffff;
            padding: 30px;
            text-align: center;
        }
        .email-header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .email-body {
            padding: 30px;
        }
        .email-body h2 {
            color: #2D2A26;
            font-size: 18px;
            margin-top: 25px;
            margin-bottom: 15px;
        }
        .email-body p {
            margin: 0 0 15px;
            color: #4a4a4a;
        }
        .details-box {
            background: #f9f9f9;
            border-radius: 6px;
            padding: 20px;
            margin: 20px 0;
        }
        .details-box p {
            margin: 5px 0;
        }
        .details-box strong {
            color: #2D2A26;
        }
        .email-footer {
            padding: 20px 30px;
            background: #f9f9f9;
            text-align: center;
            font-size: 14px;
            color: #888;
        }
        .email-footer a {
            color: #E8553D;
            text-decoration: none;
        }
        ul {
            padding-left: 20px;
        }
        li {
            margin-bottom: 8px;
            color: #4a4a4a;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h1>You're on the Waitlist!</h1>
        </div>

        <div class="email-body">
            <p>Hi {{ $waitlistEntry->first_name }},</p>

            <p>Thank you for joining the <strong>FitCRM</strong> waitlist! We're thrilled to have you on board.</p>

            <h2>What happens next?</h2>

            <p>We're working hard to build the all-in-one studio management platform that boutique fitness businesses deserve. As a waitlist member, you'll be among the first to:</p>

            <ul>
                <li><strong>Get early access</strong> when we launch</li>
                <li><strong>Receive founding member pricing</strong> (exclusive discounts)</li>
                <li><strong>Help shape the product</strong> with your feedback</li>
            </ul>

            <h2>Your Details</h2>

            <div class="details-box">
                <p><strong>Name:</strong> {{ $waitlistEntry->first_name }} {{ $waitlistEntry->last_name }}</p>
                <p><strong>Email:</strong> {{ $waitlistEntry->email }}</p>
                @if($waitlistEntry->studio_name)
                <p><strong>Studio:</strong> {{ $waitlistEntry->studio_name }}</p>
                @endif
            </div>

            <p>We'll reach out soon with updates on our progress. In the meantime, feel free to reply to this email if you have any questions or suggestions — we read every message.</p>

            <p>Thanks for believing in us!</p>

            <p><strong>The FitCRM Team</strong></p>
        </div>

        <div class="email-footer">
            <p>&copy; {{ date('Y') }} FitCRM. All rights reserved.</p>
            <p><a href="mailto:hello@fitcrm.io">hello@fitcrm.io</a></p>
        </div>
    </div>
</body>
</html>
