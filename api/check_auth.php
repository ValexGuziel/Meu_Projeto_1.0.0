<?php
session_start();

// Se não houver uma sessão de usuário, retorna um erro 401 (Não Autorizado)
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    jsonResponse(false, 'Acesso não autorizado. Por favor, faça o login.');
    exit();
}
?>