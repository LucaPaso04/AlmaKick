<section class="register-container row justify-content-center mt-4 mb-5">
    <div class="col-11 col-sm-10 col-md-8 col-lg-6">
        <div class="card shadow border-0 rounded-4 register-card">
            <div class="card-body p-4 p-md-5">
                <div class="text-center mb-4">
                    <h1 class="fw-bold fs-3">Crea un Account</h1>
                    <p class="text-secondary">Unisciti a AlmaKick e inizia a giocare</p>
                </div>

                <form action="<?= url('/register') ?>" method="POST" class="register-form">
                    <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">

                    <div class="form-floating mb-3">
                        <input type="text" 
                               class="form-control bg-body-tertiary border-0" 
                               id="fullname" 
                               name="fullname"
                               value="<?= e($_SESSION['old_fullname'] ?? '') ?>" 
                               placeholder="Mario Rossi" 
                               required>
                        <label for="fullname">Nome Completo</label>
                    </div>

                    <div class="form-floating mb-3">
                        <input type="email" 
                               class="form-control bg-body-tertiary border-0" 
                               id="email" 
                               name="email"
                               value="<?= e($_SESSION['old_email'] ?? '') ?>" 
                               placeholder="mario@email.com" 
                               required>
                        <label for="email">Indirizzo Email</label>
                    </div>

                    <div class="form-floating mb-3">
                        <input type="tel" 
                               class="form-control bg-body-tertiary border-0" 
                               id="phone" 
                               name="phone"
                               value="<?= e($_SESSION['old_phone'] ?? '') ?>" 
                               placeholder="3331234567" 
                               required>
                        <label for="phone">Telefono (Emergenza)</label>
                    </div>

                    <div class="form-floating mb-3">
                        <select class="form-select bg-body-tertiary border-0" id="preferred_role" name="preferred_role">
                            <option value="Jolly" <?= ($_SESSION['old_preferred_role'] ?? 'Jolly') == 'Jolly' ? 'selected' : '' ?>>Jolly</option>
                            <option value="Portiere" <?= ($_SESSION['old_preferred_role'] ?? '') == 'Portiere' ? 'selected' : '' ?>>Portiere</option>
                            <option value="Difensore" <?= ($_SESSION['old_preferred_role'] ?? '') == 'Difensore' ? 'selected' : '' ?>>Difensore</option>
                            <option value="Centrocampista" <?= ($_SESSION['old_preferred_role'] ?? '') == 'Centrocampista' ? 'selected' : '' ?>>Centrocampista</option>
                            <option value="Attaccante" <?= ($_SESSION['old_preferred_role'] ?? '') == 'Attaccante' ? 'selected' : '' ?>>Attaccante</option>
                        </select>
                        <label for="preferred_role">Ruolo in Campo Preferito</label>
                    </div>

                    <div class="form-floating mb-4 position-relative">
                        <input type="password" 
                               class="form-control bg-body-tertiary border-0" 
                               id="password"
                               name="password" 
                               placeholder="Password" 
                               required 
                               minlength="6">
                        <label for="password">Password</label>
                        <button type="button" class="password-toggle" aria-label="Mostra password">
                            <span class="bi bi-eye" aria-hidden="true"></span>
                        </button>
                        <div class="form-text mt-2 ms-1">Minimo 6 caratteri</div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-3 fw-bold rounded-3 shadow-sm register-btn">
                        Registrati
                    </button>

                    <div class="text-center mt-4">
                        <span class="text-secondary">Hai già un account? </span>
                        <a href="<?= url('/login') ?>" class="text-decoration-none fw-semibold link-primary">Accedi qui</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
<?php 
    // Pulisci i vecchi valori dopo averli mostrati
    unset($_SESSION['old_fullname']);
    unset($_SESSION['old_email']);
    unset($_SESSION['old_phone']);
    unset($_SESSION['old_preferred_role']);
?>
