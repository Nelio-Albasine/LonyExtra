<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('error_log', __DIR__ . '/../../logs/send_OTP.log');

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

    try {
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
                    'redirectTo' => "http://127.0.0.1:5500/0/access/confirme-seu-email.html?data=" . urlencode(json_encode($data))
                ];
            } else {
                $output = [
                    'success' => false,
                    'message' => "Ocorreu um erro ao enviar o OTP. Tente novamente!"
                ];
            }
        }
    } catch (\Throwable $th) {
        error_log("Ocorreu um erro ao enviar o OTP! O erro: " . $th->getMessage());
    } finally {
        echo json_encode($output, JSON_PRETTY_PRINT);
        $conn->close();
        exit;
    }
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
        userTimeZone VARCHAR(50),
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

    $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    $email = $data['email'];
    $name = $data['name'];
    $surname = $data['surname'];
    $userName = "$name $surname";

    $query = "INSERT INTO user_otps (email, otp, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 5 MINUTE)) ON DUPLICATE KEY UPDATE otp = VALUES(otp), expires_at = VALUES(expires_at)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $email, $otp);

    if (sendOTPtoEmail($userName, $email, $otp)) {
        return true;
    }

    return false;
}

function sendOTPtoEmail($to_user_name, $to_user_email, $otp)
{
    try {
        $phpMailerPath = "../../../../PHPMailer-master/PHPMailer-master/src/PHPMailer.php";
        $smtpPath = "../../../../PHPMailer-master/PHPMailer-master/src/SMTP.php";
        $exceptionPath = "../../../../PHPMailer-master/PHPMailer-master/src/Exception.php";

        if (file_exists($phpMailerPath) && file_exists($smtpPath) && file_exists($exceptionPath)) {
            require($phpMailerPath);
            require($smtpPath);
            require($exceptionPath);
        } else {
            error_log("Erro: Um ou mais arquivos PHPMailer-master necessários não foram encontrados.");
            die;
        }

        $mail = new PHPMailer\PHPMailer\PHPMailer();
        $mail->IsSMTP();
        $mail->SMTPDebug = 1;
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = 'ssl';
        $mail->Host = "mail.lonyextra.com";
        $mail->Port = 465; // ou 587
        $mail->IsHTML(true);
        $mail->Username = "noreply-access@lonyextra.com";
        $mail->Password = "alfa1vsomega2M@";
        $mail->SetFrom("noreply-access@lonyextra.com", "Lony Extra");
        $mail->CharSet = 'UTF-8';
        $mail->Subject = "Confirme Seu Cadastro";

        $mail->Body = '<!DOCTYPE html>
                        <html lang="pt-BR">
                        <head>
                            <meta charset="UTF-8">
                            <meta name="viewport" content="width=device-width, initial-scale=1.0">
                            <title>Código de Verificação</title>
                            <style>
                                body {
                                    font-family: Arial, sans-serif;
                                    background-color: #f4f4f4;
                                    margin: 0;
                                    padding: 0;
                                }

                                .email-container {
                                    max-width: 600px;
                                    margin: 20px auto;
                                    background-color: #ffffff;
                                    border-radius: 8px;
                                    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                                    padding: 20px;
                                    overflow: hidden;
                                }

                                .header {
                                    text-align: center;
                                    padding: 10px 0;
                                    background-color: #4A90E2;
                                    color: #ffffff;
                                    border-top-left-radius: 8px;
                                    border-top-right-radius: 8px;
                                }

                                .header h1 {
                                    font-size: 24px;
                                    margin: 0;
                                }

                                .content {
                                    text-align: center;
                                    padding: 20px;
                                }

                                .content p {
                                    font-size: 16px;
                                    color: #333333;
                                    margin-bottom: 20px;
                                }

                                .otp-code {
                                    display: inline-block;
                                    font-size: 32px;
                                    font-weight: bold;
                                    color: #4A90E2;
                                    background-color: #f1f1f1;
                                    padding: 10px 20px;
                                    border-radius: 4px;
                                    letter-spacing: 4px;
                                }

                                .footer {
                                    text-align: center;
                                    padding: 10px;
                                    font-size: 12px;
                                    color: #888888;
                                }

                                .footer a {
                                    color: #4A90E2;
                                    text-decoration: none;
                                }
                            </style>
                        </head>

                        <body>
                            <div class="email-container">
                                <div class="header">
                                    <h1>Código de Verificação</h1>
                                </div>

                                <div class="content">
                                    <p>Olá, <strong>' . htmlspecialchars($to_user_name) . '</strong>!</p>
                                    <p>Use o código abaixo para confirmar seu cadastro:</p>
                                    <div class="otp-code">' . htmlspecialchars($otp) . '</div>
                                    <p>Este código é válido por 5 minutos.</p>
                                </div>

                                <div class="footer">
                                    <p>Se você não solicitou este código, ignore este e-mail.</p>
                                    <p>© 2024 LonyExtra. Todos os direitos reservados.</p>
                                </div>
                            </div>
                        </body>

                        </html>
                  ';

        $mail->AddAddress($to_user_email);

        try {
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Erro ao enviar email: " . $mail->ErrorInfo);
            return false;
        }
    } catch (\Throwable $th) {
        error_log('Error occurred: ' . $th->getMessage());
        return false;
    }
}
