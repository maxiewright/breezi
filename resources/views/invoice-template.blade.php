<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .invoice-title {
            font-size: 18px;
            color: #666;
        }
        .invoice-details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .customer-info, .invoice-info {
            flex: 1;
        }
        .section-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #333;
        }
        .info-row {
            margin-bottom: 5px;
        }
        .label {
            font-weight: bold;
            color: #666;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .table th, .table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        .table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .total-row {
            font-weight: bold;
            font-size: 18px;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            color: #666;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">Breezi</div>
        <div class="invoice-title">INVOICE</div>
    </div>

    <div class="invoice-details">
        <div class="customer-info">
            <div class="section-title">Bill To:</div>
            <div class="info-row">
                <span class="label">Name:</span> {{ $invoice->job->site->customer->name }}
            </div>
            <div class="info-row">
                <span class="label">Address:</span> {{ $invoice->job->site->address_line_1 }}, {{ $invoice->job->site->city }}
            </div>
            @if($invoice->job->site->customer->email)
                <div class="info-row">
                    <span class="label">Email:</span> {{ $invoice->job->site->customer->email }}
                </div>
            @endif
            <div class="info-row">
                <span class="label">Phone:</span> {{ $invoice->job->site->customer->phone }}
            </div>
        </div>

        <div class="invoice-info">
            <div class="section-title">Invoice Details:</div>
            <div class="info-row">
                <span class="label">Invoice #:</span> {{ $invoice->invoice_number }}
            </div>
            <div class="info-row">
                <span class="label">Date:</span> {{ $invoice->created_at->format('M j, Y') }}
            </div>
            <div class="info-row">
                <span class="label">Status:</span> {{ ucfirst($invoice->status) }}
            </div>
            <div class="info-row">
                <span class="label">Job:</span> {{ $invoice->job->title }}
            </div>
            <div class="info-row">
                <span class="label">Service Date:</span> {{ $invoice->job->scheduled_at->format('M j, Y') }}
            </div>
        </div>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Description</th>
                <th>Quantity</th>
                <th>Unit Price</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>${{ number_format($item->unit_price, 2) }}</td>
                    <td>${{ number_format($item->total_price, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="3" style="text-align: right;">Total:</td>
                <td>${{ number_format($invoice->total_amount, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    @if($invoice->notes)
        <div style="margin-bottom: 30px;">
            <div class="section-title">Notes:</div>
            <div>{{ $invoice->notes }}</div>
        </div>
    @endif

    <div class="footer">
        <p>Thank you for your business!</p>
        <p>Breezi - Making business management breezy</p>
    </div>
</body>
</html>
