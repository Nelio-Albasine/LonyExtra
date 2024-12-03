<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('error_log', __DIR__ . '/../Logs/login_errors.log');

// Definindo os cabeçalhos CORS diretamente 
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

header('Content-Type: application/json; charset=utf-8');

require_once "../Wamp64Connection.php";

if ($_SERVER['REQUEST_METHOD'] == "GET") {
    $conn = getMySQLConnection();

    $output = [
        "success" => null,
        "message" => null,
        "redirectTo" => null
    ];

    if (!$conn) {
        error_log("Erro ao conectar ao banco de dados");
        $output = [
            "success" => false,
            "message" => "Erro interno no servidor."
        ];
        echo json_encode($output);
        exit;
    }

    $email = $_GET["email"];
    $password = $_GET["password"];

    require_once "IsUserRegistered.php";

    if (IsUserRegistered($email, $conn)) {
        if (authenticateUser($email, $password, $conn)) {
            $output = [
                "success" => true,
                "message" => "Login feito com sucesso!",
                "redirectTo" => "http://127.0.0.1:5500/0/dashboard/",
            ];
        } else {
            $output = [
                "success" => false,
                "message" => "E-mail ou senha incorreto!",
            ];
        }
    } else {
        $output = [
            "success" => false,
            "message" => "Usuário não registrado!",
        ];
    }

    //FOR TESTs
    error_log(print_r($output, true));

    echo json_encode($output);
    $conn->close();
} else {
    error_log("A requisição não é GET");
}

function authenticateUser($email, $passwordInput, $conn): bool
{
    $passwordFromDatabase = null;

    $query = "SELECT userPassword FROM Usuarios WHERE userEmail = ?";
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        error_log("Erro ao preparar a consulta: " . $conn->error);
        return false;
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($passwordFromDatabase);
    $stmt->fetch();
    $stmt->close();

    return $passwordFromDatabase && password_verify($passwordInput, $passwordFromDatabase);
}
