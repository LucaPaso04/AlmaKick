/* Admin dashboard logic */
document.addEventListener("DOMContentLoaded", function() {
    // Restore active tab
    const hash = window.location.hash;
    let activeTabTrigger = null;

    if (hash) {
        activeTabTrigger = document.querySelector(`button[data-bs-target="${hash}"]`);
    }

    if (!activeTabTrigger) {
        const savedTab = localStorage.getItem('admin_active_tab');
        if (savedTab) {
            activeTabTrigger = document.querySelector(`button[data-bs-target="${savedTab}"]`);
        }
    }

    if (activeTabTrigger) {
        const tab = new bootstrap.Tab(activeTabTrigger);
        tab.show();
    }

    // Save active tab state
    const tabButtons = document.querySelectorAll('button[data-bs-toggle="tab"]');
    tabButtons.forEach(btn => {
        btn.addEventListener('shown.bs.tab', function(e) {
            const targetHash = e.target.getAttribute('data-bs-target');
            localStorage.setItem('admin_active_tab', targetHash);
            history.pushState(null, null, targetHash);
        });
    });

    // Handle dynamic tab events
    const tabContent = document.getElementById('adminDashboardTabsContent');
    if (tabContent) {
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

        tabContent.addEventListener('submit', function(e) {
            const form = e.target.closest('form');
            if (!form) return;

            if (form.getAttribute('method').toUpperCase() === 'GET') {
                e.preventDefault();
                const formData = new FormData(form);
                const params = new URLSearchParams(formData);
                
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
