const divAlertMessage = document.getElementById("div_alert_message");
const textAlert = document.getElementById("textAlert");
const btnStartSession = document.getElementById("btnStartSession");

const recoverPassword = document.getElementById("recoverPassword");
const textCreateAccount = document.getElementById("CreateAccount");

document.addEventListener("DOMContentLoaded", function () {
    recoverPassword.addEventListener("click", () => {
        const inputEmail = document.getElementById("inputEmail").value;
        let encodedEmail = null
        if(inputEmail){
            encodedEmail = encodeURIComponent(inputEmail);
        }
        window.location.href = `http://127.0.0.1:5500/0/access/redefinir-senha.html?email=${encodedEmail}`;
    });

    textCreateAccount.addEventListener("click", () => {
        window.location.href = "http://127.0.0.1:5500/0/access/signup.html";
    });

    btnStartSession.addEventListener("click", async () => {
        const inputEmail = document.getElementById("inputEmail").value.trim();
        const inputPassword = document.getElementById("inputPassword").value.trim();

        if (!areAllFieldsFilled(inputEmail, inputPassword)) {
            showAlert(1, "Por favor, preencha todos os campos!");
            return;
        }

        btnStartSession.disabled = true;
        btnStartSession.innerText = "Aguarde...";

        const loginResponse = await handleUserAccess(inputEmail, inputPassword);

        if (loginResponse.success) {
            localStorage.clear();
            localStorage.setItem("userId", loginResponse.userId);
            btnStartSession.style.backgroundColor = "green";
            btnStartSession.innerText = "Redirecionando...!";
            showAlert(0, loginResponse.message);
            setTimeout(() => {
                window.location.replace(loginResponse.redirectTo);
            }, 1500);
        } else {
            btnStartSession.innerText = "Tentar mais novamente!";
            btnStartSession.disabled = false;
            showAlert(2, loginResponse.message);
        }
    });
});


function showAlert(alertIndex, message) {
    const divAlertMessage = document.getElementById('div_alert_message');
    const textAlert = document.getElementById('textAlert');
    const alertColors = { 0: "green", 1: "#FFB300", 2: "red" };
    divAlertMessage.style.backgroundColor = alertColors[alertIndex];
    textAlert.innerText = message;
    divAlertMessage.classList.add('show');
    setTimeout(() => {
        divAlertMessage.classList.remove('show'); 
    }, 3500);
}


function areAllFieldsFilled(email, password) {
    return email !== "" && password !== "";
}

async function handleUserAccess(email, password) {
    let loginResponse;
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

        loginResponse = await response.json();
    } catch (error) {
        console.error(responseMessage, error);
    }


    return loginResponse
}
