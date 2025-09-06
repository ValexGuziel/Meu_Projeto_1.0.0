<?php
/**
 * API para cadastrar uma nova Ordem de Serviço.
 *
 * Este script lida com requisições POST para criar uma nova OS.
 * Ele valida os dados recebidos, gera um número de OS único para o dia,
 * insere o registro no banco de dados e retorna os dados da OS criada.
 *
 * @method POST
 * @param int    $_POST['equipamento'] ID do equipamento.
 * @param int    $_POST['localizacao'] ID da localização.
 * @param int    $_POST['tipo_manutencao'] ID do tipo de manutenção.
 * @param int    $_POST['area_manutencao'] ID da área de manutenção.
 * @param string $_POST['prioridade'] Prioridade da OS ('baixa', 'media', 'alta', 'urgente').
 * @param string $_POST['solicitante'] Nome do solicitante.
 * @param string $_POST['data_inicial'] Data de início no formato YYYY-MM-DD.
 * @param string $_POST['data_final'] (Opcional) Data de finalização no formato YYYY-MM-DD.
 * @param string $_POST['descricao_problema'] Descrição detalhada do problema.
 */
require_once 'config.php';
require_once 'check_auth.php'; // Adiciona a verificação de login

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Método não permitido');
}

// Validar e obter os dados do formulário
$equipamento_id = intval($_POST['equipamento'] ?? 0);
$localizacao_id = intval($_POST['localizacao'] ?? 0);
$tipo_manutencao_id = intval($_POST['tipo_manutencao'] ?? 0);
$area_manutencao_id = intval($_POST['area_manutencao'] ?? 0);
$prioridade = trim($_POST['prioridade'] ?? '');
$solicitante = trim($_POST['solicitante'] ?? '');
$status = trim($_POST['status'] ?? 'pendente'); // Define 'pendente' como padrão na criação
$data_inicial = trim($_POST['data_inicial'] ?? '');
$data_final = trim($_POST['data_final'] ?? '');
$descricao_problema = trim($_POST['descricao_problema'] ?? '');

// Validações
if (empty($equipamento_id) || empty($localizacao_id) || empty($tipo_manutencao_id) || 
    empty($area_manutencao_id) || empty($prioridade) || empty($solicitante) || 
    empty($data_inicial) || empty($descricao_problema)) {
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
    // Gerar número único da OS
    $numero_os = getNextOSNumber($pdo);
    
    // Inserir a ordem de serviço
    $stmt = $pdo->prepare("
        INSERT INTO ordens_servico (
            numero_os, equipamento_id, localizacao_id, tipo_manutencao_id,
            area_manutencao_id, prioridade, solicitante, status, data_inicial,
            data_final, descricao_problema
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $numero_os,
        $equipamento_id,
        $localizacao_id,
        $tipo_manutencao_id,
        $area_manutencao_id,
        $prioridade,
        $solicitante,
        $status,
        $data_inicial,
        $data_final ?: null,
        $descricao_problema
    ]);
    
    $os_id = $pdo->lastInsertId();
    
    // Buscar dados completos da OS para retornar
    $os_data = getFullOsDetailsById($pdo, $os_id);
    
    jsonResponse(true, 'Ordem de Serviço cadastrada com sucesso!', [
        'numero_os' => $numero_os,
        'os_data' => $os_data
    ]);
    
} catch(PDOException $e) {
    jsonResponse(false, 'Erro ao cadastrar Ordem de Serviço: ' . $e->getMessage());
}

?>
