<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DigitalProductTier extends Model {
    protected $fillable = [
        'digital_product_id', 'tier', 'label', 'description', 'price', 'currency', 
        'license_count', 'licenses_sold', 'stock_quantity', 'stock_sold',
        'download_url', 'is_active', 'file_path', 'file_size'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'license_count' => 'integer',
        'licenses_sold' => 'integer',
        'stock_quantity' => 'integer', // NULL = unlimited
        'stock_sold' => 'integer',
        'is_active' => 'boolean',
    ];

    // Computed attributes
    protected $appends = ['stock_available', 'is_unlimited'];

    public function getStockAvailableAttribute()
    {
        if ($this->stock_quantity === null) {
            return PHP_INT_MAX; // Unlimited
        }
        return max(0, $this->stock_quantity - $this->stock_sold);
    }

    public function getIsUnlimitedAttribute()
    {
        return $this->stock_quantity === null;
    }

    // Legacy method - keep for backwards compatibility
    public function availableLicenses(): int {
        return max(0, $this->license_count - $this->licenses_sold);
    }

    // Updated to check stock_quantity
    public function isSoldOut(): bool {
        if ($this->stock_quantity === null) {
            return false; // Unlimited stock
        }
        return $this->stock_available <= 0;
    }

    // Decrement stock
    public function decrementStock(int $quantity = 1): bool
    {
        if ($this->stock_quantity === null) {
            return true; // Unlimited, always succeeds
        }

        if ($this->stock_available < $quantity) {
            return false;
        }

        $this->increment('stock_sold', $quantity);
        return true;
    }

    // Restore stock
    public function restoreStock(int $quantity = 1): void
    {
        if ($this->stock_quantity !== null) {
            $this->decrement('stock_sold', $quantity);
        }
    }

    public function product() { return $this->belongsTo(DigitalProduct::class, 'digital_product_id'); }
    public function digital_product() { return $this->belongsTo(DigitalProduct::class, 'digital_product_id'); }
    public function orders() { return $this->hasMany(DigitalProductOrder::class); }
}