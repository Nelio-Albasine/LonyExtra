document.addEventListener("DOMContentLoaded", async function () {
    const textUserRevenue = document.getElementById("the_available_revenue");
    const textUserPoints = document.getElementById("the_available_pints");
    const pointsToEarn = document.getElementById("pointsToEarn");

    const taskIndex = parseInt(localStorage.getItem("taskIndex"));
    console.log("index dp localStorage: ", taskIndex)

    const userPointsJson = loadUserPointsFromLocal();

    handlePointsToEarnText(taskIndex, pointsToEarn);

    textUserRevenue.textContent = userPointsJson.userRevenue.toLocaleString("pt-BR", { style: "currency", currency: "BRL" });
    textUserPoints.textContent = userPointsJson.userStars.toLocaleString("pt-PT");


});

function handlePointsToEarnText(index, text) {
    let points = null;

    switch (index) {
        case 0:
            points = 10;
            break;
        case 1:
            points = 20;
            break;
        case 2:
            points = 30;
            break;
        case 3:
            points = 50;
            break;
    }

    text.textContent = `+${points}`
}

function loadUserPointsFromLocal() {
    let userPoints = localStorage.getItem('userPoints');
    if (userPoints !== null) {
        return JSON.parse(userPoints);
    } else {
        alert("Erro 43");
    }
}