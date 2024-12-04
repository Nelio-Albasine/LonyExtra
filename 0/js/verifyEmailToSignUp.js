var userData = null;


document.addEventListener("DOMContentLoaded", function () {
    const btnVerifyOTP = document.getElementById("btnVerifyOTP");
    const inputOTP = document.getElementById("inputOTP");
    const userEmailToVerify = document.getElementById("userEmailToVerify");

    const urlParams = new URLSearchParams(window.location.search);

    if (!urlParams.has('data')) {
        showAlert(2, "Crie sua conta primeiro para poder receber o código de verificação!");
    } else {
        userData = JSON.parse(urlParams.get('data'));

        startCountdown();

        userEmailToVerify.textContent = userData.email;

        btnVerifyOTP.addEventListener("click", async () => {
            btnVerifyOTP.disabled = true;
            btnVerifyOTP.textContent = "Aguarde...";
            userData.otp = inputOTP.value.trim();

            try {
                const verificationResponse = await verifyOTP(userData);

                if (verificationResponse.success) {
                    localStorage.setItem("userId", userData.userId)
                    showAlert(0, 'Parabéns, sua conta foi criada com sucesso! Redirecionando para a tela principal...', true);
                    btnVerifyOTP.style.backgroundColor = "green";
                    btnVerifyOTP.textContent = "Conta criada com sucesso!";
                    window.location.href = verificationResponse.redirectTo
                } else {
                    showAlert(2, verificationResponse.message || 'Falha ao verificar o OTP. Tente novamente!', true);
                    btnVerifyOTP.disabled = false;
                }
            } catch (error) {
                showAlert(2, 'Erro ao verificar o OTP. Tente novamente!');
                btnVerifyOTP.disabled = false;
                btnVerifyOTP.textContent = "Verificar";
            }
        });
    }
});

async function verifyOTP(data) {
    const url = 'http://localhost/LonyExtra/0/api/access/otp/VerifyOTP_and_CreateUser.php';
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
        return responseData;
    } catch (error) {
        console.error("Erro:", error);
        throw error;
    }
}

async function resendOTP(data) {
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

        const responseDataText = await response.text();
        console.log("Resosta do resend: ", responseDataText)

        const responseData = JSON.parse(responseDataText);
        return responseData;
    } catch (error) {
        console.error("Erro:", error);
        throw error;
    }
}

function startCountdown() {
    const countdownDisplay = document.getElementById("textCountDownToResendOTP");

    let duration = 5 * 60;
    const timer = setInterval(() => {
        const minutes = Math.floor(duration / 60);
        const seconds = duration % 60;

        countdownDisplay.textContent = `${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;
        duration--;

        if (duration < 0) {
            clearInterval(timer);
            countdownDisplay.textContent = "Reenviar OTP";
            countdownDisplay.addEventListener("click", () => {
                startCountdown();
                resendOTP(userData);
                showAlert(1, "OTP reenviado com sucesso!", true);
            }, { once: true });
        }
    }, 1000);
}

function showAlert(alertIndex, message, hideAfterTimeout = false) {
    const divAlertMessage = document.getElementById("div_alert_message");
    const textAlert = document.getElementById("textAlert");

    const alertColors = { 0: "green", 1: "#FFB300", 2: "red" };
    divAlertMessage.style.display = "flex";
    divAlertMessage.style.backgroundColor = alertColors[alertIndex];
    textAlert.innerText = message;

    if (hideAfterTimeout) {
        setTimeout(() => {
            divAlertMessage.style.display = "none";
        }, 4000);
    }
}
