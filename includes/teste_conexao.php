<?php
include 'conexao.php';

if ($conn) {
    echo "Conexão com o banco de dados estabelecida com sucesso!";
} else {
    echo "Erro ao conectar ao banco de dados.";
}
?>