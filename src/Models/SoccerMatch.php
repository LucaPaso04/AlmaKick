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

    public function getAllActive(): array {
        // Ritorna le partite future o aperte
        $stmt = $this->db->query("
            SELECT m.*, u.name as host_name 
            FROM matches m 
            JOIN users u ON m.host_id = u.id 
            ORDER BY m.date ASC, m.time ASC
        ");
        return $stmt->fetchAll();
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
