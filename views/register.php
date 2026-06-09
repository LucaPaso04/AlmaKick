<div class="row justify-content-center my-5">
    <div class="col-12 col-md-8 col-lg-6">
        <div class="glass-panel p-4 p-md-5">
            <div class="text-center mb-4">
                <span class="logo-bg-wrapper p-2 rounded-3">
                    <img src="<?= url('/images/logo-text.svg') ?>" alt="AlmaKick" class="form-logo img-fluid">
                </span>
            </div>
            <h2 class="text-center fw-bold mb-4">Registrati su AlmaKick</h2>
            <form action="<?= url('/register') ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                
                <div class="mb-3">
                    <label for="name" class="form-label fw-semibold text-secondary-custom">Nome Completo</label>
                    <input type="text" id="name" name="name" class="form-control" placeholder="es. Mario Rossi" required>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold text-secondary-custom">Indirizzo Email</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="mario.rossi@example.com" required>
                </div>

                <div class="mb-3">
                    <label for="phone" class="form-label fw-semibold text-secondary-custom">Telefono (opzionale)</label>
                    <input type="text" id="phone" name="phone" class="form-control" placeholder="es. 3331234567">
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label fw-semibold text-secondary-custom">Password</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="scegli una password sicura" required>
                </div>

                <button type="submit" class="btn btn-primary w-100">Registrati</button>
            </form>
            <p class="mt-4 mb-0 text-center text-secondary-custom">
                Hai già un account? <a href="<?= url('/login') ?>" class="text-accent text-decoration-none">Accedi qui</a>
            </p>
        </div>
    </div>
</div>
