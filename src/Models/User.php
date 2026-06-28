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

    public function createWithRole(array $data): bool {
        $sql = "INSERT INTO users (username, name, last_name, email, password, phone, friend_code, role, preferred_role, trust_score, skill_rating, mvp_count, matches_played, total_goals, is_banned, created_at, updated_at) 
                VALUES (:username, :name, :last_name, :email, :password, :phone, :friend_code, :role, :preferred_role, 100, 6.0, 0, 0, 0, 0, NOW(), NOW())";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'username' => $data['username'],
            'name' => $data['name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'phone' => $data['phone'],
            'friend_code' => $data['friend_code'],
            'role' => $data['role'] ?? 'user',
            'preferred_role' => $data['preferred_role'] ?? 'Jolly'
        ]);
    }

    public function findByFriendCode(string $friendCode): ?array {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE friend_code = :friend_code");
        $stmt->execute(['friend_code' => $friendCode]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function updateAvatar(string $username, string $avatarPath): bool {
        $stmt = $this->db->prepare("UPDATE users SET avatar = :avatar, updated_at = NOW() WHERE username = :username");
        return $stmt->execute(['avatar' => $avatarPath, 'username' => $username]);
    }

    public function updateInfo(string $username, array $data): bool {
        $stmt = $this->db->prepare("UPDATE users SET name = :name, phone = :phone, preferred_role = :preferred_role, updated_at = NOW() WHERE username = :username");
        return $stmt->execute([
            'name' => $data['name'],
            'phone' => $data['phone'],
            'preferred_role' => $data['preferred_role'],
            'username' => $username
        ]);
    }

    public function updateEmail(string $username, string $email): bool {
        $stmt = $this->db->prepare("UPDATE users SET email = :email, updated_at = NOW() WHERE username = :username");
        return $stmt->execute(['email' => $email, 'username' => $username]);
    }

    public function updatePassword(string $username, string $passwordHash): bool {
        $stmt = $this->db->prepare("UPDATE users SET password = :password, updated_at = NOW() WHERE username = :username");
        return $stmt->execute(['password' => $passwordHash, 'username' => $username]);
    }

    public function getMatchHistory(string $username): array {
        $stmt = $this->db->prepare("
            SELECT r.*, m.date, m.time, m.location, m.format, m.result_home, m.result_away, m.id as match_id
            FROM registrations r
            JOIN matches m ON r.match_id = m.id
            WHERE r.username = :username 
              AND r.status = 'registered' 
              AND m.status = 'finished'
            ORDER BY m.date DESC, m.time DESC
        ");
        $stmt->execute(['username' => $username]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getMatchesHostedCount(string $username): int {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM matches WHERE host_username = :username AND status != 'cancelled'");
        $stmt->execute(['username' => $username]);
        return (int)$stmt->fetchColumn();
    }

    public function getPendingRequests(string $username): array {
        $stmt = $this->db->prepare("
            SELECT u.* 
            FROM friendships f 
            JOIN users u ON f.sender_username = u.username 
            WHERE f.recipient_username = :username AND f.status = 'pending'
            ORDER BY f.created_at DESC
        ");
        $stmt->execute(['username' => $username]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getFriends(string $username): array {
        $stmt = $this->db->prepare("
            SELECT u.* 
            FROM users u
            JOIN friendships f ON (
                (f.sender_username = :username1 AND f.recipient_username = u.username)
                OR (f.recipient_username = :username2 AND f.sender_username = u.username)
            )
            WHERE f.status = 'accepted'
            ORDER BY u.name ASC
        ");
        $stmt->execute([
            'username1' => $username,
            'username2' => $username
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getFriendshipStatus(string $user1, string $user2): ?array {
        $stmt = $this->db->prepare("
            SELECT * FROM friendships 
            WHERE (sender_username = :u1 AND recipient_username = :u2)
               OR (sender_username = :u3 AND recipient_username = :u4)
        ");
        $stmt->execute([
            'u1' => $user1, 'u2' => $user2,
            'u3' => $user2, 'u4' => $user1
        ]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ?: null;
    }

    public function addFriendRequest(string $sender, string $recipient): bool {
        $stmt = $this->db->prepare("
            INSERT INTO friendships (sender_username, recipient_username, status, created_at)
            VALUES (:sender, :recipient, 'pending', NOW())
            ON DUPLICATE KEY UPDATE status = 'pending', sender_username = :sender2, recipient_username = :recipient2, created_at = NOW()
        ");
        return $stmt->execute([
            'sender' => $sender,
            'recipient' => $recipient,
            'sender2' => $sender,
            'recipient2' => $recipient
        ]);
    }

    public function acceptFriendRequest(string $sender, string $recipient): bool {
        $stmt = $this->db->prepare("
            UPDATE friendships SET status = 'accepted', created_at = NOW()
            WHERE sender_username = :sender AND recipient_username = :recipient
        ");
        return $stmt->execute(['sender' => $sender, 'recipient' => $recipient]);
    }

    public function deleteFriendship(string $user1, string $user2): bool {
        $stmt = $this->db->prepare("
            DELETE FROM friendships 
            WHERE (sender_username = :u1 AND recipient_username = :u2)
               OR (sender_username = :u3 AND recipient_username = :u4)
        ");
        return $stmt->execute([
            'u1' => $user1, 'u2' => $user2,
            'u3' => $user2, 'u4' => $user1
        ]);
    }

    public function blockUser(string $user1, string $user2): bool {
        $stmt = $this->db->prepare("
            INSERT INTO friendships (sender_username, recipient_username, status, created_at)
            VALUES (:u1, :u2, 'blocked', NOW())
            ON DUPLICATE KEY UPDATE status = 'blocked', sender_username = :u3, recipient_username = :u4, created_at = NOW()
        ");
        return $stmt->execute([
            'u1' => $user1, 'u2' => $user2,
            'u3' => $user1, 'u4' => $user2
        ]);
    }

    public function getTopScorers(int $limit = 10): array {
        $stmt = $this->db->prepare("
            SELECT * FROM users
            WHERE is_banned = 0 AND total_goals > 0
            ORDER BY total_goals DESC, name ASC, last_name ASC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getTopMVPs(int $limit = 10): array {
        $stmt = $this->db->prepare("
            SELECT * FROM users
            WHERE is_banned = 0 AND mvp_count > 0
            ORDER BY mvp_count DESC, name ASC, last_name ASC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getTopRated(int $limit = 10): array {
        $stmt = $this->db->prepare("
            SELECT * FROM users
            WHERE is_banned = 0 AND matches_played >= 3
            ORDER BY skill_rating DESC, name ASC, last_name ASC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getFriendsScorers(string $username): array {
        $stmt = $this->db->prepare("
            SELECT u.* FROM users u
            WHERE u.is_banned = 0 AND u.total_goals > 0 AND (u.username = :my_username OR u.username IN (
                SELECT CASE 
                    WHEN sender_username = :my_username1 THEN recipient_username
                    ELSE sender_username
                END
                FROM friendships
                WHERE (sender_username = :my_username2 OR recipient_username = :my_username3)
                  AND status = 'accepted'
            ))
            ORDER BY u.total_goals DESC, u.name ASC, u.last_name ASC
        ");
        $stmt->execute([
            'my_username' => $username,
            'my_username1' => $username,
            'my_username2' => $username,
            'my_username3' => $username
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getFriendsMVPs(string $username): array {
        $stmt = $this->db->prepare("
            SELECT u.* FROM users u
            WHERE u.is_banned = 0 AND u.mvp_count > 0 AND (u.username = :my_username OR u.username IN (
                SELECT CASE 
                    WHEN sender_username = :my_username1 THEN recipient_username
                    ELSE sender_username
                END
                FROM friendships
                WHERE (sender_username = :my_username2 OR recipient_username = :my_username3)
                  AND status = 'accepted'
            ))
            ORDER BY u.mvp_count DESC, u.name ASC, u.last_name ASC
        ");
        $stmt->execute([
            'my_username' => $username,
            'my_username1' => $username,
            'my_username2' => $username,
            'my_username3' => $username
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getFriendsRated(string $username): array {
        $stmt = $this->db->prepare("
            SELECT u.* FROM users u
            WHERE u.is_banned = 0 AND u.matches_played >= 3 AND (u.username = :my_username OR u.username IN (
                SELECT CASE 
                    WHEN sender_username = :my_username1 THEN recipient_username
                    ELSE sender_username
                END
                FROM friendships
                WHERE (sender_username = :my_username2 OR recipient_username = :my_username3)
                  AND status = 'accepted'
            ))
            ORDER BY u.skill_rating DESC, u.name ASC, u.last_name ASC
        ");
        $stmt->execute([
            'my_username' => $username,
            'my_username1' => $username,
            'my_username2' => $username,
            'my_username3' => $username
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function searchUsers(string $query, string $currentUsername, int $limit, int $offset): array {
        $search = '%' . $query . '%';
        $stmt = $this->db->prepare("
            SELECT * FROM users
            WHERE is_banned = 0 
              AND username != :current_username
              AND (name LIKE :q1 OR last_name LIKE :q2 OR username LIKE :q3)
            ORDER BY name ASC, last_name ASC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':current_username', $currentUsername, PDO::PARAM_STR);
        $stmt->bindValue(':q1', $search, PDO::PARAM_STR);
        $stmt->bindValue(':q2', $search, PDO::PARAM_STR);
        $stmt->bindValue(':q3', $search, PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function countSearchUsers(string $query, string $currentUsername): int {
        $search = '%' . $query . '%';
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM users
            WHERE is_banned = 0 
              AND username != :current_username
              AND (name LIKE :q1 OR last_name LIKE :q2 OR username LIKE :q3)
        ");
        $stmt->bindValue(':current_username', $currentUsername, PDO::PARAM_STR);
        $stmt->bindValue(':q1', $search, PDO::PARAM_STR);
        $stmt->bindValue(':q2', $search, PDO::PARAM_STR);
        $stmt->bindValue(':q3', $search, PDO::PARAM_STR);
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }
}

