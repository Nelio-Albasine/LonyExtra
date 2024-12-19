<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/CashOutRevenue.log');

require_once "../Wamp64Connection.php";
require_once "../settings/CheckIfUserIsBanned.php";

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

$conn = Wamp64Connection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = file_get_contents('php://input');
    $cashOutRequest = json_decode($input, true);

    if (!$cashOutRequest) {
        echo json_encode(['success' => false, 'message' => 'Dados inválidos recebidos!']);
        exit;
    }

    $userId = $cashOutRequest['userId'] ?? null;
    $userEmail = $cashOutRequest['userEmail'] ?? null;
    $gatewayName = $cashOutRequest['gatewayName'] ?? null;
    $amountIndex = $cashOutRequest['amountIndex'] ?? null;
    $userPaymentName = $cashOutRequest['userPaymentName'] ?? null;
    $userPaymentAddress = $cashOutRequest['userPaymentAddress'] ?? null;
    $cashOutId = null;
    $created_at = gmdate('Y-m-d H:i:s');


    $allowedGateways = ['paypal', 'pix', 'binance', 'm-pesa', 'e-mola'];

    $indexAmountToValues = [
        0 => 0.50,   // 697 pontos
        1 => 1.30,   // 1446 pontos
        2 => 3.00,   // 3074 pontos
        3 => 7.00,   // 6910 pontos
        4 => 20.00,  // 19385 pontos
        5 => 100.00  // 95400 pontos
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

        if (!checkIfUserIsBanned($conn, $userId)) {
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
    
            $insertResponse = insertCashOutIntoTable($conn, $userId, $userEmail, $created_at,  $gatewayName, $amountToCashOut, $userPaymentName, $userPaymentAddress, $cashOutId);
            if (!$insertResponse['success']) {
                echo json_encode($insertResponse);
                exit;
            }
    
            $updateSuccess = updateUserRevenue($conn, $userId, json_encode($userRevenueJson));
            if ($updateSuccess) {
    
                require "../invitation/handleInvitation.php";
    
                if (updateMyInviterStarsWhenICashOut($conn, $amountIndex, $userId)) {
                    error_log("Atualização de estrelas realizada com sucesso para o usuário!");
                } else {
                    error_log("Falha ao atualizar as estrelas ou ainda nao inseri o codigo de convinte");
                }
    
                echo json_encode([
                    'success' => true,
                    'message' => 'Saque realizado com sucesso!',
                    'cashOutId' => $cashOutId,
                    'created_at' => $created_at
                ]);
    
                if ($conn) {
                    $conn->close();
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Erro ao atualizar os dados do usuário.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => '409']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
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

function insertCashOutIntoTable($conn, $userId, $userEmail, $created_at, $gatewayName, $amountCashedOut, $userPaymentName, $userPaymentAddress, &$cashOutId)
{
    $cashOutStatusDefault = 0;

    try {
        $cashOutId = generateNumericID();
        $query = "
            INSERT INTO Saques (
                userId, userEmail, gatewayName, cashOutId, created_at, cashOutStatus, amountCashedOut, userPaymentName, userPaymentAddress
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ";
        $stmt = $conn->prepare($query);
        $stmt->execute([$userId, $userEmail, $gatewayName, $cashOutId, $created_at, $cashOutStatusDefault, $amountCashedOut, $userPaymentName, $userPaymentAddress]);

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
