async function GetDashboardInfo(userId) {
    try {
        const response = await fetch(`http://localhost/LonyExtra/0/api/Cashout/GetMyCashouts.php?userId=${userId}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            }
        });

        if (!response.ok) {
            throw new Error(`Erro na requisição: ${response.status}`);
        }

        const result = await response.json();

        return result;
    } catch (error) {
        console.error(responseMessage, error);
    }
}

(async () => {
    const userId = "391f58325968d93b6778b9722f953bb063b44254d8e04109955c52b928ac9782";
    try {
        const response = await GetDashboardInfo(userId);
        console.log(response);
    } catch (error) {
        console.error("Erro ao obter informações do dashboard:", error);
    }
})();
