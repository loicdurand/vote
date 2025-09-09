export default async () => {

    if (document.querySelector('#stats-page') === null)
        return false;

    const { Chart } = await import('chart.js/auto');

    // Données d'exemple (à remplacer par un fetch à ton API)
    const stats = {
        invitedCount: 1000,
        voterCount: 750,
        voteDistribution: [
            { candidate: "Candidat A", votes: 300 },
            { candidate: "Candidat B", votes: 250 },
            { candidate: "Candidat C", votes: 150 },
            { candidate: "Candidat D", votes: 50 }
        ],
        dailyParticipation: [
            10, 5, 3, 2, 1, 5, 20, 30, 50, 70, 80, 90, // 0h à 11h
            100, 110, 90, 80, 70, 60, 50, 40, 30, 20, 10, 5 // 12h à 23h
        ]
    };

    // Graphique de répartition des votes (Donut)
    new Chart(document.getElementById('voteDistributionChart') as HTMLCanvasElement, {
        type: 'doughnut',
        data: {
            labels: stats.voteDistribution.map(item => item.candidate),
            datasets: [{
                data: stats.voteDistribution.map(item => item.votes),
                backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0'],
                borderColor: '#fff',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'top' }
            }
        }
    });

    // Graphique de participation par heure (Barres)
    new Chart(document.getElementById('dailyParticipationChart') as HTMLCanvasElement, {
        type: 'bar',
        data: {
            labels: Array.from({ length: 24 }, (_, i) => `${i}h`),
            datasets: [{
                label: 'Nombre de votes',
                data: stats.dailyParticipation,
                backgroundColor: '#36A2EB',
                borderColor: '#2E86C1',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    title: { display: true, text: 'Nombre de votes' }
                },
                x: {
                    title: { display: true, text: 'Heure de la journée' }
                }
            }
        }
    });

};