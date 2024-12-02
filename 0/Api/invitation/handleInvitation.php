<?php
function getMyInviterUserId($conn, $myInviterReferralCode) {
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


function increaseMyInviterTotalInvited($conn, $myInviterUserId) {
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