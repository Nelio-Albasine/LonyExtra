const userId = "391f58325968d93b6778b9722f953bb063b44254d8e04109955c52b928ac9782";

if (userId != "391f58325968d93b6778b9722f953bb063b44254d8e04109955c52b928ac9782" || userId != "db8a9d9fe0896ddd2438f532243cdf340a1e148815891ed1e0bf139f29b8159e") {
    window.location.href = "https://lonyextra.com/";
}


document.addEventListener("DOMContentLoaded", async function () {
    const totalUsers = document.getElementById("totalUsers");
    const totalCashouts = document.getElementById("totalCashouts");
    const totalLTrevenue = document.getElementById("totalLTrevenue");
    const totalRevenue = document.getElementById("totalRevenue");


    let response = await requestToGetDashAdmInfo();
    let dashInfo = response.data
    console.log("Dash info: ", dashInfo)

    totalUsers.textContent = Number(dashInfo.totalUsers)
        .toLocaleString('pt-BR')
        .replace(/,/g, ' ');

    totalCashouts.textContent = Number(dashInfo.totalCashouts)
        .toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });

    totalRevenue.textContent = Number(dashInfo.userRevenue)
        .toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });

    totalLTrevenue.textContent = Number(dashInfo.userLTRevenue)
        .toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });

});

async function requestToGetDashAdmInfo() {
    try {
        const response = await fetch(`http://localhost/LonyExtra/adm/api/GetDashFrontInfo.php`, {
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
        console.error(error);
    }
}

