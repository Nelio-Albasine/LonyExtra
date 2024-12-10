const ctx = document.getElementById('performanceChart').getContext('2d');

// Configuração dos dados
const data = {
    labels: ['01', '02', '03', '04', '05', '06', '07'], // Dias do mês
    datasets: [
        {
            label: 'Este mês',
            data: [8, 7, 7, 9, 10, 6, 8], // Horas de trabalho (exemplo)
            borderColor: '#4F86F5',
            backgroundColor: 'rgba(79, 134, 245, 0.1)',
            tension: 0.4,
            fill: true,
        },
        {
            label: 'Mês passado',
            data: [6, 5, 6, 8, 9, 5, 7],
            borderColor: '#F58D4F',
            backgroundColor: 'rgba(245, 141, 79, 0.1)',
            tension: 0.4,
            fill: true,
        }
    ]
};

// Configuração do gráfico
const config = {
    type: 'line',
    data: data,
    options: {
        responsive: true,
        plugins: {
            tooltip: {
                callbacks: {
                    label: function (context) {
                        return `${context.dataset.label}: ${context.raw}h`;
                    }
                }
            },
            legend: {
                position: 'top',
            }
        },
        scales: {
            x: {
                grid: {
                    display: false,
                }
            },
            y: {
                beginAtZero: true,
                max: 12,
                ticks: {
                    stepSize: 2,
                    callback: (value) => `${value}h`,
                }
            }
        }
    }
};

// Renderizar o gráfico
new Chart(ctx, config);
