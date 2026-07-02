<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SosSignal extends Model
{
    use HasFactory;

    // 🛠️ Explicitly tell Laravel which table to use
    protected $table = 'sos_signals';

    /**
     * 📋 ALLOWED FIELDS
     * Matches your phpMyAdmin screenshot columns exactly.
     */
    protected $fillable = [
        'user_name',
        'user_identifier',
        'phone_number',
        'message',
        'location_name',
        'latitude',
        'longitude',
        'status',
        'signal_time',
    ];

    /**
     * ⚙️ DATA CASTING
     * Ensures coordinates are treated as numbers and time as a Carbon object.
     */
    protected $casts = [
        'signal_time' => 'datetime',
        'latitude'    => 'double',
        'longitude'   => 'double',
    ];

    /**
     * 🛡️ DEFAULT VALUES
     * Automatically set status to pending if not provided.
     */
    protected $attributes = [
        'status' => 'pending',
    ];
}