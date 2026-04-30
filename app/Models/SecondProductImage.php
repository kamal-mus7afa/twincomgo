<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecondProductImage extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function product() {
        return $this->belongsTo(SecondProduct::class);
    }

    public function getUrlAttribute()
    {
        return config('services.supabase.url')
            . '/storage/v1/object/public/' .config('services.supabase.bucket')."/"
            . $this->path;
    }
}
