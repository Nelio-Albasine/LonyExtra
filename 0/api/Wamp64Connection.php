<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');

function getMySQLConnection()
{
    $dbHost = 'localhost';
    $dbName = "lonyextra";
    $dbUser = 'root';
    $dbPassword = '';

    $conn = new mysqli($dbHost, $dbUser, $dbPassword, $dbName);

    if ($conn->connect_error) {
        echo 'Erro ao conectar: ' . $conn->connect_error;
        return null;
    }

    return $conn;
}
