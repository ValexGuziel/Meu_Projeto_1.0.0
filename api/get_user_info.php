<?php
require_once 'config.php';
session_start();

if (isset($_SESSION['user_id']) && isset($_SESSION['user_name'])) {
    $data = [
        'user_id' => $_SESSION['user_id'],
        'user_name' => $_SESSION['user_name']
    ];
    jsonResponse(true, 'Informações do usuário carregadas.', $data);
} else {
    jsonResponse(false, 'Usuário não autenticado.');
}
?>