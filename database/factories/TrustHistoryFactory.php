<?php

namespace Database\Factories;

use App\Models\Match;
use App\Models\TrustHistory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TrustHistoryFactory extends Factory
{
    protected $model = TrustHistory::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'match_id' => \App\Models\SoccerMatch::factory(),
            'score_change' => fake()->randomElement([-40, -15, -10, +5, +10]),
            'reason' => fake()->randomElement([
                'Late cancellation (under 24 hours)',
                'No show for match',
                'Completed match successfully',
                'Received excellent MVP rating',
                'Received warning comportment reports',
            ]),
            'created_at' => now(),
        ];
    }
}
