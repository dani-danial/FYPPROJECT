<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    // 1. If you DON'T have 'created_at' and 'updated_at' columns in phpMyAdmin, add this:
    public $timestamps = false; 

    protected $fillable = [
        'name', 
        'description', 
        'location', 
        'members_count', 
        'status', 
        'target_km', 
        'icon_url',  // 🛠️ Correct: Now on the "VIP List"
        'banner_url', // 🛠️ Correct: Now on the "VIP List"
        'creator_id', 
        'created_date'
    ];

    protected $casts = [
        'created_date' => 'datetime',
        'target_km' => 'float',
        'members_count' => 'integer',
    ];

    public function users()
    {
        // Standard many-to-many relationship
        return $this->belongsToMany(User::class, 'group_user');
    }

    public function creator()
    {
        // Links creator_id to the User table
        return $this->belongsTo(User::class, 'creator_id');
    }
    
    public function members()
    {
        // This connects to the 'group_user' pivot table
        return $this->belongsToMany(User::class, 'group_user')->withTimestamps();
    }

    public function messages()
    {
        return $this->hasMany(GroupMessage::class);
    }
}