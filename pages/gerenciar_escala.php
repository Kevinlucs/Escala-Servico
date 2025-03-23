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

    // Insere os serviços para os militares
    if (isset($id_escala)) {
        foreach ($_POST['servicos'] as $servico) {
            // Verifique se o id_militar está sendo enviado corretamente
            $tipo_servico = $servico['tipo_servico'];
            $id_militar = $servico['id_militar'];

            // Verifique se $id_militar e $tipo_servico estão sendo recebidos corretamente
            echo "Tipo de Serviço: " . $tipo_servico . "<br>";
            echo "Id do Militar: " . $id_militar . "<br>";

            // Busca o id_tipo_servico na tabela Tipo_servicos
            $stmt_tipo_servico = $conn->prepare("SELECT id FROM Tipo_servicos WHERE nome = ?");
            $stmt_tipo_servico->bind_param("s", $tipo_servico);
            $stmt_tipo_servico->execute();
            $result_tipo_servico = $stmt_tipo_servico->get_result();
            $tipo_servico_id = $result_tipo_servico->fetch_assoc()['id'];

            // Insere os dados na tabela servicos
            $stmt_servicos = $conn->prepare("INSERT INTO servicos (id_escala, id_tipo_servico, id_militar) VALUES (?, ?, ?)");
            $stmt_servicos->bind_param("iii", $id_escala, $tipo_servico_id, $id_militar);
            $stmt_servicos->execute();
        }
    }


    // Exibir mensagem de sucesso
    if (isset($sucesso)) {
        echo "<script>alert('$sucesso');</script>";
    }

    header("Location: gerenciar_escala.php"); // Redireciona após o salvamento
    exit();
}

// Verifica os tipos de serviço disponíveis
$tipos_servicos = $conn->query("SELECT id, nome FROM Tipo_servicos");
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Gerenciar Escala</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
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

        <input type="hidden" name="id_responsavel" value="<?php echo $_SESSION['militar_id']; ?>">

        <div id="servicos">
            <div class="servico">
                <label>Tipo de Serviço:</label>
                <select name="tipo_servico" class="tipo_servico" required>
                    <?php
                    // Exibe os tipos de serviço disponíveis
                    while ($tipo_servico = $tipos_servicos->fetch_assoc()) {
                        echo "<option value='" . $tipo_servico['nome'] . "'>" . $tipo_servico['nome'] . "</option>";
                    }
                    ?>
                </select>

                <label>Militar:</label>
                <select name="servicos[0][id_militar]" class="militar_select" required>
                    <!-- Inicialmente, será preenchido via AJAX -->
                </select>
            </div>
        </div>

        <button type="button" onclick="adicionarServico()">Adicionar Serviço</button><br><br>
        <button type="submit">Salvar Escala</button>
    </form>

    <script>
        $(document).on('change', '.tipo_servico', function() {
            var tipo_servico = $(this).val(); // Pega o valor do tipo de serviço selecionado
            var select_militar = $(this).closest('.servico').find('.militar_select'); // Encontra o campo de militares

            // Realiza a requisição AJAX para o arquivo consultar_militares.php
            $.ajax({
                url: "consultar_militares.php", // Caminho para o arquivo PHP
                type: "GET",
                data: {
                    tipo_servico: tipo_servico
                }, // Envia o tipo de serviço
                dataType: "json", // Espera receber os dados em formato JSON
                success: function(data) {
                    // Preenche o campo de seleção de militares com os dados retornados
                    var options = "<option value=''>Selecione um militar</option>"; // Primeira opção
                    $.each(data, function(index, militar) {
                        options += "<option value='" + militar.id + "'>" + militar.posto_graduacao + " " + militar.nome + "</option>";
                    });
                    select_militar.html(options); // Atualiza as opções no select de militares
                }
            });
        });

        // Ao carregar a página, se já houver valor selecionado, dispara a mudança para carregar os militares
        $(document).ready(function() {
            $(".tipo_servico").trigger("change");
        });

        let contadorServicos = 1;

        function adicionarServico() {
            const div = document.createElement('div');
            div.className = 'servico';
            div.innerHTML = ` 
        <label>Tipo de Serviço:</label>
        <select name="servicos[${contadorServicos}][tipo_servico]" class="tipo_servico" required>
            <?php
            // Exibe os tipos de serviço disponíveis novamente
            $tipos_servicos->data_seek(0); // Reinicia o ponteiro para que os tipos sejam novamente exibidos
            while ($tipo_servico = $tipos_servicos->fetch_assoc()) {
                echo "<option value='" . $tipo_servico['nome'] . "'>" . $tipo_servico['nome'] . "</option>";
            }
            ?>
        </select>
        <label>Militar:</label>
        <select name="servicos[${contadorServicos}][id_militar]" class="militar_select" required>
            <!-- Inicialmente, será preenchido via AJAX -->
        </select>
    `;
            document.getElementById('servicos').appendChild(div);
            contadorServicos++;

            // Dispara a requisição AJAX para preencher o campo de militares após adicionar um novo serviço
            $(".tipo_servico").last().trigger("change");
        }
    </script>
</body>

</html>