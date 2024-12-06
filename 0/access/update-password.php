<?php
// Exibir erros para depuração (remova em produção)
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('error_log', __DIR__ . '/0/api/logs/UpdateUserPassword.log');

require_once "/0/api/Wamp64Connection.php";
require_once "/0/api/access/IsUserRegistered.php";

if (isset($_GET['data']) && isset($_GET['iv'])) {
    $encryptedData = base64_decode($_GET['data']);
    $iv = base64_decode($_GET['iv']);

    // Debug: Exibir tamanho e conteúdo do IV
    echo "<pre>";
    echo "IV (base64 decoded): " . bin2hex($iv) . "\n";
    echo "IV Length: " . strlen($iv) . "\n";
    echo "</pre>";

    // Validar o tamanho do IV (16 bytes para AES-256-CBC)
    if (strlen($iv) !== 16) {
        die("IV inválido. Por favor, solicite um novo link.");
    }

    require_once '../api/tasks/Config.php';
    $SECRET_KEY = LONY_EXTRA_POINTS_SECRET_KEY;
    $metodo = 'AES-256-CBC';

    // Descriptografar os dados
    $decryptedData = openssl_decrypt($encryptedData, $metodo, $SECRET_KEY, 0, $iv);

    // Debug: Exibir dados descriptografados
    echo "<pre>";
    echo "Dados descriptografados: " . htmlspecialchars($decryptedData) . "\n";
    echo "</pre>";

    if ($decryptedData === false) {
        die("Falha ao descriptografar os dados. Solicite um novo link.");
    }

    // Converter JSON para array associativo
    $data = json_decode($decryptedData, true);

    if (!isset($data['email']) || !isset($data['expiryTime'])) {
        die("Dados inválidos ou corrompidos. Solicite um novo link.");
    }

    // Validar expiração do link
    if (time() > $data['expiryTime']) {
        header("Refresh: 3; url=http://127.0.0.1:5500/0/access/expired-link.html");
        die("O link expirou. Você será redirecionado para solicitar um novo link.");
    }

    $email = $data['email'];

    // Processar redefinição de senha
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $novaSenha = $_POST['novaSenha'];
        $confirmarSenha = $_POST['confirmarSenha'];
        $conn = Wamp64Connection();

        if ($novaSenha !== $confirmarSenha) {
            $mensagemErro = "As senhas não coincidem.";
        } else {
            if (IsUserRegistered($email, $conn)) {
                $hashSenha = password_hash($novaSenha, PASSWORD_BCRYPT);

                $sql = "UPDATE Usuarios SET userPassword = ? WHERE userEmail = ?";
                if ($stmt = $conn->prepare($sql)) {
                    $stmt->bind_param("ss", $hashSenha, $email);
                    if ($stmt->execute()) {
                        $mensagemSucesso = "Senha redefinida com sucesso!";
                        header("Refresh: 3; url=http://127.0.0.1:5500/0/access/login.html");
                        exit;
                    } else {
                        $mensagemErro = "Erro ao atualizar a senha.";
                    }
                    $stmt->close();
                } else {
                    $mensagemErro = "Erro ao preparar a consulta.";
                }
                $conn->close();
            } else {
                $mensagemErro = "Usuário não registrado!";
            }
        }
    }
} else {
    die("Parâmetros inválidos. Solicite um novo link.");
}
?>


<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redefinir Senha</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
        }

        .container {
            max-width: 400px;
            margin: 50px auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            font-weight: bold;
        }

        input {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            width: 100%;
            padding: 10px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
        }

        button:hover {
            background: #218838;
        }

        .error {
            color: red;
            margin-bottom: 15px;
        }

        .success {
            color: green;
            margin-bottom: 15px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Redefinir Senha</h2>
        <?php if (isset($mensagemErro)) echo "<p class='error'>$mensagemErro</p>"; ?>
        <?php if (isset($mensagemSucesso)) echo "<p class='success'>$mensagemSucesso</p>"; ?>
        <form method="post">
            <div class="form-group">
                <label for="novaSenha">Nova Senha</label>
                <input type="password" id="novaSenha" name="novaSenha" required>
            </div>
            <div class="form-group">
                <label for="confirmarSenha">Confirmar Nova Senha</label>
                <input type="password" id="confirmarSenha" name="confirmarSenha" required>
            </div>
            <button type="submit">Atualizar Senha</button>
        </form>
    </div>
</body>

</html>