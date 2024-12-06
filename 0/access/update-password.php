<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');


function decryptData($encryptedData, $iv) {
    require_once '../api/tasks/Config.php';
    $SECRET_KEY = LONY_EXTRA_POINTS_SECRET_KEY;
    $metodo = "AES-256-CBC";
    $iv = base64_decode($iv);
    $encryptedData = base64_decode($encryptedData);

    $decryptedData = openssl_decrypt($encryptedData, $metodo, $SECRET_KEY, 0, $iv);
    
    if ($decryptedData === false) {
        throw new Exception("Falha ao desencriptar os dados.");
    }

    return json_decode($decryptedData, true);
}


if (isset($_GET['data']) && isset($_GET['iv'])) {
    try {
        $encryptedData = $_GET['data'];
        $iv = $_GET['iv'];

       
        $decryptedData = decryptData($encryptedData, $iv);
        $userEmail = $decryptedData['email'];
        $expiryTime = $decryptedData['expiryTime'];

        $currentTime = time();

        if ($currentTime > $expiryTime) {
            header('Location: expired-link.html');
            exit;
        } else {
            // Caso o link não tenha expirado, cria o token de redefinição
            $resetToken = base64_encode(json_encode(['email' => $userEmail, 'expiryTime' => $expiryTime]));
        }
    } catch (Exception $e) {
        header('Location: expired-link.html');
        exit;
    }
} else {
    header('Location: expired-link.html');
    exit;
}

// Processamento do formulário de redefinição de senha
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password']) && isset($_POST['confirmPassword']) && isset($_POST['resetToken'])) {
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];
    $resetToken = $_POST['resetToken'];

    if ($password === $confirmPassword) {
        $resetData = json_decode(base64_decode($resetToken), true);
        $userEmail = $resetData['email'];

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        require_once '../api/Wamp64Connection.php';
        $conn = Wamp64Connection();

        $stmt = $conn->prepare("UPDATE Usuarios SET userPassword = ? WHERE userEmail = ?");
        $stmt->bind_param('ss', $hashedPassword, $userEmail);

        if ($stmt->execute()) {
            echo "<script>
                showAlert(0, 'Senha atualizada com sucesso! Redirecionando para o login...');
                setTimeout(() => {
                    window.location.href = 'http://127.0.0.1:5500/0/access/login.html';
                }, 3000);
            </script>";
        } else {
            echo "<script>showAlert(2, 'Erro ao atualizar a senha. Tente novamente mais tarde.');</script>";
        }

        $stmt->close();
        $conn->close();
    } else {
        echo "<script>showAlert(2, 'As senhas não coincidem!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redefinir Senha</title>
    <link rel="stylesheet" href="../css/access/update_password.css">

</head>
<body>
    <div class="container">
        <h2>Redefinir Senha</h2>
        
        <form method="POST" action="">
            <input type="hidden" name="resetToken" value="<?php echo isset($resetToken) ? $resetToken : ''; ?>">

            <label for="password">Nova Senha:</label>
            <input type="password" id="password" name="password" required>

            <label for="confirmPassword">Confirmar Nova Senha:</label>
            <input type="password" id="confirmPassword" name="confirmPassword" required>

            <button type="submit">Redefinir Senha</button>
        </form>
    </div>
</body>
</html>
