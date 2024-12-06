<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

function decryptData($encryptedData, $iv) {
    require_once '../api/tasks/Config.php';
    $SECRET_KEY = LONY_EXTRA_POINTS_SECRET_KEY;
    $metodo = "AES-256-CBC";
    $iv = base64_decode($iv);
    $encryptedData = base64_decode($encryptedData);

    $decryptedData = openssl_decrypt($encryptedData, $metodo, $SECRET_KEY, 0, $iv);
    
    if ($decryptedData === false) {
        throw new Exception("Falha ao desencriptar os dados.");
    }

    return json_decode($decryptedData, true);
}

if (isset($_GET['data']) && isset($_GET['iv'])) {
    try {
        $encryptedData = $_GET['data'];
        $iv = $_GET['iv'];

        $decryptedData = decryptData($encryptedData, $iv);
        $userEmail = $decryptedData['email'];
        $expiryTime = $decryptedData['expiryTime'];

        $currentTime = time();

        if ($currentTime > $expiryTime) {
            header('Location: expired-link.html');
            exit;
        } else {
            $resetToken = base64_encode(json_encode(['email' => $userEmail, 'expiryTime' => $expiryTime]));
            header("Location: updatePassword.html?token=" . urlencode($resetToken));
            exit;
        }
    } catch (Exception $e) {
        header('Location: expired-link.html');
        exit;
    }
} else {
    header('Location: expired-link.html');
    exit;
}
?>
