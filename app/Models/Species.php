<?php

namespace App\Models;

use App\Casts\SpeciesTypeCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasMedia;
use Laravel\Scout\Searchable;

class Species extends Model
{
    use HasFactory, HasMedia, Searchable;

    protected $fillable = [
        'type_species',
        'species',
        'author',
        'subspecies',
        'common_name',
        'higher_taxa',
        'species_number',
        'changes',
        'description',
    ];

    protected $casts = [
        'type_species' => SpeciesTypeCast::class,
    ];

    public function toSearchableArray(): array
    {
        return [
            'id'             => $this->id,
            'species'        => $this->species,
            'common_name'    => $this->common_name,
            'author'         => $this->author,
            'higher_taxa'    => $this->higher_taxa,
            'subspecies'     => $this->subspecies,
            'species_number' => (int) $this->species_number,
        ];
    }

    public function searchableAs(): string
    {
        return 'species';
    }

    public function animals()
    {
        return $this->hasMany(Animal::class);
    }

    public function subspecies()
    {
        return $this->hasMany(Subspecies::class);
    }

    public function approvedMedia()
    {
        return $this->morphMany(Media::class, 'mediable')->where('moderation_status', 'approved');
    }
}
