<div class="row justify-content-center my-5">
    <div class="col-12 col-md-10 col-lg-8">
        <div class="glass-panel p-4 p-md-5">
            <h2 class="text-center fw-bold mb-4">Crea una Nuova Partita</h2>
            <form action="<?= url('/matches') ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">

                <div class="row g-3 mb-3">
                    <div class="col-12 col-sm-6">
                        <label for="date" class="form-label fw-semibold text-secondary-custom">Data</label>
                        <input type="date" id="date" name="date" class="form-control" required>
                    </div>
                    <div class="col-12 col-sm-6">
                        <label for="time" class="form-label fw-semibold text-secondary-custom">Ora</label>
                        <input type="time" id="time" name="time" class="form-control" required>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-12 col-sm-6">
                        <label for="format" class="form-label fw-semibold text-secondary-custom">Formato</label>
                        <select id="format" name="format" class="form-select">
                            <option value="5vs5">5 vs 5 (10 Giocatori)</option>
                            <option value="7vs7">7 vs 7 (14 Giocatori)</option>
                            <option value="8vs8">8 vs 8 (16 Giocatori)</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-6">
                        <label for="visibility" class="form-label fw-semibold text-secondary-custom">Visibilità</label>
                        <select id="visibility" name="visibility" class="form-select">
                            <option value="public">Pubblica</option>
                            <option value="private">Privata (Solo Amici)</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="location" class="form-label fw-semibold text-secondary-custom">Luogo / Campo</label>
                    <input type="text" id="location" name="location" class="form-control" placeholder="es. Centro Sportivo San Siro" required>
                </div>

                <div class="mb-4">
                    <label for="total_cost" class="form-label fw-semibold text-secondary-custom">Costo Totale Campo (€)</label>
                    <input type="number" step="0.01" id="total_cost" name="total_cost" class="form-control" placeholder="es. 60.00">
                </div>

                <button type="submit" class="btn btn-primary w-100">Crea Partita</button>
            </form>
        </div>
    </div>
</div>
