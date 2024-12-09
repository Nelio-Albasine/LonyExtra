<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/Influencers.log');

require_once "../Wamp64Connection.php";

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $inputData = json_decode(file_get_contents("php://input"), true);

    if (isset($inputData['influencer'])) {
        $conn = Wamp64Connection();
        
        createTableInfluencersIfNotExist($conn);

        $userId = $inputData['userId'];
        $referralCode = $inputData['referralCode'];
        $influencer = $inputData['influencer'];

        $influencerInfoFromUsers = getInfluencerNameUIDreferralCode($conn, $influencer["data"]["influencerEmail"]);
        if ($influencerInfoFromUsers['success']) {
            $userId = $influencerInfoFromUsers["userId"];
            $referralCode = $influencerInfoFromUsers["myReferralCode"];
            $influencer["data"]["influencerName"] = $influencerInfoFromUsers["userName"];

            if (checkIfInfluencerExists($conn, $userId)) {
                sendResponseToUser([
                    "success" => false,
                    "message" => "Influenciador já existe no sistema."
                ]);
            } else if (insertInfluencer($conn, $influencer, $userId, $referralCode)) {
                sendResponseToUser([
                    "success" => true,
                    "message" => "Influenciador inserido com sucesso.",
                    "userId" => $userId,
                    "referralCode" => $referralCode
                ]);
            } else {
                sendResponseToUser([
                    "success" => false,
                    "message" => "Erro ao inserir influenciador."
                ]);
            }
        } else {
            sendResponseToUser($influencerInfoFromUsers);
        }

        $conn->close();
    } else {
        sendResponseToUser([
            "success" => false,
            "message" => "Dados faltando na requisição."
        ]);
    }
} else {
    sendResponseToUser([
        "success" => false,
        "message" => "Apenas requisições POST são permitidas."
    ]);
}

function getInfluencerNameUIDreferralCode($conn, $email)
{
    $sql = "SELECT userName, userSurname, userId, myReferralCode FROM Usuarios WHERE userEmail = ?";
    $stmt = $conn->prepare($sql);

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        return [
            "success" => true,
            "userName" => $row["userName"] . " " . $row["userSurname"],
            "userId" => $row["userId"],
            "myReferralCode" => $row["myReferralCode"]
        ];
    } else {
        return [
            "success" => false,
            "message" => "Usuário não encontrado"
        ];
    }
}

function checkIfInfluencerExists($conn, $userId)
{
    $sql = "SELECT COUNT(*) AS count FROM Influencers WHERE userId = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $userId);
    $stmt->execute();

    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    return $row['count'] > 0;
}

function insertInfluencer($conn, $influencer, $userId, $referralCode)
{
    $sql = "INSERT INTO Influencers (userId, referralCode, isActive, influencerData) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    $influencerDataJson = json_encode($influencer['data']);
    $stmt->bind_param("ssis", $userId, $referralCode, $influencer['isActive'], $influencerDataJson);

    $success = $stmt->execute();
    $stmt->close();

    return $success;
}

function sendResponseToUser($response)
{
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}


function createTableInfluencersIfNotExist($conn)
{
    $createTableSQL = "
        CREATE TABLE IF NOT EXISTS Influencers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            userId VARCHAR(255) NOT NULL,
            referralCode VARCHAR(6),
            isActive BOOLEAN DEFAULT TRUE,
            influencerData JSON NULL
        );
    ";

    $conn->query($createTableSQL);
}
