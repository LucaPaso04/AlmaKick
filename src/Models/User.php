<?php

namespace App\Models;

use App\Database;
use PDO;

class User {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function find(string $username): ?array {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function findByEmail(string $email): ?array {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function create(array $data): bool {
        $sql = "INSERT INTO users (username, name, last_name, email, password, phone, friend_code, role, preferred_role, trust_score, skill_rating, mvp_count, matches_played, total_goals, is_banned, created_at, updated_at) 
                VALUES (:username, :name, :last_name, :email, :password, :phone, :friend_code, :role, 'midfielder', 100, 6.0, 0, 0, 0, 0, NOW(), NOW())";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'username' => $data['username'],
            'name' => $data['name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'phone' => $data['phone'],
            'friend_code' => $data['friend_code'],
            'role' => $data['role'] ?? 'user'
        ]);
    }
}
