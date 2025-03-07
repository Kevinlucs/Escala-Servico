<?php
session_start();
require_once('../includes/conexao.php');

if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nova_senha = password_hash($_POST['nova_senha'], PASSWORD_DEFAULT);
    $id = $_SESSION['id'];

    $sql = "UPDATE usuarios SET senha='$nova_senha', primeiro_acesso=0 WHERE id=$id";
    if ($conn->query($sql) === TRUE) {
        header("Location: dashboard.php");
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