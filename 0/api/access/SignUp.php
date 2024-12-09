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
            if (!empty(getMyInviterUserIUI($conn, $userInviterCode))) {
                if (increaseMyInviterTotalInvitedByOne($conn, $userInviterCode)) {
                    try {
                        updateUserStarsFromNormalInvitation($conn, $userId); //default +20 stars

                        creditUserWithCustomInfluencerBonus($conn, $userId, $userInviterCode);
                    } catch (\Throwable $th) {
                        error_log("Ocorreu um erro ao creditar pontos bonus: " . $th->getMessage());
                    }
                }
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
                addUserBonusStarsFromInfluencer($conn, $pointsToEarn, $myUID );
            } else {
                $startDay = $lifeTimeInfo["startDay"];
                $endDay = $lifeTimeInfo["endDay"];
                $limitUsers = $lifeTimeInfo["limitUsers"];
                // calc
            }
        }
    } else {
        // is not influencer or doesn't even exist
    }
}


function chefIfCurrentInvitingIsInfluencer($invintingIdOrReferralCode, $conn)
{
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
            "message" => "Influencer found!"
        ];
    } else {
        $response = [
            "isInfluencer" => null,
            "isActive" => null,
            "data" => null,
            "message" => "No data found for this id"
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
        return false;
    }

    $stmt->bind_param("iis", $starsToEarn, $starsToEarn, $userId);

    if ($stmt->execute()) {
        $stmt->close();
        return true;
    } else {
        $stmt->close();
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

    return $rowsUpdated > 0;
}

function getMyInviterUserIUI($conn, $myInviterReferralCode)
{
    $query = "SELECT userId FROM Usuarios WHERE myReferralCode = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $myInviterReferralCode);
    $stmt->execute();

    $result = $stmt->get_result();
    $userId = null;

    if ($row = $result->fetch_assoc()) {
        $userId = $row['userId'];
    }

    $stmt->close();
    return $userId;
}
