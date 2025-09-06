<?php
/**
 * API para excluir uma Ordem de Serviço.
 *
 * Recebe um ID via POST, verifica se a OS existe e, em caso afirmativo,
 * a remove do banco de dados.
 *
 * @method POST
 * @param int $_POST['id'] O ID da Ordem de Serviço a ser excluída.
 */
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Método não permitido');
}

$os_id = intval($_POST['id'] ?? 0);

if (empty($os_id)) {
    jsonResponse(false, 'ID da OS é obrigatório');
}

try {
    // Verificar se a OS existe
    $stmt = $pdo->prepare("SELECT numero_os FROM ordens_servico WHERE id = ?");
    $stmt->execute([$os_id]);
    $os = $stmt->fetch();
    
    if (!$os) {
        jsonResponse(false, 'Ordem de serviço não encontrada');
    }
    
    // Excluir a ordem de serviço
    $stmt = $pdo->prepare("DELETE FROM ordens_servico WHERE id = ?");
    $stmt->execute([$os_id]);
    
    jsonResponse(true, 'Ordem de Serviço ' . $os['numero_os'] . ' excluída com sucesso!');
    
} catch(PDOException $e) {
    jsonResponse(false, 'Erro ao excluir Ordem de Serviço: ' . $e->getMessage());
}
?>
