const hashToDialogData = {
    "#TodosLinks": { id: "dialog_all_links", title: "Todos os Links" },
    "#TodosLinks": { id: "dialog_all_links", title: "Todos os Links" },
    "#ConverterEstrelas": { id: "dialog_convert_stars", title: "Converter Estrelas" },
    "#ConvidarAmigos": { id: "dialog_convidar_amigos", title: "Convidar Amigos" },
    "#Sacar": { id: "dialog_sacar", title: "Sacar Saldo" },
    "#HistoricoSaques": { id: "dialog_historio_de_saques", title: "Histórico de Saques" },
    "#MinhasNotificacoes": { id: "dialog_minhas_notificacoes", title: "Minhas Notificações" },
    "#Instagram": { id: "dialog_instagram", title: "Instagram" },
    "#Telegram": { id: "dialog_telegram", title: "Telegram" },
    "#Gmail": { id: "dialog_gmail", title: "Gmail - Suporte" },
    "#YouTube": { id: "dialog_youtube", title: "YouTube" },
    "#Perfil": { id: "dialog_perfil", title: "Perfil" },
    "#Logout": { id: "dialog_logout", title: "Logout" },
};

const starsToValueMap = [
    { stars: 600, revenue: 1.0 },
    { stars: 2980, revenue: 5.20 },
    { stars: 5955, revenue: 10.50 },
    { stars: 11905, revenue: 21.30 },
    { stars: 23805, revenue: 43.19 },
];

const indexCashoutToAmountToValues = {
    0: 1.0,
    1: 5.2,
    2: 10.5,
    3: 21.3,
    4: 30.5,
    5: 48.3
};

let imgCloseDialog = document.getElementById("img_close_dialog");
const convertStarsBtn = document.getElementById("convertStarsBtn");
const btnCashoutRevenue = document.getElementById('btnCashOutRevenue');
const giftCards = document.querySelectorAll('.gift-card');
const textUserRevenue = document.querySelectorAll('.userRevenue');
const textUserLTRevenue = document.querySelectorAll('.userLTRevenue');
const textUserStars = document.querySelectorAll('.userStars');
const custom_selectPaymentMethod_container = document.querySelector('.custom-select-container');
const gift_gateway_icon = document.querySelectorAll('.gift_gateway_icon');

let conversionOption = null;
let paymentMethodSelected = null
let indexToCashOut = null;
let userCashoutName = null;
let userCashoutAdress = null;
let optionText = null;
let targetDialogId = null;
let dialogTitle = null;
let myReferralCode = null;

document.addEventListener("DOMContentLoaded", () => {
    openEspecificDialog(window.location.hash);
    closeDialogContainer();
    whenConvertStarsClicked();
    whenCashoutRevenueClicked();
});

function copyToClipboard(text) {
    navigator.clipboard.writeText(text);
    alert("Copiado com sucesso!");
}

function openEspecificDialog(dialogIdHasHashFormat = null) {
    const dialogs = document.querySelectorAll(".main_container_all_dialogs .the_dialog_container");
    const p_available_revenue = document.querySelectorAll(".p_dialog_available_revenue");
    const overlay = document.getElementById("overlay");
    const textDialogTitle = document.querySelector("#dialog_title");

    if (dialogIdHasHashFormat === "#Home") {
        document.querySelector('.drawer').classList.remove('open');
    } else {
        if (!dialogIdHasHashFormat) {
            return;
        }

        p_available_revenue.forEach(text => {
            text.style.display = dialogIdHasHashFormat == "dialog_sacar" ? "flex" : "none";
        })


        let hashFromTasks = ["#tarefas_bronze", "#tarefas_prata", "#tarefas_ouro", "#tarefas_diamante", "#tarefas_platina"];
        if (hashFromTasks.includes(dialogIdHasHashFormat)) {
            targetDialogId = hashToDialogData["#TodosLinks"].id
            dialogTitle = hashToDialogData["#TodosLinks"]?.title
        } else {
            targetDialogId = hashToDialogData[dialogIdHasHashFormat].id
            dialogTitle = hashToDialogData[dialogIdHasHashFormat]?.title
        }


        dialogs.forEach(dialog => {
            dialog.style.display = "none";
        });

        overlay.style.display = "flex";
        document.getElementById(targetDialogId).style.display = "flex";

        textDialogTitle.textContent = dialogTitle || "Desconhecido";

        if (dialogIdHasHashFormat == "#ConvidarAmigos") {
            //quando o user clica naquele arrow de convite na tela Home
            window.location.hash = "ConvidarAmigos";
        }
        handleActionsToEspecifiedHash(dialogIdHasHashFormat);
    }
}

function handleActionsToEspecifiedHash(dialogId) {
    switch (dialogId) {
        case "#TodosLinks":
            //this menu is alread handled in GetDashBoardInf.js
            break;

        case "#ConverterEstrelas":
            handleConverterEstrelas();
            break;

        case "#ConvidarAmigos":
            handleInviteFriendsDialog()
            break;

        case "#Sacar":
            handleCashoutDialog();
            break;

        case "#HistoricoSaques":
            console.log(`Case #HistoricoSaques`)
            loadMyCashoutsToTable();
            break;

        case "#MinhasNotificacoes":
            console.log("Ação selecionada: Minhas Notificações");
            break;

        case "#Instagram":
            console.log("Ação selecionada: Instagram");
            break;

        case "#Telegram":
            console.log("Ação selecionada: Telegram");
            break;

        case "#Gmail":
            handleGmailDialog();
            break;

        case "#YouTube":
            console.log("Ação selecionada: YouTube");
            break;

        case "#Perfil":
            handleProfileDIalog();
            break;

        case "#Logout":
            handleSignOutDIalog();
            break;
    }

}

function whenConvertStarsClicked() {
    const dialog_subtitle = document.querySelectorAll('.dialog_subtitle');

    convertStarsBtn.addEventListener("click", () => {
        if (conversionOption === null) {
            dialog_subtitle.forEach(text => {
                text.style.color = "red";
                text.style.scale = "1.03";
                setTimeout(() => {
                    text.style.color = "#7f8c8d";
                    text.style.scale = "1";
                }, 3000)
            })
        } else {
            convertStarsBtn.disabled = true;
            convertStarsBtn.textContent = "Aguarde...";
            handleConvertion(conversionOption, convertStarsBtn);
        }
    });
}

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
        title.textContent = 'Success!';
        button.textContent = 'OK';
    } else if (type === 'warning') {
        iconBox.textContent = '⚠';
        title.textContent = 'Atenção!';
        button.textContent = 'Entendido';
    } else if (type === 'error') {
        iconBox.textContent = '✖';
        title.textContent = 'Error!';
        button.textContent = 'Retry';
    }

    messageParagraph.innerHTML = message;

    overlay.style.display = 'flex';

    button.onclick = () => {
        overlay.style.display = 'none';
    };
}

function closeDialogContainer() {
    imgCloseDialog.addEventListener("click", () => {
        const overlay = document.getElementById("overlay");
        overlay.style.display = "none";
        history.replaceState(null, "", window.location.pathname + window.location.search);
    });
}


function handleConverterEstrelas() {
    const options = document.querySelectorAll('.conversion_option');
    convertStarsBtn.style.backgroundColor = "#7e7e7e";
    options.forEach(opt => opt.classList.remove('selected'));

    options.forEach((option, index) => {
        option.addEventListener('click', () => {
            options.forEach(opt => opt.classList.remove('selected'));
            option.classList.add('selected');
            convertStarsBtn.style.backgroundColor = "#043277";
            convertStarsBtn.textContent = "Converter Estrelas";
            conversionOption = index
        });
    });
}

async function handleConvertion(index, convertStarsBtn) {
    convertStarsBtn.disabled = true;
    const requestData = {
        userId: userId,
        index: index,
    };

    try {
        const response = await fetch("http://localhost/LonyExtra/0/api/cashout/ConvertStarsIntoReward.php", {
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

        if (responseData.success) {
            showBoxAlert(responseData.message, "success");
            convertStarsBtn.textContent = "Estrelas convertidas com sucesso!";
            convertStarsBtn.textContent = "Conversão bem sucedida!";
            convertStarsBtn.style.backgroundColor = "green";

            let userPointsString = localStorage.getItem("userPoints");
            let userPointsJson = JSON.parse(userPointsString);

            let availableUserStars = parseInt(userPointsJson.userStars, 10);
            let availableUserRevenue = parseFloat(userPointsJson.userRevenue);
            let availableUserLTRevenue = parseFloat(userPointsJson.userLTRevenue);

            const conversionData = starsToValueMap[index];

            const newStars = parseInt(availableUserStars - conversionData.stars, 10);
            const newRevenue = parseFloat(availableUserRevenue + conversionData.revenue);
            const newLTRevenue = parseFloat(availableUserLTRevenue + conversionData.revenue);

            userPointsJson.userStars = newStars;
            userPointsJson.userRevenue = newRevenue;
            userPointsJson.userLTRevenue = newLTRevenue;
            localStorage.setItem("userPoints", JSON.stringify(userPointsJson));

            textUserStars.forEach(el => {
                el.textContent = newStars.toLocaleString("pt-PT");
            });

            textUserRevenue.forEach(el => {
                el.textContent = newRevenue.toLocaleString("pt-BR", { style: "currency", currency: "BRL" });
            });

            textUserLTRevenue.forEach(el => {
                el.textContent = newLTRevenue.toLocaleString("pt-BR", { style: "currency", currency: "BRL" });
            });
        } else {
            if (responseData.message === "405") {
                let msg = "Estrelas insuficientes para conversão!";
                showBoxAlert(msg, "warning");
                convertStarsBtn.textContent = "Estrelas insuficientes!";
                convertStarsBtn.style.backgroundColor = "#c7ba05";
            } else {
                showBoxAlert(responseData.message, "error");
                convertStarsBtn.textContent = "Erro na conversão!";
                convertStarsBtn.style.backgroundColor = "#7e7e7e";
            }
        }

        setTimeout(() => {
            convertStarsBtn.textContent = "Escolha uma opção!";
            convertStarsBtn.style.backgroundColor = "#7e7e7e";

            let selectedOptions = document.querySelectorAll('.conversion_option');
            selectedOptions.forEach(option => {
                option.classList.remove('selected');
                conversionOption = null;
                convertStarsBtn.disabled = false;
            });
        }, 3000);
    } catch (error) {
        convertStarsBtn.disabled = false;
        convertStarsBtn.textContent = "Escolha uma opção!";
        convertStarsBtn.style.backgroundColor = "#7e7e7e";
        conversionOption = null;
        let selectedOptions = document.querySelectorAll('.conversion_option');
        selectedOptions.forEach(option => {
            option.classList.remove('selected');
            conversionOption = null;
            convertStarsBtn.disabled = false;
        });
        console.error("Erro ao fazer a requisição:", error.message);
        showBoxAlert("Erro na comunicação com o servidor. Tente novamente.", "error");
    }
}

function handleCashoutDialog() {
    function handleGiftCardSelection() {
        giftCards.forEach((card, index) => {
            card.addEventListener('click', () => {
                giftCards.forEach(c => c.classList.remove('selected'));
                card.classList.add('selected');
                btnCashoutRevenue.style.backgroundColor = "#043277";
                indexToCashOut = index;
            });
        });
    }

    handleGiftCardSelection();


    function toggleDropdown() {
        const dropdown = document.querySelector('.dropdown');
        if (dropdown.classList.contains('active')) {
            dropdown.classList.remove('active');
        } else {
            dropdown.classList.add('active');
        }
    }

    function selectOption(optionText, iconSrc) {
        const select = document.querySelector('.custom-select span');
        const icon = document.getElementById('selected_gatway_icon');
        const label_payment_adress = document.getElementById('label_payment_adress');

        select.textContent = optionText;
        icon.style.display = "inline-block";
        icon.src = iconSrc;
        gift_gateway_icon.forEach(icon => {
            icon.src = iconSrc;
        })
        label_payment_adress.textContent = optionText === "Paypal"
            ? "Seu Email do PayPal"
            : "Sua chave ou email do Pix";

        paymentMethodSelected = optionText
        console.log(`Opção selecionada: ${optionText}`);
        toggleDropdown();
    }

    const customSelect = document.querySelector('.custom-select');
    customSelect.addEventListener('click', toggleDropdown);

    const dropdownOptions = document.querySelectorAll('.dropdown > div');
    dropdownOptions.forEach(option => {
        option.addEventListener('click', () => {
            optionText = option.querySelector('span').textContent;
            const iconSrc = option.querySelector('img').src;
            selectOption(optionText, iconSrc);
        });
    });

    document.addEventListener('click', (event) => {
        if (!custom_selectPaymentMethod_container.contains(event.target)) {
            const dropdown = document.querySelector('.dropdown');
            dropdown.classList.remove('active');
        }
    });
}

function whenCashoutRevenueClicked() {
    btnCashoutRevenue.addEventListener("click", async (event) => {
        event.preventDefault();

        userCashoutName = document.getElementById("full-name").value.trim();
        userCashoutAdress = document.getElementById("email").value.trim();

        if (!paymentMethodSelected) {
            showBoxAlert("Escolha o <strong>método</strong> de saque!", "warning");
            return
        }
        if (indexToCashOut == null) {
            console.log(`Index no if: ${indexToCashOut}`)
            showBoxAlert("Escolha o <strong>valor 💰</strong> a sacar", "warning");
            return
        }
        if (!userCashoutName) {
            let text = optionText === "Paypal"
                ? "Insira o <strong>nome</strong> da sua conta paypal"
                : "Insira o <strong>nome</strong> da sua conta Pix";
            showBoxAlert(text, "warning");
            return
        }
        if (!userCashoutAdress) {
            let text = optionText === "Paypal"
                ? "Insira seu <strong>Email do</strong> PayPal"
                : "Insira sua <strong>chave ou email do</strong> Pix";
            showBoxAlert(text, "warning");
            return
        }

        const data = {
            userId: userId,
            gatewayName: paymentMethodSelected.toLowerCase(),
            amountIndex: indexToCashOut,
            userPaymentName: userCashoutName,
            userPaymentAddress: userCashoutAdress,
        }

        btnCashoutRevenue.disabled = true;
        btnCashoutRevenue.textContent = "Aguarde...";
        await makeRequestToCashOut(data, btnCashoutRevenue);
    })
}

function formatarData(dataISO, userTimeZone) {
    const data = new Date(dataISO);

    const meses = [
        "Janeiro", "Fevereiro", "Março", "Abril", "Maio", "Junho",
        "Julho", "Agosto", "Setembro", "Outubro", "Novembro", "Dezembro"
    ];

    const horas = data.toLocaleTimeString("pt-BR", {
        timeZone: userTimeZone,
        hour: "2-digit",
        minute: "2-digit"
    });

    const dia = data.toLocaleDateString("pt-BR", {
        timeZone: userTimeZone,
        day: "2-digit"
    });

    const mes = meses[data.toLocaleDateString("pt-BR", {
        timeZone: userTimeZone,
        month: "numeric"
    }) - 1];

    const ano = data.toLocaleDateString("pt-BR", {
        timeZone: userTimeZone,
        year: "numeric"
    });

    return `${dia} de ${mes} ${ano}, ${horas} `;
}

function showSuccessDialogCashout(data, isByHistoryTable = null) {
    const closeSuccessCashoutDialog = document.getElementById("closeSuccessCashoutDialog");
    const success_cashout_overlay = document.getElementById("success_cashout_overlay");

    success_cashout_overlay.style.display = "flex";

    const methodElement = document.querySelector(".details .detail-row:nth-child(1) .span_mutable_texts");
    const amountElement = document.querySelector(".details .detail-row:nth-child(2) .span_mutable_texts");
    const dateElement = document.querySelector(".details .detail-row:nth-child(3) .span_mutable_texts");
    const nameElement = document.querySelector(".details .detail-row:nth-child(4) .span_mutable_texts");
    const addressElement = document.querySelector(".details .detail-row:nth-child(5) .span_mutable_texts");
    const transactionIdElement = document.querySelector(".details .detail-row:nth-child(6) .span_mutable_texts");
    const statusElement = document.getElementById("status_from_dialog");
    let icon = document.getElementById("img_metod_in_dialog_success_withdraw");
    let btn_see_cashou_status = document.getElementById("btn_see_cashou_status");


    amountElement.textContent = `R$ ${data.amountCashedOut.toLocaleString("pt-BR", { style: "currency", currency: "BRL" })}` || "Valor não informado";
    nameElement.textContent = data.userPaymentName || "Nome não informado";
    addressElement.textContent = data.userPaymentAddress || "Endereço não informado";
    transactionIdElement.textContent = data.cashOutId || "ID não informado";
    dateElement.textContent = formatarData(data.created_at, userTimeZone);

    if (isByHistoryTable) {
        const status_image = document.querySelectorAll(".status-image");
        status_image.forEach(img => img.style.display = "none")
        btn_see_cashou_status.style.display = "none";
        methodElement.textContent = data.gatewayName == "paypal" ? "Paypal" : "Pix"
        icon.src = `../src/imgs/${data.gatewayName == "paypal" ? "icon_paypal_256x256" : "icon_pix_240x240"}.png`;

        const statusMap = {
            0: "pending",
            1: "paid",
            2: "declined"
        };

        const statusClass = statusMap[data.cashOutStatus];
        statusElement.className = `status ${statusClass} status_from_dialog`;

        statusElement.textContent = statusClass === "paid" ? "Pago" : statusClass === "pending" ? "Pendente" : "Recusado";
    } else {
        const status_image = document.querySelectorAll(".status-image");
        status_image.forEach(img => img.style.display = "flex")
        btn_see_cashou_status.style.display = "flex";

        methodElement.textContent = data.metodo || "Método desconhecido";
        icon.src = `../src/imgs/${data.metodo == "paypal" ? "icon_paypal_256x256" : "icon_pix_240x240"}.png`;


        statusElement.className = `status pending status_from_dialog`;
        statusElement.textContent = "Pendente";

        btn_see_cashou_status.addEventListener("click", () => {
            let hashHistorico = "#HistoricoSaques"
            window.location.hash = hashHistorico;
            openEspecificDialog(hashHistorico);
            success_cashout_overlay.style.display = "none";
        });
    }

    closeSuccessCashoutDialog.addEventListener("click", () => {
        success_cashout_overlay.style.display = "none";
    });
}

async function makeRequestToCashOut(requestData, btnCashoutRevenue) {
    try {
        const response = await fetch("http://localhost/LonyExtra/0/api/cashout/CashOutRevenue.php", {
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

        if (responseData.success) {
            btnCashoutRevenue.style.backgroundColor = "green";
            btnCashoutRevenue.textContent = responseData.message;

            let userPointsString = localStorage.getItem("userPoints");
            let userPointsJson = JSON.parse(userPointsString);

            let amountToCashout = parseFloat(indexCashoutToAmountToValues[requestData.amountIndex]);

            console.log(`O let amountToCashout guardou: ${amountToCashout}`)

            let remaingRevenue = (parseFloat(userPointsJson.userRevenue) - amountToCashout)

            userPointsJson.userRevenue = remaingRevenue
            localStorage.setItem("userPoints", JSON.stringify(userPointsJson));

            textUserRevenue.forEach(text => {
                text.textContent = remaingRevenue.toLocaleString("pt-BR", { style: "currency", currency: "BRL" });
            })

            const dataForSuccessDialog = {
                metodo: requestData.gatewayName, //is: paypal or pix
                amountCashedOut: amountToCashout,
                userPaymentName: requestData.userPaymentName,
                userPaymentAddress: requestData.userPaymentAddress,
                cashOutId: responseData.cashOutId,
                created_at: responseData.created_at
            }

            showSuccessDialogCashout(dataForSuccessDialog);
        } else if (responseData.message == "405") {
            let msg = "Saldo insuficiente!";
            btnCashoutRevenue.textContent = msg;
            showBoxAlert(msg, "warning")
            btnCashoutRevenue.style.backgroundColor = "#ffc107";
        } else {
            showBoxAlert(responseData.message, "error")
            btnCashoutRevenue.textContent = "Ocorreu um erro!";
            btnCashoutRevenue.style.backgroundColor = "red";
            console.error("Ocorreu um erro!", responseData.message);
        }
    } catch (error) {
        showBoxAlert("Um erro inesperado ocorreu", "error")
        btnCashoutRevenue.textContent = "Ocorreu um erro!";
        btnCashoutRevenue.style.backgroundColor = "red";
        console.error("Um erro inesperado ocorreu", error.message);
    }
    setTimeout(() => {
        const dropdown = document.querySelector('.dropdown');
        dropdown.classList.remove('active');
        const icon = document.getElementById('selected_gatway_icon');
        icon.style.display = "none";
        const select = document.querySelector('.custom-select span');
        select.textContent = "Selecione uma opção";
        giftCards.forEach(c => c.classList.remove('selected'));
        gift_gateway_icon.forEach(icon => icon.src = "../src/imgs/cartoon_money.png");
        btnCashoutRevenue.textContent = "Efetuar Saque";
        btnCashoutRevenue.style.backgroundColor = "#707070";
        indexToCashOut = null;
        paymentMethodSelected = null;
        btnCashoutRevenue.disabled = false;
    }, 2000)
}

async function loadMyCashoutsToTable() {
    const tableBody = document.getElementById("tbody_cashout_history");
    const emptyHistory = document.getElementById("empty_history");
    const loading_or_empty_awesome_icon = document.getElementById("loading_or_empty_awesome_icon");
    const p_sem_saques = document.querySelectorAll(".p_sem_saques");

    loading_or_empty_awesome_icon.className = "fa-regular fa-hourglass history-icon";
    p_sem_saques.forEach(text => text.textContent = "Carregando...");

    tableBody.innerHTML = "";

    const historyData = await makeRequestToGetMyCAshouts()

    if (historyData.length > 0) {
        emptyHistory.style.display = "none";

        historyData.forEach((item) => {
            const row = document.createElement("tr");

            // Coluna Método
            const methodCell = document.createElement("td");
            const methodDiv = document.createElement("div");
            const methodImg = document.createElement("img");
            methodImg.className = "teable_icon_gateway";
            methodImg.src = `../src/imgs/${item.gatewayName == "paypal" ? "icon_paypal_256x256" : "icon_pix_240x240"}.png`;
            methodImg.alt = "cashout method icon";
            const methodText = document.createElement("p");
            methodText.className = "gatway_name";
            methodText.textContent = item.gatewayName == "paypal" ? "PayPal" : "Pix";
            methodDiv.appendChild(methodImg);
            methodDiv.appendChild(methodText);
            methodCell.appendChild(methodDiv);
            row.appendChild(methodCell);

            // Coluna Quantia
            const amountCell = document.createElement("td");
            const amountDiv = document.createElement("div");
            const amountText = document.createElement("p");
            amountText.className = "textCashoutAmount";
            amountText.textContent = "R$ " + item.amountCashedOut.toLocaleString("pt-BR", { style: "currency", currency: "BRL" });
            amountDiv.appendChild(amountText);
            amountCell.appendChild(amountDiv);
            row.appendChild(amountCell);

            // Coluna Saque
            const idCell = document.createElement("td");
            const idDiv = document.createElement("div");
            const idText = document.createElement("p");
            idText.className = "textCashoutID";
            idText.textContent = item.cashOutId;
            const copyIcon = document.createElement("i");
            copyIcon.className = "fa-solid fa-copy copy-icon";
            copyIcon.title = "Copiar ID";
            copyIcon.onclick = () => copyToClipboard(item.cashOutId);
            idDiv.appendChild(idText);
            idDiv.appendChild(copyIcon);
            idCell.appendChild(idDiv);
            row.appendChild(idCell);

            // Coluna Status
            const statusCell = document.createElement("td");
            const statusDiv = document.createElement("div");
            const statusText = document.createElement("p");

            // Mapeamento dos status
            const statusMap = {
                0: "pending",
                1: "paid",
                2: "declined"
            };



            const statusClass = statusMap[item.cashOutStatus];
            statusText.className = `status ${statusClass}`;
            statusText.textContent =
                statusClass === "paid"
                    ? "Pago"
                    : statusClass === "pending"
                        ? "Pendente"
                        : "Recusado";

            const infoIcon = document.createElement("i");
            infoIcon.className = "fa-solid fa-circle-info info-icon";
            infoIcon.title = "Detalhes";
            infoIcon.onclick = () =>
                showSuccessDialogCashout(item, true);

            statusDiv.appendChild(statusText);
            statusDiv.appendChild(infoIcon);
            statusCell.appendChild(statusDiv);
            row.appendChild(statusCell);

            // Adicionar linha à tabela
            tableBody.appendChild(row);
        });
    } else {
        loading_or_empty_awesome_icon.className = "fa-solid fa-history history-icon";
        p_sem_saques.forEach(text => text.textContent = "Sem saques!");
    }

}

async function makeRequestToGetMyCAshouts() {
    try {
        const response = await fetch(`http://localhost/LonyExtra/0/api/cashout/GetMyCashouts.php?userId=${userId}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            }
        });

        if (!response.ok) {
            throw new Error(`Erro na requisição: ${response.status}`);
        }

        const result = await response.json();

        return result;
    } catch (error) {
        console.error(responseMessage, error);
    }
}

function updateCounter(input, counterId, maxLength) {
    const counter = document.getElementById(counterId);
    counter.textContent = `${input.value.length}/${maxLength}`;
}

async function waitForUserInfo() {
    while (userInfo === null) {
        await new Promise(resolve => setTimeout(resolve, 1000));
        userInfo = await userInfo;
    }
    return userInfo;
}

function getInitials(name, surname) {
    const nameInitial = name && name.trim() ? name.trim()[0].toUpperCase() : "N";
    const surnameInitial = surname && surname.trim() ? surname.trim()[0].toUpperCase() : "A";
    return nameInitial + surnameInitial;
}

function hadeUIwhenInvitedCodeIsInserted(inviterInfoParam) {
    let inviterInfo = null;

    if (typeof inviterInfoParam === "string") {
        try {
            inviterInfo = JSON.parse(inviterInfoParam);
        } catch (error) {
            console.error("O parâmetro fornecido não é um JSON válido.", error);
            inviterInfo = null;
        }
    } else {
        inviterInfo = inviterInfoParam;
    }
    let myInviterInfo = document.querySelectorAll(".container_div_who_invited_me");
    let insert_invite_code = document.querySelectorAll(".insert_invite_code");
    let h3_tem_algum_codigo = document.querySelectorAll(".h3_tem_algum_codigo");
    let nameWhoInvitedMe = document.getElementById("nameWhoInvitedMe");
    let LTstarsWhoInvitedMe = document.getElementById("LTstarsWhoInvitedMe");
    let myInviterInitial = document.querySelectorAll(".profile_initials_from_invite");
    let textLabelInsiraEreceba = document.getElementById("textLabelInsiraEreceba");

    textLabelInsiraEreceba.textContent = "Você já recebeu:"
    h3_tem_algum_codigo.forEach(t => t.textContent = "Código de convite já inserido!");
    insert_invite_code.forEach(e => e.style.display = "none");
    myInviterInfo.forEach(e => e.style.display = "flex");

    myInviterInitial.forEach(text => text.textContent = getInitials(inviterInfo.userName, inviterInfo.userSurname))
    nameWhoInvitedMe.textContent = `${inviterInfo.userName} ${inviterInfo.userSurname}`;
    LTstarsWhoInvitedMe.textContent = parseInt(inviterInfo.LTStars).toLocaleString("pt-PT");
}

async function makeRequestToApplyInvitedCode(inviteCodeInserted, btn) {
    btn.textContent = "Aguarde...";
    btn.disabled = true

    const requestData = {
        inviteCodeInserted: inviteCodeInserted,
        userId: userId
    }

    try {
        const response = await fetch("http://localhost/LonyExtra/0/api/invitation/updateFieldWhoInvitedMe.php", {
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

        if (responseData.success) {
            hadeUIwhenInvitedCodeIsInserted(responseData.myInviterInfo)
            btn.style.backgroundColor = "green";
            showBoxAlert(responseData.message, "success");
        } else {
            showBoxAlert(responseData.message, "warning");
        }
    } catch (error) {
        console.error("Erro ao fazer a requisição:", error.message);
    }
    btn.textContent = "Enviar";
    btn.disabled = true
}

async function makeRequestToGetAllInvitedFriends(myReferralCode) {
    try {
        const response = await fetch(`http://localhost/LonyExtra/0/api/invitation/GetAllInvitedFriends.php?myReferralCode=${myReferralCode}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            }
        });

        if (!response.ok) {
            throw new Error(`Erro na requisição: ${response.status}`);
        }

        const result = await response.json();
        console.log("O AllInvited Friends retornou: ", result)

        return result;
    } catch (error) {
        console.error(error);
    }
}

function loadInvitedFriendsIntoTable(invitedFriendsList) {
    let empty_invited_friends = document.getElementById("empty_invited_friends");
    let table_invited_friends = document.querySelectorAll(".table_invited_friends");
    let tbody_invited_friends = document.getElementById("tbody_invited_friends");
    let p_SemAmigosConvidados = document.getElementById("p_SemAmigosConvidados");

    // Verifica se a lista de amigos está vazia
    if (!invitedFriendsList || invitedFriendsList.length === 0) {
        // Este usuário não tem amigos convidados
        p_SemAmigosConvidados.textContent = "Sem amigos convidados!";
        empty_invited_friends.style.display = "flex";
        table_invited_friends.forEach(t => t.style.display = "none");
        tbody_invited_friends.style.display = "none";
        return;
    }

    // Exibe a tabela e oculta a mensagem de "sem amigos"
    empty_invited_friends.style.display = "none";
    table_invited_friends.forEach(t => t.style.display = "block");
    tbody_invited_friends.style.display = "flex";

    // Limpa o conteúdo atual do tbody antes de preencher
    tbody_invited_friends.innerHTML = "";

    // Itera sobre a lista de amigos e cria as linhas dinamicamente
    invitedFriendsList.forEach(friend => {
        // Cria elementos HTML para a estrutura da tabela
        let tr = document.createElement("tr");

        let td = document.createElement("td");
        let divFriendInfo = document.createElement("div");
        divFriendInfo.classList.add("friend-info");

        let divInitials = document.createElement("div");
        divInitials.style.width = "40px";
        divInitials.style.height = "40px";
        divInitials.style.borderRadius = "50%";
        divInitials.style.display = "flex";
        divInitials.style.alignItems = "center";
        divInitials.style.justifyContent = "center";
        divInitials.style.backgroundColor = "#007bff";
        divInitials.style.color = "#fff";
        divInitials.style.fontWeight = "bold";
        divInitials.textContent = getInitials(friend.userName, friend.userSurname);

        let divContainerInfo = document.createElement("div");
        divContainerInfo.classList.add("container_friends_referred_img_name_email");

        let pName = document.createElement("p");
        pName.classList.add("invited_name");
        pName.innerHTML = `<strong>${friend.userName} ${friend.userSurname}</strong>`;

        let pEmail = document.createElement("p");
        pEmail.classList.add("invited_email");
        pEmail.textContent = "Nivel não disponível"; // Substitua pelo email se estiver disponível no retorno

        let divContainerStars = document.createElement("div");
        divContainerStars.classList.add("container_earned_stars_for_this_friend");

        let pStars = document.createElement("p");
        pStars.textContent = `✨ ${friend.userLTStars.toLocaleString()}`;

        // Monta a estrutura
        divContainerInfo.appendChild(pName);
        divContainerInfo.appendChild(pEmail);

        divContainerStars.appendChild(pStars);

        divFriendInfo.appendChild(divInitials);
        divFriendInfo.appendChild(divContainerInfo);
        divFriendInfo.appendChild(divContainerStars);

        td.appendChild(divFriendInfo);
        tr.appendChild(td);

        // Adiciona a linha à tabela
        tbody_invited_friends.appendChild(tr);
    });
}


async function handleInviteFriendsDialog() {
    async function waitForInvitation() {
        while (userInvitationInfo === null) {
            await new Promise(resolve => setTimeout(resolve, 1000));
            userInvitationInfo = await userInvitationInfo;
        }
        return userInvitationInfo;
    }

    waitForInvitation().then(async invite => {
        myInviteCode = invite.myReferralCode
        textInvitationLink.textContent = `https://lonyextra.com?invite=${myInviteCode}`;
        textInvitationCode.textContent = myInviteCode

        let copyIcons = document.querySelectorAll(".copy_my_invite_code_or_link");
        let invitation_code_input = document.getElementById("invitation_code_input");
        let btnVerifyInvitationCode = document.getElementById("btnVerifyInvitationCode");

        let allInvitedFriends = await makeRequestToGetAllInvitedFriends(myInviteCode);
        loadInvitedFriendsIntoTable(allInvitedFriends)

        copyIcons.forEach((icon, index) => {
            icon.addEventListener("click", () => {
                if (index === 0) {
                    copyToClipboard(`https://lonyextra.com?invite=${myInviteCode}`);
                } else {
                    copyToClipboard(myInviteCode);
                }
            });
        });

        if (invite.myInviterCode) {
            hadeUIwhenInvitedCodeIsInserted(myInviterInfo);
        }

        btnVerifyInvitationCode.addEventListener("click", () => {
            let codeInserted = invitation_code_input.value.trim()
            if (!codeInserted) {
                alert("Insira um código de convinte!")
                return;
            }

            if (codeInserted.length < 6) {
                alert("O código de convinte deve ter 6 dígitos!")
                return;
            }

            makeRequestToApplyInvitedCode(codeInserted, btnVerifyInvitationCode);
        });

    });

}

function handleGmailDialog() {
    waitForUserInfo().then(info => {
        btnSubmitEmailDoubt.addEventListener("click", (event) => {
            event.preventDefault();
            const btnSubmitEmailDoubt = document.getElementById('btnSubmitEmailDoubt');
            const titulo = document.getElementById('tituloGmailSuporte').value.trim();
            const descricao = document.getElementById('descricaoGmailSuporte').value.trim();


            const userName = userInfo.userName;
            const userSurname = userInfo.userSurname;
            const userEmail = userInfo.userEmail;

            if (!titulo) {
                alert('Por favor, preencha o título da dúvida.');
                return;
            }

            if (!descricao) {
                alert('Por favor, preencha a descrição da dúvida.');
                return;
            }

            const emailBody = `
            ${descricao}
            ------------------
            Nome de Cadastro: ${userName} ${userSurname}
            Email de Cadastro: ${userEmail}`;

            const gmailUrl = `https://mail.google.com/mail/?view=cm&fs=1&to=support@lonyextra.com&su=${encodeURIComponent(titulo)}&body=${encodeURIComponent(emailBody)}`;

            btnSubmitEmailDoubt.disabled = true
            btnSubmitEmailDoubt.textContent = "Aguarde...";
            window.open(gmailUrl, '_blank', 'noopener,noreferrer');
            setTimeout(() => {
                btnSubmitEmailDoubt.disabled = false
                btnSubmitEmailDoubt.textContent = "Enviar";
                descricao.value = "";
                descricao.value = "";
            });
        });


    });
}

function handleProfileDIalog() {
    waitForUserInfo().then(info => {
        let joinedDateElement = document.getElementById("joinedDate");
        let timeElapsedTextElement = document.getElementById("timeElapsedText");

        let userTimeZone = info.userTimeZone;
        let userJoinedAt = new Date(info.userJoinedAt);

        let currentTime = new Date(new Date().toLocaleString("en-US", { timeZone: userTimeZone }));

        let isToday = userJoinedAt.toLocaleDateString("pt-BR", { timeZone: userTimeZone }) ===
            currentTime.toLocaleDateString("pt-BR", { timeZone: userTimeZone });

        let yesterday = new Date(currentTime);
        yesterday.setDate(yesterday.getDate() - 1);
        let isYesterday = userJoinedAt.toLocaleDateString("pt-BR", { timeZone: userTimeZone }) ===
            yesterday.toLocaleDateString("pt-BR", { timeZone: userTimeZone });

        if (isToday) {
            joinedDateElement.textContent = "Hoje";
        } else if (isYesterday) {
            joinedDateElement.textContent = "Ontem";
        } else {
            let joinedDate = userJoinedAt.toLocaleDateString("pt-BR", {
                day: "2-digit",
                month: "long",
                year: "numeric",
                timeZone: userTimeZone
            });
            joinedDateElement.textContent = `em ${joinedDate}`;
        }

        let timeDifference = currentTime - userJoinedAt;

        let seconds = Math.floor(timeDifference / 1000);
        let minutes = Math.floor(seconds / 60);
        let hours = Math.floor(minutes / 60);
        let days = Math.floor(hours / 24);
        let months = Math.floor(days / 30.44);
        let years = Math.floor(months / 12);

        let timeElapsedText = "";
        if (isToday) {
            timeElapsedText = `${hours} hora${hours > 1 ? "s" : ""}`;
        } else if (isYesterday) {
            timeElapsedText = `${hours} hora${hours > 1 ? "s" : ""}`;
        } else {
            if (years > 0) {
                timeElapsedText = `${years} ano${years > 1 ? "s" : ""}`;
            } else if (months > 0) {
                timeElapsedText = `${months} mês${months > 1 ? "es" : ""}`;
            } else if (days > 0) {
                timeElapsedText = `${days} dia${days > 1 ? "s" : ""}`;
            } else if (hours > 0) {
                timeElapsedText = `${hours} hora${hours > 1 ? "s" : ""}`;
            } else if (minutes > 0) {
                timeElapsedText = `${minutes} minuto${minutes > 1 ? "s" : ""}`;
            } else {
                timeElapsedText = `${seconds} segundo${seconds > 1 ? "s" : ""}`;
            }
        }

        timeElapsedTextElement.textContent = timeElapsedText;
    });
}

function handleSignOutDIalog() {
    waitForUserInfo().then(_ => {
        const overlay = document.getElementById("overlay");
        let cancelLogout = document.getElementById("cancelLogout");
        let confirmLogout = document.getElementById("confirmLogout");

        cancelLogout.addEventListener("click", () => {
            overlay.style.display = "none";
            history.replaceState(null, "", location.pathname + location.search);
        });

        confirmLogout.addEventListener("click", () => {
            confirmLogout.disabled = true;
            confirmLogout.textContent = "Deslogando...";
            localStorage.clear();
            window.location.href = "../access/login.html";
        })

    });
}
