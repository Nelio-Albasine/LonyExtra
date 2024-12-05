document.addEventListener("DOMContentLoaded", function () {
    let btnResetPassword = document.getElementById("btnResetPassword")
    let inputEmailToResetPassword = document.getElementById("inputEmailToResetPassword")

    btnResetPassword.addEventListener("click", async (event) => {
        event.preventDefault();
        let value = inputEmailToResetPassword.value.trim();

        if (!value) {
            alert( "Por favor, insira o email que você usou ao criar sua conta.");
            return;
        }

        let response = await tryToSendEmailToResetPassword(value);

    });
});

async function tryToSendEmailToResetPassword(email){

}