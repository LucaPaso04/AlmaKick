<section class="hero text-center py-5">
    <div class="mb-4">
        <span class="logo-bg-wrapper p-3 rounded-4">
            <img src="<?= url('/images/logo-text.svg') ?>" alt="AlmaKick" class="hero-logo img-fluid">
        </span>
    </div>
    <h1 class="display-3 fw-bold mb-3">Organizza il Calcetto</h1>
    <p class="lead text-secondary-custom mx-auto mb-4"><?= e($tagline) ?></p>
    <div class="d-flex gap-3 justify-content-center">
        <a href="<?= url('/matches') ?>" class="btn btn-primary">Sfoglia Partite</a>
        <?php if (!isset($_SESSION['user'])): ?>
            <a href="<?= url('/register') ?>" class="btn btn-outline">Registrati Ora</a>
        <?php endif; ?>
    </div>
</section>

<section class="mt-5 text-center">
    <h2 class="fw-bold mb-4">Perché AlmaKick?</h2>
    <div class="row g-4 justify-content-center mt-2">
        <div class="col-12 col-md-6 col-lg-4 d-flex">
            <div class="glass-panel p-4 text-start w-100">
                <h3 class="text-accent mb-3 h4">Organizzazione Istantanea</h3>
                <p class="text-secondary-custom mb-0">Crea una partita, imposta ora, luogo, visibilità e invita i tuoi amici o lascia che la community si iscriva.</p>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-4 d-flex">
            <div class="glass-panel p-4 text-start w-100">
                <h3 class="text-accent mb-3 h4">Squadre Equilibrate</h3>
                <p class="text-secondary-custom mb-0">Algoritmo avanzato di bilanciamento per suddividere i giocatori in due team equivalenti per livello di abilità e ruoli.</p>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-4 d-flex">
            <div class="glass-panel p-4 text-start w-100">
                <h3 class="text-accent mb-3 h4">Votazione MVP e Statistiche</h3>
                <p class="text-secondary-custom mb-0">Vota il migliore in campo post-partita, registra i gol e traccia il tuo rating per salire nella classifica generale.</p>
            </div>
        </div>
    </div>
</section>
