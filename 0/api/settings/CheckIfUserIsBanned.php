<?php
function checkIfUserIsBanned($conn, $userId): bool
{
    //checkIfUserIsBanned
    $sql = "SELECT userLockStatus FROM Usuarios WHERE userId = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return isset($row['userLockStatus']) && $row['userLockStatus'] === 1;
    } else {
        return false;
    }
}
