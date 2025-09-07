<?php
/**
 * API para gerar o próximo número de Ordem de Serviço.
 *
 * Gera um número de OS sequencial e único para o dia atual,
 * no formato 'NNNN-dd-mm-yy'.
 */
require_once 'config.php';

try {
    // Retornar o próximo número da OS usando a função centralizada
    $nextNumber = getNextOSNumber($pdo);
    jsonResponse(true, 'Número gerado com sucesso', ['numero_os' => $nextNumber]);
} catch(PDOException $e) {
    jsonResponse(false, 'Erro ao gerar número da OS: ' . $e->getMessage());
}
