<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}


require_once '../../../vendor/autoload.php';
require_once './Config.php';


use \Firebase\JWT\JWT;

function generateJWT($SECRET_KEY) {
    $issuedAt = time();
    $expirationTime = $issuedAt + (20 * 60);  // Token expira em 20 min
    $payload = [
        'iat' => $issuedAt, // Data de emissão
        'exp' => $expirationTime, // Data de expiração
        'data' => [
            'crypto_id' => bin2hex(random_bytes(16)), 
        ],
    ];

    return JWT::encode($payload, $SECRET_KEY, 'HS256'); 
}


if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $SECRET_KEY = LONY_EXTRA_POINTS_SECRET_KEY;

    $token = generateJWT($SECRET_KEY);
    echo json_encode(['token' => $token]);
}

