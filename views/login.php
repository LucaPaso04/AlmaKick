<section class="login-container row justify-content-center mt-5 mb-5 align-items-center">
    <div class="col-11 col-sm-8 col-md-6 col-lg-5">
        <div class="card shadow-sm border-0 rounded-4 login-card">
            <div class="card-body p-4 p-md-5">
                <div class="text-center mb-4">
                    <div class="mb-3">
                        <span class="logo-bg-wrapper login-icon-bg">
                            <img src="<?= url('/images/logo-text.svg') ?>" alt="AlmaKick Logo" class="form-logo">
                        </span>
                    </div>
                    <h1 class="fw-bold fs-3 mt-2">Bentornato</h1>
                    <p class="text-secondary">Accedi a AlmaKick</p>
                </div>

                <form action="<?= url('/login') ?>" method="POST" class="login-form">
                    <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">

                    <div class="mb-3 input-icon-group">
                        <span class="bi bi-person-fill form-icon" aria-hidden="true"></span>
                        <div class="form-floating w-100">
                            <input type="text"
                                   class="form-control bg-body-tertiary border-0"
                                   id="identifier"
                                   name="identifier"
                                   value="<?= e($_SESSION['old_identifier'] ?? $_SESSION['old_email'] ?? '') ?>"
                                   placeholder="Email o Username"
                                   autocomplete="username"
                                   required>
                            <label for="identifier">Email o Username</label>
                        </div>
                    </div>

                    <div class="mb-3 input-icon-group password-group">
                        <span class="bi bi-lock-fill form-icon" aria-hidden="true"></span>
                        <div class="form-floating w-100 position-relative">
                            <input type="password"
                                   class="form-control bg-body-tertiary border-0"
                                   id="password"
                                   name="password"
                                   placeholder="Password"
                                   autocomplete="current-password"
                                   required>
                            <label for="password">Password</label>
                            <button type="button" class="password-toggle" aria-label="Mostra password">
                                <span class="bi bi-eye" aria-hidden="true"></span>
                            </button>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="remember_me" name="remember_me" value="1">
                            <label class="form-check-label text-secondary" for="remember_me">Rimani connesso</label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-3 fw-bold rounded-3 shadow-sm login-btn">
                        Accedi <span class="bi bi-box-arrow-in-right ms-1"></span>
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
    unset($_SESSION['old_identifier']);
    unset($_SESSION['old_email']);
?>
