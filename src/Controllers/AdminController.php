<?php

namespace App\Controllers;

use DateTime;
use PDO;

class AdminController extends BaseController {

    public function index() {
        $db = \App\Database::getInstance()->getConnection();

        // 1. Stats
        $totalUsers = (int) $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $bannedUsers = (int) $db->query("SELECT COUNT(*) FROM users WHERE is_banned = 1")->fetchColumn();
        $totalMatches = (int) $db->query("SELECT COUNT(*) FROM matches")->fetchColumn();
        $activeMatches = (int) $db->query("SELECT COUNT(*) FROM matches WHERE status IN ('open', 'full')")->fetchColumn();
        $finishedMatches = (int) $db->query("SELECT COUNT(*) FROM matches WHERE status = 'finished'")->fetchColumn();
        $cancelledMatches = (int) $db->query("SELECT COUNT(*) FROM matches WHERE status = 'cancelled'")->fetchColumn();
        $pendingReports = (int) $db->query("SELECT COUNT(*) FROM reports WHERE status = 'pending'")->fetchColumn();

        $stats = [
            'total_users' => $totalUsers,
            'banned_users' => $bannedUsers,
            'total_matches' => $totalMatches,
            'active_matches' => $activeMatches,
            'finished_matches' => $finishedMatches,
            'cancelled_matches' => $cancelledMatches,
            'pending_reports' => $pendingReports
        ];

        // 2. Users Table with sorting, searching, and pagination
        $search = $_GET['search'] ?? '';
        $roleFilter = $_GET['role'] ?? '';
        $statusFilter = $_GET['status'] ?? '';
        $sortBy = $_GET['sort'] ?? 'username';
        if ($sortBy === 'id') {
            $sortBy = 'username';
        }
        $sortOrder = $_GET['order'] ?? 'asc';
        if (!in_array(strtolower($sortOrder), ['asc', 'desc'])) {
            $sortOrder = 'asc';
        }

        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $perPageUsers = 10;
        $offsetUsers = ($page - 1) * $perPageUsers;

        $whereUsers = [];
        $paramsUsers = [];

        if (!empty($search)) {
            $whereUsers[] = "(u.username LIKE :search OR u.name LIKE :search OR u.email LIKE :search)";
            $paramsUsers['search'] = '%' . $search . '%';
        }
        if (!empty($roleFilter)) {
            $whereUsers[] = "u.role = :role";
            $paramsUsers['role'] = $roleFilter;
        }
        if (!empty($statusFilter)) {
            if ($statusFilter === 'banned') {
                $whereUsers[] = "u.is_banned = 1";
            } elseif ($statusFilter === 'active') {
                $whereUsers[] = "u.is_banned = 0";
            }
        }

        $whereUsersSql = !empty($whereUsers) ? 'WHERE ' . implode(' AND ', $whereUsers) : '';

        $allowedSorts = ['username', 'trust_score', 'weather_cancels'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'username';
        }

        $orderBySql = "ORDER BY " . ($sortBy === 'weather_cancels' ? 'weather_cancels' : "u.{$sortBy}") . " " . $sortOrder;

        $countQueryUsers = "SELECT COUNT(*) FROM users u $whereUsersSql";
        $stmtCountUsers = $db->prepare($countQueryUsers);
        $stmtCountUsers->execute($paramsUsers);
        $totalUsersFiltered = (int)$stmtCountUsers->fetchColumn();
        $totalPagesUsers = ceil($totalUsersFiltered / $perPageUsers);

        $listQueryUsers = "
            SELECT u.*, 
                   (SELECT COUNT(*) FROM matches m WHERE m.host_username = u.username AND m.status = 'cancelled' AND m.cancellation_reason = 'Meteo avverso') AS weather_cancels
            FROM users u
            $whereUsersSql
            $orderBySql
            LIMIT :limit OFFSET :offset
        ";
        $stmtListUsers = $db->prepare($listQueryUsers);
        foreach ($paramsUsers as $k => $v) {
            $stmtListUsers->bindValue($k, $v);
        }
        $stmtListUsers->bindValue('limit', $perPageUsers, PDO::PARAM_INT);
        $stmtListUsers->bindValue('offset', $offsetUsers, PDO::PARAM_INT);
        $stmtListUsers->execute();
        $usersList = $stmtListUsers->fetchAll();

        foreach ($usersList as &$u) {
            $u['id'] = $u['username'];
        }
        unset($u);

        $allRoles = ['user', 'super_admin'];

        // 3. Reports Table with pagination and filters
        $searchReport = $_GET['search_report'] ?? '';
        $statusReport = $_GET['status_report'] ?? 'pending';
        $reportsPage = isset($_GET['reports_page']) ? max(1, (int)$_GET['reports_page']) : 1;
        $perPageReports = 5;
        $offsetReports = ($reportsPage - 1) * $perPageReports;

        $whereReports = [];
        $paramsReports = [];

        if (!empty($searchReport)) {
            $whereReports[] = "(r.reason LIKE :search_rep OR r.description LIKE :search_rep OR r.reporter_username LIKE :search_rep OR r.reported_username LIKE :search_rep)";
            $paramsReports['search_rep'] = '%' . $searchReport . '%';
        }
        if (!empty($statusReport)) {
            $whereReports[] = "r.status = :status_rep";
            $paramsReports['status_rep'] = $statusReport;
        }

        $whereReportsSql = !empty($whereReports) ? 'WHERE ' . implode(' AND ', $whereReports) : '';

        $countQueryReports = "SELECT COUNT(*) FROM reports r $whereReportsSql";
        $stmtCountReports = $db->prepare($countQueryReports);
        $stmtCountReports->execute($paramsReports);
        $totalReportsFiltered = (int)$stmtCountReports->fetchColumn();
        $totalPagesReports = ceil($totalReportsFiltered / $perPageReports);

        $listQueryReports = "
            SELECT r.*,
                   u1.name AS reporter_name, u1.email AS reporter_email,
                   u2.name AS reported_name, u2.email AS reported_email, u2.is_banned AS reported_is_banned
            FROM reports r
            LEFT JOIN users u1 ON r.reporter_username = u1.username
            LEFT JOIN users u2 ON r.reported_username = u2.username
            $whereReportsSql
            ORDER BY r.created_at DESC
            LIMIT :limit OFFSET :offset
        ";
        $stmtListReports = $db->prepare($listQueryReports);
        foreach ($paramsReports as $k => $v) {
            $stmtListReports->bindValue($k, $v);
        }
        $stmtListReports->bindValue('limit', $perPageReports, PDO::PARAM_INT);
        $stmtListReports->bindValue('offset', $offsetReports, PDO::PARAM_INT);
        $stmtListReports->execute();
        $reportsListRaw = $stmtListReports->fetchAll();

        $reportsList = [];
        foreach ($reportsListRaw as $row) {
            $row['reporter'] = $row['reporter_username'] ? [
                'id' => $row['reporter_username'],
                'name' => $row['reporter_name'],
                'email' => $row['reporter_email']
            ] : null;
            $row['reported'] = $row['reported_username'] ? [
                'id' => $row['reported_username'],
                'name' => $row['reported_name'],
                'email' => $row['reported_email'],
                'is_banned' => $row['reported_is_banned']
            ] : null;
            $row['created_at'] = new DateTime($row['created_at']);
            $reportsList[] = $row;
        }

        // 4. Matches Table with pagination and filters
        $searchMatch = $_GET['search_match'] ?? '';
        $statusMatch = $_GET['status_match'] ?? '';
        $dateMatch = $_GET['date_match'] ?? '';
        $formatMatch = $_GET['format_match'] ?? '';
        $matchesPage = isset($_GET['matches_page']) ? max(1, (int)$_GET['matches_page']) : 1;
        $perPageMatches = 5;
        $offsetMatches = ($matchesPage - 1) * $perPageMatches;

        $whereMatches = [];
        $paramsMatches = [];

        if (!empty($searchMatch)) {
            $whereMatches[] = "(m.location LIKE :search_m OR m.host_username LIKE :search_m)";
            $paramsMatches['search_m'] = '%' . $searchMatch . '%';
        }
        if (!empty($statusMatch)) {
            $whereMatches[] = "m.status = :status_m";
            $paramsMatches['status_m'] = $statusMatch;
        }
        if (!empty($dateMatch)) {
            $whereMatches[] = "m.date = :date_m";
            $paramsMatches['date_m'] = $dateMatch;
        }
        if (!empty($formatMatch)) {
            $whereMatches[] = "m.format = :format_m";
            $paramsMatches['format_m'] = $formatMatch;
        }

        $whereMatchesSql = !empty($whereMatches) ? 'WHERE ' . implode(' AND ', $whereMatches) : '';

        $countQueryMatches = "SELECT COUNT(*) FROM matches m $whereMatchesSql";
        $stmtCountMatches = $db->prepare($countQueryMatches);
        $stmtCountMatches->execute($paramsMatches);
        $totalMatchesFiltered = (int)$stmtCountMatches->fetchColumn();
        $totalPagesMatches = ceil($totalMatchesFiltered / $perPageMatches);

        $listQueryMatches = "
            SELECT m.*, u.name AS host_name
            FROM matches m
            LEFT JOIN users u ON m.host_username = u.username
            $whereMatchesSql
            ORDER BY m.date DESC, m.time DESC
            LIMIT :limit OFFSET :offset
        ";
        $stmtListMatches = $db->prepare($listQueryMatches);
        foreach ($paramsMatches as $k => $v) {
            $stmtListMatches->bindValue($k, $v);
        }
        $stmtListMatches->bindValue('limit', $perPageMatches, PDO::PARAM_INT);
        $stmtListMatches->bindValue('offset', $offsetMatches, PDO::PARAM_INT);
        $stmtListMatches->execute();
        $matchesListRaw = $stmtListMatches->fetchAll();

        $matchesList = [];
        foreach ($matchesListRaw as $row) {
            $row['host'] = [
                'name' => $row['host_name']
            ];
            $row['date'] = new DateTime($row['date']);
            $matchesList[] = $row;
        }

        // 5. Trust Score Logs with pagination
        $trustPage = isset($_GET['trust_page']) ? max(1, (int)$_GET['trust_page']) : 1;
        $perPageTrust = 5;
        $offsetTrust = ($trustPage - 1) * $perPageTrust;

        $totalTrust = (int)$db->query("SELECT COUNT(*) FROM trust_history")->fetchColumn();
        $totalPagesTrust = ceil($totalTrust / $perPageTrust);

        $listQueryTrust = "
            SELECT th.*, u.name AS user_name
            FROM trust_history th
            LEFT JOIN users u ON th.username = u.username
            ORDER BY th.created_at DESC
            LIMIT :limit OFFSET :offset
        ";
        $stmtListTrust = $db->prepare($listQueryTrust);
        $stmtListTrust->bindValue('limit', $perPageTrust, PDO::PARAM_INT);
        $stmtListTrust->bindValue('offset', $offsetTrust, PDO::PARAM_INT);
        $stmtListTrust->execute();
        $trustLogsRaw = $stmtListTrust->fetchAll();

        $trustLogsList = [];
        foreach ($trustLogsRaw as $row) {
            $row['user'] = [
                'name' => $row['user_name']
            ];
            $row['user_id'] = $row['username'];
            $row['created_at'] = new DateTime($row['created_at']);
            $trustLogsList[] = $row;
        }

        view('admin/index', [
            'title' => 'Dashboard Amministratore - AlmaKick',
            'stats' => $stats,
            
            // Users
            'users' => $usersList,
            'totalUsersFiltered' => $totalUsersFiltered,
            'totalPagesUsers' => $totalPagesUsers,
            'pageUsers' => $page,
            'search' => $search,
            'roleFilter' => $roleFilter,
            'statusFilter' => $statusFilter,
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder,
            'allRoles' => $allRoles,
            
            // Reports
            'reports' => $reportsList,
            'totalReportsFiltered' => $totalReportsFiltered,
            'totalPagesReports' => $totalPagesReports,
            'pageReports' => $reportsPage,
            'searchReport' => $searchReport,
            'statusReport' => $statusReport,
            
            // Matches
            'matches' => $matchesList,
            'totalMatchesFiltered' => $totalMatchesFiltered,
            'totalPagesMatches' => $totalPagesMatches,
            'pageMatches' => $matchesPage,
            'searchMatch' => $searchMatch,
            'statusMatch' => $statusMatch,
            'dateMatch' => $dateMatch,
            'formatMatch' => $formatMatch,

            // Trust Logs
            'trust_logs' => $trustLogsList,
            'totalPagesTrust' => $totalPagesTrust,
            'pageTrust' => $trustPage
        ]);
    }

    public function ban() {
        $this->validateCsrf();
        $username = $_POST['user_id'] ?? '';
        
        if (!empty($username) && $username !== $_SESSION['user']['username']) {
            $db = \App\Database::getInstance()->getConnection();
            
            $stmt = $db->prepare("UPDATE users SET is_banned = 1, trust_score = 0 WHERE username = :username");
            $stmt->execute(['username' => $username]);
            
            $stmtLog = $db->prepare("INSERT INTO trust_history (username, score_change, reason, created_at) VALUES (:username, -100, 'Utente bannato dall\'amministratore', NOW())");
            $stmtLog->execute(['username' => $username]);
            
            $_SESSION['success'] = "Utente bannato con successo.";
        } else {
            $_SESSION['error'] = "Impossibile completare l'operazione su se stessi o utente non valido.";
        }
        $this->redirect(url('/admin'));
    }

    public function unban() {
        $this->validateCsrf();
        $username = $_POST['user_id'] ?? '';
        
        if (!empty($username)) {
            $db = \App\Database::getInstance()->getConnection();
            
            $stmt = $db->prepare("UPDATE users SET is_banned = 0, trust_score = 100 WHERE username = :username");
            $stmt->execute(['username' => $username]);
            
            $stmtLog = $db->prepare("INSERT INTO trust_history (username, score_change, reason, created_at) VALUES (:username, 100, 'Utente riabilitato dall\'amministratore', NOW())");
            $stmtLog->execute(['username' => $username]);
            
            $_SESSION['success'] = "Utente riabilitato con successo.";
        } else {
            $_SESSION['error'] = "Azione non valida.";
        }
        $this->redirect(url('/admin'));
    }

    public function resolveReport($id) {
        $this->validateCsrf();
        $adminNotes = trim($_POST['admin_notes'] ?? '');
        
        $db = \App\Database::getInstance()->getConnection();
        $stmt = $db->prepare("UPDATE reports SET status = 'resolved', admin_notes = :notes, updated_at = NOW() WHERE id = :id");
        $stmt->execute([
            'notes' => $adminNotes ? $adminNotes : null,
            'id' => $id
        ]);
        
        $_SESSION['success'] = "Segnalazione #{$id} contrassegnata come risolta.";
        $this->redirect(url('/admin'));
    }

    public function dismissReport($id) {
        $this->validateCsrf();
        $adminNotes = trim($_POST['admin_notes'] ?? '');
        
        $db = \App\Database::getInstance()->getConnection();
        $stmt = $db->prepare("UPDATE reports SET status = 'dismissed', admin_notes = :notes, updated_at = NOW() WHERE id = :id");
        $stmt->execute([
            'notes' => $adminNotes ? $adminNotes : null,
            'id' => $id
        ]);
        
        $_SESSION['success'] = "Segnalazione #{$id} archiviata/ignorata.";
        $this->redirect(url('/admin'));
    }

    public function forceCancelMatch() {
        $this->validateCsrf();
        $matchId = $_POST['match_id'] ?? '';
        
        if (!empty($matchId)) {
            $db = \App\Database::getInstance()->getConnection();
            
            $stmt = $db->prepare("UPDATE matches SET status = 'cancelled', cancellation_reason = 'Annullata da amministratore', updated_at = NOW() WHERE id = :id");
            $stmt->execute(['id' => $matchId]);
            
            $_SESSION['success'] = "Partita #{$matchId} annullata forzatamente.";
        } else {
            $_SESSION['error'] = "Azione non valida.";
        }
        $this->redirect(url('/admin'));
    }

    public function deleteMatch() {
        $this->validateCsrf();
        $matchId = $_POST['match_id'] ?? '';
        
        if (!empty($matchId)) {
            $db = \App\Database::getInstance()->getConnection();
            
            $stmt = $db->prepare("DELETE FROM matches WHERE id = :id");
            $stmt->execute(['id' => $matchId]);
            
            $_SESSION['success'] = "Partita #{$matchId} eliminata definitivamente.";
        } else {
            $_SESSION['error'] = "Azione non valida.";
        }
        $this->redirect(url('/admin'));
    }
}
