<?php
/**
 * API para obter estatísticas para o dashboard.
 *
 * Retorna dados agregados sobre as Ordens de Serviço, como contagem
 * por status e por prioridade, para serem usados na renderização de gráficos.
 */
require_once 'config.php';

// Pega as datas do filtro, se existirem
$startDate = $_GET['start_date'] ?? null;
$endDate = $_GET['end_date'] ?? null;

$dateFilterWhere = '';
$dateFilterAnd = '';
$dateFilterAndFinal = ''; // Novo filtro para data_final
$params = [];

if ($startDate && $endDate) {
    // Filtra pelo campo data_inicial da OS
    $dateFilterWhere = 'WHERE os.data_inicial BETWEEN ? AND ?';
    $dateFilterAnd = 'AND os.data_inicial BETWEEN ? AND ?';
    // Filtra pelo campo data_final da OS para os cálculos de conclusão
    $dateFilterAndFinal = 'AND os.data_final BETWEEN ? AND ?';
    $params[] = $startDate;
    $params[] = $endDate;
}

try {
    // Contagem de OS por status
    $stmtStatus = $pdo->prepare("
        SELECT status, COUNT(*) as total
        FROM ordens_servico os
        $dateFilterWhere
        GROUP BY status
    ");
    $stmtStatus->execute($params);
    $statsByStatus = $stmtStatus->fetchAll(PDO::FETCH_KEY_PAIR);

    // Contagem de OS por prioridade
    $stmtPriority = $pdo->prepare("
        SELECT prioridade, COUNT(*) as total
        FROM ordens_servico os
        $dateFilterWhere
        GROUP BY prioridade
    ");
    $stmtPriority->execute($params);
    $statsByPriority = $stmtPriority->fetchAll(PDO::FETCH_KEY_PAIR);

    // Contagem de OS por responsável (manutentor)
    $stmtMaintainer = $pdo->prepare("
        SELECT am.responsavel, COUNT(os.id) as total
        FROM ordens_servico os
        JOIN areas_manutencao am ON os.area_manutencao_id = am.id
        $dateFilterWhere
        GROUP BY am.responsavel
        ORDER BY total DESC
    ");
    $stmtMaintainer->execute($params);
    $statsByMaintainer = $stmtMaintainer->fetchAll(PDO::FETCH_ASSOC);

    // Top 5 equipamentos com mais OS
    $stmtTopEquipment = $pdo->prepare("
        SELECT 
            e.nome as equipamento_nome,
            COUNT(os.id) as total_os
        FROM ordens_servico os
        JOIN equipamentos e ON os.equipamento_id = e.id
        $dateFilterWhere
        GROUP BY e.id, e.nome
        ORDER BY total_os DESC
        LIMIT 5
    ");
    $stmtTopEquipment->execute($params);
    $statsTopEquipment = $stmtTopEquipment->fetchAll(PDO::FETCH_ASSOC);

    // Calcular tempo de parada por equipamento para OS concluídas
    $stmtDowntime = $pdo->prepare("
        SELECT
            e.nome as equipamento_nome,
            SUM(DATEDIFF(os.data_final, os.data_inicial)) as total_dias_parado
        FROM ordens_servico os
        JOIN equipamentos e ON os.equipamento_id = e.id
        WHERE os.status = 'concluida' AND os.data_final IS NOT NULL AND os.data_inicial IS NOT NULL $dateFilterAndFinal
        GROUP BY e.id, e.nome
        HAVING total_dias_parado > 0
        ORDER BY total_dias_parado DESC
        LIMIT 10 -- Limita aos 10 equipamentos com maior tempo de parada
    ");
    $stmtDowntime->execute($params);
    $statsDowntime = $stmtDowntime->fetchAll(PDO::FETCH_ASSOC);

    // Total de OS
    $stmtTotal = $pdo->prepare("SELECT COUNT(*) FROM ordens_servico os $dateFilterWhere");
    $stmtTotal->execute($params);
    $totalOS = $stmtTotal->fetchColumn();

    // Calcular o tempo médio de conclusão (MTTR em dias)
    $stmtMttr = $pdo->prepare("
        SELECT AVG(DATEDIFF(data_final, data_inicial)) as avg_completion_time
        FROM ordens_servico os
        WHERE status = 'concluida' AND data_final IS NOT NULL AND data_inicial IS NOT NULL $dateFilterAndFinal
    ");
    $stmtMttr->execute($params);
    $avgCompletionTime = $stmtMttr->fetchColumn();

    $data = [
        'byStatus' => $statsByStatus,
        'byPriority' => $statsByPriority,
        'byMaintainer' => $statsByMaintainer,
        'topEquipment' => $statsTopEquipment,
        'equipmentDowntime' => $statsDowntime,
        'totalOS' => $totalOS,
        // Formata para 1 casa decimal ou mostra 'N/A' se não houver dados
        'avgCompletionTime' => $avgCompletionTime !== null ? number_format($avgCompletionTime, 1) : 'N/A',
    ];

    jsonResponse(true, 'Estatísticas carregadas com sucesso', $data);

} catch (PDOException $e) {
    jsonResponse(false, 'Erro ao buscar estatísticas: ' . $e->getMessage());
}
?>