document.addEventListener('DOMContentLoaded', function () {
    const passwordInput = document.getElementById('password');
    const strengthBar = document.querySelector('.password-strength-bar');
    const strengthText = document.getElementById('password-strength-text');

    if (!passwordInput || !strengthBar || !strengthText) {
        return;
    }

    function updatePasswordStrength(password) {
        let score = 0;

        if (password.length === 0) {
            strengthBar.className = 'password-strength-bar';
            strengthBar.style.width = '0%';
            strengthText.textContent = 'Inserisci almeno 6 caratteri.';
            strengthText.className = 'form-text password-strength-text';
            return;
        }

        if (password.length >= 6) score++;
        if (password.length >= 10) score++;
        if (/[A-Z]/.test(password)) score++;
        if (/[0-9]/.test(password)) score++;
        if (/[^A-Za-z0-9]/.test(password)) score++;

        switch (score) {
            case 1:
            case 2:
                strengthBar.className = 'password-strength-bar strength-weak';
                strengthBar.style.width = '33%';
                strengthText.textContent = 'Password debole';
                strengthText.className = 'form-text password-strength-text text-danger-custom';
                break;
            case 3:
            case 4:
                strengthBar.className = 'password-strength-bar strength-medium';
                strengthBar.style.width = '66%';
                strengthText.textContent = 'Password media';
                strengthText.className = 'form-text password-strength-text text-warning-custom';
                break;
            case 5:
                strengthBar.className = 'password-strength-bar strength-strong';
                strengthBar.style.width = '100%';
                strengthText.textContent = 'Password sicura!';
                strengthText.className = 'form-text password-strength-text text-success-custom';
                break;
            default:
                strengthBar.className = 'password-strength-bar';
                strengthBar.style.width = '0%';
                strengthText.textContent = 'Inserisci almeno 6 caratteri.';
                strengthText.className = 'form-text password-strength-text';
        }
    }

    passwordInput.addEventListener('input', function () {
        updatePasswordStrength(passwordInput.value);
    });

    updatePasswordStrength(passwordInput.value);
});