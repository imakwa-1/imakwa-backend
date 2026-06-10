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
}