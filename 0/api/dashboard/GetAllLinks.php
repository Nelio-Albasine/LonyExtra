<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/GetAllLinksToTable.log');

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

    $conn = Wamp64Connection();

    if ($conn) {
        try {
            $result = getLinksAvailability($userId, $conn, $batch);

            if (empty($result)) {
                $result = insertLinksAvailability($userId, $conn);
            }
         
            $currentDate = $currentDate = getUserLocalTime(getUserTimeZone($conn, $userId));

            $response = [
                "links" => $result,
                "currentDate" => $currentDate
            ];

            echo json_encode($response, JSON_PRETTY_PRINT);
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

function getUserLocalTime($userTimeZone) {
    $currentUtcTime = new DateTime("now", new DateTimeZone("UTC"));

    $userTimeZoneObj = new DateTimeZone($userTimeZone);

    $currentUtcTime->setTimezone($userTimeZoneObj);

    return $currentUtcTime->format("Y-m-d H:i:s");
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
            //para retornar um lote especifico (porem, atualmente to usando a logica que recupera todos lotes
            //entao esse codigo aqui é desnecessario)
            //#### Entao no escopo atual do projeto, nunca entrarei nessa condicao
            if (isset($linksMap[$batch])) {
                $links = &$linksMap[$batch];
                updateLinksIfExpired($conn, $userId, $links, $isUpdated);
                if ($isUpdated) {
                    saveExpiredLinksUpdate($conn, $userId, $linksMap);
                }

                $response = [$batch => $links];
                return $response;
            } else {
                return ["status" => "error", "message" => "Lote não encontrado."];
            }
        } else {

            foreach ($linksMap as $key => &$links) {
                //atualizar links expirados
                updateLinksIfExpired($conn, $userId,$links, $isUpdated);
            }

            if ($isUpdated) {
                //Salvar esses links atualizados no DB
                saveExpiredLinksUpdate($conn, $userId, $linksMap);
            }

            return $linksMap;
        }
    }

    return [];
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

function updateLinksIfExpired($conn, $userId, &$links, &$isUpdated)
{
    $userTimeZone = getUserTimeZone($conn, $userId);
    $userTimeZone = new DateTimeZone($userTimeZone);

    foreach ($links as &$linkData) {
        if (!$linkData["isAvailable"]) {
            $timeStored = new DateTime($linkData["timeStored"], $userTimeZone);

            $now = new DateTime("now", $userTimeZone);

            $interval = $now->diff($timeStored);

            if ($interval->d >= 1 || $interval->h >= 24 || $interval->days >= 1) {
                $linkData["isAvailable"] = true;
                $linkData["timeStored"] = null;
                $isUpdated = true;
            } else {
                error_log("Horário atual: " . $now->format('Y-m-d H:i:s') . " no fuso horário do usuário: " . $userTimeZone->getName());
            }
        }
    }
}

function saveExpiredLinksUpdate($conn, $userId, $linksMap)
{
    $updatedJson = json_encode($linksMap, JSON_PRETTY_PRINT);
    $updateQuery = "UPDATE Links_Availability SET availabilityJson = ? WHERE userId = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("ss", $updatedJson, $userId);
    $updateStmt->execute();
}

function getUserTimeZone($conn, $userId) {
    $query = "SELECT userTimeZone FROM usuarios WHERE userId = ?";
    $stmt = $conn->prepare($query);

    if ($stmt) {
        $stmt->bind_param("s", $userId);

        if ($stmt->execute()) {
            $result = $stmt->get_result();

            if ($result) {
                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    $userTimeZone = $row["userTimeZone"];
                    return $userTimeZone;
                } else {
                    return "America/Sao_Paulo";
                }
            } else {
                error_log("Erro ao obter o resultado da consulta para userId $userId: " . $stmt->error);
            }
        } else {
            error_log("Falha ao executar a query para userId $userId: " . $stmt->error);
        }
    } else {
        error_log("Erro ao preparar a query: " . $conn->error);
    }

    return "America/Sao_Paulo";
}


try {
    main();
} catch (\Throwable $th) {
    error_log("Ocorreu um erro ao obter os links: \n" . $th->getMessage());
}
