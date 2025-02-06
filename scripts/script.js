document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('form');

    form.addEventListener('submit', (e) => {
        const email = document.querySelector('input[name="email"]');
        const senha = document.querySelector('input[name="senha"]');
        const errorMessage = document.querySelector('.error-message');

        if (errorMessage) {
            errorMessage.remove();
        }

        if (email.value.trim() === '') {
            e.preventDefault();
            showError('Preencha seu e-mail');
        } else if (senha.value.trim() === '') {
            e.preventDefault();
            showError('Preencha sua senha');
        }
    });

    function showError(message) {
        const form = document.querySelector('form');
        const errorDiv = document.createElement('div');
        errorDiv.classList.add('error-message');
        errorDiv.textContent = message;
        form.insertBefore(errorDiv, form.firstChild);
    }
});
