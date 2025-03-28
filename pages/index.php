<?php

require_once('../backend/session.php');
require_once('../includes/conexao.php');

// Verifica se o usuário já está logado
if (isset($_SESSION['militar_id'])) {
    header("Location: dashboard.php");
    exit();
}

// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identidade_militar = $_POST['identidade_militar'];
    $senha = $_POST['senha'];

    // Consulta para verificar o usuário na tabela usuarios
    $query = "SELECT id, identidade_militar, senha, tipo, primeiro_acesso FROM usuarios WHERE identidade_militar = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $identidade_militar);
    $stmt->execute();
    $result = $stmt->get_result();
    $usuario = $result->fetch_assoc();

    if ($usuario && password_verify($senha, $usuario['senha'])) {
        // Armazena informações na sessão
        $_SESSION['militar_id'] = $usuario['id'];
        $_SESSION['identidade_militar'] = $usuario['identidade_militar'];
        $_SESSION['tipo'] = $usuario['tipo']; // Define se é admin ou comum

        // Verifica se é o primeiro acesso
        if ($usuario['primeiro_acesso'] == 1) {
            // Se for o primeiro acesso, redireciona para a redefinição de senha
            header("Location: redefinir_senha.php");
            exit();
        }

        // Caso contrário, redireciona para o dashboard
        header("Location: dashboard.php");
        exit();
    } else {
        $erro = "Identidade ou senha incorreta.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Militar</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin: 50px;
        }

        form {
            display: inline-block;
            text-align: left;
        }
    </style>
</head>

<body>
    <h2>Login</h2>
    <?php if (isset($erro)) echo "<p style='color:red;'>$erro</p>"; ?>
    <form method="POST">
        <label>Identidade Militar:</label>
        <input type="text" name="identidade_militar" required><br><br>
        <label>Senha:</label>
        <input type="password" name="senha" required><br><br>
        <p><a href="recuperar_senha.php">Esqueceu a senha?</a></p>
        <button type="submit">Entrar</button>
    </form>

</body>

</html>