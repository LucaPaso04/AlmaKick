<?php

namespace App\Models;

use App\Database;
use PDO;

class SoccerMatch {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function find(int $id): ?array {
        $stmt = $this->db->prepare("
            SELECT m.*, u.name as host_name 
            FROM matches m 
            JOIN users u ON m.host_id = u.id 
            WHERE m.id = :id
        ");
        $stmt->execute(['id' => $id]);
        $match = $stmt->fetch();
        return $match ?: null;
    }

    public function getAllActive(array $filters = []): array {
        $currentUserId = $filters['user_id'] ?? 0;
        $sql = "
            SELECT m.*, u.name as host_name,
                   (SELECT COUNT(*) FROM registrations r WHERE r.match_id = m.id AND r.status = 'registered') as posti_occupati,
                   (SELECT status FROM registrations r WHERE r.match_id = m.id AND r.user_id = :current_user_id LIMIT 1) as user_registration_status
            FROM matches m 
            JOIN users u ON m.host_id = u.id 
            WHERE 1=1
        ";
        $params = [
            'current_user_id' => $currentUserId
        ];

        if (!empty($filters['location'])) {
            $sql .= " AND m.location LIKE :location";
            $params['location'] = '%' . $filters['location'] . '%';
        }

        if (!empty($filters['date'])) {
            $sql .= " AND m.date = :date";
            $params['date'] = $filters['date'];
        }

        if (!empty($filters['format'])) {
            // Support both formats 5vs5 and 5v5 (DB has 5v5, 7v7, 8v8)
            $format = $filters['format'];
            if ($format === '5vs5') $format = '5v5';
            if ($format === '7vs7') $format = '7v7';
            if ($format === '8v8') $format = '8v8';
            if ($format === '11vs11') $format = '11v11';

            $sql .= " AND m.format = :format";
            $params['format'] = $format;
        }

        if (!empty($filters['filter'])) {
            if ($filters['filter'] === 'mine' && isset($filters['user_id'])) {
                $sql .= " AND m.host_id = :user_id";
                $params['user_id'] = $filters['user_id'];
            } elseif ($filters['filter'] === 'friends' && isset($filters['user_id'])) {
                $sql .= " AND m.host_id IN (
                    SELECT DISTINCT IF(sender_id = :user_id1, recipient_id, sender_id)
                    FROM friendships
                    WHERE (sender_id = :user_id2 OR recipient_id = :user_id3) AND status = 'accepted'
                )";
                $params['user_id1'] = $filters['user_id'];
                $params['user_id2'] = $filters['user_id'];
                $params['user_id3'] = $filters['user_id'];
            }
        }

        if (!empty($filters['hide_full'])) {
            $sql .= " AND m.status = 'open'";
        }

        $sql .= " ORDER BY m.date ASC, m.time ASC";

        if (isset($filters['limit']) && isset($filters['offset'])) {
            $sql .= " LIMIT " . (int)$filters['limit'] . " OFFSET " . (int)$filters['offset'];
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function countAllActive(array $filters = []): int {
        $sql = "
            SELECT COUNT(*) 
            FROM matches m 
            JOIN users u ON m.host_id = u.id 
            WHERE 1=1
        ";
        $params = [];

        if (!empty($filters['location'])) {
            $sql .= " AND m.location LIKE :location";
            $params['location'] = '%' . $filters['location'] . '%';
        }

        if (!empty($filters['date'])) {
            $sql .= " AND m.date = :date";
            $params['date'] = $filters['date'];
        }

        if (!empty($filters['format'])) {
            $format = $filters['format'];
            if ($format === '5vs5') $format = '5v5';
            if ($format === '7vs7') $format = '7v7';
            if ($format === '8vs8') $format = '8v8';
            if ($format === '11vs11') $format = '11v11';

            $sql .= " AND m.format = :format";
            $params['format'] = $format;
        }

        if (!empty($filters['filter'])) {
            if ($filters['filter'] === 'mine' && isset($filters['user_id'])) {
                $sql .= " AND m.host_id = :user_id";
                $params['user_id'] = $filters['user_id'];
            } elseif ($filters['filter'] === 'friends' && isset($filters['user_id'])) {
                $sql .= " AND m.host_id IN (
                    SELECT DISTINCT IF(sender_id = :user_id1, recipient_id, sender_id)
                    FROM friendships
                    WHERE (sender_id = :user_id2 OR recipient_id = :user_id3) AND status = 'accepted'
                )";
                $params['user_id1'] = $filters['user_id'];
                $params['user_id2'] = $filters['user_id'];
                $params['user_id3'] = $filters['user_id'];
            }
        }

        if (!empty($filters['hide_full'])) {
            $sql .= " AND m.status = 'open'";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public function getFriendIds(int $userId): array {
        $stmt = $this->db->prepare("
            SELECT DISTINCT IF(sender_id = :user_id1, recipient_id, sender_id) AS friend_id
            FROM friendships
            WHERE (sender_id = :user_id2 OR recipient_id = :user_id3) AND status = 'accepted'
        ");
        $stmt->execute([
            'user_id1' => $userId,
            'user_id2' => $userId,
            'user_id3' => $userId
        ]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function create(array $data): bool {
        $sql = "INSERT INTO matches (host_id, date, time, format, max_players, location, visibility, total_cost, status, created_at, updated_at) 
                VALUES (:host_id, :date, :time, :format, :max_players, :location, :visibility, :total_cost, :status, NOW(), NOW())";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'host_id' => $data['host_id'],
            'date' => $data['date'],
            'time' => $data['time'],
            'format' => $data['format'],
            'max_players' => $data['max_players'],
            'location' => $data['location'],
            'visibility' => $data['visibility'],
            'total_cost' => $data['total_cost'],
            'status' => $data['status']
        ]);
    }
}
