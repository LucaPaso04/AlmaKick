<?php // Dashboard Amministratore ?>

<?php // =================== HEADER =================== ?>
<div class="admin-header mb-4 d-flex align-items-center justify-content-between">
    <div>
        <h1 class="fw-bolder mb-1 text-white admin-header-subtitle">
            <i class="bi bi-shield-lock-fill text-warning me-2"></i>Dashboard Amministratore
        </h1>
        <p class="text-white-50 mb-0">Supervisiona utenti, partite, Trust Score e moderazione sulla piattaforma.</p>
    </div>
    <div class="d-none d-md-block">
        <i class="bi bi-graph-up-arrow text-white-50 admin-header-decor"></i>
    </div>
</div>

<?php // =================== TABS =================== ?>
<ul class="nav nav-pills nav-fill admin-tabs mb-4 p-1 rounded-4 shadow-sm border border-secondary-subtle" id="adminDashboardTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active rounded-pill fw-bold" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview-section" type="button" role="tab" aria-controls="overview-section" aria-selected="true">
            📊 Panoramica
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link rounded-pill fw-bold" id="users-tab" data-bs-toggle="tab" data-bs-target="#users-section" type="button" role="tab" aria-controls="users-section" aria-selected="false">
            👥 Gestione Utenti
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link rounded-pill fw-bold" id="reports-tab" data-bs-toggle="tab" data-bs-target="#reports-section" type="button" role="tab" aria-controls="reports-section" aria-selected="false">
            🚩 Segnalazioni
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link rounded-pill fw-bold" id="matches-tab" data-bs-toggle="tab" data-bs-target="#matches-section" type="button" role="tab" aria-controls="matches-section" aria-selected="false">
            ⚽ Partite
        </button>
    </li>
</ul>

<div class="tab-content" id="adminDashboardTabsContent">
    <!-- Tab 1: Panoramica -->
    <div class="tab-pane fade show active" id="overview-section" role="tabpanel" aria-labelledby="overview-tab">
        <?php // =================== STATS CARDS =================== ?>
        <div class="stats-grid mb-5">
    <div class="card admin-stat-card border-0 shadow-sm rounded-4 text-center h-100">
        <div class="icon-circle icon-primary">
            <i class="bi bi-people-fill"></i>
        </div>
        <h3 class="fw-bold mb-0"><?= e($stats['total_users']) ?></h3>
        <small class="text-muted fw-semibold admin-stat-card-label">Utenti Totali</small>
    </div>
    <div class="card admin-stat-card border-0 shadow-sm rounded-4 text-center h-100">
        <div class="icon-circle icon-danger">
            <i class="bi bi-person-x-fill"></i>
        </div>
        <h3 class="fw-bold mb-0"><?= e($stats['banned_users']) ?></h3>
        <small class="text-muted fw-semibold admin-stat-card-label">Bannati</small>
    </div>
    <div class="card admin-stat-card border-0 shadow-sm rounded-4 text-center h-100">
        <div class="icon-circle icon-success">
            <i class="bi bi-calendar-event-fill"></i>
        </div>
        <h3 class="fw-bold mb-0"><?= e($stats['total_matches']) ?></h3>
        <small class="text-muted fw-semibold admin-stat-card-label">Partite Totali</small>
    </div>
    <div class="card admin-stat-card border-0 shadow-sm rounded-4 text-center h-100">
        <div class="icon-circle icon-info">
            <i class="bi bi-play-circle-fill"></i>
        </div>
        <h3 class="fw-bold mb-0"><?= e($stats['active_matches']) ?></h3>
        <small class="text-muted fw-semibold admin-stat-card-label">Attive</small>
    </div>
    <div class="card admin-stat-card border-0 shadow-sm rounded-4 text-center h-100">
        <div class="icon-circle icon-dark">
            <i class="bi bi-flag-fill"></i>
        </div>
        <h3 class="fw-bold mb-0"><?= e($stats['finished_matches']) ?></h3>
        <small class="text-muted fw-semibold admin-stat-card-label">Concluse</small>
    </div>
    <div class="card admin-stat-card border-0 shadow-sm rounded-4 text-center h-100">
        <div class="icon-circle icon-warning text-dark">
            <i class="bi bi-x-circle-fill"></i>
        </div>
        <h3 class="fw-bold mb-0"><?= e($stats['cancelled_matches']) ?></h3>
        <small class="text-muted fw-semibold admin-stat-card-label">Annullate</small>
    </div>
    <div class="card admin-stat-card border-0 shadow-sm rounded-4 text-center h-100 <?= $stats['pending_reports'] > 0 ? 'border border-danger border-opacity-50' : '' ?>">
        <div class="icon-circle <?= $stats['pending_reports'] > 0 ? 'icon-danger pulse-danger' : 'icon-secondary' ?> <?= $stats['pending_reports'] > 0 ? 'pending-reports-active' : 'pending-reports-inactive' ?>">
            <i class="bi bi-flag-fill"></i>
        </div>
        <h3 class="fw-bold mb-0"><?= e($stats['pending_reports']) ?></h3>
        <small class="text-muted fw-semibold admin-stat-card-label">Segnalazioni</small>
    </div>
</div>

        <?php require VIEW_PATH . '/admin/partials/charts.php'; ?>
    </div>

    <!-- Tab 2: Gestione Utenti -->
    <div class="tab-pane fade" id="users-section" role="tabpanel" aria-labelledby="users-tab">
<?php require VIEW_PATH . '/admin/partials/users_table.php'; ?>

    </div>

    <!-- Tab 3: Segnalazioni -->
    <div class="tab-pane fade" id="reports-section" role="tabpanel" aria-labelledby="reports-tab">
<?php require VIEW_PATH . '/admin/partials/reports_table.php'; ?>

    </div>

    <!-- Tab 4: Partite -->
    <div class="tab-pane fade" id="matches-section" role="tabpanel" aria-labelledby="matches-tab">
<?php require VIEW_PATH . '/admin/partials/matches_table.php'; ?>

    </div>


</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // 1. Ripristina il tab attivo dall'hash dell'URL o da localStorage
    const hash = window.location.hash;
    let activeTabTrigger = null;

    if (hash) {
        // Cerca il bottone del tab che punta a questo hash
        activeTabTrigger = document.querySelector(`button[data-bs-target="${hash}"]`);
    }

    if (!activeTabTrigger) {
        // Fallback su localStorage se presente, altrimenti default sul primo tab
        const savedTab = localStorage.getItem('admin_active_tab');
        if (savedTab) {
            activeTabTrigger = document.querySelector(`button[data-bs-target="${savedTab}"]`);
        }
    }

    if (activeTabTrigger) {
        const tab = new bootstrap.Tab(activeTabTrigger);
        tab.show();
    }

    // 2. Registra l'evento al cambio di tab per salvare lo stato e aggiornare l'hash
    const tabButtons = document.querySelectorAll('button[data-bs-toggle="tab"]');
    tabButtons.forEach(btn => {
        btn.addEventListener('shown.bs.tab', function(e) {
            const targetHash = e.target.getAttribute('data-bs-target');
            
            // Salva lo stato in localStorage
            localStorage.setItem('admin_active_tab', targetHash);
            
            // Aggiorna l'hash dell'URL senza causare il salto della pagina
            history.pushState(null, null, targetHash);
        });
    });

    // 3. Gestione Eventi tramite Event Delegation (Deleghe sul contenitore dei tab)
    const tabContent = document.getElementById('adminDashboardTabsContent');
    if (tabContent) {
        // A. Clic sui link di paginazione
        tabContent.addEventListener('click', function(e) {
            const link = e.target.closest('a.page-link');
            if (link) {
                e.preventDefault();
                const url = link.getAttribute('href');
                if (url && url !== '#') {
                    loadDashboardState(url, false);
                }
            }
        });

        // B. Invio dei moduli (ricerca GET e azioni POST)
        tabContent.addEventListener('submit', function(e) {
            const form = e.target.closest('form');
            if (!form) return;

            if (form.getAttribute('method').toUpperCase() === 'GET') {
                e.preventDefault();
                const formData = new FormData(form);
                const params = new URLSearchParams(formData);
                
                // Rimuove il cancelletto (#) dall'action per inserire i parametri di query prima dell'hash
                const rawAction = form.getAttribute('action') || window.location.pathname;
                const actionParts = rawAction.split('#');
                const actionPath = actionParts[0];
                const actionHash = actionParts[1] ? '#' + actionParts[1] : '';
                
                const url = `${actionPath}?${params.toString()}${actionHash}`;
                loadDashboardState(url, false);
            } else if (form.getAttribute('method').toUpperCase() === 'POST') {
                e.preventDefault();

                const submitBtn = form.querySelector('button[type="submit"]');
                let originalBtnHtml = '';
                if (submitBtn) {
                    originalBtnHtml = submitBtn.innerHTML;
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span>...';
                    submitBtn.disabled = true;
                }

                const url = form.getAttribute('action');
                const formData = new FormData(form);

                fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                })
                .then(response => {
                    if (!response.ok) throw new Error('Errore di rete');
                    return response.json();
                })
                .then(data => {
                    // Chiudi le modali aperte
                    const openModalEl = document.querySelector('.modal.show');
                    if (openModalEl) {
                        const modalInstance = bootstrap.Modal.getInstance(openModalEl) || new bootstrap.Modal(openModalEl);
                        modalInstance.hide();
                        
                        document.querySelectorAll('.modal-backdrop').forEach(bd => bd.remove());
                        document.body.classList.remove('modal-open');
                        document.body.style.overflow = '';
                        document.body.style.paddingRight = '';
                    }

                    if (data.success) {
                        window.showToast(data.message, 'success');
                        loadDashboardState(window.location.href, true);
                    } else {
                        window.showToast(data.message, 'danger');
                        if (submitBtn) {
                            submitBtn.innerHTML = originalBtnHtml;
                            submitBtn.disabled = false;
                        }
                    }
                })
                .catch(err => {
                    console.error('Errore durante l\'azione:', err);
                    window.showToast('Errore di connessione o operazione non valida.', 'danger');
                    if (submitBtn) {
                        submitBtn.innerHTML = originalBtnHtml;
                        submitBtn.disabled = false;
                    }
                });
            }
        });

        // C. Rilevazione del cambio sui menu dropdown e datepicker per l'invio automatico
        tabContent.addEventListener('change', function(e) {
            const input = e.target;
            if (input.tagName === 'SELECT' || input.type === 'date') {
                const form = input.closest('form');
                if (form && form.getAttribute('method').toUpperCase() === 'GET') {
                    if (typeof form.requestSubmit === 'function') {
                        form.requestSubmit();
                    } else {
                        form.dispatchEvent(new Event('submit', { cancelable: true, bubbles: true }));
                    }
                }
            }
        });
    }
});

function loadDashboardState(url, isAction) {
    const activeTabButton = document.querySelector('.admin-tabs .nav-link.active');
    const activeTabTarget = activeTabButton ? activeTabButton.getAttribute('data-bs-target') : '#overview-section';
    const activePane = document.querySelector(activeTabTarget);

    if (activePane) {
        activePane.classList.add('ajax-loading');
    }

    return fetch(url, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.text())
    .then(html => {
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');

        if (isAction || activeTabTarget === '#overview-section') {
            const newStatsGrid = doc.querySelector('.stats-grid');
            const currentStatsGrid = document.querySelector('.stats-grid');
            if (newStatsGrid && currentStatsGrid) {
                currentStatsGrid.innerHTML = newStatsGrid.innerHTML;
            }
        }

        const panes = ['#overview-section', '#users-section', '#reports-section', '#matches-section', '#trust-section'];
        panes.forEach(paneId => {
            const newPane = doc.querySelector(paneId);
            const currentPane = document.querySelector(paneId);
            if (newPane && currentPane) {
                if (isAction || paneId === activeTabTarget) {
                    currentPane.innerHTML = newPane.innerHTML;
                } else if (paneId !== '#overview-section') {
                    currentPane.innerHTML = newPane.innerHTML;
                }
            }
        });

        history.pushState(null, null, url);

        if (isAction || activeTabTarget === '#overview-section') {
            const overviewPane = document.querySelector('#overview-section');
            if (overviewPane) {
                const scripts = overviewPane.querySelectorAll('script');
                scripts.forEach(oldScript => {
                    const newScript = document.createElement('script');
                    newScript.text = oldScript.text;
                    oldScript.parentNode.replaceChild(newScript, oldScript);
                });
            }
        }

        if (activePane) {
            activePane.classList.remove('ajax-loading');
        }
    })
    .catch(err => {
        console.error('Errore AJAX:', err);
        if (activePane) {
            activePane.classList.remove('ajax-loading');
        }
    });
}
</script>
