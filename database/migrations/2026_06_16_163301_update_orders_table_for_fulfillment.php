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
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('shipped_at')->nullable()->after('status');
            $table->timestamp('delivered_at')->nullable()->after('shipped_at');
            $table->timestamp('cancelled_at')->nullable()->after('delivered_at');
            $table->string('tracking_number')->nullable()->after('cancelled_at');
            $table->text('admin_notes')->nullable()->after('tracking_number');
            $table->text('cancellation_reason')->nullable()->after('admin_notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'shipped_at',
                'delivered_at',
                'cancelled_at',
                'tracking_number',
                'admin_notes',
                'cancellation_reason',
            ]);
        });
    }
};
