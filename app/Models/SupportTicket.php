<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportTicket extends Model
{
    protected $fillable = ['name', 'email', 'type', 'message', 'user_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
