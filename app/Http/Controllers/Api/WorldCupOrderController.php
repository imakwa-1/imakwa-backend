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
                        'url' => '/api/v1/payments/stripe/intent',
                        'method' => 'POST',
                        'payload' => ['order_id' => $order->id],
                        'note' => 'Use for Stripe payments - returns client_secret',
                    ],
                    'paystack' => [
                        'url' => '/api/v1/payments/paystack/init',
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
}