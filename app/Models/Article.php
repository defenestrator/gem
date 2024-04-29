<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasMedia;

class Article extends Model
{
    use HasFactory, HasMedia;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title', 'subtitle', 'slug', 'body',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
