async function GetDashboardInfo(userId) {
    let responseIndex = 2;
    let responseMessage = "Ocorreu um erro ao autenticar!";

    try {
        const response = await fetch(`http://localhost/LonyExtra/0/Api/Dashboard/GetDashboardInfo.php?userId=${userId}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            }
        });

        if (!response.ok) {
            throw new Error(`Erro na requisição: ${response.status}`);
        }

        const dashInfoResponse = await response.json();

        return dashInfoResponse;
    } catch (error) {
        console.error(responseMessage, error);
    }
}

(async () => {
    const userId = "391f58325968d93b6778b9722f953bb063b44254d8e04109955c52b928ac9782";
    try {
        const dashInfo = await GetDashboardInfo(userId);
        console.log(dashInfo);
    } catch (error) {
        console.error("Erro ao obter informações do dashboard:", error);
    }
})();
