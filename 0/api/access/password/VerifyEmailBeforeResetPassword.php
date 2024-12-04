<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../logs/password_reset_OTP.log');

// Definindo os cabeçalhos CORS diretamente 
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

header('Content-Type: application/json; charset=utf-8');

require_once "../../Wamp64Connection.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn = Wamp64Connection();

    $data = json_decode(file_get_contents('php://input'), true);

    if (isset($data['email'], $data['code'])) {
        $email = $data['email'];
        $code = $data['code'];

        if (checkIfOTPtoResetPasswordIsValid($conn, $email, $code)) {
            $output = [
                'success' => true,
                'redirectTo' => "http://127.0.0.1:5500/0/access/update-password.html?email=" . $email,
                'message' => 'Código OTP válido.'
            ];
        } else {
            $output = [
                'success' => false,
                'message' => 'Código OTP inválido ou expirado.'
            ];
        }
    } else {
        $output = [
            'success' => false,
            'message' => 'Dados incompletos. É necessário fornecer o e-mail e o código.'
        ];
    }

    echo json_encode($output);
    $conn->close();
} else {
    error_log("A requisição não é POST");
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Método não permitido"]);
    exit;
}

function checkIfOTPtoResetPasswordIsValid($conn, $email, $code)
{
    $stmt = $conn->prepare("SELECT * FROM password_reset_requests WHERE email = ? AND otp_code = ? AND is_active = 1 AND expiry_date > NOW()");
    $stmt->bind_param("ss", $email, $code);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $updateStmt = $conn->prepare("UPDATE password_reset_requests SET is_active = 0 WHERE email = ? AND otp_code = ?");
        $updateStmt->bind_param("ss", $email, $code);
        $updateStmt->execute();

        return true;
    }

    return false;
}
