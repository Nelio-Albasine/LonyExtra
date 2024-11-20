<?php
$data = json_decode(file_get_contents('php://input'), true);

$requiredFields = ['senderId', 'receiverId', 'text', 'file'];
$missingFields = array_filter($requiredFields, function ($field) use ($data) {
    return empty($data[$field]);
});

if ($missingFields) {
    echo json_encode(["success" => false, "message" => "Campos obrigatórios faltando: " . implode(', ', $missingFields)]);
    exit;
}
