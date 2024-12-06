<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../logs/SendURLToResetPassword.log');

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

    $email = $data['email'];

    $output = [
        'success' => null,
        'message' => null,
    ];

    require_once "../IsUserRegistered.php";

    if (IsUserRegistered($email, $conn)) {
        if (sendEmailWithURLtoResetPassword($email, $conn)) {
            $output = [
                "success" => true,
                "message" => null,
                "redirectTo" => "http://127.0.0.1:5500/0/access/reset-url-sent.html"
            ];
        } else {
            $output = [
                "success" => false,
                "message" => "Ocorreu um erro ao enviar o e-mail de redefinição.",
            ];
        }
    } else {
        $output = [
            "success" => false,
            "message" => "Email não registrado!",
        ];
    }

    error_log("Resposta da redefinicao da senha: ". print_r($output, true));
    echo json_encode($output);
    $conn->close();
    exit;
} else {
    error_log("A requisição não é POST");
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Método não permitido"]);
    exit;
}


function getUserNameAndSurname($conn, $userEmail)
{
    $userName = null;
    $userSurname = null;

    $sql = "SELECT userName, userSurname FROM Usuarios WHERE userEmail = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $userEmail);
        $stmt->execute();
        $stmt->bind_result($userName, $userSurname);
        if ($stmt->fetch()) {
            return "$userName $userSurname";
        } else {
            $stmt->close();
            return null;
        }
    } else {
        $stmt->close();
        return null;
    }
}

function encryptData($userEmail)
{
    require_once '../../tasks/Config.php';

    $SECRET_KEY = LONY_EXTRA_POINTS_SECRET_KEY;
    $metodo = "AES-256-CBC";
    $iv_length = openssl_cipher_iv_length($metodo); 
    $iv = openssl_random_pseudo_bytes($iv_length);

    $timestamp = time();
    $expiryTime = $timestamp + 60 * 60; 

    $dataToEncrypt = json_encode([
        'email' => $userEmail,
        'expiryTime' => $expiryTime
    ]);

    $dados_encriptados = openssl_encrypt($dataToEncrypt, $metodo, $SECRET_KEY, 0, $iv);

    if ($dados_encriptados === false) {
        throw new Exception("Falha ao encriptar os dados.");
    }

    $response = [
        'encryptedData' => base64_encode($dados_encriptados),
        'iv' => base64_encode($iv)
    ];

    return $response;
}


function sendEmailWithURLtoResetPassword($userEmail, $conn)
{
    $userName = getUserNameAndSurname($conn, $userEmail);

    //generate encripted that expire in 15 min
    $encryptedData = encryptData($userEmail);

    //reset password url 
    $resetURL = "http://127.0.0.1:5500/0/access/update-password.php?data=" . $encryptedData["encryptedData"] . "&iv=" . $encryptedData["iv"];

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
        $mail->SMTPDebug = 0;
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = 'ssl';
        $mail->Host = "mail.lonyextra.com";
        $mail->Port = 465; // ou 587
        $mail->IsHTML(true);
        $mail->Username = "noreply-access@lonyextra.com";
        $mail->Password = "alfa1vsomega2M@";
        $mail->SetFrom("noreply-access@lonyextra.com", "Lony Extra");
        $mail->CharSet = 'UTF-8';
        $mail->Subject = "Pedido de Redefinição de Senha";

        $mail->Body = '
               <!DOCTYPE html>
                <html lang="pt-br">
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>Redefinição de Senha</title>
                    <style>
                        * {
                            margin: 0;
                            padding: 0;
                            box-sizing: border-box;
                        }

                        body {
                            font-family: \'Arial\', sans-serif;
                            background-color: #eeeeee;
                            color: #333;
                            line-height: 1;
                        }

                        div{
                            text-align: center;
                            width: 100%;
                        }

                        table {
                            width: 100%;
                            border-spacing: 0;
                            border-collapse: collapse;
                        }

                        .main-table {
                            width: 100%;
                            max-width: 600px;
                            margin: 0 auto;
                            min-height: 100vh;
                            background-color: white;
                        }

                        .content {
                            width: 100%;
                            background-color: white;
                            padding: 20px;
                            border-radius: 10px;
                        }

                        .content-inner {
                            width: 100%;
                            text-align: center;
                            padding: 8px;
                        }

                        .content img {
                            width: 100%;
                            max-width: 200px;
                            margin: 15px auto 10px auto;
                            display: block;
                        }

                        .content p {
                            font-size: 13px;
                            color: #555;
                            margin: 0;
                        }

                        #userName {
                            font-size: 16px;
                            margin-bottom: 0;
                        }

                        .content strong {
                            color: #1e90ff;
                        }

                        #btnResetPassword {
                            background-color: #056d1f;
                            color: white;
                            font-size: 14px;
                            padding: 13px 40px;
                            border: none;
                            border-radius: 5px;
                            cursor: pointer;
                            text-transform: uppercase;
                            margin: 20px 0;
                            display: inline-block;
                            text-decoration: none;
                        }

                        #btnResetPassword:hover {
                            background-color: #0056b3;
                        }

                        #p_if_u_did_not_initiate_the_request {
                            font-size: 12.8px;
                            color: #888;
                        }

                        #p_if_u_did_not_initiate_the_request a {
                            color: #1e90ff;
                            text-decoration: none;
                        }

                        #p_if_u_did_not_initiate_the_request a:hover {
                            text-decoration: underline;
                        }

                        .footer {
                            text-align: center;
                            margin-top: 30px;
                        }

                        .footer p {
                            font-size: 12px;
                            color: #888;
                            margin: 4px 0;
                        }

                        .footer .our_social_networks a {
                            color: #1e90ff;
                            text-decoration: none;
                            margin: 0 10px;
                            font-size: 14px;
                        }

                        .footer .our_social_networks a:hover {
                            text-decoration: underline;
                        }

                        /* Responsividade */
                        @media only screen and (max-width: 600px) {
                            body {
                                    background-color: white;
                            }

                            .content {
                                padding: 20px;
                            }

                             .main-table {
                                padding: 0;
                            }

                            #btnResetPassword {
                                width: 100%;
                                padding: 12px 0;
                                font-size: 12.5px;
                            }

                            .footer p {
                                font-size: 10.3px;
                            }

                            .footer .our_social_networks a {
                                font-size: 10.5px;
                            }
                        }
                    </style>
                </head>

                <body>
                    <table class="main-table" role="presentation">
                        <tr>
                            <td>
                                <table class="content" role="presentation" align="center">
                                    <tr>
                                        <td>
                                            <div class="content-inner">
                                                <img class="main_logo" src="http://localhost/LonyExtra/0/src/imgs/lonyextra_croped.png"
                                                    alt="Logo da Lony Extra">
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="content-inner">
                                                <p id="userName">Olá <strong>'.$userName.'</strong></p>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="content-inner">
                                                <p id="p_a_request_has_been_received">Recebemos uma solicitação para alterar a senha da sua conta Lony Extra.</p>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="content-inner">
                                                <a id="btnResetPassword" href="'.$resetURL.'" target="_blank">Redefinir Senha</a>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="content-inner">
                                                <p id="p_if_u_did_not_initiate_the_request">Se você não fez esta solicitação, entre em contato conosco imediatamente em <a href="mailto:support@lonyextra.com">support@lonyextra.com</a></p>
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <table class="footer" role="presentation" width="100%">
                                    <tr>
                                        <td>
                                            <div class="footer_mini_logo">
                                                <p>Enviado com Confiança <br>
                                                Válido por <strong>10 minutos</strong></p>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <p>@Lony Extra 2024 - Todos os Direitos Reservados</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="our_social_networks">
                                                <a href="https://www.instagram.com/lonyextra?igsh=YzljYTk1ODg3Zg==" target="_blank">Instagram</a>
                                                <a href="https://t.me/LonyExtra" target="_blank">Telegram</a>
                                                <a href="https://youtube.com/@lonyextra?si=9JJXuTr3y9YVpMxb" target="_blank">YouTube</a>
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </body>

                </html>

                  ';

        $mail->AddAddress($userEmail);

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
