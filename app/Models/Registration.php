<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Registration extends Model
{
    use HasFactory;

    protected $fillable = [
        'match_id',
        'user_id',
        'status',
        'has_guest',
        'team',
        'goals_scored',
    ];

    protected $casts = [
        'has_guest' => 'boolean',
        'goals_scored' => 'integer',
    ];

    public function match()
    {
        return $this->belongsTo(\App\Models\SoccerMatch::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
