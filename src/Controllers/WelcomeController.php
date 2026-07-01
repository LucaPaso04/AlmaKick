<?php

namespace App\Controllers;

class WelcomeController extends BaseController {
    public function index() {
        if (isset($_SESSION['user'])) {
            header('Location: ' . url('/matches'));
            exit;
        }
        view('welcome', [
            'title' => 'Benvenuto - AlmaKick'
        ]);
    }
}
