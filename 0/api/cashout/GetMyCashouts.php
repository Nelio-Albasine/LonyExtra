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

$conn = getMySQLConnection();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $userId = $_GET["userId"];

    $response = getMyAllMyCashouts($conn, $userId);

    echo json_encode($response);
} else {
    http_response_code(405);
    echo json_encode(["error" => "Método não permitido"]);
}

function getMyAllMyCashouts($conn, $userId): array {
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

