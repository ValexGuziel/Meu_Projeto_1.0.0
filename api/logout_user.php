<?php
session_start();

// Destrói todas as variáveis da sessão
$_SESSION = array();

// Destrói a sessão
session_destroy();

require_once 'config.php';

jsonResponse(true, 'Logout realizado com sucesso.');
?>