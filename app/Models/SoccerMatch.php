<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SoccerMatch extends Model
{
    use HasFactory;

    // Explicitly define matches table to bypass PHP 8 Match reserved keyword conflict
    protected $table = 'matches';

    protected $fillable = [
        'host_id',
        'date',
        'time',
        'format',
        'max_players',
        'location',
        'latitude',
        'longitude',
        'visibility',
        'total_cost',
        'status',
        'cancellation_reason',
        'is_urgent',
        'result_home',
        'result_away',
        'mvp_deadline',
        'mvp_assigned',
        'mvp_id',
    ];

    protected $casts = [
        'date' => 'date',
        'is_urgent' => 'boolean',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'total_cost' => 'decimal:2',
        'mvp_deadline' => 'datetime',
        'mvp_assigned' => 'boolean',
    ];

    public function host()
    {
        return $this->belongsTo(User::class, 'host_id');
    }

    public function registrations()
    {
        return $this->hasMany(Registration::class, 'match_id');
    }

    public function evaluations()
    {
        return $this->hasMany(Evaluation::class, 'match_id');
    }
}
