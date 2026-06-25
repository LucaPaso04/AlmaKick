<?php

namespace App\Controllers;

use App\Models\SoccerMatch;

class MatchController extends BaseController {

    public function index() {
        $matchModel = new SoccerMatch();
        
        $filters = [
            'location' => $_GET['location'] ?? null,
            'date' => $_GET['date'] ?? null,
            'format' => $_GET['format'] ?? null,
            'filter' => $_GET['filter'] ?? null,
            'only_friends' => $_GET['only_friends'] ?? null,
            'exclude_my_matches' => $_GET['exclude_my_matches'] ?? null,
            'username' => $_SESSION['user']['username'] ?? null
        ];

        // Pagination parameters
        $perPage = 6;
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $offset = ($page - 1) * $perPage;

        // Clone filters for total count and add limit/offset to filters for list query
        $listFilters = array_merge($filters, [
            'limit' => $perPage,
            'offset' => $offset
        ]);

        $matches = $matchModel->getAllActive($listFilters);
        $totalMatches = $matchModel->countAllActive($filters);
        $totalPages = ceil($totalMatches / $perPage);

        // If AJAX request, return JSON with matches cards HTML and pagination controls
        if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
            $friendHostUsernames = [];
            if (!empty($_SESSION['user']['username'])) {
                $friendHostUsernames = $matchModel->getFriendUsernames($_SESSION['user']['username']);
            }
            
            ob_start();
            if (!empty($matches)) {
                foreach ($matches as $p) {
                    require VIEW_PATH . '/matches/partials/match_card.php';
                }
            } else {
                echo '
                <div class="col-12">
                    <div class="alert border shadow-sm text-center py-5 rounded-4 d-flex flex-column align-items-center justify-content-center matches-empty-state">
                        <div class="border rounded-circle d-flex align-items-center justify-content-center shadow-sm mb-3 matches-empty-icon">
                            <i class="bi bi-calendar-x fs-2"></i>
                        </div>
                        <h5 class="fw-bold">Nessuna partita trovata</h5>
                        <p class="text-secondary-custom small mb-4 matches-empty-text-wrap-sm">Nessuna partita soddisfa i criteri di ricerca impostati. Prova a modificare i filtri o organizza tu una nuova partita!</p>';
                if (isset($_SESSION['user']) && $_SESSION['user']['role'] !== 'super_admin') {
                    echo '      <a href="' . url('/matches/create') . '" class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold">Organizza Ora</a>';
                }
                echo '
                    </div>
                </div>';
            }
            $htmlContent = ob_get_clean();

            // Generate pagination HTML
            $paginationHtml = '';
            if ($totalPages > 1) {
                $paginationHtml .= '<nav aria-label="Navigazione pagine"><ul class="pagination pagination-sm justify-content-center mt-4 mb-0">';
                $prevClass = ($page <= 1) ? 'disabled' : '';
                $paginationHtml .= '<li class="page-item ' . $prevClass . '"><a class="page-link" href="#" data-page="' . ($page - 1) . '"><i class="bi bi-chevron-left"></i></a></li>';
                for ($i = 1; $i <= $totalPages; $i++) {
                    $activeClass = ($i == $page) ? 'active' : '';
                    $paginationHtml .= '<li class="page-item ' . $activeClass . '"><a class="page-link" href="#" data-page="' . $i . '">' . $i . '</a></li>';
                }
                $nextClass = ($page >= $totalPages) ? 'disabled' : '';
                $paginationHtml .= '<li class="page-item ' . $nextClass . '"><a class="page-link" href="#" data-page="' . ($page + 1) . '"><i class="bi bi-chevron-right"></i></a></li>';
                $paginationHtml .= '</ul></nav>';
            }

            header('Content-Type: application/json');
            echo json_encode([
                'html' => $htmlContent,
                'pagination' => $paginationHtml
            ]);
            exit;
        }

        $username = $_SESSION['user']['username'] ?? null;
        $matchesToReport = [];
        $matchesToVote = [];
        if ($username) {
            $matchesToReport = $matchModel->getMatchesToReport($username);
            $matchesToVote = $matchModel->getMatchesToVote($username);
        }

        view('matches/index', [
            'title' => 'Partite Disponibili - AlmaKick',
            'matches' => $matches,
            'totalPages' => $totalPages,
            'page' => $page,
            'matchesToReport' => $matchesToReport,
            'matchesToVote' => $matchesToVote
        ]);
    }

    public function show($id) {
        $matchModel = new SoccerMatch();
        $match = $matchModel->find($id);

        if (!$match) {
            http_response_code(404);
            echo "Partita non trovata.";
            return;
        }

        $from = $_GET['from'] ?? '';

        $db = \App\Database::getInstance()->getConnection();

        // Carica iscrizioni con info utenti
        $stmtReg = $db->prepare("
            SELECT r.*, u.name, u.avatar, u.preferred_role, u.trust_score, u.skill_rating 
            FROM registrations r
            JOIN users u ON r.username = u.username
            WHERE r.match_id = :match_id
            ORDER BY r.created_at ASC
        ");
        $stmtReg->execute(['match_id' => $id]);
        $registrations = $stmtReg->fetchAll(\PDO::FETCH_ASSOC);

        // Calcolo posti e quote
        $occupied_seats = 0;
        foreach ($registrations as $reg) {
            if ($reg['status'] === 'registered') {
                $occupied_seats += 1 + (int)$reg['has_guest'];
            }
        }
        $available_seats = max(0, (int)$match['max_players'] - $occupied_seats);
        $current_quote = (float)$match['total_cost'] / max(1, (int)$match['max_players']);

        // Verifica iscrizione e ruolo utente corrente
        $is_registered = false;
        if (isset($_SESSION['user']['username'])) {
            $currentUser = $_SESSION['user']['username'];
            foreach ($registrations as $reg) {
                if ($reg['username'] === $currentUser && in_array($reg['status'], ['registered', 'waitlist'])) {
                    $is_registered = true;
                    break;
                }
            }
        }

        $is_host = isset($_SESSION['user']['username']) && $_SESSION['user']['username'] === $match['host_username'];

        // Carica votazioni se utente loggato
        $evaluations = [];
        if (isset($_SESSION['user']['username'])) {
            $stmtEval = $db->prepare("SELECT * FROM evaluations WHERE match_id = :match_id AND evaluator_username = :username");
            $stmtEval->execute([
                'match_id' => $id,
                'username' => $_SESSION['user']['username']
            ]);
            $evaluations = $stmtEval->fetchAll(\PDO::FETCH_ASSOC);
        }

        // Dettagli MVP
        $mvp = null;
        if (!empty($match['mvp_assigned']) && !empty($match['mvp_username'])) {
            $stmtMvp = $db->prepare("SELECT username, name, avatar FROM users WHERE username = :username");
            $stmtMvp->execute(['username' => $match['mvp_username']]);
            $mvp = $stmtMvp->fetch(\PDO::FETCH_ASSOC);
        }

        $weather = 'Caricamento meteo...';

        view('matches/show', [
            'title' => 'Dettagli Partita - AlmaKick',
            'match' => $match,
            'from' => $from,
            'registrations' => $registrations,
            'occupied_seats' => $occupied_seats,
            'available_seats' => $available_seats,
            'current_quote' => $current_quote,
            'is_registered' => $is_registered,
            'is_host' => $is_host,
            'evaluations' => $evaluations,
            'mvp' => $mvp,
            'weather' => $weather
        ]);
    }

    public function join($id) {
        $this->validateCsrf();
        $username = $_SESSION['user']['username'] ?? null;
        if (!$username) {
            $_SESSION['error'] = "Devi effettuare l'accesso per iscriverti.";
            $this->redirect(url('/login'));
        }

        $matchModel = new SoccerMatch();
        $match = $matchModel->find($id);
        if (!$match) {
            $_SESSION['error'] = "Partita non trovata.";
            $this->redirect(url('/matches'));
        }

        $db = \App\Database::getInstance()->getConnection();

        // Controlla se l'utente è già iscritto
        $stmtCheck = $db->prepare("SELECT * FROM registrations WHERE match_id = :match_id AND username = :username");
        $stmtCheck->execute(['match_id' => $id, 'username' => $username]);
        $existing = $stmtCheck->fetch();

        // Calcola posti occupati
        $stmtCount = $db->prepare("SELECT COALESCE(SUM(1 + has_guest), 0) FROM registrations WHERE match_id = :match_id AND status = 'registered'");
        $stmtCount->execute(['match_id' => $id]);
        $occupied = (int)$stmtCount->fetchColumn();

        $hasGuest = (isset($_POST['has_guest']) && $_POST['has_guest'] == 1) ? 1 : 0;
        $neededSeats = $hasGuest ? 2 : 1;

        // Gestione inserimento o panchina
        $status = 'registered';
        if ($occupied + $neededSeats > (int)$match['max_players']) {
            $status = 'waitlist';
        }

        if ($existing) {
            if ($existing['status'] === 'cancelled') {
                $stmtUpdate = $db->prepare("UPDATE registrations SET status = :status, has_guest = :has_guest, updated_at = NOW() WHERE id = :id");
                $stmtUpdate->execute(['status' => $status, 'has_guest' => $hasGuest, 'id' => $existing['id']]);
                $_SESSION['success'] = $status === 'registered' ? "Ti sei iscritto con successo!" : "Posti esauriti. Sei in panchina (lista d'attesa).";
            } else {
                $_SESSION['error'] = "Sei già iscritto a questa partita.";
            }
        } else {
            $stmtInsert = $db->prepare("INSERT INTO registrations (match_id, username, status, has_guest, created_at, updated_at) VALUES (:match_id, :username, :status, :has_guest, NOW(), NOW())");
            $stmtInsert->execute(['match_id' => $id, 'username' => $username, 'status' => $status, 'has_guest' => $hasGuest]);
            $_SESSION['success'] = $status === 'registered' ? "Ti sei iscritto con successo!" : "Posti esauriti. Sei in panchina (lista d'attesa).";
        }

        // Se la partita è ora piena, aggiorna lo stato
        if ($status === 'registered') {
            $newOccupied = $occupied + $neededSeats;
            if ($newOccupied >= (int)$match['max_players']) {
                $db->prepare("UPDATE matches SET status = 'full' WHERE id = :id")->execute(['id' => $id]);
            }
        }

        $this->redirectToMatch($id);
    }

    public function leave($id) {
        $this->validateCsrf();
        $username = $_SESSION['user']['username'] ?? null;
        if (!$username) {
            $_SESSION['error'] = "Devi effettuare l'accesso per ritirarti.";
            $this->redirect(url('/login'));
        }

        $db = \App\Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM registrations WHERE match_id = :match_id AND username = :username AND status IN ('registered', 'waitlist')");
        $stmt->execute(['match_id' => $id, 'username' => $username]);
        $reg = $stmt->fetch();
        if (!$reg) {
            $_SESSION['error'] = "Non sei iscritto a questa partita.";
            $this->redirectToMatch($id);
        }

        $matchModel = new SoccerMatch();
        $match = $matchModel->find($id);
        
        // Penalità di Trust Score se manca meno di 24h all'inizio ed era iscritto attivo
        $matchDateTime = strtotime($match['date'] . ' ' . $match['time']);
        $timeDiff = $matchDateTime - time();
        $scorePenalty = 0;

        if ($timeDiff < 24 * 3600 && $reg['status'] === 'registered') {
            $scorePenalty = -15;
            $stmtPenalty = $db->prepare("UPDATE users SET trust_score = GREATEST(0, trust_score + :penalty) WHERE username = :username");
            $stmtPenalty->execute(['penalty' => $scorePenalty, 'username' => $username]);

            $stmtLog = $db->prepare("INSERT INTO trust_history (username, match_id, score_change, reason, created_at) VALUES (:username, :match_id, :change, 'Ritiro tardivo (<24h)', NOW())");
            $stmtLog->execute(['username' => $username, 'match_id' => $id, 'change' => $scorePenalty]);
        }

        // Cancella iscrizione
        $db->prepare("UPDATE registrations SET status = 'cancelled', updated_at = NOW() WHERE id = :id")->execute(['id' => $reg['id']]);

        // Se era un iscritto attivo, prova a promuovere il primo in panchina
        if ($reg['status'] === 'registered') {
            $stmtNext = $db->prepare("SELECT * FROM registrations WHERE match_id = :match_id AND status = 'waitlist' ORDER BY created_at ASC LIMIT 1");
            $stmtNext->execute(['match_id' => $id]);
            $next = $stmtNext->fetch();
            if ($next) {
                $db->prepare("UPDATE registrations SET status = 'registered', updated_at = NOW() WHERE id = :id")->execute(['id' => $next['id']]);
            }

            // Ricalcola se la partita è di nuovo disponibile
            $stmtCount = $db->prepare("SELECT COALESCE(SUM(1 + has_guest), 0) FROM registrations WHERE match_id = :match_id AND status = 'registered'");
            $stmtCount->execute(['match_id' => $id]);
            $occupied = (int)$stmtCount->fetchColumn();

            if ($occupied < (int)$match['max_players']) {
                $db->prepare("UPDATE matches SET status = 'open' WHERE id = :id")->execute(['id' => $id]);
            }
        }

        $_SESSION['success'] = "Ritiro effettuato." . ($scorePenalty < 0 ? " Hai ricevuto una penalità di $scorePenalty punti sul tuo Trust Score per ritiro tardivo." : "");
        $this->redirectToMatch($id);
    }

    public function generateTeams($id) {
        $this->validateCsrf();
        $username = $_SESSION['user']['username'] ?? null;
        $matchModel = new SoccerMatch();
        $match = $matchModel->find($id);

        if (!$match || $match['host_username'] !== $username) {
            $_SESSION['error'] = "Non sei l'organizzatore di questa partita.";
            $this->redirectToMatch($id);
        }

        $db = \App\Database::getInstance()->getConnection();
        // Recupera iscritti attivi
        $stmt = $db->prepare("
            SELECT r.id, u.skill_rating 
            FROM registrations r
            JOIN users u ON r.username = u.username
            WHERE r.match_id = :match_id AND r.status = 'registered'
        ");
        $stmt->execute(['match_id' => $id]);
        $players = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if (count($players) < 2) {
            $_SESSION['error'] = "Numero di iscritti insufficiente per equilibrare le squadre.";
            $this->redirectToMatch($id);
        }

        // Ordina per skill
        usort($players, function($a, $b) {
            return $b['skill_rating'] <=> $a['skill_rating'];
        });

        // Snake draft per assegnare Home e Away
        $homeIds = [];
        $awayIds = [];
        foreach ($players as $idx => $player) {
            $round = (int)floor($idx / 2);
            if ($round % 2 === 0) {
                if ($idx % 2 === 0) $homeIds[] = $player['id'];
                else $awayIds[] = $player['id'];
            } else {
                if ($idx % 2 === 0) $awayIds[] = $player['id'];
                else $homeIds[] = $player['id'];
            }
        }

        // Esegue modifiche su DB
        $stmtUpdateHome = $db->prepare("UPDATE registrations SET team = 'home' WHERE id = :id");
        foreach ($homeIds as $regId) {
            $stmtUpdateHome->execute(['id' => $regId]);
        }
        $stmtUpdateAway = $db->prepare("UPDATE registrations SET team = 'away' WHERE id = :id");
        foreach ($awayIds as $regId) {
            $stmtUpdateAway->execute(['id' => $regId]);
        }

        $_SESSION['success'] = "Squadre generate con successo in base alla Skill dei giocatori!";
        $this->redirectToMatch($id);
    }

    public function close($id) {
        $this->validateCsrf();
        $username = $_SESSION['user']['username'] ?? null;
        $matchModel = new SoccerMatch();
        $match = $matchModel->find($id);

        if (!$match || $match['host_username'] !== $username) {
            $_SESSION['error'] = "Non sei l'organizzatore di questa partita.";
            $this->redirect(url('/matches/' . $id));
        }

        $db = \App\Database::getInstance()->getConnection();
        $db->prepare("UPDATE matches SET status = 'finished', updated_at = NOW() WHERE id = :id")->execute(['id' => $id]);
        
        $_SESSION['success'] = "Partita conclusa. Ora puoi inserire il tabellino finale e i gol.";
        $this->redirectToMatch($id);
    }

    public function cancel($id) {
        $this->validateCsrf();
        $username = $_SESSION['user']['username'] ?? null;
        $matchModel = new SoccerMatch();
        $match = $matchModel->find($id);

        if (!$match || $match['host_username'] !== $username) {
            $_SESSION['error'] = "Non sei l'organizzatore di questa partita.";
            $this->redirectToMatch($id);
        }

        $db = \App\Database::getInstance()->getConnection();
        $motivoMeteo = (isset($_POST['motivo_meteo']) && $_POST['motivo_meteo'] == 1) ? 1 : 0;
        $reason = $motivoMeteo ? "Meteo avverso" : "Annullata dall'organizzatore";

        // Penalità di Trust Score se manca meno di 24h all'inizio e non è per maltempo
        $matchDateTime = strtotime($match['date'] . ' ' . $match['time']);
        $timeDiff = $matchDateTime - time();
        $scorePenalty = 0;

        if (!$motivoMeteo && $timeDiff < 24 * 3600) {
            $scorePenalty = -40;
            $stmtPenalty = $db->prepare("UPDATE users SET trust_score = GREATEST(0, trust_score + :penalty) WHERE username = :username");
            $stmtPenalty->execute(['penalty' => $scorePenalty, 'username' => $username]);

            $stmtLog = $db->prepare("INSERT INTO trust_history (username, match_id, score_change, reason, created_at) VALUES (:username, :match_id, :change, 'Annullamento partita tardivo (<24h)', NOW())");
            $stmtLog->execute(['username' => $username, 'match_id' => $id, 'change' => $scorePenalty]);
        }

        // Annulla match e iscrizioni
        $stmtCancel = $db->prepare("UPDATE matches SET status = 'cancelled', cancellation_reason = :reason, updated_at = NOW() WHERE id = :id");
        $stmtCancel->execute(['reason' => $reason, 'id' => $id]);

        $db->prepare("UPDATE registrations SET status = 'cancelled', updated_at = NOW() WHERE match_id = :match_id")->execute(['match_id' => $id]);

        $_SESSION['success'] = "La partita è stata annullata." . ($scorePenalty < 0 ? " Hai ricevuto una penalità di $scorePenalty sul tuo Trust Score per preavviso insufficiente." : "");
        $this->redirectToMatch($id);
    }

    public function setMvpDeadline($id) {
        $this->validateCsrf();
        $username = $_SESSION['user']['username'] ?? null;
        $matchModel = new SoccerMatch();
        $match = $matchModel->find($id);

        if (!$match || $match['host_username'] !== $username) {
            $_SESSION['error'] = "Non sei l'organizzatore di questa partita.";
            $this->redirectToMatch($id);
        }

        $deadline = $_POST['mvp_deadline'] ?? '';
        if (empty($deadline)) {
            $_SESSION['error'] = "Seleziona una data e ora di scadenza valide.";
            $this->redirectToMatch($id);
        }

        $db = \App\Database::getInstance()->getConnection();
        $stmt = $db->prepare("UPDATE matches SET mvp_deadline = :deadline, updated_at = NOW() WHERE id = :id");
        $stmt->execute(['deadline' => $deadline, 'id' => $id]);

        $_SESSION['success'] = "Scadenza votazioni MVP salvata.";
        $this->redirectToMatch($id);
    }

    public function vote($id) {
        $this->validateCsrf();
        $username = $_SESSION['user']['username'] ?? null;
        if (!$username) {
            $_SESSION['error'] = "Devi essere loggato per votare.";
            $this->redirect(url('/login'));
        }

        $votes = $_POST['votes'] ?? [];
        if (empty($votes)) {
            $_SESSION['error'] = "Nessun voto compilato.";
            $this->redirectToMatch($id);
        }

        $db = \App\Database::getInstance()->getConnection();
        $votedCount = 0;

        foreach ($votes as $targetUsername => $voteData) {
            $skillVote = (isset($voteData['skill_vote']) && $voteData['skill_vote'] !== '') ? (int)$voteData['skill_vote'] : null;
            $thumbDown = (isset($voteData['thumb_down']) && $voteData['thumb_down'] == 1) ? 1 : 0;

            if ($skillVote === null && $thumbDown === 0) {
                continue;
            }

            // Verifica che il destinatario sia iscritto attivo alla partita
            $stmtReg = $db->prepare("SELECT 1 FROM registrations WHERE match_id = :match_id AND username = :username AND status = 'registered'");
            $stmtReg->execute(['match_id' => $id, 'username' => $targetUsername]);
            if (!$stmtReg->fetch()) {
                continue;
            }

            // Verifica che non sia già presente una valutazione dello stesso utente per lo stesso destinatario
            $stmtCheck = $db->prepare("SELECT 1 FROM evaluations WHERE match_id = :match_id AND evaluator_username = :evaluator AND evaluated_username = :evaluated");
            $stmtCheck->execute(['match_id' => $id, 'evaluator' => $username, 'evaluated' => $targetUsername]);
            if ($stmtCheck->fetch()) {
                continue;
            }

            // Salva voto
            $stmtInsert = $db->prepare("
                INSERT INTO evaluations (match_id, evaluator_username, evaluated_username, skill_vote, thumb_down, created_at)
                VALUES (:match_id, :evaluator, :evaluated, :skill, :thumb, NOW())
            ");
            $stmtInsert->execute([
                'match_id' => $id,
                'evaluator' => $username,
                'evaluated' => $targetUsername,
                'skill' => $skillVote,
                'thumb' => $thumbDown
            ]);
            $votedCount++;

            // Se c'è pollice in giù, applica penalità di trust score (-10)
            if ($thumbDown) {
                $db->prepare("UPDATE users SET trust_score = GREATEST(0, trust_score - 10) WHERE username = :username")->execute(['username' => $targetUsername]);
                $stmtLog = $db->prepare("INSERT INTO trust_history (username, match_id, score_change, reason, created_at) VALUES (:username, :match_id, -10, 'Ricevuto feedback negativo da compagno', NOW())");
                $stmtLog->execute(['username' => $targetUsername, 'match_id' => $id]);
            }
        }

        if ($votedCount > 0) {
            $_SESSION['success'] = "I tuoi voti per i compagni sono stati salvati.";
        } else {
            $_SESSION['error'] = "Impossibile registrare le valutazioni.";
        }

        $this->redirectToMatch($id);
    }

    public function create() {
        view('matches/create', [
            'title' => 'Crea Partita - AlmaKick'
        ]);
    }

    public function store() {
        $this->validateCsrf();

        $date = $_POST['date'] ?? '';
        $time = $_POST['time'] ?? '';
        $format = $_POST['format'] ?? '5vs5';
        $location = trim($_POST['location'] ?? '');
        $totalCost = $_POST['total_cost'] ?? 0;
        $visibility = $_POST['visibility'] ?? 'public';

        if (empty($date) || empty($time) || empty($location)) {
            $_SESSION['error'] = "Tutti i campi obbligatori devono essere compilati.";
            $this->redirect('/matches/create');
        }

        // Determina il numero massimo di giocatori in base al formato
        $maxPlayers = 10; // Default per 5vs5
        if ($format === '7vs7') {
            $maxPlayers = 14;
        } elseif ($format === '8vs8') {
            $maxPlayers = 16;
        }

        $matchModel = new SoccerMatch();
        $matchData = [
            'host_username' => $_SESSION['user']['username'],
            'date' => $date,
            'time' => $time,
            'format' => $format,
            'max_players' => $maxPlayers,
            'location' => $location,
            'visibility' => $visibility,
            'total_cost' => $totalCost,
            'status' => 'open'
        ];

        if ($matchModel->create($matchData)) {
            $_SESSION['success'] = "Partita creata con successo!";
            $this->redirect('/matches');
        } else {
            $_SESSION['error'] = "Impossibile creare la partita. Riprova.";
            $this->redirect('/matches/create');
        }
    }

    public function showReport($id) {
        $matchModel = new SoccerMatch();
        $match = $matchModel->find($id);

        if (!$match) {
            http_response_code(404);
            echo "Partita non trovata.";
            return;
        }

        $username = $_SESSION['user']['username'] ?? null;
        if ($match['host_username'] !== $username) {
            $_SESSION['error'] = "Non sei l'organizzatore di questa partita.";
            $this->redirectToMatch($id);
        }

        if ($match['status'] !== 'finished') {
            $_SESSION['error'] = "La partita deve essere conclusa per poterne compilare il tabellino.";
            $this->redirectToMatch($id);
        }

        $matchDateTime = strtotime($match['date'] . ' ' . $match['time']);
        $isWithin24Hours = (time() < $matchDateTime + 24 * 3600);
        $canCompileOrEdit = ($match['result_home'] === null) || $isWithin24Hours;
        if (!$canCompileOrEdit) {
            $_SESSION['error'] = "Tempo scaduto per compilare il tabellino (limite 24 ore).";
            $this->redirectToMatch($id);
        }

        $db = \App\Database::getInstance()->getConnection();
        $stmtReg = $db->prepare("
            SELECT r.*, u.name, u.avatar, u.preferred_role, u.trust_score, u.skill_rating 
            FROM registrations r
            JOIN users u ON r.username = u.username
            WHERE r.match_id = :match_id
            ORDER BY r.created_at ASC
        ");
        $stmtReg->execute(['match_id' => $id]);
        $registrations = $stmtReg->fetchAll(\PDO::FETCH_ASSOC);

        $home_team = array_filter($registrations, function($reg) {
            return $reg['team'] === 'home' && $reg['status'] === 'registered';
        });
        $away_team = array_filter($registrations, function($reg) {
            return $reg['team'] === 'away' && $reg['status'] === 'registered';
        });
        $unassigned = array_filter($registrations, function($reg) {
            return empty($reg['team']) && $reg['status'] === 'registered';
        });

        view('matches/report', [
            'title' => 'Compila Tabellino - AlmaKick',
            'match' => $match,
            'home_team' => $home_team,
            'away_team' => $away_team,
            'unassigned' => $unassigned
        ]);
    }

    public function storeReport($id) {
        $this->validateCsrf();
        $matchModel = new SoccerMatch();
        $match = $matchModel->find($id);

        if (!$match) {
            http_response_code(404);
            echo "Partita non trovata.";
            return;
        }

        $username = $_SESSION['user']['username'] ?? null;
        if ($match['host_username'] !== $username) {
            $_SESSION['error'] = "Non sei l'organizzatore di questa partita.";
            $this->redirectToMatch($id);
        }

        if ($match['status'] !== 'finished') {
            $_SESSION['error'] = "La partita deve essere conclusa per poterne compilare il tabellino.";
            $this->redirectToMatch($id);
        }

        $matchDateTime = strtotime($match['date'] . ' ' . $match['time']);
        $isWithin24Hours = (time() < $matchDateTime + 24 * 3600);
        $canCompileOrEdit = ($match['result_home'] === null) || $isWithin24Hours;
        if (!$canCompileOrEdit) {
            $_SESSION['error'] = "Tempo scaduto per compilare il tabellino (limite 24 ore).";
            $this->redirectToMatch($id);
        }

        $result_home = isset($_POST['result_home']) ? max(0, (int)$_POST['result_home']) : 0;
        $result_away = isset($_POST['result_away']) ? max(0, (int)$_POST['result_away']) : 0;
        $goals = $_POST['goals'] ?? [];

        $db = \App\Database::getInstance()->getConnection();
        try {
            $db->beginTransaction();

            $stmtUpdateMatch = $db->prepare("UPDATE matches SET result_home = :home, result_away = :away, updated_at = NOW() WHERE id = :id");
            $stmtUpdateMatch->execute([
                'home' => $result_home,
                'away' => $result_away,
                'id' => $id
            ]);

            $stmtUpdateReg = $db->prepare("UPDATE registrations SET goals_scored = :goals, updated_at = NOW() WHERE id = :id AND match_id = :match_id");
            foreach ($goals as $regId => $goalsCount) {
                $goalsCount = max(0, (int)$goalsCount);
                $stmtUpdateReg->execute([
                    'goals' => $goalsCount,
                    'id' => $regId,
                    'match_id' => $id
                ]);
            }

            $db->commit();
            $_SESSION['success'] = "Tabellino salvato con successo!";
        } catch (\Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            $_SESSION['error'] = "Errore durante il salvataggio del tabellino: " . $e->getMessage();
        }

        $this->redirectToMatch($id);
    }

    private function redirectToMatch($id) {
        $from = $_GET['from'] ?? $_POST['from'] ?? '';
        $redirectUrl = '/matches/' . $id;
        if ($from !== '') {
            $redirectUrl .= '?from=' . urlencode($from);
        }
        $this->redirect(url($redirectUrl));
    }
}
