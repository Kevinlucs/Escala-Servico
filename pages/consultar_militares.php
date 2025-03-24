<?php

require_once('../includes/conexao.php');

// Verifica se o tipo de serviço foi enviado
if (isset($_GET['tipo_servico'])) {
    $tipo_servico = $_GET['tipo_servico'];

    // Busca os militares associados ao tipo de serviço e exclui os administradores
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

    $militares = [];
    while ($row = $result->fetch_assoc()) {
        $militares[] = $row;
    }

    echo json_encode($militares);
}
