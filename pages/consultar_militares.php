<?php
require_once('../includes/conexao.php');

// Recebe o tipo de serviço via GET (AJAX)
if (isset($_GET['tipo_servico'])) {
    $tipo_servico = $_GET['tipo_servico'];

    // Busca os militares que podem pegar o serviço selecionado e não são admins
    $stmt = $conn->prepare("
        SELECT m.id, m.nome, m.posto_graduacao 
        FROM militares m
        JOIN servicos s ON m.id = s.id_militar
        JOIN tipo_servicos ts ON ts.id = s.id_tipo_servico
        JOIN usuarios u ON u.identidade_militar = m.identidade_militar
        WHERE ts.nome = ? AND u.tipo != 'admin'
    ");
    $stmt->bind_param("s", $tipo_servico);
    $stmt->execute();
    $result = $stmt->get_result();

    // Prepara a resposta com os militares
    $militares = [];
    while ($militar = $result->fetch_assoc()) {
        $militares[] = $militar;
    }

    // Retorna os militares em formato JSON
    echo json_encode($militares);
}
