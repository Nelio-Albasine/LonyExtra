async function convertStars(requestData) {
    try {
        const response = await fetch("http://localhost/LonyExtra/0/api/Cashout/CashOutRevenue.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify(requestData),
        });

        if (!response.ok) {
            throw new Error(`Erro na API: ${response.statusText}`);
        }

        const responseData = await response.json();

        console.log("Resposta da API:", responseData);
    } catch (error) {
        console.error("Erro ao fazer a requisição:", error.message);
    }
}

(async () => {
    const requestData = {
        userId: "391f58325968d93b6778b9722f953bb063b44254d8e04109955c52b928ac9782",
        gatewayName: "paypal",
        amountIndex: 0,
        userPaymentName: "Nelio Albasine",
        userPaymentAddress: "nelioalbasine@gmail.com",
    };

    await convertStars(requestData);
})();
