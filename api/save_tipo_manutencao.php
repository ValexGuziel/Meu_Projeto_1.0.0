<?php
/**
 * API para cadastrar um novo tipo de manutenção.
 *
 * Recebe dados via POST, valida, verifica se o tipo já existe
 * e, se não, o insere no banco de dados.
 *
 * @method POST
 * @param string $_POST['nome_tipo_manutencao'] Nome do novo tipo.
 * @param string $_POST['descricao_tipo_manutencao'] (Opcional) Descrição do tipo.
 */
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Método não permitido');
}

$nome = trim($_POST['nome'] ?? '');
$descricao = trim($_POST['descricao'] ?? '');

if (empty($nome)) {
    jsonResponse(false, 'Nome é obrigatório');
}

try {
    // Verificar se o tipo já existe
    $stmt = $pdo->prepare("SELECT id FROM tipos_manutencao WHERE nome = ?");
    $stmt->execute([$nome]);
    
    if ($stmt->fetch()) {
        jsonResponse(false, 'Tipo de manutenção já existe');
    }
    
    // Inserir novo tipo
    $stmt = $pdo->prepare("INSERT INTO tipos_manutencao (nome, descricao) VALUES (?, ?)");
    $stmt->execute([$nome, $descricao]);
    
    jsonResponse(true, 'Tipo de manutenção cadastrado com sucesso');
    
} catch(PDOException $e) {
    jsonResponse(false, 'Erro ao cadastrar tipo de manutenção: ' . $e->getMessage());
}
?>
