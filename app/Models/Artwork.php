<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Artwork extends Model {
    protected $fillable = [
        'artist_id', 'title', 'description', 'medium', 'dimensions', 'year', 
        'price', 'currency', 'stock_quantity', 'stock_sold', 'status', 
        'site_context', 'category', 'region', 'is_active', 'is_approved'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'year' => 'integer',
        'stock_quantity' => 'integer',
        'stock_sold' => 'integer',
        'is_active' => 'boolean',
        'is_approved' => 'boolean',
    ];

    // Computed attribute for available stock
    protected $appends = ['stock_available'];

    public function getStockAvailableAttribute()
    {
        return max(0, ($this->stock_quantity ?? 1) - ($this->stock_sold ?? 0));
    }

    // Check if in stock
    public function isInStock(): bool
    {
        return $this->stock_available > 0 && $this->status !== 'sold' && $this->is_active;
    }

    // Check if out of stock
    public function isOutOfStock(): bool
    {
        return $this->stock_available <= 0;
    }

    // Decrement stock (called after successful payment)
    public function decrementStock(int $quantity = 1): bool
    {
        if ($this->stock_available < $quantity) {
            return false; // Not enough stock
        }

        $this->increment('stock_sold', $quantity);
        
        // Auto-update status if out of stock
        if ($this->stock_available <= 0) {
            $this->update(['status' => 'out_of_stock']); // ✅ Changed from 'sold'
        }

        return true;
    }

    // Restore stock (for cancelled orders/refunds)
    public function restoreStock(int $quantity = 1): void
    {
        $this->decrement('stock_sold', $quantity);
        
        // If was sold/out of stock, mark as available again
        if (($this->status === 'sold' || $this->status === 'out_of_stock') && $this->stock_available > 0) {
            $this->update(['status' => 'available']);
        }
    }

    public function artist() { return $this->belongsTo(Artist::class); }
    public function images() { return $this->hasMany(ArtworkImage::class); }
    public function collections() { return $this->belongsToMany(Collection::class, 'artwork_collections'); }
    public function primaryImage() { return $this->hasOne(ArtworkImage::class)->where('is_primary', true); }
}