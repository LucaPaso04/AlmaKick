<?php

namespace App\Controllers;

class WelcomeController extends BaseController {
    public function index() {
        view('welcome', [
            'title' => 'Benvenuto - AlmaKick',
        ]);
    }
}
