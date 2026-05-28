<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Evaluation extends Model
{
    use HasFactory;

    // This table only has created_at
    const UPDATED_AT = null;

    protected $fillable = [
        'match_id',
        'evaluator_id',
        'evaluated_id',
        'skill_vote',
        'thumb_down',
    ];

    protected $casts = [
        'skill_vote' => 'integer',
        'thumb_down' => 'boolean',
    ];

    public function match()
    {
        return $this->belongsTo(\App\Models\SoccerMatch::class);
    }

    public function evaluator()
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }

    public function evaluated()
    {
        return $this->belongsTo(User::class, 'evaluated_id');
    }
}
