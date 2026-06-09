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
            'user_id' => $_SESSION['user']['id'] ?? null
        ];

        $matches = $matchModel->getAllActive($filters);

        view('matches/index', [
            'title' => 'Partite Disponibili - AlmaKick',
            'matches' => $matches
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
            'host_id' => $_SESSION['user']['id'],
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
