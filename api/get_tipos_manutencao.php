<?php
/**
 * API para obter todos os tipos de manutenção.
 *
 * Retorna uma lista de todos os tipos de manutenção cadastrados,
 * ordenados por nome.
 */
require_once 'config.php';

try {
    $stmt = $pdo->query("SELECT id, nome, descricao FROM tipos_manutencao ORDER BY nome");
    $tipos = $stmt->fetchAll();
    
    header('Content-Type: application/json');
    echo json_encode($tipos);
    
} catch(PDOException $e) {
    jsonResponse(false, 'Erro ao buscar tipos de manutenção: ' . $e->getMessage());
}
?>
