<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice</title>
    <style>
        @page {
            margin: 120px 50px 80px 50px;
            padding: 50px;
        }
        @media dompdf {
            body {
                font-family: Arial, sans-serif;
                font-size: 20px;
                line-height: 1.4;
                margin: 0;
                padding: 0;
                color: #333;
            }

            .header {
                display: table;
                width: 100%;
                margin-bottom: 30px;
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
                padding-right: 20px;
            }

            .company-logo {
                font-size: 24px;
                font-weight: bold;
                color: #adb3bb;
                margin-bottom: 10px;
                width: 200px;
                height: auto;
            }

            .company-info {
                font-size: 20px;
                line-height: 1.3;
            }

            .invoice-title {
                font-size: 28px;
                font-weight: bold;
                color: #adb3bb;
                margin-bottom: 5px;
            }

            .invoice-info {
                font-size: 20px;
            }

            .billing-section {
                display: table;
                width: 100%;
                margin: 30px 0;
            }

            .bill-to,
            .ship-to {
                display: table-cell;
                width: 33.33%;
                vertical-align: top;
            }

            .notes {
                display: table-cell;
                width: 33.33%;
                vertical-align: top;
            }

            .ship-to {
                margin-left: 4%;
            }

            .section-title {
                font-weight: bold;
                font-size: 12px;
                margin-bottom: 8px;
                color: #adb3bb;
            }

            .customer-info {
                font-size: 20px;
                line-height: 1.3;
            }

            .items-table {
                width: 100%;
                border-collapse: collapse;
                margin: 20px 0;
            }

            .items-table th {
                background-color: #adb3bb;
                color: white;
                padding: 10px 8px;
                text-align: left;
                font-weight: bold;
                font-size: 20px;
            }

            .items-table td {
                padding: 8px;
                border-bottom: 1px solid #ddd;
                font-size: 20px;
            }

            .items-table tr:nth-child(even) {
                background-color: #f8f9fa;
            }

            .text-right {
                text-align: right;
            }

            .text-center {
                text-align: center;
            }

            .totals-section {
                width: 300px;
                float: right;
                margin-top: 20px;
            }

            .totals-table {
                width: 100%;
                border-collapse: collapse;
            }

            .totals-table td {
                padding: 5px 10px;
                font-size: 20px;
            }

            .totals-table .label {
                text-align: right;
                font-weight: bold;
            }

            .totals-table .amount {
                text-align: right;
            }

            .totals-table .simple-border {
                border-bottom: 1px solid #ddd;
            }

            .total-final {
                background-color: #adb3bb;
                color: white;
                font-weight: bold;
                font-size: 20px;
                text-align: right;
                padding-top: 10px;
            }

            .payment-terms {
                margin-top: 30px;
                font-size: 20px;
                color: #666;
            }

            .payment-terms strong {
                color: #333;
            }

            /* Footer positioning for domPDF */
            footer {
                position: absolute;
                bottom: 0;
                left: 0;
                right: 0;
                width: 100%;
                margin: 0;
                padding: 20px 0;
                background: white;
                border-top: 1px solid #ddd;
            }

            /* this is the footer it needs to be at the bottom of the page and centered*/
            .footer {
                display: table;
                width: 100%;
                text-align: center;
            }

            /* this div show on the left and right of the page*/
            .signature {
                font-size: 20px;
                color: #666;
                width: 50%;
                float: left;
            }

            .date {
                font-size: 20px;
                color: #666;
                width: 50%;
                float: right;
            }

            /* Clearfix for floated elements */
            .clearfix::after {
                content: "";
                display: table;
                clear: both;
            }

            /* Ensure content doesn't overlap with absolute footer */
            body {
                padding-bottom: 60px;
            }
        }
    </style>
</head>

<body>
    <!-- Header Section -->
    <div class="header">
        <div class="header-left">
            <div class="company-logo">
                @if ($logo_base64)
                    <img src="data:image/png;base64,{{ $logo_base64 }}" alt="Some Logo" />
                @endif
            </div>

        </div>
        <div class="header-right">
            <div class="company-info">
                PO Box 93508<br>
                Lafayette, LA 70501 US<br>
                Phone: {{ $technician_phone ?? '' }}<br>
                Email: {{ $technician_email ?? '' }}<br>
                License #: F2532
            </div>

        </div>
    </div>

    <content>
        <div class="invoice-title">INVOICE</div>
        <!-- Billing Information -->
        <div class="billing-section">
            <div class="invoice-info">
                <strong>Invoice #:</strong> {{ $invoice_number ?? 'N/A' }}<br>
                <strong>Date:</strong> {{ $generated_date }}<br>
                <strong>Due Date:</strong> {{ $due_date }}<br />
                <strong>Terms:</strong> Net 30<br />
                <strong>P.O.:</strong> {{ $purchase_order ?? '' }}<br />
            </div>
            <div class="notes">
                <div class="section-title">NOTES:</div>
                <div class="customer-info">
                    {{ $note_to_customer ?? '' }}
                </div>
            </div>
            <div class="bill-to">
                <div class="section-title">BILL TO:</div>
                <div class="customer-info">
                    {{ $bill_to_address ?? '' }}
                </div>
            </div>

        </div>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 20%;" class="text-center">SKU</th>
                    <th style="width: 40%;" class="text-center">DESCRIPTION</th>
                    <th style="width: 10%;" class="text-center">QTY</th>
                    <th style="width: 10%;" class="text-center">PRICE</th>
                    <th style="width: 10%;" class="text-center">AMOUNT</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($line_items as $line)
                    <tr>
                        <td class="text-center">{{ $line['sku'] ?? '' }}</td>
                        <td>Stock: {{ $line['stock'] ?? '' }} /
                            Vin: {{ $line['vin'] ?? '' }} /
                            {{ $line['year_make_model'] ?? '' }}
                        </td>
                        <td class="text-center">{{ $line['quantity'] ?? 1 }}</td>
                        <td class="text-center">${{ number_format($line['price'] ?? 0, 2) }}</td>
                        <td class="text-center">${{ number_format(($line['quantity'] ?? 1) * ($line['price'] ?? 0), 2) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals Section -->
        <div class="totals-section">
            <table class="totals-table">
                <tr>
                    <td class="label">Subtotal:</td>
                    <td class="amount simple-border">${{ number_format($subtotal, 2) }}</td>
                </tr>
                <tr>
                    <td class="label">Sales Taxs:</td>
                    <td class="amount"></td>
                </tr>
                <tr class="total-final">
                    <td class="label">TOTAL:</td>
                    <td class="amount">${{ number_format($total, 2) }}</td>
                </tr>
            </table>
        </div>
    </content>
    <!-- Footer -->
    <footer>
        <div class="footer clearfix">
            <div class="signature">
                Signature: ___________________________
            </div>
            <div class="date">
                Date: ___________________________
            </div>
        </div>
    </footer>
</body>

</html>
