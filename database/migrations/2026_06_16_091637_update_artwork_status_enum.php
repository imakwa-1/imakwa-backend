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
        // Update the status enum to include 'out_of_stock'
        DB::statement("ALTER TABLE artworks MODIFY COLUMN status ENUM('available', 'reserved', 'sold', 'out_of_stock') DEFAULT 'available'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original enum (convert any 'out_of_stock' to 'sold')
        DB::statement("UPDATE artworks SET status = 'sold' WHERE status = 'out_of_stock'");
        DB::statement("ALTER TABLE artworks MODIFY COLUMN status ENUM('available', 'reserved', 'sold') DEFAULT 'available'");
    }
};
