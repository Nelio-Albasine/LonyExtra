<?php
function chefIfInvitingIsInfluencer($invintingIdOrReferralCode, $conn)
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

function createTableInfluencersIfNotExist($conn)
{
    $createTableSQL = "
        CREATE TABLE IF NOT EXISTS Influencers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            userId VARCHAR(255) NOT NULL,
            referralCode VARCHAR(6),
            isActive BOOLEAN DEFAULT TRUE,
            influencerData JSON NULL
        );
    ";

    $conn->query($createTableSQL);
}
