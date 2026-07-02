<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RunSummary extends Model
{
    use HasFactory;

    protected $table = 'run_summaries';

    protected $fillable = [
        'user_id',
        'username',
        'distance_km',
        'time',
        'pace',
        'date',
        'route_path',
        'ai_evaluation',
    ];

    protected $casts = [
        'route_path' => 'array',
    ];

    public $timestamps = true;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}