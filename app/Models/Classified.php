<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Models\Traits\HasMedia;

class Classified extends Model
{
    use HasFactory, HasMedia;

    protected $fillable = [
        'slug',
        'title',
        'description',
        'price',
        'status',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Classified $classified) {
            if (empty($classified->slug)) {
                $classified->slug = (string) Str::ulid();
            }
        });
    }

    /**
     * Get the user that owns the classified.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
