<?php
$host = "127.0.0.1";
$usuario = "root";
$senha = "admin1234";
$banco = "escala_servico";

$conn = new mysqli($host, $usuario, $senha, $banco);

if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

// Definir charset para utf8
$conn->set_charset("utf8mb4");