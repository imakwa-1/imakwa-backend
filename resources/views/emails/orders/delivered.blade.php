@component('mail::message')
# Order Delivered!

Hello {{ $order->customer_name }},

Your order **{{ $order->reference }}** has been successfully delivered!

We hope you are thrilled with your new artwork. If you have any questions or feedback, please feel free to reply to this email.

@component('mail::button', ['url' => config('app.frontend_url') . '/orders/' . $order->id])
View Order Details
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
