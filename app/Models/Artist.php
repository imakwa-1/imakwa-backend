<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Artist extends Model {
    protected $fillable = ['user_id','display_name','country','region','bio','profile_image','instagram','website','is_verified','is_active','is_featured'];

    public function user() { return $this->belongsTo(User::class); }
    public function artworks() { return $this->hasMany(Artwork::class); }
    public function collections() { return $this->hasMany(Collection::class); }
}