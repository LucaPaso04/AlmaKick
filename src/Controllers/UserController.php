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
            // Limit to 10 users
            $users = $userModel->searchUsers($q, $currentUsername, 10, 0);
        }

        // AJAX request
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
        
        // Get user parameter
        $username = $_GET['username'] ?? $_SESSION['user']['username'];
        $viewedUser = $userModel->find($username);

        if (!$viewedUser) {
            $_SESSION['error'] = "Utente non trovato.";
            $this->redirect('/');
        }

        $is_own_profile = ($username === $_SESSION['user']['username']);
        
        // Get friendship status
        $friendship = null;
        if (!$is_own_profile) {
            $friendship = $userModel->getFriendshipStatus($_SESSION['user']['username'], $username);
        }

        // Get stats and history
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
        $trend_votes = $userModel->getRecentVotesTrend($username);
        $sentPendingRequests = $userModel->getSentPendingRequests($username);

        $trust_score = (int)$viewedUser['trust_score'];
        if ($trust_score >= 90) {
            $ring_class = 'border-success text-success';
        } elseif ($trust_score >= 70) {
            $ring_class = 'border-warning text-warning';
        } else {
            $ring_class = 'border-danger text-danger';
        }

        if (!$is_own_profile) {
            // Calculate helpers for public profile
            $is_friend = ($friendship && $friendship['status'] === 'accepted');
            $sent_request = ($friendship && $friendship['status'] === 'pending' && $friendship['sender_username'] === $_SESSION['user']['username']);
            $received_request = ($friendship && $friendship['status'] === 'pending' && $friendship['sender_username'] !== $_SESSION['user']['username']);
            
            $currentUsername = $_SESSION['user']['username'];
            $me = $userModel->find($currentUsername);
            $mutual_friends = $userModel->getMutualFriends($currentUsername, $username);
            $matches_played_together = $userModel->getMatchesPlayedTogetherCount($currentUsername, $username);
            
            view('profile/public', [
                'title' => 'Profilo di ' . e($viewedUser['name']) . ' - AlmaKick',
                'user' => $viewedUser,
                'me' => $me,
                'is_friend' => $is_friend,
                'sent_request' => $sent_request,
                'received_request' => $received_request,
                'friendship' => $friendship,
                'matches_played' => $viewedUser['matches_played'] ?? 0,
                'friends_count' => count($friends),
                'trust_score' => $trust_score,
                'trend_votes' => $trend_votes,
                'mutual_friends' => $mutual_friends,
                'matches_played_together' => $matches_played_together
            ]);
        } else {
            view('profile/index', [
                'title' => 'Profilo di ' . e($viewedUser['name']) . ' - AlmaKick',
                'user' => $viewedUser,
                'is_own_profile' => $is_own_profile,
                'friendship' => $friendship,
                'matches_hosted' => $matches_hosted,
                'matchHistory' => $matchHistory,
                'pendingRequests' => $pendingRequests,
                'sentPendingRequests' => $sentPendingRequests,
                'friends' => $friends,
                'trust_score' => $trust_score,
                'ring_class' => $ring_class,
                'trend_votes' => $trend_votes
            ]);
        }
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

            // Create uploads dir if missing
            if (!is_dir(UPLOAD_PATH)) {
                mkdir(UPLOAD_PATH, 0755, true);
            }

            $newFileName = md5(time() . $username) . '.' . $fileExtension;
            $dest_path = UPLOAD_PATH . '/' . $newFileName;

            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                // Remove old avatar
                if ($currentUser && $currentUser['avatar']) {
                    $oldPath = BASE_PATH . '/public/' . ltrim($currentUser['avatar'], '/');
                    if (file_exists($oldPath) && is_file($oldPath)) {
                        unlink($oldPath);
                    }
                }

                // Save path to DB
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

        // Update password
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

        // Update email
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

            // Check email availability
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

        // Update profile details
        if (isset($_POST['name'])) {
            $name = trim($_POST['name']);
            $lastName = trim($_POST['last_name'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $preferredRole = trim($_POST['preferred_role'] ?? 'Jolly');

            if (empty($name)) {
                $_SESSION['error'] = "Il campo Nome è obbligatorio.";
                $this->redirect('/profile');
            }
            if (empty($lastName)) {
                $_SESSION['error'] = "Il campo Cognome è obbligatorio.";
                $this->redirect('/profile');
            }

            $userModel->updateInfo($username, [
                'name' => $name,
                'last_name' => $lastName,
                'phone' => $phone ? $phone : null,
                'preferred_role' => $preferredRole
            ]);

            $_SESSION['user']['name'] = $name; // aggiorna sessione
            $_SESSION['user']['last_name'] = $lastName; // aggiorna sessione
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
                if ($existing['sender_username'] === $myUsername) {
                    $_SESSION['error'] = "C'è già una richiesta di amicizia in sospeso.";
                } else {
                    // Accept automatically if pending
                    $userModel->acceptFriendRequest($recipient['username'], $myUsername);
                    
                    // Notify original sender
                    $notificationModel = new \App\Models\Notification();
                    $notificationModel->create([
                        'user_recipient' => $recipient['username'],
                        'type' => 'friend_accept',
                        'message' => '🤝 ' . $_SESSION['user']['name'] . ' (@' . $myUsername . ') ha accettato la tua richiesta di amicizia!',
                        'link' => url('/profile?username=' . urlencode($myUsername))
                    ]);
                    // Mark request as read
                    $notificationModel->markFriendRequestAsRead($recipient['username'], $myUsername);
                    
                    $_SESSION['success'] = "Richiesta di amicizia accettata automaticamente!";
                }
            } elseif ($existing['status'] === 'blocked') {
                $_SESSION['error'] = "Operazione non consentita.";
            } else {
                // Reactivate if rejected
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
            // Notify recipient
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

        // Accept request
        $userModel->acceptFriendRequest($username, $myUsername);

        // Notify sender
        $notificationModel = new \App\Models\Notification();
        $notificationModel->create([
            'user_recipient' => $username,
            'type' => 'friend_accept',
            'message' => '🤝 ' . $_SESSION['user']['name'] . ' (@' . $myUsername . ') ha accettato la tua richiesta di amicizia!',
            'link' => url('/profile?username=' . urlencode($myUsername))
        ]);

        // Mark request as read
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

        // Delete pending friendship
        $userModel->deleteFriendship($username, $myUsername);

        // Mark request as read
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

        // Mark request as read
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

        // Mark request as read
        $notificationModel = new \App\Models\Notification();
        $notificationModel->markFriendRequestAsRead($username, $myUsername);
        $notificationModel->markFriendRequestAsRead($myUsername, $username);

        $_SESSION['success'] = "Amico rimosso.";
        $this->redirect('/profile');
    }

    public function storeReport() {
        $this->validateCsrf();

        if (!isset($_SESSION['user'])) {
            $_SESSION['error'] = "Accesso negato.";
            $this->redirect('/login');
        }

        $reporter = $_SESSION['user']['username'];
        $reported = trim($_POST['reported_username'] ?? $_POST['reported_id'] ?? '');
        $reason = trim($_POST['reason'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if (empty($reported) || empty($reason)) {
            $_SESSION['error'] = "Campi obbligatori mancanti per la segnalazione.";
            $this->redirect('/profile');
        }

        // Validate reported user exists
        $userModel = new User();
        $viewedUser = $userModel->find($reported);
        if (!$viewedUser) {
            $_SESSION['error'] = "Giocatore da segnalare non trovato.";
            $this->redirect('/profile');
        }

        $matchId = isset($_POST['match_id']) && $_POST['match_id'] !== '' ? (int)$_POST['match_id'] : null;

        $db = \App\Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            INSERT INTO reports (reporter_username, reported_username, match_id, reason, description, status, created_at, updated_at)
            VALUES (:reporter, :reported, :match_id, :reason, :description, 'pending', NOW(), NOW())
        ");
        
        $success = $stmt->execute([
            'reporter' => $reporter,
            'reported' => $reported,
            'match_id' => $matchId,
            'reason' => $reason,
            'description' => $description
        ]);

        if ($success) {
            $_SESSION['success'] = "Segnalazione inviata con successo agli amministratori.";
        } else {
            $_SESSION['error'] = "Impossibile inviare la segnalazione.";
        }

        $this->redirect('/profile?username=' . urlencode($reported));
    }
}
