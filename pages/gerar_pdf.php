<?php
require_once('../tcpdf/tcpdf.php'); // Certifique-se de que a biblioteca está instalada
require_once('../includes/conexao.php');

// Se o formulário não foi submetido, exibe a seleção de escalas
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $query = "SELECT id, data_servico FROM escalas ORDER BY data_servico DESC";
    $result = $conn->query($query);
?>
    <!DOCTYPE html>
    <html lang="pt-BR">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Visualizar Escala</title>
        <style>
            body {
                text-align: center;
                font-family: Arial, sans-serif;
            }

            form {
                display: inline-block;
                margin-top: 50px;
            }
        </style>
    </head>

    <body>
        <h2>Visualizar Escala</h2>
        <form action="gerar_pdf.php" method="POST">
            <label><strong>Escalas de quais dias:</strong></label><br>
            <?php while ($row = $result->fetch_assoc()): ?>
                <input type="checkbox" name="escala_id[]" value="<?php echo $row['id']; ?>">
                <?php echo htmlspecialchars($row['data_servico']); ?><br>
            <?php endwhile; ?>
            <br>
            <button type="submit">VISUALIZAR ESCALA</button>
        </form>
    </body>

    </html>
<?php
    exit();
}

// Verifica se escalas foram selecionadas
if (isset($_POST['escala_id']) && is_array($_POST['escala_id'])) {
    $escala_ids = array_map('intval', $_POST['escala_id']);
    $placeholders = implode(',', array_fill(0, count($escala_ids), '?'));

    // Consulta os dados das escalas
    $query = "SELECT * FROM escalas WHERE id IN ($placeholders)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param(str_repeat('i', count($escala_ids)), ...$escala_ids);
    $stmt->execute();
    $result = $stmt->get_result();
    $escalas = $result->fetch_all(MYSQLI_ASSOC);

    if (!$escalas) {
        die("Nenhuma escala encontrada!");
    }

    // Criando PDF
    $pdf = new TCPDF();
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetTitle("Escala de Trabalho");
    $pdf->setPrintHeader(false);
    $pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
    $pdf->SetMargins(15, 20, 15);
    $pdf->SetAutoPageBreak(TRUE, 25);
    $pdf->AddPage(); // Criamos uma única página para todas as escalas

    $html = '<h1 style="text-align: center;">SERVIÇO INTERNO</h1>';

    foreach ($escalas as $escala) {
        // Configura o locale corretamente
        setlocale(LC_TIME, 'pt_BR.UTF-8', 'pt_BR', 'Portuguese_Brazil', 'ptb');

        // Obtém e formata a data corretamente
        $dataObj = new DateTime($escala['data_servico']);
        $dataFormatada = $dataObj->format('d M y (D)');

        // Correção manual para exibir os meses e dias corretamente
        $meses = [
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

        $diasSemana = ['Sun' => 'DOM', 'Mon' => 'SEG', 'Tue' => 'TER', 'Wed' => 'QUA', 'Thu' => 'QUI', 'Fri' => 'SEX', 'Sat' => 'SÁB'];

        // Substitui os valores para PT-BR
        $dataFormatada = str_replace(array_keys($meses), array_values($meses), $dataFormatada);
        $dataFormatada = str_replace(array_keys($diasSemana), array_values($diasSemana), $dataFormatada);

        // Consulta os serviços associados a essa escala, incluindo os nomes dos militares
        $query_servicos = "SELECT s.tipo_servico, m.nome FROM servicos s 
                            JOIN militares m ON s.id_militar = m.id 
                            WHERE s.id_escala = ?";
        $stmt = $conn->prepare($query_servicos);
        $stmt->bind_param("i", $escala['id']);
        $stmt->execute();
        $result_servicos = $stmt->get_result();

        $servicos = [];
        while ($row = $result_servicos->fetch_assoc()) {
            $servicos[$row['tipo_servico']][] = $row['nome'];
        }

        // Adicionando título e dados de cada escala na mesma página
        $html .= '<h3><strong>DIA ' . mb_strtoupper($dataFormatada, 'UTF-8') . '</strong></h3>';
        $html .= '<table border="1" cellpadding="5" cellspacing="0" width="100%">';
        foreach ($servicos as $tipo_servico => $militares) {
            $html .= '<tr><td><strong>' . htmlspecialchars(mb_strtoupper(str_replace('_', ' ', $tipo_servico), 'UTF-8')) . '</strong></td>';
            $html .= '<td>' . htmlspecialchars(implode(', ', $militares)) . '</td></tr>';
        }
        $html .= '</table><br><br>';
    }

    $pdf->writeHTML($html, true, false, true, false, '');

    // Exibir o PDF na tela antes de baixar
    $pdf->Output('escala.pdf', 'I'); // 'I' para visualizar no navegador
    exit();
} else {
    echo "Nenhuma escala selecionada.";
}
