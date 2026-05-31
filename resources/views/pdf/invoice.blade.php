<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #111827; font-size: 12px; }
        .header { display: table; width: 100%; margin-bottom: 28px; }
        .header > div { display: table-cell; vertical-align: top; }
        .right { text-align: right; }
        h1 { margin: 0 0 6px; font-size: 24px; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { border-bottom: 1px solid #e5e7eb; padding: 9px 7px; text-align: left; }
        th { background: #f9fafb; font-weight: bold; }
        .totals { width: 280px; margin-left: auto; margin-top: 20px; }
        .totals td { border: 0; padding: 5px 0; }
        .total { font-size: 16px; font-weight: bold; }
        .muted { color: #6b7280; }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <h1>AlBaik Store</h1>
            <div class="muted">Commercial invoice</div>
        </div>
        <div class="right">
            <strong>Invoice: {{ $order->order_number }}</strong><br>
            Date: {{ $order->created_at?->format('Y-m-d H:i') }}<br>
            Status: {{ ucfirst($order->status) }}
        </div>
    </div>

    <p>
        <strong>Customer:</strong> {{ $order->user?->name }}<br>
        <strong>Email:</strong> {{ $order->user?->email }}<br>
        <strong>Payment:</strong> {{ $order->paymentMethod?->name ?? 'N/A' }}<br>
        <strong>Shipping:</strong> {{ $order->shipping_carrier_name ?? 'N/A' }}
    </p>

    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th>SKU</th>
                <th>Qty</th>
                <th>Unit</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->items as $item)
                <tr>
                    <td>
                        {{ ($item->item_type ?? 'product') === 'offer' ? $item->offer_title : $item->product?->name }}
                        @if(($item->item_type ?? 'product') === 'offer')
                            <br><span class="muted">
                                @foreach(collect($item->components_snapshot ?? [])->take(4) as $component)
                                    {{ $component['product_name'] ?? 'Product' }} x {{ $component['quantity'] ?? 1 }}@if(!$loop->last), @endif
                                @endforeach
                            </span>
                        @endif
                    </td>
                    <td>{{ ($item->item_type ?? 'product') === 'offer' ? ('OFFER-'.$item->offer_id) : ($item->variant?->sku ?? $item->product?->sku) }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ number_format((float) $item->unit_price, 2) }}</td>
                    <td>{{ number_format((float) $item->total_price, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr>
            <td>Subtotal</td>
            <td class="right">{{ number_format((float) $order->subtotal, 2) }}</td>
        </tr>
        <tr>
            <td>Shipping</td>
            <td class="right">{{ number_format((float) $order->shipping_cost, 2) }}</td>
        </tr>
        <tr>
            <td>Discount</td>
            <td class="right">{{ number_format((float) $order->discount_amount, 2) }}</td>
        </tr>
        <tr>
            <td>Payment fee</td>
            <td class="right">{{ number_format((float) $order->payment_fee, 2) }}</td>
        </tr>
        <tr class="total">
            <td>Total</td>
            <td class="right">{{ number_format((float) $order->total, 2) }}</td>
        </tr>
    </table>
</body>
</html>
