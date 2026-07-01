<?php
// Avvia sessione se non è già attiva
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$current_path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
if (defined('BASE_URL') && BASE_URL !== '') {
    $len = strlen(BASE_URL);
    if (substr($current_path, 0, $len) === BASE_URL) {
        $current_path = substr($current_path, $len);
    }
}
if (empty($current_path)) {
    $current_path = '/';
}

$isHomeActive = (isset($_SESSION['user']) && ($current_path === '/matches' || $current_path === '/')) || (!isset($_SESSION['user']) && $current_path === '/');

$userAvatar = $layoutUserAvatar ?? null;
$pendingRequestsCount = $layoutPendingRequestsCount ?? 0;
$pendingReportsCount = $layoutPendingReportsCount ?? 0;
$avatarUrl = null;

if ($userAvatar) {
    if (strpos($userAvatar, 'http://') === 0 || strpos($userAvatar, 'https://') === 0) {
        $avatarUrl = $userAvatar;
    } elseif (strpos($userAvatar, 'uploads/') === 0) {
        $avatarUrl = url('/' . $userAvatar);
    } else {
        $avatarUrl = url('/uploads/' . ltrim($userAvatar, '/'));
    }
}
?>
<!DOCTYPE html>
<html lang="it" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="AlmaKick - Organizza e trova partite di calcetto nella tua zona">
    <title><?= e($title ?? 'AlmaKick') ?></title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom Modular Stylesheet -->
    <link rel="stylesheet" href="<?= url('/css/style.css') ?>">
    <meta name="csrf-token" content="<?= e($_SESSION['csrf_token'] ?? '') ?>">
    <!-- Page-specific Styles -->
    <?php if ($current_path === '/'): ?>
        <link rel="stylesheet" href="<?= url('/css/welcome.css') ?>">
    <?php elseif ($current_path === '/login'): ?>
        <link rel="stylesheet" href="<?= url('/css/login.css') ?>">
    <?php elseif ($current_path === '/register'): ?>
        <link rel="stylesheet" href="<?= url('/css/register.css') ?>">
    <?php elseif ($current_path === '/leaderboard'): ?>
        <link rel="stylesheet" href="<?= url('/css/leaderboard.css') ?>">
    <?php elseif ($current_path === '/users'): ?>
        <link rel="stylesheet" href="<?= url('/css/users.css') ?>">
    <?php endif; ?>
</head>
<body class="d-flex flex-column min-vh-100">

    <!-- Scroll Progress Bar -->
    <div id="scroll-progress-container">
        <div id="scroll-progress-bar"></div>
    </div>

    <!-- Toast Container per notifiche fluttuanti -->
    <div class="custom-toast-container" id="toast-container">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="custom-toast toast-success" role="alert" data-duration="4500">
                <div class="custom-toast-content">
                    <span class="bi bi-check-circle-fill fs-5"></span>
                    <span><?= e($_SESSION['success']) ?></span>
                </div>
                <button type="button" class="btn-close-toast" aria-label="Chiudi avviso">&times;</button>
                <div class="custom-toast-progress"></div>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="custom-toast toast-danger" role="alert">
                <div class="custom-toast-content">
                    <span class="bi bi-exclamation-triangle-fill fs-5"></span>
                    <span><?= e($_SESSION['error']) ?></span>
                </div>
                <button type="button" class="btn-close-toast" aria-label="Chiudi avviso">&times;</button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
    </div>

    <header>
        <nav class="navbar navbar-expand sticky-top border-bottom">
            <div class="container-fluid px-3 px-md-4">
                <!-- Brand Logo & Home Link (Top Left) -->
                <div class="d-flex align-items-center gap-2">
                    <a href="<?= url('/') ?>" class="navbar-brand py-0 d-flex align-items-center gap-2" aria-label="AlmaKick Home">
                        <span class="logo-bg-wrapper <?= $isHomeActive ? 'logo-active' : '' ?>">
                            <!-- Logo monogramma per mobile -->
                            <img src="<?= url('/images/logo.svg') ?>" alt="AlmaKick Logo" class="header-logo-mobile d-md-none">
                            <!-- Logo completo per desktop -->
                            <img src="<?= url('/images/logo-text.svg') ?>" alt="AlmaKick Logo" class="header-logo-desktop d-none d-md-block">
                        </span>
                        <span class="fw-bold fs-5 <?= $isHomeActive ? 'text-primary' : 'text-body' ?>">Home</span>
                    </a>

                    <?php 
                        $isClassificheActive = ($current_path === '/leaderboard');
                        $isCercaActive = ($current_path === '/users');
                    ?>
                    <?php if (isset($_SESSION['user'])): ?>
                        <!-- Classifica con effetto hover reveal elegante -->
                        <a class="nav-link fw-semibold p-0 text-decoration-none search-hover-reveal ms-3 d-none d-md-inline-flex <?= $isClassificheActive ? 'text-primary' : '' ?>" href="<?= url('/leaderboard') ?>" aria-label="Visualizza Classifica">
                            <span class="bi bi-trophy-fill fs-5 <?= $isClassificheActive ? 'text-warning' : 'text-body' ?>"></span>
                            <span class="search-text-reveal fw-semibold text-primary">Classifica</span>
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Right side -->
                <div class="d-flex align-items-center gap-2 gap-sm-3 ms-auto">
                    
                    <?php if (isset($_SESSION['user'])): ?>
                        <!-- Lente d'ingrandimento per la ricerca con effetto hover reveal elegante -->
                        <a class="btn btn-link p-0 text-decoration-none search-hover-reveal d-none d-md-inline-flex <?= $isCercaActive ? 'text-primary' : 'text-body' ?>" href="<?= url('/users') ?>" aria-label="Cerca Giocatori">
                            <span class="bi bi-search fs-5 <?= $isCercaActive ? 'text-primary' : 'text-body' ?>"></span>
                            <span class="search-text-reveal fw-semibold text-primary">Cerca</span>
                        </a>
                    <?php endif; ?>

                    <!-- Theme Toggle (Invertito con la lente, posizionato dopo la lente) -->
                    <button class="btn btn-link text-body p-0 text-decoration-none" id="theme-toggle" aria-label="Cambia tema">
                        <span class="bi bi-sun-fill fs-5 transition-transform" id="theme-icon"></span>
                    </button>

                    <?php if (isset($_SESSION['user'])): ?>
                        <!-- Campana Notifiche Premium -->
                        <div class="dropdown notifications-dropdown-wrapper">
                            <button class="btn btn-link text-body p-0 text-decoration-none position-relative" 
                                    id="notificationsBell" 
                                    data-bs-toggle="dropdown" 
                                    aria-expanded="false" 
                                    aria-label="Notifiche">
                                <span class="bi bi-bell fs-5 transition-transform" id="notificationsBellIcon"></span>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-2 border-white d-none" id="notificationsBadge" style="font-size: 0.6rem; padding: 0.25em 0.4em;">
                                    0
                                </span>
                            </button>
                            
                            <div class="dropdown-menu dropdown-menu-end shadow border-0 mt-2 p-0 notifications-dropdown fade-down" aria-labelledby="notificationsBell" style="width: 320px; max-width: 90vw;">
                                 <div class="notifications-header d-flex justify-content-between align-items-center p-3 border-bottom border-secondary-subtle">
                                    <h6 class="fw-bold mb-0 text-body" style="font-size: 0.95rem;">Notifiche</h6>
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-link btn-sm text-decoration-none p-0 text-primary fw-semibold d-none" id="markAllReadBtn" style="font-size: 0.8rem;">
                                            Segna come lette
                                        </button>
                                        <span class="text-secondary small d-none" id="notificationsHeaderDivider">|</span>
                                        <button class="btn btn-link btn-sm text-decoration-none p-0 text-danger fw-semibold d-none" id="clearAllBtn" style="font-size: 0.8rem;">
                                            Svuota tutto
                                        </button>
                                    </div>
                                </div>
                                <div class="notifications-list" id="notificationsList" style="max-height: 350px; overflow-y: auto;">
                                    <div class="text-center py-4 text-muted small" id="notificationsEmpty">
                                        <i class="bi bi-bell-slash fs-4 d-block mb-1 opacity-50"></i>
                                        Nessuna nuova notifica
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Avatar User Dropdown (Profilo) -->
                        <div class="dropdown d-none d-md-block">
                            <button class="btn btn-link p-0 position-relative text-decoration-none dropdown-toggle d-flex align-items-center gap-2 border-0 bg-transparent"
                                type="button" id="userMenuDropdown" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Menu utente">
                                
                                <span class="position-relative d-inline-block">
                                    <?php if($userAvatar): ?>
                                        <img src="<?= htmlspecialchars($avatarUrl) ?>" alt="Il tuo Avatar" class="rounded-circle object-fit-cover shadow-sm transition-transform hover-scale user-avatar-img">
                                    <?php else: ?>
                                        <span class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center shadow-sm transition-transform hover-scale user-avatar-fallback">
                                            <?= strtoupper(substr($_SESSION['user']['name'], 0, 1)) ?>
                                        </span>
                                    <?php endif; ?>
                                </span>
                            </button>
                            
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2 fade-down user-dropdown-menu" aria-labelledby="userMenuDropdown">
                                <li>
                                    <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="<?= url('/profile') ?>">
                                        <span class="bi bi-person fs-5 text-primary"></span> <span class="fw-medium">Il mio Profilo</span>
                                        <?php if($pendingRequestsCount > 0): ?>
                                            <span class="badge rounded-pill bg-danger ms-auto">
                                                <?= $pendingRequestsCount ?>
                                            </span>
                                        <?php endif; ?>
                                    </a>
                                </li>
                                <?php if($_SESSION['user']['role'] === 'super_admin'): ?>
                                    <li>
                                        <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="<?= url('/admin') ?>">
                                            <span class="bi bi-shield-lock fs-5 text-warning"></span> <span class="fw-medium">Dashboard Admin</span>
                                            <?php if($pendingReportsCount > 0): ?>
                                                <span class="badge rounded-pill bg-danger ms-auto">
                                                    <?= $pendingReportsCount ?>
                                                </span>
                                            <?php endif; ?>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider border-secondary-subtle my-1"></li>
                                <li>
                                    <form action="<?= url('/logout') ?>" method="POST" class="m-0">
                                        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token'] ?? '') ?>">
                                        <button type="submit" class="dropdown-item d-flex align-items-center gap-2 py-2 text-danger fw-medium bg-transparent border-0 w-100 text-start">
                                            <span class="bi bi-box-arrow-right fs-5"></span> Esci
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a href="<?= url('/login') ?>" class="btn btn-link text-body fw-semibold text-decoration-none px-2 px-sm-3 transition-colors hover-text-primary">Accedi</a>
                        <a href="<?= url('/register') ?>" class="btn btn-primary rounded-pill px-3 px-sm-4 shadow-sm">Registrati</a>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    </header>

    <main class="container py-4 mb-5 pb-5">
        <!-- Contenuto dinamico della pagina -->
        <?= $content ?>
    </main>

    <footer class="bg-body-tertiary border-top py-4 mt-auto pb-5 pb-lg-4">
        <div class="container">
            <div class="row align-items-center gy-4">
                <!-- Left Column: Logo & Tagline -->
                <div class="col-12 col-md-8 text-center text-md-start">
                    <div class="mb-3 d-inline-flex">
                        <span class="logo-bg-wrapper">
                            <!-- Logo monogramma per mobile -->
                            <img src="<?= url('/images/logo.svg') ?>" alt="AlmaKick Logo" class="header-logo-mobile d-md-none">
                            <!-- Logo completo per desktop -->
                            <img src="<?= url('/images/logo-text.svg') ?>" alt="AlmaKick Logo" class="header-logo-desktop d-none d-md-block">
                        </span>
                    </div>
                    <p class="text-body-secondary small mb-0 footer-tagline">La migliore piattaforma per organizzare e trovare partite di calcetto nella tua zona. Scendi in campo con noi!</p>
                </div>
                <?php if (isset($_SESSION['user'])): ?>
                    <!-- Right Column: Links aligned horizontally on desktop -->
                    <div class="col-12 col-md-4 text-center text-md-end">
                        <h2 class="h6 fw-semibold mb-3">Link Utili</h2>
                        <ul class="list-unstyled small mb-0 d-flex flex-column flex-md-row justify-content-md-end gap-3 align-items-center">
                            <li><a href="<?= url('/matches') ?>" class="text-body-secondary text-decoration-none hover-text-primary transition-colors">Esplora Partite</a></li>
                            <li><a href="<?= url('/leaderboard') ?>" class="text-body-secondary text-decoration-none hover-text-primary transition-colors">Classifiche</a></li>
                            <li><a href="<?= url('/profile') ?>" class="text-body-secondary text-decoration-none hover-text-primary transition-colors">Il mio Profilo</a></li>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
            <hr class="my-4 border-secondary-subtle">
            <div class="text-center text-body-secondary small">
                &copy; <?= date('Y') ?> AlmaKick. Tutti i diritti riservati.
            </div>
        </div>
    </footer>

    <?php if (isset($_SESSION['user'])): ?>
        <?php 
            $isHomeActive = ($current_path === '/' || $current_path === '' || strpos($current_path, '/matches') === 0);
            $isCercaActive = ($current_path === '/users');
            $isClassificheActive = ($current_path === '/leaderboard');
            $isProfiloActive = ($current_path === '/profile');
        ?>
        <!-- BOTTOM BAR MOBILE GLASSMORPHISM -->
        <div class="d-lg-none fixed-bottom border-top shadow-lg pb-safe bottom-mobile-bar">
            <nav aria-label="Menu navigazione principale">
                <ul class="nav nav-pills nav-justified py-2 align-items-center">
                    <li class="nav-item">
                        <a href="<?= url('/') ?>" class="nav-link flex-column d-flex align-items-center <?= $isHomeActive ? 'text-primary fw-bold' : 'text-secondary' ?>" aria-current="<?= $isHomeActive ? 'page' : 'false' ?>">
                            <div class="position-relative transition-transform <?= $isHomeActive ? 'scale-110' : '' ?>">
                                <span class="bi bi-calendar-event<?= $isHomeActive ? '-fill text-primary' : '' ?> fs-4"></span>
                            </div>
                            <small class="mt-1 mobile-nav-label<?= $isHomeActive ? ' active' : '' ?>">Partite</small>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="<?= url('/leaderboard') ?>" class="nav-link flex-column d-flex align-items-center <?= $isClassificheActive ? 'text-warning fw-bold' : 'text-secondary' ?>" aria-current="<?= $isClassificheActive ? 'page' : 'false' ?>">
                            <div class="position-relative transition-transform <?= $isClassificheActive ? 'scale-110' : '' ?>">
                                <span class="bi bi-trophy<?= $isClassificheActive ? '-fill text-warning' : '' ?> fs-4"></span>
                            </div>
                            <small class="mt-1 mobile-nav-label<?= $isClassificheActive ? ' active' : '' ?>">Classifica</small>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="<?= url('/users') ?>" class="nav-link flex-column d-flex align-items-center <?= $isCercaActive ? 'text-primary fw-bold' : 'text-secondary' ?>" aria-current="<?= $isCercaActive ? 'page' : 'false' ?>">
                            <div class="position-relative transition-transform <?= $isCercaActive ? 'scale-110' : '' ?>">
                                <span class="bi bi-search fs-4 <?= $isCercaActive ? 'text-primary' : '' ?>"></span>
                            </div>
                            <small class="mt-1 mobile-nav-label<?= $isCercaActive ? ' active' : '' ?>">Cerca</small>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="<?= url('/profile') ?>" class="nav-link flex-column d-flex align-items-center <?= $isProfiloActive ? 'text-primary fw-bold' : 'text-secondary' ?>" aria-current="<?= $isProfiloActive ? 'page' : 'false' ?>">
                            <div class="position-relative transition-transform <?= $isProfiloActive ? 'scale-110' : '' ?>">
                                <?php if($userAvatar): ?>
                                    <img src="<?= htmlspecialchars($avatarUrl) ?>" alt="Avatar" class="rounded-circle object-fit-cover profile-nav-avatar <?= $isProfiloActive ? 'border border-2 border-primary shadow-sm' : '' ?>">
                                <?php else: ?>
                                    <span class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center shadow-sm profile-nav-avatar <?= $isProfiloActive ? 'border border-2 border-primary' : '' ?>" style="font-weight: 700; font-size: 0.95rem; width: 32px; height: 32px;">
                                        <?= strtoupper(substr($_SESSION['user']['name'], 0, 1)) ?>
                                    </span>
                                <?php endif; ?>
                                <?php // Il badge è centralizzato sulla campana per evitare ridondanze ?>
                            </div>
                            <small class="mt-1 mobile-nav-label<?= $isProfiloActive ? ' active' : '' ?>">Profilo</small>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    <?php endif; ?>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="<?= url('/js/app.js') ?>"></script>
    <!-- Floating Back to Top Button -->
    <button id="back-to-top" class="btn" aria-label="Torna in alto">
        <span class="bi bi-arrow-up fs-5"></span>
    </button>
</body>
</html>
