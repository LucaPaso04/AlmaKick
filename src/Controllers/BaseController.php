<?php

namespace App\Controllers;

abstract class BaseController {
    // Redirect helper
    protected function redirect(string $url): void {
        header("Location: " . $url);
        exit;
    }

    // Validate CSRF
    protected function validateCsrf(): bool {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                http_response_code(403);
                echo "Errore di validazione CSRF (Token non valido o scaduto).";
                exit;
            }
        }
        return true;
    }
}
