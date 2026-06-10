<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    /**
     * Get authenticated user profile
     */
    public function profile(Request $request)
    {
        return response()->json([
            'user' => $request->user()
        ]);
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'current_password' => 'required_with:password',
            'password' => ['sometimes', 'confirmed', Password::defaults()],
        ]);

        // If changing password, verify current password
        if (isset($validated['password'])) {
            if (!Hash::check($validated['current_password'], $user->password)) {
                return response()->json([
                    'message' => 'Current password is incorrect'
                ], 422);
            }
            $validated['password'] = Hash::make($validated['password']);
        }

        // Remove current_password from update
        unset($validated['current_password'], $validated['password_confirmation']);

        $user->update($validated);

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user
        ]);
    }

    /**
     * Get user's order history (gallery orders)
     */
    public function orders(Request $request)
    {
        $orders = $request->user()
            ->orders()
            ->with('items.itemable')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json($orders);
    }

    /**
     * Get user's favorites
     */
    public function favorites(Request $request)
    {
        $favorites = $request->user()
            ->favorites()
            ->with(['artwork.images', 'artwork.artist'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($favorites);
    }

    /**
     * Get user's digital product orders
     */
    public function digitalOrders(Request $request)
    {
        $email = $request->user()->email;
        
        $digitalOrders = \App\Models\DigitalProductOrder::where('email', $email)
            ->with('tier.digital_product')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json($digitalOrders);
    }
}
