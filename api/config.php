<?php
/**
 * Arquivo de configuração principal.
 *
 * Estabelece a conexão com o banco de dados MySQL usando PDO e define
 * uma função auxiliar para padronizar as respostas JSON da API.
 */
$host = 'localhost';
$dbname = 'sistema_os';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}

// Inclui o arquivo de funções reutilizáveis
require_once 'functions.php';

/**
 * Gera uma resposta JSON padronizada e encerra o script.
 *
 * @param bool   $success Indica se a operação foi bem-sucedida.
 * @param string $message Mensagem descritiva sobre o resultado da operação.
 * @param mixed  $data    (Opcional) Dados a serem retornados no corpo da resposta.
 *
 * @return void
 */
function jsonResponse($success, $message = '', $data = null) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}
?>
