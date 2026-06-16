<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

Route::get('/', function () {
    return view('welcome');
});

// Diagnostic endpoint - remove after testing
Route::get('/check-inventory', function () {
    $artworksColumns = Schema::getColumnListing('artworks');
    $tiersColumns = Schema::getColumnListing('digital_product_tiers');
    
    $statusCheck = DB::select("SHOW COLUMNS FROM artworks WHERE Field = 'status'");
    $enumValues = !empty($statusCheck) ? $statusCheck[0]->Type : 'Not found';
    
    $sampleArtwork = DB::table('artworks')->first();
    $sampleTier = DB::table('digital_product_tiers')->first();
    
    return response()->json([
        'artworks_table' => [
            'has_stock_quantity' => in_array('stock_quantity', $artworksColumns),
            'has_stock_sold' => in_array('stock_sold', $artworksColumns),
            'status_enum' => $enumValues,
            'sample_data' => $sampleArtwork ? [
                'id' => $sampleArtwork->id,
                'stock_quantity' => $sampleArtwork->stock_quantity ?? 'NULL',
                'stock_sold' => $sampleArtwork->stock_sold ?? 'NULL',
                'status' => $sampleArtwork->status ?? 'NULL',
            ] : null,
        ],
        'digital_product_tiers_table' => [
            'has_stock_quantity' => in_array('stock_quantity', $tiersColumns),
            'has_stock_sold' => in_array('stock_sold', $tiersColumns),
            'sample_data' => $sampleTier ? [
                'id' => $sampleTier->id,
                'stock_quantity' => $sampleTier->stock_quantity ?? 'NULL',
                'stock_sold' => $sampleTier->stock_sold ?? 'NULL',
            ] : null,
        ],
        'all_artworks_columns' => $artworksColumns,
        'all_tiers_columns' => $tiersColumns,
    ]);
});
