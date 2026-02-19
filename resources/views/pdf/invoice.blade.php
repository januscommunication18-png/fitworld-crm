<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #333;
            background: #fff;
        }

        .container {
            padding: 40px;
        }

        /* Header */
        .header {
            display: table;
            width: 100%;
            margin-bottom: 40px;
        }

        .header-left {
            display: table-cell;
            width: 60%;
            vertical-align: top;
        }

        .header-right {
            display: table-cell;
            width: 40%;
            vertical-align: top;
            text-align: right;
        }

        .studio-name {
            font-size: 24px;
            font-weight: bold;
            color: #1a1a1a;
            margin-bottom: 8px;
        }

        .studio-info {
            font-size: 11px;
            color: #666;
            line-height: 1.6;
        }

        .invoice-title {
            font-size: 32px;
            font-weight: bold;
            color: #4f46e5;
            margin-bottom: 8px;
        }

        .invoice-number {
            font-size: 14px;
            color: #666;
            margin-bottom: 4px;
        }

        .invoice-date {
            font-size: 11px;
            color: #888;
        }

        /* Status Badge */
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 10px;
        }

        .status-paid {
            background: #dcfce7;
            color: #166534;
        }

        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .status-draft {
            background: #f3f4f6;
            color: #4b5563;
        }

        .status-void {
            background: #fee2e2;
            color: #991b1b;
        }

        /* Billing Section */
        .billing-section {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }

        .billing-from, .billing-to {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }

        .billing-label {
            font-size: 10px;
            font-weight: bold;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }

        .billing-name {
            font-size: 14px;
            font-weight: bold;
            color: #1a1a1a;
            margin-bottom: 4px;
        }

        .billing-details {
            font-size: 11px;
            color: #666;
            line-height: 1.6;
        }

        /* Items Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        .items-table th {
            background: #f8fafc;
            border-bottom: 2px solid #e2e8f0;
            padding: 12px 10px;
            text-align: left;
            font-size: 10px;
            font-weight: bold;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .items-table th.text-right {
            text-align: right;
        }

        .items-table td {
            padding: 14px 10px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: top;
        }

        .items-table td.text-right {
            text-align: right;
        }

        .item-description {
            font-weight: 500;
            color: #1a1a1a;
            margin-bottom: 2px;
        }

        .item-meta {
            font-size: 10px;
            color: #888;
        }

        /* Totals */
        .totals-section {
            width: 100%;
        }

        .totals-table {
            width: 280px;
            margin-left: auto;
            border-collapse: collapse;
        }

        .totals-table td {
            padding: 8px 0;
        }

        .totals-table td:first-child {
            color: #666;
        }

        .totals-table td:last-child {
            text-align: right;
            font-weight: 500;
        }

        .totals-table .total-row td {
            border-top: 2px solid #1a1a1a;
            padding-top: 12px;
            font-size: 16px;
            font-weight: bold;
            color: #1a1a1a;
        }

        .totals-table .total-row td:last-child {
            color: #4f46e5;
        }

        /* Notes */
        .notes-section {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
        }

        .notes-label {
            font-size: 10px;
            font-weight: bold;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }

        .notes-content {
            font-size: 11px;
            color: #666;
            line-height: 1.6;
        }

        /* Payment Info */
        .payment-info {
            margin-top: 30px;
            padding: 16px;
            background: #f8fafc;
            border-radius: 6px;
        }

        .payment-info-label {
            font-size: 10px;
            font-weight: bold;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }

        .payment-info-content {
            font-size: 11px;
            color: #666;
        }

        /* Footer */
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            font-size: 10px;
            color: #888;
        }

        .footer a {
            color: #4f46e5;
            text-decoration: none;
        }

        /* Transaction ID */
        .transaction-id {
            margin-top: 10px;
            font-size: 10px;
            color: #888;
        }

        .transaction-id code {
            font-family: monospace;
            background: #f3f4f6;
            padding: 2px 6px;
            border-radius: 3px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="header-left">
                <div class="studio-name">{{ $host->studio_name }}</div>
                <div class="studio-info">
                    @if($host->email){{ $host->email }}<br>@endif
                    @if($host->phone){{ $host->phone }}<br>@endif
                    @if($host->address)
                        {{ $host->address }}<br>
                        @if($host->city){{ $host->city }}, @endif
                        @if($host->state){{ $host->state }} @endif
                        @if($host->postal_code){{ $host->postal_code }}@endif
                    @endif
                </div>
            </div>
            <div class="header-right">
                <div class="invoice-title">INVOICE</div>
                <div class="invoice-number">{{ $invoice->invoice_number }}</div>
                <div class="invoice-date">
                    Issued: {{ $invoice->issue_date->format('M j, Y') }}<br>
                    @if($invoice->due_date)
                    Due: {{ $invoice->due_date->format('M j, Y') }}
                    @endif
                </div>
                <div class="status-badge status-{{ $invoice->status }}">
                    {{ ucfirst($invoice->status) }}
                </div>
            </div>
        </div>

        <!-- Billing Section -->
        <div class="billing-section">
            <div class="billing-from">
                <div class="billing-label">From</div>
                <div class="billing-name">{{ $host->studio_name }}</div>
                <div class="billing-details">
                    @if($host->email){{ $host->email }}<br>@endif
                    @if($host->phone){{ $host->phone }}@endif
                </div>
            </div>
            <div class="billing-to">
                <div class="billing-label">Bill To</div>
                <div class="billing-name">{{ $client->full_name }}</div>
                <div class="billing-details">
                    {{ $client->email }}<br>
                    @if($client->phone){{ $client->phone }}<br>@endif
                    @php
                        $billing = $invoice->billing_info ?? [];
                    @endphp
                    @if(!empty($billing['address']))
                        {{ $billing['address'] }}<br>
                        @if(!empty($billing['city'])){{ $billing['city'] }}, @endif
                        @if(!empty($billing['state'])){{ $billing['state'] }} @endif
                        @if(!empty($billing['postal_code'])){{ $billing['postal_code'] }}@endif
                    @endif
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 50%;">Description</th>
                    <th class="text-right" style="width: 15%;">Qty</th>
                    <th class="text-right" style="width: 17%;">Unit Price</th>
                    <th class="text-right" style="width: 18%;">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                <tr>
                    <td>
                        <div class="item-description">{{ $item->description }}</div>
                        @php
                            $meta = $item->metadata ?? [];
                        @endphp
                        @if(!empty($meta['datetime']) || !empty($meta['instructor']) || !empty($meta['location']))
                        <div class="item-meta">
                            @if(!empty($meta['datetime'])){{ $meta['datetime'] }}@endif
                            @if(!empty($meta['instructor'])) &bull; {{ $meta['instructor'] }}@endif
                            @if(!empty($meta['location'])) &bull; {{ $meta['location'] }}@endif
                        </div>
                        @endif
                    </td>
                    <td class="text-right">{{ $item->quantity }}</td>
                    <td class="text-right">{{ $item->formatted_unit_price }}</td>
                    <td class="text-right">{{ $item->formatted_total_price }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals -->
        <div class="totals-section">
            <table class="totals-table">
                <tr>
                    <td>Subtotal</td>
                    <td>{{ $invoice->formatted_subtotal }}</td>
                </tr>
                @if($invoice->tax_amount > 0)
                <tr>
                    <td>Tax</td>
                    <td>${{ number_format($invoice->tax_amount, 2) }}</td>
                </tr>
                @endif
                @if($invoice->discount_amount > 0)
                <tr>
                    <td>Discount</td>
                    <td>-${{ number_format($invoice->discount_amount, 2) }}</td>
                </tr>
                @endif
                <tr class="total-row">
                    <td>Total</td>
                    <td>{{ $invoice->formatted_total }}</td>
                </tr>
            </table>
        </div>

        <!-- Payment Info -->
        @if($invoice->transaction)
        <div class="payment-info">
            <div class="payment-info-label">Payment Information</div>
            <div class="payment-info-content">
                <strong>Method:</strong> {{ $invoice->transaction->payment_method_label }}<br>
                @if($invoice->transaction->is_paid)
                <strong>Paid:</strong> {{ $invoice->paid_at?->format('M j, Y g:i A') ?? 'Yes' }}<br>
                @endif
                <strong>Transaction ID:</strong> <code>{{ $invoice->transaction->transaction_id }}</code>
            </div>
        </div>
        @endif

        <!-- Notes -->
        @if($invoice->notes)
        <div class="notes-section">
            <div class="notes-label">Notes</div>
            <div class="notes-content">{{ $invoice->notes }}</div>
        </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            Thank you for your business!<br>
            @if($host->subdomain)
            <a href="https://{{ $host->subdomain }}.fitcrm.biz">{{ $host->subdomain }}.fitcrm.biz</a>
            @endif
        </div>
    </div>
</body>
</html>
