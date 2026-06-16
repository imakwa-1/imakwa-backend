@component('mail::message')
# Order Shipped!

Hello {{ $order->customer_name }},

Great news! Your order **{{ $order->reference }}** has been shipped.

@if($order->tracking_number)
**Tracking Number:** {{ $order->tracking_number }}
@endif

We hope you love your new artwork! You can track the progress or view the details using the link below:

@component('mail::button', ['url' => config('app.frontend_url') . '/orders/' . $order->id])
View Order Details
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
