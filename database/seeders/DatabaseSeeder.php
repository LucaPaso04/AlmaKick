<?php

namespace Database\Seeders;

use App\Models\Evaluation;
use App\Models\Registration;
use App\Models\TrustHistory;
use App\Models\User;
use App\Models\Report;
use App\Models\Friendship;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create a specific test user
        $testUser = User::factory()->create([
            'name' => 'Test User',
            'email' => 'admin@email.it',
            'password' => 'password',
            'role' => 'super_admin',
        ]);

        // 2. Create 50 random players
        $users = User::factory(50)->create()->concat([$testUser]);

        // 3. Create 20 matches hosted by random players
        $matchClass = \App\Models\SoccerMatch::class;
        $matches = $matchClass::factory()->count(20)->make()->each(function ($match) use ($users) {
            $match->host_id = $users->random()->id;
            $match->save();
        });

        // 4. For each match, generate player registrations, evaluations, and trust histories
        foreach ($matches as $match) {
            // Determine max players based on format (5v5 => 10, 7v7 => 14, 8v8 => 16)
            $playerCount = 10;
            if ($match->format === '7v7') {
                $playerCount = 14;
            } elseif ($match->format === '8v8') {
                $playerCount = 16;
            }

            // We can register between playerCount - 3 and playerCount + 2 (waitlist)
            $actualRegistrationsCount = rand($playerCount - 3, $playerCount + 2);
            $selectedUsers = $users->where('id', '!=', $match->host_id)->random(min($actualRegistrationsCount, 40));

            $count = 0;
            foreach ($selectedUsers as $user) {
                $count++;

                // Determine registration status
                $status = 'registered';
                if ($count > $playerCount) {
                    $status = 'waitlist';
                }
                if ($count === 1 && $match->status === 'cancelled') {
                    $status = 'cancelled'; // One person cancelled, which could be the reason
                }

                // If match is finished, distribute home/away teams & goal scores
                $team = null;
                $goalsScored = 0;
                if ($match->status === 'finished' && $status === 'registered') {
                    $team = ($count % 2 === 0) ? 'home' : 'away';
                    $goalsScored = rand(0, 3);
                }

                Registration::create([
                    'match_id' => $match->id,
                    'user_id' => $user->id,
                    'status' => $status,
                    'has_guest' => rand(1, 10) === 1, // 10% chance
                    'team' => $team,
                    'goals_scored' => $goalsScored,
                ]);

                // Create Trust History for late cancellations
                if ($status === 'cancelled' && rand(1, 2) === 1) {
                    TrustHistory::create([
                        'user_id' => $user->id,
                        'match_id' => $match->id,
                        'score_change' => -15,
                        'reason' => 'Late cancellation (under 24 hours prior)',
                    ]);
                }
            }

            // Generate Post-match Evaluations
            if ($match->status === 'finished') {
                $registeredPlayers = Registration::where('match_id', $match->id)
                    ->where('status', 'registered')
                    ->get();

                if ($registeredPlayers->count() >= 2) {
                    // Seed a few evaluations among players
                    for ($i = 0; $i < min(5, $registeredPlayers->count()); $i++) {
                        $evaluator = $registeredPlayers->random()->user_id;
                        $evaluated = $registeredPlayers->where('user_id', '!=', $evaluator)->random()->user_id;

                        Evaluation::create([
                            'match_id' => $match->id,
                            'evaluator_id' => $evaluator,
                            'evaluated_id' => $evaluated,
                            'skill_vote' => rand(3, 5),
                            'thumb_down' => rand(1, 20) === 1, // Rare behavioral issue
                        ]);
                    }
                }
            }
        }

        // 5. Seed 15 random Report tickets
        for ($i = 0; $i < 15; $i++) {
            $reporter = $users->random();
            $reported = $users->where('id', '!=', $reporter->id)->random();

            Report::factory()->create([
                'reporter_id' => $reporter->id,
                'reported_id' => $reported->id,
            ]);
        }

        // 6. Seed 40 random Friendships (amicizie)
        $seededFriendships = [];
        for ($i = 0; $i < 40; $i++) {
            $richiedente = $users->random();
            $ricevente = $users->where('id', '!=', $richiedente->id)->random();

            $key = min($richiedente->id, $ricevente->id) . '-' . max($richiedente->id, $ricevente->id);
            if (in_array($key, $seededFriendships)) {
                continue; // Avoid composite primary key duplicate collisions
            }
            $seededFriendships[] = $key;

            Friendship::factory()->create([
                'id_utente_richiedente' => $richiedente->id,
                'id_utente_ricevente' => $ricevente->id,
            ]);
        }
    }
}
