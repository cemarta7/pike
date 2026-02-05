<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $invoice_number }}</title>
    <style>
        @page {
            margin: 40px 50px;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            line-height: 1.4;
            margin: 0;
            padding: 0;
            color: #333;
        }

        .header {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }

        .header-left {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }

        .header-right {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            text-align: right;
        }

        .logo {
            max-width: 180px;
            max-height: 80px;
            margin-bottom: 15px;
        }

        .invoice-title {
            font-size: 32px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }

        .invoice-number {
            font-size: 16px;
            color: #666;
        }

        .from-section {
            margin-bottom: 20px;
            font-size: 14px;
            line-height: 1.5;
        }

        .details-row {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }

        .details-left {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }

        .details-right {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }

        .detail-label {
            color: #666;
            font-size: 12px;
            margin-bottom: 4px;
        }

        .detail-value {
            font-size: 14px;
            margin-bottom: 12px;
        }

        .address-section {
            display: table;
            width: 100%;
            margin-bottom: 25px;
        }

        .bill-to, .ship-to {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }

        .section-title {
            font-weight: bold;
            font-size: 12px;
            color: #666;
            margin-bottom: 8px;
            text-transform: uppercase;
        }

        .address-content {
            font-size: 14px;
            line-height: 1.5;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .items-table th {
            background-color: #2d3748;
            color: white;
            padding: 12px 10px;
            text-align: left;
            font-weight: 600;
            font-size: 13px;
        }

        .items-table th:nth-child(2),
        .items-table th:nth-child(3),
        .items-table th:nth-child(4) {
            text-align: right;
        }

        .items-table td {
            padding: 12px 10px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 14px;
        }

        .items-table td:nth-child(2),
        .items-table td:nth-child(3),
        .items-table td:nth-child(4) {
            text-align: right;
        }

        .bottom-section {
            display: table;
            width: 100%;
            margin-top: 20px;
        }

        .notes-terms {
            display: table-cell;
            width: 55%;
            vertical-align: top;
            padding-right: 30px;
        }

        .totals-section {
            display: table-cell;
            width: 45%;
            vertical-align: top;
        }

        .notes-box, .terms-box {
            margin-bottom: 20px;
        }

        .notes-title, .terms-title {
            font-weight: bold;
            font-size: 13px;
            color: #333;
            margin-bottom: 6px;
        }

        .notes-content, .terms-content {
            font-size: 13px;
            color: #666;
            line-height: 1.5;
        }

        .totals-table {
            width: 100%;
            border-collapse: collapse;
        }

        .totals-table td {
            padding: 8px 0;
            font-size: 14px;
        }

        .totals-table .label {
            text-align: right;
            padding-right: 15px;
            color: #666;
        }

        .totals-table .amount {
            text-align: right;
            width: 100px;
        }

        .totals-table .total-row td {
            font-weight: bold;
            font-size: 16px;
            color: #333;
            padding-top: 12px;
        }

        .totals-table .balance-row td {
            font-weight: bold;
            font-size: 18px;
            color: #2d3748;
            border-top: 2px solid #2d3748;
            padding-top: 12px;
        }
    </style>
</head>

<body>
    <!-- Header -->
    <div class="header">
        <div class="header-left">
            @if ($logo_base64)
                <img src="data:image/png;base64,{{ $logo_base64 }}" alt="Logo" class="logo" />
            @endif
            <div class="from-section">
                {!! nl2br(e($from_address ?? '')) !!}
            </div>
        </div>
        <div class="header-right">
            <div class="invoice-title">INVOICE</div>
            <div class="invoice-number"># {{ $invoice_number }}</div>
        </div>
    </div>

    <!-- Invoice Details -->
    <div class="details-row">
        <div class="details-left">
            <!-- Addresses -->
            <div class="address-section">
                <div class="bill-to">
                    <div class="section-title">Bill To</div>
                    <div class="address-content">
                        {!! nl2br(e($bill_to_address ?? '')) !!}
                    </div>
                </div>
                @if ($ship_to_address ?? false)
                    <div class="ship-to">
                        <div class="section-title">Ship To</div>
                        <div class="address-content">
                            {!! nl2br(e($ship_to_address)) !!}
                        </div>
                    </div>
                @endif
            </div>
        </div>
        <div class="details-right">
            <div class="detail-label">Date</div>
            <div class="detail-value">{{ $generated_date }}</div>

            @if ($payment_terms ?? false)
                <div class="detail-label">Payment Terms</div>
                <div class="detail-value">{{ $payment_terms }}</div>
            @endif

            <div class="detail-label">Due Date</div>
            <div class="detail-value">{{ $due_date }}</div>

            @if ($purchase_order ?? false)
                <div class="detail-label">PO Number</div>
                <div class="detail-value">{{ $purchase_order }}</div>
            @endif
        </div>
    </div>

    <!-- Items Table -->
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 50%;">Item</th>
                <th style="width: 15%;">Quantity</th>
                <th style="width: 15%;">Rate</th>
                <th style="width: 20%;">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($line_items as $item)
                <tr>
                    <td>{{ $item['description'] ?? '' }}</td>
                    <td>{{ $item['quantity'] ?? 1 }}</td>
                    <td>${{ number_format($item['rate'] ?? 0, 2) }}</td>
                    <td>${{ number_format(($item['quantity'] ?? 1) * ($item['rate'] ?? 0), 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Bottom Section: Notes/Terms and Totals -->
    <div class="bottom-section">
        <div class="notes-terms">
            @if ($notes ?? false)
                <div class="notes-box">
                    <div class="notes-title">Notes</div>
                    <div class="notes-content">{!! nl2br(e($notes)) !!}</div>
                </div>
            @endif

            @if ($terms ?? false)
                <div class="terms-box">
                    <div class="terms-title">Terms</div>
                    <div class="terms-content">{!! nl2br(e($terms)) !!}</div>
                </div>
            @endif
        </div>

        <div class="totals-section">
            <table class="totals-table">
                <tr>
                    <td class="label">Subtotal</td>
                    <td class="amount">${{ number_format($subtotal, 2) }}</td>
                </tr>
                @if (($tax_percent ?? 0) > 0)
                    <tr>
                        <td class="label">Tax ({{ $tax_percent }}%)</td>
                        <td class="amount">${{ number_format($tax_amount, 2) }}</td>
                    </tr>
                @endif
                @if (($discount ?? 0) > 0)
                    <tr>
                        <td class="label">Discount</td>
                        <td class="amount">-${{ number_format($discount, 2) }}</td>
                    </tr>
                @endif
                @if (($shipping ?? 0) > 0)
                    <tr>
                        <td class="label">Shipping</td>
                        <td class="amount">${{ number_format($shipping, 2) }}</td>
                    </tr>
                @endif
                <tr class="total-row">
                    <td class="label">Total</td>
                    <td class="amount">${{ number_format($total, 2) }}</td>
                </tr>
                @if (($amount_paid ?? 0) > 0)
                    <tr>
                        <td class="label">Amount Paid</td>
                        <td class="amount">-${{ number_format($amount_paid, 2) }}</td>
                    </tr>
                @endif
                <tr class="balance-row">
                    <td class="label">Balance Due</td>
                    <td class="amount">${{ number_format($balance_due, 2) }}</td>
                </tr>
            </table>
        </div>
    </div>
</body>

</html>
