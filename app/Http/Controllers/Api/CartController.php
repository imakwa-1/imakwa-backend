<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Artwork;
use App\Models\DigitalProductTier;
use Illuminate\Http\Request;

class CartController extends Controller
{
    private function getCart(Request $request)
    {
        if ($request->user()) {
            return Cart::firstOrCreate(['user_id' => $request->user()->id]);
        }
        
        $sessionId = $request->header('X-Session-ID');
        
        // Defensive null guard (should not happen with middleware in place)
        if (!$sessionId) {
            abort(400, 'X-Session-ID header is required for guest cart access.');
        }
        
        return Cart::firstOrCreate(['session_id' => $sessionId]);
    }

    public function index(Request $request)
    {
        $cart = $this->getCart($request);
        $cart->load('items.itemable');
        return response()->json($cart);
    }

    public function add(Request $request)
    {
        $request->validate([
            'item_type' => ['required', 'in:artwork,digital_tier'],
            'item_id'   => ['required', 'integer'],
        ]);

        $cart = $this->getCart($request);

        if ($request->item_type === 'artwork') {
            $item = Artwork::where('is_active', true)->where('status', 'available')->findOrFail($request->item_id);
            $type = Artwork::class;
        } else {
            $item = DigitalProductTier::where('is_active', true)->findOrFail($request->item_id);
            if ($item->isSoldOut()) {
                return response()->json(['message' => 'This tier is sold out'], 422);
            }
            $type = DigitalProductTier::class;
        }

        $existing = $cart->items()->where('itemable_id', $item->id)->where('itemable_type', $type)->first();

        if ($existing) {
            return response()->json(['message' => 'Item already in cart', 'cart_item' => $existing]);
        }

        $cartItem = $cart->items()->create([
            'itemable_id'   => $item->id,
            'itemable_type' => $type,
            'quantity'      => 1,
            'price'         => $item->price,
        ]);

        return response()->json(['message' => 'Added to cart', 'cart_item' => $cartItem], 201);
    }

    public function remove(Request $request, $itemId)
    {
        $cart = $this->getCart($request);
        $cart->items()->where('id', $itemId)->delete();
        return response()->json(['message' => 'Item removed']);
    }

    public function clear(Request $request)
    {
        $cart = $this->getCart($request);
        $cart->items()->delete();
        return response()->json(['message' => 'Cart cleared']);
    }

    public function merge(Request $request)
    {
        if (!$request->user()) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $sessionId = $request->header('X-Session-ID');
        if (!$sessionId) {
            return response()->json(['message' => 'No session cart to merge']);
        }

        $sessionCart = Cart::where('session_id', $sessionId)->first();
        if (!$sessionCart) {
            return response()->json(['message' => 'No session cart found']);
        }

        $userCart = Cart::firstOrCreate(['user_id' => $request->user()->id]);

        foreach ($sessionCart->items as $item) {
            $exists = $userCart->items()
                ->where('itemable_id', $item->itemable_id)
                ->where('itemable_type', $item->itemable_type)
                ->exists();

            if (!$exists) {
                $userCart->items()->create([
                    'itemable_id'   => $item->itemable_id,
                    'itemable_type' => $item->itemable_type,
                    'quantity'      => $item->quantity,
                    'price'         => $item->price,
                ]);
            }
        }

        $sessionCart->delete();
        return response()->json(['message' => 'Cart merged']);
    }
}