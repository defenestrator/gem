<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasMedia;
use Laravel\Scout\Searchable;

class Subspecies extends Model
{
    use HasFactory, HasMedia, Searchable;

    protected $fillable = [
        'species_id',
        'genus',
        'species',
        'subspecies',
        'author',
    ];

    public function parentSpecies()
    {
        return $this->belongsTo(Species::class, 'species_id');
    }

    public function approvedMedia()
    {
        return $this->morphMany(Media::class, 'mediable')->where('moderation_status', 'approved');
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->genus} {$this->species} {$this->subspecies}";
    }

    public function toSearchableArray(): array
    {
        return [
            'id'         => $this->id,
            'genus'      => $this->genus,
            'species'    => $this->species,
            'subspecies' => $this->subspecies,
            'full_name'  => $this->full_name,
            'author'     => $this->author,
            'species_id' => $this->species_id,
        ];
    }

    public function searchableAs(): string
    {
        return 'subspecies';
    }
}
