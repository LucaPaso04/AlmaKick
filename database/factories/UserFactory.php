<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'phone' => fake()->phoneNumber(),
            'friend_code' => strtoupper(Str::random(6)),
            'avatar' => null,
            'role' => fake()->randomElement(['user', 'user', 'user', 'user', 'super_admin']),
            'preferred_role' => fake()->randomElement(['Goalkeeper', 'Defender', 'Midfielder', 'Striker', 'All-rounder']),
            'trust_score' => fake()->numberBetween(70, 100),
            'skill_rating' => fake()->randomFloat(2, 2.5, 5.0),
            'mvp_count' => fake()->numberBetween(0, 10),
            'matches_played' => fake()->numberBetween(0, 40),
            'total_goals' => fake()->numberBetween(0, 30),
            'is_banned' => fake()->boolean(2), // 2% chance of being banned
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
