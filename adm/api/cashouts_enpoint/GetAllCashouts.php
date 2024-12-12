<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/GetAllCashOutsToTable.log');

require_once "../../../0/api/Wamp64Connection.php";

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

function main()
{
    $conn = Wamp64Connection();
    $filterBy = $_GET['filterBy'] ?: "1";

    $output = getAllCashouts($conn, $filterBy);


    echo json_encode($output);
}

try {
    main();
} catch (\Throwable $th) {
    error_log("Ocorreu um erro ao obter todos saques: " . $th->getMessage());
}

function getAllCashouts($conn, $filterBy)
{
    $whereClause = null;
    $equalTo = null;

    switch ($filterBy) {
        case '1':
            # Filtrar por saques Pendentes...
            $whereClause = "cashOutStatus";
            $equalTo = 0;
            break;
        case '2':
            # Filtrar por saques Pagos...
            $whereClause = "cashOutStatus";
            $equalTo = 1;
            break;
        case '3':
            # Filtrar por saques Recusados...
            $whereClause = "cashOutStatus";
            $equalTo = 2;
            break;
        case '4':
            # Filtrar por saques gateways PayPal...
            $whereClause = "gatewayName";
            $equalTo = "paypal";
            break;
        case '5':
            # Filtrar por saques gateways Pix...
            $whereClause = "gatewayName";
            $equalTo = "pix";
            break;
        default:
            # Filtrar por saques Pendentes...
            $whereClause = "cashOutStatus";
            $equalTo = 0;
            break;
    }

    if ($whereClause === null) {
        return [];
    }

    $sql = "SELECT * FROM Saques WHERE $whereClause = ?";

    if ($stmt = $conn->prepare($sql)) {
        if (is_int($equalTo)) {
            $stmt->bind_param("i", $equalTo);
        } else {
            $stmt->bind_param("s", $equalTo);
        }

        $stmt->execute();

        $result = $stmt->get_result();
        $rows = [];

        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }

        $stmt->close();
        return $rows;
    } else {
        error_log("Erro ao preparar a consulta: " . $conn->error);
        return [];
    }
}
