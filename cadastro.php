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

// Recebe dados do formulário
$nome = $_POST['nome'];
$tag = $_POST['tag'];
$setor = $_POST['setor'];
$descricao = $_POST['descricao'];

// Upload da foto
$foto_nome = "";
if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
    $foto_nome = uniqid() . "_" . basename($_FILES["foto"]["name"]);
    $foto_destino = "uploads/" . $foto_nome;
    move_uploaded_file($_FILES["foto"]["tmp_name"], $foto_destino);
}

// Insere no banco (ainda sem o qrcode_path)
$sql = "INSERT INTO equipamentos (nome, tag, setor, descricao, foto) VALUES (?, ?, ?, ?, ?)";
if (!$stmt = $conn->prepare($sql)) {
    // Adiciona uma verificação caso a preparação da query falhe
    die("Erro na preparação da query: " . $conn->error);
}
$stmt->bind_param("sssss", $nome, $tag, $setor, $descricao, $foto_nome);

if ($stmt->execute()) {
    // Retorna uma resposta JSON com sucesso
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Equipamento cadastrado com sucesso!'
    ]);
} else {
    // Retorna uma resposta JSON com erro
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Erro ao cadastrar: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>