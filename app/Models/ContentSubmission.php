<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ContentSubmission extends Model
{
    protected $table = 'species_content_submissions';

    protected $fillable = [
        'submittable_type',
        'submittable_id',
        'user_id',
        'proposed_value',
        'status',
        'reviewer_id',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function submittable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}
