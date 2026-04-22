<?php

namespace App\Models;

use App\Models\Traits\HasMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Seller extends Model
{
    use HasFactory, HasMedia;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'email',
        'phone',
        'website',
        'instagram',
        'youtube',
        'facebook',
        'morph_market',
    ];

    protected static function booted(): void
    {
        static::creating(function (Seller $seller) {
            if (empty($seller->slug)) {
                $seller->slug = Str::slug($seller->name) . '-' . Str::lower(Str::random(6));
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
