<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('error_log', __DIR__ . '/../../logs/verify_OTP.log');

// Definindo os cabeçalhos CORS diretamente 
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');


if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}


require_once "../../Wamp64Connection.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = Wamp64Connection();
    $data = json_decode(file_get_contents('php://input'), true);

    $requiredFields = ['userId', 'name', 'surname', 'email', 'password', 'gender', 'age'];
    $missingFields = array_filter($requiredFields, fn($field) => empty($data[$field]));

    if (!empty($missingFields)) {
        echo json_encode(["success" => false, "message" => "Campos obrigatórios faltando: " . implode(', ', $missingFields)]);
        exit;
    }

    $output = ['success' => false, 'message' => null];
    $verifyOTPResponse = verifyOTP($conn, $data['email'], $data['otp']);

    try {
        if ($verifyOTPResponse['success']) {
            require_once '../SignUp.php';

            if (createNewUser($conn, $data)) {
                $output = [
                    'success' => true,
                    'message' => "Parabéns, sua conta foi criada com sucesso!",
                    'redirectTo' => "http://127.0.0.1:5500/0/dashboard"
                ];
            } else {
                $output = [
                    'success' => false,
                    'message' => "Ocorreu um erro ao criar a conta, tente novamente!"
                ];
            }
        } else {
            $output = [
                'success' => false,
                'message' => $verifyOTPResponse['message']
            ];
        }
    } catch (\Throwable $th) {
        error_log("Ocorreu um erro ao verificar o OTP! O erro: " . $th->getMessage());
    } finally {
        error_log("Resposta a da veridicacao do OTP e criacao do user : " . print_r($output, true));
        echo json_encode($output);
        $conn = null;
        exit;
    }
} else {
    error_log("A requisição não é POST");
    http_response_code(405);
    die(json_encode(['success' => false, 'message' => 'Método não permitido']));
}

function verifyOTP($conn, $email, $otp)
{
    $dbOtp = null;
    $expiresAt = "";

    $query = "SELECT otp, expires_at FROM user_otps WHERE email = ? LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($dbOtp, $expiresAt);
        $stmt->fetch();

        if ($otp === $dbOtp) {
            if (new DateTime() < new DateTime($expiresAt)) {
                return ['success' => true];
            } else {
                return ['success' => false, 'message' => "OTP expirado."];
            }
        } else {
            return ['success' => false, 'message' => "OTP incorreto."];
        }
    } else {
        return ['success' => false, 'message' => "OTP não encontrado para este e-mail."];
    }
}
