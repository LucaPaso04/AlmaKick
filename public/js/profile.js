/* User profile helper functions */
function copyFriendCode(btn) {
    const textEl = document.getElementById('friendCodeText');
    if (!textEl) return;
    
    navigator.clipboard.writeText(textEl.innerText).then(() => {
        const originalHTML = btn.innerHTML;
        btn.innerHTML = '<i class="bi bi-check-lg"></i>';
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
