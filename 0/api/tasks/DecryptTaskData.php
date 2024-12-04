<?php
require_once './Config.php';
require_once '../Wamp64Connection.php';

// Configurações de cabeçalhos para CORS e tipo de conteúdo
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Configurações de ambiente
error_reporting(E_ALL);
ini_set('display_errors', 1);
define('LOG_FILE_PATH', __DIR__ . '/../logs/EncryptAndDecryption.log');
ini_set('error_log', LOG_FILE_PATH);

// Trata requisições OPTIONS (pré-voo)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

function main()
{
    // Lê e valida os dados recebidos na requisição
    $input = getInputData();
    validateInputData($input);

    $SECRET_KEY = LONY_EXTRA_POINTS_SECRET_KEY;
    if (empty($SECRET_KEY)) {
        respondWithError('Chave secreta não configurada no servidor.');
    }

    $decryptedData = decryptData($input['encryptedData'], $input['iv'], $SECRET_KEY);

    if ($decryptedData === false) {
        respondWithError('Falha ao descriptografar os dados.');
    }

    logDecryptedData($decryptedData);


    $decryptedData = json_decode($decryptedData, true);

    if ($decryptedData === null || !is_array($decryptedData)) {
        respondWithError('Os dados descriptografados não são um JSON válido.');
    }

    $conn = Wamp64Connection();
    $linkStatusUpdateResponse = processUserPointsAndLinkStatus($conn, $decryptedData);

    // Retorna uma resposta de sucesso
    respondWithSuccess(['success' => $linkStatusUpdateResponse]);
}

try {
    main();
} catch (\Throwable $th) {
    error_log("Ocorreum um erro generico no DecrypeTaskData.php: " . $th->getMessage());
}
exit;

/**
 * Função para obter os dados enviados na requisição.
 */
function getInputData(): array
{
    $rawInput = file_get_contents('php://input');
    $decodedInput = json_decode($rawInput, true);

    if ($decodedInput === null || !is_array($decodedInput)) {
        respondWithError('O corpo da requisição deve ser um JSON válido.');
    }

    return $decodedInput;
}

/**
 * Função para validar os dados obrigatórios da entrada.
 */
function validateInputData(array $input): void
{
    if (empty($input['encryptedData']) || empty($input['iv'])) {
        respondWithError('Os parâmetros "encryptedData" e "iv" são obrigatórios.');
    }
}

/**
 * Função para descriptografar os dados.
 */
function decryptData(string $encryptedData, string $iv, string $key): string|false
{
    $decodedData = base64_decode($encryptedData);
    $decodedIv = base64_decode($iv);
    $method = 'AES-256-CBC';

    if ($decodedData === false || $decodedIv === false) {
        respondWithError('Os dados recebidos estão inválidos.');
    }

    return openssl_decrypt($decodedData, $method, $key, 0, $decodedIv);
}

/**
 * Função para registrar logs de dados descriptografados.
 */
function logDecryptedData(string $data): void
{
    error_log("Decrypted Data: " . $data);
}

/**
 * Função para enviar uma resposta de erro em JSON.
 */
function respondWithError(string $message, int $httpCode = 400): void
{
    http_response_code($httpCode);
    echo json_encode(['error' => $message]);
    exit;
}

/**
 * Função para enviar uma resposta de sucesso em JSON.
 */
function respondWithSuccess(array $data, int $httpCode = 200): void
{
    http_response_code($httpCode);
    echo json_encode($data);
    exit;
}

/**
 * Função para atualizar a pontuacao do usuario
 */
function updateUserPoints($conn, $decryptedData, $userId, $taskId)
{
    $index = $decryptedData["index"];
    $starsToEarn = 10;

    switch ($index) {
        case 0:
            $starsToEarn = 10;
            break;
        case 1:
            $starsToEarn = 20;
            break;
        case 2:
            $starsToEarn = 30;
            break;
        case 3:
            $starsToEarn = 50;
            break;
        case 4:
            $starsToEarn = 80;
            break;
    }

    $updatePointsQuery = "
        UPDATE Usuarios
        SET userPointsJSON = JSON_SET(
            userPointsJSON,
            '$.userStars', JSON_EXTRACT(userPointsJSON, '$.userStars') + ?,
            '$.userLTStars', JSON_EXTRACT(userPointsJSON, '$.userLTStars') + ?
        )
        WHERE userId = ?
    ";

    $stmt = $conn->prepare($updatePointsQuery);
    if ($stmt === false) {
        return false;
    }

    $stmt->bind_param("iis", $starsToEarn, $starsToEarn, $userId);

    if ($stmt->execute()) {
        $stmt->close();
        return true;
    } else {
        $stmt->close();
        return false;
    }
}

/**
 * Função para atualizar o status e a data do link
 */
function updatelinkStatus($conn, $userId, $taskId, $jsonBatchName)
{
    $userTimeZone = getUserTimeZone($conn, $userId);

    $userDateTime = new DateTime("now", new DateTimeZone($userTimeZone));
    $timeToStored = $userDateTime->format("Y-m-d H:i:s");

    $isAvailable = false;

    $updateQuery = "
        UPDATE links_availability
        SET availabilityJson = JSON_SET(
            availabilityJson,
            '$.$jsonBatchName.$taskId.timeStored', ?,
            '$.$jsonBatchName.$taskId.isAvailable', ?
        )
        WHERE userId = ?
    ";

    $stmt = $conn->prepare($updateQuery);
    if ($stmt === false) {
        error_log("Erro ao preparar a query: " . $conn->error);
        return false;
    }

    // Bind dos parâmetros (string para timeStored, boolean para isAvailable, e int para userId)
    $stmt->bind_param("sii", $timeToStored, $isAvailable, $userId);

    // Executa a query e verifica o resultado
    if ($stmt->execute()) {
        $stmt->close();
        return true;
    } else {
        error_log("Erro ao executar a query: " . $stmt->error);
        $stmt->close();
        return false;
    }
}

function processUserPointsAndLinkStatus($conn, array $decryptedData): bool
{
    $userId = $decryptedData["userId"];
    $taskId = $decryptedData["taskId"];
    $index = $decryptedData["index"];

    $jsonBatchName = null;

    switch ($index) {
        case 0:
            $jsonBatchName = "bronzeAvailability";
            break;
        case 1:
            $jsonBatchName = "prataAvailability";
            break;
        case 2:
            $jsonBatchName = "ouroAvailability";
            break;
        case 3:
            $jsonBatchName = "diamanteAvailability";
            break;
        case 4:
            $jsonBatchName = "platinaAvailability";
            break;
    }

    if ($jsonBatchName === null) {
        return false;
    }

    /*
        Verificar se, essa requisicao para receber o premio,
        é de fato de uma tarefa que esta DISPONIVEL.
     */

    $checkIfTaskIdIsAvailable = checkIfTaskIdIsAvailable($conn, $userId, $taskId, $jsonBatchName);

    if ($checkIfTaskIdIsAvailable === true) {
        if (!updateUserPoints($conn, $decryptedData, $userId, $taskId)) {
            respondWithError('Falha ao atualizar a pontuação do usuário');
        }

        if (!updateLinkStatus(
            $conn,
            $userId,
            $taskId,
            $jsonBatchName
        )) {
            respondWithError('Falha ao atualizar o status do link');
        }
        return true;
    } else {
        //user is tryng to earn stars from an expired/unavailable Task
        echo json_encode(['message' => "204"]);
        exit;
    }
}

function checkIfTaskIdIsAvailable($conn, $userId, $taskId, $jsonBatchName): bool
{
    $isAvailable = "";

    // Modifique o caminho JSON para usar aspas duplas nas chaves
    $query = "
        SELECT JSON_EXTRACT(availabilityJson, CONCAT('$.', JSON_QUOTE(?), '.', JSON_QUOTE(?), '.isAvailable')) AS isAvailable
        FROM links_availability
        WHERE userId = ?
    ";

    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        error_log("Erro ao preparar a consulta: " . $conn->error);
        return false;
    }

    // Use "sss" para os tipos de parâmetros: string para $jsonBatchName, $taskId e $userId
    $stmt->bind_param("sss", $jsonBatchName, $taskId, $userId);
    $stmt->execute();
    $stmt->bind_result($isAvailable);
    $stmt->fetch();
    $stmt->close();


    $isAvailable = json_decode($isAvailable);

    error_log("Essa tarefa: $taskId do lote: $jsonBatchName tem o isAvailable como: " . ($isAvailable ? 'true' : 'false'));

    return $isAvailable === true;
}

function getUserTimeZone($conn, $userId)
{
    $query = "SELECT userTimeZone FROM Usuarios WHERE userId = ?";
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

function updatemyInviterStars() {}
