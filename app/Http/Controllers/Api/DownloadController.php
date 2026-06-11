<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DigitalProductOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DownloadController extends Controller
{
    /**
     * Download digital product using token
     */
    public function download(Request $request, string $token)
    {
        // Find order by token
        $order = DigitalProductOrder::where('download_token', $token)
            ->with('tier.digital_product')
            ->first();

        if (!$order) {
            return response()->json([
                'message' => 'Invalid download link'
            ], 404);
        }

        // Check if token is valid
        if (!$order->isTokenValid()) {
            $reason = $order->token_used 
                ? 'This download link has already been used' 
                : 'This download link has expired';

            return response()->json([
                'message' => $reason
            ], 403);
        }

        // Get the digital product and tier
        $product = $order->tier->digital_product;
        $tier = $order->tier;

        // Add null guard for product
        if (!$product) {
            return response()->json([
                'message' => 'Product not found. Please contact support.'
            ], 404);
        }

        // Check if file exists
        $filePath = $tier->file_path;
        
        if (!$filePath || !Storage::exists($filePath)) {
            return response()->json([
                'message' => 'File not found. Please contact support.'
            ], 404);
        }

        // Mark token as used
        $order->markTokenAsUsed();

        // Generate filename
        $fileName = sprintf(
            '%s_%s.%s',
            str_replace(' ', '_', $product->name),
            $tier->label,
            pathinfo($filePath, PATHINFO_EXTENSION)
        );

        // Return file download
        return Storage::download($filePath, $fileName);
    }

    /**
     * Get download link info (without downloading)
     */
    public function info(Request $request, string $token)
    {
        $order = DigitalProductOrder::where('download_token', $token)
            ->with('tier.digital_product')
            ->first();

        if (!$order) {
            return response()->json([
                'message' => 'Invalid download link'
            ], 404);
        }

        // Add null guard for product relationship
        if (!$order->tier || !$order->tier->digital_product) {
            return response()->json([
                'message' => 'Product information not found. Please contact support.'
            ], 404);
        }

        return response()->json([
            'product' => $order->tier->digital_product->name,
            'tier' => $order->tier->label,
            'expires_at' => $order->token_expires_at,
            'is_used' => $order->token_used,
            'is_valid' => $order->isTokenValid(),
        ]);
    }
}
