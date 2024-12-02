function encryptDataWithJWT(data) {
    fetch('http://localhost/LonyExtra/0/api/Config/GetJWTtokens.php', {
        method: 'GET', 
        headers: {
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json()) 
    .then(result => {
        console.log('Result', result);
    })
    .catch(error => {
        console.error("Erro ao obter o token do servidor:", error);
    });
}

encryptDataWithJWT();
