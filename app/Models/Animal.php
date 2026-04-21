<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\Sluggable;
use App\Models\Traits\HasMedia;

class Animal extends Model
{
    use HasFactory, HasMedia, Sluggable;

    protected $fillable = [
        'pet_name',
        'description',
        'date_of_birth',
        'female',
        'proven_breeder',
        'acquisition_date',
        'acquisition_cost',
        'status',
        'user_id',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'acquisition_date' => 'date',
        'female' => 'boolean',
        'proven_breeder' => 'boolean',
    ];

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'pet_name'
            ]
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Animal $animal) {
            if (empty($animal->slug)) {
                $animal->slug = static::generateUniqueSlug($animal->pet_name);
            }
        });
    }

    protected static function generateUniqueSlug(string $name): string
    {
        $base = static::createSlug($name);
        $slug = $base;
        $i = 1;
        while (static::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function classifieds()
    {
        return $this->hasMany(Classified::class);
    }
}
