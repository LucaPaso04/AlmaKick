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
            'hide_full' => $_GET['hide_full'] ?? null,
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

        view('matches/index', [
            'title' => 'Partite Disponibili - AlmaKick',
            'matches' => $matches,
            'totalPages' => $totalPages,
            'page' => $page
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

        view('matches/show', [
            'title' => 'Dettagli Partita - AlmaKick',
            'match' => $match
        ]);
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
}
