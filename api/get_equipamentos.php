<?php
/**
 * API para obter todos os equipamentos.
 *
 * Retorna uma lista de todos os equipamentos cadastrados,
 * ordenados por nome.
 */
require_once 'config.php';

try {
    $stmt = $pdo->query("SELECT id, nome, codigo, descricao FROM equipamentos ORDER BY nome");
    $equipamentos = $stmt->fetchAll();
    
    header('Content-Type: application/json');
    echo json_encode($equipamentos);
    
} catch(PDOException $e) {
    jsonResponse(false, 'Erro ao buscar equipamentos: ' . $e->getMessage());
}
?>
