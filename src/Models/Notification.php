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
     * Crea una nuova notifica
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
     * Ritorna il conteggio delle notifiche non lette per un utente
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
     * Recupera le ultime notifiche per un utente
     */
    public function getLatest(string $username, int $limit = 5): array {
        // Auto-pulizia: Elimina le notifiche già lette più vecchie di 7 giorni
        try {
            $stmtPurge = $this->db->prepare("
                DELETE FROM notifications 
                WHERE user_recipient = :username 
                  AND is_read = 1 
                  AND created_at < DATE_SUB(NOW(), INTERVAL 7 DAY)
            ");
            $stmtPurge->execute(['username' => $username]);
        } catch (\PDOException $e) {
            // Fallback silenzioso
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
     * Segna una specifica notifica come letta
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
     * Segna tutte le notifiche di un utente come lette
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
     * Elimina fisicamente una singola notifica
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
     * Elimina fisicamente tutte le notifiche di un utente (svuota tutto)
     */
    public function clearAll(string $username): bool {
        $stmt = $this->db->prepare("
            DELETE FROM notifications 
            WHERE user_recipient = :username
        ");
        return $stmt->execute(['username' => $username]);
    }

    /**
     * Segna come lette le notifiche di richiesta amicizia tra due utenti
     * (Usato quando la richiesta viene risolta con accetta/rifiuta)
     */
    public function markFriendRequestAsRead(string $sender, string $recipient): bool {
        $stmt = $this->db->prepare("
            UPDATE notifications 
            SET is_read = 1 
            WHERE user_recipient = :recipient 
              AND type = 'friend_request' 
              AND message LIKE :sender_pattern
        ");
        // Cerchiamo notifiche contenenti lo username del mittente per assicurarci di colpire la notifica giusta
        return $stmt->execute([
            'recipient' => $recipient,
            'sender_pattern' => '%' . $sender . '%'
        ]);
    }
}
