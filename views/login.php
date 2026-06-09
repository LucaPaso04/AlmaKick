<div class="row justify-content-center my-5">
    <div class="col-12 col-md-8 col-lg-5">
        <div class="glass-panel p-4 p-md-5">
            <div class="text-center mb-4">
                <span class="logo-bg-wrapper p-2 rounded-3">
                    <img src="<?= url('/images/logo-text.svg') ?>" alt="AlmaKick" class="form-logo img-fluid">
                </span>
            </div>
            <h2 class="text-center fw-bold mb-4">Accedi a AlmaKick</h2>
            <form action="<?= url('/login') ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                
                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold text-secondary-custom">Email</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="inserisci la tua email" required>
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label fw-semibold text-secondary-custom">Password</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="inserisci la tua password" required>
                </div>

                <button type="submit" class="btn btn-primary w-100">Accedi</button>
            </form>
            <p class="mt-4 mb-0 text-center text-secondary-custom">
                Non hai un account? <a href="<?= url('/register') ?>" class="text-accent text-decoration-none">Registrati qui</a>
            </p>
        </div>
    </div>
</div>
