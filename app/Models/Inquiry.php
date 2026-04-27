<?php

namespace App\Models;

use App\Enums\InquiryStatus;
use Illuminate\Database\Eloquent\Model;

class Inquiry extends Model
{
    protected $fillable = [
        'animal_id',
        'classified_id',
        'user_id',
        'name',
        'email',
        'phone',
        'message',
        'status',
    ];

    protected $casts = [
        'status' => InquiryStatus::class,
    ];

    public function animal()
    {
        return $this->belongsTo(Animal::class);
    }

    public function classified()
    {
        return $this->belongsTo(Classified::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function replies()
    {
        return $this->hasMany(InquiryReply::class)->oldest();
    }
}
