<?php
/**
 * Arquivo de funções reutilizáveis para a API.
 *
 * Este arquivo centraliza lógicas de negócio e acesso a dados que são
 * compartilhadas entre diferentes endpoints da API, promovendo a reutilização
 * de código e facilitando a manutenção.
 */

/**
 * Gera o próximo número de Ordem de Serviço sequencial para o dia corrente.
 *
 * O formato gerado é 'NNNN-dd-mm-yy'.
 *
 * @param PDO $pdo Instância da conexão com o banco de dados.
 * @return string O próximo número de OS formatado.
 * @throws PDOException Se ocorrer um erro no banco de dados.
 */
function getNextOSNumber(PDO $pdo): string {
    // Obter a data atual no formato dd-mm-yy
    $today = date('d-m-y');
    
    // Buscar o último número de OS para hoje
    $stmt = $pdo->prepare("
        SELECT numero_os 
        FROM ordens_servico 
        WHERE numero_os LIKE ? 
        ORDER BY numero_os DESC 
        LIMIT 1
    ");
    
    $pattern = "%-" . $today;
    $stmt->execute([$pattern]);
    $lastOS = $stmt->fetch();
    
    $nextNumber = 1;
    if ($lastOS) {
        // Extrair o número sequencial do último OS
        $parts = explode('-', $lastOS['numero_os']);
        $lastNumber = intval($parts[0]);
        $nextNumber = $lastNumber + 1;
    }
    
    // Formatar o número com 4 dígitos e zeros à esquerda
    $formattedNumber = str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    
    return $formattedNumber . '-' . $today;
}

/**
 * Busca os dados completos de uma Ordem de Serviço pelo seu ID.
 *
 * @param PDO $pdo Instância da conexão com o banco de dados.
 * @param int $os_id O ID da Ordem de Serviço.
 * @return array|false Os dados da OS ou false se não for encontrada.
 */
function getFullOsDetailsById(PDO $pdo, int $os_id) {
    $query = "
        SELECT 
            os.*, e.nome as equipamento_nome, e.codigo as equipamento_codigo,
            l.nome as localizacao_nome, l.setor as localizacao_setor,
            tm.nome as tipo_manutencao_nome, am.nome as area_manutencao_nome,
            am.responsavel as area_responsavel
        FROM ordens_servico os
        JOIN equipamentos e ON os.equipamento_id = e.id JOIN localizacoes l ON os.localizacao_id = l.id
        JOIN tipos_manutencao tm ON os.tipo_manutencao_id = tm.id JOIN areas_manutencao am ON os.area_manutencao_id = am.id
        WHERE os.id = ?
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$os_id]);
    return $stmt->fetch();
}