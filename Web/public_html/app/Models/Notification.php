<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'user_id', // ADDED: To track which runner owns this notification
        'title', 
        'message', 
        'type', 
        'status', 
        'scheduled_at', 
        'recipients_count'
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
    ];

    // Relationship to User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}