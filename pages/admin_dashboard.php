<?php
session_start();
require_once('../includes/conexao.php');

// Se o usuário não for administrador, redireciona
if (!isset($_SESSION['militar_id']) || $_SESSION['tipo'] != 'admin') {
    header("Location: dashboard.php");
    exit();
}

// Função para calcular as folgas (preta ou vermelha)
function calcularFolgas($data_servico, $tipo_escala)
{
    $hoje = date('Y-m-d'); // Data atual

    if ($data_servico) {
        $data_servico = strtotime($data_servico);
        $hoje = strtotime($hoje);

        // Calculando a folga (tanto para preta quanto vermelha)
        $dias_folga = round(($hoje - $data_servico) / 86400);

        return $dias_folga;
    }

    return 0;
}

// Consulta para pegar as folgas de todos os militares
$query = "
    SELECT 
        m.nome, 
        s.tipo_servico, 
        e.data_servico, 
        e.tipo_escala, 
        f.folga_preta, 
        f.folga_vermelha
    FROM militares m
    LEFT JOIN servicos s ON m.id = s.id_militar
    LEFT JOIN escalas e ON s.id_escala = e.id
    LEFT JOIN folgas f ON m.id = f.id_militar
    ORDER BY s.tipo_servico, m.nome
";

$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Folgas de Todos os Militares</title>
    <link rel="stylesheet" href="../assets/css/admin_dashboard.css">
</head>

<body>
    <div class="header">
        <h1>Folgas de Todos os Militares</h1>
    </div>

    <!-- Estrutura da Tabela -->
    <table>
        <thead>
            <tr>
                <th>Permanência</th>
                <th>AUX Permanência</th>
                <th>CB Garagem DGP</th>
                <th>SD Garagem DGP</th>
                <th>Responsável pelos Banheiros</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Inicializar os dados por tipo de serviço
            $tipos_servico = ['Permanência', 'Aux Permanência', 'CB Garagem DGP', 'SD Garagem DGP', 'Responsável pelos Banheiros'];
            $militares_por_servico = [
                'Permanência' => [],
                'Aux Permanência' => [],
                'CB Garagem DGP' => [],
                'SD Garagem DGP' => [],
                'Responsável pelos Banheiros' => []
            ];

            // Organizar os militares nos arrays corretos de acordo com o tipo de serviço
            while ($row = $result->fetch_assoc()) {
                $dias_folga = calcularFolgas($row['data_servico'], $row['tipo_escala']);
                $tipo_servico = $row['tipo_servico'];
                if (array_key_exists($tipo_servico, $militares_por_servico)) {
                    $militares_por_servico[$tipo_servico][] = [
                        'nome' => $row['nome'],
                        'dias_folga' => $dias_folga,
                        'tipo_escala' => $row['tipo_escala']
                    ];
                }
            }

            // Exibir as colunas conforme os tipos de serviço
            foreach ($tipos_servico as $tipo) {
                echo "<tr>";
                echo "<td><strong>$tipo</strong></td>";

                // Preencher cada coluna de serviço com os militares e suas folgas
                foreach ($militares_por_servico[$tipo] as $militar) {
                    $dias_folga = $militar['dias_folga'];
                    echo "<td>";
                    echo $militar['nome'] . " - $dias_folga dias";
                    echo "</td>";
                }

                echo "</tr>";
            }
            ?>
        </tbody>
    </table>

    <div class="footer">
        <a href="dashboard.php" class="back-btn">Voltar ao painel</a>
    </div>
</body>

</html>