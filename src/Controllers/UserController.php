<?php

namespace App\Controllers;

use App\Models\User;

class UserController extends BaseController {

    public function index() {
        if (!isset($_SESSION['user'])) {
            $_SESSION['error'] = "Accesso negato. Effettua prima il login.";
            $this->redirect('/login');
        }

        $userModel = new User();
        $q = trim($_GET['q'] ?? '');

        $users = [];
        if ($q !== '') {
            $currentUsername = $_SESSION['user']['username'];
            // Cerca al massimo 10 utenti
            $users = $userModel->searchUsers($q, $currentUsername, 10, 0);
        }

        // Se la richiesta è AJAX, restituisce JSON con i risultati
        if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
            ob_start();
            require VIEW_PATH . '/users/partials/results.php';
            $htmlContent = ob_get_clean();

            header('Content-Type: application/json');
            echo json_encode([
                'html' => $htmlContent,
                'pagination' => ''
            ]);
            exit;
        }

        view('users/index', [
            'title' => 'Ricerca Giocatori - AlmaKick',
            'users' => $users,
            'totalPages' => 0,
            'page' => 1,
            'q' => $q
        ]);
    }


    public function show() {
        if (!isset($_SESSION['user'])) {
            $_SESSION['error'] = "Accesso negato. Effettua prima il login.";
            $this->redirect('/login');
        }

        $userModel = new User();
        
        // Determina l'utente da visualizzare
        $username = $_GET['username'] ?? $_SESSION['user']['username'];
        $viewedUser = $userModel->find($username);

        if (!$viewedUser) {
            $_SESSION['error'] = "Utente non trovato.";
            $this->redirect('/');
        }

        $is_own_profile = ($username === $_SESSION['user']['username']);
        
        // Recupera lo stato dell'amicizia
        $friendship = null;
        if (!$is_own_profile) {
            $friendship = $userModel->getFriendshipStatus($_SESSION['user']['username'], $username);
        }

        // Recupera le statistiche e lo storico
        $matches_hosted = $userModel->getMatchesHostedCount($username);
        $rawHistory = $userModel->getMatchHistory($username);

        $matchHistory = [];
        foreach ($rawHistory as $row) {
            $matchHistory[] = [
                'id' => $row['id'],
                'username' => $row['username'],
                'status' => $row['status'],
                'match' => [
                    'id' => $row['match_id'],
                    'date' => $row['date'],
                    'time' => $row['time'],
                    'location' => $row['location'],
                    'format' => $row['format'],
                    'result_home' => $row['result_home'],
                    'result_away' => $row['result_away']
                ]
            ];
        }

        $pendingRequests = $userModel->getPendingRequests($username);
        $friends = $userModel->getFriends($username);

        $trust_score = (int)$viewedUser['trust_score'];
        if ($trust_score >= 90) {
            $ring_class = 'border-success text-success';
        } elseif ($trust_score >= 70) {
            $ring_class = 'border-warning text-warning';
        } else {
            $ring_class = 'border-danger text-danger';
        }

        view('profile', [
            'title' => 'Profilo di ' . e($viewedUser['name']) . ' - AlmaKick',
            'user' => $viewedUser,
            'is_own_profile' => $is_own_profile,
            'friendship' => $friendship,
            'matches_hosted' => $matches_hosted,
            'matchHistory' => $matchHistory,
            'pendingRequests' => $pendingRequests,
            'friends' => $friends,
            'trust_score' => $trust_score,
            'ring_class' => $ring_class
        ]);
    }

    public function updateAvatar() {
        $this->validateCsrf();

        if (!isset($_SESSION['user'])) {
            $_SESSION['error'] = "Accesso negato.";
            $this->redirect('/login');
        }

        $username = $_SESSION['user']['username'];
        $userModel = new User();
        $currentUser = $userModel->find($username);

        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['avatar']['tmp_name'];
            $fileName = $_FILES['avatar']['name'];
            $fileSize = $_FILES['avatar']['size'];
            $fileNameCmps = explode(".", $fileName);
            $fileExtension = strtolower(end($fileNameCmps));

            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
            if (!in_array($fileExtension, $allowedExtensions)) {
                $_SESSION['error'] = "Estensione file non consentita. Scegli JPG, JPEG, PNG o WEBP.";
                $this->redirect('/profile');
            }

            if ($fileSize > 2 * 1024 * 1024) { // 2MB Limit
                $_SESSION['error'] = "Il file supera la dimensione massima consentita di 2MB.";
                $this->redirect('/profile');
            }

            // Crea la cartella uploads se non esiste
            if (!is_dir(UPLOAD_PATH)) {
                mkdir(UPLOAD_PATH, 0755, true);
            }

            $newFileName = md5(time() . $username) . '.' . $fileExtension;
            $dest_path = UPLOAD_PATH . '/' . $newFileName;

            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                // Rimuovi eventuale avatar precedente
                if ($currentUser && $currentUser['avatar']) {
                    $oldPath = BASE_PATH . '/public/' . ltrim($currentUser['avatar'], '/');
                    if (file_exists($oldPath) && is_file($oldPath)) {
                        unlink($oldPath);
                    }
                }

                // Salva il nuovo percorso relativo nel database
                $avatarRelativePath = 'uploads/' . $newFileName;
                $userModel->updateAvatar($username, $avatarRelativePath);
                $_SESSION['success'] = "Foto profilo aggiornata con successo!";
            } else {
                $_SESSION['error'] = "Errore durante il caricamento del file.";
            }
        } else {
            $_SESSION['error'] = "Nessun file selezionato o errore nell'invio.";
        }

        $this->redirect('/profile');
    }

    public function updateInfo() {
        $this->validateCsrf();

        if (!isset($_SESSION['user'])) {
            $_SESSION['error'] = "Accesso negato.";
            $this->redirect('/login');
        }

        $username = $_SESSION['user']['username'];
        $userModel = new User();
        $currentUser = $userModel->find($username);

        if (!$currentUser) {
            $_SESSION['error'] = "Utente non trovato.";
            $this->redirect('/');
        }

        // 1. Caso Cambio Password
        if (isset($_POST['password'])) {
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['password'] ?? '';
            $confirmPassword = $_POST['password_confirmation'] ?? '';

            if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
                $_SESSION['error'] = "Tutti i campi password sono obbligatori.";
                $this->redirect('/profile');
            }

            if (strlen($newPassword) < 6) {
                $_SESSION['error'] = "La nuova password deve essere di almeno 6 caratteri.";
                $this->redirect('/profile');
            }

            if ($newPassword !== $confirmPassword) {
                $_SESSION['error'] = "La nuova password e la conferma non coincidono.";
                $this->redirect('/profile');
            }

            if (!password_verify($currentPassword, $currentUser['password'])) {
                $_SESSION['error'] = "La password attuale non è corretta.";
                $this->redirect('/profile');
            }

            $passwordHash = password_hash($newPassword, PASSWORD_BCRYPT);
            $userModel->updatePassword($username, $passwordHash);
            $_SESSION['success'] = "Password aggiornata con successo.";
            $this->redirect('/profile');
        }

        // 2. Caso Cambio Email
        if (isset($_POST['email'])) {
            $newEmail = trim($_POST['email']);
            $currentPassword = $_POST['current_password'] ?? '';

            if (empty($newEmail) || empty($currentPassword)) {
                $_SESSION['error'] = "L'indirizzo email e la password attuale sono richiesti.";
                $this->redirect('/profile');
            }

            if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
                $_SESSION['error'] = "Formato indirizzo email non valido.";
                $this->redirect('/profile');
            }

            // Verifica se l'email è già in uso da un altro utente
            $existing = $userModel->findByEmail($newEmail);
            if ($existing && $existing['username'] !== $username) {
                $_SESSION['error'] = "Questo indirizzo email è già utilizzato da un altro account.";
                $this->redirect('/profile');
            }

            if (!password_verify($currentPassword, $currentUser['password'])) {
                $_SESSION['error'] = "Password di conferma non corretta.";
                $this->redirect('/profile');
            }

            $userModel->updateEmail($username, $newEmail);
            $_SESSION['user']['email'] = $newEmail; // aggiorna sessione
            $_SESSION['success'] = "Indirizzo email aggiornato con successo.";
            $this->redirect('/profile');
        }

        // 3. Caso Modifica Informazioni Generali
        if (isset($_POST['name'])) {
            $name = trim($_POST['name']);
            $phone = trim($_POST['phone'] ?? '');
            $preferredRole = trim($_POST['preferred_role'] ?? 'Jolly');

            if (empty($name)) {
                $_SESSION['error'] = "Il campo Nome e Cognome è obbligatorio.";
                $this->redirect('/profile');
            }

            $userModel->updateInfo($username, [
                'name' => $name,
                'phone' => $phone ? $phone : null,
                'preferred_role' => $preferredRole
            ]);

            $_SESSION['user']['name'] = $name; // aggiorna sessione
            $_SESSION['success'] = "Informazioni personali aggiornate con successo.";
            $this->redirect('/profile');
        }

        $this->redirect('/profile');
    }

    public function addFriend() {
        $this->validateCsrf();

        if (!isset($_SESSION['user'])) {
            $this->redirect('/login');
        }

        $friendCode = strtoupper(trim($_POST['friend_code'] ?? ''));
        if (empty($friendCode)) {
            $_SESSION['error'] = "Codice amico non valido.";
            $this->redirect('/profile');
        }

        $userModel = new User();
        $recipient = $userModel->findByFriendCode($friendCode);

        if (!$recipient) {
            $_SESSION['error'] = "Nessun giocatore trovato con questo codice amico.";
            $this->redirect('/profile');
        }

        $myUsername = $_SESSION['user']['username'];
        if ($recipient['username'] === $myUsername) {
            $_SESSION['error'] = "Non puoi aggiungere te stesso come amico.";
            $this->redirect('/profile');
        }

        $existing = $userModel->getFriendshipStatus($myUsername, $recipient['username']);
        $sent = false;
        if ($existing) {
            if ($existing['status'] === 'accepted') {
                $_SESSION['error'] = "Siete già amici.";
            } elseif ($existing['status'] === 'pending') {
                $_SESSION['error'] = "C'è già una richiesta di amicizia in attesa.";
            } elseif ($existing['status'] === 'blocked') {
                $_SESSION['error'] = "Operazione non consentita.";
            } else {
                // Se era rifiutata o altro, la riattiviamo
                $userModel->addFriendRequest($myUsername, $recipient['username']);
                $sent = true;
                $_SESSION['success'] = "Richiesta di amicizia inviata!";
            }
        } else {
            $userModel->addFriendRequest($myUsername, $recipient['username']);
            $sent = true;
            $_SESSION['success'] = "Richiesta di amicizia inviata!";
        }

        if ($sent) {
            // Crea una notifica persistente per il destinatario
            $notificationModel = new \App\Models\Notification();
            $notificationModel->create([
                'user_recipient' => $recipient['username'],
                'type' => 'friend_request',
                'message' => '👋 ' . $_SESSION['user']['name'] . ' (@' . $myUsername . ') ti ha inviato una richiesta di amicizia!',
                'link' => url('/profile?tab=social')
            ]);
        }

        $this->redirect('/profile');
    }

    public function acceptFriend($username) {
        $this->validateCsrf();

        if (!isset($_SESSION['user'])) {
            $this->redirect('/login');
        }

        $myUsername = $_SESSION['user']['username'];
        $userModel = new User();

        // Accetta la richiesta inviata da $username a me
        $userModel->acceptFriendRequest($username, $myUsername);

        // Notifica il mittente che la richiesta è stata accettata
        $notificationModel = new \App\Models\Notification();
        $notificationModel->create([
            'user_recipient' => $username,
            'type' => 'friend_accept',
            'message' => '🤝 ' . $_SESSION['user']['name'] . ' (@' . $myUsername . ') ha accettato la tua richiesta di amicizia!',
            'link' => url('/profile?username=' . urlencode($myUsername))
        ]);

        // Segna come letta la notifica di richiesta ricevuta
        $notificationModel->markFriendRequestAsRead($username, $myUsername);

        $_SESSION['success'] = "Richiesta di amicizia accettata!";
        $this->redirect('/profile');
    }

    public function rejectFriend($username) {
        $this->validateCsrf();

        if (!isset($_SESSION['user'])) {
            $this->redirect('/login');
        }

        $myUsername = $_SESSION['user']['username'];
        $userModel = new User();

        // Elimina record di amicizia in sospeso
        $userModel->deleteFriendship($username, $myUsername);

        // Segna come letta la notifica di richiesta ricevuta
        $notificationModel = new \App\Models\Notification();
        $notificationModel->markFriendRequestAsRead($username, $myUsername);

        $_SESSION['success'] = "Richiesta di amicizia rifiutata.";
        $this->redirect('/profile');
    }

    public function blockFriend($username) {
        $this->validateCsrf();

        if (!isset($_SESSION['user'])) {
            $this->redirect('/login');
        }

        $myUsername = $_SESSION['user']['username'];
        $userModel = new User();

        $userModel->blockUser($myUsername, $username);

        // Segna come letta la notifica di richiesta ricevuta (se c'era)
        $notificationModel = new \App\Models\Notification();
        $notificationModel->markFriendRequestAsRead($username, $myUsername);

        $_SESSION['success'] = "Giocatore bloccato.";
        $this->redirect('/profile');
    }

    public function removeFriend($username) {
        $this->validateCsrf();

        if (!isset($_SESSION['user'])) {
            $this->redirect('/login');
        }

        $myUsername = $_SESSION['user']['username'];
        $userModel = new User();

        $userModel->deleteFriendship($myUsername, $username);

        // Segna come letta la notifica di richiesta ricevuta (se c'era)
        $notificationModel = new \App\Models\Notification();
        $notificationModel->markFriendRequestAsRead($username, $myUsername);
        $notificationModel->markFriendRequestAsRead($myUsername, $username);

        $_SESSION['success'] = "Amico rimosso.";
        $this->redirect('/profile');
    }

}
