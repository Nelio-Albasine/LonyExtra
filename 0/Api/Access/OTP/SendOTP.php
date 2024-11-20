<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '../../Logs/send_OTP.log');

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
    $conn = getMySQLConnection();
    createTableUserIfNotExists($conn);

    $data = json_decode(file_get_contents('php://input'), true);

    $requiredFields = ['userId', 'name', 'surname', 'email', 'password', 'gender', 'age'];
    $missingFields = array_filter($requiredFields, function ($field) use ($data) {
        return empty($data[$field]);
    });

    if ($missingFields) {
        echo json_encode(["success" => false, "message" => "Campos obrigatórios faltando: " . implode(', ', $missingFields)]);
        exit;
    }

    $output = [
        'success' => false,
        'message' => null
    ];

    require_once "../IsUserRegistered.php";
    if (IsUserRegistered($data['email'], $conn)) {
        $output = [
            'success' => false,
            'message' => "Este email já está em uso!"
        ];
    } else {
        if (sendOTP($conn, $data)) {
            $output = [
                'success' => true,
                'message' => "OTP enviado com sucesso para o email!",
                'redirectTo' => "http://127.0.0.1:5500/0/Access/confirme-seu-email.html?data=" . urlencode(json_encode($data))
            ];            
        } else {
            $output = [
                'success' => false,
                'message' => "Ocorreu um erro ao enviar o OTP. Tente novamente!"
            ];
        }
    }

    echo json_encode($output, JSON_PRETTY_PRINT);

    exit;
} else {
    error_log("A requisição não é POST");
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Metodo nao permitido"]);
    exit;
}


function createTableUserIfNotExists($conn)
{
    $queryCreateUser = "CREATE TABLE IF NOT EXISTS Usuarios (
        id INT AUTO_INCREMENT PRIMARY KEY,
        userId VARCHAR(191) NOT NULL UNIQUE,
        userName VARCHAR(15) NOT NULL,
        userSurname VARCHAR(20) NOT NULL,
        userEmail VARCHAR(100) NOT NULL UNIQUE,
        userPassword VARCHAR(255),
        myReferralCode VARCHAR(6),
        userGender VARCHAR(10) NOT NULL,
        userAge INT,
        userJoinedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        userPointsJSON JSON,
        userInvitationJSON JSON
    )";

    if (!mysqli_query($conn, $queryCreateUser)) {
        die("Erro ao criar a tabela: " . mysqli_error($conn));
    }
}

function createOtpTable($conn)
{
    $query = "
        CREATE TABLE IF NOT EXISTS user_otps (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(100) NOT NULL,
            otp VARCHAR(6) NOT NULL,
            expires_at DATETIME NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE(email)
        );
    ";

    if ($conn->query($query) === TRUE) {
        // echo "Tabela 'user_otps' criada com sucesso.";
    } else {
        // echo "Erro ao criar a tabela 'user_otps': " . $conn->error;
    }
}


function sendOTP($conn, $data)
{
    createOtpTable($conn);

    $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT); // Gera um código OTP de 6 dígitos
    $email = $data['email'];

    $query = "INSERT INTO user_otps (email, otp, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 5 MINUTE)) ON DUPLICATE KEY UPDATE otp = VALUES(otp), expires_at = VALUES(expires_at)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $email, $otp);

    if ($stmt->execute()) {
        $subject = "Seu código de verificação";
        $message = "Seu código de verificação é: $otp. Ele expira em 5 minutos.";
        $headers = "From: no-reply@seu-dominio.com\r\n";

        error_log($message);
        /* if (mail($email, $subject, $message, $headers)) {
            return true;
        } */

        return true;
    }

    return false;
}

function sendOTPtoEmail() {}
