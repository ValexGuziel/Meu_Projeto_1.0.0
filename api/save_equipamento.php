<?php
/**
 * API para cadastrar um novo equipamento.
 *
 * Recebe dados via POST, valida, verifica se o código do equipamento já existe
 * e, se não, o insere no banco de dados.
 *
 * @method POST
 * @param string $_POST['nome_equipamento'] Nome do novo equipamento.
 * @param string $_POST['codigo_equipamento'] Código único do equipamento.
 * @param string $_POST['descricao_equipamento'] (Opcional) Descrição do equipamento.
 */
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Método não permitido');
}

$nome = trim($_POST['nome'] ?? '');
$codigo = trim($_POST['codigo'] ?? '');
$descricao = trim($_POST['descricao'] ?? '');

if (empty($nome) || empty($codigo)) {
    jsonResponse(false, 'Nome e código são obrigatórios');
}

try {
    // Verificar se o código já existe
    $stmt = $pdo->prepare("SELECT id FROM equipamentos WHERE codigo = ?");
    $stmt->execute([$codigo]);
    
    if ($stmt->fetch()) {
        jsonResponse(false, 'Código de equipamento já existe');
    }
    
    // Inserir novo equipamento
    $stmt = $pdo->prepare("INSERT INTO equipamentos (nome, codigo, descricao) VALUES (?, ?, ?)");
    $stmt->execute([$nome, $codigo, $descricao]);
    
    jsonResponse(true, 'Equipamento cadastrado com sucesso');
    
} catch(PDOException $e) {
    jsonResponse(false, 'Erro ao cadastrar equipamento: ' . $e->getMessage());
}
?>
