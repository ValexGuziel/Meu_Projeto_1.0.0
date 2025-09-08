// Este script verifica se o usuário está logado.
// Ele faz uma chamada a um endpoint de verificação de sessão. Se a sessão não for válida (erro 401), redireciona para o login.
// Ele também controla o acesso a funcionalidades restritas, como o botão de cadastrar OS.

/**
 * Desativa o botão de "Nova OS" para usuários não administradores.
 */
function disableCreateOsButton() {
    // Espera o DOM carregar para garantir que o botão exista.
    document.addEventListener('DOMContentLoaded', () => {
        const newOsButton = document.getElementById('new-os-btn');
        if (newOsButton) {
            newOsButton.disabled = true;
            newOsButton.title = 'Apenas administradores podem cadastrar novas Ordens de Serviço.';
        }
    });
}

(async function checkAuthentication() {
    // Usamos um endpoint dedicado para verificar a sessão, tornando o código mais robusto.
    const verificationEndpoint = 'api/check_session.php';

    try {
        const response = await fetch(verificationEndpoint, {
            method: 'GET',
            headers: {
                'Accept': 'application/json'
            }
        });

        // Se a resposta não for OK, verificamos o status.
        // Apenas o status 401 (Não Autorizado) deve causar um redirecionamento.
        if (!response.ok) {
            if (response.status === 401) {
                window.location.href = 'login.html';
            }
            // Para outros erros (ex: 500), podemos apenas logar sem redirecionar,
            // evitando que uma falha temporária no servidor deslogue o usuário.
            console.error(`Erro na verificação de sessão: Status ${response.status}`);
        }
        // Se a resposta for 'ok' (status 200-299), o usuário está autenticado.
        // Verificamos se é o administrador para habilitar o botão.
        else {
            const data = await response.json();
            const user = data.user;
            const isAdmin = user && user.email === 'claudio.ramos@mastpet.com.br';

            if (!isAdmin) {
                disableCreateOsButton();
            }
        }

    } catch (error) {
        // Este bloco 'catch' geralmente captura erros de rede (ex: servidor offline).
        // Nesses casos, é razoável não redirecionar imediatamente,
        // pois o problema pode ser temporário.
        console.error("Falha de rede na verificação de autenticação. O usuário não será redirecionado.", error);
    }
})();