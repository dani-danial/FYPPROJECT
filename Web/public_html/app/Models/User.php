<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    /**
     * The attributes that are mass assignable.
     * 🛠️ Matching your database column names exactly.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'username',
        'about_me',
        'profile_photo_path',
        'phone',
        'age',
        'gender',
        'running_goal',
        'runner_tier',
        'weight_kg',
        'height_cm',
        'base_pace_min_km',
        'distance_km', 
        'total_runs',  
        'latitude',
        'longitude',
        'status', 
        'role',   
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The accessors to append to the model's array form.
     * 🛠️ Included virtual fields to guarantee sync compatibility.
     */
    protected $appends = [
        'profile_photo_url',
        'total_distance',
        'runs_count',
    ];

    /**
     * The attributes that should be cast.
     * 🛠️ Ensures numeric types for Android compatibility.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'weight_kg' => 'float',
        'height_cm' => 'float',
        'base_pace_min_km' => 'float',
        'distance_km' => 'float',  
        'total_runs' => 'integer', 
        'age' => 'integer',
    ];

    /**
     * Accessor: Get the user's profile photo URL.
     * 🛠️ FIXED: Points directly to your /app/public folder as seen in File Manager.
     */
    /**
     * 🛠️ THE FINAL BYPASS FIX
     * This uses the /serve-image route to sneak the photo past Hostinger's security.
     */
    public function getProfilePhotoUrlAttribute()
    {
        if ($this->profile_photo_path) {
            if (filter_var($this->profile_photo_path, FILTER_VALIDATE_URL)) {
                return $this->profile_photo_path;
            }

            // 🚀 MAGIC CHANGE: Route through our Smart Checker
            return url('/serve-image?path=' . ltrim($this->profile_photo_path, '/'));
        }

        // Default avatar if no photo is set
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&color=FFFFFF&background=8B8B6B';
    }

    /**
     * 🛠️ Virtual Accessor: total_distance
     * Returns the value of distance_km so the app syncs correctly.
     */
    public function getTotalDistanceAttribute()
    {
        return $this->distance_km ?? 0.00;
    }

    /**
     * 🛠️ Virtual Accessor: runs_count
     * Returns the value of total_runs so the app syncs correctly.
     */
    public function getRunsCountAttribute()
    {
        return $this->total_runs ?? 0;
    }

    // ===========================================
    // RELATIONSHIPS
    // ===========================================

    /**
     * Joined events.
     */
    public function joinedEvents()
    {
        return $this->belongsToMany(Event::class, 'event_user');
    }

    /**
     * Run summaries.
     */
    public function runs()
    {
        return $this->hasMany(RunSummary::class);
    }

    /**
     * Followers.
     */
    public function followers()
    {
        return $this->belongsToMany(User::class, 'follows', 'following_id', 'follower_id');
    }

    /**
     * Following.
     */
    public function following()
    {
        return $this->belongsToMany(User::class, 'follows', 'follower_id', 'following_id');
    }

    /**
     * Joined Groups.
     */
    public function joinedGroups()
    {
        return $this->belongsToMany(Group::class, 'group_user')->withTimestamps();
    }
}
