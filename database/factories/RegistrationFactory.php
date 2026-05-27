<?php

namespace Database\Factories;

use App\Models\Match;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class RegistrationFactory extends Factory
{
    protected $model = Registration::class;

    public function definition(): array
    {
        return [
            'match_id' => \App\Models\SoccerMatch::factory(),
            'user_id' => User::factory(),
            'status' => fake()->randomElement(['registered', 'waitlist', 'cancelled']),
            'has_guest' => fake()->boolean(10), // 10% chance of bringing a guest
            'team' => fake()->randomElement(['home', 'away', null]),
            'goals_scored' => fake()->numberBetween(0, 4),
        ];
    }
}
