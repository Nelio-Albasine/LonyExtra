document.addEventListener("DOMContentLoaded", () => {
    // Dados fictícios de saques por mês
    const labels = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];

    // Dados para cada método de saque
    const data = {
        labels: labels,
        datasets: [
            {
                label: "Pix",
                data: [120, 200, 150, 220, 300, 350, 400, 450, 500, 550, 600, 650], // Quantidade de saques
                borderColor: "rgba(75, 192, 192, 1)",
                backgroundColor: "rgba(75, 192, 192, 0.2)",
                tension: 0.4, // Curvatura da linha
            },
            {
                label: "PayPal",
                data: [80, 100, 90, 150, 200, 220, 240, 260, 280, 300, 320, 340],
                borderColor: "rgba(255, 99, 132, 1)",
                backgroundColor: "rgba(255, 99, 132, 0.2)",
                tension: 0.4,
            },
            {
                label: "Binance",
                data: [60, 70, 50, 90, 100, 120, 140, 160, 180, 200, 220, 240],
                borderColor: "rgba(54, 162, 235, 1)",
                backgroundColor: "rgba(54, 162, 235, 0.2)",
                tension: 0.4,
            },
            {
                label: "M-Pesa",
                data: [30, 40, 35, 50, 60, 70, 80, 90, 100, 110, 120, 130],
                borderColor: "rgba(255, 206, 86, 1)",
                backgroundColor: "rgba(255, 206, 86, 0.2)",
                tension: 0.4,
            },
            {
                label: "E-Mola",
                data: [20, 25, 30, 40, 50, 55, 60, 65, 70, 75, 80, 85],
                borderColor: "rgba(153, 102, 255, 1)",
                backgroundColor: "rgba(153, 102, 255, 0.2)",
                tension: 0.4,
            },
        ],
    };

    // Configuração do gráfico
    const config = {
        type: "line",
        data: data,
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: "top",
                },
                title: {
                    display: true,
                    text: "Uso Mensal dos Métodos de Saque",
                },
            },
            scales: {
                x: {
                    title: {
                        display: true,
                        text: "Mês",
                    },
                },
                y: {
                    title: {
                        display: true,
                        text: "Quantidade de Saques",
                    },
                    beginAtZero: true,
                },
            },
        },
    };

    // Renderizar o gráfico
    const ctx = document.getElementById("withdrawalChart").getContext("2d");
    new Chart(ctx, config);
});