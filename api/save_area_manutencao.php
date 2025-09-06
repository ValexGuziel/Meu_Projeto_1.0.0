<?php
/**
 * API para cadastrar uma nova área de manutenção.
 *
 * Recebe dados via POST, valida, verifica se a área já existe
 * e, se não, a insere no banco de dados.
 *
 * @method POST
 * @param string $_POST['nome_area_manutencao'] Nome da nova área.
 * @param string $_POST['responsavel_area'] Nome do responsável pela área.
 * @param string $_POST['descricao_area_manutencao'] (Opcional) Descrição da área.
 */
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Método não permitido');
}

$nome = trim($_POST['nome'] ?? '');
$responsavel = trim($_POST['responsavel'] ?? '');
$descricao = trim($_POST['descricao'] ?? '');

if (empty($nome) || empty($responsavel)) {
    jsonResponse(false, 'Nome e responsável são obrigatórios');
}

try {
    // Verificar se a área já existe
    $stmt = $pdo->prepare("SELECT id FROM areas_manutencao WHERE nome = ?");
    $stmt->execute([$nome]);
    
    if ($stmt->fetch()) {
        jsonResponse(false, 'Área de manutenção já existe');
    }
    
    // Inserir nova área
    $stmt = $pdo->prepare("INSERT INTO areas_manutencao (nome, responsavel, descricao) VALUES (?, ?, ?)");
    $stmt->execute([$nome, $responsavel, $descricao]);
    
    jsonResponse(true, 'Área de manutenção cadastrada com sucesso');
    
} catch(PDOException $e) {
    jsonResponse(false, 'Erro ao cadastrar área de manutenção: ' . $e->getMessage());
}
?>
