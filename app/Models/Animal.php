<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Traits\Sluggable;
use Traits\HasMedia;

class Animal extends Model
{
    use HasFactory, HasMedia, Sluggable;


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
}
