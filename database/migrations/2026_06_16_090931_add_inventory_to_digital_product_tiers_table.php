<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('digital_product_tiers', function (Blueprint $table) {
            // Add inventory tracking
            // NULL means unlimited stock (current behavior)
            $table->integer('stock_quantity')->nullable()->after('price');
            $table->integer('stock_sold')->default(0)->after('stock_quantity');
        });
        
        // Set existing tiers to unlimited (NULL stock_quantity)
        DB::table('digital_product_tiers')->update(['stock_quantity' => null, 'stock_sold' => 0]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('digital_product_tiers', function (Blueprint $table) {
            $table->dropColumn(['stock_quantity', 'stock_sold']);
        });
    }
};
