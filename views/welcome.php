<div class="welcome-container">
    <div class="row align-items-center mb-5 mt-5">
        <div class="col-lg-6 text-center text-lg-start mb-5 mb-lg-0">
            <h1 class="display-3 fw-bold mb-4 text-primary">Unisciti, <br>Gioca, <br><span class="text-warning">Divertiti.</span></h1>
            <p class="lead text-muted mb-4">Non lasciare che la pioggia o le disdette dell'ultimo minuto rovinino la tua passione. AlmaKick organizza per te partite affidabili nel tuo Campus universitario.</p>
            
            <div class="d-flex flex-column flex-md-row gap-3 justify-content-center justify-content-lg-start mt-5">
                <a href="<?= url('/register') ?>" class="btn btn-primary btn-lg rounded-pill px-4 shadow-sm fw-bold">
                    <i class="bi bi-person-plus-fill me-2"></i>Registrati Ora
                </a>
                <a href="<?= url('/login') ?>" class="btn btn-outline-secondary btn-lg rounded-pill px-4 fw-bold">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Accedi
                </a>
            </div>
        </div>
        
        <div class="col-lg-6">
            <div class="position-relative">
                <div class="card bg-dark text-white border-0 shadow-lg rounded-4 overflow-hidden transform-tilt">
                    <img src="https://images.unsplash.com/photo-1579952363873-27f3bade9f55?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" 
                         class="card-img welcome-hero-img opacity-50" alt="Campo di calcetto illuminato">
                    <div class="card-img-overlay d-flex flex-column justify-content-end p-4">
                        <h3 class="card-title fw-bold mb-1"><i class="bi bi-geo-alt-fill text-danger me-2"></i>Trova i Campi</h3>
                        <p class="card-text">Visualizza la posizione esatta con il nostro servizio di mappe integrato.</p>
                    </div>
                </div>
                
                <!-- Floating Badge -->
                <div class="position-absolute text-white bg-success badge rounded-pill shadow fs-5 p-3 welcome-floating-badge">
                    <i class="bi bi-shield-check me-2"></i>Sistema Trust Score
                </div>
            </div>
        </div>
    </div>

    <div class="row text-center mt-5 pt-5 mb-5 g-4 border-top">
        <div class="col-md-4">
            <div class="p-4 bg-body-tertiary rounded-4 h-100 shadow-sm border-0">
                <i class="bi bi-people fs-1 text-primary mb-3"></i>
                <h4 class="fw-bold">Squadre Equilibrate</h4>
                <p class="text-muted">Il nostro algoritmo genera formazioni bilanciate in base al ruolo preferito e all'affidabilità dei giocatori.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="p-4 bg-body-tertiary rounded-4 h-100 shadow-sm border-0">
                <i class="bi bi-cloud-sun fs-1 text-warning mb-3"></i>
                <h4 class="fw-bold">Meteo in Tempo Reale</h4>
                <p class="text-muted">Integrazione con OpenWeatherMap per previsioni sul campo precise fino all'ora della partita.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="p-4 bg-body-tertiary rounded-4 h-100 shadow-sm border-0">
                <i class="bi bi-star-half fs-1 text-danger mb-3"></i>
                <h4 class="fw-bold">Eleggi l'MVP</h4>
                <p class="text-muted">A fine partita, usa le votazioni per scegliere il miglior giocatore e sanziona comportamenti antisportivi.</p>
            </div>
        </div>
    </div>
</div>
