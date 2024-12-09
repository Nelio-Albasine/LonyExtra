<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/handleInvitations.log');

function getMyInviterUserId($conn, $myInviterReferralCode)
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

function increaseMyInviterTotalInvited($conn, $myInviterUserId)
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


function creditUserWithInfluencerBonus($conn, $myUID, $indluencerReferrarCode)
{
    error_log("Dentro do credit creditUserWithInfluencerBonus");

    require_once "../influencers/CheckIfUserIsInfluencer.php";
    $chefIfInvitingIsInfluencer = chefIfInvitingIsInfluencer($indluencerReferrarCode, $conn);

    error_log("Resposta do chefIfInvitingIsInfluencer retornou: ". print_r($chefIfInvitingIsInfluencer, true));

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


function addUserBonusStarsFromInfluencer($conn, $stars, $userId)
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


function isInviterCodeEmptyOrNull($conn, $userId)
{
    $query = "
        SELECT JSON_UNQUOTE(JSON_EXTRACT(userInvitationJSON, '$.myInviterCode')) AS inviterCode
        FROM Usuarios
        WHERE userId = ?
    ";
    $myInviterCode = null;

    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $myInviterCode = $row['inviterCode'];
        if ($myInviterCode === "null") {
            $myInviterCode = null;
        }
    }

    $stmt->close();
    return $myInviterCode;
}

function updateFieldWhoInvitedMe($conn, $myUserId, $myInviterReferralCode)
{
    $query = "
        UPDATE Usuarios
        SET userInvitationJSON = JSON_SET(userInvitationJSON, '$.myInviterCode', ?)
        WHERE userId = ?
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $myInviterReferralCode, $myUserId);
    $rowsUpdated = $stmt->execute();
    $stmt->close();

    return $rowsUpdated > 0;
}

function getMyInviterInfo($conn, $myInviterUserId)
{
    $query = "
        SELECT userName, userSurname, JSON_UNQUOTE(JSON_EXTRACT(userPointsJSON, '$.userLTStars')) AS userLTStars
        FROM Usuarios
        WHERE userId = ?
    ";

    $inviterInfo = null;

    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $myInviterUserId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $inviterInfo = [
            "userName" => $row['userName'] ?? "N/A",
            "userSurname" => $row['userSurname'] ?? "N/A",
            "LTStars" => $row['userLTStars'] ?? "0"
        ];
    } else {
        $inviterInfo = [];
    }

    $stmt->close();
    return $inviterInfo;
}

function getMyOwnInviteCode($conn, $userId)
{
    $query = "SELECT myReferralCode FROM Usuarios WHERE userId = ?";
    $myReferralCode = null;

    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $myReferralCode = $row['myReferralCode'];
    }

    $stmt->close();
    return $myReferralCode;
}

function thisInviteCodeReallyExists($conn, $codeToCheckExistence)
{
    $query = "SELECT myReferralCode FROM Usuarios WHERE BINARY myReferralCode = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $codeToCheckExistence);
    $stmt->execute();
    $result = $stmt->get_result();

    $exists = $result->num_rows > 0;

    $stmt->close();
    return $exists;
}

function updateUserStars($conn, $userId)
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
