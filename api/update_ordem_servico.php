<?php
/**
 * API para atualizar uma Ordem de Serviço existente.
 *
 * Este script lida com requisições POST para atualizar os dados de uma OS
 * com base no ID fornecido. Ele valida todos os campos obrigatórios,
 * atualiza o registro no banco de dados e retorna os dados atualizados.
 *
 * @method POST
 * @param int    $_POST['os_id'] ID da Ordem de Serviço a ser atualizada.
 * @param int    $_POST['equipamento'] ID do equipamento.
 * @param int    $_POST['localizacao'] ID da localização.
 * @param int    $_POST['tipo_manutencao'] ID do tipo de manutenção.
 * @param int    $_POST['area_manutencao'] ID da área de manutenção.
 * @param string $_POST['prioridade'] Prioridade da OS ('baixa', 'media', 'alta', 'urgente').
 * @param string $_POST['solicitante'] Nome do solicitante.
 * @param string $_POST['status'] Status da OS ('pendente', 'em_andamento', 'concluida', 'cancelada').
 * @param string $_POST['data_inicial'] Data de início no formato YYYY-MM-DD.
 * @param string $_POST['data_final'] (Opcional) Data de finalização no formato YYYY-MM-DD.
 * @param string $_POST['descricao_problema'] Descrição detalhada do problema.
 */
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Método não permitido');
}

// Validar e obter os dados do formulário
$os_id = intval($_POST['os_id'] ?? 0);
$equipamento_id = intval($_POST['equipamento'] ?? 0);
$localizacao_id = intval($_POST['localizacao'] ?? 0);
$tipo_manutencao_id = intval($_POST['tipo_manutencao'] ?? 0);
$area_manutencao_id = intval($_POST['area_manutencao'] ?? 0);
$prioridade = trim($_POST['prioridade'] ?? '');
$solicitante = trim($_POST['solicitante'] ?? '');
$status = trim($_POST['status'] ?? '');
$data_inicial = trim($_POST['data_inicial'] ?? '');
$data_final = trim($_POST['data_final'] ?? '');
$descricao_problema = trim($_POST['descricao_problema'] ?? '');

// Validações
if (empty($os_id) || empty($equipamento_id) || empty($localizacao_id) || empty($tipo_manutencao_id) || 
    empty($area_manutencao_id) || empty($prioridade) || empty($solicitante) || 
    empty($status) || empty($data_inicial) || empty($descricao_problema)) {
    jsonResponse(false, 'Todos os campos obrigatórios devem ser preenchidos');
}

// Validar formato da data
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data_inicial)) {
    jsonResponse(false, 'Formato de data inicial inválido');
}

if (!empty($data_final) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data_final)) {
    jsonResponse(false, 'Formato de data final inválido');
}

// Validar se a data final é posterior à data inicial
if (!empty($data_final) && $data_final < $data_inicial) {
    jsonResponse(false, 'A data final deve ser posterior à data inicial');
}

try {
    // Verificar se a OS existe
    $stmt = $pdo->prepare("SELECT id FROM ordens_servico WHERE id = ?");
    $stmt->execute([$os_id]);
    
    if (!$stmt->fetch()) {
        jsonResponse(false, 'Ordem de serviço não encontrada');
    }
    
    // Atualizar a ordem de serviço
    $stmt = $pdo->prepare("
        UPDATE ordens_servico SET 
            equipamento_id = ?, localizacao_id = ?, tipo_manutencao_id = ?, 
            area_manutencao_id = ?, prioridade = ?, solicitante = ?, status = ?,
            data_inicial = ?, data_final = ?, descricao_problema = ?, updated_at = CURRENT_TIMESTAMP
        WHERE id = ?
    ");
    
    $stmt->execute([
        $equipamento_id,
        $localizacao_id,
        $tipo_manutencao_id,
        $area_manutencao_id,
        $prioridade,
        $solicitante,
        $status,
        $data_inicial,
        $data_final ?: null,
        $descricao_problema,
        $os_id
    ]);
    
    // Buscar dados atualizados da OS
    $os_data = getFullOsDetailsById($pdo, $os_id);
    
    jsonResponse(true, 'Ordem de Serviço atualizada com sucesso!', $os_data);
    
} catch(PDOException $e) {
    jsonResponse(false, 'Erro ao atualizar Ordem de Serviço: ' . $e->getMessage());
}
?>
