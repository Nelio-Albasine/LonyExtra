<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/SearchCashouts.log');

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
    $searchTerm = isset($_GET['searchTerm']) ? $_GET['searchTerm'] : null;

    if (!$searchTerm) {
        echo json_encode(["error" => "Por favor, forneça um termo de pesquisa"]);
        http_response_code(400);
        return;
    }

    $output = searchCashouts($conn, $searchTerm);
    echo json_encode($output);
}

try {
    main();
} catch (\Throwable $th) {
    error_log("Ocorreu um erro ao pesquisar os saques: " . $th->getMessage());
    echo json_encode(["error" => "Erro interno no servidor"]);
    http_response_code(500);
}

// Função para pesquisar saques por endereço de pagamento, ID, nome ou e-mail
function searchCashouts($conn, $searchTerm)
{
    // SQL para procurar no banco de dados baseado em vários critérios
    $sql = "SELECT * FROM Saques WHERE 
            userPaymentAddress LIKE ? OR 
            cashOutId LIKE ? OR 
            userPaymentName LIKE ? OR 
            userEmail LIKE ? 
            ORDER BY created_at ASC";

    if ($stmt = $conn->prepare($sql)) {
        // Adiciona o '%' para permitir que a busca encontre qualquer valor que contenha o termo
        $searchTerm = "%" . $searchTerm . "%";
        
        $stmt->bind_param("ssss", $searchTerm, $searchTerm, $searchTerm, $searchTerm);
        $stmt->execute();
        $result = $stmt->get_result();
        $rows = [];

        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }

        $stmt->close();
        return $rows;
    } else {
        error_log("Erro ao preparar a consulta de pesquisa: " . $conn->error);
        return [];
    }
}
?>
