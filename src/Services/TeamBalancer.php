<?php

namespace App\Services;

use App\Database;
use PDO;

class TeamBalancer
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Balance teams for match
     */
    public function balanceTeams(int $matchId): bool
    {
        // Load active players
        $stmt = $this->db->prepare("
            SELECT r.id, r.has_guest, u.skill_rating 
            FROM registrations r
            JOIN users u ON r.username = u.username
            WHERE r.match_id = :match_id AND r.status = 'registered'
        ");
        $stmt->execute(['match_id' => $matchId]);
        $players = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($players) < 2) {
            return false;
        }

        // Sort by skill descending
        usort($players, function($a, $b) {
            return $b['skill_rating'] <=> $a['skill_rating'];
        });

        // Greedy snake draft balancer
        $homeIds = [];
        $awayIds = [];
        $homeWeight = 0;
        $awayWeight = 0;

        foreach ($players as $idx => $player) {
            $weight = 1 + (int)$player['has_guest'];
            if ($homeWeight < $awayWeight) {
                $homeIds[] = $player['id'];
                $homeWeight += $weight;
            } elseif ($awayWeight < $homeWeight) {
                $awayIds[] = $player['id'];
                $awayWeight += $weight;
            } else {
                $round = (int)floor($idx / 2);
                if ($round % 2 === 0) {
                    if ($idx % 2 === 0) {
                        $homeIds[] = $player['id'];
                        $homeWeight += $weight;
                    } else {
                        $awayIds[] = $player['id'];
                        $awayWeight += $weight;
                    }
                } else {
                    if ($idx % 2 === 0) {
                        $awayIds[] = $player['id'];
                        $awayWeight += $weight;
                    } else {
                        $homeIds[] = $player['id'];
                        $homeWeight += $weight;
                    }
                }
            }
        }

        // Update DB
        $stmtUpdateHome = $this->db->prepare("UPDATE registrations SET team = 'home' WHERE id = :id");
        foreach ($homeIds as $regId) {
            $stmtUpdateHome->execute(['id' => $regId]);
        }
        $stmtUpdateAway = $this->db->prepare("UPDATE registrations SET team = 'away' WHERE id = :id");
        foreach ($awayIds as $regId) {
            $stmtUpdateAway->execute(['id' => $regId]);
        }

        return true;
    }
}
