let alertMessage = null;
let currentStars = 0;
let pointsToEarn = null;
let textUserPoints;
let taskBatch;

document.addEventListener("DOMContentLoaded", async function () {
    const textUserRevenue = document.getElementById("the_available_revenue");
    textUserPoints = document.getElementById("the_available_points");
    const pointsToEarn = document.getElementById("pointsToEarn");
    const btnGetReward = document.getElementById("btnGetReward");

    const taskIndex = parseInt(localStorage.getItem("taskIndex"));
    const iv = localStorage.getItem("iv");
    const userPointsJson = loadUserPointsFromLocal();

    handlePointsToEarnText(taskIndex, pointsToEarn);

    taskBatch = getTaskBatch();

    const backLink = document.getElementById("backLink");
    if (taskBatch) {
        backLink.href = `http://127.0.0.1:5500/0/dashboard/index.html#${taskBatch}`;
    } else {
        console.error("Task batch inválido, o link não foi atualizado.");
    }

    textUserRevenue.textContent = userPointsJson.userRevenue.toLocaleString("pt-BR", { style: "currency", currency: "BRL" });
    currentStars = userPointsJson.userStars
    textUserPoints.textContent = currentStars.toLocaleString().replace(/,/g, ' ');

    btnGetReward.addEventListener("click", async () => {
        await sendEncryptedDataToServer(getCookie("taskData"), iv, btnGetReward);
    });
});


function showBoxAlert(message, type) {
    const overlay = document.getElementById('confirmation-box_overlay');
    const confirmationBox = overlay.querySelector('.confirmation-box');
    const iconBox = confirmationBox.querySelector('.icon-box');
    const title = confirmationBox.querySelector('h1');
    const messageParagraph = confirmationBox.querySelector('p');
    const button = confirmationBox.querySelector('button');

    confirmationBox.className = `confirmation-box ${type}`;

    if (type === 'success') {
        iconBox.textContent = '✔';
        title.innerHTML = `Você ganhou <strong>+✨ ${pointsToEarn} </strong>`;
        button.textContent = 'OK';
    } else if (type === 'warning') {
        iconBox.textContent = '⚠';
        title.textContent = 'Tarefas ainda indisponível!';
        button.textContent = 'Volte dentro de algum tempinho';
    } else if (type === 'warningv2') {
        iconBox.textContent = '⚠';
        title.textContent = 'Etapas não realizadas!';
        button.textContent = 'OK';
    } if (type === 'warningv3') {
        iconBox.textContent = '⚠';
        title.textContent = 'Conta Bloqueada!';
        button.textContent = 'OK';
    } else if (type === 'error') {
        iconBox.textContent = '✖';
        title.textContent = 'Error!';
        button.textContent = 'Retry';
    }

    if (type === 'success') {
        messageParagraph.innerHTML = message;
    } else {
        messageParagraph.textContent = message;
    }

    overlay.style.display = 'flex';

    button.onclick = () => {
        if (type === 'success') {
            button.textContent = 'Voltando...';
            let url = `http://127.0.0.1:5500/0/dashboard/index.html#${taskBatch}`;
            window.open(url, "_self")
        } else if (type === 'warning') {
            button.textContent = 'Voltando...';
            let url = `http://127.0.0.1:5500/0/dashboard/index.html#${taskBatch}`;
            window.open(url, "_self")
        } else if (type === 'warningv2') {
            button.textContent = 'Voltando...';
            let url = `http://127.0.0.1:5500/0/dashboard/index.html#${taskBatch}`;
            window.open(url, "_self")
        } else if (type === 'error') {
            iconBox.textContent = '✖';
            title.textContent = 'Error!';
            button.textContent = 'Retry';
            overlay.style.display = 'none';
        }

    };
}

function handlePointsToEarnText(index, text) {
    switch (index) {
        case 0:
            pointsToEarn = 4;
            break;
        case 1:
            pointsToEarn = 7;
            break;
        case 2:
            pointsToEarn = 13;
            break;
        case 3:
            pointsToEarn = 21;
            break;
        case 4:
            pointsToEarn = 26;
            break;
    }

    text.textContent = `+${pointsToEarn}`
}

function loadUserPointsFromLocal() {
    let userPoints = localStorage.getItem('userPoints');
    if (userPoints !== null) {
        return JSON.parse(userPoints);
    } else {
        alert("Erro 43");
    }
}

function getCookie(name) {
    const cookies = document.cookie.split("; ");
    for (let cookie of cookies) {
        const [key, value] = cookie.split("=");
        if (key === name) {
            return decodeURIComponent(value);
        }
    }
    return null;
}

async function sendEncryptedDataToServer(encryptedData, iv, btnGetReward) {
    btnGetReward.textContent = "Aguarde...";
    btnGetReward.disabled = true;
    let feedbackMessage = null;
    var referer = document.referrer;
    try {
        const response = await fetch('http://localhost/LonyExtra/0/api/tasks/DecryptTaskData.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                iv: iv,
                referer: referer,
                encryptedData: encryptedData
            }),
        });

        if (!response.ok) {
            throw new Error(`Erro na requisição: ${response.status}`);
        }

        let result = await response.json();
        console.log("Data respnse are: ", result)

        if (result.success) {
            btnGetReward.textContent = "Pontos Adicionados!";
            btnGetReward.style.backgroundColor = "green";

            feedbackMessage = "Pontuação adicionada com sucesso!";
            showBoxAlert(feedbackMessage, "success");

            textUserPoints.textContent = (currentStars + pointsToEarn).toLocaleString().replace(/,/g, ' ');
        } else if (result.message === "204") {
            feedbackMessage = "Tarefa ainda não disponivel!";
            showBoxAlert(feedbackMessage, "warning");
            btnGetReward.textContent = "Volte dentro de algum tempinho!";
            btnGetReward.style.backgroundColor = "#c7ba05";
        } else if (result.message === "206") {
            feedbackMessage = "Você precisa passar pelas etapas de cada tarefa para poder receber sua recompensa.";
            showBoxAlert(feedbackMessage, "warningv2");
            btnGetReward.textContent = "Etapas não realizadas!";
            btnGetReward.style.backgroundColor = "#c7ba05";
        } if (result.message === "208") {
            feedbackMessage = "Sua conta está bloqueada. Pontuação não creditada!";
            showBoxAlert(feedbackMessage, "warningv3");
            btnGetReward.textContent = "Conta Bloqueada!";
            btnGetReward.style.backgroundColor = "#c7ba05";
        }
    } catch (error) {
        console.log('Erro ao enviar os dados:', error);
        console.error('Erro ao enviar os dados:', error);
    }
}

function getTaskBatch() {
    const taskIndex = parseInt(localStorage.getItem("taskIndex"));
    let taskBatch = null;
    switch (taskIndex) {
        case 0:
            taskBatch = "tarefas_bronze";
            break;
        case 1:
            taskBatch = "tarefas_prata";
            break;
        case 2:
            taskBatch = "tarefas_ouro";
            break;
        case 3:
            taskBatch = "tarefas_diamante";
            break;
        case 4:
            taskBatch = "tarefas_platina";
            break;
        default:
            console.error("Índice de tarefa inválido:", taskIndex);
    }
    return taskBatch;
}






