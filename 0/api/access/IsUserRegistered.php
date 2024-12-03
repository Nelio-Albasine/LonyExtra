<?php
function IsUserRegistered($email, $conn): bool
{
    $response = false;

    $query = "SELECT * FROM Usuarios WHERE userEmail = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        $response = true;
    }
    
    $stmt->close();
    return $response;
}

