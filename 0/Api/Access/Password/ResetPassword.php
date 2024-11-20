<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '../../Logs/password_reset_OTP.log');

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
    $conn = getMySQLConnection();
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (isset($data['email'], $data['password'])) {
        $email = $data['email'];
        $newPassword = password_hash($data['password'], PASSWORD_BCRYPT); // Hashear a nova senha

        if (resetPassword($conn, $email, $newPassword)) {
            $output = [
                'success' => true,
                'message' => "Senha redefinida com sucesso."
            ];
        } else {
            $output = [
                'success' => false,
                'message' => "Falha ao redefinir a senha. Tente novamente."
            ];
        }
    } else {
        $output = [
            'success' => false,
            'message' => "Dados incompletos. Email e senha são obrigatórios."
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

function resetPassword($conn, $email, $newPassword) {
    // Verifica se há uma solicitação ativa para este email na tabela 'password_reset_requests'
    $checkOtpQuery = "SELECT * FROM password_reset_requests WHERE email = ? AND is_active = 0 ORDER BY expires_at DESC LIMIT 1";
    $stmt = $conn->prepare($checkOtpQuery);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Atualiza a senha do usuário na tabela 'Usuarios'
        $updateQuery = "UPDATE Usuarios SET userPassword = ? WHERE userEmail = ?";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param("ss", $newPassword, $email);

        if ($updateStmt->execute()) {
            // Opcional: remove a solicitação de OTP da tabela 'password_reset_requests' para maior segurança
            $deleteOtpQuery = "DELETE FROM password_reset_requests WHERE email = ?";
            $deleteStmt = $conn->prepare($deleteOtpQuery);
            $deleteStmt->bind_param("s", $email);
            $deleteStmt->execute();
            
            return true; 
        }
    }

    return false; // Redefinição de senha falhou
}
