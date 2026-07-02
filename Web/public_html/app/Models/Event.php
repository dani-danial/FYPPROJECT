<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'date',
        'time',
        'runner_tier',
        'location',
        'state',
        'run_type',
        'logo_path',
        'entry_fee',   
        'latitude',
        'longitude',
        'distance_km',
        'description',
        'organizer',
        'status'
    ];
  
    /**
     * Relationship: Many runners can join an event.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'event_user');
    }
    
    public function participants()
{
    // This tells Laravel to look at the 'event_user' table
    return $this->belongsToMany(User::class, 'event_user');
}
}