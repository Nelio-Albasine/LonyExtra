<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('error_log', __DIR__ . '../Logs/get_dash_info.log');

// Definindo os cabeçalhos CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

header('Content-Type: application/json; charset=utf-8');

require_once "../Wamp64Connection.php";

if ($_SERVER['REQUEST_METHOD'] == "GET") {
    $conn = getMySQLConnection();

    if (isset($_GET['userId'])) {
        $userId = $_GET['userId'];

        $userInfo = getDashboardInfo($conn, $userId);

        require_once "LinkAvailabilityChecker.php";

        $hasValidLinksPerBatch = hasValidLinksPerBatch($userId);

        $output = [
            'success' => $userInfo['success'],
            'userInfo' => $userInfo['data'] ?? [],
            'hasValidLinksPerBatch' => json_decode($hasValidLinksPerBatch) ?? []
        ];
    } else {
        $output = [
            'success' => false,
            'message' => "Parâmetro 'userId' é obrigatório."
        ];
    }

    echo json_encode($output);
    $conn->close();
} else {
    error_log("A requisição não é GET");
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Método não permitido"]);
    exit;
}


// Função para obter as informações do dashboard do usuário
function getDashboardInfo($conn, $userId)
{
    $query = "SELECT userId, userName, userSurname, userEmail, myReferralCode, userGender, userAge, userJoinedAt, userPointsJSON, userInvitationJSON 
              FROM Usuarios 
              WHERE userId = ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $userId);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            return [
                'success' => true,
                'data' => $result->fetch_assoc()
            ];
        } else {
            return [
                'success' => false,
                'message' => "Usuário não encontrado."
            ];
        }
    } else {
        error_log("Erro ao executar a consulta: " . $stmt->error);
        return [
            'success' => false,
            'message' => "Erro ao buscar informações do usuário."
        ];
    }
}
