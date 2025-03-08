<?php
session_start();
require_once('../includes/conexao.php');

if (!isset($_SESSION['militar_id']) || $_SESSION['tipo'] != 'admin') {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
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
        $tipo_servico = isset($servico['tipo_servico']) && !empty($servico['tipo_servico']) ? $servico['tipo_servico'] : 'Permanência';
        $id_militar = $servico['id_militar'];

        $stmt = $conn->prepare("INSERT INTO servicos (id_escala, tipo_servico, id_militar) VALUES (?, ?, ?)");
        $stmt->bind_param("isi", $id_escala, $tipo_servico, $id_militar);
        $stmt->execute();
    }

    $_SESSION['mensagem'] = "Escala cadastrada com sucesso!";
    header("Location: gerenciar_escala.php");
    exit();
}


// Busca os militares no banco
$militares = $conn->query("SELECT id, nome FROM militares");
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
                <select name="tipo_servico" required>
                    <option value="Permanência">Permanência</option>
                    <option value="Aux Permanência">Aux Permanência</option>
                    <option value="CB Garagem DGP">Cb Garagem DGP</option>
                    <option value="SD Garagem DGP">Sd Garagem DGP</option>
                    <option value="Responsável pelos Banheiros">Responsável pelos Banheiros</option>
                </select>

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
                    <option value="Permanência">Permanência</option>
                    <option value="Aux Permanência">Aux Permanência</option>
                    <option value="Garagem DGP">Garagem DGP</option>
                    <option value="Missão">Missão</option>
                    <option value="Responsável pelos banheiros">Responsável pelos banheiros</option>
                </select>
                <label>Militar:</label>
                <select name="servicos[${contadorServicos}][id_militar]" required>
                    <?php
                    $militares->data_seek(0);
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