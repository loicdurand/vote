export default async () => {

    if (document.querySelector('#stats-page') === null)
        return false;

    const { Chart } = await import('chart.js/auto');

    const // 
        voteDistributionChart = document.getElementById('voteDistributionChart'),
        voteDistributionData = voteDistributionChart.dataset.distribution,
        raw_data = voteDistributionData.split('|'), // ex: ["candidatA=12", "candidatB=10"]
        voteDistribution = raw_data.map(data => {
            const [candidat, votes] = data.split('=');
            return ({ candidat, votes });
        }); //

    // Graphique de répartition des votes (Donut)
    new Chart(voteDistributionChart as HTMLCanvasElement, {
        type: 'doughnut',
        data: {
            labels: voteDistribution.map(item => item.candidat),
            datasets: [{
                data: voteDistribution.map(item => item.votes),
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

    const // 
        dailyParticipationChart = document.getElementById('dailyParticipationChart'),
        dailyParticipationData = dailyParticipationChart.dataset.daylyparticipation,
        part_data = dailyParticipationData.split('|'), // ex: ["2025-09-10=2", "2025-09-11=6"]
        dailyParticipation = part_data.map(data => {
            const [date, votes] = data.split('=');
            return ({ date: (date.split('-')).reverse().join('/'), votes });
        }); //

    // Graphique de participation par heure (Barres)
    new Chart(dailyParticipationChart as HTMLCanvasElement, {
        type: 'bar',
        data: {
            labels: dailyParticipation.map(item => item.date),
            datasets: [{
                label: 'Nombre de votes',
                data: dailyParticipation.map(item => item.votes),
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
                    title: { display: true, text: 'Journées d\'ouverture du vote' }
                }
            }
        }
    });

};