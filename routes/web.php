<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MatchController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\EvaluationController;
use App\Http\Controllers\ReportController;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::get('/', function () {
    if (Illuminate\Support\Facades\Auth::check()) {
        return app(App\Http\Controllers\MatchController::class)->index();
    }
    return view('welcome');
})->name('home');

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    Route::get('/matches/create', [MatchController::class, 'create'])->name('matches.create');
    Route::post('/matches', [MatchController::class, 'store'])->name('matches.store');
    Route::get('/matches/{id}', [MatchController::class, 'show'])->name('matches.show');
    
    Route::post('/matches/{id}/join', [MatchController::class, 'join'])->name('matches.join');
    Route::post('/matches/{id}/leave', [MatchController::class, 'leave'])->name('matches.leave');
    Route::post('/matches/{id}/cancel', [MatchController::class, 'cancel'])->name('matches.cancel');
    Route::post('/matches/{id}/generate-teams', [MatchController::class, 'generateTeams'])->name('matches.generateTeams');
    Route::post('/matches/{id}/close', [MatchController::class, 'closeMatch'])->name('matches.close');

    // Post-match: Report, Voting, MVP
    Route::get('/matches/{id}/report', [EvaluationController::class, 'showReport'])->name('matches.report');
    Route::post('/matches/{id}/report', [EvaluationController::class, 'storeReport'])->name('matches.storeReport');
    Route::post('/matches/{id}/vote', [EvaluationController::class, 'storeVote'])->name('matches.vote');
    Route::post('/matches/{id}/set-mvp-deadline', [EvaluationController::class, 'setMvpDeadline'])->name('matches.setMvpDeadline');

    Route::get('/profile', [UserController::class, 'profile'])->name('profile');
    Route::get('/user/{id}', [UserController::class, 'show'])->name('user.show');
    Route::post('/profile/avatar', [UserController::class, 'updateAvatar'])->name('profile.updateAvatar');
    Route::post('/profile/info', [UserController::class, 'updateInfo'])->name('profile.updateInfo');
    Route::post('/friends/add', [\App\Http\Controllers\FriendController::class, 'addFriend'])->name('friends.add');
    Route::post('/friends/{id}/accept', [\App\Http\Controllers\FriendController::class, 'acceptFriend'])->name('friends.accept');
    Route::post('/friends/{id}/reject', [\App\Http\Controllers\FriendController::class, 'rejectFriend'])->name('friends.reject');
    Route::post('/friends/{id}/remove', [\App\Http\Controllers\FriendController::class, 'removeFriend'])->name('friends.remove');
    Route::post('/friends/{id}/block', [\App\Http\Controllers\FriendController::class, 'blockUser'])->name('friends.block');
    Route::get('/leaderboard', [\App\Http\Controllers\LeaderboardController::class, 'index'])->name('leaderboard');
    Route::post('/reports', [ReportController::class, 'store'])->name('reports.store');

    Route::middleware('super_admin')->group(function () {
        Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');
        Route::post('/admin/ban', [AdminController::class, 'ban'])->name('admin.ban');
        Route::post('/admin/unban', [AdminController::class, 'unban'])->name('admin.unban');
        Route::post('/admin/delete-match', [AdminController::class, 'deleteMatch'])->name('admin.deleteMatch');
        Route::post('/admin/force-cancel', [AdminController::class, 'forceCancel'])->name('admin.forceCancel');
        
        Route::post('/admin/reports/{id}/resolve', [ReportController::class, 'resolve'])->name('admin.reports.resolve');
        Route::post('/admin/reports/{id}/dismiss', [ReportController::class, 'dismiss'])->name('admin.reports.dismiss');
    });
});