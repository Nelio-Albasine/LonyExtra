<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('error_log', __DIR__ . '/../Logs/EncryptAndDecryption.log');

require_once './Config.php';


if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['data'])) {
        echo json_encode(['success' => false, 'message' => 'Dados não fornecidos']);
        exit;
    }

    $dados = $input['data'];
    $SECRET_KEY = LONY_EXTRA_POINTS_SECRET_KEY;
    $metodo = "AES-256-CBC";

    try {
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($metodo));

        $dados_encriptados = openssl_encrypt($dados, $metodo, $SECRET_KEY, 0, $iv);

        if ($dados_encriptados === false) {
            throw new Exception("Falha ao encriptar os dados.");
        }

        $response = [
            'success' => true,
            'message' => 'Dados encriptados com sucesso.',
            'data' => [
                'encryptedData' => base64_encode($dados_encriptados),
                'iv' => base64_encode($iv)
            ]
        ];

        echo json_encode($response);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Erro interno no servidor.']);
    }
    exit;
} else {
    exit;
}

