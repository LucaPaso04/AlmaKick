<?php

namespace App\Controllers;

use App\Models\User;

class AuthController extends BaseController {
    
    public function showLogin() {
        if (isset($_SESSION['user'])) {
            $this->redirect('/');
        }
        view('login', ['title' => 'Accedi - AlmaKick']);
    }

    public function login() {
        $this->validateCsrf();

        $identifier = trim($_POST['identifier'] ?? $_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $rememberMe = !empty($_POST['remember_me']);

        if (empty($identifier) || empty($password)) {
            $_SESSION['error'] = "Tutti i campi sono obbligatori.";
            $_SESSION['old_identifier'] = $identifier;
            $this->redirect('/login');
        }

        $userModel = new User();
        $user = $userModel->findByLoginIdentifier($identifier);

        if ($user && password_verify($password, $user['password'])) {
            if ($user['is_banned']) {
                $_SESSION['error'] = "Questo account è stato sospeso o bannato.";
                $_SESSION['old_identifier'] = $identifier;
                $this->redirect('/login');
            }

            if ($rememberMe) {
                $this->setRememberMeCookie($user['username']);
            } else {
                $this->clearRememberMeCookie();
            }

            unset($_SESSION['old_identifier']);
            unset($_SESSION['old_email']);
            $_SESSION['user'] = [
                'username' => $user['username'],
                'name' => $user['name'],
                'last_name' => $user['last_name'],
                'email' => $user['email'],
                'role' => $user['role'],
                'friend_code' => $user['friend_code']
            ];
            
            $this->redirect('/');
        } else {
            $_SESSION['error'] = "Credenziali non valide.";
            $_SESSION['old_identifier'] = $identifier;
            $this->redirect('/login');
        }
    }

    private function setRememberMeCookie(string $identifier): void {
        $payload = $identifier . '|' . hash_hmac('sha256', $identifier, APP_NAME);
        setcookie('remember_me', base64_encode($payload), time() + 60 * 60 * 24 * 30, '/', '', false, true);
    }

    private function clearRememberMeCookie(): void {
        setcookie('remember_me', '', time() - 3600, '/', '', false, true);
    }

    public function showRegister() {
        if (isset($_SESSION['user'])) {
            $this->redirect('/');
        }
        view('register', ['title' => 'Registrati - AlmaKick']);
    }

    public function register() {
        $this->validateCsrf();

        $name = trim($_POST['name'] ?? '');
        $lastName = trim($_POST['last_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $preferred_role = trim($_POST['preferred_role'] ?? 'Jolly');
        $password = $_POST['password'] ?? '';

        // Keep old input values
        $_SESSION['old_name'] = $name;
        $_SESSION['old_last_name'] = $lastName;
        $_SESSION['old_username'] = $username;
        $_SESSION['old_email'] = $email;
        $_SESSION['old_phone'] = $phone;
        $_SESSION['old_preferred_role'] = $preferred_role;

        if (empty($name) || empty($lastName) || empty($email) || empty($phone) || empty($username) || empty($password)) {
            $_SESSION['error'] = "I campi Nome, Cognome, Email, Telefono, Username e Password sono obbligatori.";
            $this->redirect('/register');
        }

        if (strlen($password) < 6) {
            $_SESSION['error'] = "La password deve avere almeno 6 caratteri.";
            $this->redirect('/register');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = "Formato email non valido.";
            $this->redirect('/register');
        }

        $userModel = new User();
        if ($userModel->findByEmail($email)) {
            $_SESSION['error'] = "Questa email è già registrata.";
            $this->redirect('/register');
        }

        // Ensure username is unique
        $baseUsername = strtolower($username);
        $counter = 1;
        $uniqueUsername = $baseUsername;
        while ($userModel->find($uniqueUsername)) {
            $uniqueUsername = $baseUsername . $counter;
            $counter++;
        }

        // Generate friend code
        $friendCode = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));

        $userData = [
            'username' => $uniqueUsername,
            'name' => $name,
            'last_name' => $lastName,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_BCRYPT),
            'phone' => $phone,
            'friend_code' => $friendCode,
            'role' => 'user',
            'preferred_role' => $preferred_role
        ];

        if ($userModel->createWithRole($userData)) {
            // Clear old input values
            unset($_SESSION['old_name']);
            unset($_SESSION['old_last_name']);
            unset($_SESSION['old_username']);
            unset($_SESSION['old_email']);
            unset($_SESSION['old_phone']);
            unset($_SESSION['old_preferred_role']);
            
            $_SESSION['success'] = "Registrazione completata! Ora puoi effettuare l'accesso.";
            $this->redirect('/login');
        } else {
            $_SESSION['error'] = "Errore durante la registrazione. Riprova.";
            $this->redirect('/register');
        }
    }

    public function logout() {
        $this->validateCsrf();
        $this->clearRememberMeCookie();
        unset($_SESSION['user']);
        session_destroy();
        
        // Restart session
        session_start();
        $_SESSION['success'] = "Disconnessione effettuata con successo.";
        $this->redirect('/login');
    }
}
