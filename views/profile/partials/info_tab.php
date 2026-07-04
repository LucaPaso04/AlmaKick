<div class="row justify-content-center g-4">
    <div class="col-12 col-lg-10">
        <div class="card shadow-sm border rounded-4 overflow-hidden">
            <div class="card-header bg-primary bg-opacity-10 border-bottom-0 p-4">
                <h5 class="fw-bold text-primary mb-0"><i class="bi bi-gear-fill me-2"></i>Gestione Account</h5>
            </div>

            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <!-- Profilo Base -->
                    <div class="list-group-item p-4 border-bottom">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold mb-0 text-uppercase text-muted settings-section-header">Informazioni Personali</h6>
                            <button class="btn btn-sm btn-outline-primary rounded-pill px-3 shadow-sm"
                                data-bs-toggle="modal" data-bs-target="#settingsModal" onclick="switchSettingsTab('modal-info-tab')">
                                <i class="bi bi-pencil-square me-1"></i>Modifica
                            </button>
                        </div>
                        <div class="row g-3">
                            <div class="col-12 col-sm-4">
                                <span class="d-block fw-semibold text-body mb-1">Nome</span>
                                <span class="text-muted"><?= e($user['name']) ?> <?= e($user['last_name'] ?? '') ?></span>
                            </div>
                            <div class="col-12 col-sm-4">
                                <span class="d-block fw-semibold text-body mb-1">Telefono</span>
                                <span class="text-muted"><?= $user['phone'] ? e($user['phone']) : '<em>Non inserito</em>' ?></span>
                            </div>
                            <div class="col-12 col-sm-4">
                                <span class="d-block fw-semibold text-body mb-1">Ruolo</span>
                                <span class="badge bg-primary bg-opacity-10 text-primary"><?= e($user['preferred_role'] ?? 'Jolly') ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Sicurezza -->
                    <div class="list-group-item p-4 border-bottom">
                        <h6 class="fw-bold mb-4 text-uppercase text-muted settings-section-header">Sicurezza e Accesso</h6>

                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center py-2 mb-3 border-bottom border-light">
                            <div class="mb-3 mb-md-0">
                                <span class="d-block fw-semibold text-body mb-1">Indirizzo Email</span>
                                <span class="text-muted"><?= e($user['email']) ?></span>
                            </div>
                            <button class="btn btn-outline-primary rounded-pill px-4 fw-bold shadow-sm"
                                data-bs-toggle="modal" type="button" data-bs-target="#settingsModal" onclick="switchSettingsTab('modal-email-tab')">
                                <i class="bi bi-envelope me-2"></i>Cambia Email
                            </button>
                        </div>

                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center py-2">
                            <div class="mb-3 mb-md-0">
                                <span class="d-block fw-semibold text-body mb-1">Password</span>
                                <span class="text-muted">************</span>
                            </div>
                            <button class="btn btn-outline-danger rounded-pill px-4 fw-bold shadow-sm"
                                data-bs-toggle="modal" type="button" data-bs-target="#settingsModal" onclick="switchSettingsTab('modal-pwd-tab')">
                                <i class="bi bi-key me-2"></i>Cambia Password
                            </button>
                        </div>
                    </div>

                    <!-- Footer Info -->
                    <div class="list-group-item p-4 bg-body-tertiary text-center">
                        <span class="text-muted small"><i class="bi bi-calendar-check me-1"></i>Iscritto a AlmaKick dal
                            <strong><?= date('d/m/Y', strtotime($user['created_at'])) ?></strong></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Gestione Account (Schede Unificate) -->
<div class="modal fade" id="settingsModal" tabindex="-1" aria-labelledby="settingsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold" id="settingsModalLabel">Impostazioni Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
            </div>
            
            <div class="px-3 pt-3">
                <ul class="nav nav-tabs border-bottom-0 gap-1 bg-light p-1 rounded-3 font-size-sm" id="settingsModalTabs" role="tablist">
                    <li class="nav-item flex-grow-1 text-center" role="presentation">
                        <button class="nav-link active rounded-2 border-0 w-100 fw-semibold" id="modal-info-tab" data-bs-toggle="tab" data-bs-target="#modal-info-pane" type="button" role="tab" aria-controls="modal-info-pane" aria-selected="true">
                            <i class="bi bi-person me-1"></i>Profilo
                        </button>
                    </li>
                    <li class="nav-item flex-grow-1 text-center" role="presentation">
                        <button class="nav-link rounded-2 border-0 w-100 fw-semibold" id="modal-email-tab" data-bs-toggle="tab" data-bs-target="#modal-email-pane" type="button" role="tab" aria-controls="modal-email-pane" aria-selected="false">
                            <i class="bi bi-envelope me-1"></i>Email
                        </button>
                    </li>
                    <li class="nav-item flex-grow-1 text-center" role="presentation">
                        <button class="nav-link rounded-2 border-0 w-100 fw-semibold" id="modal-pwd-tab" data-bs-toggle="tab" data-bs-target="#modal-pwd-pane" type="button" role="tab" aria-controls="modal-pwd-pane" aria-selected="false">
                            <i class="bi bi-key me-1"></i>Password
                        </button>
                    </li>
                </ul>
            </div>

            <div class="tab-content" id="settingsModalTabsContent">
                <!-- Tab: Modifica Info -->
                <div class="tab-pane fade show active" id="modal-info-pane" role="tabpanel" aria-labelledby="modal-info-tab">
                    <form action="<?= url('/profile/info') ?>" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token'] ?? '') ?>">
                        <div class="modal-body pt-3">
                            <div class="mb-3">
                                <label for="name" class="form-label fw-semibold">Nome</label>
                                <input type="text" class="form-control" id="name" name="name" value="<?= e($user['name']) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="last_name" class="form-label fw-semibold">Cognome</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" value="<?= e($user['last_name'] ?? '') ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="phone" class="form-label fw-semibold">Numero di Telefono</label>
                                <input type="tel" class="form-control" id="phone" name="phone" value="<?= e($user['phone'] ?? '') ?>" placeholder="es. 3331234567">
                            </div>
                            <div class="mb-3">
                                <label for="preferred_role" class="form-label fw-semibold">Ruolo Preferito</label>
                                <select class="form-select" id="preferred_role" name="preferred_role">
                                    <option value="Jolly" <?= $user['preferred_role'] === 'Jolly' ? 'selected' : '' ?>>Jolly</option>
                                    <option value="Portiere" <?= $user['preferred_role'] === 'Portiere' ? 'selected' : '' ?>>Portiere</option>
                                    <option value="Difensore" <?= $user['preferred_role'] === 'Difensore' ? 'selected' : '' ?>>Difensore</option>
                                    <option value="Centrocampista" <?= $user['preferred_role'] === 'Centrocampista' ? 'selected' : '' ?>>Centrocampista</option>
                                    <option value="Attaccante" <?= $user['preferred_role'] === 'Attaccante' ? 'selected' : '' ?>>Attaccante</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer border-top-0 pt-0">
                            <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Annulla</button>
                            <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Salva Modifiche</button>
                        </div>
                    </form>
                </div>

                <!-- Tab: Cambia Email -->
                <div class="tab-pane fade" id="modal-email-pane" role="tabpanel" aria-labelledby="modal-email-tab">
                    <form action="<?= url('/profile/info') ?>" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token'] ?? '') ?>">
                        <div class="modal-body pt-3">
                            <p class="text-muted small">Per motivi di sicurezza, ti chiediamo di confermare la tua password attuale.</p>
                            <div class="mb-3">
                                <label for="new_email" class="form-label fw-semibold">Nuova Email</label>
                                <input type="email" class="form-control" id="new_email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="current_password_email" class="form-label fw-semibold">Password Attuale</label>
                                <input type="password" class="form-control" id="current_password_email" name="current_password" required>
                            </div>
                        </div>
                        <div class="modal-footer border-top-0 pt-0">
                            <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Annulla</button>
                            <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Aggiorna Email</button>
                        </div>
                    </form>
                </div>

                <!-- Tab: Cambia Password -->
                <div class="tab-pane fade" id="modal-pwd-pane" role="tabpanel" aria-labelledby="modal-pwd-tab">
                    <form action="<?= url('/profile/info') ?>" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token'] ?? '') ?>">
                        <div class="modal-body pt-3">
                            <div class="mb-3">
                                <label for="current_password" class="form-label fw-semibold">Password Attuale</label>
                                <input type="password" class="form-control" id="current_password" name="current_password" required>
                            </div>
                            <div class="mb-3">
                                <label for="new_password" class="form-label fw-semibold">Nuova Password</label>
                                <input type="password" class="form-control" id="new_password" name="password" required minlength="6">
                            </div>
                            <div class="mb-3">
                                <label for="password_confirmation" class="form-label fw-semibold">Conferma Nuova Password</label>
                                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required minlength="6">
                            </div>
                        </div>
                        <div class="modal-footer border-top-0 pt-0">
                            <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Annulla</button>
                            <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold">Aggiorna Password</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
