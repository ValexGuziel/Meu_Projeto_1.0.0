// Este script verifica se o usuário está logado.
// Ele faz uma chamada a um endpoint de verificação de sessão. Se a sessão não for válida (erro 401), redireciona para o login.

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
        // Se a resposta for 'ok' (status 200-299), o usuário está autenticado e nada precisa ser feito.

    } catch (error) {
        // Este bloco 'catch' geralmente captura erros de rede (ex: servidor offline).
        // Nesses casos, é razoável não redirecionar imediatamente,
        // pois o problema pode ser temporário.
        console.error("Falha de rede na verificação de autenticação. O usuário não será redirecionado.", error);
    }
})();
            window.location.href = 'login.html';
        }
    } catch (error) {
        console.error("Falha na verificação de autenticação, redirecionando para login.", error);
        window.location.href = 'login.html';
    }
})();