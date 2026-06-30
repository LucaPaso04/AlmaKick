<?php

namespace App\Controllers;

use App\Models\User;

class LeaderboardController extends BaseController {
    
    public function index() {
        if (!isset($_SESSION['user'])) {
            $_SESSION['error'] = "Devi effettuare l'accesso per visualizzare le classifiche.";
            $this->redirect(url('/login'));
        }

        $userModel = new User();
        
        // Classifiche globali
        $topScorers = $userModel->getTopScorers(10);
        $topMVPs = $userModel->getTopMVPs(10);
        $topRated = $userModel->getTopRated(10);
        
        // Classifiche amici
        $friendsScorers = [];
        $friendsMVPs = [];
        $friendsRated = [];
        
        if (isset($_SESSION['user'])) {
            $username = $_SESSION['user']['username'];
            $friendsScorers = $userModel->getFriendsScorers($username);
            $friendsMVPs = $userModel->getFriendsMVPs($username);
            $friendsRated = $userModel->getFriendsRated($username);
        }
        
        view('leaderboard', [
            'title' => 'Classifiche - AlmaKick',
            'topScorers' => $topScorers,
            'topMVPs' => $topMVPs,
            'topRated' => $topRated,
            'friendsScorers' => $friendsScorers,
            'friendsMVPs' => $friendsMVPs,
            'friendsRated' => $friendsRated
        ]);
    }
}
