document.addEventListener("DOMContentLoaded", function () {
    const btnVerifyOTP = document.getElementById("btnVerifyOTP");
    const inputOTP = document.getElementById("inputOTP");
    const userEmailToVerify = document.getElementById("userEmailToVerify");

    let data = null;
    const urlParams = new URLSearchParams(window.location.search);

    if (!urlParams.has('data')) {
        showAlert(2, "Crie sua conta primeiro para poder receber o código de verificação!");
    } else {
        data = JSON.parse(urlParams.get('data'));

        console.log(data);

        startCountdown();

        userEmailToVerify.textContent = data.email;

        btnVerifyOTP.addEventListener("click", async () => {
            btnVerifyOTP.disabled = true;
            data.otp = inputOTP.value.trim();

            try {
                const verificationResponse = await verifyOTP(data);

                if (verificationResponse.success) {
                    localStorage.setItem("userId", data.userId)
                    showAlert(0, 'Parabéns, sua conta foi criada com sucesso! Redirecionando para a tela principal...', true);
                    btnVerifyOTP.style.backgroundColor = "green";
                    btnVerifyOTP.textContent = "Conta criada com sucesso!";
                    window.location.href = verificationResponse.redirectTo
                } else {
                    showAlert(2, verificationResponse.message || 'Falha ao verificar o OTP. Tente novamente!');
                    btnVerifyOTP.disabled = false;
                }
            } catch (error) {
                showAlert(2, 'Erro ao verificar o OTP. Tente novamente!');
                console.error("Erro:", error);
                btnVerifyOTP.disabled = false;
            }
        });
    }
});

async function verifyOTP(data) {
    const url = 'http://localhost/LonyExtra/0/api/access/OTP/VerifyOTP.php';
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
        console.log("Resposta da API:", responseData);
        return responseData;
    } catch (error) {
        console.error("Erro:", error);
        throw error;
    }
}

async function resendOTP(data) {
    const url = 'http://localhost/LonyExtra/0/api/access/OTP/SendOTP.php';

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
        console.log("Resposta da API:", responseData);
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
                resendOTP(data);
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
