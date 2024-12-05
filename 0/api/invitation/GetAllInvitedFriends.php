<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/GetAllInvitedFriends.log');

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == "GET") {
    require_once "../Wamp64Connection.php";
    $conn = Wamp64Connection();

    $myReferralCode = $_GET['myReferralCode'];

    $sql = "SELECT 
                userName, 
                userSurname, 
                JSON_EXTRACT(userPointsJSON, '$.userLTStars') AS userLTStars, 
                JSON_EXTRACT(userInvitationJSON, '$.myReferralCode') AS myReferralCode 
            FROM usuarios 
            WHERE JSON_EXTRACT(userInvitationJSON, '$.myInviterCode') = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $myReferralCode);
    $stmt->execute();
    $result = $stmt->get_result();

    $usuariosComMeuCodigo = [];

    while ($row = $result->fetch_assoc()) {
        $usuariosComMeuCodigo[] = [
            'userName' => $row['userName'],
            'userSurname' => $row['userSurname'],
            'userLTStars' => (int) $row['userLTStars']
        ];
    }

    // Ordena os usuários por estrelas (userLTStars) em ordem decrescente
    usort($usuariosComMeuCodigo, function ($a, $b) {
        return $b['userLTStars'] - $a['userLTStars'];
    });

    // Resultado final em JSON
    header('Content-Type: application/json');
    echo json_encode($usuariosComMeuCodigo, JSON_PRETTY_PRINT);

    $conn->close();
} else {
    http_response_code(405); 
}
