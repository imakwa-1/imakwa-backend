<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DigitalProductOrder extends Model {
    protected $fillable = [
        'reference',
        'digital_product_tier_id',
        'email',
        'download_token',
        'token_used',
        'token_expires_at',
        'payment_status',
        'payment_reference',
        'payment_gateway',
        'amount_paid',
        'payment_intent_id',
        'paystack_reference',
    ];

    protected $casts = [
        'token_expires_at' => 'datetime',
        'token_used' => 'boolean',
    ];

    protected static function boot() {
        parent::boot();
        static::creating(function ($order) {
            $order->reference = 'WC-' . strtoupper(Str::random(8));
        });
    }

    public function tier() {
        return $this->belongsTo(DigitalProductTier::class, 'digital_product_tier_id');
    }

    /**
     * Generate a unique download token and set expiration
     */
    public function generateDownloadToken(): string
    {
        $this->download_token = Str::random(64);
        $this->token_expires_at = now()->addDays(30);
        $this->token_used = false;
        $this->save();

        return $this->download_token;
    }

    /**
     * Check if download token is valid
     */
    public function isTokenValid(): bool
    {
        return !$this->token_used && 
               $this->token_expires_at && 
               $this->token_expires_at->isFuture();
    }

    /**
     * Mark token as used
     */
    public function markTokenAsUsed(): void
    {
        $this->token_used = true;
        $this->save();
    }
}