<?php
function VpsConnection()
{
    $dbHost = '191.101.18.114'; // IP do VPS aqui
    $dbName = 'Lony_Extra_VPS'; // nome do banco de dados que você criou no VPS
    $dbUser = 'nelioalbasine_sql'; // nome de usuário do banco de dados
    $dbPassword = 'alfa1vsomega2M@'; //senha do banco de dados

    $conn = new mysqli($dbHost, $dbUser, $dbPassword, $dbName);

    if ($conn->connect_error) {
        echo 'Erro ao conectar: ' . $conn->connect_error;
        return null;
    }

    return $conn;
}
