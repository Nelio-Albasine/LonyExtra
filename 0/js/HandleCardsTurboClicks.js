document.addEventListener("DOMContentLoaded", () => {
    let card_option_name = document.querySelectorAll(".card_option_name");
    card_option_name.forEach(card => {
        card.addEventListener("click", () => {
            console.log("clicado")
            showBoxAlertMessage("Estamos fazendo algumas melhorias...", "warning");
        });
    });
});

function showBoxAlertMessage(message, type) {
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
        title.textContent = 'OPs!';
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
