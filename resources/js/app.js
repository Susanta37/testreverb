import './bootstrap';
import Chart from 'chart.js/auto';

// document.addEventListener('DOMContentLoaded', () => {
//     if (window.Echo) {
//         window.Echo.channel('test')
//             .listen('Tutorial', (event) => {
//                 // Update the DOM with the received message
//                 const messageElement = document.getElementById('message');
//                 if (messageElement) {
//                     messageElement.innerText = event.message;
//                 } else {
//                     console.error('Element with ID "message" not found');
//                 }
//             })
//             .subscribed(() => {
//                 console.log('Subscribed to test channel');
//             });
//     } else {
//         console.error('Laravel Echo is not initialized');
//     }
// });
// Initialize Chart.js
document.addEventListener('DOMContentLoaded', () => {
    if (!window.Echo) {
        console.error('Laravel Echo is not initialized');
        return;
    }
    const ctx = document.getElementById('priceChart')?.getContext('2d');
    if (!ctx) {
        console.error('Canvas element with ID "priceChart" not found');
        return;
    }

    const priceData = [];
    const labels = [];
    const chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Price/Random Data',
                data: priceData,
                borderColor: '#F53003',
                backgroundColor: 'rgba(245, 48, 3, 0.2)',
                fill: false,
                tension: 0.1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: false,
                    suggestedMin: 0,
                    suggestedMax: 150,
                    title: { display: true, text: 'Value', color: '#fff' }
                },
                x: {
                    title: { display: true, text: 'Time', color: '#fff' }
                }
            },
            plugins: {
                legend: { labels: { color: '#fff' } }
            }
        }
    });

    // Listen for the 'Tutorial' event
    window.Echo.channel('test')
        .listen('Tutorial', (event) => {
            // Update message div
            const messageElement = document.getElementById('message');
            if (messageElement) {
                messageElement.innerText = event.message;
            }

            // Update chart
            const value = parseFloat(event.message.match(/\d+(\.\d+)?/)?.[0]);
            if (!isNaN(value)) {
                priceData.push(value);
                labels.push(new Date().toLocaleTimeString());
                if (priceData.length > 10) {
                    priceData.shift();
                    labels.shift();
                }
                chart.data.datasets[0].data = priceData;
                chart.data.labels = labels;
                chart.update();
            }
        })
        .subscribed(() => {
            console.log('Subscribed to test channel');
        });
});