<?php
function getUserTimeZone($conn, $userId) {
    $query = "SELECT userTimeZone FROM Usuarios WHERE userId = ?";
    $stmt = $conn->prepare($query);

    if ($stmt) {
        $stmt->bind_param("s", $userId);

        if ($stmt->execute()) {
            $result = $stmt->get_result();

            if ($result) {
                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    $userTimeZone = $row["userTimeZone"];
                    return $userTimeZone;
                } else {
                    return "America/Sao_Paulo";
                }
            } else {
                error_log("Erro ao obter o resultado da consulta para userId $userId: " . $stmt->error);
            }
        } else {
            error_log("Falha ao executar a query para userId $userId: " . $stmt->error);
        }
    } else {
        error_log("Erro ao preparar a query: " . $conn->error);
    }

    return "America/Sao_Paulo";
}


function hasValidLinksPerBatch($userId)
{
    require_once "../Wamp64Connection.php";
    $conn = Wamp64Connection();

    $userTimeZone = getUserTimeZone($conn, $userId);

    $query = "SELECT availabilityJson FROM Links_Availability WHERE userId = ?";
    $stmt = $conn->prepare($query);

    if ($stmt === false) {
        return null;
    }

    $stmt->bind_param("s", $userId);
    $stmt->execute();

    $result = $stmt->get_result();
    if ($result && $row = $result->fetch_assoc()) {
        $availabilityJson = $row['availabilityJson'];

        $linkAvailability = json_decode($availabilityJson, true);

        if (!$linkAvailability) {
            return null;
        }

        function processBatch($links, $userTimeZone, $validityDays = 1)
        {
            $oldestTimeStored = null;
            $validLinkCount = 0;
            $currentDate = new DateTime();
            $oldestTimeStoredFormatted = null;
        
            foreach ($links as $linkData) {
                $isAvailable = $linkData['isAvailable'];
                $timeStored = $linkData['timeStored'] ? $linkData['timeStored'] : null; 
    
                $interval = $timeStored ? $currentDate->diff(new DateTime($timeStored))->days : 0;
        
                if ($isAvailable && ($timeStored === null || $interval <= $validityDays)) {
                    $validLinkCount++;
                }
        
                if (!$isAvailable && $timeStored !== null && ($oldestTimeStored === null || $timeStored < $oldestTimeStored)) {
                    $oldestTimeStored = $timeStored;  
                }
            }
        
            return [
                "hasValidLinks" => $validLinkCount > 0,
                "oldestTimeStored" => $oldestTimeStored,  
                "validLinkCount" => $validLinkCount,
                "currentTime" => $currentDate->format(DATE_ATOM)
            ];
        }
        
        


        $result = [
            "ouroAvailability" => processBatch($linkAvailability['ouroAvailability'],  $userTimeZone),
            "prataAvailability" => processBatch($linkAvailability['prataAvailability'],  $userTimeZone),
            "bronzeAvailability" => processBatch($linkAvailability['bronzeAvailability'],  $userTimeZone),
            "diamanteAvailability" => processBatch($linkAvailability['diamanteAvailability'],  $userTimeZone)
        ];

        return json_encode($result, JSON_PRETTY_PRINT);
    }

    return null;
}
