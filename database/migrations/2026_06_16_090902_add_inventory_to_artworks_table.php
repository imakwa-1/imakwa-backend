<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('artworks', function (Blueprint $table) {
            // Add inventory tracking columns
            $table->integer('stock_quantity')->default(1)->after('price');
            $table->integer('stock_sold')->default(0)->after('stock_quantity');
            
            // No need to modify status enum - it already supports the values we need
            // We'll just add 'out_of_stock' as a valid value in code logic
        });
        
        // Update existing artworks to have stock_quantity = 1
        DB::table('artworks')->update(['stock_quantity' => 1, 'stock_sold' => 0]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('artworks', function (Blueprint $table) {
            $table->dropColumn(['stock_quantity', 'stock_sold']);
        });
    }
};
