<?php
// Verifica se o ID foi passado na URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("ID do equipamento não fornecido.");
}

$equipamento_id = intval($_GET['id']);

// Configuração do banco de dados
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "industria";

// Conexão
$conn = new mysqli($servername, $username, $password, $dbname);

// Verifica conexão
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// --- Busca dados do equipamento ---
$sql_equip = "SELECT id, nome, tag, setor, descricao, foto FROM equipamentos WHERE id = ?";
$stmt_equip = $conn->prepare($sql_equip);
$stmt_equip->bind_param("i", $equipamento_id);
$stmt_equip->execute();
$result_equip = $stmt_equip->get_result();

if ($result_equip->num_rows === 0) {
    die("Equipamento não encontrado.");
}
$equipamento = $result_equip->fetch_assoc();

// --- Busca histórico de manutenções ---
$sql_manut = "SELECT data_manutencao, tipo_manutencao, descricao, responsavel, custo FROM manutencoes WHERE equipamento_id = ? ORDER BY data_manutencao DESC";
$stmt_manut = $conn->prepare($sql_manut);
$stmt_manut->bind_param("i", $equipamento_id);
$stmt_manut->execute();
$result_manut = $stmt_manut->get_result();

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes do Equipamento: <?php echo htmlspecialchars($equipamento['nome']); ?></title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,500,700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>

<body>
    <div class="container details-container">
        <?php include 'header.php'; ?>
        <h1>Detalhes do Equipamento</h1>

        <div class="details-grid">
            <div>
                <?php if (!empty($equipamento['foto'])) : ?>
                    <img src="uploads/<?php echo htmlspecialchars($equipamento['foto']); ?>" alt="Foto do Equipamento" class="equip-photo">
                <?php endif; ?>
            </div>
            <div>
                <div class="info-item">
                    <strong>TAG de Identificação:</strong>
                    <span><?php echo htmlspecialchars($equipamento['tag']); ?></span>
                </div>
                <div class="info-item">
                    <strong>Nome:</strong>
                    <span><?php echo htmlspecialchars($equipamento['nome']); ?></span>
                </div>
                <div class="info-item">
                    <strong>Setor:</strong>
                    <span><?php echo htmlspecialchars($equipamento['setor']); ?></span>
                </div>
                <div class="info-item">
                    <strong>Descrição:</strong>
                    <span><?php echo htmlspecialchars($equipamento['descricao']); ?></span>
                </div>
            </div>
        </div>

        <h2>Histórico de Manutenções</h2>
        <table class="table-manutencoes">
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Tipo</th>
                    <th>Descrição do Serviço</th>
                    <th>Responsável</th>
                    <th>Custo (R$)</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result_manut->num_rows > 0) : ?>
                    <?php while ($manut = $result_manut->fetch_assoc()) : ?>
                        <tr>
                            <td><?php echo date("d/m/Y", strtotime($manut['data_manutencao'])); ?></td>
                            <td><?php echo htmlspecialchars($manut['tipo_manutencao']); ?></td>
                            <td><?php echo htmlspecialchars($manut['descricao']); ?></td>
                            <td><?php echo htmlspecialchars($manut['responsavel']); ?></td>
                            <td><?php echo number_format($manut['custo'], 2, ',', '.'); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="5" class="no-results">Nenhuma manutenção registrada para este equipamento.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
<?php
$stmt_equip->close();
$stmt_manut->close();
$conn->close();
?>