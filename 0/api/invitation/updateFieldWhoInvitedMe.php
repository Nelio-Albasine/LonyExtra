<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/updateFieldWhoInvitedMe.log');

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');


if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    $userId = $data["userId"];
    $inviteCodeInserted = $data["inviteCodeInserted"];

    require_once "../Wamp64Connection.php";

    $outPut = [
        "success" => false,
        "myInviterInfo" => null,
        "message" => null,
    ];

    if (!empty($userId) && !empty($inviteCodeInserted)) {
        require_once "handleInvitation.php";
        $conn = Wamp64Connection();

        try {
            if (thisInviteCodeReallyExists($conn, $inviteCodeInserted)) {
                if (empty(isInviterCodeEmptyOrNull($conn, $userId))) {
                    if (getMyOwnInviteCode($conn, $userId) != $inviteCodeInserted) {
                        if (updateFieldWhoInvitedMe($conn, $userId, $inviteCodeInserted)) {
                            if (updateUserStars($conn, $userId)) {
                                $myInviterUserId = getMyInviterUserId($conn, $inviteCodeInserted);

                                increaseMyInviterTotalInvited($conn, $myInviterUserId);

                                try {
                                    creditUserWithInfluencerBonus($conn, $userId, $inviteCodeInserted);
                                } catch (\Throwable $th) {
                                    error_log("Ocorreu um erro ao creditar pontos bonus: ". $th->getMessage());
                                }

                                $getMyInviterInfo = getMyInviterInfo($conn, $myInviterUserId);
                                $outPut["success"] = true;
                                $outPut["myInviterInfo"] = json_encode($getMyInviterInfo) ?? [];
                                $outPut["message"] = "Código de convinte inserido com sucesso!";
                            } else {
                                $outPut["message"] = "Ocorreu um erro ao creditar tuas estrelas!";
                            }
                        } else {
                            $outPut["message"] = "Ocorreu um erro ao inserir o codigo de ocnvinte, tenta mais tarde!";
                        }
                    } else {
                        $outPut["message"] = "Você não pode inserir o seu próprio código de convinte!";
                    }
                } else {
                    $outPut["message"] = "Você ja inseriu um código de convinte!";
                }
            } else {
                $outPut["message"] = "Esse código é inválido! Verifique e tente novamente!";
            }
        } catch (\Throwable $th) {
            error_log("Ocorreu um erro no catch: " . $th->getMessage());
        }

        error_log("Resposta da insercao do codigo de convinte: " . print_r($outPut, true));
        echo json_encode($outPut);
        $conn = null;
        exit;
    } else {
        error_log("Campo userId ou inviteCodeInserted vazio ou nulos");
    }
} else {
    error_log("Requisicao invalida!");
}
