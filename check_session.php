<?php
/**
 * Endpoint para verificar se a sessão do usuário está ativa.
 * Retorna um status 200 OK se o usuário estiver logado,
 * e um status 401 Unauthorized caso contrário.
 */

session_start();

header('Content-Type: application/json');

if (isset($_SESSION['user_id'])) {
    http_response_code(200);
    echo json_encode(['success' => true, 'message' => 'Sessão ativa.']);
} else {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Sessão não encontrada ou expirada.']);
}
?>
