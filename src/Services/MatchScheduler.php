<?php

namespace App\Services;

use App\Database;
use App\Models\Notification;
use App\Models\SoccerMatch;
use PDO;
use Exception;

class MatchScheduler
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Esegue tutti i controlli automatici di manutenzione sulle partite.
     */
    public function runAllMaintenance(): void
    {
        $this->resolveExpiredMvps();
        $this->autoCancelExpiredMatches();
        $this->resolveExpiredWaitlistOffers();
        $this->autoFinishPastMatches();
        $this->autoCloseUnreportedMatches();
    }

    public function resolveExpiredMvps(): void
    {
        // Trova tutte le partite finite, con scadenza votazione passata e MVP non ancora assegnato
        $stmt = $this->db->prepare("
            SELECT id, host_username 
            FROM matches 
            WHERE status = 'finished' 
              AND mvp_assigned = 0 
              AND mvp_deadline IS NOT NULL 
              AND mvp_deadline <= NOW()
        ");
        $stmt->execute();
        $expiredMatches = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($expiredMatches)) {
            return;
        }

        foreach ($expiredMatches as $match) {
            $matchId = $match['id'];

            // Trova l'utente con la valutazione media più alta (skill_vote) tra i partecipanti di questo match
            $stmtMvp = $this->db->prepare("
                SELECT evaluated_username, AVG(skill_vote) as avg_vote, COUNT(skill_vote) as vote_count
                FROM evaluations
                WHERE match_id = :match_id 
                  AND skill_vote IS NOT NULL
                GROUP BY evaluated_username
                ORDER BY avg_vote DESC, vote_count DESC, evaluated_username ASC
                LIMIT 1
            ");
            $stmtMvp->execute(['match_id' => $matchId]);
            $mvpResult = $stmtMvp->fetch(PDO::FETCH_ASSOC);

            if ($mvpResult) {
                $mvpUsername = $mvpResult['evaluated_username'];

                try {
                    $this->db->beginTransaction();

                    // Aggiorna il match con l'MVP assegnato
                    $stmtUpdateMatch = $this->db->prepare("
                        UPDATE matches 
                        SET mvp_assigned = 1, mvp_username = :username, updated_at = NOW() 
                        WHERE id = :id
                    ");
                    $stmtUpdateMatch->execute([
                        'username' => $mvpUsername,
                        'id' => $matchId
                    ]);

                    // Incrementa il conteggio MVP dell'utente
                    $stmtUpdateUser = $this->db->prepare("
                        UPDATE users 
                        SET mvp_count = mvp_count + 1, updated_at = NOW() 
                        WHERE username = :username
                    ");
                    $stmtUpdateUser->execute(['username' => $mvpUsername]);

                    $this->db->commit();
                } catch (Exception $e) {
                    if ($this->db->inTransaction()) {
                        $this->db->rollBack();
                    }
                }
            } else {
                // Se nessuno ha ricevuto voti, assegniamo comunque mvp_assigned = 1 per evitare di ripetere l'operazione
                $this->db->prepare("
                    UPDATE matches 
                    SET mvp_assigned = 1, updated_at = NOW() 
                    WHERE id = :id
                ")->execute(['id' => $matchId]);
            }
        }
    }

    public function autoCancelExpiredMatches(): void
    {
        // Trova tutte le partite 'open' che iniziano tra NOW() e NOW() + 2 ore (selezioniamo anche data e luogo)
        $stmt = $this->db->prepare("
            SELECT m.id, m.max_players, m.format, m.location, m.date
            FROM matches m
            WHERE m.status = 'open'
              AND CONCAT(m.date, ' ', m.time) <= DATE_ADD(NOW(), INTERVAL 2 HOUR)
              AND CONCAT(m.date, ' ', m.time) > NOW()
        ");
        $stmt->execute();
        $matches = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($matches)) {
            return;
        }

        $formatMinPlayers = [
            '5v5' => 8,
            '5vs5' => 8,
            '7v7' => 12,
            '7vs7' => 12,
            '8v8' => 14,
            '8vs8' => 14,
            '11v11' => 20,
            '11vs11' => 20
        ];

        foreach ($matches as $match) {
            $matchId = $match['id'];

            // Conta i posti occupati in questo match (considerando gli ospiti)
            $stmtCount = $this->db->prepare("
                SELECT COALESCE(SUM(1 + has_guest), 0) 
                FROM registrations 
                WHERE match_id = :match_id 
                  AND status = 'registered'
            ");
            $stmtCount->execute(['match_id' => $matchId]);
            $occupied = (int)$stmtCount->fetchColumn();

            // Calcola la soglia minima richiesta
            $fmt = str_replace(' ', '', strtolower($match['format'] ?? ''));
            if (isset($formatMinPlayers[$fmt])) {
                $minPlayers = $formatMinPlayers[$fmt];
            } else {
                $minPlayers = (int)ceil((int)$match['max_players'] * 0.8);
            }

            // Se gli iscritti sono sotto la soglia minima, annulla la partita
            if ($occupied < $minPlayers) {
                try {
                    // Recupera gli utenti iscritti per notificarli prima dell'annullamento
                    $stmtGetPlayers = $this->db->prepare("SELECT username FROM registrations WHERE match_id = :match_id AND status = 'registered'");
                    $stmtGetPlayers->execute(['match_id' => $matchId]);
                    $playersToNotify = $stmtGetPlayers->fetchAll(PDO::FETCH_COLUMN);

                    $this->db->beginTransaction();

                    // Aggiorna lo stato del match
                    $stmtCancelMatch = $this->db->prepare("
                        UPDATE matches 
                        SET status = 'cancelled', 
                            cancellation_reason = 'Annullamento automatico: soglia minima di iscritti non raggiunta a 2 ore dall\'inizio.', 
                            updated_at = NOW() 
                        WHERE id = :id
                    ");
                    $stmtCancelMatch->execute(['id' => $matchId]);

                    // Annulla tutte le registrazioni
                    $stmtCancelRegs = $this->db->prepare("
                        UPDATE registrations 
                        SET status = 'cancelled', 
                            updated_at = NOW() 
                        WHERE match_id = :match_id
                    ");
                    $stmtCancelRegs->execute(['match_id' => $matchId]);

                    $this->db->commit();

                    // Invia notifiche
                    $notificationModel = new Notification();
                    foreach ($playersToNotify as $playerUsername) {
                        $notificationModel->create([
                            'user_recipient' => $playerUsername,
                            'type' => 'match_cancellation',
                            'message' => '⚠️ La partita a ' . $match['location'] . ' del ' . date('d/m/Y', strtotime($match['date'])) . ' è stata annullata automaticamente (numero iscritti insufficiente).',
                            'link' => url('/matches/' . $matchId)
                        ]);
                    }
                } catch (Exception $e) {
                    if ($this->db->inTransaction()) {
                        $this->db->rollBack();
                    }
                }
            }
        }
    }

    public function resolveExpiredWaitlistOffers(): void
    {
        // Trova offerte scadute
        $stmt = $this->db->prepare("
            SELECT r.*, m.location, m.date 
            FROM registrations r
            JOIN matches m ON r.match_id = m.id
            WHERE r.status = 'waitlist' 
              AND r.offer_expires_at IS NOT NULL 
              AND r.offer_expires_at <= NOW()
        ");
        $stmt->execute();
        $expired = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($expired)) {
            return;
        }

        $notificationModel = new Notification();

        foreach ($expired as $reg) {
            // Aggiorna a cancellato e rimuovi scadenza
            $this->db->prepare("
                UPDATE registrations 
                SET status = 'cancelled', offer_expires_at = NULL, updated_at = NOW() 
                WHERE id = :id
            ")->execute(['id' => $reg['id']]);

            // Invia notifica di scadenza offerta
            $notificationModel->create([
                'user_recipient' => $reg['username'],
                'type' => 'offer_expired',
                'message' => '⏰ L\'offerta per partecipare alla partita a ' . $reg['location'] . ' del ' . date('d/m/Y', strtotime($reg['date'])) . ' è scaduta.',
                'link' => url('/matches/' . $reg['match_id'])
            ]);

            // Promuovi o offri al prossimo panchinaro
            $this->promoteNextWaitlistPlayer($reg['match_id']);
        }
    }

    public function promoteNextWaitlistPlayer(int $matchId): void
    {
        $stmtMatch = $this->db->prepare("SELECT * FROM matches WHERE id = :id");
        $stmtMatch->execute(['id' => $matchId]);
        $match = $stmtMatch->fetch(PDO::FETCH_ASSOC);
        if (!$match) {
            return;
        }

        // Calcola posti occupati attivi
        $stmtCountReg = $this->db->prepare("
            SELECT COALESCE(SUM(1 + has_guest), 0) 
            FROM registrations 
            WHERE match_id = :match_id AND status = 'registered'
        ");
        $stmtCountReg->execute(['match_id' => $matchId]);
        $occupiedReg = (int)$stmtCountReg->fetchColumn();

        // Calcola posti riservati da offerte attive non scadute
        $stmtCountPending = $this->db->prepare("
            SELECT COALESCE(SUM(1 + has_guest), 0) 
            FROM registrations 
            WHERE match_id = :match_id 
              AND status = 'waitlist' 
              AND offer_expires_at IS NOT NULL 
              AND offer_expires_at > NOW()
        ");
        $stmtCountPending->execute(['match_id' => $matchId]);
        $occupiedPending = (int)$stmtCountPending->fetchColumn();

        $occupied = $occupiedReg + $occupiedPending;
        $freeSeats = max(0, (int)$match['max_players'] - $occupied);

        if ($freeSeats <= 0) {
            // Aggiorna stato match a full
            $this->db->prepare("UPDATE matches SET status = 'full', updated_at = NOW() WHERE id = :id")->execute(['id' => $matchId]);
            return;
        }

        // Recupera panchina ordinata cronologicamente (escludendo chi ha già offerte attive)
        $stmtWaitlist = $this->db->prepare("
            SELECT * FROM registrations 
            WHERE match_id = :match_id 
              AND status = 'waitlist' 
              AND (offer_expires_at IS NULL OR offer_expires_at <= NOW()) 
            ORDER BY created_at ASC
        ");
        $stmtWaitlist->execute(['match_id' => $matchId]);
        $waitlist = $stmtWaitlist->fetchAll(PDO::FETCH_ASSOC);

        $matchDateTime = strtotime($match['date'] . ' ' . $match['time']);
        $timeDiff = $matchDateTime - time();
        $isLastMinute = ($timeDiff > 0 && $timeDiff < 24 * 3600);

        $notificationModel = new Notification();

        foreach ($waitlist as $next) {
            $needed = 1 + (int)$next['has_guest'];
            if ($needed <= $freeSeats) {
                if ($isLastMinute) {
                    // Notifica offerta non automatica (15 min)
                    $this->db->prepare("
                        UPDATE registrations 
                        SET offer_expires_at = DATE_ADD(NOW(), INTERVAL 15 MINUTE), updated_at = NOW() 
                        WHERE id = :id
                    ")->execute(['id' => $next['id']]);

                    $notificationModel->create([
                        'user_recipient' => $next['username'],
                        'type' => 'match_offer',
                        'message' => '⚡ Si è liberato un posto per la partita a ' . $match['location'] . ' del ' . date('d/m/Y', strtotime($match['date'])) . '! Hai 15 minuti per accettare.',
                        'link' => url('/matches/' . $matchId)
                    ]);
                } else {
                    // Promozione automatica immediata
                    $this->db->prepare("
                        UPDATE registrations 
                        SET status = 'registered', offer_expires_at = NULL, updated_at = NOW() 
                        WHERE id = :id
                    ")->execute(['id' => $next['id']]);

                    $notificationModel->create([
                        'user_recipient' => $next['username'],
                        'type' => 'match_promotion',
                        'message' => '🔔 Congratulazioni! Sei stato promosso a giocatore attivo per la partita a ' . $match['location'] . ' del ' . date('d/m/Y', strtotime($match['date'])) . '.',
                        'link' => url('/matches/' . $matchId)
                    ]);
                }

                $freeSeats -= $needed;
                $occupied += $needed;
                if ($freeSeats <= 0) {
                    break;
                }
            }
        }

        // Ricalcola se la partita è di nuovo disponibile o piena
        if ($occupied < (int)$match['max_players']) {
            $this->db->prepare("UPDATE matches SET status = 'open', updated_at = NOW() WHERE id = :id")->execute(['id' => $matchId]);
        } else {
            $this->db->prepare("UPDATE matches SET status = 'full', updated_at = NOW() WHERE id = :id")->execute(['id' => $matchId]);
        }
    }

    public function autoFinishPastMatches(): void
    {
        // 1. Trova tutte le partite 'open' o 'full' iniziate da più di 2 ore
        $stmt = $this->db->prepare("
            SELECT id, host_username, location, date, time
            FROM matches
            WHERE status IN ('open', 'full')
              AND CONCAT(date, ' ', time) <= DATE_SUB(NOW(), INTERVAL 2 HOUR)
        ");
        $stmt->execute();
        $pastMatches = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($pastMatches)) {
            return;
        }

        foreach ($pastMatches as $match) {
            $matchId = $match['id'];
            try {
                // Imposta lo stato a 'finished' per avviare il processo di refertazione
                $this->db->prepare("
                    UPDATE matches 
                    SET status = 'finished', updated_at = NOW() 
                    WHERE id = :id
                ")->execute(['id' => $matchId]);
            } catch (Exception $e) {
                // Ignore to not block page load
            }
        }
    }

    public function autoCloseUnreportedMatches(): void
    {
        // 1. Trova tutte le partite iniziate da più di 48 ore non refertate e non cancellate
        $stmt = $this->db->prepare("
            SELECT id, host_username, date, time, location
            FROM matches
            WHERE status IN ('open', 'full', 'finished')
              AND (result_home IS NULL OR result_away IS NULL)
              AND CONCAT(date, ' ', time) <= DATE_SUB(NOW(), INTERVAL 48 HOUR)
        ");
        $stmt->execute();
        $expiredMatches = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($expiredMatches)) {
            return;
        }

        $notificationModel = new Notification();

        foreach ($expiredMatches as $match) {
            $matchId = $match['id'];
            $host = $match['host_username'];
            $location = $match['location'];
            $dateFormatted = date('d/m/Y', strtotime($match['date']));

            try {
                $this->db->beginTransaction();

                // 2. Aggiorna lo stato della partita, i risultati d'ufficio (0-0) e la scadenza per la votazione MVP (24 ore da adesso)
                $stmtUpdateMatch = $this->db->prepare("
                    UPDATE matches 
                    SET status = 'finished', 
                        result_home = 0, 
                        result_away = 0, 
                        mvp_deadline = DATE_ADD(NOW(), INTERVAL 24 HOUR), 
                        updated_at = NOW() 
                    WHERE id = :id
                ");
                $stmtUpdateMatch->execute(['id' => $matchId]);

                // 3. Ricalcola total_goals e matches_played per tutti i partecipanti del match
                $stmtUpdateStats = $this->db->prepare("
                    UPDATE users u
                    SET 
                      u.total_goals = (
                          SELECT COALESCE(SUM(r.goals_scored), 0)
                          FROM registrations r
                          JOIN matches m ON r.match_id = m.id
                          WHERE r.username = u.username 
                            AND r.status = 'registered' 
                            AND m.status = 'finished'
                      ),
                      u.matches_played = (
                          SELECT COUNT(*)
                          FROM registrations r
                          JOIN matches m ON r.match_id = m.id
                          WHERE r.username = u.username 
                            AND r.status = 'registered' 
                            AND m.status = 'finished'
                      )
                    WHERE u.username IN (
                        SELECT DISTINCT username 
                        FROM registrations 
                        WHERE match_id = :match_id 
                          AND status = 'registered'
                    )
                ");
                $stmtUpdateStats->execute(['match_id' => $matchId]);

                // 4. Detrazione di 15 punti sul Trust Score dell'host
                $penalty = -15;
                $stmtPenalty = $this->db->prepare("
                    UPDATE users 
                    SET trust_score = GREATEST(0, CAST(trust_score AS SIGNED) + :penalty), 
                        updated_at = NOW() 
                    WHERE username = :host
                ");
                $stmtPenalty->execute(['penalty' => $penalty, 'host' => $host]);

                // Inserimento del log di Trust Score
                $stmtLog = $this->db->prepare("
                    INSERT INTO trust_history (username, match_id, score_change, reason, created_at) 
                    VALUES (:host, :match_id, :change, 'Mancato inserimento tabellino entro 48h', NOW())
                ");
                $stmtLog->execute([
                    'host' => $host,
                    'match_id' => $matchId,
                    'change' => $penalty
                ]);

                $this->db->commit();

                // 5. Invia notifiche
                // Notifica per l'host
                $notificationModel->create([
                    'user_recipient' => $host,
                    'type' => 'match_autoclose_host',
                    'message' => '⚠️ La partita a ' . $location . ' del ' . $dateFormatted . ' è stata chiusa d\'ufficio per mancato inserimento del tabellino entro 48 ore. Hai ricevuto una penalità di -15 al Trust Score.',
                    'link' => url('/matches/' . $matchId)
                ]);

                // Recupera tutti gli altri partecipanti registrati
                $stmtPlayers = $this->db->prepare("
                    SELECT username 
                    FROM registrations 
                    WHERE match_id = :match_id 
                      AND status = 'registered' 
                      AND username != :host
                ");
                $stmtPlayers->execute(['match_id' => $matchId, 'host' => $host]);
                $players = $stmtPlayers->fetchAll(PDO::FETCH_COLUMN);

                foreach ($players as $player) {
                    $notificationModel->create([
                        'user_recipient' => $player,
                        'type' => 'match_autoclose_player',
                        'message' => '📢 La partita a ' . $location . ' del ' . $dateFormatted . ' è stata chiusa d\'ufficio senza risultato (tabellino mancante). Ora puoi votare per i compagni e per l\'MVP!',
                        'link' => url('/matches/' . $matchId)
                    ]);
                }

            } catch (Exception $e) {
                if ($this->db->inTransaction()) {
                    $this->db->rollBack();
                }
            }
        }
    }
}
