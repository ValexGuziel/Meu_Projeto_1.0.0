<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Método não permitido');
}

$nome = trim($_POST['nome'] ?? '');
$email = trim($_POST['email'] ?? '');
$senha = trim($_POST['senha'] ?? '');

if (empty($nome) || empty($email) || empty($senha)) {
    jsonResponse(false, 'Todos os campos são obrigatórios.');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse(false, 'Formato de e-mail inválido.');
}

if (strlen($senha) < 6) {
    jsonResponse(false, 'A senha deve ter no mínimo 6 caracteres.');
}

try {
    // Verificar se o e-mail já existe
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        jsonResponse(false, 'Este e-mail já está cadastrado.');
    }

    // Criptografar a senha
    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)");
    $stmt->execute([$nome, $email, $senha_hash]);

    jsonResponse(true, 'Usuário cadastrado com sucesso! Você já pode fazer o login.');
} catch (PDOException $e) {
    jsonResponse(false, 'Erro no banco de dados: ' . $e->getMessage());
}