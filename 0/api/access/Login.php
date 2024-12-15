<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/login_errors.log');

// Definindo os cabeçalhos CORS diretamente 
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

header('Content-Type: application/json; charset=utf-8');

require_once "../Wamp64Connection.php";

if ($_SERVER['REQUEST_METHOD'] == "GET") {
    $conn = Wamp64Connection();

    createTableUserIfNotExists($conn);

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
    $userId = null;

    require_once "IsUserRegistered.php";

    if (IsUserRegistered($email, $conn)) {
        if (authenticateUser($email, $password, $conn, $userId)) {
            $output = [
                "success" => true,
                "message" => "Login feito com sucesso!",
                "redirectTo" => "http://127.0.0.1:5500/0/dashboard/",
                "userId" => $userId
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

   // error_log("Output de login: " . print_r($output, true));
    
    echo json_encode($output);
    $conn->close();
} else {
    error_log("A requisição não é GET");
}
function authenticateUser($email, $passwordInput, $conn, &$userId): bool
{
    $passwordFromDatabase = null;

    $query = "SELECT userPassword, userId FROM Usuarios WHERE userEmail = ?";
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        error_log("Erro ao preparar a consulta: " . $conn->error);
        return false;
    }

    $stmt->bind_param("s", $email);
    if (!$stmt->execute()) {
        error_log("Erro ao executar a consulta: " . $stmt->error);
        $stmt->close();
        return false;
    }

    $stmt->bind_result($passwordFromDatabase, $userId);
    $stmt->fetch();
    $stmt->close();

    if (!$passwordFromDatabase) {
        return false;
    }

    return password_verify($passwordInput, $passwordFromDatabase);
}

function createTableUserIfNotExists($conn)
{
    $queryCreateUser = "CREATE TABLE IF NOT EXISTS Usuarios (
        id INT AUTO_INCREMENT PRIMARY KEY,
        userId VARCHAR(191) NOT NULL UNIQUE,
        userName VARCHAR(15) NOT NULL,
        userSurname VARCHAR(20) NOT NULL,
        userEmail VARCHAR(100) NOT NULL UNIQUE,
        userPassword VARCHAR(255),
        userTimeZone VARCHAR(50),
        myReferralCode VARCHAR(6),
        userGender VARCHAR(10) NOT NULL,
        userAge INT,
        userJoinedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        userPointsJSON JSON,
        userInvitationJSON JSON
    )";

    if (!mysqli_query($conn, $queryCreateUser)) {
        die("Erro ao criar a tabela: " . mysqli_error($conn));
    }
}
