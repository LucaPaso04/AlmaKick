<div class="welcome-container">
    <section class="welcome-hero">
        <div class="container welcome-hero-content">
            <div class="welcome-hero-card">
                <span class="welcome-eyebrow">
                    <span class="bi bi-lightning-charge-fill me-2"></span>AlmaKick • Organizza il tuo match in un click
                </span>
                <h1>Scendi in campo.<br><span>Quando vuoi, con chi vuoi.</span></h1>
                <p>La piattaforma del tuo Campus per organizzare e trovare partite di calcetto in pochi secondi.</p>

                <div class="welcome-actions">
                    <a href="<?= url('/register') ?>" class="btn btn-lg welcome-primary-btn">
                        <span>Inizia Ora</span>
                        <span class="bi bi-arrow-right ms-2"></span>
                    </a>
                    <a href="<?= url('/login') ?>" class="btn btn-lg welcome-secondary-btn">
                        <span class="bi bi-box-arrow-in-right me-2"></span>Accedi
                    </a>
                </div>

                <div class="welcome-highlights">
                    <span class="welcome-pill"><span class="bi bi-geo-alt-fill me-2"></span>Campi vicini</span>
                    <span class="welcome-pill"><span class="bi bi-shield-check me-2"></span>Trust score</span>
                    <span class="welcome-pill"><span class="bi bi-stars me-2"></span>Partite affidabili</span>
                </div>
            </div>
        </div>
    </section>

    <div class="welcome-features">
        <article class="welcome-feature-card">
            <div class="feature-copy">
                <span class="feature-kicker">Squadre equilibrate</span>
                <h2>Formazioni bilanciate in base al ruolo e all’affidabilità.</h2>
                <p>Il nostro sistema crea partite più giuste e coinvolgenti, riducendo il caos dell’ultimo minuto.</p>
                <ul class="feature-list">
                    <li><span class="bi bi-check2-circle"></span>Matchmaking intelligente</li>
                    <li><span class="bi bi-check2-circle"></span>Ruoli preferiti e livelli di gioco</li>
                    <li><span class="bi bi-check2-circle"></span>Organizzazione senza stress</li>
                </ul>
            </div>
            <div class="feature-visual formation-visual" aria-hidden="true">
                <div class="formation-card">
                    <div class="formation-pitch">
                        <div class="formation-line line-top"></div>
                        <div class="formation-line line-mid"></div>
                        <div class="formation-line line-bottom"></div>
                        <span class="player player-1"></span>
                        <span class="player player-2"></span>
                        <span class="player player-3"></span>
                        <span class="player player-4"></span>
                        <span class="player player-5"></span>
                        <span class="player player-6"></span>
                        <span class="player player-7"></span>
                        <span class="player player-8"></span>
                        <span class="player player-9"></span>
                        <span class="player player-10"></span>
                        <span class="player player-11"></span>
                    </div>
                </div>
            </div>
        </article>

        <article class="welcome-feature-card reverse">
            <div class="feature-visual weather-visual" aria-hidden="true">
                <div class="weather-card">
                    <div class="weather-top">
                        <span class="weather-icon"><span class="bi bi-cloud-sun"></span></span>
                        <div>
                            <strong>22°</strong>
                            <p>Partita ideale</p>
                        </div>
                    </div>
                    <div class="weather-bars">
                        <span></span>
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </div>
            </div>
            <div class="feature-copy">
                <span class="feature-kicker">Meteo in tempo reale</span>
                <h2>Controlla il meteo già prima della partita.</h2>
                <p>Previsioni aggiornate e alert utili per scegliere il momento giusto e non perdere l’evento.</p>
                <div class="feature-badge">
                    <span class="bi bi-cloud-drizzle"></span>
                    <span>Previsioni aggiornate ogni ora</span>
                </div>
            </div>
        </article>

        <article class="welcome-feature-card">
            <div class="feature-copy">
                <span class="feature-kicker">MVP & Trust Score</span>
                <h2>Premia il talento e riconosci chi fa la differenza.</h2>
                <p>Vota i giocatori, assegna il badge MVP e costruisci un ambiente affidabile per ogni serata.</p>
                <div class="mvp-preview">
                    <div class="mvp-badge">
                        <span class="bi bi-star-fill"></span>
                        <span>MVP della Serata</span>
                    </div>
                    <div class="trust-meter">
                        <div class="trust-fill"></div>
                    </div>
                </div>
            </div>
            <div class="feature-visual mvp-visual" aria-hidden="true">
                <div class="mvp-card">
                    <div class="mvp-avatar">M</div>
                    <div>
                        <div class="mvp-name">Marco R.</div>
                        <p>4.9/5 • 12 presenze</p>
                    </div>
                </div>
            </div>
        </article>
    </div>
</div>