<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/GetAllLinksToTable.log');

require_once "../../0/api/Wamp64Connection.php";

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

function main()
{
    try {
        // Conecta ao banco
        $conn = Wamp64Connection();
        if (!$conn) {
            throw new Exception("Não foi possível conectar ao banco de dados.");
        }

        // Consulta 1: Dados da tabela Usuarios
        $userQuery = "
            SELECT 
                COUNT(*) AS totalUsers, 
                SUM(CAST(JSON_EXTRACT(userPointsJSON, '$.userStars') AS UNSIGNED)) AS totalUserStars,
                SUM(CAST(JSON_EXTRACT(userPointsJSON, '$.userLTStars') AS UNSIGNED)) AS totalUserLTStars,
                SUM(CAST(JSON_EXTRACT(userPointsJSON, '$.userRevenue') AS DECIMAL(10, 2))) AS userRevenue,
                SUM(CAST(JSON_EXTRACT(userPointsJSON, '$.userLTRevenue') AS DECIMAL(10, 2))) AS userLTRevenue
            FROM Usuarios
        ";

        $userStmt = $conn->prepare($userQuery);
        if (!$userStmt) {
            throw new Exception("Erro ao preparar a consulta SQL para Usuarios: " . $conn->error);
        }
        $userStmt->execute();
        $userResult = $userStmt->get_result();
        $userData = $userResult->fetch_assoc();

        // Consulta 2: Soma dos saques na tabela Saques
        $cashoutQuery = "
            SELECT 
                SUM(amountCashedOut) AS totalCashouts
            FROM Saques
        ";

        $cashoutStmt = $conn->prepare($cashoutQuery);
        if (!$cashoutStmt) {
            throw new Exception("Erro ao preparar a consulta SQL para Saques: " . $conn->error);
        }
        $cashoutStmt->execute();
        $cashoutResult = $cashoutStmt->get_result();
        $cashoutData = $cashoutResult->fetch_assoc();

        // Combina os resultados das consultas
        $response = array_merge($userData, $cashoutData);

        // Verifica se há dados
        if ($response) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'data' => $response
            ]);
        } else {
            throw new Exception("Nenhum dado encontrado.");
        }
    } catch (Exception $e) {
        // Registra o erro e envia resposta JSON
        error_log($e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    } finally {
        // Fecha a conexão com o banco
        if (isset($conn)) {
            $conn->close();
        }
    }
}

main();
