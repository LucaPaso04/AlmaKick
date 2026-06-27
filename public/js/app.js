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
});