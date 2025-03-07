<?php
session_start();
require_once('../includes/conexao.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $identidade = $_POST['identidade'];

    $stmt = $conn->prepare("SELECT id, email FROM usuarios WHERE identidade_militar = ?");
    $stmt->bind_param("s", $identidade);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $id_usuario = $user['id'];
        $token = bin2hex(random_bytes(16));
        $expiracao = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Salva o token no banco
        $stmt = $conn->prepare("INSERT INTO recuperacao_senha (id_usuario, token, expiracao) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $id_usuario, $token, $expiracao);
        $stmt->execute();

        // Exibir o token para testes (em produção, envie por e-mail)
        $_SESSION['mensagem'] = "Seu código de recuperação: " . $token;
        header("Location: nova_senha.php");
        exit();
    } else {
        $_SESSION['erro'] = "Identidade militar não encontrada!";
        header("Location: recuperar_senha.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Recuperar Senha</title>
</head>

<body>
    <h2>Recuperar Senha</h2>
    <?php if (isset($_SESSION['erro'])) {
        echo "<p style='color: red;'>" . $_SESSION['erro'] . "</p>";
        unset($_SESSION['erro']);
    } ?>
    <form method="POST" action="recuperar_senha.php">
        <label>Identidade Militar:</label>
        <input type="text" name="identidade" required><br><br>
        <button type="submit">Enviar</button>
    </form>
</body>

</html>