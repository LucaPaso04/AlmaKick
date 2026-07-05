<?php

namespace App\Models;

use App\Database;
use PDO;

class Notification {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

     /**
      * Create a new notification
      */
    public function create(array $data): bool {
        $stmt = $this->db->prepare("
            INSERT INTO notifications (user_recipient, type, message, link, is_read, created_at)
            VALUES (:user_recipient, :type, :message, :link, 0, NOW())
        ");
        return $stmt->execute([
            'user_recipient' => $data['user_recipient'],
            'type' => $data['type'],
            'message' => $data['message'],
            'link' => $data['link'] ?? null
        ]);
    }

     /**
      * Get unread notifications count
      */
    public function getUnreadCount(string $username): int {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM notifications 
            WHERE user_recipient = :username AND is_read = 0
        ");
        $stmt->execute(['username' => $username]);
        return (int)$stmt->fetchColumn();
    }

     /**
      * Get latest notifications
      */
    public function getLatest(string $username, int $limit = 5): array {
        // Auto-purge read notifications older than 7 days
        try {
            $stmtPurge = $this->db->prepare("
                DELETE FROM notifications 
                WHERE user_recipient = :username 
                  AND is_read = 1 
                  AND created_at < DATE_SUB(NOW(), INTERVAL 7 DAY)
            ");
            $stmtPurge->execute(['username' => $username]);
        } catch (\PDOException $e) {
            // Silent fallback
        }

        $stmt = $this->db->prepare("
            SELECT * FROM notifications 
            WHERE user_recipient = :username 
            ORDER BY created_at DESC 
            LIMIT :limit
        ");
        $stmt->bindValue(':username', $username, PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

     /**
      * Mark notification as read
      */
    public function markAsRead(int $id, string $username): bool {
        $stmt = $this->db->prepare("
            UPDATE notifications 
            SET is_read = 1 
            WHERE id = :id AND user_recipient = :username
        ");
        return $stmt->execute([
            'id' => $id,
            'username' => $username
        ]);
    }

     /**
      * Mark all notifications as read
      */
    public function markAllAsRead(string $username): bool {
        $stmt = $this->db->prepare("
            UPDATE notifications 
            SET is_read = 1 
            WHERE user_recipient = :username AND is_read = 0
        ");
        return $stmt->execute(['username' => $username]);
    }

     /**
      * Delete notification
      */
    public function delete(int $id, string $username): bool {
        $stmt = $this->db->prepare("
            DELETE FROM notifications 
            WHERE id = :id AND user_recipient = :username
        ");
        return $stmt->execute([
            'id' => $id,
            'username' => $username
        ]);
    }

     /**
      * Clear all notifications
      */
    public function clearAll(string $username): bool {
        $stmt = $this->db->prepare("
            DELETE FROM notifications 
            WHERE user_recipient = :username
        ");
        return $stmt->execute(['username' => $username]);
    }

     /**
      * Mark friend request notification as read
      */
    public function markFriendRequestAsRead(string $sender, string $recipient): bool {
        $stmt = $this->db->prepare("
            UPDATE notifications 
            SET is_read = 1 
            WHERE user_recipient = :recipient 
              AND type = 'friend_request' 
              AND message LIKE :sender_pattern
        ");
        // Match sender's username
        return $stmt->execute([
            'recipient' => $recipient,
            'sender_pattern' => '%' . $sender . '%'
        ]);
    }
}
