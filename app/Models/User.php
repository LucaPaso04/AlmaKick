<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'friend_code',
        'avatar',
        'role',
        'preferred_role',
        'trust_score',
        'skill_rating',
        'mvp_count',
        'matches_played',
        'total_goals',
        'is_banned',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_banned' => 'boolean',
        ];
    }

    public function updateTrustScore($change, $reason, $match_id = null)
    {
        $new_score = max(0, min(100, $this->trust_score + $change));
        $this->update(['trust_score' => $new_score]);

        TrustHistory::create([
            'user_id' => $this->id,
            'match_id' => $match_id,
            'score_change' => $change,
            'reason' => $reason
        ]);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            if (empty($user->friend_code)) {
                $user->friend_code = strtoupper(\Illuminate\Support\Str::random(6));
            }
        });
    }

    public function friends()
    {
        return $this->belongsToMany(User::class, 'amicizie', 'id_utente_richiedente', 'id_utente_ricevente')
                    ->wherePivot('stato', 'accepted');
    }

    public function pendingFriendRequests()
    {
        return $this->belongsToMany(User::class, 'amicizie', 'id_utente_ricevente', 'id_utente_richiedente')
                    ->wherePivot('stato', 'pending');
    }

    public function matchesHosted()
    {
        return $this->hasMany(\App\Models\SoccerMatch::class, 'host_id');
    }

    public function registrations()
    {
        return $this->hasMany(Registration::class);
    }

    public function evaluationsAsEvaluator()
    {
        return $this->hasMany(Evaluation::class, 'evaluator_id');
    }

    public function evaluationsAsEvaluated()
    {
        return $this->hasMany(Evaluation::class, 'evaluated_id');
    }

    public function trustHistories()
    {
        return $this->hasMany(TrustHistory::class);
    }

    public function reportsSubmitted()
    {
        return $this->hasMany(Report::class, 'reporter_id');
    }

    public function reportsReceived()
    {
        return $this->hasMany(Report::class, 'reported_id');
    }

    public function friendshipsSent()
    {
        return $this->hasMany(Friendship::class, 'id_utente_richiedente');
    }

    public function friendshipsReceived()
    {
        return $this->hasMany(Friendship::class, 'id_utente_ricevente');
    }
}
