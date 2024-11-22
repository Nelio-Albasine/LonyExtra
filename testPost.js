async function sendEncryptedDataToServer(encryptedData, token) {
    if (!encryptedData || !token) {
        console.error("Dados ou token ausentes.");
        console.log("Os dados ou o token estão ausentes.");
        return;
    }

    try {
        const response = await fetch('http://localhost/LonyExtra/0/Api/Tasks/TokenAndDataHandler.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                token: token,
                encryptedData: encryptedData,
            }),
        });

        // Verifica se a resposta foi bem-sucedida
        if (!response.ok) {
            throw new Error(`Erro na requisição: ${response.status} ${response.statusText}`);
        }

        let result;
        try {
            result = await response.json();
        } catch (error) {
            throw new Error("Erro ao interpretar a resposta do servidor como JSON.");
        }

        console.log("Resposta do servidor:", result);

        if (result.error) {
            console.log(`Erro: ${result.error}`);
        } else {
            console.log(`Sucesso: ${result.message}`);
        }

    } catch (error) {
        console.error("Erro ao enviar os dados:", error);
        console.log("Erro ao enviar os dados para o servidor. Verifique os detalhes no console.");
    }
}

// Valores de exemplo
const encryptedData = "U2FsdGVkX18r6L/+BiRCrxcTa/BvEqX8ADjCFnk0s5XnVkl+8cEsdfGUtWkoCWWCVoiJ39ArWSyJ2gDFMhubjmFEv3BCILw7oSBAX0XQr4HLWiO2Yy2CkUNrlycETJZ40aAvrbmeUDvV50abKDR1pyxNR9dIqm1c3SliVUrat8g=";
const token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3MzIxNDA1MjUsImV4cCI6MTczMjE0MTcyNSwiZGF0YSI6eyJjcnlwdG9faWQiOiIzNmI5OGRmZTQ5ZDI4YzAyZjg5ZjdjYjYwYjgzMDkwMiJ9fQ.9gf4uxMldQ_4cb1wOGnVqbTyz6TRZ96eimNRDa-Tb6o";

// Chamada da função
(async () => {
    await sendEncryptedDataToServer(encryptedData, token);
})();
