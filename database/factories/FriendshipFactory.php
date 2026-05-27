<?php

namespace Database\Factories;

use App\Models\Friendship;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class FriendshipFactory extends Factory
{
    protected $model = Friendship::class;

    public function definition(): array
    {
        return [
            'id_utente_richiedente' => User::factory(),
            'id_utente_ricevente' => User::factory(),
            'stato' => fake()->randomElement(['pending', 'accepted', 'declined']),
            'created_at' => now(),
        ];
    }
}
