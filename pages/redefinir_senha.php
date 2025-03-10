<?php
// Incluir o arquivo de verificação de sessão
require_once('../backend/session.php'); // Caminho ajustado para o arquivo session.php

require_once('../includes/conexao.php');

// Verifica se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Cria o hash da nova senha
    $nova_senha = password_hash($_POST['nova_senha'], PASSWORD_DEFAULT);
    $id = $_SESSION['id'];

    // Atualiza a senha no banco de dados
    $sql = "UPDATE usuarios SET senha='$nova_senha', primeiro_acesso=0 WHERE id=$id";
    if ($conn->query($sql) === TRUE) {
        header("Location: dashboard.php"); // Redireciona para o painel após sucesso
        exit();
    } else {
        echo "Erro ao atualizar senha!";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Redefinir Senha</title>
</head>

<body>
    <h2>Redefina sua senha</h2>
    <form method="POST">
        <label>Nova Senha:</label>
        <input type="password" name="nova_senha" required><br>
        <button type="submit">Salvar</button>
    </form>
</body>

</html>