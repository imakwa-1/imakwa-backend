<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DigitalProductTier;
use App\Models\DigitalProductOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WorldCupOrderController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'tier_id'         => ['required', 'integer', 'exists:digital_product_tiers,id'],
            'email'           => ['required', 'email'],
            'payment_gateway' => ['required', 'in:stripe,paystack'],
        ]);

        $tier = DigitalProductTier::with('product')->findOrFail($request->tier_id);

        if (!$tier->is_active) {
            return response()->json(['message' => 'This tier is not available'], 422);
        }

        if (!$tier->product->isOpen()) {
            return response()->json(['message' => 'This product is no longer available'], 422);
        }

        if ($tier->isSoldOut()) {
            return response()->json(['message' => 'This tier is sold out'], 422);
        }

        $order = DB::transaction(function () use ($request, $tier) {
            $token = Str::random(64);

            $order = DigitalProductOrder::create([
                'digital_product_tier_id' => $tier->id,
                'email'                   => $request->email,
                'download_token'          => $token,
                'token_used'              => false,
                'token_expires_at'        => now()->addDays(30),
                'payment_status'          => 'pending',
                'payment_gateway'         => $request->payment_gateway,
                'amount_paid'             => $tier->price,
            ]);

            return $order;
        });

        return response()->json([
            'message'  => 'Order created successfully',
            'order' => [
                'id' => $order->id,
                'reference' => $order->reference,
                'email' => $order->email,
                'amount' => $tier->price,
                'currency' => $tier->currency,
                'tier' => $tier->label,
                'product' => $tier->product->name,
            ],
            'next_step' => [
                'action' => 'initialize_payment',
                'endpoints' => [
                    'stripe' => [
                        'url' => '/api/v1/worldcup/stripe/init',
                        'method' => 'POST',
                        'payload' => ['order_id' => $order->id],
                        'note' => 'Use for Stripe payments - returns client_secret',
                    ],
                    'paystack' => [
                        'url' => '/api/v1/worldcup/paystack/init',
                        'method' => 'POST',
                        'payload' => [
                            'order_id' => $order->id,
                            'email' => $request->email,
                        ],
                        'note' => 'Use for Paystack payments - returns authorization_url',
                    ],
                ],
                'selected_gateway' => $request->payment_gateway,
            ],
        ], 201);
    }

    public function download($token)
    {
        $order = DigitalProductOrder::where('download_token', $token)
            ->where('payment_status', 'paid')
            ->firstOrFail();

        if ($order->token_used) {
            return response()->json(['message' => 'This download link has already been used'], 403);
        }

        if ($order->token_expires_at && $order->token_expires_at->isPast()) {
            return response()->json(['message' => 'This download link has expired'], 403);
        }

        $order->update(['token_used' => true]);

        $downloadUrl = $order->tier->download_url;

        return response()->json([
            'message'      => 'Download ready',
            'download_url' => $downloadUrl,
        ]);
    }

    public function status($orderId)
    {
        $order = DigitalProductOrder::with('tier.product')
            ->findOrFail($orderId);

        return response()->json([
            'order_id'       => $order->id,
            'email'          => $order->email,
            'payment_status' => $order->payment_status,
            'tier'           => $order->tier->label ?? null,
            'product'        => $order->tier->product->name ?? null,
            'token_used'     => $order->token_used,
        ]);
    }

    public function stripeInit(Request $request)
    {
        $request->validate([
            'order_id' => 'required|integer|exists:digital_product_orders,id',
        ]);

        $order = DigitalProductOrder::with('tier.product')->findOrFail($request->order_id);

        if ($order->payment_status === 'paid') {
            return response()->json(['error' => 'Order already paid'], 400);
        }

        $amount = $order->amount_paid;
        $currency = $order->tier->currency ?? 'usd';

        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

        try {
            $paymentIntent = \Stripe\PaymentIntent::create([
                'amount' => $amount * 100, // Convert to cents
                'currency' => strtolower($currency),
                'metadata' => [
                    'order_id' => $order->id,
                    'order_reference' => $order->reference,
                    'order_type' => 'worldcup',
                ],
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
            ]);

            $order->update([
                'payment_intent_id' => $paymentIntent->id,
                'payment_gateway' => 'stripe',
            ]);

            return response()->json([
                'client_secret' => $paymentIntent->client_secret,
                'payment_intent_id' => $paymentIntent->id,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Payment initialization failed',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function paystackInit(Request $request)
    {
        $request->validate([
            'order_id' => 'required|integer|exists:digital_product_orders,id',
            'email' => 'required|email',
        ]);

        $order = DigitalProductOrder::with('tier.product')->findOrFail($request->order_id);

        if ($order->payment_status === 'paid') {
            return response()->json(['error' => 'Order already paid'], 400);
        }

        try {
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Authorization' => 'Bearer ' . config('services.paystack.secret_key'),
                'Content-Type' => 'application/json',
            ])->post('https://api.paystack.co/transaction/initialize', [
                'email' => $request->email,
                'amount' => $order->amount_paid * 100, // Convert to kobo/cents
                'currency' => $order->tier->currency ?? 'NGN',
                'reference' => $order->reference,
                'metadata' => [
                    'order_id' => $order->id,
                    'order_type' => 'worldcup',
                    'custom_fields' => [
                        [
                            'display_name' => 'Order Reference',
                            'variable_name' => 'order_reference',
                            'value' => $order->reference,
                        ],
                    ],
                ],
                'callback_url' => env('FRONTEND_URL', 'http://localhost:5173') . '/worldcup/success',
            ]);

            if ($response->successful()) {
                $data = $response->json();

                $order->update([
                    'paystack_reference' => $data['data']['reference'],
                    'payment_gateway' => 'paystack',
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

    public function orderByReference($reference)
    {
        $order = DigitalProductOrder::with('tier.product')
            ->where('reference', $reference)
            ->orWhere('paystack_reference', $reference)
            ->firstOrFail();

        return response()->json([
            'order_id'       => $order->id,
            'email'          => $order->email,
            'payment_status' => $order->payment_status,
            'tier'           => $order->tier->label ?? null,
            'product'        => $order->tier->product->name ?? null,
            'token_used'     => $order->token_used,
        ]);
    }

    public function lookup(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $orders = DigitalProductOrder::with('tier.product')
            ->where('email', $request->email)
            ->where('payment_status', 'paid')
            ->get();

        $formatted = $orders->map(function ($order) {
            return [
                'id' => $order->id,
                'reference' => $order->reference,
                'amount' => $order->amount_paid,
                'payment_status' => $order->payment_status,
                'tier' => $order->tier->label ?? null,
                'product' => $order->tier->product->name ?? null,
                'token_used' => $order->token_used,
                'token_expires_at' => $order->token_expires_at ? $order->token_expires_at->toIso8601String() : null,
            ];
        });

        return response()->json($formatted);
    }
}