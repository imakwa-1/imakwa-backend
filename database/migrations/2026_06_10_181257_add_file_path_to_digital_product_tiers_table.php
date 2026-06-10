<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('digital_product_tiers', function (Blueprint $table) {
            $table->string('file_path')->nullable()->after('price');
            $table->bigInteger('file_size')->nullable()->after('file_path'); // in bytes
        });
    }

    public function down(): void
    {
        Schema::table('digital_product_tiers', function (Blueprint $table) {
            $table->dropColumn(['file_path', 'file_size']);
        });
    }
};
