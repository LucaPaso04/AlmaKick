<?php

namespace App\Models;

use App\Database;
use PDO;

class SoccerMatch
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT m.*, u.name as host_name 
            FROM matches m 
            JOIN users u ON m.host_username = u.username 
            WHERE m.id = :id
        ");
        $stmt->execute(['id' => $id]);
        $match = $stmt->fetch();
        return $match ?: null;
    }

    public function getAllActive(array $filters = []): array
    {
        $where = [];
        $where[] = "m.status IN ('open', 'full')";
        $where[] = "CONCAT(m.date, ' ', m.time) >= NOW()";
        $params = [];

        $sessionUsername = $filters['username'] ?? null;
        $params['session_username'] = $sessionUsername ?: '';

        // Visibility control
        $isAdmin = (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'super_admin') ? 1 : 0;
        if ($sessionUsername) {
            $where[] = "(m.visibility = 'public' 
                         OR m.host_username = :session_username_vis 
                         OR :is_admin = 1 
                         OR EXISTS (
                             SELECT 1 FROM friendships f 
                             WHERE f.status = 'accepted' 
                               AND (
                                 (f.sender_username = m.host_username AND f.recipient_username = :session_username_vis2)
                                 OR 
                                 (f.recipient_username = m.host_username AND f.sender_username = :session_username_vis3)
                               )
                         ))";
            $params['session_username_vis'] = $sessionUsername;
            $params['session_username_vis2'] = $sessionUsername;
            $params['session_username_vis3'] = $sessionUsername;
            $params['is_admin'] = $isAdmin;
        } else {
            $where[] = "m.visibility = 'public'";
        }

        if (!empty($filters['location'])) {
            $where[] = "m.location LIKE :location";
            $params['location'] = '%' . $filters['location'] . '%';
        }

        if (!empty($filters['date_from'])) {
            $where[] = "m.date >= :date_from";
            $params['date_from'] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $where[] = "m.date <= :date_to";
            $params['date_to'] = $filters['date_to'];
        }

        if (!empty($filters['format'])) {
            $fmt1 = $filters['format'];
            $fmt2 = str_replace('vs', 'v', $fmt1);
            $fmt3 = str_replace('v', 'vs', $fmt1);
            $where[] = "(m.format = :format_val1 OR m.format = :format_val2)";
            $params['format_val1'] = $fmt2;
            $params['format_val2'] = $fmt3;
        }

        if (!empty($filters['only_friends']) && $sessionUsername) {
            $friends = $this->getFriendUsernames($sessionUsername);
            if (!empty($friends)) {
                $placeholders = [];
                foreach ($friends as $idx => $friend) {
                    $key = 'friend_toggle_' . $idx;
                    $placeholders[] = ':' . $key;
                    $params[$key] = $friend;
                }
                $where[] = "m.host_username IN (" . implode(', ', $placeholders) . ")";
            } else {
                $where[] = "1 = 0";
            }
        }

        if (!empty($filters['filter'])) {
            if ($filters['filter'] === 'friends' && $sessionUsername) {
                $friends = $this->getFriendUsernames($sessionUsername);
                if (!empty($friends)) {
                    $placeholders = [];
                    foreach ($friends as $idx => $friend) {
                        $key = 'friend_' . $idx;
                        $placeholders[] = ':' . $key;
                        $params[$key] = $friend;
                    }
                    $where[] = "m.host_username IN (" . implode(', ', $placeholders) . ")";
                } else {
                    $where[] = "1 = 0";
                }
            } elseif ($filters['filter'] === 'mine' && $sessionUsername) {
                $where[] = "(m.host_username = :my_username OR EXISTS (SELECT 1 FROM registrations r WHERE r.match_id = m.id AND r.username = :my_username2 AND r.status IN ('registered', 'waitlist')))";
                $params['my_username'] = $sessionUsername;
                $params['my_username2'] = $sessionUsername;
            }
        }

        if (!empty($filters['exclude_my_matches']) && $sessionUsername) {
            $where[] = "m.host_username != :exclude_username 
                        AND NOT EXISTS (
                            SELECT 1 FROM registrations r 
                            WHERE r.match_id = m.id 
                              AND r.username = :exclude_username2 
                              AND r.status IN ('registered', 'waitlist')
                        )";
            $params['exclude_username'] = $sessionUsername;
            $params['exclude_username2'] = $sessionUsername;
        }

        $whereSql = '';
        if (!empty($where)) {
            $whereSql = 'WHERE ' . implode(' AND ', $where);
        }

        $sql = "
            SELECT m.*, u.name as host_name,
                    (SELECT COALESCE(SUM(1 + r.has_guest), 0) FROM registrations r WHERE r.match_id = m.id AND (r.status = 'registered' OR (r.status = 'waitlist' AND r.offer_expires_at IS NOT NULL AND r.offer_expires_at > NOW()))) as posti_occupati,
                   (SELECT r.status FROM registrations r WHERE r.match_id = m.id AND r.username = :session_username LIMIT 1) as user_registration_status
            FROM matches m 
            JOIN users u ON m.host_username = u.username 
            $whereSql
            ORDER BY m.date ASC, m.time ASC
        ";

        if (isset($filters['limit']) && isset($filters['offset'])) {
            $sql .= " LIMIT :limit OFFSET :offset";
        }

        $stmt = $this->db->prepare($sql);

        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        if (isset($filters['limit']) && isset($filters['offset'])) {
            $stmt->bindValue('limit', (int) $filters['limit'], PDO::PARAM_INT);
            $stmt->bindValue('offset', (int) $filters['offset'], PDO::PARAM_INT);
        }

        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function countAllActive(array $filters = []): int
    {
        $where = [];
        $where[] = "m.status IN ('open', 'full')";
        $where[] = "CONCAT(m.date, ' ', m.time) >= NOW()";
        $params = [];

        $sessionUsername = $filters['username'] ?? null;

        // Visibility control
        $isAdmin = (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'super_admin') ? 1 : 0;
        if ($sessionUsername) {
            $where[] = "(m.visibility = 'public' 
                         OR m.host_username = :session_username_vis 
                         OR :is_admin = 1 
                         OR EXISTS (
                             SELECT 1 FROM friendships f 
                             WHERE f.status = 'accepted' 
                               AND (
                                 (f.sender_username = m.host_username AND f.recipient_username = :session_username_vis2)
                                 OR 
                                 (f.recipient_username = m.host_username AND f.sender_username = :session_username_vis3)
                               )
                         ))";
            $params['session_username_vis'] = $sessionUsername;
            $params['session_username_vis2'] = $sessionUsername;
            $params['session_username_vis3'] = $sessionUsername;
            $params['is_admin'] = $isAdmin;
        } else {
            $where[] = "m.visibility = 'public'";
        }

        if (!empty($filters['location'])) {
            $where[] = "m.location LIKE :location";
            $params['location'] = '%' . $filters['location'] . '%';
        }

        if (!empty($filters['date_from'])) {
            $where[] = "m.date >= :date_from";
            $params['date_from'] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $where[] = "m.date <= :date_to";
            $params['date_to'] = $filters['date_to'];
        }

        if (!empty($filters['format'])) {
            $fmt1 = $filters['format'];
            $fmt2 = str_replace('vs', 'v', $fmt1);
            $fmt3 = str_replace('v', 'vs', $fmt1);
            $where[] = "(m.format = :format_val1 OR m.format = :format_val2)";
            $params['format_val1'] = $fmt2;
            $params['format_val2'] = $fmt3;
        }

        if (!empty($filters['only_friends']) && $sessionUsername) {
            $friends = $this->getFriendUsernames($sessionUsername);
            if (!empty($friends)) {
                $placeholders = [];
                foreach ($friends as $idx => $friend) {
                    $key = 'friend_toggle_' . $idx;
                    $placeholders[] = ':' . $key;
                    $params[$key] = $friend;
                }
                $where[] = "m.host_username IN (" . implode(', ', $placeholders) . ")";
            } else {
                $where[] = "1 = 0";
            }
        }

        if (!empty($filters['filter'])) {
            if ($filters['filter'] === 'friends' && $sessionUsername) {
                $friends = $this->getFriendUsernames($sessionUsername);
                if (!empty($friends)) {
                    $placeholders = [];
                    foreach ($friends as $idx => $friend) {
                        $key = 'friend_' . $idx;
                        $placeholders[] = ':' . $key;
                        $params[$key] = $friend;
                    }
                    $where[] = "m.host_username IN (" . implode(', ', $placeholders) . ")";
                } else {
                    $where[] = "1 = 0";
                }
            } elseif ($filters['filter'] === 'mine' && $sessionUsername) {
                $where[] = "(m.host_username = :my_username OR EXISTS (SELECT 1 FROM registrations r WHERE r.match_id = m.id AND r.username = :my_username2 AND r.status IN ('registered', 'waitlist')))";
                $params['my_username'] = $sessionUsername;
                $params['my_username2'] = $sessionUsername;
            }
        }

        if (!empty($filters['exclude_my_matches']) && $sessionUsername) {
            $where[] = "m.host_username != :exclude_username 
                        AND NOT EXISTS (
                            SELECT 1 FROM registrations r 
                            WHERE r.match_id = m.id 
                              AND r.username = :exclude_username2 
                              AND r.status IN ('registered', 'waitlist')
                        )";
            $params['exclude_username'] = $sessionUsername;
            $params['exclude_username2'] = $sessionUsername;
        }

        $whereSql = '';
        if (!empty($where)) {
            $whereSql = 'WHERE ' . implode(' AND ', $where);
        }

        $sql = "
            SELECT COUNT(*) 
            FROM matches m 
            JOIN users u ON m.host_username = u.username 
            $whereSql
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function getFriendUsernames(string $username): array
    {
        $stmt = $this->db->prepare("
            SELECT DISTINCT CASE 
                WHEN sender_username = :username1 THEN recipient_username 
                ELSE sender_username 
            END as friend_username
            FROM friendships 
            WHERE (sender_username = :username2 OR recipient_username = :username3) 
              AND status = 'accepted'
        ");
        $stmt->execute([
            'username1' => $username,
            'username2' => $username,
            'username3' => $username
        ]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
    }

    public function create(array $data): bool
    {
        try {
            $this->db->beginTransaction();

            $sql = "INSERT INTO matches (host_username, date, time, format, max_players, location, latitude, longitude, visibility, total_cost, status, created_at, updated_at) 
                    VALUES (:host_username, :date, :time, :format, :max_players, :location, :latitude, :longitude, :visibility, :total_cost, :status, NOW(), NOW())";

            $stmt = $this->db->prepare($sql);
            $success = $stmt->execute([
                'host_username' => $data['host_username'],
                'date' => $data['date'],
                'time' => $data['time'],
                'format' => $data['format'],
                'max_players' => $data['max_players'],
                'location' => $data['location'],
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'visibility' => $data['visibility'],
                'total_cost' => $data['total_cost'],
                'status' => $data['status']
            ]);

            if (!$success) {
                $this->db->rollBack();
                return false;
            }

            $matchId = $this->db->lastInsertId();

            // Registra automaticamente l'host come giocatore
            $sqlReg = "INSERT INTO registrations (match_id, username, status, created_at, updated_at) 
                       VALUES (:match_id, :username, 'registered', NOW(), NOW())";
            $stmtReg = $this->db->prepare($sqlReg);
            $successReg = $stmtReg->execute([
                'match_id' => $matchId,
                'username' => $data['host_username']
            ]);

            if (!$successReg) {
                $this->db->rollBack();
                return false;
            }

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return false;
        }
    }

    public function getMatchesToReport(string $username): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM matches 
            WHERE host_username = :username 
              AND status = 'finished' 
              AND (result_home IS NULL OR result_away IS NULL)
            ORDER BY date DESC, time DESC
        ");
        $stmt->execute(['username' => $username]);
        return $stmt->fetchAll() ?: [];
    }

    public function getMatchesToVote(string $username): array
    {
        $stmt = $this->db->prepare("
            SELECT m.* FROM matches m
            JOIN registrations r ON m.id = r.match_id
            WHERE r.username = :username1
              AND r.status = 'registered'
              AND m.status = 'finished'
              AND m.result_home IS NOT NULL
              AND m.result_away IS NOT NULL
              AND m.mvp_assigned = 0
              AND NOT EXISTS (
                  SELECT 1 FROM evaluations e 
                  WHERE e.match_id = m.id 
                    AND e.evaluator_username = :username2
              )
            ORDER BY m.date DESC, m.time DESC
        ");
        $stmt->execute([
            'username1' => $username,
            'username2' => $username
        ]);
        return $stmt->fetchAll() ?: [];
    }
}
