<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../logs/password_reset_OTP.log');

// Definindo os cabeçalhos CORS
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

    if (sendOTPToResetPassword($conn, $data)) {
        $output = [
            'success' => true,
            'message' => null
        ];
    } else {
        $output = [
            'success' => false,
            'message' => "Ocorreu um erro ao enviar o OTP. Tente novamente!"
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

// Função para criar a tabela de OTPs com campo 'is_active'
function createPasswordOtpTable($conn)
{
    $query = "
        CREATE TABLE IF NOT EXISTS password_reset_requests (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(100) NOT NULL,
            otp_code VARCHAR(6) NOT NULL,
            expires_at DATETIME NOT NULL,
            is_active TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE(email)
        );
    ";

    if ($conn->query($query) !== TRUE) {
        error_log("Erro ao criar a tabela 'password_reset_requests': " . $conn->error);
    }
}

// Função para enviar o OTP
function sendOTPToResetPassword($conn, $data)
{
    createPasswordOtpTable($conn);

    $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT); // Gera um código OTP de 6 dígitos
    $email = $data['email'];

    // Consulta para inserir ou atualizar OTP e definir 'is_active' como 1
    $query = "
        INSERT INTO password_reset_requests (email, otp_code, expires_at, is_active) 
        VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 5 MINUTE), 1)
        ON DUPLICATE KEY UPDATE otp_code = VALUES(otp_code), expires_at = VALUES(expires_at), is_active = 1
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $email, $otp);

    if ($stmt->execute()) {
        $subject = "Recuperar Senha";
        $message = "Seu código de confirmação é: $otp. Ele expira em 5 minutos.";
        $headers = "From: no-reply@seu-dominio.com\r\n";

        error_log($message);
        
        // Enviar o e-mail (a linha está comentada para evitar envio acidental em ambiente de teste)
        /* if (mail($email, $subject, $message, $headers)) {
            return true;
        } */

        return true;
    }

    return false;
}
