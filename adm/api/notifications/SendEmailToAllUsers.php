<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('error_log', __DIR__ . '/../../logs/send_mass_emails.log');

// Definindo os cabeçalhos CORS diretamente 
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once "../../DBConnection.php";

$conn = Wamp64Connection();

/**
 * Pega e-mails e nomes dos usuários em lotes de 500 para envio controlado.
 * @param mysqli $conn
 * @param int &$cursor
 * @return array
 */
function getUsersEmailsAnNameBatch($conn, &$cursor = 0): array
{
    $limitPerBatch = 500;

    // Alterando a consulta para usar parâmetros posicionais com mysqli
    $query = "SELECT userEmail, CONCAT(userName, ' ', userSurname) AS fullName 
               FROM Usuarios 
               WHERE id > ? 
               ORDER BY id ASC 
               LIMIT ?";

    // Prepara a consulta
    $stmt = $conn->prepare($query);

    // Associa os parâmetros com bind_param (com mysqli)
    $stmt->bind_param('ii', $cursor, $limitPerBatch); // 'ii' para dois inteiros

    // Executa a consulta
    $stmt->execute();

    // Obtém os resultados
    $result = $stmt->get_result();
    $users = $result->fetch_all(MYSQLI_ASSOC);

    // Atualiza o cursor com o maior ID retornado
    if (!empty($users)) {
        $cursor = $cursor + count($users);
    }

    return $users;
}

/**
 * Envia um e-mail individual usando PHPMailer
 * @param string $to_user_name
 * @param string $to_user_email
 * @return bool
 */
function sendEmailForEachUser($to_user_name, $to_user_email)
{
    try {
        require_once "../../../../PHPMailer-master/PHPMailer-master/src/PHPMailer.php";
        require_once "../../../../PHPMailer-master/PHPMailer-master/src/SMTP.php";
        require_once "../../../../PHPMailer-master/PHPMailer-master/src/Exception.php";

        $mail = new PHPMailer\PHPMailer\PHPMailer();
        $mail->IsSMTP();
        $mail->SMTPDebug = 0;
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = 'ssl';
        $mail->Host = "mail.lonyextra.com";
        $mail->Port = 465; 
        $mail->IsHTML(true);
        $mail->Username = "noreply-access@lonyextra.com";
        $mail->Password = "alfa1vsomega2M@";
        $mail->SetFrom("noreply-access@lonyextra.com", "Lony Extra");
        $mail->CharSet = 'UTF-8';
        $mail->Subject = "Aviso Importante: Migração e Novidades!";

        // Template do e-mail
        $body = '<!DOCTYPE html>
                <html lang="pt-BR">
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>Migração do Site</title>
                    <style>
                        body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0; }
                        .container { background-color: #fff; margin: 20px auto; padding: 20px; max-width: 600px; border-radius: 8px; }
                        .header { background-color: #4CAF50; color: #fff; padding: 10px; text-align: center; }
                        .content { color: #333; font-size: 16px; line-height: 1.5; }
                        .footer { text-align: center; font-size: 12px; color: #777; margin-top: 20px; }
                        a { color: #4CAF50; text-decoration: none; font-weight: bold; }
                    </style>
                </head>
                <body>
                    <div class="container">
                        <div class="header">
                            <h2>Nosso Site Está Migrando!</h2>
                        </div>
                        <div class="content">
                            <p>Olá, <strong>' . htmlspecialchars($to_user_name) . '</strong>!</p>
                            <p>Estamos migrando nosso site para um novo servidor. Seu progresso será salvo e traremos novidades:</p>
                            <ul>
                                <li>Novos limites de saque: de <strong>$1.12</strong> para <strong>$0.5</strong>!</li>
                                <li>Melhorias de desempenho e novas funcionalidades.</li>
                            </ul>
                            <p>Acesse nosso grupo no Telegram para mais informações:</p>
                            <p style="text-align: center;"><a href="https://t.me/LonyExtra">Entrar no Telegram</a></p>
                        </div>
                        <div class="footer">
                            <p>&copy; 2024 LonyExtra. Todos os direitos reservados.</p>
                        </div>
                    </div>
                </body>
                </html>';
        
        $mail->Body = $body;
        $mail->AddAddress($to_user_email);

        return $mail->send();
    } catch (Exception $e) {
        error_log("Erro ao enviar e-mail: " . $e->getMessage());
        return false;
    }
}

/**
 * Envia e-mails em lotes com intervalo de tempo.
 */
function sendEmailsInBatches($conn)
{
    $cursor = 0;

    do {
        $usersBatch = getUsersEmailsAnNameBatch($conn, $cursor);
        
        if (empty($usersBatch)) {
            echo "Todos os e-mails foram enviados!\n";
            break;
        }

        foreach ($usersBatch as $user) {
            $name = $user['fullName'];
            $email = $user['userEmail'];

            echo "Enviando e-mail para: $name ($email)\n";
            
            if (sendEmailForEachUser($name, $email)) {
                echo "E-mail enviado com sucesso!\n";
            } else {
                echo "Falha ao enviar e-mail para $email.\n";
            }

            // Intervalo entre e-mails para evitar bloqueio (ajuste conforme necessário)
            sleep(2); // 2 segundos por e-mail
        }

        // Intervalo entre lotes para não sobrecarregar o servidor
        echo "Aguardando 10 segundos antes de iniciar o próximo lote...\n";
        sleep(10);

    } while (!empty($usersBatch));
}

sendEmailsInBatches($conn);
?>
