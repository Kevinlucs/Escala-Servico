<?php
// Incluir o arquivo de verificação de sessão
require_once('../backend/session.php'); // Caminho para o arquivo session.php
require_once('../includes/conexao.php'); // Para realizar a conexão com o banco de dados

// Verifica se o administrador está acessando a página
if ($_SESSION['tipo'] != 'admin') {
    header("Location: index.php");
    exit();
}

// Verifica se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Coleta os dados do formulário
    $identidade_militar = $_POST['identidade_militar'];
    $posto_graducao = $_POST['posto_graducao'];
    $nome = $_POST['nome'];
    $servicos = $_POST['servicos']; // Array de IDs de serviços
    $secao = $_POST['secao'];
    $funcao = $_POST['funcao'];

    // Verificar se a identidade militar já existe
    $stmt_check = $conn->prepare("SELECT * FROM militares WHERE identidade_militar = ?");
    $stmt_check->bind_param("s", $identidade_militar);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        // Se a identidade já existe, exibe uma mensagem de erro
        echo "Erro: Identidade Militar já cadastrada.";
    } else {
        // Insere o militar na tabela militares
        $stmt_militar = $conn->prepare("INSERT INTO militares (identidade_militar, posto_graducao, nome, secao) VALUES (?, ?, ?, ?)");
        $stmt_militar->bind_param("ssss", $identidade_militar, $posto_graducao, $nome, $secao);
        $stmt_militar->execute();
        $militar_id = $stmt_militar->insert_id;

        // Insere o militar na tabela usuarios com a senha inicial como a identidade
        $senha_inicial = password_hash($identidade_militar, PASSWORD_DEFAULT); // Hashea a senha
        $primeiro_acesso = 1; // Marca como primeiro acesso

        // Insere o militar na tabela usuarios
        $stmt_usuario = $conn->prepare("INSERT INTO usuarios (identidade_militar, senha, tipo, primeiro_acesso) VALUES (?, ?, ?, ?)");
        $stmt_usuario->bind_param("ssss", $identidade_militar, $senha_inicial, $funcao, $primeiro_acesso);
        $stmt_usuario->execute();
        $usuario_id = $stmt_usuario->insert_id;

        // Associa os serviços permitidos ao militar
        foreach ($servicos as $servico_id) {
            $stmt_servicos = $conn->prepare("INSERT INTO servicos (id_militar, id_tipo_servico) VALUES (?, ?)");
            $stmt_servicos->bind_param("ii", $militar_id, $servico_id);
            $stmt_servicos->execute();
        }

        echo "Militar cadastrado com sucesso!";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Cadastrar Militar</title>
</head>

<body>
    <h2>Cadastrar Novo Militar</h2>
    <form method="POST">
        <label>Identidade Militar (10 dígitos):</label>
        <input type="text" name="identidade_militar" maxlength="10" required><br><br>

        <label>Posto Graduação:</label>
        <select name="posto_graducao" required>
            <option value="ST">ST</option>
            <option value="CB">CB</option>
            <option value="SD">SD</option>
        </select><br><br>

        <label>Nome:</label>
        <input type="text" name="nome" placeholder="Somente o nome de guerra" required><br><br>

        <label>Serviços:</label><br>
        <select name="servicos[]" multiple required>
            <?php
            // Consulta para listar os tipos de serviço disponíveis
            $result_servicos = $conn->query("SELECT id, nome FROM Tipo_servicos");
            while ($servico = $result_servicos->fetch_assoc()) {
                echo "<option value='" . $servico['id'] . "'>" . $servico['nome'] . "</option>";
            }
            ?>
        </select><br><br>

        <label>Seção:</label>
        <select name="secao" required>
            <option value="1ª Seção">1ª Seção</option>
            <option value="2ª Seção">2ª Seção</option>
            <option value="3ª Seção">3ª Seção</option>
            <option value="4ª Seção">4ª Seção</option>
            <option value="AJ GERAL">AJ GERAL</option>
            <option value="APG">APG</option>
            <option value="SEC INFO">SEC INFO</option>
            <option value="JURÍDICA">JURÍDICA</option>
        </select><br><br>

        <label>Função:</label>
        <select name="funcao" required>
            <option value="admin">Administrador</option>
            <option value="usuario">Usuário</option>
        </select><br><br>

        <button type="submit">Cadastrar</button>
    </form>
</body>

</html>