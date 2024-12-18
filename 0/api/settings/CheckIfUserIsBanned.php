<?php
function checkIfUserIsBanned($conn, $userId): bool
{
    $sql = "SELECT userLockStatus FROM Usuarios WHERE userId = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['userLockStatus'] == 1;
    } else {
        return false;
    }
}
