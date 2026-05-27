<?php

namespace Database\Factories;

use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReportFactory extends Factory
{
    protected $model = Report::class;

    public function definition(): array
    {
        $status = fake()->randomElement(['pending', 'resolved', 'dismissed']);
        $adminNotes = null;

        if ($status !== 'pending') {
            $adminNotes = fake()->sentence() . ' Action taken accordingly.';
        }

        return [
            'reporter_id' => User::factory(),
            'reported_id' => User::factory(),
            'reason' => fake()->randomElement(['Unsportsmanlike behavior', 'No show without warning', 'Aggressive language', 'Cheating/Fake stats']),
            'description' => fake()->paragraph(),
            'status' => $status,
            'admin_notes' => $adminNotes,
        ];
    }
}
