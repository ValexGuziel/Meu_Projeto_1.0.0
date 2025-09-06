<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Método não permitido');
}

$email = trim($_POST['email'] ?? '');
$senha = trim($_POST['senha'] ?? '');

if (empty($email) || empty($senha)) {
    jsonResponse(false, 'E-mail e senha são obrigatórios.');
}

try {
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario && password_verify($senha, $usuario['senha'])) {
        // Login bem-sucedido, iniciar sessão
        session_start();
        $_SESSION['user_id'] = $usuario['id'];
        $_SESSION['user_name'] = $usuario['nome'];

        jsonResponse(true, 'Login realizado com sucesso!');
    } else {
        // Credenciais inválidas
        jsonResponse(false, 'E-mail ou senha inválidos.');
    }
} catch (PDOException $e) {
    jsonResponse(false, 'Erro no banco de dados: ' . $e->getMessage());
}