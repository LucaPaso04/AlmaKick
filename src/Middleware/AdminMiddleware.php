<?php

namespace App\Middleware;

class AdminMiddleware {
    public function handle(): bool {
        if (!isset($_SESSION['user'])) {
            $_SESSION['error'] = "Accesso negato. Effettua prima il login.";
            header("Location: " . url('/login'));
            exit;
        }
        if ($_SESSION['user']['role'] !== 'super_admin') {
            $_SESSION['error'] = "Accesso negato. Area riservata agli amministratori.";
            header("Location: " . url('/'));
            exit;
        }
        return true;
    }
}
