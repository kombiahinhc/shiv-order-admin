<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 24px; }
        body { font-family: DejaVu Sans, sans-serif; color: #172033; font-size: 10px; }
        h1 { margin: 0; font-size: 22px; color: #111827; }
        .subtle { color: #64748b; margin: 5px 0 20px; }
        .summary { width: 100%; border-collapse: separate; border-spacing: 8px 0; margin: 0 -8px 22px; }
        .summary td { width: 25%; background: #f8fafc; border: 1px solid #e2e8f0; padding: 12px; border-radius: 6px; }
        .label { color: #64748b; font-size: 9px; text-transform: uppercase; }
        .value { font-size: 17px; font-weight: bold; margin-top: 5px; color: #0f172a; }
        table.orders { width: 100%; border-collapse: collapse; }
        .orders th { background: #1e3a5f; color: #fff; text-align: left; font-size: 9px; padding: 9px 8px; text-transform: uppercase; }
        .orders td { border-bottom: 1px solid #e2e8f0; padding: 8px; }
        .orders tr:nth-child(even) td { background: #f8fafc; }
        .number { text-align: right !important; }
        .center { text-align: center !important; }
        .footer { position: fixed; bottom: 0; color: #94a3b8; font-size: 8px; }
    </style>
</head>
<body>
    <h1>Sales report</h1>
    <p class="subtle">
        {{ $fromDate->format('d M Y') }} – {{ $toDate->format('d M Y') }}
        @if($salesperson) · {{ $salesperson }} @endif
    </p>

    <table class="summary">
        <tr>
            <td><div class="label">Total orders</div><div class="value">{{ number_format($summary['count'] ?? 0) }}</div></td>
            <td><div class="label">Total revenue</div><div class="value">₹{{ number_format($summary['total_revenue'] ?? 0, 0) }}</div></td>
            <td><div class="label">Avg. order value</div><div class="value">₹{{ number_format($summary['average_order_value'] ?? 0, 0) }}</div></td>
            <td><div class="label">Total tax</div><div class="value">₹{{ number_format($summary['total_tax'] ?? 0, 0) }}</div></td>
        </tr>
    </table>

    <table class="orders">
        <thead><tr><th>Date</th><th>Sales rep</th><th>Shop</th><th class="center">Items</th><th class="number">Subtotal</th><th class="number">Tax</th><th class="number">Discount</th><th class="number">Grand total</th></tr></thead>
        <tbody>
            @forelse($orders as $order)
                <tr>
                    <td>{{ \Illuminate\Support\Carbon::parse($order['order_date'])->format('d M Y') }}</td>
                    <td>{{ $order['salesperson']['name'] ?? 'Unassigned' }}</td>
                    <td>{{ $order['shop']['name'] ?? $order['shop_name_snapshot'] ?? 'N/A' }}</td>
                    <td class="center">{{ count($order['lines'] ?? []) }}</td>
                    <td class="number">₹{{ number_format($order['subtotal'], 2) }}</td>
                    <td class="number">₹{{ number_format($order['tax_total'], 2) }}</td>
                    <td class="number">₹{{ number_format($order['discount_value'], 2) }}</td>
                    <td class="number"><strong>₹{{ number_format($order['grand_total'], 2) }}</strong></td>
                </tr>
            @empty
                <tr><td colspan="8" class="center">No orders matched the selected filters.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="footer">Generated on {{ now()->format('d M Y, h:i A') }}</div>
</body>
</html>
