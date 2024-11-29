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
    window.addEventListener("hashchange", updateDialogsVisibility);

    updateDialogsVisibility();
    closeDialogContainer();
    handleCashoutDialog();

    whenConvertStarsClicked();
    whenCashoutRevenueClicked();
});


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

function openEspecificDialog(dialogId) {
    const dialogs = document.querySelectorAll(".main_container_all_dialogs .the_dialog_container");
    const overlay = document.getElementById("overlay");

    dialogs.forEach(dialog => {
        dialog.style.display = dialog.id === dialogId ? "block" : "none";
    });

    const hashEntry = Object.entries(hashToDialogData).find(([, data]) => data.id === dialogId);
    if (hashEntry) {
        const [hash] = hashEntry;
        window.location.hash = hash;

        const titleElement = document.querySelector("#dialog_title");
        if (titleElement) {
            titleElement.textContent = hashToDialogData[hash]?.title || "Desconhecido";
        }

        overlay.style.display = "flex";
    } else {
        closeDialogContainer();
    }

    console.log("Ação selecionada:", dialogId);

    handleDialogActions(dialogId);
}

function handleDialogActions(dialogId) {
    switch (dialogId) {
        case "#Home":
            console.log("Ação padrão: Home");
            document.querySelector('.drawer').classList.toggle('open');
            document.querySelector('.main-content').classList.toggle('drawer-open');
            break;
        case "#TodosLinks":
            console.log("Ação selecionada: Todos os Links");
            break;

        case "#ConverterEstrelas":
            handleConverterEstrelas();
            break;

        case "#ConvidarAmigos":
            console.log("Ação selecionada: Convidar Amigos");
            break;

        case "#Sacar":
            console.log("Ação selecionada: Sacar");
            break;

        case "#HistoricoSaques":
            console.log("Ação selecionada: Histórico de Saques");
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

function updateDialogsVisibility() {
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
        "#Logout": { id: "dialog_logout", title: "Logout" }
    };

    const hash = window.location.hash || "";
    const overlay = document.getElementById("overlay");
    const dialogs = document.querySelectorAll(".main_container_all_dialogs .the_dialog_container");

    handleDialogActions(hash);


    // Gerenciar visibilidade com base no hash
    if (hash === "#home") {
        dialogs.forEach(dialog => (dialog.style.display = "none"));
        overlay.style.display = "none";
        const titleElement = document.querySelector("#dialog_title");
        if (titleElement) titleElement.textContent = "";
        return;
    }

    const dialogData = hashToDialogData[hash] || null;
    dialogs.forEach(dialog => {
        dialog.style.display = dialogData && dialog.id === dialogData.id ? "block" : "none";
    });

    overlay.style.display = dialogData ? "flex" : "none"; // Mostra/oculta overlay

    const titleElement = document.querySelector("#dialog_title");
    if (titleElement) {
        titleElement.textContent = dialogData?.title || "Desconhecido";
    }
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

function handleCashoutDialog(l) {
    function handleGiftCardSelection() {
        giftCards.forEach((card, index) => {
            card.addEventListener('click', () => {
                giftCards.forEach(c => c.classList.remove('selected'));
                card.classList.add('selected');
                btnCashoutRevenue.style.backgroundColor = "#043277";
                const selectedValue = card.querySelector('p').textContent;
                indexToCashOut = index;
            });
        });
    }

    handleGiftCardSelection();


    function toggleDropdown() {
        const dropdown = document.querySelector('.dropdown');
        dropdown.classList.toggle('active');
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


function showSuccessDialogCashout(data) {
    console.log(`dados recebidos: `, data)
    const closeSuccessCashoutDialog = document.getElementById("closeSuccessCashoutDialog");
    const success_cashout_overlay = document.getElementById("success_cashout_overlay");

    success_cashout_overlay.style.display = "flex";

    const methodElement = document.querySelector(".details .detail-row:nth-child(1) .span_mutable_texts");
    const amountElement = document.querySelector(".details .detail-row:nth-child(2) .span_mutable_texts");
    const nameElement = document.querySelector(".details .detail-row:nth-child(3) .span_mutable_texts");
    const addressElement = document.querySelector(".details .detail-row:nth-child(4) .span_mutable_texts");
    const transactionIdElement = document.querySelector(".details .detail-row:nth-child(5) .span_mutable_texts");
    const statusElement = document.querySelector(".details .detail-row:nth-child(6) .status-pending");

    methodElement.textContent = data.metodo || "Método desconhecido";
    amountElement.textContent = `💰 ${data.amountCashedOut.toLocaleString("pt-BR", { style: "currency", currency: "BRL" })}` || "Valor não informado";
    nameElement.textContent = data.userPaymentName || "Nome não informado";
    addressElement.textContent = data.userPaymentAddress || "Endereço não informado";
    transactionIdElement.textContent = data.cashOutId || "ID não informado";

    statusElement.textContent = "Pendente";
    statusElement.classList.add("status-pending");
    statusElement.classList.remove("status-success");

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
        btnCashoutRevenue.disabled = false;
    }, 2000)
}

