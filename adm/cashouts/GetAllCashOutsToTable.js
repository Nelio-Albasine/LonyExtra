let allCashoutsLoaded = null;
let btnRefuse = null;
let btnApprove = null;
let btnApproveAndNotify = null;
let divContainer = null;


const userId = localStorage.getItem("userId");
if (userId != "391f58325968d93b6778b9722f953bb063b44254d8e04109955c52b928ac9782") {
    //window.location.href = `../../0/access/login.html`; ##REMOVE IN yml
}

document.addEventListener("DOMContentLoaded", async () => {
    btnRefuse = document.getElementById("btn_refuse");
    btnApprove = document.getElementById("btn_aprove");
    btnApproveAndNotify = document.getElementById("btn_aprove_and_notify");
    divContainer = document.getElementById("div_container_handle_saques");

    // Inicialmente, esconder todos os botões
    btnRefuse.style.display = 'none';
    btnApprove.style.display = 'none';
    btnApproveAndNotify.style.display = 'none';

    allCashoutsLoaded = await makeRequestToGetAllCAshouts(null);
    if (allCashoutsLoaded) {
        loadAllCashoutsToTable(allCashoutsLoaded);
    } else {
        console.log("Nenhum dado de saque foi carregado.");
    }

    const toggleButton = document.getElementById("closeSuccessCashoutDialog");
    const contentDiv = document.getElementById("div_base_gerenciar_saque_content");

    contentDiv.classList.add('hidden');

    toggleButton.addEventListener("click", function () {
        contentDiv.classList.toggle('visible');

        if (contentDiv.classList.contains('visible')) {
            contentDiv.classList.remove('hidden');
        } else {
            contentDiv.classList.add('hidden');
        }

        toggleButton.classList.toggle('rotated');
    });
});


document.getElementById('filterCashouts').addEventListener('change', async function () {
    const filterValue = this.value;

    if (filterValue === "0") {
        // Caso seja uma opção inválida ou desabilitada
        alert("Selecione um filtro válido!");
        return;
    }

    allCashoutsLoaded = await makeRequestToGetAllCAshouts(filterValue);
    loadAllCashoutsToTable(allCashoutsLoaded);

});


document.getElementById('filterByDateBtn').addEventListener('click', async function () {
    let startDate = document.getElementById('startDate').value;
    let endDate = document.getElementById('endDate').value;

    console.log(`startDate: ${startDate} e o endDate e: ${endDate}`);

    if (!startDate || !endDate) {
        alert('Por favor, selecione ambas as datas para filtrar.');
        return;
    }

    // Adicionando horário de 00:00:00 para o startDate e 23:59:59 para o endDate
    let startDateTime = new Date(`${startDate}T00:00:00`); // Coloca a hora como 00:00
    let endDateTime = new Date(`${endDate}T23:59:59`); // Coloca a hora como 23:59

    // Ajustando para o fuso horário de Maputo (ou qualquer outro fuso horário desejado)
    const timeZone = 'Africa/Maputo';

    // Convertendo para UTC para garantir que as datas sejam consistentes
    startDateTime = startDateTime.toLocaleString('en-US', { timeZone: timeZone });
    endDateTime = endDateTime.toLocaleString('en-US', { timeZone: timeZone });

    // Convertendo as datas para o formato ISO (UTC)
    startDateTime = new Date(startDateTime).toISOString();
    endDateTime = new Date(endDateTime).toISOString();

    console.log(`StartDate UTC: ${startDateTime}`);
    console.log(`EndDate UTC: ${endDateTime}`);

    // Enviar as datas para o backend
    allCashoutsLoaded = await makeRequestToGetAllCAshoutsFilteredByDate(startDateTime, endDateTime);
    loadAllCashoutsToTable(allCashoutsLoaded);
});


async function makeRequestToGetAllCAshoutsFilteredByDate(startDate, endDate) {
    try {
        const response = await fetch(`http://localhost/LonyExtra/adm/api/cashouts_enpoint/GetAllCashoutsFilteredbyDate.php?startDate=${startDate}&endDate=${endDate}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            }
        });

        if (!response.ok) {
            throw new Error(`Erro na requisição: ${response.status}`);
        }

        return await response.json();
    } catch (error) {
        console.error(error);
        return null;
    }
}

async function makeRequestToGetAllCAshouts(filterBy) {
    try {
        const response = await fetch(`http://localhost/LonyExtra/adm/api/cashouts_enpoint/GetAllCashouts.php?filterBy=${filterBy}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            }
        });

        if (!response.ok) {
            throw new Error(`Erro na requisição: ${response.status}`);
        }

        return await response.json();
    } catch (error) {
        console.error(error);
        return null;
    }
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text);
    alert("Copiado com sucesso!");
}

function loadAllCashoutsToTable(cashoutData) {
    const tableBody = document.getElementById("tbody_cashout_history");

    tableBody.innerHTML = "";

    if (cashoutData.length > 0) {
        cashoutData.forEach((item) => {
            const row = document.createElement("tr");

            // Coluna Index
            const indexCell = document.createElement("td");
            const indexDiv = document.createElement("div");
            const indexText = document.createElement("p");
            indexText.className = "cashoutIndex";
            indexText.textContent = item.id;
            indexDiv.appendChild(indexText);
            indexCell.appendChild(indexDiv);
            row.appendChild(indexCell);

            // Coluna Nome
            const nameCell = document.createElement("td");
            const nameDiv = document.createElement("div");
            const nameText = document.createElement("p");
            nameText.className = "userPaymentName";
            nameText.textContent = item.userPaymentName;
            nameDiv.appendChild(nameText);
            nameCell.appendChild(nameDiv);
            row.appendChild(nameCell);

            // Coluna Método
            const methodCell = document.createElement("td");
            const methodDiv = document.createElement("div");
            const methodImg = document.createElement("img");
            methodImg.className = "teable_icon_gateway";
            methodImg.src = `/0/src/imgs/${item.gatewayName === "paypal" ? "icon_paypal_256x256" : "icon_pix_240x240"}.png`;
            methodImg.alt = "Ícone do método de saque";
            const methodText = document.createElement("p");
            methodText.className = "gatway_name";
            methodText.textContent = item.gatewayName === "paypal" ? "PayPal" : "Pix";
            methodDiv.appendChild(methodImg);
            methodDiv.appendChild(methodText);
            methodCell.appendChild(methodDiv);
            row.appendChild(methodCell);

            // Coluna Quantia
            const amountCell = document.createElement("td");
            const amountDiv = document.createElement("div");
            const amountText = document.createElement("p");
            amountText.className = "textCashoutAmount";
            amountText.textContent = item.amountCashedOut.toLocaleString("pt-BR", { style: "currency", currency: "BRL" });
            amountDiv.appendChild(amountText);
            amountCell.appendChild(amountDiv);
            row.appendChild(amountCell);

            // Coluna Endereço
            const addressCell = document.createElement("td");
            const addressDiv = document.createElement("div");
            const addressText = document.createElement("p");
            addressText.className = "userPaymentAddress";
            addressText.textContent = item.userPaymentAddress;
            const copyAddressIcon = document.createElement("i");
            copyAddressIcon.className = "fa-solid fa-copy copy-icon";
            copyAddressIcon.title = "Copiar Endereço";
            copyAddressIcon.onclick = () => copyToClipboard(item.paymentAddress);
            addressDiv.appendChild(addressText);
            addressDiv.appendChild(copyAddressIcon);
            addressCell.appendChild(addressDiv);
            row.appendChild(addressCell);

            // Coluna Saque
            const idCell = document.createElement("td");
            const idDiv = document.createElement("div");
            const idText = document.createElement("p");
            idText.className = "textCashoutID";
            idText.textContent = item.cashOutId;
            const copyIdIcon = document.createElement("i");
            copyIdIcon.className = "fa-solid fa-copy copy-icon";
            copyIdIcon.title = "Copiar ID";
            copyIdIcon.onclick = () => copyToClipboard(item.cashOutId);
            idDiv.appendChild(idText);
            idDiv.appendChild(copyIdIcon);
            idCell.appendChild(idDiv);
            row.appendChild(idCell);

            // Coluna Status
            const statusCell = document.createElement("td");
            const statusDiv = document.createElement("div");
            const statusText = document.createElement("p");

            const statusMap = {
                0: "pending",
                1: "paid",
                2: "declined"
            };

            const statusClass = statusMap[item.cashOutStatus];
            statusText.className = `status ${statusClass}`;
            statusText.textContent =
                statusClass === "paid" ? "Pago" : statusClass === "pending" ? "Pendente" : "Recusado";

            const infoIcon = document.createElement("i");
            infoIcon.className = "fa fa-cog";

            infoIcon.onclick = () => updateFloatingDialogCashoutManagerUI(item);

            statusDiv.appendChild(statusText);
            statusDiv.appendChild(infoIcon);
            statusCell.appendChild(statusDiv);
            row.appendChild(statusCell);

            tableBody.appendChild(row);
        });
    } else {
        const emptyRow = document.createElement("tr");
        const emptyCell = document.createElement("td");
        emptyCell.colSpan = 7;
        emptyCell.textContent = "Nenhum saque encontrado.";
        emptyRow.appendChild(emptyCell);
        tableBody.appendChild(emptyRow);
    }
}

document.getElementById('restoreButton').addEventListener('click', function () {
    document.getElementById("restoreButton").style.display = "none";

    if (allCashoutsLoaded) {
        loadAllCashoutsToTable(allCashoutsLoaded);
    } else {
        alert("Não há dados para restaurar.");
    }
});


document.getElementById('searchButton').addEventListener('click', async function () {
    const searchTerm = document.getElementById('searchInput').value.trim();

    if (searchTerm === "") {
        alert("Por favor, insira um termo de pesquisa!");
        return;
    }

    document.getElementById("restoreButton").style.display = "flex";


    const cashouts = await searchCashouts(searchTerm);
    loadAllCashoutsToTable(cashouts);
});

// Função para buscar saques por diferentes parâmetros
async function searchCashouts(searchTerm) {
    try {
        const response = await fetch(`http://localhost/LonyExtra/adm/api/cashouts_enpoint/SearchCashouts.php?searchTerm=${encodeURIComponent(searchTerm)}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            }
        });

        if (!response.ok) {
            throw new Error(`Erro na requisição: ${response.status}`);
        }

        return await response.json();
    } catch (error) {
        console.error(error);
        return [];
    }
}


function updateFloatingDialogCashoutManagerUI(item) {
    const statusMap = {
        0: "pending",
        1: "paid",
        2: "declined"
    };

    // Selecionando o elemento de status
    const statusText = document.getElementById("status_from_dialog");
    const statusClass = statusMap[item.cashOutStatus];
    statusText.className = `status ${statusClass}`;
    statusText.textContent =
        statusClass === "paid" ? "Pago" :
            statusClass === "pending" ? "Pendente" :
                "Recusado";

    const methodImg = document.getElementById("img_metod_in_dialog_success_withdraw");
    methodImg.src = `/0/src/imgs/${item.gatewayName === "paypal" ? "icon_paypal_256x256" : "icon_pix_240x240"}.png`;

    document.getElementById("amount_text").textContent = `R$ ${parseFloat(item.amountCashedOut).toFixed(2).replace(".", ",")}`;
    document.getElementById("date_text").textContent = formatRelativeDate(new Date(item.created_at));  // Data do saque
    document.getElementById("name_text").textContent = item.userPaymentName;
    document.getElementById("address_text").textContent = item.userPaymentAddress;
    document.getElementById("transaction_id_text").textContent = item.cashOutId;



    // Mostrar os botões com base no status
    if (statusClass === "pending") {
        // Se o status for "pendente", mostrar todos os botões
        btnRefuse.style.display = 'flex';
        btnApprove.style.display = 'flex';
        btnApproveAndNotify.style.display = 'flex';
    } else if (statusClass === "paid") {
        // Se o status for "pago", mostrar apenas o botão "Recusar"
        btnRefuse.style.display = 'flex';
        btnApprove.style.display = 'none';
        btnApproveAndNotify.style.display = 'none';
    } else if (statusClass === "declined") {
        // Se o status for "recusado", mostrar os botões "Aprovar" e "Aprovar e Notificar"
        btnApprove.style.display = 'flex';
        btnApproveAndNotify.style.display = 'flex';
        btnRefuse.style.display = 'none';
    }
}

function formatRelativeDate(date) {
    const now = new Date();
    const diffInMs = now - date;
    const diffInSecs = Math.floor(diffInMs / 1000);
    const diffInMins = Math.floor(diffInSecs / 60);
    const diffInHours = Math.floor(diffInMins / 60);
    const diffInDays = Math.floor(diffInHours / 24);
    const diffInWeeks = Math.floor(diffInDays / 7);

    if (diffInSecs < 60) {
        return `${diffInSecs}s`;
    }

    if (diffInMins < 60) {
        return `${diffInMins}min`;
    }

    if (diffInHours < 24) {
        return `${diffInHours}h`;
    }

    if (diffInDays < 7) {
        return `${diffInDays}d`;
    }

    if (diffInDays >= 7) {
        const remainingHours = diffInHours % 24;
        return `${diffInDays}d:${remainingHours}h`;
    }

    if (diffInWeeks < 4) {
        return `${diffInWeeks} semana${diffInWeeks > 1 ? 's' : ''}`;
    }

    const options = {
        weekday: 'short',
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        timeZone: 'Africa/Maputo'
    };

    const formatter = new Intl.DateTimeFormat('pt-PT', options);
    return formatter.format(date);
}
