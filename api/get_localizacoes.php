<?php
/**
 * API para obter todas as localizações.
 *
 * Retorna uma lista de todas as localizações cadastradas,
 * ordenadas por setor e depois por nome.
 */
require_once 'config.php';

try {
    $stmt = $pdo->query("SELECT id, nome, setor, descricao FROM localizacoes ORDER BY setor, nome");
    $localizacoes = $stmt->fetchAll();
    
    header('Content-Type: application/json');
    echo json_encode($localizacoes);
    
} catch(PDOException $e) {
    jsonResponse(false, 'Erro ao buscar localizações: ' . $e->getMessage());
}
?>
