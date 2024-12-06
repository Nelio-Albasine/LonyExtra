<?php
// Exibir erros para depuração (remova em produção)
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('error_log', __DIR__ . '/../api/logs/UpdateUserPassword.log');

require_once "../api/Wamp64Connection.php";
require_once "../api/access/IsUserRegistered.php";

if (isset($_GET['data']) && isset($_GET['iv'])) {
    $encryptedData = base64_decode($_GET['data']);
    $iv = base64_decode($_GET['iv']);

    // Validar o tamanho do IV (16 bytes para AES-256-CBC)
    if (strlen($iv) !== 16) {
        die("IV inválido. Por favor, solicite um novo link.");
    }

    require_once '../api/tasks/Config.php';
    $SECRET_KEY = LONY_EXTRA_POINTS_SECRET_KEY;
    $metodo = 'AES-256-CBC';

    // Descriptografar os dados
    $decryptedData = openssl_decrypt($encryptedData, $metodo, $SECRET_KEY, 0, $iv);

    if ($decryptedData === false) {
        die("Falha ao descriptografar os dados. Solicite um novo link.");
    }

    $data = json_decode($decryptedData, true);

    if (!isset($data['email']) || !isset($data['expiryTime'])) {
        die("Dados inválidos ou corrompidos. Solicite um novo link.");
    }

    if (time() > $data['expiryTime']) {
        header("Refresh: 3; url=http://127.0.0.1:5500/0/access/expired-link.html");
        die("O link expirou. Você será redirecionado para solicitar um novo link.");
    }

    $email = $data['email'];

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
                        // Redireciona imediatamente para a página de login
                        header("Location: http://127.0.0.1:5500/0/access/login.html");
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
    <link rel="icon" href="../src/favicon_io/favicon.ico" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <style>
        body, h2, p, form, input, button {
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f7f7f7;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            width: 100%;
            max-width: 450px;
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 30px;
            box-sizing: border-box;
            text-align: center;
        }

        h2 {
            font-size: 1.8rem;
            color: #333;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
            text-align: left;
        }

        label {
            font-weight: 500;
            color: #555;
            display: block;
            margin-bottom: 5px;
        }

        input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        input:focus {
            border-color: #66afe9;
            outline: none;
        }

        button {
            width: 100%;
            padding: 12px;
            border: none;
            background-color: #4CAF50;
            color: white;
            border-radius: 8px;
            font-size: 1.2rem;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #45a049;
        }

        .message {
            font-size: 1.1rem;
            margin-bottom: 20px;
            padding: 10px;
            border-radius: 8px;
            color: white;
        }

        .error {
            background-color: #f44336;
        }

        .success {
            background-color: #4caf50;
        }

        #loading {
            display: none;
            background-color: #ff9800;
            color: white;
            padding: 10px;
            margin-top: 20px;
            border-radius: 5px;
        }

        @media (max-width: 600px) {
            .container {
                padding: 20px;
            }

            h2 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Redefinir Senha</h2>
        <?php if (isset($mensagemErro)) echo "<p class='message error'>$mensagemErro</p>"; ?>
        <?php if (isset($mensagemSucesso)) echo "<p class='message success'>$mensagemSucesso</p>"; ?>
        <form method="post" onsubmit="showLoadingMessage()">
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

        <!-- Mensagem de carregamento -->
        <div id="loading">Aguarde... Estamos verificando seus dados.</div>
    </div>

    <script>
        function showLoadingMessage() {
            document.getElementById('loading').style.display = 'block'; // Exibe a mensagem de carregamento
        }
    </script>
</body>

</html>
