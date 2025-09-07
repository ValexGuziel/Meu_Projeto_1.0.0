<?php
/**
 * Script para apagar TODAS as Ordens de Serviço do banco de dados.
 *
 * Para executar, acesse http://localhost/Meu_Projeto/api/clear_os.php no seu navegador.
 *
 * IMPORTANTE: Esta ação é IRREVERSÍVEL. Use com cuidado.
 */

require_once 'config.php';

echo "<pre>";
echo "Iniciando o processo de limpeza da tabela 'ordens_servico'...\n";

try {
    // O comando TRUNCATE TABLE é mais rápido que DELETE e reseta o AUTO_INCREMENT
    $pdo->exec("TRUNCATE TABLE ordens_servico");

    echo "\nSUCESSO! Todas as Ordens de Serviço foram apagadas.\n";
    echo "A tabela 'ordens_servico' está limpa e o contador de IDs foi resetado.\n";
    echo "Você já pode apagar este arquivo ('clear_os.php') para evitar execuções acidentais.\n";

} catch (Exception $e) {
    echo "\nERRO: " . $e->getMessage() . "\n";
    echo "Nenhuma alteração foi feita no banco de dados.\n";
} finally {
    echo "\nProcesso finalizado.\n";
    echo "</pre>";
}
?>
