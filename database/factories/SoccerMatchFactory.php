<?php

namespace Database\Factories;

use App\Models\SoccerMatch;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SoccerMatchFactory extends Factory
{
    protected $model = SoccerMatch::class;

    public function definition(): array
    {
        $date = fake()->dateTimeBetween('-15 days', '+15 days');
        $isPast = $date < new \DateTime();

        if ($isPast) {
            $status = fake()->randomElement(['finished', 'cancelled']);
        } else {
            $status = fake()->randomElement(['open', 'full']);
        }

        $resultHome = null;
        $resultAway = null;
        $cancellationReason = null;

        if ($status === 'finished') {
            $resultHome = fake()->numberBetween(0, 10);
            $resultAway = fake()->numberBetween(0, 10);
        } elseif ($status === 'cancelled') {
            $cancellationReason = fake()->randomElement(['Bad weather', 'Not enough players', 'Pitch unavailable']);
        }

        return [
            'host_id' => User::factory(),
            'date' => $date->format('Y-m-d'),
            'time' => fake()->time('H:i'),
            'format' => fake()->randomElement(['5v5', '7v7', '8v8']),
            'max_players' => fake()->randomElement([10, 14, 16]),
            'location' => fake()->streetName() . ' Sports Center, Bologna',
            'latitude' => fake()->latitude(44.48, 44.52),
            'longitude' => fake()->longitude(11.31, 11.38),
            'visibility' => fake()->randomElement(['public', 'private']),
            'total_cost' => fake()->randomElement([50.00, 60.00, 70.00, 80.00]),
            'status' => $status,
            'cancellation_reason' => $cancellationReason,
            'is_urgent' => fake()->boolean(15),
            'result_home' => $resultHome,
            'result_away' => $resultAway,
            'mvp_deadline' => $status === 'finished' ? fake()->dateTimeBetween('-1 day', 'now') : null,
            'mvp_assigned' => $status === 'finished',
            'mvp_id' => null,
        ];
    }
}
