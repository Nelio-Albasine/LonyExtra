<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/GetTop10Users.log');

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once "../Wamp64Connection.php";

$conn = getMySQLConnection();

try {
    $topUsers = getTop10Users($conn);
    header('Content-Type: application/json');
    echo json_encode([
        "success" => true,
        "data" => $topUsers
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Erro ao buscar os usuários: " . $e->getMessage()
    ]);
}


function getTop10Users($conn) {
    $query = "
        SELECT 
            userName,
            userSurname,
            JSON_UNQUOTE(JSON_EXTRACT(userPointsJSON, '$.userLTStars')) AS userLTStars
        FROM Usuarios
        ORDER BY CAST(JSON_UNQUOTE(JSON_EXTRACT(userPointsJSON, '$.userLTStars')) AS UNSIGNED) DESC
        LIMIT 10
    ";

    $result = $conn->query($query);

    if (!$result) {
        throw new Exception("Erro ao executar a consulta: " . $conn->error);
    }

    $top10Users = [];
    while ($row = $result->fetch_assoc()) {
        $top10Users[] = [
            "userName" => $row["userName"] ?? "N/A",
            "userSurname" => $row["userSurname"] ?? "N/A",
            "userLTStars" => (int)($row["userLTStars"] ?? 0)
        ];
    }

    return $top10Users;
}
