<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Dyrynda\Database\Support\GeneratesUuid;
use Dyrynda\Database\Casts\EfficientUuid;
use Cviebrock\EloquentSluggable\Sluggable;

class Animal extends Model
{
    use HasFactory, Sluggable, GeneratesUuid;

    protected $casts = [
        'uuid' => EfficientUuid::class,
    ];

    /**
     * Return the sluggable configuration array for this model.
     *
     * @return array
     */
    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'pet_name'
            ]
        ];
    }

    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
    }
}
