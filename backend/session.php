<?php
// Iniciar a sessão
session_start();

// Definir o tempo de expiração da sessão (1 hora = 3600 segundos)
$session_timeout = 3600;

// Verifica se a sessão está iniciada
if (isset($_SESSION['last_activity'])) {
    // Verifica se o tempo de inatividade foi superior ao limite de 1 hora
    if (time() - $_SESSION['last_activity'] > $session_timeout) {
        // Destrói a sessão e desconecta o usuário
        session_unset();
        session_destroy();
        header("Location: index.php");  // Redireciona para a página de login
        exit();
    }
}

// Atualiza o timestamp de última atividade
$_SESSION['last_activity'] = time();

// Verifica se o usuário já está logado
if (isset($_SESSION['militar_id'])) {
    // Verifica se está tentando acessar a página de login
    $current_page = basename($_SERVER['PHP_SELF']);
    if ($current_page == 'index.php') {
        header("Location: dashboard.php"); // Redireciona diretamente para o painel
        exit();
    }
}
