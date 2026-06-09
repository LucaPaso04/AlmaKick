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
            $this->redirect('/login');
        }

        $userModel = new User();
        $user = $userModel->findByEmail($email);

        if ($user && password_verify($password, $user['password'])) {
            if ($user['is_banned']) {
                $_SESSION['error'] = "Questo account è stato sospeso o bannato.";
                $this->redirect('/login');
            }

            // Imposta sessione utente
            $_SESSION['user'] = [
                'username' => $user['username'],
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => $user['role'],
                'friend_code' => $user['friend_code']
            ];
            
            $this->redirect('/');
        } else {
            $_SESSION['error'] = "Credenziali non valide.";
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

        $username = trim($_POST['username'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $lastName = trim($_POST['last_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $phone = trim($_POST['phone'] ?? '');

        if (empty($username) || empty($name) || empty($lastName) || empty($email) || empty($password)) {
            $_SESSION['error'] = "I campi Nome Utente, Nome, Cognome, Email e Password sono obbligatori.";
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

        if ($userModel->find($username)) {
            $_SESSION['error'] = "Questo Nome Utente è già registrato.";
            $this->redirect('/register');
        }

        // Genera friend code univoco di 6 caratteri alfanumerici
        $friendCode = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));

        $userData = [
            'username' => $username,
            'name' => $name,
            'last_name' => $lastName,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_BCRYPT),
            'phone' => $phone ? $phone : null,
            'friend_code' => $friendCode,
            'role' => 'user'
        ];

        if ($userModel->create($userData)) {
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
