<?php
function hasValidLinksPerBatch($userId)
{
    require_once "../Wamp64Connection.php";
    $conn = Wamp64Connection();

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

        function processBatch($links, $validityDays = 30)
        {
            $oldestTimeStored = null;
            $validLinkCount = 0;
            $currentDate = new DateTime();

            foreach ($links as $linkData) {
                $isAvailable = $linkData['isAvailable'];
                $timeStored = $linkData['timeStored'] ? new DateTime($linkData['timeStored']) : null;

                // Verifica se o link está dentro do prazo de validade
                $interval = $timeStored ? $currentDate->diff($timeStored)->days : 0;

                if ($isAvailable && ($timeStored === null || $interval <= $validityDays)) {
                    $validLinkCount++;
                }

                if (!$isAvailable && $timeStored !== null && ($oldestTimeStored === null || $timeStored < $oldestTimeStored)) {
                    $oldestTimeStored = $timeStored;
                }
            }

            return [
                "hasValidLinks" => $validLinkCount > 0,
                "oldestTimeStored" => $oldestTimeStored ? $oldestTimeStored->format(DATE_ATOM) : null,
                "validLinkCount" => $validLinkCount,
                "currentTime" => $currentDate->format(DATE_ATOM)
            ];
        }


        $result = [
            "ouroAvailability" => processBatch($linkAvailability['ouroAvailability']),
            "prataAvailability" => processBatch($linkAvailability['prataAvailability']),
            "bronzeAvailability" => processBatch($linkAvailability['bronzeAvailability']),
            "diamanteAvailability" => processBatch($linkAvailability['diamanteAvailability'])
        ];

        return json_encode($result, JSON_PRETTY_PRINT);
    }

    return null;
}
