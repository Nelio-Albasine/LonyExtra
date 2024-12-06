var userTimeZone = null;
const textStartSession = document.getElementById("StartSession");
const btnCreateAccount = document.getElementById("btnCreateAccount");

let inputName = document.getElementById("inputName");
let inputSurName = document.getElementById("inputSurName");

let inputEmail = document.getElementById("inputEmail");
let inputPassword = document.getElementById("inputPassword");
let inputPasswordConfirm = document.getElementById("inputPasswordConfirm");

let selectGender = document.getElementById("selectGender");
let inputAge = document.getElementById("birthDate");

let inviterCode = null;
const urlParams = new URLSearchParams(window.location.search);

function showAlert(alertIndex, message) {
    const divAlertMessage = document.getElementById("div_alert_message");

    const alertColors = { 0: "green", 1: "#FFB300", 2: "red" };
    divAlertMessage.style.display = "flex";
    divAlertMessage.style.backgroundColor = alertColors[alertIndex];
    textAlert.innerText = message;

    setTimeout(() => {
        divAlertMessage.style.display = "none";
    }, 4000);
}

function verifyPassword() {
    return inputPassword.value === inputPasswordConfirm.value
}

async function getTimeZoneFromAPI() {
    try {
        fetch("https://worldtimeapi.org/api/ip")
            .then(response => {
                if (!response.ok) {
                    throw new Error("Erro ao obter dados da API.");
                }
                return response.json();
            })
            .then(data => {
                userTimeZone = data.timezone
            })
            .catch(error => {
                console.error("Erro ao obter fuso horário da API:", error);
            });
    } catch (error) {
        console.error("Erro no processo:", error);
    }
}

getTimeZoneFromAPI();

function getUserTimeZoneFromLocal() {
    const timeZone = Intl.DateTimeFormat().resolvedOptions().timeZone;
    return timeZone;
}

document.addEventListener("DOMContentLoaded", async function () {
    textStartSession.addEventListener("click", () => {
        window.location.href = "http://127.0.0.1:5500/0/access/login.html";
    });

    async function emailToUniqueHash(email) {
        const encoder = new TextEncoder();
        const data = encoder.encode(email);
        const hashBuffer = await window.crypto.subtle.digest('SHA-256', data);
        const hashArray = Array.from(new Uint8Array(hashBuffer));
        const hashHex = hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
        return hashHex;
    }

    btnCreateAccount.addEventListener("click", async () => {
        if (urlParams.has('invite')) {
            inviterCode = urlParams.get('invite');
        }

        let userId = await emailToUniqueHash(inputEmail.value);

        if (!userTimeZone) {
            userTimeZone = getUserTimeZoneFromLocal();
        }

        const allFields = new Map([
            ["userId", userId],
            ["name", inputName.value],
            ["surname", inputSurName.value],
            ["email", inputEmail.value],
            ["password", inputPassword.value],
            ["gender", selectGender.value],
            ["age", inputAge.value],
            ["userTimeZone", userTimeZone],
            ["inviterCode", inviterCode]
        ]);

        const allFieldsFilled = Array.from(allFields.values()).every(field => String(field).trim() !== "");

        if (allFieldsFilled) {
            if (verifyPassword()) {
                if (inputAge.value >= 16) {
                    btnCreateAccount.disabled = true;
                    btnCreateAccount.innerText = "Aguarde...";

                    let sendResponse = await sendOTPtoVerifyEmail(Object.fromEntries(allFields));

                    if (sendResponse.success) {
                        btnCreateAccount.innerText = "Redirecionando...";
                        window.open(sendResponse.redirectTo, "_blank");
                        window.close();
                    } else {
                        btnCreateAccount.disabled = false;
                        btnCreateAccount.innerText = "Criar Conta";
                        showAlert(2, sendResponse.message);
                    }
                } else {
                    showAlert(1, "Você precisa ter 16 anos no mínimo para prosseguir!");
                }
            } else {
                showAlert(2, "As senhas não correspondem. Verifique e tente novamente");
            }
        } else {
            showAlert(1, "Por favor, preencha todos os campos");
        }
    });

});

function limitateInputsMaxChars(maxChars = 20) {
    let num = 0;
    let inputs = [inputName, inputSurName];
    inputs.forEach(input => {
        input.addEventListener("input", () => {
            if (input.value.length > maxChars) {
                input.value = input.value.slice(0, maxChars);
            }

            num++;
        });
    });
}

limitateInputsMaxChars();



async function sendOTPtoVerifyEmail(data) {
    const url = 'http://localhost/LonyExtra/0/api/access/otp/SendOTP.php';

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
        console.log("Resposta da API SignUp:", responseData);
        return responseData;
    } catch (error) {
        console.error("Erro:", error);
        return { success: false, message: "Erro ao comunicar com o servidor" };
    }
}