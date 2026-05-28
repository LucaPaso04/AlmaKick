<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrustHistory extends Model
{
    use HasFactory;

    // Explicitly set table name since Laravel's pluralizer expects trust_histories
    protected $table = 'trust_history';

    // This table only has created_at
    const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'match_id',
        'score_change',
        'reason',
    ];

    protected $casts = [
        'score_change' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function match()
    {
        return $this->belongsTo(\App\Models\SoccerMatch::class);
    }
}
