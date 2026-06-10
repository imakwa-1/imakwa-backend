<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Artwork;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'payment_gateway'      => ['required', 'in:stripe,paystack'],
            'shipping_name'        => ['required', 'string'],
            'shipping_email'       => ['required', 'email'],
            'shipping_phone'       => ['nullable', 'string'],
            'shipping_address'     => ['required', 'string'],
            'shipping_city'        => ['required', 'string'],
            'shipping_country'     => ['required', 'string'],
            'shipping_postal_code' => ['nullable', 'string'],
        ]);

        $cart = Cart::with('items.itemable')
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$cart || $cart->items->isEmpty()) {
            return response()->json(['message' => 'Cart is empty'], 422);
        }

        // Only gallery (physical) items
        $items = $cart->items->filter(fn($i) => $i->itemable_type === Artwork::class);

        if ($items->isEmpty()) {
            return response()->json(['message' => 'No gallery items in cart'], 422);
        }

        $subtotal = $items->sum(fn($i) => $i->price * $i->quantity);
        $shipping = 0;
        $total    = $subtotal + $shipping;

        $order = DB::transaction(function () use ($request, $items, $subtotal, $shipping, $total) {
            $order = Order::create([
                'user_id'              => $request->user()->id,
                'fulfillment_type'     => 'physical',
                'payment_gateway'      => $request->payment_gateway,
                'subtotal'             => $subtotal,
                'shipping_cost'        => $shipping,
                'total'                => $total,
                'currency'             => 'USD',
                'shipping_name'        => $request->shipping_name,
                'shipping_email'       => $request->shipping_email,
                'shipping_phone'       => $request->shipping_phone,
                'shipping_address'     => $request->shipping_address,
                'shipping_city'        => $request->shipping_city,
                'shipping_country'     => $request->shipping_country,
                'shipping_postal_code' => $request->shipping_postal_code,
            ]);

            foreach ($items as $item) {
                $order->items()->create([
                    'itemable_id'   => $item->itemable_id,
                    'itemable_type' => $item->itemable_type,
                    'title'         => $item->itemable->title ?? 'Artwork',
                    'quantity'      => $item->quantity,
                    'price'         => $item->price,
                    'subtotal'      => $item->price * $item->quantity,
                ]);

                // Mark artwork as reserved
                Artwork::where('id', $item->itemable_id)->update(['status' => 'reserved']);
            }

            return $order;
        });

        return response()->json([
            'message'   => 'Order created successfully',
            'order'     => $order->load('items'),
            'next_step' => [
                'action' => 'initialize_payment',
                'endpoints' => [
                    'stripe' => [
                        'url' => '/api/v1/payments/stripe/intent',
                        'method' => 'POST',
                        'payload' => ['order_id' => $order->id],
                    ],
                    'paystack' => [
                        'url' => '/api/v1/payments/paystack/init',
                        'method' => 'POST',
                        'payload' => [
                            'order_id' => $order->id,
                            'email' => $request->shipping_email,
                        ],
                    ],
                ],
                'selected_gateway' => $request->payment_gateway,
            ],
        ], 201);
    }

    public function index(Request $request)
    {
        $orders = Order::with('items')
            ->where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->get();

        return response()->json($orders);
    }

    public function show(Request $request, $id)
    {
        $order = Order::with('items.itemable')
            ->where('user_id', $request->user()->id)
            ->findOrFail($id);

        return response()->json($order);
    }
}