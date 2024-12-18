<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/get_dash_info.log');

// Definindo os cabeçalhos CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');

require_once "../Wamp64Connection.php";

try {
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] == "GET") {
        $conn = Wamp64Connection();
        handleDefaultDashLinksTableCreation($conn);
        createTableLinksAvailabilityIfNotExists($conn);


        if (isset($_GET['userId'])) {
            $userId = $_GET['userId'];

            $userInfo = getDashboardInfo($conn, $userId);

            require_once "LinkAvailabilityChecker.php";

            $hasValidLinksPerBatch = hasValidLinksPerBatch($userId);

            if (empty($hasValidLinksPerBatch)) {
                insertLinksAvailability($userId, $conn);
                $hasValidLinksPerBatch = hasValidLinksPerBatch($userId);
            } 


            require_once "../invitation/handleInvitation.php";
            $myInviterInfo = [];
            $isInviterCodeEmptyOrNull = isInviterCodeEmptyOrNull($conn, $userId);

            if (!empty($isInviterCodeEmptyOrNull)) {
                $myInviterInfo = getMyInviterInfo($conn, getMyInviterUserId($conn, $isInviterCodeEmptyOrNull));
            }

            $userInfo['data']['userLTCashouts'] = floatval(getUserLTCashouts($conn, $userId));

            $output = [
                'success' => $userInfo['success'],
                'userInfo' => $userInfo['data'] ?? [],
                'hasValidLinksPerBatch' => json_encode($hasValidLinksPerBatch) ?: [],
                'myInviterInfo' => json_encode($myInviterInfo) ?: []
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
} catch (\Throwable $th) {
    error_log("Ocorreu erro em GetDashboard: " . $th->getMessage());
}

function getDashboardInfo($conn, $userId)
{
    $query = "SELECT userId, userLockStatus , userName, userSurname, userEmail, userTimeZone, myReferralCode, userGender, userAge, userJoinedAt, userPointsJSON, userInvitationJSON 
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
            "timeStored" => gmdate("Y-m-d H:i:s")
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

function getUserLTCashouts($conn, $userId)
{
    $totalCashoutRevenue = 0;
    $sql = "SELECT SUM(amountCashedOut) AS totalCashouts FROM Saques WHERE userId = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $userId);
    $stmt->execute();
    $stmt->bind_result($totalCashoutRevenue);
    $stmt->fetch();
    $stmt->close();
    return $totalCashoutRevenue ?: 0;
}


function handleDefaultDashLinksTableCreation($conn)
{
    $lotes = [
        'loteBronze' => json_encode([
            'link_1' => 'https://alpharede.com/Bronze_Tarefa_1',
            'link_2' => 'https://alpharede.com/Bronze_Tarefa_2',
            'link_3' => 'https://alpharede.com/Bronze_Tarefa_3',
            'link_4' => 'https://alpharede.com/Bronze_Tarefa_4',
            'link_5' => 'https://alpharede.com/Bronze_Tarefa_5',
            'link_6' => 'https://alpharede.com/Bronze_Tarefa_6',
            'link_7' => 'https://alpharede.com/Bronze_Tarefa_7',
            'link_8' => 'https://alpharede.com/Bronze_Tarefa_8',
            'link_9' => 'https://alpharede.com/Bronze_Tarefa_9',
            'link_10' => 'https://alpharede.com/Bronze_Tarefa_10',
            'link_11' => 'https://alpharede.com/Bronze_Tarefa_11',
            'link_12' => 'https://alpharede.com/Bronze_Tarefa_12',
            'link_13' => 'https://alpharede.com/Bronze_Tarefa_13',
            'link_14' => 'https://alpharede.com/Bronze_Tarefa_14',
            'link_15' => 'https://alpharede.com/Bronze_Tarefa_15'
        ]),
        'lotePrata' => json_encode([
            'link_1' => 'https://alpharede.com/Prata_Tarefa_1',
            'link_2' => 'https://alpharede.com/Prata_Tarefa_2',
            'link_3' => 'https://alpharede.com/Prata_Tarefa_3',
            'link_4' => 'https://alpharede.com/Prata_Tarefa_4',
            'link_5' => 'https://alpharede.com/Prata_Tarefa_5',
            'link_6' => 'https://alpharede.com/Prata_Tarefa_6',
            'link_7' => 'https://alpharede.com/Prata_Tarefa_7',
            'link_8' => 'https://alpharede.com/Prata_Tarefa_8',
            'link_9' => 'https://alpharede.com/Prata_Tarefa_9',
            'link_10' => 'https://alpharede.com/Prata_Tarefa_10',
            'link_11' => 'https://alpharede.com/Prata_Tarefa_11',
            'link_12' => 'https://alpharede.com/Prata_Tarefa_12',
            'link_13' => 'https://alpharede.com/Prata_Tarefa_13',
            'link_14' => 'https://alpharede.com/Prata_Tarefa_14',
            'link_15' => 'https://alpharede.com/Prata_Tarefa_15'
        ]),
        'loteOuro' => json_encode([
            'link_1' => 'https://alpharede.com/Ouro_Tarefa_1',
            'link_2' => 'https://alpharede.com/Ouro_Tarefa_2',
            'link_3' => 'https://alpharede.com/Ouro_Tarefa_3',
            'link_4' => 'https://alpharede.com/Ouro_Tarefa_4',
            'link_5' => 'https://alpharede.com/Ouro_Tarefa_5',
            'link_6' => 'https://alpharede.com/Ouro_Tarefa_6',
            'link_7' => 'https://alpharede.com/Ouro_Tarefa_7',
            'link_8' => 'https://alpharede.com/Ouro_Tarefa_8',
            'link_9' => 'https://alpharede.com/Ouro_Tarefa_9',
            'link_10' => 'https://alpharede.com/Ouro_Tarefa_10',
            'link_11' => 'https://alpharede.com/Ouro_Tarefa_11',
            'link_12' => 'https://alpharede.com/Ouro_Tarefa_12',
            'link_13' => 'https://alpharede.com/Ouro_Tarefa_13',
            'link_14' => 'https://alpharede.com/Ouro_Tarefa_14',
            'link_15' => 'https://alpharede.com/Ouro_Tarefa_15'
        ]),
        'loteDiamante' => json_encode([
            'link_1' => 'https://alpharede.com/Diamante_Tarefa_1',
            'link_2' => 'https://alpharede.com/Diamante_Tarefa_2',
            'link_3' => 'https://alpharede.com/Diamante_Tarefa_3',
            'link_4' => 'https://alpharede.com/Diamante_Tarefa_4',
            'link_5' => 'https://alpharede.com/Diamante_Tarefa_5',
            'link_6' => 'https://alpharede.com/Diamante_Tarefa_6',
            'link_7' => 'https://alpharede.com/Diamante_Tarefa_7',
            'link_8' => 'https://alpharede.com/Diamante_Tarefa_8',
            'link_9' => 'https://alpharede.com/Diamante_Tarefa_9',
            'link_10' => 'https://alpharede.com/Diamante_Tarefa_10',
            'link_11' => 'https://alpharede.com/Diamante_Tarefa_11',
            'link_12' => 'https://alpharede.com/Diamante_Tarefa_12',
            'link_13' => 'https://alpharede.com/Diamante_Tarefa_13',
            'link_14' => 'https://alpharede.com/Diamante_Tarefa_14',
            'link_15' => 'https://alpharede.com/Diamante_Tarefa_15'
        ]),
        'lotePlatina' => json_encode([
            'link_1' => 'https://alpharede.com/Platina_Tarefa_1',
            'link_2' => 'https://alpharede.com/Platina_Tarefa_2',
            'link_3' => 'https://alpharede.com/Platina_Tarefa_3',
            'link_4' => 'https://alpharede.com/Platina_Tarefa_4',
            'link_5' => 'https://alpharede.com/Platina_Tarefa_5',
            'link_6' => 'https://alpharede.com/Platina_Tarefa_6',
            'link_7' => 'https://alpharede.com/Platina_Tarefa_7',
            'link_8' => 'https://alpharede.com/Platina_Tarefa_8',
            'link_9' => 'https://alpharede.com/Platina_Tarefa_9',
            'link_10' => 'https://alpharede.com/Platina_Tarefa_10',
            'link_11' => 'https://alpharede.com/Platina_Tarefa_11',
            'link_12' => 'https://alpharede.com/Platina_Tarefa_12',
            'link_13' => 'https://alpharede.com/Platina_Tarefa_13',
            'link_14' => 'https://alpharede.com/Platina_Tarefa_14',
            'link_15' => 'https://alpharede.com/Platina_Tarefa_15'
        ])
    ];

    try {
        createDefaultTableDashLinkWithInitialData($conn, $lotes);
    } catch (Exception $e) {
        error_log("ocorreu um erro ao criar a tabela Dash_Links, o erro é: " . $e->getMessage());
    }
}

function createDefaultTableDashLinkWithInitialData($conn, $lotes)
{
    $createTableSQL = "
        CREATE TABLE IF NOT EXISTS Dash_Links (
            id INT AUTO_INCREMENT PRIMARY KEY,
            loteBronze JSON NULL,
            lotePrata JSON NULL,
            loteOuro JSON NULL,
            loteDiamante JSON NULL,
            lotePlatina JSON NULL
        );
    ";

    $checkTableSQL = "SELECT COUNT(*) AS count FROM Dash_Links";

    $conn->query($createTableSQL);

    $result = $conn->query($checkTableSQL);
    $row = $result->fetch_assoc();
    if ($row['count'] > 0) {
        return false;
    }

    $insertSQL = "
        INSERT INTO Dash_Links (loteBronze, lotePrata, loteOuro, loteDiamante, lotePlatina)
        VALUES (?, ?, ?, ?, ?)
    ";
    $stmt = $conn->prepare($insertSQL);
    $stmt->bind_param(
        'sssss',
        $lotes['loteBronze'],
        $lotes['lotePrata'],
        $lotes['loteOuro'],
        $lotes['loteDiamante'],
        $lotes['lotePlatina']
    );
    $stmt->execute();
    return true;
}

function createTableLinksAvailabilityIfNotExists($conn)
{
    $createTable = "
        CREATE TABLE IF NOT EXISTS Links_Availability(
            id INT AUTO_INCREMENT PRIMARY KEY,
            userId VARCHAR(191) NOT NULL UNIQUE,
            availabilityJson JSON NOT NULL
        );
    ";

    if (!$conn->query($createTable)) {
        throw new Exception("Erro ao criar/verificar tabela: " . $conn->error);
    }
}