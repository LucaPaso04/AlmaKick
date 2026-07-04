<section class="register-container">
    <div class="row g-0 register-split">
        <div class="col-md-6 register-side register-side-left d-none d-md-flex align-items-center justify-content-center">
            <div class="register-side-content text-white text-center px-4">
                <span class="register-side-badge">Benvenuto in AlmaKick</span>
                <h1 class="register-side-title fw-bold">Registrati, trova la tua squadra e diventa l'MVP della serata</h1>
                <p class="register-side-copy">Gioca con stile, costruisci il tuo profilo e scopri le serate perfette per il tuo ruolo in campo.</p>
            </div>
        </div>

        <div class="col-12 col-md-6 register-side register-side-right d-flex align-items-center justify-content-center">
            <div class="register-card shadow-sm rounded-4">
                <div class="card-body p-4 p-md-5">
                    <div class="text-center mb-4">
                        <p class="text-uppercase text-primary fw-semibold small mb-2">Nuovo account</p>
                        <h1 class="fw-bold fs-3">Crea il tuo profilo AlmaKick</h1>
                        <p class="text-secondary">Scegli il tuo username, personalizza il ruolo e unisciti al campo.</p>
                    </div>

                    <form action="<?= url('/register') ?>" method="POST" class="register-form">
                        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">

                        <div class="form-section mb-4">
                            <div class="section-label"> Dati Personali</div>

                            <div class="row gx-3">
                                <div class="col-sm-6">
                                    <div class="mb-3 input-icon-group">
                                        <span class="bi bi-person-fill form-icon" aria-hidden="true"></span>
                                        <div class="form-floating w-100">
                                            <input type="text"
                                                   class="form-control bg-body-tertiary border-0"
                                                   id="name"
                                                   name="name"
                                                   value="<?= e($_SESSION['old_name'] ?? '') ?>"
                                                   placeholder="Mario"
                                                   required>
                                            <label for="name">Nome</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="mb-3 input-icon-group">
                                        <span class="bi bi-person-lines-fill form-icon" aria-hidden="true"></span>
                                        <div class="form-floating w-100">
                                            <input type="text"
                                                   class="form-control bg-body-tertiary border-0"
                                                   id="last_name"
                                                   name="last_name"
                                                   value="<?= e($_SESSION['old_last_name'] ?? '') ?>"
                                                   placeholder="Rossi"
                                                   required>
                                            <label for="last_name">Cognome</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3 input-icon-group">
                                <span class="bi bi-envelope form-icon" aria-hidden="true"></span>
                                <div class="form-floating w-100">
                                    <input type="email"
                                           class="form-control bg-body-tertiary border-0"
                                           id="email"
                                           name="email"
                                           value="<?= e($_SESSION['old_email'] ?? '') ?>"
                                           placeholder="mario@email.com"
                                           required>
                                    <label for="email">Indirizzo Email</label>
                                </div>
                            </div>

                            <div class="mb-3 input-icon-group">
                                <span class="bi bi-telephone form-icon" aria-hidden="true"></span>
                                <div class="form-floating w-100">
                                    <input type="tel"
                                           class="form-control bg-body-tertiary border-0"
                                           id="phone"
                                           name="phone"
                                           value="<?= e($_SESSION['old_phone'] ?? '') ?>"
                                           placeholder="3331234567"
                                           required>
                                    <label for="phone">Telefono</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-section mb-4">
                            <div class="section-label"> Profilo Giocatore</div>

                            <div class="mb-3 input-icon-group">
                                <span class="bi bi-person-badge form-icon" aria-hidden="true"></span>
                                <div class="form-floating w-100">
                                    <input type="text"
                                           class="form-control bg-body-tertiary border-0"
                                           id="username"
                                           name="username"
                                           value="<?= e($_SESSION['old_username'] ?? '') ?>"
                                           placeholder="mariorossi"
                                           minlength="3"
                                           maxlength="20"
                                           required>
                                    <label for="username">Username</label>
                                </div>
                            </div>

                            <div class="mb-3 input-icon-group">
                                <span class="bi bi-flag form-icon" aria-hidden="true"></span>
                                <div class="form-floating w-100">
                                    <select class="form-select bg-body-tertiary border-0"
                                            id="preferred_role"
                                            name="preferred_role">
                                        <option value="" selected disabled hidden>Scegli il tuo ruolo</option>
                                        <option value="Jolly">Jolly</option>
                                        <option value="Portiere">Portiere</option>
                                        <option value="Difensore">Difensore</option>
                                        <option value="Centrocampista">Centrocampista</option>
                                        <option value="Attaccante">Attaccante</option>
                                    </select>
                                    <label for="preferred_role">Ruolo in Campo Preferito</label>
                                </div>
                            </div>

                            <div class="mb-2 input-icon-group password-group">
                                <span class="bi bi-shield-lock form-icon" aria-hidden="true"></span>
                                <div class="form-floating w-100 position-relative">
                                    <input type="password"
                                           class="form-control bg-body-tertiary border-0"
                                           id="password"
                                           name="password"
                                           placeholder="Password"
                                           required
                                           minlength="6"
                                           aria-describedby="password-strength-text">
                                    <label for="password">Password</label>
                                    <button type="button" class="password-toggle" aria-label="Mostra password">
                                        <span class="bi bi-eye" aria-hidden="true"></span>
                                    </button>
                                </div>
                            </div>

                            <div class="password-strength" aria-hidden="true">
                                <div class="password-strength-bar"></div>
                            </div>
                            <div id="password-strength-text" class="form-text password-strength-text">Inserisci almeno 6 caratteri.</div>
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
    </div>
</section>
<script src="<?= url('/js/passwordchecker.js') ?>"></script>
<?php 
    // Clear old input values
    unset($_SESSION['old_name']);
    unset($_SESSION['old_last_name']);
    unset($_SESSION['old_username']);
    unset($_SESSION['old_email']);
    unset($_SESSION['old_phone']);
    unset($_SESSION['old_preferred_role']);
?>
