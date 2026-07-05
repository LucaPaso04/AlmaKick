/* User profile helper functions */
function copyFriendCode(btn) {
    const textEl = document.getElementById('friendCodeText');
    if (!textEl) return;
    
    navigator.clipboard.writeText(textEl.innerText).then(() => {
        const originalHTML = btn.innerHTML;
        btn.innerHTML = '<span class="bi bi-check-lg"></span>';
        btn.classList.replace('btn-outline-primary', 'btn-success');
        
        setTimeout(() => {
            btn.innerHTML = originalHTML;
            btn.classList.replace('btn-success', 'btn-outline-primary');
        }, 2000);
    }).catch(err => {
        console.error("Errore durante la copia negli appunti: ", err);
    });
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
        // Change submit
        avatarInput.addEventListener('change', () => {
            avatarForm.submit();
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
