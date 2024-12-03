const divAlertMessage = document.getElementById("div_alert_message");
const textAlert = document.getElementById("textAlert");
const btnStartSession = document.getElementById("btnStartSession");

const recoverPassword = document.getElementById("recoverPassword");
const textCreateAccount = document.getElementById("CreateAccount");

document.addEventListener("DOMContentLoaded", function () {
    recoverPassword.addEventListener("click", ()=> {
        const inputEmail = document.getElementById("inputEmail").value;

        window.location.href = "http://127.0.0.1:5500/0/access/recuperar-senha.html?email=" , inputEmail ;
    });

    textCreateAccount.addEventListener("click", ()=> {
        window.location.href = "http://127.0.0.1:5500/0/access/signup.html";
    });

    btnStartSession.addEventListener("click", async () => {
        const inputEmail = document.getElementById("inputEmail").value;
        const inputPassword = document.getElementById("inputPassword").value;

        if (!areAllFieldsFilled(inputEmail, inputPassword)) {
            showAlert(1, "Por favor, preencha todos os campos!");
            return;
        }

        btnStartSession.disabled = true;
        btnStartSession.innerText = "Aguarde...";

        const userAccess = await handleUserAccess(inputEmail, inputPassword);

        if (userAccess.index === 0) {
            showAlert(userAccess.index, userAccess.message);
            setTimeout(() => {
                window.location.href = userAccess.redirectTo;
            }, 1500);
        } else {
            btnStartSession.disabled = false;
            showAlert(userAccess.index, userAccess.message);
        }
    });
});

function showAlert(alertIndex, message) {
    const alertColors = { 0: "green", 1: "#FFB300", 2: "red" };
    divAlertMessage.style.display = "flex";
    divAlertMessage.style.backgroundColor = alertColors[alertIndex];
    textAlert.innerText = message;

    setTimeout(() => {
        divAlertMessage.style.display = "none";
    }, 4000);
}

function areAllFieldsFilled(email, password) {
    return email !== "" && password !== "";
}

async function handleUserAccess(email, password) {
    let responseIndex = 2;
    let responseMessage = "Ocorreu um erro ao autenticar!";

    try {
        const response = await fetch(`http://localhost/LonyExtra/0/api/access/Login.php?email=${email}&password=${password}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            }
        });

        if (!response.ok) {
            throw new Error(`Erro na requisição: ${response.status}`);
        }

        const loginResponse = await response.json();

        if (loginResponse.success) {
            btnStartSession.style.backgroundColor = "green";
            btnStartSession.innerText = "Redirecionando...!";
            responseIndex = 0;
            responseMessage = loginResponse.message;
        window.location.href = loginResponse.redirectTo;
        } else {
            responseMessage = loginResponse.message;
        }
    } catch (error) {
        console.error(responseMessage, error);
    }

    return {
        index: responseIndex,
        message: responseMessage,
        redirectTo: loginResponse?.redirectTo || "#"
    };
}
