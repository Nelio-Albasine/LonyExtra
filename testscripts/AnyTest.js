async function sendTestEmailOtp(requestData) {
    try {

        const response = await fetch("https://lonyextra.com/0/api/access/otp/testSendOTP.php", {
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
        userName: "Nelio Albasine",
        userEmail: "nelioalbasine@gmail.com",
        otp: "012345"
    };

    await sendTestEmailOtp(requestData);
})();
