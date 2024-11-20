<?php
require_once "../Wamp64Connection.php";
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

function main()
{
    header('Content-Type: application/json');
    $response = ["status" => "error", "message" => "Ocorreu um erro inesperado."];

    $userId = isset($_GET['userId']) ? $_GET['userId'] : null;
    $batch = isset($_GET['batch']) ? $_GET['batch'] : null;

    if (!$userId) {
        $response['message'] = "Parâmetro userId não fornecido.";
        echo json_encode($response);
        exit;
    }

    $conn = getMySQLConnection();

    if ($conn) {
        try {
            createTableLinksIfNotExists($conn);

            $result = getLinksAvailability($userId, $conn, $batch);

            if (empty($result)) {
                $result = insertLinksAvailability($userId, $conn);
            }

            echo json_encode($result, JSON_PRETTY_PRINT);
        } catch (Exception $e) {
            $response['message'] = "Erro: " . $e->getMessage();
            echo json_encode($response);
        } finally {
            $conn->close();
        }
    } else {
        $response['message'] = "Erro ao conectar ao banco de dados.";
        echo json_encode($response);
    }
}

function createTableLinksIfNotExists($conn)
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

function insertLinksAvailability($userId, $conn)
{
    $linksMap = [
        "bronzeAvailability" => createLinkGroup("Bronze", $conn),
        "prataAvailability" => createLinkGroup("Prata", $conn),
        "ouroAvailability" => createLinkGroup("Ouro", $conn),
        "diamanteAvailability" => createLinkGroup("Diamante", $conn)
    ];

    $availabilityJson = json_encode($linksMap, JSON_PRETTY_PRINT);
    $stmt = $conn->prepare("INSERT INTO Links_Availability (userId, availabilityJson) VALUES (?, ?)");
    $stmt->bind_param("ss", $userId, $availabilityJson);

    if (!$stmt->execute()) {
        throw new Exception("Erro ao inserir LinksAvailability: " . $stmt->error);
    }

    return $linksMap;
}

function getLinksAvailability($userId, $conn, $batch = null)
{
    $query = "SELECT availabilityJson FROM Links_Availability WHERE userId = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $availabilityJson = $row["availabilityJson"];
        $linksMap = json_decode($availabilityJson, true);

        $isUpdated = false;

        if ($batch) {
            if (isset($linksMap[$batch])) {
                $links = &$linksMap[$batch];
                updateLinks($links, $isUpdated);
                if ($isUpdated) {
                    saveUpdatedLinks($conn, $userId, $linksMap);
                }
                return [$batch => $links];
            } else {
                return ["status" => "error", "message" => "Lote não encontrado."];
            }
        } else {
            foreach ($linksMap as $key => &$links) {
                updateLinks($links, $isUpdated);
            }
            if ($isUpdated) {
                saveUpdatedLinks($conn, $userId, $linksMap);
            }
            return $linksMap;
        }
    }

    return [];
}

function getLinksBatchs($conn)
{
    $sql = "SELECT loteBronze, lotePrata, loteOuro, loteDiamante FROM dash_links";
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
    for ($i = 1; $i <= 10; $i++) {
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

function updateLinks(&$links, &$isUpdated)
{
    foreach ($links as &$linkData) {
        if (!$linkData["isAvailable"]) {
            $timeStored = new DateTime($linkData["timeStored"]);
            $now = new DateTime();
            $interval = $now->diff($timeStored);
            if ($interval->h >= 24) {
                $linkData["isAvailable"] = true;
                $isUpdated = true;
            }
        }
    }
}

function saveUpdatedLinks($conn, $userId, $linksMap)
{
    $updatedJson = json_encode($linksMap, JSON_PRETTY_PRINT);
    $updateQuery = "UPDATE Links_Availability SET availabilityJson = ? WHERE userId = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("ss", $updatedJson, $userId);
    $updateStmt->execute();
}

main();
