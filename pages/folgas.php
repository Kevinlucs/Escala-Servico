<?php
// Incluir o arquivo de verificação de sessão
require_once('../backend/session.php');

// Agora, a sessão está validada e você pode prosseguir com o restante do código da página
require_once('../includes/conexao.php');

// Consulta para obter os dados da folga e o último serviço
$query = "
    SELECT f.data_ultimo_servico, f.dias_folga, e.tipo_escala
    FROM folgas f
    JOIN escalas e ON f.id_militar = e.id_responsavel
    WHERE f.id_militar = ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $_SESSION['militar_id']);
$stmt->execute();
$result = $stmt->get_result();
$folga = $result->fetch_assoc();


$data_ultimo_servico = $folga ? $folga['data_ultimo_servico'] : null;
$hoje = date('Y-m-d');

// Calcular os dias de folga na escala preta
$folga_preta = $data_ultimo_servico ? (strtotime($hoje) - strtotime($data_ultimo_servico)) / 86400 : 0;

// Verifica se há um feriado na data atual
$query_feriados = "SELECT COUNT(*) as total FROM feriados WHERE data = ?";
$stmt = $conn->prepare($query_feriados);
$stmt->bind_param("s", $hoje);
$stmt->execute();
$result = $stmt->get_result();
$feriado = $result->fetch_assoc();
$eh_feriado = $feriado['total'] > 0;

// Determina se hoje é fim de semana (Sábado ou Domingo)
$dia_semana = date('N', strtotime($hoje)); // 6 = Sábado, 7 = Domingo
$escala_vermelha = ($dia_semana >= 6 || $eh_feriado) ? true : false;

// Calcula a folga vermelha apenas se for fim de semana ou feriado
$folga_vermelha = $escala_vermelha ? $folga_preta : 0;
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minhas Folgas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin: 40px;
        }

        table {
            width: 50%;
            margin: auto;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid black;
            padding: 10px;
        }
    </style>
</head>

<body>
    <h2>Minhas Folgas</h2>
    <table>
        <tr>
            <th>Data do Último Serviço</th>
            <th>Folga Preta</th>
            <th>Folga Vermelha</th>
        </tr>
        <tr>
            <td><?= $data_ultimo_servico ? date('d/m/Y', strtotime($data_ultimo_servico)) : 'N/A' ?></td>
            <td><?= $folga_preta ?> dias</td>
            <td><?= $folga_vermelha ?> dias</td>
        </tr>
    </table>
</body>

</html>