<?php

namespace App\Controllers;

use App\Models\Notification;

class NotificationController extends BaseController {
    private Notification $notificationModel;

    public function __construct() {
        $this->notificationModel = new Notification();
    }

    /**
     * Ritorna le ultime notifiche e il numero di non lette in JSON
     */
    public function getLatest() {
        header('Content-Type: application/json');
        
        $username = $_SESSION['user']['username'] ?? null;
        if (!$username) {
            http_response_code(401);
            echo json_encode(['error' => 'Non autorizzato']);
            return;
        }

        $unreadCount = $this->notificationModel->getUnreadCount($username);
        $notifications = $this->notificationModel->getLatest($username, 10);

        // Formatta la data per renderla più leggibile
        foreach ($notifications as &$n) {
            $n['time_ago'] = $this->timeAgo($n['created_at']);
        }
        unset($n);

        echo json_encode([
            'unread_count' => $unreadCount,
            'notifications' => $notifications
        ]);
    }

    /**
     * Segna una notifica come letta
     */
    public function markAsRead($id) {
        $this->validateCsrf();
        header('Content-Type: application/json');

        $username = $_SESSION['user']['username'] ?? null;
        if (!$username) {
            http_response_code(401);
            echo json_encode(['error' => 'Non autorizzato']);
            return;
        }

        $success = $this->notificationModel->markAsRead((int)$id, $username);
        
        echo json_encode(['success' => $success]);
    }

    /**
     * Segna tutte le notifiche come lette
     */
    public function markAllAsRead() {
        $this->validateCsrf();
        header('Content-Type: application/json');

        $username = $_SESSION['user']['username'] ?? null;
        if (!$username) {
            http_response_code(401);
            echo json_encode(['error' => 'Non autorizzato']);
            return;
        }

        $success = $this->notificationModel->markAllAsRead($username);

        echo json_encode(['success' => $success]);
    }

    /**
     * Funzione di utility per calcolare il tempo trascorso (es. "5 minuti fa")
     */
    private function timeAgo($datetime) {
        $timestamp = strtotime($datetime);
        $difference = time() - $timestamp;

        if ($difference < 60) {
            return "Adesso";
        } elseif ($difference < 3600) {
            $mins = round($difference / 60);
            return $mins == 1 ? "1 minuto fa" : "$mins minuti fa";
        } elseif ($difference < 86400) {
            $hours = round($difference / 3600);
            return $hours == 1 ? "1 ora fa" : "$hours ore fa";
        } else {
            $days = round($difference / 86400);
            return $days == 1 ? "Ieri" : "$days giorni fa";
        }
    }
}
