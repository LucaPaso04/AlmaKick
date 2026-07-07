/* User profile helper functions */
function copyFriendCode(btn) {
    const textEl = document.getElementById('friendCodeText');
    if (!textEl) return;
    
    const textToCopy = textEl.innerText;

    function handleSuccess() {
        const originalHTML = btn.innerHTML;
        btn.innerHTML = '<span class="bi bi-check-lg"></span>';
        btn.classList.replace('btn-outline-primary', 'btn-success');
        
        setTimeout(() => {
            btn.innerHTML = originalHTML;
            btn.classList.replace('btn-success', 'btn-outline-primary');
        }, 2000);
    }

    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(textToCopy).then(handleSuccess).catch(err => {
            console.error("Errore copia navigator.clipboard: ", err);
            fallbackCopy(textToCopy);
        });
    } else {
        fallbackCopy(textToCopy);
    }

    function fallbackCopy(text) {
        const textArea = document.createElement("textarea");
        textArea.value = text;
        textArea.style.top = "0";
        textArea.style.left = "0";
        textArea.style.position = "fixed";
        textArea.style.opacity = "0";

        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();

        try {
            const successful = document.execCommand('copy');
            if (successful) {
                handleSuccess();
            } else {
                console.error("Copia fallback non riuscita");
            }
        } catch (err) {
            console.error("Errore copia fallback: ", err);
        }

        document.body.removeChild(textArea);
    }
}

function switchSettingsTab(tabId) {
    const tabEl = document.getElementById(tabId);
    if (tabEl) {
        const tab = new bootstrap.Tab(tabEl);
        tab.show();
    }
}

document.addEventListener('DOMContentLoaded', () => {
    // Avatar upload button styling and interaction
    const avatarInput = document.getElementById('avatarInput');
    const avatarLabel = document.querySelector('label[for="avatarInput"]');
    const avatarForm = document.getElementById('avatarForm');

    if (avatarInput && avatarLabel && avatarForm) {
        // Compress and upload avatar
        avatarInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (!file) return;

            if (window.showToast) {
                window.showToast('Elaborazione immagine in corso...', 'info', 3000);
            }

            const reader = new FileReader();
            reader.readAsDataURL(file);
            reader.onload = function(event) {
                const img = new Image();
                img.src = event.target.result;
                img.onload = function() {
                    const canvas = document.createElement('canvas');
                    let width = img.width;
                    let height = img.height;
                    const max_size = 1020;
                    
                    if (width > height) {
                        if (width > max_size) {
                            height *= max_size / width;
                            width = max_size;
                        }
                    } else {
                        if (height > max_size) {
                            width *= max_size / height;
                            height = max_size;
                        }
                    }
                    
                    canvas.width = width;
                    canvas.height = height;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, width, height);
                    
                    canvas.toBlob(function(blob) {
                        if (!blob) {
                            if (window.showToast) {
                                window.showToast('Errore durante l\'elaborazione dell\'immagine.', 'danger');
                            }
                            return;
                        }
                        
                        const formData = new FormData();
                        formData.append('csrf_token', avatarForm.querySelector('input[name="csrf_token"]')?.value || '');
                        formData.append('avatar', blob, 'avatar.jpg');
                        
                        fetch(avatarForm.action, {
                            method: 'POST',
                            body: formData
                        })
                        .then(() => {
                            window.location.reload();
                        })
                        .catch(err => {
                            console.error(err);
                            if (window.showToast) {
                                window.showToast('Errore durante il caricamento.', 'danger');
                            }
                        });
                    }, 'image/jpeg', 0.8);
                };
            };
        });

        // Keydown behavior
        avatarLabel.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                avatarInput.click();
            }
        });

        // Hover scale behavior
        avatarLabel.addEventListener('mouseover', () => {
            avatarLabel.style.transform = 'scale(1.1)';
        });
        avatarLabel.addEventListener('mouseout', () => {
            avatarLabel.style.transform = 'scale(1)';
        });
        avatarLabel.addEventListener('focus', () => {
            avatarLabel.style.transform = 'scale(1.1)';
        });
        avatarLabel.addEventListener('blur', () => {
            avatarLabel.style.transform = 'scale(1)';
        });
    }

    // Match history table rows click behavior
    const historyRows = document.querySelectorAll('.history-match-row');
    historyRows.forEach(row => {
        row.addEventListener('click', () => {
            const url = row.getAttribute('data-href');
            if (url) {
                window.location.href = url;
            }
        });
        row.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                const url = row.getAttribute('data-href');
                if (url) {
                    window.location.href = url;
                }
            }
        });
    });

    // Badge cards click and keydown behavior
    const badgeCards = document.querySelectorAll('.badge-card');
    badgeCards.forEach(card => {
        card.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                card.click();
            }
        });
    });

    // Copy friend code button click behavior
    const copyBtn = document.getElementById('copy-friend-code-btn');
    if (copyBtn) {
        copyBtn.addEventListener('click', () => {
            copyFriendCode(copyBtn);
        });
    }

    // Switch settings tab buttons click behavior
    const switchTabsBtns = document.querySelectorAll('.switch-settings-tab-btn');
    switchTabsBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const targetTab = btn.getAttribute('data-target-tab');
            if (targetTab) {
                switchSettingsTab(targetTab);
            }
        });
    });
});
