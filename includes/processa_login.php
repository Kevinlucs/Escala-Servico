<?php
session_start();
require_once('../includes/conexao.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $identidade = $_POST['identidade'];
    $senha = $_POST['senha'];

    $stmt = $conn->prepare("SELECT id, senha, primeiro_acesso, tipo FROM usuarios WHERE identidade_militar = ?");
    $stmt->bind_param("s", $identidade);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($senha, $row['senha'])) {
            $_SESSION['id'] = $row['id'];
            $_SESSION['tipo'] = $row['tipo'];
            if ($row['primeiro_acesso'] == 1) {
                header("Location: redefinir_senha.php");
            } else {
                header("Location: dashboard.php");
            }
            exit();
        } else {
            $_SESSION['erro'] = "Senha incorreta!";
            header("Location: index.php");
        }
    } else {
        $_SESSION['erro'] = "Usuário não encontrado!";
        header("Location: index.php");
    }
}
