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
    $startDate = isset($_GET['startDate']) ? $_GET['startDate'] : null;
    $endDate = isset($_GET['endDate']) ? $_GET['endDate'] : null;

    if (!$startDate || !$endDate) {
        echo json_encode(["error" => "Por favor, forneça startDate e endDate"]);
        http_response_code(400);
        return;
    }

    // Converte as datas UTC para o fuso horário local (Africa/Maputo)
    $startDate = convertUtcToLocal($startDate, 'Africa/Maputo');
    $endDate = convertUtcToLocal($endDate, 'Africa/Maputo');

    $output = getAllCashoutsFilteredByDate($conn, $startDate, $endDate);
    echo json_encode($output);
}

try {
    main();
} catch (\Throwable $th) {
    error_log("Ocorreu um erro ao obter os saques filtrados por data: " . $th->getMessage());
    echo json_encode(["error" => "Erro interno no servidor"]);
    http_response_code(500);
}

// Função para converter UTC para fuso horário local
function convertUtcToLocal($date, $timeZone)
{
    // Cria um novo objeto DateTime com a data recebida em UTC
    $utcDate = new DateTime($date, new DateTimeZone('UTC'));

    // Converte para o fuso horário desejado
    $localDate = $utcDate->setTimezone(new DateTimeZone($timeZone));

    // Retorna a data no formato adequado para o banco de dados
    return $localDate->format('Y-m-d H:i:s');
}

function getAllCashoutsFilteredByDate($conn, $startDate, $endDate)
{
    // Query para obter os saques entre o intervalo de datas fornecido
    $sql = "SELECT * FROM Saques WHERE created_at BETWEEN ? AND ? ORDER BY created_at ASC";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ss", $startDate, $endDate);

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
?>
