<?php
function Wamp64Connection()
{
    $dbHost = 'localhost';
    $dbName = "qyagfite_Lony_Extra";
    $dbUser = 'qyagfite_Nelio_Albasine';
    $dbPassword = 'alfa1vsomega2M@';

    $conn = new mysqli($dbHost, $dbUser, $dbPassword, $dbName);

    if ($conn->connect_error) {
        echo 'Erro ao conectar: ' . $conn->connect_error;
        return null;
    }

    return $conn;
}

