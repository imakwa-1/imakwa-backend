<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CheckInventorySetup extends Command
{
    protected $signature = 'inventory:check';
    protected $description = 'Check if inventory system columns exist in database';

    public function handle()
    {
        $this->info('🔍 Checking Inventory System Setup...');
        $this->newLine();

        // Check artworks table
        $this->info('📦 Artworks Table:');
        $artworksColumns = Schema::getColumnListing('artworks');
        
        $this->checkColumn($artworksColumns, 'stock_quantity', 'artworks');
        $this->checkColumn($artworksColumns, 'stock_sold', 'artworks');
        
        // Check status enum values
        $statusCheck = DB::select("SHOW COLUMNS FROM artworks WHERE Field = 'status'");
        if (!empty($statusCheck)) {
            $enumValues = $statusCheck[0]->Type;
            $this->line("   Status ENUM: {$enumValues}");
            
            if (str_contains($enumValues, 'out_of_stock')) {
                $this->info("   ✅ Status enum includes 'out_of_stock'");
            } else {
                $this->error("   ❌ Status enum missing 'out_of_stock'");
            }
        }
        
        $this->newLine();

        // Check digital_product_tiers table
        $this->info('🎫 Digital Product Tiers Table:');
        $tiersColumns = Schema::getColumnListing('digital_product_tiers');
        
        $this->checkColumn($tiersColumns, 'stock_quantity', 'digital_product_tiers');
        $this->checkColumn($tiersColumns, 'stock_sold', 'digital_product_tiers');
        
        $this->newLine();

        // Check for sample data
        $this->info('📊 Sample Data:');
        $artwork = DB::table('artworks')->first();
        if ($artwork) {
            $this->line("   Artwork ID {$artwork->id}:");
            $this->line("   - stock_quantity: " . ($artwork->stock_quantity ?? 'NULL'));
            $this->line("   - stock_sold: " . ($artwork->stock_sold ?? 'NULL'));
            $this->line("   - status: {$artwork->status}");
        }
        
        $tier = DB::table('digital_product_tiers')->first();
        if ($tier) {
            $this->newLine();
            $this->line("   Tier ID {$tier->id}:");
            $this->line("   - stock_quantity: " . ($tier->stock_quantity ?? 'NULL (unlimited)'));
            $this->line("   - stock_sold: " . ($tier->stock_sold ?? 'NULL'));
        }

        $this->newLine();
        $this->info('✅ Inventory check complete!');
        
        return 0;
    }

    private function checkColumn(array $columns, string $columnName, string $tableName)
    {
        if (in_array($columnName, $columns)) {
            $this->info("   ✅ {$columnName} exists");
        } else {
            $this->error("   ❌ {$columnName} missing - run: php artisan migrate");
        }
    }
}
