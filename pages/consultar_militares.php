<?php

require_once('../includes/conexao.php');

// Obtemos o tipo de serviço e a data do serviço para filtrar os militares
$tipo_servico = $_GET['tipo_servico'];
$data_servico = $_GET['data_servico'];

// Verifica os militares disponíveis para o tipo de serviço, excluindo os militares já associados a uma escala na data e que são administradores
$query = "
    SELECT m.id, m.nome, m.posto_graduacao
    FROM militares m
    JOIN usuarios u ON u.identidade_militar = m.identidade_militar
    WHERE m.id NOT IN (
        SELECT s.id_militar 
        FROM servicos s
        JOIN escalas e ON e.id = s.id_escala
        WHERE e.data_servico = ?  -- Filtra pela data do serviço
    )
    AND m.id IN (
        SELECT m.id 
        FROM militares m
        JOIN servicos s ON s.id_militar = m.id
        JOIN tipo_servicos ts ON ts.id = s.id_tipo_servico
        WHERE ts.nome = ?
    )
    AND u.tipo != 'admin'  -- Exclui os militares com tipo de usuário 'admin'
";

$stmt = $conn->prepare($query);
$stmt->bind_param("ss", $data_servico, $tipo_servico); // Passamos a data do serviço e tipo_servico
$stmt->execute();
$result = $stmt->get_result();

// Armazena os militares em um array
$militares = [];
while ($row = $result->fetch_assoc()) {
    $militares[] = $row;
}

// Retorna os dados como JSON
echo json_encode($militares);
