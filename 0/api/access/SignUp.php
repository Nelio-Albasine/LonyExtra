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
            $myInviterUserId = getMyInviterUserUID($conn, $userInviterCode);
            if (!empty($myInviterUserId)) {
                if (increaseMyInviterTotalInvitedByOne($conn, $myInviterUserId)) {
                    try {
                        updateUserStarsFromNormalInvitation($conn, $userId); //default +20 stars
                        
                        creditUserWithCustomInfluencerBonus($conn, $userId, $userInviterCode);
                    } catch (\Throwable $th) {
                        error_log("Ocorreu um erro ao creditar pontos bônus para o usuário ID: " . $userId . " - " . $th->getMessage());
                    }
                } else {
                    error_log("Falha ao aumentar o número de convites para o código do convidado: " . $userInviterCode);
                }
            } else {
               // error_log("Nenhum convite encontrado para o código do convidado: " . $userInviterCode);
            }
            
        }
        return true;
    } else {
        error_log("Erro ao inserir o usuário: " . $stmtInsertUser->error);
        return false;
    }
}

function creditUserWithCustomInfluencerBonus($conn, $myUID, $indluencerReferrarCode)
{
    $chefIfInvitingIsInfluencer = chefIfCurrentInvitingIsInfluencer($indluencerReferrarCode, $conn);

    if (isset($chefIfInvitingIsInfluencer['data']) && is_string($chefIfInvitingIsInfluencer['data'])) {
        $chefIfInvitingIsInfluencer['data'] = json_decode($chefIfInvitingIsInfluencer['data'], true); // true para retornar um array
    }

    if ($chefIfInvitingIsInfluencer["isInfluencer"]) {
        if ($chefIfInvitingIsInfluencer["isActive"]) {
            $pointsToEarn = $chefIfInvitingIsInfluencer["data"]["pointsToEarn"];
            $lifeTimeInfo = $chefIfInvitingIsInfluencer["data"]["lifeTimeInfo"];

            $isLifetime = $lifeTimeInfo["isLifetime"];

            if ($isLifetime) {
                addBonusToUserFromInfluencerInvitation($conn, $pointsToEarn, $myUID);
            } else {
                $startDay = $lifeTimeInfo["startDay"];
                $endDay = $lifeTimeInfo["endDay"];
                $limitUsers = $lifeTimeInfo["limitUsers"];
                // aqui você pode adicionar logs para os cálculos conforme necessário
                // calc
            }
        } else {
          //  error_log("O influenciador com o código " . $indluencerReferrarCode . " não está ativo.");
        }
    } else {
       // error_log("O código " . $indluencerReferrarCode . " não corresponde a um influenciador válido.");
    }
}

function chefIfCurrentInvitingIsInfluencer($invintingIdOrReferralCode, $conn)
{
    $response = null;

    $sql = "SELECT * FROM Influencers WHERE userId = ? OR referralCode = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $invintingIdOrReferralCode, $invintingIdOrReferralCode);  

    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        $isActive = $row['isActive'];
        $influencerDataJson = $row['influencerData'];

        $response = [
            "isInfluencer" => true,
            "isActive" => $isActive ===1 ? true : false,
            "data" => $influencerDataJson,
            "message" => "Influenciador encontrado!"
        ];
        
    } else {
        $response = [
            "isInfluencer" => null,
            "isActive" => null,
            "data" => null,
            "message" => "Nenhum influenciador encontrado para este código."
        ];
        
    }

    return $response;
}

function updateUserStarsFromNormalInvitation($conn, $userId)
{
    $starsToEarn = 20;
    $updatePointsQuery = "
        UPDATE Usuarios
        SET userPointsJSON = JSON_SET(
            userPointsJSON,
            '$.userStars', JSON_EXTRACT(userPointsJSON, '$.userStars') + ?,
            '$.userLTStars', JSON_EXTRACT(userPointsJSON, '$.userLTStars') + ?
        )
        WHERE userId = ?
    ";

    $stmt = $conn->prepare($updatePointsQuery);
    if ($stmt === false) {
        error_log("Erro ao preparar a query para atualizar as estrelas do usuário ID: " . $userId);
        return false;
    }

    $stmt->bind_param("iis", $starsToEarn, $starsToEarn, $userId);

    if ($stmt->execute()) {
        $stmt->close();
        return true;
    } else {
        $stmt->close();
        error_log("Falha ao atualizar as estrelas do usuário ID: " . $userId);
        return false;
    }
}

function increaseMyInviterTotalInvitedByOne($conn, $myInviterUserId)
{
    $query = "
        UPDATE Usuarios
        SET userInvitationJSON = JSON_SET(
            userInvitationJSON,
            '$.myTotalReferredFriends',
            COALESCE(JSON_EXTRACT(userInvitationJSON, '$.myTotalReferredFriends'), 0) + 1
        )
        WHERE userId = ?
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $myInviterUserId);
    $stmt->execute();

    $rowsUpdated = $stmt->affected_rows;
    $stmt->close();

    if ($rowsUpdated > 0) {
        return true;
    } else {
        error_log("Falha ao aumentar o número de convites para o convidador ID: " . $myInviterUserId);
        return false;
    }
}

function getMyInviterUserUID($conn, $myInviterReferralCode)
{
    $query = "SELECT userId FROM Usuarios WHERE myReferralCode = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $myInviterReferralCode);
    $stmt->execute();

    $result = $stmt->get_result();
    $userId = null;

    if ($row = $result->fetch_assoc()) {
        $userId = $row['userId'];
    } else {
        error_log("Nenhum usuário encontrado para o código de convite: " . $myInviterReferralCode);
    }

    $stmt->close();
    return $userId;
}


function addBonusToUserFromInfluencerInvitation($conn, $stars, $userId)
{

    $updatePointsQuery = "
        UPDATE Usuarios
        SET userPointsJSON = JSON_SET(
            userPointsJSON,
            '$.userStars', JSON_EXTRACT(userPointsJSON, '$.userStars') + ?,
            '$.userLTStars', JSON_EXTRACT(userPointsJSON, '$.userLTStars') + ?
        )
        WHERE userId = ?
    ";

    $stmt = $conn->prepare($updatePointsQuery);
    if ($stmt === false) {
        return false;
    }

    $stmt->bind_param("iis", $stars, $stars, $userId);

    if ($stmt->execute()) {
        $stmt->close();
        return true;
    } else {
        $stmt->close();
        return false;
    }
}