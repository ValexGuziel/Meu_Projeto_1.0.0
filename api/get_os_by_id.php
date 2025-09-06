<?php
/**
 * API para buscar uma Ordem de Serviço específica pelo seu ID.
 *
 * Retorna todos os dados detalhados de uma única OS, incluindo
 * informações das tabelas relacionadas (equipamentos, localizações, etc.).
 *
 * @param int $_GET['id'] O ID da Ordem de Serviço a ser buscada.
 */
require_once 'config.php';

$os_id = intval($_GET['id'] ?? 0);

if (empty($os_id)) {
    jsonResponse(false, 'ID da OS é obrigatório');
}

try {
    $os = getFullOsDetailsById($pdo, $os_id);
    
    if (!$os) {
        jsonResponse(false, 'Ordem de serviço não encontrada');
    }
    
    jsonResponse(true, 'OS carregada com sucesso', $os);
    
} catch(PDOException $e) {
    jsonResponse(false, 'Erro ao buscar OS: ' . $e->getMessage());
}
?>
