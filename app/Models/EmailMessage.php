<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailMessage extends Model
{
    protected $fillable = [
        'conversation_id',
        'direction',
        'from_email',
        'from_name',
        'to_email',
        'body_text',
        'body_html',
        'sendgrid_message_id',
    ];

    public function conversation()
    {
        return $this->belongsTo(EmailConversation::class, 'conversation_id');
    }

    public function isInbound(): bool
    {
        return $this->direction === 'inbound';
    }
}
