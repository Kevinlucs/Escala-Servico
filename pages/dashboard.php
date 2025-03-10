<?php
// Incluir o arquivo de verificação de sessão
require_once('../backend/session.php');
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Painel</title>
</head>

<body>
    <h2>Bem-vindo ao Sistema de Escala de Serviço</h2>
    <nav>
        <ul>
            <?php if ($_SESSION['tipo'] == 'admin') { ?>
                <!-- Exibe o link 'Gerenciar Escala' apenas para administradores -->
                <li><a href="gerenciar_escala.php">Gerenciar Escala</a></li>
            <?php } ?>

            <li><a href="visualizar_escala.php">Visualizar Escala</a></li>

            <?php if ($_SESSION['tipo'] == 'admin') { ?>
                <li><a href="gerar_pdf.php">Gerar PDF</a></li>
            <?php } ?>

            <li>
                <?php if ($_SESSION['tipo'] == 'admin') { ?>
                    <a href="admin_dashboard.php">Folgas</a>
                <?php } else { ?>
                    <a href="folgas.php">Folgas</a>
                <?php } ?>
            </li>
        </ul>
    </nav>
    <p><a href="logout.php">Sair</a></p>
</body>

</html>