async function GetDashboardInfo(userId, batch = null) {
    let responseMessage = "Ocorreu um erro ao autenticar!";

    try {
        // Adiciona o parâmetro `batch` se ele for fornecido
        const batchParam = batch ? `&batch=${batch}` : '';
        const response = await fetch(`http://localhost/LonyExtra/0/api/Dashboard/GetAllLinks.php?userId=${userId}${batchParam}`, {
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
    const userId = "391f58325968d93b6778b9722f953bb063b44254d8e04109955c52b928ac9782";

    try {
        // Busca todos os lotes
        const allLinks = await GetDashboardInfo(userId);
        console.log("Todos os lotes:", allLinks);

        console.log("\n====================\n");
        console.log("\n====================");
        console.log("\n====================");

        //const bronzeLinks = await GetDashboardInfo(userId, "bronzeAvailability");
        // console.log("Lote bronze:", bronzeLinks);
    } catch (error) {
        console.error("Erro ao obter informações do dashboard:", error);
    }
})();
