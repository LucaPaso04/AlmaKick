<?php

namespace Database\Factories;

use App\Models\Evaluation;
use App\Models\Match;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EvaluationFactory extends Factory
{
    protected $model = Evaluation::class;

    public function definition(): array
    {
        return [
            'match_id' => \App\Models\SoccerMatch::factory(),
            'evaluator_id' => User::factory(),
            'evaluated_id' => User::factory(),
            'skill_vote' => fake()->numberBetween(1, 5),
            'thumb_down' => fake()->boolean(5), // 5% chance of severe behavioral issue
            'created_at' => now(),
        ];
    }
}
