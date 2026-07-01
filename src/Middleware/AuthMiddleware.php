<?php

namespace App\Middleware;

class AuthMiddleware {
    public function handle(): bool {
        if (!isset($_SESSION['user'])) {
            $_SESSION['error'] = "Accesso negato. Effettua prima il login.";
            header("Location: " . url('/login'));
            exit;
        }
        return true;
    }
}
