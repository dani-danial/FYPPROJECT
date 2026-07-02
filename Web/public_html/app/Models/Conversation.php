<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     * This allows us to use Conversation::create() without crashing!
     */
    protected $fillable = [
        'sender_id',
        'receiver_id',
    ];

    /**
     * Get the messages associated with this conversation.
     */
    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Get the user who started the conversation.
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Get the user who is receiving the conversation.
     */
    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    /**
     * Helper to quickly get the "other" person in the chat.
     * Useful for displaying the name in your chat sidebar.
     */
    public function getOtherUserAttribute()
    {
        if ($this->sender_id === auth()->id()) {
            return $this->receiver;
        }
        return $this->sender;
    }
}