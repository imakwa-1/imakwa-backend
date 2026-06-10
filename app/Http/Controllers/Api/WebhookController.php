<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\DigitalProductOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function stripe(Request $request)
    {
        $payload   = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $secret    = config('services.stripe.webhook_secret');

        try {
            $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $secret);
        } catch (\Exception $e) {
            Log::error('Stripe webhook error: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        if ($event->type === 'payment_intent.succeeded') {
            $intent    = $event->data->object;
            $reference = $intent->metadata->order_reference ?? null;
            $type      = $intent->metadata->order_type ?? 'gallery';

            if ($type === 'worldcup') {
                $this->fulfillDigitalOrder($intent->metadata->order_id ?? null, $intent->id);
            } else {
                $this->fulfillGalleryOrder($reference, $intent->id);
            }
        }

        return response()->json(['received' => true]);
    }

    public function paystack(Request $request)
    {
        $secret    = config('services.paystack.secret_key');
        $signature = $request->header('x-paystack-signature');
        $payload   = $request->getContent();

        if ($signature !== hash_hmac('sha512', $payload, $secret)) {
            Log::error('Paystack webhook: invalid signature');
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        $event = json_decode($payload, true);

        if (($event['event'] ?? '') === 'charge.success') {
            $data      = $event['data'];
            $reference = $data['reference'] ?? null;
            $type      = $data['metadata']['order_type'] ?? 'gallery';

            if ($type === 'worldcup') {
                $this->fulfillDigitalOrder($data['metadata']['order_id'] ?? null, $reference);
            } else {
                $this->fulfillGalleryOrder($reference, $reference);
            }
        }

        return response()->json(['received' => true]);
    }

    private function fulfillGalleryOrder($reference, $paymentRef)
    {
        if (!$reference) return;

        $order = Order::where('reference', $reference)->first();
        if (!$order) return;

        $order->update([
            'payment_status'    => 'paid',
            'status'            => 'processing',
            'payment_reference' => $paymentRef,
        ]);

        // Mark artworks as sold
        foreach ($order->items as $item) {
            if ($item->itemable_type === \App\Models\Artwork::class) {
                \App\Models\Artwork::where('id', $item->itemable_id)->update(['status' => 'sold']);
            }
        }

        // Send order confirmation email
        try {
            \Illuminate\Support\Facades\Mail::to($order->shipping_email)
                ->send(new \App\Mail\GalleryOrderConfirmed($order));
            
            Log::info('Gallery order fulfilled and email sent', [
                'reference' => $reference,
                'email' => $order->shipping_email,
                'order_id' => $order->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send gallery order email', [
                'reference' => $reference,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function fulfillDigitalOrder($orderId, $paymentRef)
    {
        if (!$orderId) return;

        $order = DigitalProductOrder::find($orderId);
        if (!$order) return;

        $order->update([
            'payment_status'    => 'paid',
            'payment_reference' => $paymentRef,
        ]);

        // Decrement available licenses
        $tier = $order->tier;
        if ($tier) {
            $tier->increment('licenses_sold');
        }

        // Send download email
        try {
            \Illuminate\Support\Facades\Mail::to($order->email)
                ->send(new \App\Mail\DigitalOrderPurchased($order));
            
            Log::info('Digital order fulfilled and email sent', [
                'order_id' => $orderId,
                'reference' => $order->reference,
                'email' => $order->email,
                'token' => $order->download_token,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send digital order email', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}