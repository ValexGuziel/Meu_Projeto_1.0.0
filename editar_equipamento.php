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
$equipamento = null;

// --- Processa o formulário de atualização (quando enviado via POST) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validação e recebimento dos dados
    $equip_id_post = intval($_POST['id']);
    $nome = $_POST['nome'];
    $tag = $_POST['tag'];
    $setor = $_POST['setor'];
    $descricao = $_POST['descricao'];

    // Prepara o SQL para UPDATE
    $sql = "UPDATE equipamentos SET nome = ?, tag = ?, setor = ?, descricao = ? WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("ssssi", $nome, $tag, $setor, $descricao, $equip_id_post);
        
        if ($stmt->execute()) {
            // Redireciona para a lista de equipamentos após o sucesso
            header("Location: listar_equipamentos.php?status=updated");
            exit();
        } else {
            echo "Erro ao atualizar equipamento: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Erro na preparação da query: " . $conn->error;
    }
}

// --- Busca os dados do equipamento para preencher o formulário (quando a página é carregada via GET) ---
if (isset($_GET['id'])) {
    $equipamento_id = intval($_GET['id']);
    $sql_select = "SELECT id, nome, tag, setor, descricao FROM equipamentos WHERE id = ?";
    $stmt_select = $conn->prepare($sql_select);
    $stmt_select->bind_param("i", $equipamento_id);
    $stmt_select->execute();
    $result = $stmt_select->get_result();
    if ($result->num_rows > 0) {
        $equipamento = $result->fetch_assoc();
    } else {
        die("Equipamento não encontrado.");
    }
    $stmt_select->close();
} else {
    die("ID do equipamento não fornecido.");
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Equipamento</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,500,700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>

<body>
    <div class="container form-edicao-container">
        <h1>Editar Equipamento</h1>

        <?php if ($equipamento) : ?>
            <form action="editar_equipamento.php" method="POST">
                <input type="hidden" name="id" value="<?php echo $equipamento['id']; ?>">

                <div class="form-group">
                    <label for="nome">Nome do Equipamento:</label>
                    <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($equipamento['nome']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="tag">TAG de Identificação:</label>
                    <input type="text" id="tag" name="tag" value="<?php echo htmlspecialchars($equipamento['tag']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="setor">Setor:</label>
                    <input type="text" id="setor" name="setor" value="<?php echo htmlspecialchars($equipamento['setor']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="descricao">Descrição:</label>
                    <textarea id="descricao" name="descricao" rows="4" required><?php echo htmlspecialchars($equipamento['descricao']); ?></textarea>
                </div>

                <button type="submit" class="submit-btn btn-edicao">Salvar Alterações</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>