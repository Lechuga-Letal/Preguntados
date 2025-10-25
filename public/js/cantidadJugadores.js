document.addEventListener("DOMContentLoaded", () => {
    const ctx = document.getElementById("jugadoresPorPeriodoChart");
    if (!ctx) return;

    const dia = parseInt(ctx.dataset.dia || 0);
    const semana = parseInt(ctx.dataset.semana || 0);
    const mes = parseInt(ctx.dataset.mes || 0);
    const anio = parseInt(ctx.dataset.anio || 0);

    new Chart(ctx, {
        type: "bar",
        data: {
            labels: ["Día", "Semana", "Mes", "Año"],
            datasets: [{
                label: "Cantidad de jugadores",
                data: [dia, semana, mes, anio],
                backgroundColor: [
                    "rgba(75, 192, 192, 0.7)",
                    "rgba(75, 192, 192, 0.7)",
                    "rgba(75, 192, 192, 0.7)",
                    "rgba(75, 192, 192, 0.7)",
                ],
                borderColor: [
                    "rgba(75, 192, 192, 0.7)",
                    "rgba(75, 192, 192, 0.7)",
                    "rgba(75, 192, 192, 0.7)",
                    "rgba(75, 192, 192, 0.7)",
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
});
