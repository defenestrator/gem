<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\Sluggable;

class Classified extends Model
{
    /** @use HasFactory<\Database\Factories\ClassifiedFactory> */
    use HasFactory, Sluggable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'price',
        'status',
        'animal_id',
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
                'source' => 'title'
            ]
        ];
    }

    /**
     * Get the user that owns the classified.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the animal associated with the classified.
     */
    public function animal()
    {
        return $this->belongsTo(Animal::class);
    }
}
