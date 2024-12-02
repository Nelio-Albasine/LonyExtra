document.addEventListener("DOMContentLoaded", function () {
    const inputEmail = document.getElementById('email');
    const btnRequestReset = document.getElementById('btnRequestReset');
    const p_description = document.getElementById('p_description');

    let data = null;
    let userEmail = null;
    const urlParams = new URLSearchParams(window.location.search);

    if (!urlParams.has('email')) {
        inputEmail.value = urlParams.get('email');
    }

    btnRequestReset.addEventListener('click', async () => {
        if (!inputEmail.value) {
            showAlert(2, "Insira o email que você usou ao criar a conta!");
            return;
        }

        userEmail = inputEmail.value;
        btnRequestReset.disabled = true
        let response = await sendCodeToResetEmail(userEmail);

        if (response.success) {
            inputEmail.value = "";
            p_description.innerHTML = `Enviamos um código de verificação de <br>6 dígitos para <span id="emailToResetPassword">${userEmail}</span>`;
            btnRequestReset.textContent = "Confirmar Email";
            btnRequestReset.disabled = false

            btnRequestReset.addEventListener("click", async () => {
                if (!inputEmail.value) {
                    showAlert(2, "Insira o código de verificação!");
                    return;
                }

                btnRequestReset.disabled = true
                let verificationResponse = await VerifyEmailBeforeResetPassword(inputEmail.value, userEmail);

                if (verificationResponse.success) {
                    btnRequestReset.style.backgroundColor = "green";
                    btnRequestReset.textContent = "Codigo validado!"
                    setTimeout(() => {
                        window.location.href = "../access/update-password.html?email=", userEmail;
                    }, 2000)
                } else {
                    btnRequestReset.disabled = false
                    showAlert(2, verificationResponse.message);
                }
            });
        } else {
            btnRequestReset.disabled = false
            showAlert(2, response.message);
        }

    });

});

function showAlert(alertIndex, message) {
    const divAlertMessage = document.getElementById("div_alert_message");

    const alertColors = { 0: "green", 1: "#FFB300", 2: "red" };
    divAlertMessage.style.display = "flex";
    divAlertMessage.style.backgroundColor = alertColors[alertIndex];
    textAlert.innerText = message;

    setTimeout(() => {
        divAlertMessage.style.display = "none";
    }, 8000);
}

async function sendCodeToResetEmail(email) {
    const url = 'http://localhost/LonyExtra/0/api/access/Password/SendOTPtoResetPassword.php';

    let data = {
        email: email
    }

    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });

        if (!response.ok) {
            throw new Error('Erro na solicitação');
        }

        const responseData = await response.json();
        console.log("Resposta da API em JSON:", responseData);
        return responseData;
    } catch (error) {
        console.error("Erro:", error);
        return { success: false, message: "Erro ao comunicar com o servidor" };
    }
}

async function VerifyEmailBeforeResetPassword(code, email) {
    const url = 'http://localhost/LonyExtra/0/api/access/Password/VerifyEmailBeforeResetPassword.php';

    let data = {
        email: email,
        code: code
    }

    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });

        if (!response.ok) {
            throw new Error('Erro na solicitação');
        }

        const responseData = await response.json();
        console.log("Resposta da API em JSON:", responseData);
        return responseData;
    } catch (error) {
        console.error("Erro:", error);
        return { success: false, message: "Erro ao comunicar com o servidor" };
    }
}