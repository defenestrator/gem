<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InquiryReply extends Model
{
    protected $fillable = ['inquiry_id', 'admin_user_id', 'body'];

    public function inquiry()
    {
        return $this->belongsTo(Inquiry::class);
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_user_id');
    }
}
