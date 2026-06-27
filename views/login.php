<section class="login-container row justify-content-center mt-5 mb-5 align-items-center">
    <div class="col-11 col-sm-8 col-md-6 col-lg-5">
        <div class="card shadow border-0 rounded-4 login-card">
            <div class="card-body p-4 p-md-5">
                <div class="text-center mb-4">
                    <div class="mb-3">
                        <span class="logo-bg-wrapper login-icon-bg">
                            <img src="<?= url('/images/logo-text.svg') ?>" alt="AlmaKick Logo" class="form-logo">
                        </span>
                    </div>
                    <h2 class="fw-bold fs-3 mt-2">Bentornato</h2>
                    <p class="text-secondary">Accedi a AlmaKick</p>
                </div>

                <form action="<?= url('/login') ?>" method="POST" class="login-form">
                    <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">

                    <div class="form-floating mb-3">
                        <input type="email" 
                               class="form-control bg-body-tertiary border-0 shadow-none" 
                               id="email"
                               name="email" 
                               value="<?= e($_SESSION['old_email'] ?? '') ?>" 
                               placeholder="nome@esempio.com" 
                               required>
                        <label for="email">Indirizzo Email</label>
                    </div>

                    <div class="form-floating mb-4">
                        <input type="password" 
                               class="form-control bg-body-tertiary border-0 shadow-none" 
                               id="password"
                               name="password" 
                               placeholder="Password" 
                               required>
                        <label for="password">Password</label>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-3 fw-bold rounded-3 shadow-sm login-btn">
                        Accedi <i class="bi bi-box-arrow-in-right ms-1"></i>
                    </button>

                    <div class="text-center mt-4">
                        <span class="text-secondary">Non hai un account? </span>
                        <a href="<?= url('/register') ?>" class="text-decoration-none fw-semibold link-primary">Registrati ora</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
<?php 
    // Pulisci il valore vecchio dopo averlo mostrato
    unset($_SESSION['old_email']);
?>
