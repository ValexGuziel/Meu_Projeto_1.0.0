document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('login-form');
    const registerForm = document.getElementById('register-form');

    if (loginForm) {
        loginForm.addEventListener('submit', handleAuthFormSubmit);
    }

    if (registerForm) {
        registerForm.addEventListener('submit', handleAuthFormSubmit);
    }
});

async function handleAuthFormSubmit(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    const endpoint = form.id === 'login-form' ? 'api/login_user.php' : 'api/register_user.php';

    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processando...';
    submitBtn.disabled = true;

    try {
        const response = await fetch(endpoint, {
            method: 'POST',
            body: formData
        });

        if (!response.ok) {
            throw new Error('Falha na comunicação com o servidor.');
        }

        const result = await response.json();

        if (result.success) {
            showToast(result.message, 'success');
            
            // Se for login bem-sucedido, redireciona para a página principal
            if (form.id === 'login-form') {
                setTimeout(() => {
                    window.location.href = 'index.html';
                }, 1500);
            } else {
                // Se for cadastro, limpa o formulário
                form.reset();
            }
        } else {
            showToast(result.message, 'error');
        }

    } catch (error) {
        console.error('Erro de autenticação:', error);
        showToast('Ocorreu um erro inesperado. Tente novamente.', 'error');
    } finally {
        // Restaura o botão
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }
}