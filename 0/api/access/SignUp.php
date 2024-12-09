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
            if (!empty(getMyInviterUserUID($conn, $userInviterCode))) {
                error_log("Convite encontrado para o código do convidado: " . $userInviterCode);
                
                if (increaseMyInviterTotalInvitedByOne($conn, $userInviterCode)) {
                    error_log("Contagem de convites aumentada para o código do convidado: " . $userInviterCode);
                    
                    try {
                        updateUserStarsFromNormalInvitation($conn, $userId); //default +20 stars
                        error_log("Estrelas padrão (+20) adicionadas para o usuário ID: " . $userId);
                        
                        creditUserWithCustomInfluencerBonus($conn, $userId, $userInviterCode);
                        error_log("Bônus de influenciador creditado para o usuário ID: " . $userId);
                        
                    } catch (\Throwable $th) {
                        error_log("Ocorreu um erro ao creditar pontos bônus para o usuário ID: " . $userId . " - " . $th->getMessage());
                    }
                } else {
                    error_log("Falha ao aumentar o número de convites para o código do convidado: " . $userInviterCode);
                }
            } else {
                error_log("Nenhum convite encontrado para o código do convidado: " . $userInviterCode);
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
    error_log("Verificando se o convidado com código: " . $indluencerReferrarCode . " é um influenciador...");
    
    $chefIfInvitingIsInfluencer = chefIfCurrentInvitingIsInfluencer($indluencerReferrarCode, $conn);

    if (isset($chefIfInvitingIsInfluencer['data']) && is_string($chefIfInvitingIsInfluencer['data'])) {
        $chefIfInvitingIsInfluencer['data'] = json_decode($chefIfInvitingIsInfluencer['data'], true); // true para retornar um array
    }

    if ($chefIfInvitingIsInfluencer["isInfluencer"]) {
        error_log("O código do convidado é de um influenciador ativo!");
        
        if ($chefIfInvitingIsInfluencer["isActive"]) {
            $pointsToEarn = $chefIfInvitingIsInfluencer["data"]["pointsToEarn"];
            $lifeTimeInfo = $chefIfInvitingIsInfluencer["data"]["lifeTimeInfo"];

            $isLifetime = $lifeTimeInfo["isLifetime"];
            error_log("Informações do influenciador: pontos a ganhar: " . $pointsToEarn . ", isLifetime: " . $isLifetime);

            if ($isLifetime) {
                addUserBonusStarsFromInfluencer($conn, $pointsToEarn, $myUID);
                error_log("Estrelas bônus adicionadas permanentemente para o usuário ID: " . $myUID);
            } else {
                $startDay = $lifeTimeInfo["startDay"];
                $endDay = $lifeTimeInfo["endDay"];
                $limitUsers = $lifeTimeInfo["limitUsers"];
                // aqui você pode adicionar logs para os cálculos conforme necessário
                error_log("Bônus de influenciador limitado: de " . $startDay . " até " . $endDay . ", limite de usuários: " . $limitUsers);
                // calc
            }
        } else {
            error_log("O influenciador com o código " . $indluencerReferrarCode . " não está ativo.");
        }
    } else {
        error_log("O código " . $indluencerReferrarCode . " não corresponde a um influenciador válido.");
    }
}

function chefIfCurrentInvitingIsInfluencer($invintingIdOrReferralCode, $conn)
{
    error_log("Verificando se o código do convidado " . $invintingIdOrReferralCode . " é de um influenciador...");

    createTableInfluencersIfNotExist($conn);

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
        
        error_log("Influenciador encontrado! Código: " . $invintingIdOrReferralCode . ", Status: " . ($isActive ? "Ativo" : "Inativo"));
    } else {
        $response = [
            "isInfluencer" => null,
            "isActive" => null,
            "data" => null,
            "message" => "Nenhum influenciador encontrado para este código."
        ];
        
        error_log("Nenhum influenciador encontrado para o código: " . $invintingIdOrReferralCode);
    }

    return $response;
}

function updateUserStarsFromNormalInvitation($conn, $userId)
{
    $starsToEarn = 20;
    error_log("Adicionando estrelas padrão (+20) para o usuário ID: " . $userId);

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
        error_log("Estrelas do usuário ID " . $userId . " atualizadas com sucesso.");
        return true;
    } else {
        $stmt->close();
        error_log("Falha ao atualizar as estrelas do usuário ID: " . $userId);
        return false;
    }
}

function increaseMyInviterTotalInvitedByOne($conn, $myInviterUserId)
{
    error_log("Aumentando o número total de convites para o convidador ID: " . $myInviterUserId);
    
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
        error_log("Número de convites aumentado para o convidador ID: " . $myInviterUserId);
        return true;
    } else {
        error_log("Falha ao aumentar o número de convites para o convidador ID: " . $myInviterUserId);
        return false;
    }
}

function getMyInviterUserUID($conn, $myInviterReferralCode)
{
    error_log("Buscando o ID do usuário para o código de convite: " . $myInviterReferralCode);
    
    $query = "SELECT userId FROM Usuarios WHERE myReferralCode = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $myInviterReferralCode);
    $stmt->execute();

    $result = $stmt->get_result();
    $userId = null;

    if ($row = $result->fetch_assoc()) {
        $userId = $row['userId'];
        error_log("Usuário encontrado com o ID: " . $userId);
    } else {
        error_log("Nenhum usuário encontrado para o código de convite: " . $myInviterReferralCode);
    }

    $stmt->close();
    return $userId;
}
