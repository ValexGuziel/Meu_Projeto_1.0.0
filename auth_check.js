// Este script verifica se o usuário está logado.
// Ele faz uma chamada a um endpoint protegido. Se falhar (erro 401), redireciona para o login.

(async function checkAuthentication() {
    try {
        // Usamos get_dashboard_stats.php pois ele já está protegido
        const response = await fetch('api/get_dashboard_stats.php');
        if (response.status === 401) {
            window.location.href = 'login.html';
        }
    } catch (error) {
        console.error("Falha na verificação de autenticação, redirecionando para login.", error);
        window.location.href = 'login.html';
    }
})();