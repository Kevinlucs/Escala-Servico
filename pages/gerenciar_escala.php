<?php
// Incluir o arquivo de verificação de sessão
require_once('../backend/session.php'); // Caminho ajustado para o arquivo session.php

require_once('../includes/conexao.php');

// Verifica se o usuário está logado e se é um administrador
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Lógica para inserir a escala no banco de dados
    $data_servico = $_POST['data_servico'];
    $tipo_escala = $_POST['tipo_escala'];
    $id_responsavel = $_POST['id_responsavel'];

    // Insere a escala
    $stmt = $conn->prepare("INSERT INTO escalas (data_servico, tipo_escala, id_responsavel) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $data_servico, $tipo_escala, $id_responsavel);
    $stmt->execute();
    $id_escala = $stmt->insert_id;

    // Insere os serviços
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

    $_SESSION['mensagem'] = "Escala cadastrada com sucesso!";
    header("Location: gerenciar_escala.php");
    exit();
}

// Busca os militares no banco
$militares = $conn->query("SELECT id, nome FROM militares");

// Busca os tipos de serviço disponíveis
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
        <input type="date" name="data_servico" required><br><br>

        <label>Tipo de Escala:</label>
        <select name="tipo_escala" required>
            <option value="Preta">Preta</option>
            <option value="Vermelha">Vermelha</option>
        </select><br><br>

        <!-- O responsável será o administrador logado -->
        <input type="hidden" name="id_responsavel" value="<?php echo $_SESSION['militar_id']; ?>">

        <div id="servicos">
            <div class="servico">
                <label>Tipo de Serviço:</label>
                <select name="servicos[0][tipo_servico]" required>
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
                    // Excluindo o responsável da lista de militares
                    while ($militar = $militares->fetch_assoc()) {
                        if ($militar['id'] != $_SESSION['militar_id']) {
                            echo "<option value='" . $militar['id'] . "'>" . $militar['nome'] . "</option>";
                        }
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
                    // Excluindo o responsável da lista de militares
                    $militares->data_seek(0); // Reinicia o ponteiro
                    while ($militar = $militares->fetch_assoc()) { ?>
                        <option value="<?php echo $militar['id']; ?>"><?php echo $militar['nome']; ?></option>
                    <?php } ?>
                </select>
            `;
            document.getElementById('servicos').appendChild(div);
            contadorServicos++;
        }
    </script>
</body>

</html>