let divAlertMessage = null;
document.addEventListener("DOMContentLoaded", function () {
    let btnSendEmailToResetPassword = document.getElementById("btnSendEmailToResetPassword")
    let inputEmailToResetPassword = document.getElementById("inputEmailToResetPassword");
    divAlertMessage = document.getElementById("div_alert_message");


    let params = new URLSearchParams(window.location.search);

    if (params.has("email") && params.get("email") !== "null") {
        let email = params.get("email");
        inputEmailToResetPassword.value = email;
    }


    btnSendEmailToResetPassword.addEventListener("click", async (event) => {
        event.preventDefault();
        let value = inputEmailToResetPassword.value.trim();

        if (!value) {
            alert("Por favor, insira o email que você usou ao criar sua conta.");
            return;
        }

        btnSendEmailToResetPassword.disabled = true;
        btnSendEmailToResetPassword.textContent = "Aguarde...";

        let response = await tryToSendEmailToResetPassword(value);
        console.log("resposta do SendURLtoResetPassword", response);

        if (response.success) {

            window.history.replaceState({}, "", response.redirectTo);
        } else {
            showAlert(2, response.message);
            btnSendEmailToResetPassword.disabled = false;
            btnSendEmailToResetPassword.style.backgroundColor = "red";
            btnSendEmailToResetPassword.textContent = "Tentar novamente!";
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
    }, 4000);
}


async function tryToSendEmailToResetPassword(email) {
    const requestData = {
        "email": email
    };
    try {
        const response = await fetch("http://localhost/LonyExtra/0/api/access/password/SendURLtoResetPassword.php", {
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
        return responseData;
    } catch (error) {
        console.error("Erro ao fazer a requisição:", error.message);
    }
}