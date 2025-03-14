<?php
// Incluir o arquivo de verificação de sessão
require_once('../backend/session.php'); // Caminho ajustado para o arquivo session.php

// Incluir a conexão com o banco de dados
require_once('../includes/conexao.php');

// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Lógica para inserir a escala no banco de dados
    $data_servico = $_POST['data_servico'];
    $tipo_escala = $_POST['tipo_escala'];
    $id_responsavel = $_POST['id_responsavel'];

    // Insere a escala
    $stmt = $conn->prepare("INSERT INTO escalas (data_servico, tipo_escala, id_responsavel) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $data_servico, $tipo_escala, $id_responsavel);
    if ($stmt->execute()) {
        $id_escala = $stmt->insert_id; // Obtemos o id da escala inserida
        $sucesso = "Escala cadastrada com sucesso!"; // Mensagem de sucesso
    } else {
        $erro = "Erro ao cadastrar escala."; // Caso haja algum erro
    }

    // Insere os serviços
    if (isset($id_escala)) {
        foreach ($_POST['servicos'] as $servico) {
            // Verifica o id_tipo_servico com base no tipo_servico
            $tipo_servico = $servico['tipo_servico'];

            // Busca o id_tipo_servico na tabela Tipo_servicos
            $stmt_tipo_servico = $conn->prepare("SELECT id FROM Tipo_servicos WHERE nome = ?");
            $stmt_tipo_servico->bind_param("s", $tipo_servico);
            $stmt_tipo_servico->execute();
            $result_tipo_servico = $stmt_tipo_servico->get_result();
            $tipo_servico_id = $result_tipo_servico->fetch_assoc()['id'];

            $id_militar = $servico['id_militar'];

            // Insere os dados na tabela servicos usando o id_tipo_servico
            $stmt = $conn->prepare("INSERT INTO servicos (id_escala, id_tipo_servico, id_militar) VALUES (?, ?, ?)");
            $stmt->bind_param("iii", $id_escala, $tipo_servico_id, $id_militar);
            $stmt->execute();
        }
    }

    // Exibir mensagem de sucesso
    if (isset($sucesso)) {
        echo "<script>alert('$sucesso');</script>";
    }

    header("Location: gerenciar_escala.php"); // Redireciona após o salvamento
    exit();
}

// Verifica se um tipo de serviço foi selecionado para filtrar os militares
$militares = [];
if (isset($_POST['tipo_servico'])) {
    $tipo_servico = $_POST['tipo_servico'];

    // Busca os militares que estão associados ao tipo de serviço selecionado
    $stmt = $conn->prepare("
        SELECT m.id, m.nome, m.posto_graduacao 
        FROM militares m
        JOIN servicos s ON m.id = s.id_militar 
        JOIN tipo_servicos ts ON ts.id = s.id_tipo_servico 
        WHERE ts.nome = ?
    ");
    $stmt->bind_param("s", $tipo_servico); // Usando o tipo de serviço selecionado
    $stmt->execute();
    $result = $stmt->get_result();
    $militares = $result->fetch_all(MYSQLI_ASSOC);
} else {
    // Caso não tenha sido selecionado um tipo de serviço, traz todos os militares
    $militares = $conn->query("SELECT id, nome, posto_graduacao FROM militares")->fetch_all(MYSQLI_ASSOC);
}

$tipos_servicos = $conn->query("SELECT id, nome FROM Tipo_servicos");

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Gerenciar Escala</title>
</head>

<body>
    <h2>Gerenciar Escala</h2>
    <form method="POST">
        <label>Data do Serviço:</label>
        <input type="date" name="data_servico" value="<?php echo isset($data_servico) ? $data_servico : ''; ?>" required><br><br>

        <label>Tipo de Escala:</label>
        <select name="tipo_escala" required>
            <option value="Preta" <?php echo isset($tipo_escala) && $tipo_escala == 'Preta' ? 'selected' : ''; ?>>Preta</option>
            <option value="Vermelha" <?php echo isset($tipo_escala) && $tipo_escala == 'Vermelha' ? 'selected' : ''; ?>>Vermelha</option>
        </select><br><br>

        <!-- O responsável será o administrador logado -->
        <input type="hidden" name="id_responsavel" value="<?php echo $_SESSION['militar_id']; ?>">

        <div id="servicos">
            <div class="servico">
                <label>Tipo de Serviço:</label>
                <select name="tipo_servico" required>
                    <?php
                    // Exibe os tipos de serviço disponíveis
                    while ($tipo_servico = $tipos_servicos->fetch_assoc()) {
                        echo "<option value='" . $tipo_servico['nome'] . "'>" . $tipo_servico['nome'] . "</option>";
                    }
                    ?>
                </select>

                <label>Militar:</label>
                <select name="servicos[0][id_militar]" required>
                    <?php
                    // Exibindo todos os militares
                    foreach ($militares as $militar) {
                        echo "<option value='" . $militar['id'] . "'>" . $militar['posto_graduacao'] . " " . $militar['nome'] . "</option>";
                    }
                    ?>
                </select>
            </div>
        </div>
        <button type="button" onclick="adicionarServico()">Adicionar Serviço</button><br><br>
        <button type="submit">Salvar Escala</button>
    </form>

    <script>
        let contadorServicos = 1;

        function adicionarServico() {
            const div = document.createElement('div');
            div.className = 'servico';
            div.innerHTML = `
                <label>Tipo de Serviço:</label>
                <select name="servicos[${contadorServicos}][tipo_servico]" required>
                    <?php
                    // Exibe os tipos de serviço disponíveis novamente
                    $tipos_servicos->data_seek(0); // Reinicia o ponteiro para que os tipos sejam novamente exibidos
                    while ($tipo_servico = $tipos_servicos->fetch_assoc()) {
                        echo "<option value='" . $tipo_servico['nome'] . "'>" . $tipo_servico['nome'] . "</option>";
                    }
                    ?>
                </select>
                <label>Militar:</label>
                <select name="servicos[${contadorServicos}][id_militar]" required>
                    <?php
                    // Exibindo todos os militares
                    foreach ($militares as $militar) {
                        echo "<option value='" . $militar['id'] . "'>" . $militar['posto_graduacao'] . " " . $militar['nome'] . "</option>";
                    }
                    ?>
                </select>
            `;
            document.getElementById('servicos').appendChild(div);
            contadorServicos++;
        }
    </script>
</body>

</html>