<?php
// Avvia la sessione con impostazioni sicure
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 0); // Impostare a 1 in produzione con HTTPS
ini_set('session.use_only_cookies', 1);

session_start();

// Rigenera ID di sessione periodicamente per evitare session fixation
if (!isset($_SESSION['last_regeneration'])) {
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
} elseif (time() - $_SESSION['last_regeneration'] > 1800) { // 30 min
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
}

// Includi file di configurazione
require_once __DIR__ . '/../config.php';

// Autoloader PSR-4 personalizzato per evitare dipendenza forzata da Composer all'avvio
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../src/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

// Funzione helper per includere le viste
function view($viewName, $data = []) {
    extract($data);
    $viewFile = VIEW_PATH . '/' . $viewName . '.php';
    if (file_exists($viewFile)) {
        // Avvia il buffering dell'output
        ob_start();
        require $viewFile;
        $content = ob_get_clean();
        // Carica il layout principale passando il contenuto
        require VIEW_PATH . '/layout.php';
    } else {
        echo "Vista $viewName non trovata.";
    }
}

// Generazione Token CSRF se non esiste
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Inizializza il Router
$router = new \App\Router();

// DEFINIZIONE DELLE ROTTE
$router->add('GET', '/', 'HomeController@index');
$router->add('GET', '/welcome', 'WelcomeController@index');
$router->add('GET', '/login', 'AuthController@showLogin');
$router->add('POST', '/login', 'AuthController@login');
$router->add('GET', '/register', 'AuthController@showRegister');
$router->add('POST', '/register', 'AuthController@register');
$router->add('POST', '/logout', 'AuthController@logout');

$router->add('GET', '/matches', 'MatchController@index', [\App\Middleware\AuthMiddleware::class]);
$router->add('GET', '/matches/create', 'MatchController@create', [\App\Middleware\AuthMiddleware::class]);
$router->add('POST', '/matches', 'MatchController@store', [\App\Middleware\AuthMiddleware::class]);
$router->add('GET', '/matches/{id}', 'MatchController@show', [\App\Middleware\AuthMiddleware::class]);
$router->add('POST', '/matches/{id}/join', 'MatchController@join', [\App\Middleware\AuthMiddleware::class]);
$router->add('POST', '/matches/{id}/leave', 'MatchController@leave', [\App\Middleware\AuthMiddleware::class]);
$router->add('POST', '/matches/{id}/generate-teams', 'MatchController@generateTeams', [\App\Middleware\AuthMiddleware::class]);
$router->add('POST', '/matches/{id}/close', 'MatchController@close', [\App\Middleware\AuthMiddleware::class]);
$router->add('POST', '/matches/{id}/cancel', 'MatchController@cancel', [\App\Middleware\AuthMiddleware::class]);
$router->add('POST', '/matches/{id}/set-mvp-deadline', 'MatchController@setMvpDeadline', [\App\Middleware\AuthMiddleware::class]);
$router->add('POST', '/matches/{id}/vote', 'MatchController@vote', [\App\Middleware\AuthMiddleware::class]);
$router->add('GET', '/matches/{id}/report', 'MatchController@showReport', [\App\Middleware\AuthMiddleware::class]);
$router->add('POST', '/matches/{id}/report', 'MatchController@storeReport', [\App\Middleware\AuthMiddleware::class]);

// ROTTE PROFILO E AMICIZIE
$router->add('GET', '/profile', 'UserController@show', [\App\Middleware\AuthMiddleware::class]);
$router->add('POST', '/profile/avatar', 'UserController@updateAvatar', [\App\Middleware\AuthMiddleware::class]);
$router->add('POST', '/profile/info', 'UserController@updateInfo', [\App\Middleware\AuthMiddleware::class]);
$router->add('POST', '/friends/add', 'UserController@addFriend', [\App\Middleware\AuthMiddleware::class]);
$router->add('POST', '/friends/accept/{username}', 'UserController@acceptFriend', [\App\Middleware\AuthMiddleware::class]);
$router->add('POST', '/friends/reject/{username}', 'UserController@rejectFriend', [\App\Middleware\AuthMiddleware::class]);
$router->add('POST', '/friends/block/{username}', 'UserController@blockFriend', [\App\Middleware\AuthMiddleware::class]);
$router->add('POST', '/friends/remove/{username}', 'UserController@removeFriend', [\App\Middleware\AuthMiddleware::class]);

// ROTTE AMMINISTRATORE
$router->add('GET', '/admin', 'AdminController@index', [\App\Middleware\AuthMiddleware::class, \App\Middleware\AdminMiddleware::class]);
$router->add('POST', '/admin/ban', 'AdminController@ban', [\App\Middleware\AuthMiddleware::class, \App\Middleware\AdminMiddleware::class]);
$router->add('POST', '/admin/unban', 'AdminController@unban', [\App\Middleware\AuthMiddleware::class, \App\Middleware\AdminMiddleware::class]);
$router->add('POST', '/admin/reports/{id}/resolve', 'AdminController@resolveReport', [\App\Middleware\AuthMiddleware::class, \App\Middleware\AdminMiddleware::class]);
$router->add('POST', '/admin/reports/{id}/dismiss', 'AdminController@dismissReport', [\App\Middleware\AuthMiddleware::class, \App\Middleware\AdminMiddleware::class]);
$router->add('POST', '/admin/matches/cancel', 'AdminController@forceCancelMatch', [\App\Middleware\AuthMiddleware::class, \App\Middleware\AdminMiddleware::class]);
$router->add('POST', '/admin/matches/delete', 'AdminController@deleteMatch', [\App\Middleware\AuthMiddleware::class, \App\Middleware\AdminMiddleware::class]);

// Esegui il Router
$router->handle($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
