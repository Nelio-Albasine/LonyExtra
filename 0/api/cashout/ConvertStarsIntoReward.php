<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/ConvertStarsIntoReward.log');

require_once "../Wamp64Connection.php";
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once "../Wamp64Connection.php";

class UserPoints {
    public float $userStars;
    public float $userLTStars;
    public float $userRevenue;
    public float $userLTRevenue;

    public function __construct($userStars, $userLTStars, $userRevenue, $userLTRevenue) {
        $this->userStars = $userStars;
        $this->userLTStars = $userLTStars;
        $this->userRevenue = $userRevenue;
        $this->userLTRevenue = $userLTRevenue;
    }
}

class ConversionResponse {
    public bool $success;
    public ?string $message;

    public function __construct($success = false, $message = null) {
        $this->success = $success;
        $this->message = $message;
    }
}

function main() {
    $data = json_decode(file_get_contents('php://input'), true);

    $userId = $data["userId"];
    $index = (int) $data["index"]; 

    $conn = Wamp64Connection();

    try {
        $response = handleConversion($conn, $userId, $index);
        header('Content-Type: application/json');
        echo json_encode($response);
    } catch (Exception $e) {
        echo json_encode([
            "success" => false,
            "message" => "Erro: " . $e->getMessage()
        ]);
    } finally {
        $conn = null; 
    }
}


function handleConversion($conn, $userId, $index) {
    $starsToValueMap = [
        0 => ["stars" => 497, "revenue" => 0.50],
        1 => ["stars" => 1246, "revenue" => 1.30],
        2 => ["stars" => 2874, "revenue" => 3.00],
        3 => ["stars" => 6710, "revenue" => 7.00],
        4 => ["stars" => 19185, "revenue" => 20.00],
        5 => ["stars" => 95200, "revenue" => 100.00],
    ];    

    $response = new ConversionResponse();

    $userPointsJson = checkIfUserHasSufficientStars($conn, $userId);

    if ($userPointsJson) {
        $userPoints = json_decode($userPointsJson);
        $starsRequired = $starsToValueMap[$index]["stars"] ?? null;

        if ($userPoints->userStars >= $starsRequired) {
            $revenueEarned = $starsToValueMap[$index]["revenue"];

            $updatedPoints = new UserPoints(
                $userPoints->userStars - $starsRequired,
                $userPoints->userLTStars,
                $userPoints->userRevenue + $revenueEarned,
                $userPoints->userLTRevenue + $revenueEarned
            );

            $updateSuccess = updateUserPoints($conn, $userId, json_encode($updatedPoints));
            if ($updateSuccess) {
                $response->success = true;
                $response->message = "<strong>✨ $starsRequired</strong> estrelas convertidas com sucesso <br> para <strong>💰 R$ $revenueEarned</strong>";
            } else {
                $response->message = "Erro ao atualizar os dados do usuário.";
            }
        } else {
            $response->message = "405";
        }
    } else {
        $response->message = "Usuário não encontrado ou sem dados suficientes.";
    }

    return $response;
}

function checkIfUserHasSufficientStars($conn, $userId) {
    $query = "SELECT userPointsJSON FROM Usuarios WHERE userId = ?";

    try {
        $stmt = $conn->prepare($query);
        $stmt->bind_param('s', $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            return $row['userPointsJSON'];
        } else {
            return null;
        }
    } catch (Exception $e) {
        throw new Exception("Erro ao buscar dados do usuário: " . $e->getMessage());
    }
}

function updateUserPoints($conn, $userId, $userPointsJson) {
    $query = "UPDATE Usuarios SET userPointsJSON = ? WHERE userId = ?";

    try {
        $stmt = $conn->prepare($query);
        $stmt->bind_param('ss', $userPointsJson, $userId);
        return $stmt->execute();
    } catch (Exception $e) {
        throw new Exception("Erro ao atualizar os pontos do usuário: " . $e->getMessage());
    }
}

main();
