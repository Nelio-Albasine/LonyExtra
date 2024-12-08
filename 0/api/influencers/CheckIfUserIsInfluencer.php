<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/Influencers.log');

require_once "../Wamp64Connection.php";
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $userIdOrReferralCode = $_GET["userIdOrReferralCode"];

    if (empty($userIdOrReferralCode)) {
        echo json_encode(["message" => "Parametro userIdOrReferralCode em falta"]);
        exit;
    }

    $conn = Wamp64Connection();

    try {
        createTableInfluencersIfNotExist($conn);

        $response = chefIfUserIsInfluencer($userIdOrReferralCode, $conn);

        returnResponse($response);
    } catch (Exception $e) {
        $message = ["message" => "Erro durante a execução: " . $e->getMessage()];
        echo json_encode($message);
        error_log(print_r($message, true));
    } finally {
        $conn->close();
    }
}

function chefIfUserIsInfluencer($userIdOrReferralCode, $conn)
{
    $response = null;

    $sql = "SELECT * FROM Influencers WHERE userId = ? OR referralCode = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $userIdOrReferralCode, $userIdOrReferralCode);  

    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        $isActive = $row['isActive'];
        $influencerDataJson = $row['influencerData'];

        $influencerData = parseInfluencerData($influencerDataJson);

        $response = [
            "isInfluencer" => true,
            "isActive" => $isActive ===1 ? true : false,
            "data" => $influencerData,
            "message" => "Influencer found!"
        ];
    } else {
        $response = [
            "isInfluencer" => null,
            "isActive" => null,
            "data" => null,
            "message" => "No data found for this id"
        ];
    }

    return $response;
}

function parseInfluencerData($influencerDataJson)
{
    return json_decode($influencerDataJson, true);
}

function returnResponse($response)
{
    echo json_encode($response, JSON_PRETTY_PRINT);
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
