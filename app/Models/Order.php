<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Order extends Model {
    protected $fillable = [
        'user_id', 'reference', 'fulfillment_type', 'status', 'payment_gateway',
        'payment_reference', 'payment_status', 'subtotal', 'shipping_cost', 'total',
        'currency', 'shipping_name', 'shipping_email', 'shipping_phone',
        'shipping_address', 'shipping_city', 'shipping_country', 'shipping_postal_code',
        'payment_intent_id', 'paystack_reference',
        'shipped_at', 'delivered_at', 'cancelled_at', 'tracking_number',
        'admin_notes', 'cancellation_reason',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'total' => 'decimal:2',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    protected static function boot() {
        parent::boot();
        static::creating(function ($order) {
            $order->reference = 'IMK-' . strtoupper(Str::random(8));
        });
    }

    // ===== RELATIONSHIPS =====

    public function user() { return $this->belongsTo(User::class); }
    public function items() { return $this->hasMany(OrderItem::class); }
    public function statusHistories()
    {
        return $this->hasMany(OrderStatusHistory::class)->orderBy('created_at', 'desc');
    }

    // ===== SCOPES =====

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
    }

    public function scopeShipped($query)
    {
        return $query->where('status', 'shipped');
    }

    public function scopeDelivered($query)
    {
        return $query->where('status', 'delivered');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    // ===== ACCESSORS =====

    public function getCustomerNameAttribute(): string
    {
        return $this->shipping_name ?? $this->user?->name ?? 'Guest';
    }

    public function getCustomerEmailAttribute(): string
    {
        return $this->shipping_email ?? $this->user?->email ?? 'N/A';
    }

    // ===== BUSINESS LOGIC =====

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    /**
     * Safely transition order to new status with validation and side effects
     */
    public function transitionTo(
        string $newStatus,
        ?string $notes = null,
        ?string $trackingNumber = null,
        ?string $cancellationReason = null
    ): void {
        $oldStatus = $this->status;

        // No-op if already in target status
        if ($oldStatus === $newStatus) {
            return;
        }

        // Validation: Cannot modify final states
        if ($oldStatus === 'cancelled') {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'status' => ['Cannot transition a cancelled order.']
            ]);
        }

        if ($oldStatus === 'delivered' && $newStatus !== 'cancelled') {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'status' => ['Cannot change status of a delivered order except to cancel it.']
            ]);
        }

        // Business rule: Cannot ship unpaid orders
        if ($newStatus === 'shipped' && !$this->isPaid()) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'status' => ['Cannot ship an unpaid order. Please verify payment first.']
            ]);
        }

        // Prepare update data
        $updates = ['status' => $newStatus];

        // Update timestamps based on new status
        if ($newStatus === 'shipped') {
            $updates['shipped_at'] = now();
            if ($trackingNumber) {
                $updates['tracking_number'] = $trackingNumber;
            }
        } elseif ($newStatus === 'delivered') {
            $updates['delivered_at'] = now();
        } elseif ($newStatus === 'cancelled') {
            $updates['cancelled_at'] = now();
            if ($cancellationReason) {
                $updates['cancellation_reason'] = $cancellationReason;
            }

            // Restore stock for all items
            $this->restoreStockForAllItems();
        }

        if ($notes) {
            $updates['admin_notes'] = $notes;
        }

        // Apply updates
        $this->update($updates);

        // Log status history
        $this->statusHistories()->create([
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'changed_by_user_id' => auth()->id(),
            'notes' => $notes,
        ]);

        // Send notification email
        $this->sendNotificationEmail($newStatus);
    }

    /**
     * Restore stock for all order items (used when cancelling)
     */
    protected function restoreStockForAllItems(): void
    {
        foreach ($this->items as $item) {
            if ($item->itemable_type === Artwork::class && $item->itemable) {
                $item->itemable->restoreStock($item->quantity);
            }
        }
    }

    /**
     * Send appropriate email based on status
     */
    protected function sendNotificationEmail(string $status): void
    {
        if (!$this->shipping_email) {
            return;
        }

        try {
            $mailClass = match ($status) {
                'processing' => \App\Mail\OrderProcessingMail::class,
                'shipped' => \App\Mail\OrderShippedMail::class,
                'delivered' => \App\Mail\OrderDeliveredMail::class,
                'cancelled' => \App\Mail\OrderCancelledMail::class,
                default => null,
            };

            if ($mailClass) {
                \Illuminate\Support\Facades\Mail::to($this->shipping_email)->send(new $mailClass($this));
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Failed to send order status email for order {$this->reference}: " . $e->getMessage());
        }
    }
}