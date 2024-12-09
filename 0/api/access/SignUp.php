<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/SignUp_Erros.log');

function createNewUser($conn, $data): bool
{
    $userId = $data['userId'];
    $userName = $data['name'];
    $userSurName = $data['surname'];
    $userEmail = $data['email'];
    $userTimeZone = $data['userTimeZone'];
    $userPassword = password_hash($data['password'], PASSWORD_BCRYPT);  // Senha criptografada
    $userGender = $data['gender'];
    $userAge = $data['age'];
    $userInviterCode = $data['inviterCode'];
    $myReferralCode = strtoupper(substr(md5(uniqid($userId, true)), 0, 6));  // Geração de código de referência único

    $userPointsJSON = json_encode([
        "userRevenue" => 0.000,
        "userLTRevenue" => 0.000,
        "userLTCashouts" => 0.000,
        "userStars" => 0,
        "userLTStars" => 0,
    ], JSON_FORCE_OBJECT);


    $userInvitationJSON = json_encode([
        "myReferralCode" => $myReferralCode,
        "myTotalReferredFriends" => 0,
        "totalStarsEarnedByReferral" => 0.000,
        "myInviterCode" => $userInviterCode
    ], JSON_FORCE_OBJECT);

    $queryToInsert = "INSERT INTO Usuarios 
        (userId, userName, userSurname, userEmail, userTimeZone, userPassword, myReferralCode, userGender, userAge, userPointsJSON, userInvitationJSON)
        VALUES (?, ?, ?,?, ?, ?, ?, ?, ?, ?, ?)";

    $stmtInsertUser = $conn->prepare($queryToInsert);
    $stmtInsertUser->bind_param("ssssssssiss", $userId, $userName, $userSurName, $userEmail, $userTimeZone, $userPassword, $myReferralCode, $userGender, $userAge, $userPointsJSON, $userInvitationJSON);

    if ($stmtInsertUser->execute()) {
        if (!empty($userInviterCode)) {
           // $handleInvitationDIR = "../invitation/handleInvitation.php";
            $handleInvitationDIR = "lonyextra.com/0/api/invitation/handleInvitation.php";
            if (file_exists($handleInvitationDIR)) {
                require_once $handleInvitationDIR;
                if (!empty(getMyInviterUserId($conn, $userInviterCode))) {
                    if (increaseMyInviterTotalInvited($conn, $userInviterCode)) {
                        try {
                            updateUserStars($conn, $userId); //default +20 stars

                            creditUserWithInfluencerBonus($conn, $userId, $userInviterCode);
                        } catch (\Throwable $th) {
                            error_log("Ocorreu um erro ao creditar pontos bonus: " . $th->getMessage());
                        }
                    }
                }
            } else {
                error_log("Arquivo handleInvitation.php nao existe!");
            }
        }
        return true;
    } else {
        error_log("Erro ao inserir o usuário: " . $stmtInsertUser->error);
        return false;
    }
}
