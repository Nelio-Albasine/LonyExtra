const hashToDialogData = {
    "#TodosLinks": { id: "dialog_all_links", title: "Todos os Links" },
    "#ConverterEstrelas": { id: "dialog_convert_stars", title: "Converter Estrelas" },
    "#ConvidarAmigos": { id: "dialog_convidar_amigos", title: "Convidar Amigos" },
    "#Sacar": { id: "dialog_sacar", title: "Sacar Saldo" },
    "#HistoricoSaques": { id: "dialog_historio_de_saques", title: "Histórico de Saques" },
    "#MinhasNotificacoes": { id: "dialog_minhas_notificacoes", title: "Minhas Notificações" },
    "#Instagram": { id: "dialog_instagram", title: "Instagram" },
    "#Telegram": { id: "dialog_telegram", title: "Telegram" },
    "#Gmail": { id: "dialog_gmail", title: "Gmail" },
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
const userId = "391f58325968d93b6778b9722f953bb063b44254d8e04109955c52b928ac9782";


document.addEventListener("DOMContentLoaded", () => {
    openEspecificDialog()
    closeDialogContainer();
    whenConvertStarsClicked();
    whenCashoutRevenueClicked();
});

function copyToClipboard(text) {
    navigator.clipboard.writeText(text);
    alert("ID copiado: " + text);
}

function openEspecificDialog(dialogIdHasHashFormat = null) {
    console.log("Dentro da funcao: openEspecificDialog() e o id passado é: ", dialogIdHasHashFormat);

    if (!dialogIdHasHashFormat) {
        let pageLoadedWithHash = window.location.hash
        dialogIdHasHashFormat = pageLoadedWithHash

        if (pageLoadedWithHash === "#home") {
            dialogs.forEach(dialog => (dialog.style.display = "none"));
            overlay.style.display = "none";
            const titleElement = document.querySelector("#dialog_title");
            if (titleElement) titleElement.textContent = "";
            return;
        }
    }


    const dialogs = document.querySelectorAll(".main_container_all_dialogs .the_dialog_container");
    const overlay = document.getElementById("overlay");
    const textDialogTitle = document.querySelector("#dialog_title");

    let targetDialogId = hashToDialogData[dialogIdHasHashFormat].id
    let dialogTitle = hashToDialogData[dialogIdHasHashFormat]?.title

    dialogs.forEach(dialog => {
        dialog.style.display = "none";
    });

    overlay.style.display = "flex";
    document.getElementById(targetDialogId).style.display = "flex";

    textDialogTitle.textContent = dialogTitle || "Desconhecido";

    handleActionsToEspecifiedHash(dialogIdHasHashFormat);
}

let chamadashandleActionsToEspecifiedHash = 1

function handleActionsToEspecifiedHash(dialogId) {
    console.log(`Funcao handleActionsToEspecifiedHash chamada ${chamadashandleActionsToEspecifiedHash++} vezes`)

    switch (dialogId) {
        case "#Home":
            console.log("Ação padrão: Home");
            document.querySelector('.drawer').classList.toggle('open');
            document.querySelector('.main-content').classList.toggle('drawer-open');
            break;
        case "#TodosLinks":
            //this menu is alread handled in GetDashBoardInf.js
            break;

        case "#ConverterEstrelas":
            handleConverterEstrelas();
            break;

        case "#ConvidarAmigos":
            console.log("Ação selecionada: Convidar Amigos");
            break;

        case "#Sacar":
            handleCashoutDialog();
            break;

        case "#HistoricoSaques":
            console.log(`Case #HistoricoSaques`)
            handleCashoutHistoryDialog();
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
            console.log("Ação selecionada: Gmail");
            break;

        case "#YouTube":
            console.log("Ação selecionada: YouTube");
            break;

        case "#Perfil":
            console.log("Ação selecionada: Perfil");
            break;

        case "#Logout":
            console.log("Ação selecionada: Logout");
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
        const response = await fetch("http://localhost/LonyExtra/0/Api/Cashout/ConvertStarsIntoReward.php", {
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
        icon.src = `../SRC/IMGs/${data.gatewayName == "paypal" ? "icon_paypal_256x256" : "icon_pix_240x240"}.png`;

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
        icon.src = `../SRC/IMGs/${data.metodo == "paypal" ? "icon_paypal_256x256" : "icon_pix_240x240"}.png`;


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
        const response = await fetch("http://localhost/LonyExtra/0/Api/Cashout/CashOutRevenue.php", {
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
        gift_gateway_icon.forEach(icon => icon.src = "../SRC/IMGs/cartoon_money.png");
        btnCashoutRevenue.textContent = "Efetuar Saque";
        btnCashoutRevenue.style.backgroundColor = "#707070";
        indexToCashOut = null;
        paymentMethodSelected = null;
        btnCashoutRevenue.disabled = false;
    }, 2000)
}


async function handleCashoutHistoryDialog() {
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
            methodImg.src = `../SRC/IMGs/${item.gatewayName == "paypal" ? "icon_paypal_256x256" : "icon_pix_240x240"}.png`;
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
        const response = await fetch(`http://localhost/LonyExtra/0/Api/Cashout/GetMyCashouts.php?userId=${userId}`, {
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





