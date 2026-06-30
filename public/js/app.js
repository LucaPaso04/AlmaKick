document.addEventListener('DOMContentLoaded', function() {
    const themeToggle = document.getElementById('theme-toggle');
    const themeIcon = document.getElementById('theme-icon');
    const htmlEl = document.documentElement;

    const savedTheme = localStorage.getItem('theme') || 'dark';
    htmlEl.setAttribute('data-bs-theme', savedTheme);
    updateThemeIcon(savedTheme);

    if (themeToggle) {
        themeToggle.addEventListener('click', function() {
            const currentTheme = htmlEl.getAttribute('data-bs-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            htmlEl.setAttribute('data-bs-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateThemeIcon(newTheme);
        });
    }

    function updateThemeIcon(theme) {
        if (!themeIcon) return;
        if (theme === 'dark') {
            themeIcon.className = 'bi bi-sun-fill fs-5 transition-transform';
        } else {
            themeIcon.className = 'bi bi-moon-fill fs-5 transition-transform';
        }
    }

    const passwordInput = document.getElementById('password');
    const toggleButton = document.querySelector('.password-toggle');
    if (passwordInput && toggleButton) {
        const icon = toggleButton.querySelector('.bi');
        toggleButton.addEventListener('click', function() {
            const isPassword = passwordInput.type === 'password';
            passwordInput.type = isPassword ? 'text' : 'password';
            toggleButton.setAttribute('aria-label', isPassword ? 'Nascondi password' : 'Mostra password');
            if (icon) {
                icon.className = isPassword ? 'bi bi-eye-slash' : 'bi bi-eye';
            }
        });
    }

    function initToast(toast) {
        const isError = toast.classList.contains('toast-danger') || toast.getAttribute('data-duration') === '0';
        const progressBar = toast.querySelector('.custom-toast-progress');
        const closeBtn = toast.querySelector('.btn-close-toast');
        let animationFrameId = null;

        function dismissToast(el) {
            if (animationFrameId) cancelAnimationFrame(animationFrameId);
            el.classList.add('hide');
            el.addEventListener('animationend', function() {
                el.remove();
            });
        }

        if (closeBtn) {
            closeBtn.addEventListener('click', () => {
                dismissToast(toast);
            });
        }

        if (isError) {
            if (progressBar) progressBar.remove();
            return;
        }

        const duration = parseInt(toast.getAttribute('data-duration')) || 4500;
        let remainingTime = duration;
        let lastFrameTime = performance.now();
        let isPaused = false;

        function updateProgress(timestamp) {
            if (isPaused) {
                lastFrameTime = timestamp;
                animationFrameId = requestAnimationFrame(updateProgress);
                return;
            }

            const delta = timestamp - lastFrameTime;
            lastFrameTime = timestamp;
            remainingTime -= delta;

            if (remainingTime <= 0) {
                if (progressBar) progressBar.style.width = '0%';
                dismissToast(toast);
            } else {
                const percent = (remainingTime / duration) * 100;
                if (progressBar) progressBar.style.width = percent + '%';
                animationFrameId = requestAnimationFrame(updateProgress);
            }
        }

        toast.addEventListener('mouseenter', () => { isPaused = true; });
        toast.addEventListener('mouseleave', () => { isPaused = false; });
        animationFrameId = requestAnimationFrame(updateProgress);
    }

    document.querySelectorAll('.custom-toast').forEach(initToast);

    window.showToast = function(message, type = 'success', duration = 4500) {
        const container = document.getElementById('toast-container');
        if (!container) return;

        const toast = document.createElement('div');
        toast.className = `custom-toast toast-${type}`;
        if (type !== 'danger' && duration > 0) {
            toast.setAttribute('data-duration', duration);
        } else {
            toast.setAttribute('data-duration', '0');
        }
        toast.setAttribute('role', 'alert');

        const iconClass = type === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill';
        const progressHtml = (type !== 'danger' && duration > 0) ? '<div class="custom-toast-progress"></div>' : '';

        toast.innerHTML = `
            <div class="custom-toast-content">
                <span class="bi ${iconClass} fs-5"></span>
                <span>${escapeHtml(message)}</span>
            </div>
            <button type="button" class="btn-close-toast" aria-label="Chiudi avviso">&times;</button>
            ${progressHtml}
        `;

        container.appendChild(toast);
        initToast(toast);
    };

    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }

    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function() {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn && !form.classList.contains('no-spinner')) {
                if (submitBtn.classList.contains('no-loading-state')) return;
                submitBtn.innerHTML = `
                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                    Caricamento...
                `;
                setTimeout(() => {
                    submitBtn.disabled = true;
                }, 0);
            }
        });
    });

    const backToTopBtn = document.getElementById('back-to-top');
    if (backToTopBtn) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 400) {
                backToTopBtn.classList.add('show');
            } else {
                backToTopBtn.classList.remove('show');
            }
        });

        backToTopBtn.addEventListener('click', function() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    const scrollProgressBar = document.getElementById('scroll-progress-bar');
    if (scrollProgressBar) {
        window.addEventListener('scroll', function() {
            const winScroll = window.scrollY || document.documentElement.scrollTop;
            const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
            const scrolled = height > 0 ? (winScroll / height) * 100 : 0;
            scrollProgressBar.style.width = scrolled + '%';
        });
    }

    // ----------------------------------------------------
    // GESTIONE NOTIFICHE PREMIUM
    // ----------------------------------------------------
    const notificationsBell = document.getElementById('notificationsBell');
    const notificationsBadge = document.getElementById('notificationsBadge');
    const notificationsList = document.getElementById('notificationsList');
    const notificationsEmpty = document.getElementById('notificationsEmpty');
    const markAllReadBtn = document.getElementById('markAllReadBtn');
    const clearAllBtn = document.getElementById('clearAllBtn');
    const notificationsHeaderDivider = document.getElementById('notificationsHeaderDivider');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    if (notificationsBell) {
        // Fetch iniziale delle notifiche
        fetchNotifications();

        // Polling asincrono ogni 45 secondi
        setInterval(fetchNotifications, 45000);

        // Click su "Segna come lette"
        if (markAllReadBtn) {
            markAllReadBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                markAllNotificationsAsRead();
            });
        }

        // Click su "Svuota tutto"
        if (clearAllBtn) {
            clearAllBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                if (confirm('Sei sicuro di voler eliminare tutte le notifiche?')) {
                    clearAllNotifications();
                }
            });
        }
    }

    function fetchNotifications() {
        // Rileva il BASE_URL in modo dinamico
        let baseUrl = '';
        const homeLink = document.querySelector('a[href*="/welcome"], a[href*="/matches"]');
        if (homeLink) {
            const href = homeLink.getAttribute('href');
            if (href) {
                if (href.includes('/welcome')) {
                    baseUrl = href.split('/welcome')[0];
                } else if (href.includes('/matches')) {
                    baseUrl = href.split('/matches')[0];
                }
            }
        }

        fetch(baseUrl + '/api/notifications')
            .then(response => {
                if (!response.ok) throw new Error('Risposta di rete non valida');
                return response.json();
            })
            .then(data => {
                updateNotificationsUI(data, baseUrl);
            })
            .catch(err => console.error('Errore nel caricamento delle notifiche:', err));
    }

    function updateNotificationsUI(data, baseUrl) {
        if (!notificationsList) return;

        const count = data.unread_count || 0;
        if (count > 0) {
            notificationsBadge.innerText = count;
            notificationsBadge.classList.remove('d-none');
            if (markAllReadBtn) markAllReadBtn.classList.remove('d-none');
        } else {
            notificationsBadge.classList.add('d-none');
            if (markAllReadBtn) markAllReadBtn.classList.add('d-none');
        }

        const notifications = data.notifications || [];
        
        // Gestione visibilità divider e pulsante svuota tutto
        if (notifications.length > 0) {
            if (clearAllBtn) clearAllBtn.classList.remove('d-none');
            if (notificationsHeaderDivider && count > 0) {
                notificationsHeaderDivider.classList.remove('d-none');
            } else if (notificationsHeaderDivider) {
                notificationsHeaderDivider.classList.add('d-none');
            }
        } else {
            if (clearAllBtn) clearAllBtn.classList.add('d-none');
            if (notificationsHeaderDivider) notificationsHeaderDivider.classList.add('d-none');
            
            notificationsList.innerHTML = `
                <div class="text-center py-4 text-muted small" id="notificationsEmpty">
                    <i class="bi bi-bell-slash fs-4 d-block mb-1 opacity-50"></i>
                    Nessuna notifica ricevuta
                </div>
            `;
            return;
        }

        let html = '';
        notifications.forEach(n => {
            let iconClass = 'bi-bell-fill';
            let iconBgClass = '';
            
            switch (n.type) {
                case 'friend_request':
                    iconClass = 'bi-person-plus-fill';
                    iconBgClass = 'text-primary';
                    break;
                case 'friend_accept':
                    iconClass = 'bi-people-fill';
                    iconBgClass = 'text-success';
                    break;
                case 'match_promotion':
                    iconClass = 'bi-award-fill';
                    iconBgClass = 'text-warning';
                    break;
                case 'match_cancellation':
                    iconClass = 'bi-exclamation-triangle-fill';
                    iconBgClass = 'text-danger';
                    break;
            }

            const unreadClass = n.is_read == 0 ? 'unread' : '';
            const statusDot = n.is_read == 0 ? '<span class="notification-status-dot"></span>' : '';

            html += `
                <a href="${n.link || '#'}" class="notification-item ${unreadClass}" data-id="${n.id}">
                    <div class="notification-icon-wrapper ${iconBgClass}">
                        <i class="bi ${iconClass}"></i>
                    </div>
                    <div class="notification-content">
                        <div class="notification-message text-body">${escapeHtml(n.message)}</div>
                        <div class="notification-time">${n.time_ago}</div>
                    </div>
                    ${statusDot}
                    <button type="button" class="notification-delete-btn" data-id="${n.id}" title="Elimina notifica">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </a>
            `;
        });

        notificationsList.innerHTML = html;

        // Click handler su ciascun elemento di notifica
        notificationsList.querySelectorAll('.notification-item').forEach(item => {
            item.addEventListener('click', function(e) {
                const id = this.getAttribute('data-id');
                const isUnread = this.classList.contains('unread');
                const link = this.getAttribute('href');

                if (isUnread && id) {
                    e.preventDefault();
                    fetch(baseUrl + '/api/notifications/' + id + '/read', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'csrf_token=' + encodeURIComponent(csrfToken)
                    })
                    .finally(() => {
                        window.location.href = link;
                    });
                }
            });
        });

        // Click handler sul pulsante "X" per eliminare la singola notifica
        notificationsList.querySelectorAll('.notification-delete-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const id = this.getAttribute('data-id');
                const item = this.closest('.notification-item');
                
                if (id && item) {
                    // Animazione di uscita
                    item.style.transition = 'opacity 0.25s ease, transform 0.25s ease';
                    item.style.opacity = '0';
                    item.style.transform = 'translateX(20px)';
                    
                    fetch(baseUrl + '/api/notifications/' + id + '/delete', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'csrf_token=' + encodeURIComponent(csrfToken)
                    })
                    .then(response => {
                        if (!response.ok) throw new Error('Errore di rete');
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            setTimeout(() => {
                                fetchNotifications();
                            }, 250);
                        } else {
                            // Ripristina l'elemento se fallisce
                            item.style.opacity = '1';
                            item.style.transform = 'translateX(0)';
                        }
                    })
                    .catch(err => {
                        console.error('Errore durante l\'eliminazione della notifica:', err);
                        item.style.opacity = '1';
                        item.style.transform = 'translateX(0)';
                    });
                }
            });
        });
    }

    function markAllNotificationsAsRead() {
        let baseUrl = '';
        const homeLink = document.querySelector('a[href*="/welcome"], a[href*="/matches"]');
        if (homeLink) {
            const href = homeLink.getAttribute('href');
            if (href) {
                if (href.includes('/welcome')) {
                    baseUrl = href.split('/welcome')[0];
                } else if (href.includes('/matches')) {
                    baseUrl = href.split('/matches')[0];
                }
            }
        }

        fetch(baseUrl + '/api/notifications/read-all', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'csrf_token=' + encodeURIComponent(csrfToken)
        })
        .then(response => {
            if (!response.ok) throw new Error('Errore di rete');
            return response.json();
        })
        .then(data => {
            if (data.success) {
                fetchNotifications();
            }
        })
        .catch(err => console.error('Errore nel segnare le notifiche come lette:', err));
    }

    function clearAllNotifications() {
        let baseUrl = '';
        const homeLink = document.querySelector('a[href*="/welcome"], a[href*="/matches"]');
        if (homeLink) {
            const href = homeLink.getAttribute('href');
            if (href) {
                if (href.includes('/welcome')) {
                    baseUrl = href.split('/welcome')[0];
                } else if (href.includes('/matches')) {
                    baseUrl = href.split('/matches')[0];
                }
            }
        }

        fetch(baseUrl + '/api/notifications/clear-all', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'csrf_token=' + encodeURIComponent(csrfToken)
        })
        .then(response => {
            if (!response.ok) throw new Error('Errore di rete');
            return response.json();
        })
        .then(data => {
            if (data.success) {
                fetchNotifications();
            }
        })
        .catch(err => console.error('Errore durante lo svuotamento delle notifiche:', err));
    }
});