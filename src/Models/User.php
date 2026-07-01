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

    public function findByLoginIdentifier(string $identifier): ?array {
        $identifier = trim($identifier);

        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            return $this->findByEmail($identifier);
        }

        $stmt = $this->db->prepare("SELECT * FROM users WHERE LOWER(username) = LOWER(:username)");
        $stmt->execute(['username' => $identifier]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function createWithRole(array $data): bool {
        $sql = "INSERT INTO users (username, name, last_name, email, password, phone, friend_code, role, preferred_role, trust_score, skill_rating, mvp_count, matches_played, total_goals, is_banned, created_at, updated_at) 
                VALUES (:username, :name, :last_name, :email, :password, :phone, :friend_code, :role, :preferred_role, 100, 0.00, 0, 0, 0, 0, NOW(), NOW())";
        
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
        $stmt = $this->db->prepare("UPDATE users SET name = :name, last_name = :last_name, phone = :phone, preferred_role = :preferred_role, updated_at = NOW() WHERE username = :username");
        return $stmt->execute([
            'name' => $data['name'],
            'last_name' => $data['last_name'],
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

    public function getRecentVotesTrend(string $username): array {
        $stmt = $this->db->prepare("
            SELECT e.match_id, AVG(e.skill_vote) as avg_vote
            FROM evaluations e
            JOIN matches m ON e.match_id = m.id
            WHERE e.evaluated_username = :username AND e.skill_vote IS NOT NULL
            GROUP BY e.match_id, m.date, m.time
            ORDER BY m.date DESC, m.time DESC
            LIMIT 5
        ");
        $stmt->execute(['username' => $username]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        
        return array_reverse(array_map(function($row) {
            return (float)$row['avg_vote'];
        }, $results));
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

    public function getSentPendingRequests(string $username): array {
        $stmt = $this->db->prepare("
            SELECT u.* 
            FROM friendships f 
            JOIN users u ON f.recipient_username = u.username 
            WHERE f.sender_username = :username AND f.status = 'pending'
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
        $existing = $this->getFriendshipStatus($sender, $recipient);
        if ($existing) {
            $stmt = $this->db->prepare("
                UPDATE friendships 
                SET status = 'pending', sender_username = :sender, recipient_username = :recipient, created_at = NOW()
                WHERE sender_username = :old_sender AND recipient_username = :old_recipient
            ");
            return $stmt->execute([
                'sender' => $sender,
                'recipient' => $recipient,
                'old_sender' => $existing['sender_username'],
                'old_recipient' => $existing['recipient_username']
            ]);
        } else {
            $stmt = $this->db->prepare("
                INSERT INTO friendships (sender_username, recipient_username, status, created_at)
                VALUES (:sender, :recipient, 'pending', NOW())
            ");
            return $stmt->execute([
                'sender' => $sender,
                'recipient' => $recipient
            ]);
        }
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
        $existing = $this->getFriendshipStatus($user1, $user2);
        if ($existing) {
            $stmt = $this->db->prepare("
                UPDATE friendships 
                SET status = 'blocked', sender_username = :user1, recipient_username = :user2, created_at = NOW()
                WHERE sender_username = :old_sender AND recipient_username = :old_recipient
            ");
            return $stmt->execute([
                'user1' => $user1,
                'user2' => $user2,
                'old_sender' => $existing['sender_username'],
                'old_recipient' => $existing['recipient_username']
            ]);
        } else {
            $stmt = $this->db->prepare("
                INSERT INTO friendships (sender_username, recipient_username, status, created_at)
                VALUES (:u1, :u2, 'blocked', NOW())
            ");
            return $stmt->execute([
                'u1' => $user1,
                'u2' => $user2
            ]);
        }
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



    public function getFriendsCount(string $username): int {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) 
            FROM friendships 
            WHERE (sender_username = :username1 OR recipient_username = :username2) 
              AND status = 'accepted'
        ");
        $stmt->execute([
            'username1' => $username,
            'username2' => $username
        ]);
        return (int)$stmt->fetchColumn();
    }

    public function getMutualFriends(string $user1, string $user2): array {
        $stmt = $this->db->prepare("
            SELECT u.*
            FROM users u
            WHERE u.is_banned = 0 
              AND u.username IN (
                  SELECT CASE WHEN sender_username = :u1 THEN recipient_username ELSE sender_username END
                  FROM friendships
                  WHERE (sender_username = :u1_alt OR recipient_username = :u1_alt2) AND status = 'accepted'
              ) 
              AND u.username IN (
                  SELECT CASE WHEN sender_username = :u2 THEN recipient_username ELSE sender_username END
                  FROM friendships
                  WHERE (sender_username = :u2_alt OR recipient_username = :u2_alt2) AND status = 'accepted'
              )
            ORDER BY u.name ASC, u.last_name ASC
        ");
        $stmt->execute([
            'u1' => $user1,
            'u1_alt' => $user1,
            'u1_alt2' => $user1,
            'u2' => $user2,
            'u2_alt' => $user2,
            'u2_alt2' => $user2
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getMatchesPlayedTogetherCount(string $user1, string $user2): int {
        $stmt = $this->db->prepare("
            SELECT COUNT(DISTINCT r1.match_id)
            FROM registrations r1
            JOIN registrations r2 ON r1.match_id = r2.match_id
            JOIN matches m ON r1.match_id = m.id
            WHERE r1.username = :u1
              AND r2.username = :u2
              AND r1.status = 'registered'
              AND r2.status = 'registered'
              AND m.status = 'finished'
        ");
        $stmt->execute([
            'u1' => $user1,
            'u2' => $user2
        ]);
        return (int)$stmt->fetchColumn();
    }
}


