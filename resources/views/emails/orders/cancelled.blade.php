@component('mail::message')
# Order Cancelled

Hello {{ $order->customer_name }},

Your order **{{ $order->reference }}** has been cancelled.

@if($order->cancellation_reason)
**Reason for Cancellation:** {{ $order->cancellation_reason }}
@endif

If this cancellation was unexpected or if you have any questions, please reach out to our support team.

@component('mail::button', ['url' => config('app.frontend_url') . '/orders/' . $order->id])
View Order Details
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
