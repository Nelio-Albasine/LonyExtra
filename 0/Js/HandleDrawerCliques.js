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

let imgCloseDialog = document.getElementById("img_close_dialog");
const convertStarsBtn = document.getElementById("convertStarsBtn");

let conversionOption = null;
const userId = "391f58325968d93b6778b9722f953bb063b44254d8e04109955c52b928ac9782";


const textUserRevenue = document.querySelectorAll('.userRevenue');
const textUserLTRevenue = document.querySelectorAll('.userLTRevenue');
const textUserStars = document.querySelectorAll('.userStars');

document.addEventListener("DOMContentLoaded", () => {
    window.addEventListener("hashchange", updateDialogsVisibility);

    updateDialogsVisibility();
    closeDialogContainer();
    whenConvertStarsClicked();
    handleCashoutDialog();

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
        title.textContent = 'Warning!';
        button.textContent = 'Entendido';
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
        const giftCards = document.querySelectorAll('.gift-card');
      
        giftCards.forEach((card, index) => {
          card.addEventListener('click', () => {
            giftCards.forEach(c => c.classList.remove('selected'));
      
            card.classList.add('selected');
      
            const selectedValue = card.querySelector('p').textContent;
            console.log(`Valor selecionado: ${selectedValue} do index: ${index}`);
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
        const gift_gateway_icon = document.querySelectorAll('.gift_gateway_icon');

        select.textContent = optionText;
        icon.src = iconSrc;
        gift_gateway_icon.forEach(icon => {
            icon.src = iconSrc;
        })

        console.log(`Opção selecionada: ${optionText}`);
        toggleDropdown();
    }

    const customSelect = document.querySelector('.custom-select');
    customSelect.addEventListener('click', toggleDropdown);

    // Evento para selecionar uma opção
    const dropdownOptions = document.querySelectorAll('.dropdown > div');
    dropdownOptions.forEach(option => {
        option.addEventListener('click', () => {
            const optionText = option.querySelector('span').textContent;
            const iconSrc = option.querySelector('img').src;
            selectOption(optionText, iconSrc);
        });
    });

    // Evento para fechar o dropdown ao clicar fora
    document.addEventListener('click', (event) => {
        const container = document.querySelector('.custom-select-container');
        if (!container.contains(event.target)) {
            const dropdown = document.querySelector('.dropdown');
            dropdown.classList.remove('active');
        }
    });
}


