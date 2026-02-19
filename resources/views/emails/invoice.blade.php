<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isPaid ? 'Receipt' : 'Invoice' }} from {{ $host->studio_name }}</title>
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
            background: #4f46e5;
            color: #ffffff;
            padding: 30px;
            text-align: center;
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
        .invoice-summary {
            background: #f8fafc;
            border-radius: 6px;
            padding: 20px;
            margin-bottom: 24px;
        }
        .invoice-summary-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e2e8f0;
        }
        .invoice-summary-row:last-child {
            border-bottom: none;
        }
        .invoice-summary-label {
            color: #64748b;
            font-size: 14px;
        }
        .invoice-summary-value {
            font-weight: 500;
            font-size: 14px;
        }
        .total-row {
            margin-top: 12px;
            padding-top: 12px;
            border-top: 2px solid #e2e8f0;
        }
        .total-row .invoice-summary-label {
            font-weight: 600;
            color: #1a1a1a;
            font-size: 16px;
        }
        .total-row .invoice-summary-value {
            font-weight: 700;
            color: #4f46e5;
            font-size: 18px;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status-paid {
            background: #dcfce7;
            color: #166534;
        }
        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }
        .items-list {
            margin-bottom: 24px;
        }
        .item {
            padding: 12px 0;
            border-bottom: 1px solid #e2e8f0;
        }
        .item:last-child {
            border-bottom: none;
        }
        .item-name {
            font-weight: 500;
            margin-bottom: 4px;
        }
        .item-details {
            font-size: 13px;
            color: #64748b;
        }
        .item-price {
            font-weight: 500;
            color: #1a1a1a;
            float: right;
        }
        .cta-button {
            display: inline-block;
            background: #4f46e5;
            color: #ffffff !important;
            padding: 12px 24px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            margin: 16px 0;
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
        .note-box {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 12px 16px;
            margin: 20px 0;
            font-size: 14px;
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
                <h1>{{ $isPaid ? 'Payment Receipt' : 'Invoice' }}</h1>
                <div class="studio-name">{{ $host->studio_name }}</div>
            </div>

            <div class="email-body">
                <p class="greeting">
                    Hi {{ $client->first_name }},
                </p>

                @if($isPaid)
                <p>
                    Thank you for your payment! Here's your receipt for your records.
                </p>
                @else
                <p>
                    Please find your invoice attached. Here's a summary of your purchase:
                </p>
                @endif

                <div class="invoice-summary">
                    <div class="invoice-summary-row">
                        <span class="invoice-summary-label">Invoice Number</span>
                        <span class="invoice-summary-value">{{ $invoice->invoice_number }}</span>
                    </div>
                    <div class="invoice-summary-row">
                        <span class="invoice-summary-label">Date</span>
                        <span class="invoice-summary-value">{{ $invoice->issue_date->format('M j, Y') }}</span>
                    </div>
                    <div class="invoice-summary-row">
                        <span class="invoice-summary-label">Status</span>
                        <span class="invoice-summary-value">
                            <span class="status-badge status-{{ $isPaid ? 'paid' : 'pending' }}">
                                {{ $isPaid ? 'Paid' : 'Pending' }}
                            </span>
                        </span>
                    </div>
                    @if($invoice->transaction)
                    <div class="invoice-summary-row">
                        <span class="invoice-summary-label">Transaction ID</span>
                        <span class="invoice-summary-value" style="font-family: monospace; font-size: 12px;">
                            {{ $invoice->transaction->transaction_id }}
                        </span>
                    </div>
                    @endif
                </div>

                <h3 style="margin-bottom: 12px; font-size: 16px;">Items</h3>
                <div class="items-list">
                    @foreach($items as $item)
                    <div class="item">
                        <span class="item-price">{{ $item->formatted_total_price }}</span>
                        <div class="item-name">{{ $item->description }}</div>
                        @php
                            $meta = $item->metadata ?? [];
                        @endphp
                        @if(!empty($meta['datetime']) || !empty($meta['instructor']))
                        <div class="item-details">
                            @if(!empty($meta['datetime'])){{ $meta['datetime'] }}@endif
                            @if(!empty($meta['instructor'])) with {{ $meta['instructor'] }}@endif
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>

                <div class="invoice-summary">
                    <div class="invoice-summary-row">
                        <span class="invoice-summary-label">Subtotal</span>
                        <span class="invoice-summary-value">{{ $invoice->formatted_subtotal }}</span>
                    </div>
                    @if($invoice->tax_amount > 0)
                    <div class="invoice-summary-row">
                        <span class="invoice-summary-label">Tax</span>
                        <span class="invoice-summary-value">${{ number_format($invoice->tax_amount, 2) }}</span>
                    </div>
                    @endif
                    @if($invoice->discount_amount > 0)
                    <div class="invoice-summary-row">
                        <span class="invoice-summary-label">Discount</span>
                        <span class="invoice-summary-value">-${{ number_format($invoice->discount_amount, 2) }}</span>
                    </div>
                    @endif
                    <div class="invoice-summary-row total-row">
                        <span class="invoice-summary-label">Total</span>
                        <span class="invoice-summary-value">{{ $invoice->formatted_total }}</span>
                    </div>
                </div>

                @if(!$isPaid && $invoice->transaction && $invoice->transaction->payment_method === 'manual')
                <div class="note-box">
                    <strong>Payment Required</strong><br>
                    Please complete your payment using {{ $invoice->transaction->payment_method_label }} to finalize your booking.
                    Contact the studio if you need payment details.
                </div>
                @endif

                @if($invoice->notes)
                <div class="note-box" style="background: #f0f9ff; border-color: #0ea5e9;">
                    <strong>Note:</strong> {{ $invoice->notes }}
                </div>
                @endif

                @if($host->subdomain)
                <p style="text-align: center; margin-top: 24px;">
                    <a href="https://{{ $host->subdomain }}.fitcrm.biz" class="cta-button">
                        Visit {{ $host->studio_name }}
                    </a>
                </p>
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
