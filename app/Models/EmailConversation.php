<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailConversation extends Model
{
    protected $fillable = [
        'contact_email',
        'contact_name',
        'subject',
        'status',
        'last_message_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    public function messages()
    {
        return $this->hasMany(EmailMessage::class, 'conversation_id');
    }

    public function statusBadgeClasses(): string
    {
        return match ($this->status) {
            'open'   => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
            'closed' => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400',
            'spam'   => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
            default  => 'bg-gray-100 text-gray-600',
        };
    }

    public function statusLabel(): string
    {
        return ucfirst($this->status);
    }
}
