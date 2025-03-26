<?php

require_once('../backend/session.php');
require_once('../includes/conexao.php');


setlocale(LC_TIME, 'pt_BR.utf8', 'pt_BR', 'Portuguese_Brazil');

// Função para remover acentos
function removerAcentos($texto)
{
    return iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texto);
}

// Busca as escalas, agora com o JOIN na tabela Tipo_servicos e a coluna posto_graduacao
$escalas = $conn->query("
    SELECT esc.id, esc.data_servico, esc.tipo_escala, ts.nome AS tipo_servico, m.nome AS militar, m.posto_graduacao
    FROM escalas esc
    JOIN servicos s ON esc.id = s.id_escala
    JOIN militares m ON s.id_militar = m.id
    JOIN Tipo_servicos ts ON s.id_tipo_servico = ts.id
    ORDER BY esc.data_servico, s.id
");

if (!$escalas) {
    die("Erro na consulta: " . $conn->error);
}

// Organiza os dados em um array
$escalas_organizadas = [];
while ($escala = $escalas->fetch_assoc()) {
    $data_servico = $escala['data_servico'];
    $tipo_servico = $escala['tipo_servico'];
    $militar = $escala['posto_graduacao'] . " " . $escala['militar']; // Concatenando posto e nome do militar

    if (!isset($escalas_organizadas[$data_servico])) {
        $escalas_organizadas[$data_servico] = [];
    }

    if (!isset($escalas_organizadas[$data_servico][$tipo_servico])) {
        $escalas_organizadas[$data_servico][$tipo_servico] = [];
    }

    // Adicionando os militares ao tipo de serviço correspondente
    $escalas_organizadas[$data_servico][$tipo_servico][] = $militar;
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Visualizar Escala</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        h2 {
            text-align: center;
        }

        h3 {
            font-weight: bold;
            margin-bottom: 5px;
            text-align: left;
            width: 50%;
            margin-left: auto;
            margin-right: auto;
        }

        table {
            width: 50%;
            border-collapse: collapse;
            margin-bottom: 20px;
            margin-left: auto;
            margin-right: auto;
        }

        table,
        th,
        td {
            border: 1px solid black;
        }

        th,
        td {
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        .signature {
            margin-top: 20px;
            text-align: right;
        }
    </style>
</head>

<body>
    <h2><?php echo removerAcentos('SERVIÇO INTERNO'); ?></h2>
    <?php foreach ($escalas_organizadas as $data_servico => $servicos) { ?>
        <h3>
            <?php
            setlocale(LC_TIME, 'ptb', 'portuguese', 'pt_BR.utf8', 'pt_BR', 'Portuguese_Brazil'); // Garante compatibilidade

            $traducao_dias = [
                'Sunday'    => 'DOM',
                'Monday'    => 'SEG',
                'Tuesday'   => 'TER',
                'Wednesday' => 'QUA',
                'Thursday'  => 'QUI',
                'Friday'    => 'SEX',
                'Saturday'  => 'SAB'
            ];

            $traducao_meses = [
                'Jan' => 'JAN',
                'Feb' => 'FEV',
                'Mar' => 'MAR',
                'Apr' => 'ABR',
                'May' => 'MAI',
                'Jun' => 'JUN',
                'Jul' => 'JUL',
                'Aug' => 'AGO',
                'Sep' => 'SET',
                'Oct' => 'OUT',
                'Nov' => 'NOV',
                'Dec' => 'DEZ'
            ];

            $data_servico_formatada = date('d M y', strtotime($data_servico));
            $mes_ingles = date('M', strtotime($data_servico));
            $mes_portugues = $traducao_meses[$mes_ingles] ?? $mes_ingles; // Traduz o mês

            $dia_semana = date('l', strtotime($data_servico));
            $dia_semana_traduzido = $traducao_dias[$dia_semana] ?? ''; // Traduz para português

            // Substitui o mês no formato final
            $data_formatada = str_replace($mes_ingles, $mes_portugues, $data_servico_formatada);

            echo 'DIA ' . $data_formatada . ' (' . $dia_semana_traduzido . ')';
            ?>
        </h3>

        <table>
            <?php foreach ($servicos as $tipo_servico => $militares) { ?>
                <tr>
                    <td><?php echo $tipo_servico; ?></td>
                    <td>
                        <?php
                        if (empty($militares)) {
                            echo 'Militar de outra OM';
                        } else {
                            echo implode(' e ', $militares);
                        }
                        ?>
                    </td>
                </tr>
            <?php } ?>
        </table>
    <?php } ?>

    <div class="signature">
    </div>
</body>

</html>