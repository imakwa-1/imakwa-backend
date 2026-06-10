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
        Schema::table('digital_product_orders', function (Blueprint $table) {
            $table->string('reference')->unique()->after('id');
            $table->string('payment_intent_id')->nullable()->after('payment_reference');
            $table->string('paystack_reference')->nullable()->after('payment_intent_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('digital_product_orders', function (Blueprint $table) {
            $table->dropColumn(['reference', 'payment_intent_id', 'paystack_reference']);
        });
    }
};
