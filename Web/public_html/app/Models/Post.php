<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Post extends Model
{
    use HasFactory;

    // Allows all fields to be saved
    protected $guarded = []; 

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'image_url' => 'array', // Converts JSON in DB to PHP Array
        'posted_at' => 'datetime',
    ];

    /**
     * 🛠️ SAFETY ACCESSOR (The Fix)
     * This ensures $post->image_url is ALWAYS an array.
     * If it's a string, it wraps it in []. This stops the emoji fallback.
     */
    public function getImageUrlAttribute($value)
    {
        if (is_array($value)) return $value;
        
        $decoded = null;
        if (is_string($value) && (strpos($value, '[') === 0 || strpos($value, '{') === 0)) {
            $decoded = json_decode($value, true);
        }
        
        if (is_array($decoded)) {
            return $decoded;
        }

        // Clear any json error that might have been set by a failed decode
        json_decode('{}');

        // If it's just a single string path, wrap it in an array for the Carousel
        return $value ? [$value] : [];
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function likes(): HasMany
    {
        return $this->hasMany(Like::class);
    }

    public function likers()
    {
        return $this->belongsToMany(User::class, 'likes', 'post_id', 'user_id');
    }

    /**
     * Helper to get comment count
     */
    public function getCommentsCountAttribute(): int
    {
        return $this->comments()->count();
    }
}