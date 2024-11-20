<?php
function createNewUser($conn, $data): bool
{
    $userId = $data['userId'];
    $userName = $data['name'];
    $userSurName = $data['surname'];
    $userEmail = $data['email'];
    $userPassword = password_hash($data['password'], PASSWORD_BCRYPT);  // Senha criptografada
    $userGender = $data['gender'];
    $userAge = $data['age'];
    $userInviterCode = $data['inviterCode'];
    $myReferralCode = strtoupper(substr(md5(uniqid($userId, true)), 0, 6));  // Geração de código de referência único

    $userPointsJSON = json_encode([
        "userRevenue" => 0.000,
        "userLTRevenue" => 0.000,
        "userStars" => 0,
        "userLTStars" => 0,
    ], JSON_FORCE_OBJECT);

    $userInvitationJSON = json_encode([
        "myReferralCode" => $myReferralCode,
        "myTotalReferredFriends" => 0,
        "totalEarnedByReferral" => 0.000,
        "myInviterCode" => $userInviterCode
    ], JSON_FORCE_OBJECT);

    $queryToInsert = "INSERT INTO Usuarios 
        (userId, userName, userSurname, userEmail, userPassword, myReferralCode, userGender, userAge, userPointsJSON, userInvitationJSON)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmtInsertUser = $conn->prepare($queryToInsert);
    $stmtInsertUser->bind_param("sssssssiss", $userId, $userName, $userSurName, $userEmail, $userPassword, $myReferralCode, $userGender, $userAge, $userPointsJSON, $userInvitationJSON);

    if ($stmtInsertUser->execute()) {
        return true;
    } else {
        error_log("Erro ao inserir o usuário: " . $stmtInsertUser->error);
        return false;
    }
}


