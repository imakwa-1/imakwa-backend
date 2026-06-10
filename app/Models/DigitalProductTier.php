<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DigitalProductTier extends Model {
    protected $fillable = ['digital_product_id','tier','label','description','price','currency','license_count','licenses_sold','download_url','is_active','file_path','file_size'];

    public function product() { return $this->belongsTo(DigitalProduct::class, 'digital_product_id'); }
    public function orders() { return $this->hasMany(DigitalProductOrder::class); }

    public function availableLicenses(): int {
        return max(0, $this->license_count - $this->licenses_sold);
    }

    public function isSoldOut(): bool {
        return $this->availableLicenses() === 0;
    }
}