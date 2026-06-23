<?php

namespace App\Controllers;

class HomeController extends BaseController {
    public function index() {
        if (isset($_SESSION['user'])) {
            header('Location: ' . url('/matches'));
            exit;
         }
         view('home', [
             'title' => 'AlmaKick - Home',
             'tagline' => 'La piattaforma per le tue partite di calcetto amatoriali!'
         ]);
    }
}
