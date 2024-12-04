<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/get_dash_info.log');


// Definindo os cabeçalhos CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');


try {
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }

    header('Content-Type: application/json; charset=utf-8');

    require_once "../Wamp64Connection.php";

    if ($_SERVER['REQUEST_METHOD'] == "GET") {
        $conn = Wamp64Connection();

        if (isset($_GET['userId'])) {
            $userId = $_GET['userId'];

            $userInfo = getDashboardInfo($conn, $userId);

            require_once "LinkAvailabilityChecker.php";
            require_once "../invitation/handleInvitation.php";

            $hasValidLinksPerBatch = hasValidLinksPerBatch($userId);

            error_log("O hasValidLinksPerBatch retornouL " . $hasValidLinksPerBatch);

            if (empty($hasValidLinksPerBatch)) {
                $hasValidLinksPerBatch = insertLinksAvailability($userId, $conn);
                error_log("O insertLinksAvailability retornouL " . $hasValidLinksPerBatch);
            }

            $myInviterInfo = [];
            $isInviterCodeEmptyOrNull = isInviterCodeEmptyOrNull($conn, $userId);

            if (!empty($isInviterCodeEmptyOrNull)) {
                $myInviterInfo = getMyInviterInfo($conn, getMyInviterUserId($conn, $isInviterCodeEmptyOrNull));
            }

            $output = [
                'success' => $userInfo['success'],
                'userInfo' => $userInfo['data'] ?? [],
                'hasValidLinksPerBatch' => json_decode(json: $hasValidLinksPerBatch) ?? [],
                'myInviterInfo' => json_encode($myInviterInfo) ?? []
            ];
        } else {
            $output = [
                'success' => false,
                'message' => "Parâmetro 'userId' é obrigatório."
            ];
        }

        error_log("Output da tela Home é: " . print_r($output, true));
        echo json_encode($output);
        $conn->close();
    } else {
        error_log("A requisição não é GET");
        http_response_code(405);
        echo json_encode(["success" => false, "message" => "Método não permitido"]);
        exit;
    }
} catch (\Throwable $th) {
    error_log("Ocorreu erro em GetDashboard: " . $th->getMessage());
}

// Função para obter as informações do dashboard do usuário
function getDashboardInfo($conn, $userId)
{
    $query = "SELECT userId, userName, userSurname, userEmail, userTimeZone, myReferralCode, userGender, userAge, userJoinedAt, userPointsJSON, userInvitationJSON 
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


function insertLinksAvailability($userId, $conn)
{
    $linksMap = [
        "bronzeAvailability" => createLinkGroup("Bronze", $conn),
        "prataAvailability" => createLinkGroup("Prata", $conn),
        "ouroAvailability" => createLinkGroup("Ouro", $conn),
        "diamanteAvailability" => createLinkGroup("Diamante", $conn),
        "platinaAvailability" => createLinkGroup("Platina", $conn)
    ];

    $availabilityJson = json_encode($linksMap, JSON_PRETTY_PRINT);
    $stmt = $conn->prepare("INSERT INTO Links_Availability (userId, availabilityJson) VALUES (?, ?)");
    $stmt->bind_param("ss", $userId, $availabilityJson);

    if (!$stmt->execute()) {
        throw new Exception("Erro ao inserir LinksAvailability: " . $stmt->error);
    }

    return $linksMap;
}


function createLinkGroup($batch, $conn)
{
    $linksBatchs = getLinksBatchs($conn);

    if (empty($linksBatchs)) {
        error_log("Nenhum lote de links encontrado.");
        return [];
    }

    $group = [];
    $batchKey = "lote" . ucfirst(strtolower($batch));

    if (!isset($linksBatchs[0][$batchKey])) {
        error_log("Lote {$batch} não encontrado.");
        return [];
    }

    $batchLinks = $linksBatchs[0][$batchKey];
    for ($i = 1; $i <= 15; $i++) {
        $linkKey = "link_" . $i;
        $url = $batchLinks[$linkKey] ?? null;

        $key = "{$batch}_{$i}";
        $group[$key] = [
            "url" => $url,
            "isAvailable" => $url !== null,
            "timeStored" => null,
        ];
    }

    return $group;
}


function getLinksBatchs($conn)
{
    $sql = "SELECT loteBronze, lotePrata, loteOuro, loteDiamante, lotePlatina FROM Dash_Links";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->execute();
        $result = $stmt->get_result();

        $links = [];
        while ($row = $result->fetch_assoc()) {
            $links[] = array_map(fn($item) => json_decode($item, true), $row);
        }

        $stmt->close();
        return $links;
    } else {
        error_log("Erro ao preparar a consulta: " . $conn->error);
        return [];
    }
}
