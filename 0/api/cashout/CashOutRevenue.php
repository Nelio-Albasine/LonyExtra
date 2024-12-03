<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/CashOutRevenue.log');

require_once "../Wamp64Connection.php";
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

$conn = getMySQLConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = file_get_contents('php://input');
    $cashOutRequest = json_decode($input, true);

    if (!$cashOutRequest) {
        echo json_encode(['success' => false, 'message' => 'Dados inválidos recebidos!']);
        exit;
    }

    $userId = $cashOutRequest['userId'] ?? null;
    $gatewayName = $cashOutRequest['gatewayName'] ?? null;
    $amountIndex = $cashOutRequest['amountIndex'] ?? null;
    $userPaymentName = $cashOutRequest['userPaymentName'] ?? null;
    $userPaymentAddress = $cashOutRequest['userPaymentAddress'] ?? null;
    $cashOutId = null;
    $created_at = gmdate('Y-m-d H:i:s');


    $allowedGateways = ['paypal', 'pix'];
    $indexAmountToValues = [
        0 => 1.0,
        1 => 5.2,
        2 => 10.5,
        3 => 21.3,
        4 => 30.5,
        5 => 48.30
    ];

    if (!in_array($gatewayName, $allowedGateways)) {
        echo json_encode(['success' => false, 'message' => 'Método de saque inválido!']);
        exit;
    }

    if (!isset($indexAmountToValues[$amountIndex])) {
        echo json_encode(['success' => false, 'message' => 'Escolha uma quantia de saque válida!']);
        exit;
    }

    $amountToCashOut = $indexAmountToValues[$amountIndex];

    try {
        createTableSaquesIfNotExists($conn);

        $userRevenueJsonString = checkIfUserHasSufficientRevenue($conn, $userId);
        if (!$userRevenueJsonString) {
            echo json_encode(['success' => false, 'message' => 'Nenhum registro encontrado para o usuário!']);
            exit;
        }

        $userRevenueJson = json_decode($userRevenueJsonString, true);
        if ($userRevenueJson['userRevenue'] < $amountToCashOut) {
            echo json_encode(['success' => false, 'message' => '405']);
            exit;
        }

        $remainingBalance = $userRevenueJson['userRevenue'] - $amountToCashOut;
        $userRevenueJson['userRevenue'] = $remainingBalance;

        $insertResponse = insertCashOutIntoTable($conn, $userId, $created_at,  $gatewayName, $amountToCashOut, $userPaymentName, $userPaymentAddress, $cashOutId);
        if (!$insertResponse['success']) {
            echo json_encode($insertResponse);
            exit;
        }

        $updateSuccess = updateUserRevenue($conn, $userId, json_encode($userRevenueJson));
        if ($updateSuccess) {
            echo json_encode([
                'success' => true,
                'message' => 'Saque realizado com sucesso!',
                'cashOutId' => $cashOutId,
                'created_at' => $created_at
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao atualizar os dados do usuário.']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
    }
}

function createTableSaquesIfNotExists($conn)
{
    $query = "
        CREATE TABLE IF NOT EXISTS Saques (
            id INT AUTO_INCREMENT PRIMARY KEY,
            userId VARCHAR(191) NOT NULL,
            gatewayName VARCHAR(10) NOT NULL,
            cashOutId VARCHAR(6) UNIQUE NOT NULL,
            amountCashedOut DECIMAL(10,2),
            cashOutStatus INT(1),
            userPaymentName VARCHAR(27) NOT NULL,
            userPaymentAddress VARCHAR(45) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
    ";
    if (!mysqli_query($conn, $query)) {
        die("Erro ao criar a tabela: " . mysqli_error($conn));
    }
}

function checkIfUserHasSufficientRevenue($conn, $userId)
{
    $userPointsJSON = null;
    $stmt = $conn->prepare("SELECT userPointsJSON FROM Usuarios WHERE userId = ?");
    if (!$stmt) {
        throw new Exception("Erro ao preparar consulta: " . $conn->error);
    }

    $stmt->bind_param("s", $userId);
    $stmt->execute();
    $stmt->bind_result($userPointsJSON);

    if ($stmt->fetch()) {
        return $userPointsJSON;
    } else {
        return null;
    }
}

function insertCashOutIntoTable($conn, $userId, $created_at, $gatewayName, $amountCashedOut, $userPaymentName, $userPaymentAddress, &$cashOutId)
{
    $cashOutStatusDefault = 0;

    try {
        $cashOutId = generateNumericID();
        $query = "
            INSERT INTO Saques (
                userId, gatewayName, cashOutId, created_at, cashOutStatus, amountCashedOut, userPaymentName, userPaymentAddress
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ";
        $stmt = $conn->prepare($query);
        $stmt->execute([$userId, $gatewayName, $cashOutId, $created_at, $cashOutStatusDefault, $amountCashedOut, $userPaymentName, $userPaymentAddress]);

        return ['success' => true, 'message' => 'Novo registro inserido com sucesso!'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Erro ao inserir registro: ' . $e->getMessage()];
    }
}

function updateUserRevenue($conn, $userId, $userRevenueJson)
{
    $stmt = $conn->prepare("UPDATE Usuarios SET userPointsJSON = ? WHERE userId = ?");
    return $stmt->execute([$userRevenueJson, $userId]);
}

function generateNumericID()
{
    return str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
}
