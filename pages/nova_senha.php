<?php
session_start();
require_once('../includes/conexao.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $token = $_POST['token'];
    $nova_senha = password_hash($_POST['nova_senha'], PASSWORD_DEFAULT);

    // Verifica se o token é válido
    $stmt = $conn->prepare("SELECT id_usuario FROM recuperacao_senha WHERE token = ? AND expiracao > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $id_usuario = $row['id_usuario'];

        // Atualiza a senha do usuário
        $stmt = $conn->prepare("UPDATE usuarios SET senha = ? WHERE id = ?");
        $stmt->bind_param("si", $nova_senha, $id_usuario);
        $stmt->execute();

        // Remove o token usado
        $stmt = $conn->prepare("DELETE FROM recuperacao_senha WHERE id_usuario = ?");
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();

        $_SESSION['mensagem'] = "Senha redefinida com sucesso!";
        header("Location: index.php");
        exit();
    } else {
        $_SESSION['erro'] = "Código inválido ou expirado!";
        header("Location: nova_senha.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Nova Senha</title>
</head>

<body>
    <h2>Redefinir Senha</h2>
    <?php if (isset($_SESSION['mensagem'])) {
        echo "<p style='color: green;'>" . $_SESSION['mensagem'] . "</p>";
        unset($_SESSION['mensagem']);
    } ?>
    <?php if (isset($_SESSION['erro'])) {
        echo "<p style='color: red;'>" . $_SESSION['erro'] . "</p>";
        unset($_SESSION['erro']);
    } ?>
    <form method="POST" action="nova_senha.php">
        <label>Código de Recuperação:</label>
        <input type="text" name="token" required><br><br>
        <label>Nova Senha:</label>
        <input type="password" name="nova_senha" required><br><br>
        <button type="submit">Redefinir Senha</button>
    </form>
</body>

</html>