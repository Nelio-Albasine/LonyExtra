let countdownIntervals = {};
let isOverlayVisible = false
let previusLinksFetched = null;
let allLinks = null;
let pointsToEarnByLevel = 10
let currentURL_hash;


document.addEventListener("DOMContentLoaded", async function () {
    const userName = document.getElementById("userName");
    const userRevenue = document.getElementById("userRevenue");
    const userLTRevenue = document.getElementById("userLTRevenue");
    const userStars = document.getElementById("userStars");
    const userLTStars = document.getElementById("userLTStars");
    const userEmail = document.getElementById("userEmail");

    const textTotalInvitedFriends = document.getElementById("textTotalInvitedFriends");
    const textTotalEarnedByReferrals = document.getElementById("textTotalEarnedByReferrals");

    const userId = "391f58325968d93b6778b9722f953bb063b44254d8e04109955c52b928ac9782";
    const dashInfo = await GetDashboardInfo(userId);
    const userInfo = dashInfo.userInfo;
    const userPoints = JSON.parse(userInfo.userPointsJSON);
    const userInvitationInfo = JSON.parse(userInfo.userInvitationJSON);

    saveUserPointsToLocalStorage(userPoints);
    handleLinkAvailabilityChecker(dashInfo.hasValidLinksPerBatch);
    handleStartTasksClicks();
    handleURLHashs();
    window.addEventListener("hashchange", handleURLHashs);

    userName.textContent = `${userInfo.userName} ${userInfo.userSurname}`;
    userEmail.textContent = userInfo.userEmail;

    userRevenue.textContent = userPoints.userRevenue.toLocaleString("pt-BR", { style: "currency", currency: "BRL" });
    userLTRevenue.textContent = userPoints.userLTRevenue.toLocaleString("pt-BR", { style: "currency", currency: "BRL" });

    userStars.textContent = userPoints.userStars.toLocaleString("pt-PT");
    userLTStars.textContent = userPoints.userLTStars.toLocaleString("pt-PT");

    textTotalInvitedFriends.textContent = userInvitationInfo.myTotalReferredFriends.toLocaleString("pt-PT");
    textTotalEarnedByReferrals.textContent = userInvitationInfo.totalEarnedByReferral.toLocaleString("pt-BR", { style: "currency", currency: "BRL" });
});

function handleStartTasksClicks() {
    const containerBronzeChecker = document.getElementById("containerBronzeChecker");
    const containerPrataChecker = document.getElementById("containerPrataChecker");
    const containerOuroChecker = document.getElementById("containerOuroChecker");
    const containerDiamanteChecker = document.getElementById("containerDiamanteChecker");

    const startTasksBtns = [containerBronzeChecker, containerPrataChecker, containerOuroChecker, containerDiamanteChecker];

    startTasksBtns.forEach((btn, index) => {
        btn.addEventListener("click", () => {
            handleDialogChooseTask(index);
        });
    });


}


async function FetchAllLinks(userId, batch = null) {
    try {
        const batchParam = batch ? `&batch=${batch}` : '';
        const response = await fetch(`http://localhost/LonyExtra/0/Api/Dashboard/GetAllLinks.php?userId=${userId}${batchParam}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            }
        });

        if (!response.ok) {
            throw new Error(`Erro na requisição: ${response.status}`);
        }

        const dashInfoResponse = await response.json();
        return dashInfoResponse;
    } catch (error) {
        console.error("Erro ao buscar links:", error);
        return null;
    }

}


async function loadLinksIntoTable(levelIndex) {
    const dialog_title_tasks_remaining = document.getElementById("dialog_title_tasks_remaining");
    const userId = "391f58325968d93b6778b9722f953bb063b44254d8e04109955c52b928ac9782";

    if (previusLinksFetched === null) {
        allLinks = await FetchAllLinks(userId);
    }

    let pointsToEarnByLevel;
    switch (levelIndex) {
        case 0:
            previusLinksFetched = allLinks.bronzeAvailability;
            pointsToEarnByLevel = 10;
            break;
        case 1:
            previusLinksFetched = allLinks.prataAvailability;
            pointsToEarnByLevel = 20;
            break;
        case 2:
            previusLinksFetched = allLinks.ouroAvailability;
            pointsToEarnByLevel = 30;
            break;
        case 3:
            previusLinksFetched = allLinks.diamanteAvailability;
            pointsToEarnByLevel = 50;
            break;
    }
    updateHashSilently(currentURL_hash);


    const tarefas = Object.entries(previusLinksFetched).map(([key, value], index) => {
        const timeLeft = value.isAvailable ? "Disponível" : new Date(value.timeStored).toLocaleString();
        return {
            nome: `Tarefa`,
            pontos: pointsToEarnByLevel,
            status: value.isAvailable ? "Disponível" : `⏳ ${timeLeft}`,
            disponivel: value.isAvailable,
            url: value.url,
            key: key,
        };
    });

    dialog_title_tasks_remaining.textContent = tarefas.length;

    const tbody = document.getElementById("tbody_links");

    function limparTabela() {
        tbody.innerHTML = '';
    }

    function adicionarShimmer() {
        for (let i = 0; i < 10; i++) {
            const tr = document.createElement("tr");
            tr.className = "tbody_tr_links shimmer";

            const tdNome = document.createElement("td");
            tdNome.innerHTML = `<div class="div_task_name_shimmer"></div>`;
            tr.appendChild(tdNome);

            const tdPontos = document.createElement("td");
            tdPontos.innerHTML = `<div class="div_pontuation_shimmer"></div>`;
            tr.appendChild(tdPontos);

            const tdStatus = document.createElement("td");
            tdStatus.innerHTML = `<div class="div_status_shimmer"></div>`;
            tr.appendChild(tdStatus);

            tbody.appendChild(tr);
        }
    }

    function removerShimmer() {
        const shimmerRows = tbody.querySelectorAll(".tbody_tr_links.shimmer");
        shimmerRows.forEach((row) => row.remove());
    }

    async function getToken() {
        try {
            const response = await fetch('http://localhost/LonyExtra/0/Api/Config/GetJWTtokens.php', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                }
            });

            if (!response.ok) {
                throw new Error(`Erro na requisição: ${response.status}`);
            }

            const result = await response.json();
            return result.token;
        } catch (error) {
            console.error("Erro ao obter o token do servidor:", error);
            return null;
        }
    }


    async function saveToCookies(data) {
        const jsonData = JSON.stringify(data);

        const token = await getToken();

        if (!token) {
            alert("Erro 19");
            return;
        }

        const encryptedData = CryptoJS.AES.encrypt(jsonData, token).toString();

        document.cookie = "userData=" + encodeURIComponent(encryptedData) + "; path=/; Secure; SameSite=Strict";
        console.log("Dados encriptados e  resultou em: ", encryptedData);
    }


    function popularTabela(tarefas) {
        tarefas.forEach((tarefa, index) => {
            const tr = document.createElement("tr");
            tr.className = "tbody_tr_links";

            const tdNome = document.createElement("td");
            tdNome.innerHTML = `${tarefa.nome} <strong>${index + 1}</strong>`;
            tr.appendChild(tdNome);

            const tdPontos = document.createElement("td");
            tdPontos.innerHTML = `+${tarefa.pontos} <img src="../SRC/IMGs/star.png" alt="Estrela" class="task_star">`;
            tr.appendChild(tdPontos);

            const tdStatus = document.createElement("td");
            const statusDiv = document.createElement("div");
            statusDiv.classList.add("status", tarefa.disponivel ? "disponivel" : "indisponivel");
            statusDiv.innerHTML = tarefa.disponivel
                ? `<span>${tarefa.status}</span><span class="arrow">➔</span>`
                : `<span>${tarefa.status}</span>`;

            if (tarefa.disponivel && tarefa.url) {
                statusDiv.addEventListener("click", () => {
                    statusDiv.innerText = "Carregando...";
                    setTimeout(() => {
                        let toCookies = {
                            userId: userId,
                            index: levelIndex,
                            taskId: tarefa.key,
                        }

                        console.log("data to save in cookies: ", toCookies)
                        localStorage.setItem("taskIndex", levelIndex);

                        saveToCookies(toCookies);

                        window.open(tarefa.url, "_blank");
                    }, 500);
                });
            }

            tdStatus.appendChild(statusDiv);
            tr.appendChild(tdStatus);

            tbody.appendChild(tr);
        });
    }


    limparTabela();

    if (previusLinksFetched && Object.keys(previusLinksFetched).length > 0) {
        popularTabela(tarefas);
    } else {
        adicionarShimmer();

        setTimeout(() => {
            popularTabela(tarefas);
            setTimeout(() => {
                removerShimmer();
            }, 250);
        }, 1500);
    }
}


function handleDialogChooseTask(levelIndex) {
    const overlay = document.getElementById("overlay");

    if (isOverlayVisible) {
        overlay.style.display = "none";
        isOverlayVisible = !isOverlayVisible
    } else {
        overlay.style.display = "flex";
        isOverlayVisible = !isOverlayVisible

        const closeDialog = document.getElementById("close_dialog_choose_links");
        const dialog_title_level_name = document.getElementById("dialog_title_level_name");

        const levelsNames = ["Bronze", "Prata", "Ouro", "Diamante"];
        dialog_title_level_name.textContent = levelsNames[levelIndex]

        closeDialog.addEventListener("click", () => {
            overlay.style.display = "none";
            isOverlayVisible = !isOverlayVisible
            history.replaceState(null, "", window.location.pathname + window.location.search);
        });

        loadLinksIntoTable(levelIndex);
    }
}

async function GetDashboardInfo(userId) {
    const responseMessage = "Ocorreu um erro ao autenticar!";

    try {
        const response = await fetch(`http://localhost/LonyExtra/0/Api/Dashboard/GetDashboardInfo.php?userId=${userId}`, {
            method: "GET",
            headers: {
                "Content-Type": "application/json"
            }
        });

        if (!response.ok) {
            throw new Error(`Erro na requisição: ${response.status}`);
        }

        const dashInfoResponse = await response.json();
        return dashInfoResponse;
    } catch (error) {
        console.error(responseMessage, error);
    }
}

function handleLinkAvailabilityChecker(data) {
    const checkers = [
        {
            container: document.getElementById("containerBronzeChecker"),
            textTask: document.getElementById("tasksBronzeRemaining"),
            availability: data.bronzeAvailability
        },
        {
            container: document.getElementById("containerPrataChecker"),
            textTask: document.getElementById("tasksPrataRemaining"),
            availability: data.prataAvailability
        },
        {
            container: document.getElementById("containerOuroChecker"),
            textTask: document.getElementById("tasksOuroRemaining"),
            availability: data.ouroAvailability
        },
        {
            container: document.getElementById("containerDiamanteChecker"),
            textTask: document.getElementById("tasksDiamanteRemaining"),
            availability: data.diamanteAvailability
        }
    ];

    checkers.forEach(({ container, textTask, availability }) => {
        updateTaskAvailability(container, textTask, availability);
    });
}

function calculateRemainingTime(oldestTimeStored, currentServerDate) {
    const storedTime = new Date(oldestTimeStored); // A data armazenada que foi passada

    console.log(`Oldesttime: ${oldestTimeStored} || current time from the server: ${currentServerDate}`);
    // Se a diferença é maior que zero, retorna o tempo restante
    return {};
}

function updateTaskAvailability(container, textTask, availability) {
    const intervalKey = container.id;

    if (countdownIntervals[intervalKey]) {
        clearInterval(countdownIntervals[intervalKey]);
    }

    if (availability.hasValidLinks) {
        container.classList.add("hasSomeTaskAvailable");
        container.classList.remove("dontHasSomeTaskAvailable");
        textTask.innerHTML = `Realizar tarefa <strong>(${availability.validLinkCount}/10)</strong>`;
    } else {
        container.classList.add("dontHasSomeTaskAvailable");
        container.classList.remove("hasSomeTaskAvailable");


        countdownIntervals[intervalKey] = setInterval(() => {

            //let remainingTime = calculateRemainingTime(availability.oldestTimeStored, availability.currentTime);

            let remainingTime = 1

            if (remainingTime === "Expirado") {
                textTask.innerHTML = `Tempo <strong>Expirado</strong>`;
                clearInterval(countdownIntervals[intervalKey]);
            } else {
                //textTask.innerHTML = `Disponível em <strong>${remainingTime.hours}h ${remainingTime.minutes}m ${remainingTime.seconds}s</strong>`;
            }
        }, 1000);

    }
}

function handleURLHashs() {
    const hash = window.location.hash.substring(1);
    currentURL_hash = hash;

    let index = null;
    switch (hash) {
        case "tarefas_bronze":
            index = 0;
            break;
        case "tarefas_prata":
            index = 1;
            break;
        case "tarefas_ouro":
            index = 2;
            break;
        case "tarefas_diamante":
            index = 3;
            break;
        default:
            console.warn("Página não encontrada ou hash inválido.");
            break;
    }

    if (index !== null) {
        loadLinksIntoTable(index);
        handleDialogChooseTask(index);
    } else {
        console.warn("Nenhuma tarefa será carregada porque o hash é inválido ou está vazio.");
    }
}


function updateHashSilently(hashValue) {
    if (hashValue) {
        const newURL = `${window.location.origin}${window.location.pathname}#${hashValue}`;
        history.replaceState(null, "", newURL);
    } else {
        console.warn("O valor do hash está vazio.");
    }
}


function saveUserPointsToLocalStorage(jsonPoints) {
    const userPoints = JSON.stringify(jsonPoints);
    localStorage.setItem("userPoints", userPoints);
}




