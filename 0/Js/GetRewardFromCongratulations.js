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
    console.log("o iv é: ", iv)

    const userPointsJson = loadUserPointsFromLocal();

    handlePointsToEarnText(taskIndex, pointsToEarn);

    taskBatch = getTaskBatch();
    const backLink = document.getElementById("backLink");
    if (taskBatch) {
        backLink.href = `http://127.0.0.1:5500/0/Dashboard/index.html#${taskBatch}`;
    } else {
        console.error("Task batch inválido, o link não foi atualizado.");
    }

    textUserRevenue.textContent = userPointsJson.userRevenue.toLocaleString("pt-BR", { style: "currency", currency: "BRL" });
    currentStars = userPointsJson.userStars
    textUserPoints.textContent = currentStars.toLocaleString("pt-PT");

    btnGetReward.addEventListener("click", async () => {
        await sendEncryptedDataToServer(getCookie("taskData"), iv, btnGetReward, taskIndex);
    });
});


function showAlert(message, type) {
    const alertBox = document.getElementById('alert');
    const iconSpan = alertBox.querySelector('.icon');
    const messageSpan = alertBox.querySelector('.message');

    if (type === 'success') {
        alertBox.className = 'alert show success';
        iconSpan.textContent = '✔';
    } else if (type === 'warning') {
        alertBox.className = 'alert show warning';
        iconSpan.textContent = '⚠';
    } else {
        alertBox.className = 'alert show error';
        iconSpan.textContent = '✖';
    }

    messageSpan.textContent = message;

    setTimeout(() => {
        alertBox.className = 'alert hidden';
        if (type === 'success') {
            let url = `http://127.0.0.1:5500/0/Dashboard/index.html#${taskBatch}`;
            window.open(url , "_self")
        }
    }, 1500);
    
}

function handlePointsToEarnText(index, text) {
    switch (index) {
        case 0:
            pointsToEarn = 10;
            break;
        case 1:
            pointsToEarn = 20;
            break;
        case 2:
            pointsToEarn = 30;
            break;
        case 3:
            pointsToEarn = 50;
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

async function sendEncryptedDataToServer(encryptedData, iv, btnGetReward, taskIndex) {
    btnGetReward.textContent = "Aguarde...";
    btnGetReward.disabled = true;
    let feedbackMessage = null;

    try {
        const response = await fetch('http://localhost/LonyExtra/0/Api/Tasks/DecryptTaskData.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                iv: iv,
                encryptedData: encryptedData
            }),
        });

        if (!response.ok) {
            throw new Error(`Erro na requisição: ${response.status}`);
        }

        const result = await response.json();
        console.log('Resposta do servidor:', result);

        if (result.success) {
            btnGetReward.textContent = "Pontos Adicionados!";
            btnGetReward.style.backgroundColor = "green";

            feedbackMessage = "Pontuação adicionada com sucesso!";
            showAlert(feedbackMessage, "success");

            textUserPoints.textContent = (currentStars + pointsToEarn).toLocaleString("pt-PT");
        } else if (result.message == "204") {
            feedbackMessage = "Tarefa ainda não disponivel!";
            showAlert(feedbackMessage, "warning");
            btnGetReward.textContent = "Volte dentro de algum tempinho!";
            btnGetReward.style.backgroundColor = "#c7ba05";
        } else {
            btnGetReward.disabled = false;
            console.error(result.message)
            feedbackMessage = "Ocorreu um erro ao somar estrelas!";
            showAlert(feedbackMessage, "success");
        }
    } catch (error) {
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
        default:
            console.error("Índice de tarefa inválido:", taskIndex);
    }
    return taskBatch;
}






