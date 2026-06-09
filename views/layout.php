<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'AlmaKick') ?></title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Custom Modular Stylesheet -->
    <link rel="stylesheet" href="<?= url('/css/style.css') ?>">
</head>
<body>
    <header>
        <nav class="navbar navbar-expand-md navbar-dark bg-transparent py-3">
            <div class="container">
                <a href="<?= url('/') ?>" class="navbar-brand py-0 d-flex align-items-center">
                    <span class="logo-bg-wrapper">
                        <!-- Logo monogramma per mobile -->
                        <img src="<?= url('/images/logo.svg') ?>" alt="AlmaKick Logo" class="header-logo-mobile d-md-none">
                        <!-- Logo completo per desktop -->
                        <img src="<?= url('/images/logo-text.svg') ?>" alt="AlmaKick Logo" class="header-logo-desktop d-none d-md-block">
                    </span>
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <div class="navbar-nav ms-auto align-items-md-center gap-3 mt-3 mt-md-0">
                        <a class="nav-link text-white-50" href="<?= url('/matches') ?>">Partite</a>
                        <?php if (isset($_SESSION['user'])): ?>
                            <a class="nav-link text-white-50" href="<?= url('/matches/create') ?>">Crea Partita</a>
                            <span class="text-white nav-item">Ciao, <strong><?= e($_SESSION['user']['name']) ?></strong></span>
                            <form action="<?= url('/logout') ?>" method="POST" class="d-inline">
                                <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                                <button type="submit" class="btn btn-outline py-1 px-3">Esci</button>
                            </form>
                        <?php else: ?>
                            <a class="nav-link text-white-50" href="<?= url('/login') ?>">Accedi</a>
                            <a href="<?= url('/register') ?>" class="btn btn-primary">Registrati</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <main class="container py-4">
        <!-- Messaggi Flash di Successo o Errore -->
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error alert-dismissible fade show" role="alert">
                <?= e($_SESSION['error']) ?>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= e($_SESSION['success']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                <?php unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <!-- Contenuto dinamico della pagina -->
        <?= $content ?>
    </main>

    <footer>
        <p class="mb-0">&copy; <?= date('Y') ?> AlmaKick. Creato in PHP Vanilla puro.</p>
    </footer>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
