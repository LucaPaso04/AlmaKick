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

$userAvatar = null;
$avatarUrl = null;
$pendingRequestsCount = 0;
$pendingReportsCount = 0;

if (isset($_SESSION['user'])) {
    try {
        $db = \App\Database::getInstance()->getConnection();
        
        // Carica l'avatar dell'utente corrente dal DB
        $stmt = $db->prepare("SELECT avatar FROM users WHERE username = :username");
        $stmt->execute(['username' => $_SESSION['user']['username']]);
        $userAvatar = $stmt->fetchColumn();
        
        if ($userAvatar) {
            if (strpos($userAvatar, 'http://') === 0 || strpos($userAvatar, 'https://') === 0) {
                $avatarUrl = $userAvatar;
            } elseif (strpos($userAvatar, 'uploads/') === 0) {
                $avatarUrl = url('/' . $userAvatar);
            } else {
                $avatarUrl = url('/uploads/' . ltrim($userAvatar, '/'));
            }
        }

        // Carica il conteggio delle richieste di amicizia in attesa
        $stmtCount = $db->prepare("SELECT COUNT(*) FROM friendships WHERE recipient_username = :username AND status = 'pending'");
        $stmtCount->execute(['username' => $_SESSION['user']['username']]);
        $pendingRequestsCount = (int)$stmtCount->fetchColumn();

        // Carica il conteggio delle segnalazioni in attesa per l'admin
        if ($_SESSION['user']['role'] === 'super_admin') {
            $stmtReports = $db->prepare("SELECT COUNT(*) FROM reports WHERE status = 'pending'");
            $stmtReports->execute();
            $pendingReportsCount = (int)$stmtReports->fetchColumn();
        }
    } catch (\PDOException $e) {
        // Fallback silenzioso in caso di problemi col DB
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
</head>
<body class="d-flex flex-column min-vh-100">

    <header>
        <nav class="navbar navbar-expand sticky-top border-bottom" style="background-color: rgba(var(--bs-body-bg-rgb), 0.85) !important; backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px);">
            <div class="container-fluid px-3 px-md-4">
                <!-- Brand Logo (Clickable on both Desktop and Mobile) -->
                <a href="<?= url('/') ?>" class="navbar-brand py-0 d-flex align-items-center" aria-label="AlmaKick Home">
                    <span class="logo-bg-wrapper">
                        <!-- Logo monogramma per mobile -->
                        <img src="<?= url('/images/logo.svg') ?>" alt="AlmaKick Logo" class="header-logo-mobile d-md-none">
                        <!-- Logo completo per desktop -->
                        <img src="<?= url('/images/logo-text.svg') ?>" alt="AlmaKick Logo" class="header-logo-desktop d-none d-md-block">
                    </span>
                </a>

                <!-- Right side -->
                <div class="d-flex align-items-center gap-2 gap-sm-3 ms-auto">
                    
                    <?php if (isset($_SESSION['user'])): ?>
                        <?php 
                            $isCercaActive = ($current_path === '/users');
                            $isClassificheActive = ($current_path === '/leaderboard');
                        ?>
                        <!-- Desktop only links -->
                        <ul class="navbar-nav d-none d-lg-flex me-3">
                            <li class="nav-item">
                                <a class="nav-link fw-semibold px-3 py-2 rounded-pill transition-all <?= $isCercaActive ? 'bg-primary bg-opacity-10 text-primary' : 'text-body hover-bg-light' ?>" href="<?= url('/users') ?>" aria-label="Cerca Giocatori">
                                    <i class="bi bi-people-fill <?= $isCercaActive ? 'text-primary' : 'text-secondary' ?> me-1"></i> Cerca Giocatori
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link fw-semibold px-3 py-2 rounded-pill transition-all <?= $isClassificheActive ? 'bg-primary bg-opacity-10 text-primary' : 'text-body hover-bg-light' ?>" href="<?= url('/leaderboard') ?>" aria-label="Visualizza Classifiche">
                                    <i class="bi bi-trophy-fill <?= $isClassificheActive ? 'text-primary' : 'text-warning' ?> me-1"></i> Classifiche
                                </a>
                            </li>
                        </ul>
                    <?php endif; ?>

                    <!-- Theme Toggle -->
                    <button class="btn btn-link text-body p-0 text-decoration-none" id="theme-toggle" aria-label="Cambia tema">
                        <i class="bi bi-sun-fill fs-5 transition-transform" id="theme-icon"></i>
                    </button>

                    <?php if (isset($_SESSION['user'])): ?>
                        <!-- Avatar User Dropdown (Desktop Only) -->
                        <div class="dropdown d-none d-md-block">
                            <button class="btn btn-link p-0 position-relative text-decoration-none dropdown-toggle d-flex align-items-center gap-2 border-0 bg-transparent"
                                type="button" id="userMenuDropdown" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Menu utente">
                                
                                <span class="position-relative d-inline-block">
                                    <?php if($userAvatar): ?>
                                        <img src="<?= htmlspecialchars($avatarUrl) ?>" alt="Il tuo Avatar" class="rounded-circle object-fit-cover shadow-sm transition-transform hover-scale" style="width: 38px; height: 38px; border: 2px solid var(--bs-primary);">
                                    <?php else: ?>
                                        <span class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center shadow-sm transition-transform hover-scale" style="width: 38px; height: 38px; font-weight: 700; font-size: 1.1rem;">
                                            <?= strtoupper(substr($_SESSION['user']['name'], 0, 1)) ?>
                                        </span>
                                    <?php endif; ?>
                                    
                                    <?php if($pendingRequestsCount > 0): ?>
                                        <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-2 border-white rounded-circle shadow-sm" style="width: 14px; height: 14px;">
                                            <span class="visually-hidden"><?= $pendingRequestsCount ?> notifiche</span>
                                        </span>
                                    <?php endif; ?>
                                </span>
                            </button>
                            
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2 fade-down" aria-labelledby="userMenuDropdown" style="min-width: 200px; border-radius: 0.75rem;">
                                <li>
                                    <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="<?= url('/profile') ?>">
                                        <i class="bi bi-person fs-5 text-primary"></i> <span class="fw-medium">Il mio Profilo</span>
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
                                            <i class="bi bi-shield-lock fs-5 text-warning"></i> <span class="fw-medium">Dashboard Admin</span>
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
                                            <i class="bi bi-box-arrow-right fs-5"></i> Esci
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
        <!-- Messaggi Flash di Successo o Errore -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 bg-success text-white rounded-4 mb-4 p-3 pe-5" role="alert">
                <i class="bi bi-check-circle-fill me-2 fs-5"></i> <?= e($_SESSION['success']) ?>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Chiudi avviso"></button>
                <?php unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 bg-danger text-white rounded-4 mb-4 p-3 pe-5" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i> <?= e($_SESSION['error']) ?>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Chiudi avviso"></button>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <!-- Contenuto dinamico della pagina -->
        <?= $content ?>
    </main>

    <!-- FOOTER -->
    <footer class="bg-body-tertiary border-top py-4 mt-auto pb-5 pb-lg-4">
        <div class="container">
            <div class="row gy-4">
                <div class="col-12 col-md-4">
                    <div class="mb-3">
                        <span class="logo-bg-wrapper">
                            <!-- Logo monogramma per mobile -->
                            <img src="<?= url('/images/logo.svg') ?>" alt="AlmaKick Logo" class="header-logo-mobile d-md-none">
                            <!-- Logo completo per desktop -->
                            <img src="<?= url('/images/logo-text.svg') ?>" alt="AlmaKick Logo" class="header-logo-desktop d-none d-md-block">
                        </span>
                    </div>
                    <p class="text-body-secondary small mb-0">La migliore piattaforma per organizzare e trovare partite di calcetto nella tua zona. Scendi in campo con noi!</p>
                </div>
                <div class="col-6 col-md-4">
                    <h3 class="h6 fw-semibold">Link Utili</h3>
                    <ul class="list-unstyled small mb-0">
                        <li class="mb-2"><a href="<?= url('/') ?>" class="text-body-secondary text-decoration-none hover-text-primary transition-colors">Esplora Partite</a></li>
                        <li class="mb-2"><a href="<?= url('/leaderboard') ?>" class="text-body-secondary text-decoration-none hover-text-primary transition-colors">Classifiche</a></li>
                        <?php if (isset($_SESSION['user'])): ?>
                            <li><a href="<?= url('/profile') ?>" class="text-body-secondary text-decoration-none hover-text-primary transition-colors">Il mio Profilo</a></li>
                        <?php else: ?>
                            <li><a href="<?= url('/login') ?>" class="text-body-secondary text-decoration-none hover-text-primary transition-colors">Accedi</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="col-6 col-md-4">
                    <h3 class="h6 fw-semibold">Contatti</h3>
                    <ul class="list-unstyled small mb-0">
                        <li class="mb-2 text-body-secondary"><i class="bi bi-envelope me-2"></i>info@almakick.it</li>
                        <li class="text-body-secondary"><i class="bi bi-instagram me-2"></i>@almakick</li>
                    </ul>
                </div>
            </div>
            <hr class="my-4 border-secondary-subtle">
            <div class="text-center text-body-secondary small">
                &copy; <?= date('Y') ?> AlmaKick. Tutti i diritti riservati.
            </div>
        </div>
    </footer>

    <?php if (isset($_SESSION['user'])): ?>
        <?php 
            $isHomeActive = ($current_path === '/' || strpos($current_path, '/matches') === 0);
            $isCercaActive = ($current_path === '/users');
            $isClassificheActive = ($current_path === '/leaderboard');
            $isProfiloActive = ($current_path === '/profile');
        ?>
        <!-- BOTTOM BAR MOBILE GLASSMORPHISM -->
        <div class="d-lg-none fixed-bottom border-top shadow-lg pb-safe" style="background-color: rgba(var(--bs-body-bg-rgb), 0.85) !important; backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); z-index: 1030;">
            <nav aria-label="Menu navigazione principale">
                <ul class="nav nav-pills nav-justified py-2 align-items-center">
                    <li class="nav-item">
                        <a href="<?= url('/') ?>" class="nav-link flex-column d-flex align-items-center <?= $isHomeActive ? 'text-primary fw-bold' : 'text-secondary' ?>" aria-current="<?= $isHomeActive ? 'page' : 'false' ?>">
                            <div class="position-relative transition-transform <?= $isHomeActive ? 'scale-110' : '' ?>">
                                <i class="bi bi-house-door<?= $isHomeActive ? '-fill' : '' ?> fs-4"></i>
                            </div>
                            <small class="mt-1" style="font-size: 0.7rem; font-weight: <?= $isHomeActive ? '700' : '500' ?>">Home</small>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="<?= url('/users') ?>" class="nav-link flex-column d-flex align-items-center <?= $isCercaActive ? 'text-primary fw-bold' : 'text-secondary' ?>" aria-current="<?= $isCercaActive ? 'page' : 'false' ?>">
                            <div class="position-relative transition-transform <?= $isCercaActive ? 'scale-110' : '' ?>">
                                <i class="bi bi-people<?= $isCercaActive ? '-fill' : '' ?> fs-4"></i>
                            </div>
                            <small class="mt-1" style="font-size: 0.7rem; font-weight: <?= $isCercaActive ? '700' : '500' ?>">Cerca</small>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="<?= url('/leaderboard') ?>" class="nav-link flex-column d-flex align-items-center <?= $isClassificheActive ? 'text-warning fw-bold' : 'text-secondary' ?>" aria-current="<?= $isClassificheActive ? 'page' : 'false' ?>">
                            <div class="position-relative transition-transform <?= $isClassificheActive ? 'scale-110' : '' ?>">
                                <i class="bi bi-trophy<?= $isClassificheActive ? '-fill text-warning' : '' ?> fs-4"></i>
                            </div>
                            <small class="mt-1" style="font-size: 0.7rem; font-weight: <?= $isClassificheActive ? '700' : '500' ?>">Top 10</small>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= url('/profile') ?>" class="nav-link flex-column d-flex align-items-center <?= $isProfiloActive ? 'text-primary fw-bold' : 'text-secondary' ?>" aria-current="<?= $isProfiloActive ? 'page' : 'false' ?>">
                            <div class="position-relative transition-transform <?= $isProfiloActive ? 'scale-110' : '' ?>">
                                <?php if($userAvatar): ?>
                                    <img src="<?= htmlspecialchars($avatarUrl) ?>" alt="Avatar" class="rounded-circle object-fit-cover <?= $isProfiloActive ? 'border border-2 border-primary shadow-sm' : '' ?>" style="width: 26px; height: 26px;">
                                <?php else: ?>
                                    <i class="bi bi-person<?= $isProfiloActive ? '-fill' : '' ?> fs-4"></i>
                                <?php endif; ?>
                                <?php if($pendingRequestsCount > 0): ?>
                                    <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-2 border-white rounded-circle shadow-sm" style="width: 12px; height: 12px;">
                                        <span class="visually-hidden">Notifiche</span>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <small class="mt-1" style="font-size: 0.7rem; font-weight: <?= $isProfiloActive ? '700' : '500' ?>">Profilo</small>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    <?php endif; ?>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Theme toggle logic
            const themeToggle = document.getElementById('theme-toggle');
            const themeIcon = document.getElementById('theme-icon');
            const htmlEl = document.documentElement;

            // Load saved theme or default to dark
            const savedTheme = localStorage.getItem('theme') || 'dark';
            htmlEl.setAttribute('data-bs-theme', savedTheme);
            updateThemeIcon(savedTheme);

            if (themeToggle) {
                themeToggle.addEventListener('click', function() {
                    const currentTheme = htmlEl.getAttribute('data-bs-theme');
                    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
                    htmlEl.setAttribute('data-bs-theme', newTheme);
                    localStorage.setItem('theme', newTheme);
                    updateThemeIcon(newTheme);
                });
            }

            function updateThemeIcon(theme) {
                if (!themeIcon) return;
                if (theme === 'dark') {
                    themeIcon.className = 'bi bi-sun-fill fs-5 transition-transform';
                } else {
                    themeIcon.className = 'bi bi-moon-fill fs-5 transition-transform';
                }
            }

            // Auto-dismiss alerts after 4.5 seconds with smooth fade out
            setTimeout(function() {
                let alerts = document.querySelectorAll('.alert-dismissible');
                alerts.forEach(function(alert) {
                    let bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
                    if (bsAlert) {
                        bsAlert.close();
                    }
                });
            }, 4500);
        });
    </script>
</body>
</html>

