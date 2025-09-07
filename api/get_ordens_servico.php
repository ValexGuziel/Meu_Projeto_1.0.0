<?php
/**
 * API para buscar e filtrar Ordens de Serviço com paginação.
 *
 * Retorna uma lista paginada de Ordens de Serviço, permitindo filtros
 * por termo de busca, status, prioridade e data.
 *
 * @param int    $_GET['pagina'] (Opcional) Número da página. Padrão: 1.
 * @param int    $_GET['limite'] (Opcional) Itens por página. Padrão: 10.
 * @param string $_GET['busca'] (Opcional) Termo para buscar em número da OS, equipamento ou solicitante.
 * @param string $_GET['status'] (Opcional) Filtra pelo status da OS.
 * @param string $_GET['prioridade'] (Opcional) Filtra pela prioridade da OS.
 * @param string $_GET['data'] (Opcional) Filtra pela data inicial no formato YYYY-MM-DD.
 */
require_once 'config.php';

// Parâmetros de paginação e filtros
$pagina = intval($_GET['pagina'] ?? 1);
$limite = intval($_GET['limite'] ?? 10);
$offset = ($pagina - 1) * $limite;

$busca = trim($_GET['busca'] ?? '');
$status = trim($_GET['status'] ?? '');
$prioridade = trim($_GET['prioridade'] ?? '');
$data = trim($_GET['data'] ?? '');

try {
    // Construir a query base
    $query = "
        SELECT 
            os.*,
            e.nome as equipamento_nome,
            e.codigo as equipamento_codigo,
            l.nome as localizacao_nome,
            l.setor as localizacao_setor,
            tm.nome as tipo_manutencao_nome,
            am.nome as area_manutencao_nome,
            am.responsavel as area_responsavel
        FROM ordens_servico os
        JOIN equipamentos e ON os.equipamento_id = e.id
        JOIN localizacoes l ON os.localizacao_id = l.id
        JOIN tipos_manutencao tm ON os.tipo_manutencao_id = tm.id
        JOIN areas_manutencao am ON os.area_manutencao_id = am.id
        WHERE 1=1
    ";
    
    $params = [];
    
    // Aplicar filtros
    if (!empty($busca)) {
        $query .= " AND (os.numero_os LIKE ? OR e.nome LIKE ? OR e.codigo LIKE ? OR os.solicitante LIKE ?)";
        $buscaParam = "%$busca%";
        $params = array_merge($params, [$buscaParam, $buscaParam, $buscaParam, $buscaParam]);
    }
    
    if (!empty($status)) {
        $query .= " AND os.status = ?";
        $params[] = $status;
    }
    
    if (!empty($prioridade)) {
        $query .= " AND os.prioridade = ?";
        $params[] = $prioridade;
    }
    
    if (!empty($data)) {
        $query .= " AND DATE(os.data_inicial) = ?";
        $params[] = $data;
    }
    
    // Query para contar total de registros
    $countQuery = "SELECT COUNT(*) as total FROM ($query) as subquery";
    $stmt = $pdo->prepare($countQuery);
    $stmt->execute($params);
    $totalRegistros = $stmt->fetch()['total'];
    
    // Query principal com paginação
    $query .= " ORDER BY os.created_at DESC LIMIT $limite OFFSET $offset";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $ordens = $stmt->fetchAll();
    
    // Calcular informações de paginação
    $totalPaginas = ceil($totalRegistros / $limite);
    
    jsonResponse(true, 'Ordens de serviço carregadas com sucesso', [
        'ordens' => $ordens,
        'paginacao' => [
            'pagina_atual' => $pagina,
            'total_paginas' => $totalPaginas,
            'total_registros' => $totalRegistros,
            'registros_por_pagina' => $limite
        ]
    ]);
    
} catch(PDOException $e) {
    jsonResponse(false, 'Erro ao buscar ordens de serviço: ' . $e->getMessage());
}
?>
