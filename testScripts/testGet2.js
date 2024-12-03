async function GetDashboardInfo() {
    try {
        const response = await fetch(`http://localhost/LonyExtra/0/api/dashboard/GetTop10Users.php?`, {
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

// Exemplo de uso
(async () => {
    try {
        const top10 = await GetDashboardInfo();
        console.log("Todos os top 10:", top10);
    } catch (error) {
        console.error("Erro ao obter informações do dashboard:", error);
    }
})();
