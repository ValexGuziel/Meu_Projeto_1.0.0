<?php
/**
 * API para cadastrar uma nova localização.
 *
 * Recebe dados via POST, valida, verifica se a combinação de nome e setor já existe
 * e, se não, a insere no banco de dados.
 *
 * @method POST
 * @param string $_POST['nome_localizacao'] Nome da nova localização.
 * @param string $_POST['setor_localizacao'] Setor da localização.
 * @param string $_POST['descricao_localizacao'] (Opcional) Descrição da localização.
 */
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Método não permitido');
}

$nome = trim($_POST['nome'] ?? '');
$setor = trim($_POST['setor'] ?? '');
$descricao = trim($_POST['descricao'] ?? '');

if (empty($nome) || empty($setor)) {
    jsonResponse(false, 'Nome e setor são obrigatórios');
}

try {
    // Verificar se a localização já existe
    $stmt = $pdo->prepare("SELECT id FROM localizacoes WHERE nome = ? AND setor = ?");
    $stmt->execute([$nome, $setor]);
    
    if ($stmt->fetch()) {
        jsonResponse(false, 'Localização já existe');
    }
    
    // Inserir nova localização
    $stmt = $pdo->prepare("INSERT INTO localizacoes (nome, setor, descricao) VALUES (?, ?, ?)");
    $stmt->execute([$nome, $setor, $descricao]);
    
    jsonResponse(true, 'Localização cadastrada com sucesso');
    
} catch(PDOException $e) {
    jsonResponse(false, 'Erro ao cadastrar localização: ' . $e->getMessage());
}
?>
