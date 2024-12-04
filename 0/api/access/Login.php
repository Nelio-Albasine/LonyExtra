<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/login_errors.log');

// Definindo os cabeçalhos CORS diretamente 
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

header('Content-Type: application/json; charset=utf-8');

require_once "../Wamp64Connection.php";

if ($_SERVER['REQUEST_METHOD'] == "GET") {
    $conn = Wamp64Connection();

    createTableUserIfNotExists($conn);
    handleDefaultDashLinksTableCreation($conn);

    $output = [
        "success" => null,
        "message" => null,
        "redirectTo" => null
    ];

    if (!$conn) {
        error_log("Erro ao conectar ao banco de dados");
        $output = [
            "success" => false,
            "message" => "Erro interno no servidor."
        ];
        echo json_encode($output);
        exit;
    }

    $email = $_GET["email"];
    $password = $_GET["password"];

    require_once "IsUserRegistered.php";

    if (IsUserRegistered($email, $conn)) {
        if (authenticateUser($email, $password, $conn)) {
            $output = [
                "success" => true,
                "message" => "Login feito com sucesso!",
                "redirectTo" => "http://127.0.0.1:5500/0/dashboard/",
            ];
        } else {
            $output = [
                "success" => false,
                "message" => "E-mail ou senha incorreto!",
            ];
        }
    } else {
        $output = [
            "success" => false,
            "message" => "Usuário não registrado!",
        ];
    }

    echo json_encode($output);
    $conn->close();
} else {
    error_log("A requisição não é GET");
}
function authenticateUser($email, $passwordInput, $conn): bool
{
    $passwordFromDatabase = null;

    $query = "SELECT userPassword FROM Usuarios WHERE userEmail = ?";
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        error_log("Erro ao preparar a consulta: " . $conn->error);
        return false;
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($passwordFromDatabase);
    $stmt->fetch();
    $stmt->close();

    return $passwordFromDatabase && password_verify($passwordInput, $passwordFromDatabase);
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
function handleDefaultDashLinksTableCreation($conn) {
    $lotes = [
        'loteBronze' => json_encode([
            'link_1' => 'https://alpharede.com/Bronze_Tarefa_1',
            'link_2' => 'https://alpharede.com/Bronze_Tarefa_2',
            'link_3' => 'https://alpharede.com/Bronze_Tarefa_3',
            'link_4' => 'https://alpharede.com/Bronze_Tarefa_4',
            'link_5' => 'https://alpharede.com/Bronze_Tarefa_5',
            'link_6' => 'https://alpharede.com/Bronze_Tarefa_6',
            'link_7' => 'https://alpharede.com/Bronze_Tarefa_7',
            'link_8' => 'https://alpharede.com/Bronze_Tarefa_8',
            'link_9' => 'https://alpharede.com/Bronze_Tarefa_9',
            'link_10' => 'https://alpharede.com/Bronze_Tarefa_10',
            'link_11' => 'https://alpharede.com/Bronze_Tarefa_11',
            'link_12' => 'https://alpharede.com/Bronze_Tarefa_12',
            'link_13' => 'https://alpharede.com/Bronze_Tarefa_13',
            'link_14' => 'https://alpharede.com/Bronze_Tarefa_14',
            'link_15' => 'https://alpharede.com/Bronze_Tarefa_15'
        ]),
        'lotePrata' => json_encode([
            'link_1' => 'https://alpharede.com/Prata_Tarefa_1',
            'link_2' => 'https://alpharede.com/Prata_Tarefa_2',
            'link_3' => 'https://alpharede.com/Prata_Tarefa_3',
            'link_4' => 'https://alpharede.com/Prata_Tarefa_4',
            'link_5' => 'https://alpharede.com/Prata_Tarefa_5',
            'link_6' => 'https://alpharede.com/Prata_Tarefa_6',
            'link_7' => 'https://alpharede.com/Prata_Tarefa_7',
            'link_8' => 'https://alpharede.com/Prata_Tarefa_8',
            'link_9' => 'https://alpharede.com/Prata_Tarefa_9',
            'link_10' => 'https://alpharede.com/Prata_Tarefa_10',
            'link_11' => 'https://alpharede.com/Prata_Tarefa_11',
            'link_12' => 'https://alpharede.com/Prata_Tarefa_12',
            'link_13' => 'https://alpharede.com/Prata_Tarefa_13',
            'link_14' => 'https://alpharede.com/Prata_Tarefa_14',
            'link_15' => 'https://alpharede.com/Prata_Tarefa_15'
        ]),
        'loteOuro' => json_encode([
            'link_1' => 'https://alpharede.com/Ouro_Tarefa_1',
            'link_2' => 'https://alpharede.com/Ouro_Tarefa_2',
            'link_3' => 'https://alpharede.com/Ouro_Tarefa_3',
            'link_4' => 'https://alpharede.com/Ouro_Tarefa_4',
            'link_5' => 'https://alpharede.com/Ouro_Tarefa_5',
            'link_6' => 'https://alpharede.com/Ouro_Tarefa_6',
            'link_7' => 'https://alpharede.com/Ouro_Tarefa_7',
            'link_8' => 'https://alpharede.com/Ouro_Tarefa_8',
            'link_9' => 'https://alpharede.com/Ouro_Tarefa_9',
            'link_10' => 'https://alpharede.com/Ouro_Tarefa_10',
            'link_11' => 'https://alpharede.com/Ouro_Tarefa_11',
            'link_12' => 'https://alpharede.com/Ouro_Tarefa_12',
            'link_13' => 'https://alpharede.com/Ouro_Tarefa_13',
            'link_14' => 'https://alpharede.com/Ouro_Tarefa_14',
            'link_15' => 'https://alpharede.com/Ouro_Tarefa_15'
        ]),
        'loteDiamante' => json_encode([
            'link_1' => 'https://alpharede.com/Diamante_Tarefa_1',
            'link_2' => 'https://alpharede.com/Diamante_Tarefa_2',
            'link_3' => 'https://alpharede.com/Diamante_Tarefa_3',
            'link_4' => 'https://alpharede.com/Diamante_Tarefa_4',
            'link_5' => 'https://alpharede.com/Diamante_Tarefa_5',
            'link_6' => 'https://alpharede.com/Diamante_Tarefa_6',
            'link_7' => 'https://alpharede.com/Diamante_Tarefa_7',
            'link_8' => 'https://alpharede.com/Diamante_Tarefa_8',
            'link_9' => 'https://alpharede.com/Diamante_Tarefa_9',
            'link_10' => 'https://alpharede.com/Diamante_Tarefa_10',
            'link_11' => 'https://alpharede.com/Diamante_Tarefa_11',
            'link_12' => 'https://alpharede.com/Diamante_Tarefa_12',
            'link_13' => 'https://alpharede.com/Diamante_Tarefa_13',
            'link_14' => 'https://alpharede.com/Diamante_Tarefa_14',
            'link_15' => 'https://alpharede.com/Diamante_Tarefa_15'
        ]),
        'lotePlatina' => json_encode([
            'link_1' => 'https://alpharede.com/Platina_Tarefa_1',
            'link_2' => 'https://alpharede.com/Platina_Tarefa_2',
            'link_3' => 'https://alpharede.com/Platina_Tarefa_3',
            'link_4' => 'https://alpharede.com/Platina_Tarefa_4',
            'link_5' => 'https://alpharede.com/Platina_Tarefa_5',
            'link_6' => 'https://alpharede.com/Platina_Tarefa_6',
            'link_7' => 'https://alpharede.com/Platina_Tarefa_7',
            'link_8' => 'https://alpharede.com/Platina_Tarefa_8',
            'link_9' => 'https://alpharede.com/Platina_Tarefa_9',
            'link_10' => 'https://alpharede.com/Platina_Tarefa_10',
            'link_11' => 'https://alpharede.com/Platina_Tarefa_11',
            'link_12' => 'https://alpharede.com/Platina_Tarefa_12',
            'link_13' => 'https://alpharede.com/Platina_Tarefa_13',
            'link_14' => 'https://alpharede.com/Platina_Tarefa_14',
            'link_15' => 'https://alpharede.com/Platina_Tarefa_15'
        ])
    ];

    try {
        createDefaultTableDashLinkWithInitialData($conn, $lotes);
    } catch (Exception $e) {
        error_log("ocorreu um erro ao criar a tabela Dash_Links, o erro é: " . $e->getMessage());
    } 
}

function createDefaultTableDashLinkWithInitialData($conn, $lotes) {
    $createTableSQL = "
        CREATE TABLE IF NOT EXISTS Dash_Links (
            id INT AUTO_INCREMENT PRIMARY KEY,
            loteBronze JSON NULL,
            lotePrata JSON NULL,
            loteOuro JSON NULL,
            loteDiamante JSON NULL,
            lotePlatina JSON NULL
        );
    ";

    $checkTableSQL = "SELECT COUNT(*) AS count FROM Dash_Links";

    $conn->query($createTableSQL);

    $result = $conn->query($checkTableSQL);
    $row = $result->fetch_assoc();
    if ($row['count'] > 0) {
        return false; 
    }

    $insertSQL = "
        INSERT INTO Dash_Links (loteBronze, lotePrata, loteOuro, loteDiamante, lotePlatina)
        VALUES (?, ?, ?, ?, ?)
    ";
    $stmt = $conn->prepare($insertSQL);
    $stmt->bind_param(
        'sssss',
        $lotes['loteBronze'],
        $lotes['lotePrata'],
        $lotes['loteOuro'],
        $lotes['loteDiamante'],
        $lotes['lotePlatina']
    );
    $stmt->execute();
    return true;
}
