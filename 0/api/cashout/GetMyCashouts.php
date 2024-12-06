<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/GetMyCashouts.log');

require_once "../Wamp64Connection.php";
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$conn = Wamp64Connection();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $userId = $_GET["userId"];

    createTableSaquesIfNotExists($conn);

    $response = getMyAllMyCashouts($conn, $userId);

    error_log("Resposta do cashout: ". print_r($response, true));

    echo json_encode($response);
    $conn->close();
    exit;
} else {
    http_response_code(405);
    echo json_encode(["error" => "Método não permitido"]);
}

function getMyAllMyCashouts($conn, $userId): array
{
    $query = "SELECT 
        gatewayName, cashOutId, 
        amountCashedOut, cashOutStatus, 
        userPaymentName, userPaymentAddress, created_at 
        FROM Saques 
        WHERE userId = ? 
        ORDER BY created_at DESC";

    $stmt = $conn->prepare($query);

    if (!$stmt) {
        error_log("Erro ao preparar a consulta: " . $conn->error);
        return ["error" => "Erro interno no servidor"];
    }

    $stmt->bind_param("i", $userId);

    if (!$stmt->execute()) {
        error_log("Erro ao executar a consulta: " . $stmt->error);
        return ["error" => "Erro interno no servidor"];
    }

    $result = $stmt->get_result();
    $cashouts = [];

    while ($row = $result->fetch_assoc()) {
        $cashouts[] = $row;
    }

    $stmt->close();
    return $cashouts;
}


function createTableSaquesIfNotExists($conn)
{
    $query = "
        CREATE TABLE IF NOT EXISTS Saques (
            id INT AUTO_INCREMENT PRIMARY KEY,
            userId VARCHAR(191) NOT NULL,
            gatewayName VARCHAR(10) NOT NULL,
            cashOutId VARCHAR(6) UNIQUE NOT NULL,
            amountCashedOut DECIMAL(10,2),
            cashOutStatus INT(1),
            userPaymentName VARCHAR(27) NOT NULL,
            userPaymentAddress VARCHAR(45) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
    ";
    if (!mysqli_query($conn, $query)) {
        die("Erro ao criar a tabela: " . mysqli_error($conn));
    }
}