<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PaymentController extends Controller
{
    /**
     * Initialize Stripe payment
     * POST /api/v1/payments/stripe/intent
     */
    public function stripeIntent(Request $request)
    {
        $request->validate([
            'order_id' => 'required|integer',
            'order_type' => 'nullable|in:gallery,worldcup', // Optional: specify order type
        ]);

        // Determine order type and fetch order
        $orderType = $request->order_type ?? 'gallery';
        
        if ($orderType === 'worldcup') {
            $order = \App\Models\DigitalProductOrder::findOrFail($request->order_id);
            $amount = $order->amount_paid;
            $currency = $order->tier->currency ?? 'usd';
        } else {
            $order = Order::findOrFail($request->order_id);
            
            // Ensure order belongs to authenticated user
            if ($order->user_id !== auth()->id()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            
            $amount = $order->total;
            $currency = $order->currency ?? 'usd';
        }

        // Check if order is already paid
        if ($order->payment_status === 'paid') {
            return response()->json(['error' => 'Order already paid'], 400);
        }

        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

        try {
            $paymentIntent = \Stripe\PaymentIntent::create([
                'amount' => $amount * 100, // Convert to cents
                'currency' => strtolower($currency),
                'metadata' => [
                    'order_id' => $order->id,
                    'order_reference' => $order->reference,
                    'order_type' => $orderType,
                ],
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
            ]);

            // Update order with payment intent ID
            $order->update([
                'payment_intent_id' => $paymentIntent->id,
                'payment_gateway' => 'stripe',
            ]);

            return response()->json([
                'client_secret' => $paymentIntent->client_secret,
                'payment_intent_id' => $paymentIntent->id,
            ]);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            return response()->json([
                'error' => 'Payment initialization failed',
                'message' => $e->getMessage(),
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Payment initialization failed',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Initialize Paystack payment
     * POST /api/v1/payments/paystack/init
     */
    public function paystackInit(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'email' => 'nullable|email',
        ]);

        $order = Order::findOrFail($request->order_id);

        // Ensure order belongs to authenticated user
        if ($order->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Check if order is already paid
        if ($order->payment_status === 'paid') {
            return response()->json(['error' => 'Order already paid'], 400);
        }

        $email = $request->email ?? $order->shipping_email ?? auth()->user()->email;
        if (!$email) {
            return response()->json(['error' => 'The email field is required.'], 422);
        }

        $amount = $order->total;
        $currency = $order->currency ?? 'USD';

        if ($currency === 'USD' && env('PAYSTACK_CONVERT_TO_NGN', true)) {
            $rate = (float) env('PAYSTACK_USD_TO_NGN_RATE', 1500.0);
            $amount = $amount * $rate;
            $currency = 'NGN';
        }

        $amountInMinorUnits = (int) round($amount * 100);

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('services.paystack.secret_key'),
                'Content-Type' => 'application/json',
            ])->post('https://api.paystack.co/transaction/initialize', [
                'email' => $email,
                'amount' => $amountInMinorUnits,
                'currency' => $currency,
                'reference' => $order->reference,
                'metadata' => [
                    'order_id' => $order->id,
                    'custom_fields' => [
                        [
                            'display_name' => 'Order Reference',
                            'variable_name' => 'order_reference',
                            'value' => $order->reference,
                        ],
                    ],
                ],
                'callback_url' => env('FRONTEND_URL', 'http://localhost:5173') . '/checkout/success',
            ]);

            if ($response->successful()) {
                $data = $response->json();

                // Update order with Paystack reference
                $order->update([
                    'paystack_reference' => $data['data']['reference'],
                    'payment_gateway' => 'paystack',
                    'payment_reference' => $data['data']['reference'],
                ]);

                return response()->json([
                    'authorization_url' => $data['data']['authorization_url'],
                    'access_code' => $data['data']['access_code'],
                    'reference' => $data['data']['reference'],
                ]);
            }

            return response()->json([
                'error' => 'Payment initialization failed',
                'message' => $response->json()['message'] ?? 'Unknown error',
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Payment initialization failed',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Paystack transaction verification endpoint
     * GET /api/v1/payments/paystack/verify
     */
    public function paystackVerify(Request $request)
    {
        $reference = $request->query('reference');

        if (!$reference) {
            return response()->json(['error' => 'No reference provided'], 400);
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('services.paystack.secret_key'),
            ])->get("https://api.paystack.co/transaction/verify/{$reference}");

            if ($response->successful()) {
                $data = $response->json();

                if ($data['data']['status'] === 'success') {
                    $order = Order::where('reference', $reference)
                        ->orWhere('payment_reference', $reference)
                        ->orWhere('paystack_reference', $reference)
                        ->first();

                    if (!$order) {
                        return response()->json(['error' => 'Order not found'], 404);
                    }

                    // Ensure the order belongs to the authenticated user
                    if ($order->user_id !== auth()->id()) {
                        return response()->json(['error' => 'Unauthorized'], 403);
                    }

                    // Fallback fulfillment check in case webhook hasn't processed it yet
                    if ($order->payment_status !== 'paid') {
                        $order->update([
                            'payment_status' => 'paid',
                            'status' => 'processing',
                            'payment_reference' => $reference,
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
                        } catch (\Exception $e) {
                            \Illuminate\Support\Facades\Log::error('Failed to send gallery order email in verify route: ' . $e->getMessage());
                        }
                    }

                    return response()->json([
                        'status' => 'success',
                        'message' => 'Payment verified successfully',
                        'order' => $order->load('items'),
                    ]);
                }

                return response()->json([
                    'status' => 'failed',
                    'error' => 'Payment not successful',
                    'paystack_status' => $data['data']['status'],
                ], 400);
            }

            return response()->json([
                'status' => 'failed',
                'error' => 'Payment verification failed',
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failed',
                'error' => 'Payment verification failed',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
