<x-mail::message>
# Order Being Processed

Hello {{ $order->customer_name }},

Great news! Your order **{{ $order->reference }}** is now being processed and prepared for shipment.

## Order Details

- **Order ID:** {{ $order->reference }}
- **Total:** {{ $order->currency }} {{ number_format($order->total, 2) }}
- **Status:** Processing

We'll notify you once your order has been shipped with tracking information.

<x-mail::button :url="config('app.frontend_url') . '/orders/' . $order->id">
View Order
</x-mail::button>

Thanks for shopping with us!

{{ config('app.name') }}
</x-mail::message>
