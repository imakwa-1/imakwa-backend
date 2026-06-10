<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Download is Ready</title>
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
        .content {
            margin-bottom: 30px;
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
        .order-details strong {
            color: #2c3e50;
        }
        .download-button {
            display: inline-block;
            background-color: #fbbf24;
            color: #000;
            text-decoration: none;
            padding: 16px 40px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 16px;
            margin: 20px 0;
            text-align: center;
        }
        .download-button:hover {
            background-color: #f59e0b;
        }
        .expiry-notice {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
            font-size: 14px;
        }
        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
            font-size: 14px;
            color: #666;
        }
        .button-container {
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="emoji">🎨⚽</div>
            <h1>Payment Successful!</h1>
            <p style="color: #666; font-size: 16px;">Your World Cup digital product is ready</p>
        </div>

        <div class="content">
            <p>Hi there,</p>
            <p>Thank you for your purchase! Your payment has been confirmed and your download is now available.</p>

            <div class="order-details">
                <p><strong>Product:</strong> {{ $order->tier->product->name ?? 'Digital Product' }}</p>
                <p><strong>Tier:</strong> {{ $order->tier->label ?? 'N/A' }}</p>
                <p><strong>Order Reference:</strong> {{ $order->reference }}</p>
                <p><strong>Amount Paid:</strong> {{ $order->tier->currency ?? 'USD' }} {{ number_format($order->amount_paid, 2) }}</p>
            </div>

            <div class="button-container">
                <a href="{{ $downloadUrl }}" class="download-button">
                    Download Your Product
                </a>
            </div>

            <div class="expiry-notice">
                <strong>⏰ Important:</strong> This download link will expire on 
                <strong>{{ $order->token_expires_at->format('F j, Y') }}</strong>. 
                Please download your product before this date.
            </div>

            <p><strong>Having trouble?</strong> Copy and paste this link into your browser:</p>
            <p style="word-break: break-all; background-color: #f8f9fa; padding: 10px; border-radius: 4px; font-family: monospace; font-size: 12px;">
                {{ $downloadUrl }}
            </p>
        </div>

        <div class="footer">
            <p>Thank you for supporting African art! 🌍</p>
            <p style="margin-top: 10px;">
                <a href="{{ config('app.frontend_url') }}" style="color: #fbbf24; text-decoration: none;">Visit Imakwa</a>
            </p>
            <p style="font-size: 12px; color: #999; margin-top: 20px;">
                This email was sent to {{ $order->email }}. If you did not make this purchase, please contact us immediately.
            </p>
        </div>
    </div>
</body>
</html>
