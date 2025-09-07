<?php
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

$equipamento_id = 0;
$nome_equipamento = "Equipamento não encontrado";

// Se o ID do equipamento for passado via GET, busca o nome
if (isset($_GET['equipamento_id'])) {
    $equipamento_id = intval($_GET['equipamento_id']);
    $sql_nome = "SELECT nome FROM equipamentos WHERE id = ?";
    $stmt_nome = $conn->prepare($sql_nome);
    $stmt_nome->bind_param("i", $equipamento_id);
    $stmt_nome->execute();
    $result_nome = $stmt_nome->get_result();
    if ($result_nome->num_rows > 0) {
        $equipamento = $result_nome->fetch_assoc();
        $nome_equipamento = $equipamento['nome'];
    }
    $stmt_nome->close();
}

// Processa o formulário quando enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validação e recebimento dos dados
    $equip_id_post = intval($_POST['equipamento_id']);
    $data_manutencao = $_POST['data_manutencao'];
    $tipo_manutencao = $_POST['tipo_manutencao'];
    $descricao = $_POST['descricao'];
    $responsavel = $_POST['responsavel'];
    $custo = floatval(str_replace(',', '.', $_POST['custo'])); // Converte vírgula para ponto

    // Insere no banco
    $sql = "INSERT INTO manutencoes (equipamento_id, data_manutencao, tipo_manutencao, descricao, responsavel, custo) VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("issssd", $equip_id_post, $data_manutencao, $tipo_manutencao, $descricao, $responsavel, $custo);
        
        if ($stmt->execute()) {
            // Redireciona para a página de detalhes do equipamento após o sucesso
            header("Location: detalhes_equipamento.php?id=" . $equip_id_post . "&status=success");
            exit();
        } else {
            echo "Erro ao registrar manutenção: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Erro na preparação da query: " . $conn->error;
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Manutenção</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,500,700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>

<body>
    <div class="container form-manutencao-container">
        <?php include 'header.php'; ?>
        <h1>Registrar Manutenção</h1>
        <p class="sub-header">Para o equipamento: <strong><?php echo htmlspecialchars($nome_equipamento); ?></strong></p>

        <form action="adicionar_manutencao.php" method="POST">
            <input type="hidden" name="equipamento_id" value="<?php echo $equipamento_id; ?>">

            <div class="form-group">
                <label for="data_manutencao">Data da Manutenção:</label>
                <input type="date" id="data_manutencao" name="data_manutencao" required>
            </div>

            <div class="form-group">
                <label for="tipo_manutencao">Tipo de Manutenção:</label>
                <select id="tipo_manutencao" name="tipo_manutencao" required>
                    <option value="Preventiva">Preventiva</option>
                    <option value="Corretiva">Corretiva</option>
                    <option value="Preditiva">Preditiva</option>
                </select>
            </div>

            <div class="form-group">
                <label for="descricao">Descrição do Serviço:</label>
                <textarea id="descricao" name="descricao" rows="4" required></textarea>
            </div>

            <div class="form-group">
                <label for="responsavel">Responsável:</label>
                <input type="text" id="responsavel" name="responsavel" required>
            </div>

            <div class="form-group">
                <label for="custo">Custo (R$):</label>
                <input type="text" id="custo" name="custo" placeholder="Ex: 150,50">
            </div>

            <button type="submit" class="submit-btn btn-manutencao">Registrar Manutenção</button>
        </form>
    </div>
</body>
</html>