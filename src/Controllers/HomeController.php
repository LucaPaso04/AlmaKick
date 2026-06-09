<?php

namespace App\Controllers;

class HomeController extends BaseController {
    public function index() {
        view('home', [
            'title' => 'AlmaKick - Home',
            'tagline' => 'La piattaforma per le tue partite di calcetto amatoriali!'
        ]);
    }
}
