<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmed</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 40px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #2c3e50;
            margin: 0;
            font-size: 28px;
        }
        .emoji {
            font-size: 48px;
            margin-bottom: 20px;
        }
        .order-details {
            background-color: #f8f9fa;
            border-left: 4px solid #fbbf24;
            padding: 20px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .order-details p {
            margin: 8px 0;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .items-table th {
            background-color: #f8f9fa;
            padding: 12px;
            text-align: left;
            border-bottom: 2px solid #dee2e6;
        }
        .items-table td {
            padding: 12px;
            border-bottom: 1px solid #dee2e6;
        }
        .total-row {
            font-weight: 600;
            background-color: #fff3cd;
        }
        .shipping-info {
            background-color: #e7f3ff;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
            font-size: 14px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="emoji">🎨✅</div>
            <h1>Order Confirmed!</h1>
            <p style="color: #666;">Thank you for your purchase</p>
        </div>

        <div class="content">
            <p>Hi {{ $order->shipping_name }},</p>
            <p>We've received your payment and your order is being processed. We'll send you another email once your artwork ships.</p>

            <div class="order-details">
                <p><strong>Order Reference:</strong> {{ $order->reference }}</p>
                <p><strong>Order Date:</strong> {{ $order->created_at->format('F j, Y') }}</p>
                <p><strong>Status:</strong> <span style="color: #28a745;">{{ ucfirst($order->status) }}</span></p>
            </div>

            <h3>Order Items</h3>
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Artwork</th>
                        <th style="text-align: right;">Price</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                    <tr>
                        <td>
                            <strong>{{ $item->title }}</strong>
                            @if($item->itemable)
                                <br><small style="color: #666;">by {{ $item->itemable->artist->display_name ?? 'Unknown Artist' }}</small>
                            @endif
                        </td>
                        <td style="text-align: right;">{{ $order->currency }} {{ number_format($item->price, 2) }}</td>
                    </tr>
                    @endforeach
                    <tr>
                        <td><strong>Shipping</strong></td>
                        <td style="text-align: right;">{{ $order->currency }} {{ number_format($order->shipping_cost, 2) }}</td>
                    </tr>
                    <tr class="total-row">
                        <td><strong>Total</strong></td>
                        <td style="text-align: right;"><strong>{{ $order->currency }} {{ number_format($order->total, 2) }}</strong></td>
                    </tr>
                </tbody>
            </table>

            <h3>Shipping Address</h3>
            <div class="shipping-info">
                <p style="margin: 0;"><strong>{{ $order->shipping_name }}</strong></p>
                <p style="margin: 5px 0;">{{ $order->shipping_address }}</p>
                <p style="margin: 5px 0;">{{ $order->shipping_city }}, {{ $order->shipping_country }}</p>
                @if($order->shipping_postal_code)
                <p style="margin: 5px 0;">{{ $order->shipping_postal_code }}</p>
                @endif
                <p style="margin: 5px 0;">{{ $order->shipping_phone }}</p>
            </div>

            <p style="margin-top: 30px;">If you have any questions about your order, please reply to this email or contact us at support@imakwa.co</p>
        </div>

        <div class="footer">
            <p>Thank you for supporting African art! 🌍</p>
            <p style="margin-top: 10px;">
                <a href="{{ config('app.frontend_url') }}" style="color: #fbbf24; text-decoration: none;">Visit Imakwa Gallery</a>
            </p>
        </div>
    </div>
</body>
</html>
