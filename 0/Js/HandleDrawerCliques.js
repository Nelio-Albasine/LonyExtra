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
let imgCloseDialog = document.getElementById("img_close_dialog");


document.addEventListener("DOMContentLoaded", () => {
    window.addEventListener("hashchange", updateDialogsVisibility);

    updateDialogsVisibility();
    closeDialogContainer();

});

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
    console.log("Ação passada: ", dialogId);

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
            console.log("Ação selecionada: Converter estrelas");
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

    options.forEach(option => {
        option.addEventListener('click', () => {
            options.forEach(opt => opt.classList.remove('selected'));
            option.classList.add('selected');
        });
    });

}
