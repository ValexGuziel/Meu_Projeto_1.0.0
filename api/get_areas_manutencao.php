<?php
/**
 * API para obter todas as áreas de manutenção.
 *
 * Retorna uma lista de todas as áreas de manutenção cadastradas,
 * ordenadas por nome.
 */
require_once 'config.php';

try {
    $stmt = $pdo->query("SELECT id, nome, responsavel, descricao FROM areas_manutencao ORDER BY nome");
    $areas = $stmt->fetchAll();
    
    header('Content-Type: application/json');
    echo json_encode($areas);
    
} catch(PDOException $e) {
    jsonResponse(false, 'Erro ao buscar áreas de manutenção: ' . $e->getMessage());
}
?>
