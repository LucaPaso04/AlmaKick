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

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $_SESSION['error'] = "Tutti i campi sono obbligatori.";
            $_SESSION['old_email'] = $email;
            $this->redirect('/login');
        }

        $userModel = new User();
        $user = $userModel->findByEmail($email);

        if ($user && password_verify($password, $user['password'])) {
            if ($user['is_banned']) {
                $_SESSION['error'] = "Questo account è stato sospeso o bannato.";
                $_SESSION['old_email'] = $email;
                $this->redirect('/login');
            }

            // Pulisci i vecchi valori e imposta sessione utente
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
            $_SESSION['old_email'] = $email;
            $this->redirect('/login');
        }
    }

    public function showRegister() {
        if (isset($_SESSION['user'])) {
            $this->redirect('/');
        }
        view('register', ['title' => 'Registrati - AlmaKick']);
    }

    public function register() {
        $this->validateCsrf();

        $fullname = trim($_POST['fullname'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $preferred_role = trim($_POST['preferred_role'] ?? 'Jolly');
        $password = $_POST['password'] ?? '';

        // Salva i vecchi valori per ripopolare il form in caso di errore
        $_SESSION['old_fullname'] = $fullname;
        $_SESSION['old_email'] = $email;
        $_SESSION['old_phone'] = $phone;
        $_SESSION['old_preferred_role'] = $preferred_role;

        if (empty($fullname) || empty($email) || empty($phone) || empty($password)) {
            $_SESSION['error'] = "I campi Nome Completo, Email, Telefono e Password sono obbligatori.";
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

        // Dividi il nome completo in name e last_name
        $nameParts = explode(' ', trim($fullname), 2);
        $name = $nameParts[0];
        $lastName = $nameParts[1] ?? $nameParts[0];

        // Genera username da email (parte prima dell'@)
        $username = strtolower(explode('@', $email)[0]);
        $baseUsername = $username;
        $counter = 1;
        while ($userModel->find($username)) {
            $username = $baseUsername . $counter;
            $counter++;
        }

        // Genera friend code univoco di 6 caratteri alfanumerici
        $friendCode = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));

        $userData = [
            'username' => $username,
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
            // Pulisci i vecchi valori
            unset($_SESSION['old_fullname']);
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
        unset($_SESSION['user']);
        session_destroy();
        
        // Ricrea sessione per mostrare un eventuale messaggio o permettere nuove operazioni CSRF
        session_start();
        $_SESSION['success'] = "Disconnessione effettuata con successo.";
        $this->redirect('/login');
    }
}
