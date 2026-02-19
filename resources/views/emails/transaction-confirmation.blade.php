<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isPaid ? 'Booking Confirmed' : 'Booking Received' }} - {{ $host->studio_name }}</title>
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
            background: {{ $isPaid ? '#059669' : '#d97706' }};
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
        .booking-card {
            background: #f8fafc;
            border-radius: 8px;
            padding: 24px;
            margin-bottom: 24px;
            border-left: 4px solid {{ $isPaid ? '#059669' : '#d97706' }};
        }
        .booking-title {
            font-size: 20px;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 16px;
        }
        .booking-detail {
            display: flex;
            align-items: flex-start;
            margin-bottom: 12px;
        }
        .booking-detail-icon {
            width: 20px;
            margin-right: 12px;
            color: #64748b;
        }
        .booking-detail-content {
            flex: 1;
        }
        .booking-detail-label {
            font-size: 12px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .booking-detail-value {
            font-size: 15px;
            color: #1a1a1a;
            font-weight: 500;
        }
        .status-badge {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status-confirmed {
            background: #dcfce7;
            color: #166534;
        }
        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }
        .status-waitlist {
            background: #dbeafe;
            color: #1e40af;
        }
        .transaction-info {
            background: #f1f5f9;
            border-radius: 6px;
            padding: 16px;
            margin-top: 24px;
        }
        .transaction-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e2e8f0;
        }
        .transaction-row:last-child {
            border-bottom: none;
        }
        .transaction-label {
            color: #64748b;
            font-size: 14px;
        }
        .transaction-value {
            font-weight: 500;
            font-size: 14px;
        }
        .total-row {
            margin-top: 8px;
            padding-top: 12px;
            border-top: 2px solid #cbd5e1;
        }
        .total-row .transaction-label {
            font-weight: 600;
            color: #1a1a1a;
        }
        .total-row .transaction-value {
            font-weight: 700;
            color: #059669;
            font-size: 18px;
        }
        .cta-button {
            display: inline-block;
            background: #4f46e5;
            color: #ffffff !important;
            padding: 14px 28px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            margin: 16px 0;
        }
        .note-box {
            padding: 16px;
            border-radius: 6px;
            margin: 20px 0;
            font-size: 14px;
        }
        .note-warning {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
        }
        .note-info {
            background: #dbeafe;
            border-left: 4px solid #3b82f6;
        }
        .note-success {
            background: #dcfce7;
            border-left: 4px solid #22c55e;
        }
        .calendar-note {
            background: #f0f9ff;
            border: 1px solid #bae6fd;
            border-radius: 6px;
            padding: 16px;
            margin: 20px 0;
            text-align: center;
        }
        .calendar-note strong {
            color: #0369a1;
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
        .divider {
            height: 1px;
            background: #e2e8f0;
            margin: 24px 0;
        }
        @media only screen and (max-width: 600px) {
            .email-wrapper {
                padding: 10px;
            }
            .email-header, .email-body {
                padding: 20px;
            }
            .booking-card {
                padding: 16px;
            }
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-container">
            <div class="email-header">
                <div class="icon">
                    @if($isWaitlist)
                        &#9203;
                    @elseif($isPaid)
                        &#10004;
                    @else
                        &#128337;
                    @endif
                </div>
                <h1>
                    @if($isWaitlist)
                        You're on the Waitlist!
                    @elseif($isPaid)
                        Booking Confirmed!
                    @else
                        Booking Received
                    @endif
                </h1>
                <div class="studio-name">{{ $host->studio_name }}</div>
            </div>

            <div class="email-body">
                <p class="greeting">
                    Hi {{ $client->first_name }},
                </p>

                @if($isWaitlist)
                <p>
                    You've been added to the waitlist for <strong>{{ $itemName }}</strong>.
                    We'll notify you as soon as a spot becomes available.
                </p>
                @elseif($isPaid)
                <p>
                    Your booking has been confirmed! Here are your booking details:
                </p>
                @else
                <p>
                    We've received your booking request. Please complete payment to secure your spot.
                </p>
                @endif

                <div class="booking-card">
                    <div class="booking-title">{{ $itemName }}</div>

                    @if($itemDatetime)
                    <div class="booking-detail">
                        <span class="booking-detail-icon">&#128197;</span>
                        <div class="booking-detail-content">
                            <div class="booking-detail-label">When</div>
                            <div class="booking-detail-value">{{ $itemDatetime }}</div>
                        </div>
                    </div>
                    @endif

                    @if($itemInstructor)
                    <div class="booking-detail">
                        <span class="booking-detail-icon">&#128100;</span>
                        <div class="booking-detail-content">
                            <div class="booking-detail-label">Instructor</div>
                            <div class="booking-detail-value">{{ $itemInstructor }}</div>
                        </div>
                    </div>
                    @endif

                    @if($itemLocation)
                    <div class="booking-detail">
                        <span class="booking-detail-icon">&#128205;</span>
                        <div class="booking-detail-content">
                            <div class="booking-detail-label">Location</div>
                            <div class="booking-detail-value">{{ $itemLocation }}</div>
                        </div>
                    </div>
                    @endif

                    <div style="margin-top: 16px;">
                        @if($isWaitlist)
                            <span class="status-badge status-waitlist">Waitlisted</span>
                        @elseif($isPaid)
                            <span class="status-badge status-confirmed">Confirmed</span>
                        @else
                            <span class="status-badge status-pending">Pending Payment</span>
                        @endif
                    </div>
                </div>

                @if($hasCalendarInvite && $isPaid && !$isWaitlist)
                <div class="calendar-note">
                    <strong>&#128197; Calendar Invite Attached</strong><br>
                    <span style="font-size: 13px; color: #64748b;">
                        Open the attached .ics file to add this booking to your calendar.
                    </span>
                </div>
                @endif

                @if($isManualPayment && !$isPaid)
                <div class="note-box note-warning">
                    <strong>&#9888; Payment Required</strong><br>
                    Please complete your payment using <strong>{{ $transaction->payment_method_label }}</strong>
                    to secure your booking. Contact the studio if you need payment details.
                </div>
                @endif

                <div class="transaction-info">
                    <div class="transaction-row">
                        <span class="transaction-label">Transaction ID</span>
                        <span class="transaction-value" style="font-family: monospace; font-size: 12px;">
                            {{ $transaction->transaction_id }}
                        </span>
                    </div>
                    <div class="transaction-row">
                        <span class="transaction-label">Payment Method</span>
                        <span class="transaction-value">{{ $transaction->payment_method_label }}</span>
                    </div>
                    <div class="transaction-row">
                        <span class="transaction-label">Status</span>
                        <span class="transaction-value">
                            {{ $isPaid ? 'Paid' : 'Pending' }}
                        </span>
                    </div>
                    <div class="transaction-row total-row">
                        <span class="transaction-label">Total</span>
                        <span class="transaction-value">{{ $transaction->formatted_total }}</span>
                    </div>
                </div>

                @if($isPaid)
                <div class="note-box note-success">
                    <strong>&#127881; You're all set!</strong><br>
                    We look forward to seeing you. If you need to make any changes,
                    please contact the studio directly.
                </div>
                @endif

                @if($host->subdomain)
                <div style="text-align: center; margin-top: 24px;">
                    <a href="https://{{ $host->subdomain }}.fitcrm.biz" class="cta-button">
                        Visit {{ $host->studio_name }}
                    </a>
                </div>
                @endif
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
                @if($host->phone)
                <p>
                    or call <a href="tel:{{ $host->phone }}">{{ $host->phone }}</a>
                </p>
                @endif
                @if($host->address)
                <p style="margin-top: 12px; font-size: 12px;">
                    {{ $host->address }}
                    @if($host->city), {{ $host->city }}@endif
                    @if($host->state), {{ $host->state }}@endif
                    @if($host->postal_code) {{ $host->postal_code }}@endif
                </p>
                @endif
            </div>
        </div>
    </div>
</body>
</html>
