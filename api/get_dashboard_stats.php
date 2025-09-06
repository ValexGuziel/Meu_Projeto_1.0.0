<?php
/**
 * API para obter estatísticas para o dashboard.
 *
 * Retorna dados agregados sobre as Ordens de Serviço, como contagem
 * por status e por prioridade, para serem usados na renderização de gráficos.
 */
require_once 'config.php';

try {
    // Contagem de OS por status
    $stmtStatus = $pdo->query("
        SELECT status, COUNT(*) as total
        FROM ordens_servico
        GROUP BY status
    ");
    $statsByStatus = $stmtStatus->fetchAll(PDO::FETCH_KEY_PAIR);

    // Contagem de OS por prioridade
    $stmtPriority = $pdo->query("
        SELECT prioridade, COUNT(*) as total
        FROM ordens_servico
        GROUP BY prioridade
    ");
    $statsByPriority = $stmtPriority->fetchAll(PDO::FETCH_KEY_PAIR);

    // Contagem de OS por responsável (manutentor)
    $stmtMaintainer = $pdo->query("
        SELECT am.responsavel, COUNT(os.id) as total
        FROM ordens_servico os
        JOIN areas_manutencao am ON os.area_manutencao_id = am.id
        GROUP BY am.responsavel
        ORDER BY total DESC
    ");
    $statsByMaintainer = $stmtMaintainer->fetchAll(PDO::FETCH_ASSOC);

    // Top 5 equipamentos com mais OS
    $stmtTopEquipment = $pdo->query("
        SELECT 
            e.nome as equipamento_nome,
            COUNT(os.id) as total_os
        FROM ordens_servico os
        JOIN equipamentos e ON os.equipamento_id = e.id
        GROUP BY e.id, e.nome
        ORDER BY total_os DESC
        LIMIT 5
    ");
    $statsTopEquipment = $stmtTopEquipment->fetchAll(PDO::FETCH_ASSOC);

    // Total de OS
    $totalOS = $pdo->query("SELECT COUNT(*) FROM ordens_servico")->fetchColumn();

    $data = [
        'byStatus' => $statsByStatus,
        'byPriority' => $statsByPriority,
        'byMaintainer' => $statsByMaintainer,
        'topEquipment' => $statsTopEquipment,
        'totalOS' => $totalOS,
    ];

    jsonResponse(true, 'Estatísticas carregadas com sucesso', $data);

} catch (PDOException $e) {
    jsonResponse(false, 'Erro ao buscar estatísticas: ' . $e->getMessage());
}
?>