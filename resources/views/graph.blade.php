<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Price Graph</title>
    @vite(['resources/js/app.js'])
</head>
<body>
    <div id="app" class="p-6">
        <h1 class="text-3xl mb-4">Price Graph</h1>
        <canvas id="priceChart" width="400" height="200" class="mb-6"></canvas>
        <div class="space-x-2 mb-4">
            <button onclick="updatePrice('up')" class="px-4 py-2 bg-blue-500 text-white rounded">Up</button>
            <button onclick="updatePrice('down')" class="px-4 py-2 bg-blue-500 text-white rounded">Down</button>
            <button onclick="showSimulationInputs()" class="px-4 py-2 bg-green-500 text-white rounded">Start 5-min Simulation</button>
        </div>
        <div id="simulationInputs" class="hidden mb-4">
            <label for="minPrice" class="mr-2">Min Price:</label>
            <input id="minPrice" type="number" min="0" value="20" class="border px-2 py-1 mr-4">
            <label for="maxPrice" class="mr-2">Max Price:</label>
            <input id="maxPrice" type="number" min="0" value="60" class="border px-2 py-1 mr-4">
            <button onclick="startSimulation()" class="px-4 py-2 bg-green-500 text-white rounded">Confirm</button>
        </div>
        <div class="space-x-2 mb-4">
            <button onclick="loadPrices('today')" class="px-4 py-2 bg-gray-500 text-white rounded">Today</button>
            <button onclick="loadPrices('1m')" class="px-4 py-2 bg-gray-500 text-white rounded">1 Month</button>
            <button onclick="loadPrices('6m')" class="px-4 py-2 bg-gray-500 text-white rounded">6 Months</button>
            <button onclick="loadPrices('1y')" class="px-4 py-2 bg-gray-500 text-white rounded">1 Year</button>
            <button onclick="loadPrices('total')" class="px-4 py-2 bg-gray-500 text-white rounded">Total</button>
        </div>
        <div id="status" class="mb-2"></div>
        <div id="timer" class="mb-2"></div>
        <div id="direction" class="mb-2"></div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Initialize Chart.js
        const ctx = document.getElementById('priceChart').getContext('2d');
        let price = 40;
        const priceData = [price];
        const labels = [new Date().toLocaleTimeString()];
        let minPrice = 20;
        let maxPrice = 60;

        const chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Price',
                    data: priceData,
                    borderColor: '#F53003',
                    backgroundColor: 'rgba(245, 48, 3, 0.2)',
                    fill: false,
                    tension: 0.5
                }]
            },
            options: {
                animation: {
                    duration: 500,
                    easing: 'easeInOutQuad'
                },
                scales: {
                    y: {
                        min: 0,
                        max: 100,
                        title: { display: true, text: 'Price' }
                    },
                    x: {
                        title: { display: true, text: 'Time' }
                    }
                }
            }
        });

        function updatePrice(direction) {
            const statusElement = document.getElementById('status');
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

            if (!csrfToken) {
                statusElement.innerText = 'Error: CSRF token not found';
                console.error('CSRF token not found');
                return;
            }

            const oldPrice = price;
            if (direction === 'up') {
                price += 10;
            } else if (direction === 'down') {
                price -= 10;
            }
            price = Math.max(minPrice, Math.min(maxPrice, Math.round(price)));

            // Update direction
            const directionElement = document.getElementById('direction');
            if (directionElement) {
                directionElement.innerText = price > oldPrice ? 'Up ▲' : (price < oldPrice ? 'Down ▼' : 'No Change');
            }

            // Update chart
            priceData.push(price);
            labels.push(new Date().toLocaleTimeString());
            if (priceData.length > 10) {
                priceData.shift();
                labels.shift();
            }
            chart.data.datasets[0].data = priceData;
            chart.data.labels = labels;
            chart.update();

            // Send price to server
            statusElement.innerText = 'Sending price update...';
            fetch('/update-price', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ price: price })
            })
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(data => {
                statusElement.innerText = `Success: ${data.status}`;
                console.log('Server response:', data);
            })
            .catch(error => {
                statusElement.innerText = 'Error sending price update';
                console.error('Error sending price update:', error);
            });
        }

        function showSimulationInputs() {
            const simulationInputs = document.getElementById('simulationInputs');
            simulationInputs.classList.remove('hidden');
        }

        let simulationInterval;
        let timerInterval;
        let remainingTime = 300;

        function startSimulation() {
            const statusElement = document.getElementById('status');
            const timerElement = document.getElementById('timer');
            const minPriceInput = document.getElementById('minPrice');
            const maxPriceInput = document.getElementById('maxPrice');

            minPrice = parseInt(minPriceInput.value) || 20;
            maxPrice = parseInt(maxPriceInput.value) || 60;
            if (minPrice >= maxPrice) {
                statusElement.innerText = 'Error: Min price must be less than max price';
                return;
            }

            // Update y-axis bounds
            chart.options.scales.y.min = minPrice - 10;
            chart.options.scales.y.max = maxPrice + 10;
            chart.update();

            // Hide input fields
            document.getElementById('simulationInputs').classList.add('hidden');

            statusElement.innerText = 'Simulation started';

            // Update every 5 seconds
            simulationInterval = setInterval(() => {
                const oldPrice = price;

                // Random integer between minPrice and maxPrice
                const changeOptions = [-5, -3, -1, 0, 0, 1, 3, 5];
                const change = changeOptions[Math.floor(Math.random() * changeOptions.length)];
                price += change;
                price = Math.max(minPrice, Math.min(maxPrice, Math.round(price)));

                // Update direction
                const directionElement = document.getElementById('direction');
                if (directionElement) {
                    directionElement.innerText = price > oldPrice ? 'Up ▲' : (price < oldPrice ? 'Down ▼' : 'No Change');
                }

                // Update chart
                priceData.push(price);
                labels.push(new Date().toLocaleTimeString());
                if (priceData.length > 10) {
                    priceData.shift();
                    labels.shift();
                }
                chart.data.datasets[0].data = priceData;
                chart.data.labels = labels;
                chart.update();

                // Send to server for broadcast
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                fetch('/update-price', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ price: price })
                })
                .then(response => response.json())
                .then(data => {
                    console.log('Simulation update sent:', data);
                })
                .catch(error => {
                    console.error('Error in simulation update:', error);
                });
            }, 5000);

            // Timer countdown
            timerInterval = setInterval(() => {
                remainingTime -= 1;
                const minutes = Math.floor(remainingTime / 60);
                const seconds = remainingTime % 60;
                timerElement.innerText = `Time remaining: ${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;

                if (remainingTime <= 0) {
                    stopSimulation();
                }
            }, 1000);
        }

        function stopSimulation() {
            clearInterval(simulationInterval);
            clearInterval(timerInterval);
            const statusElement = document.getElementById('status');
            const timerElement = document.getElementById('timer');
            statusElement.innerText = 'Simulation ended';
            timerElement.innerText = '';
            remainingTime = 300;
        }

        function loadPrices(range) {
            fetch(`/prices?range=${range}`)
                .then(response => response.json())
                .then(data => {
                    chart.data.datasets[0].data = data.prices;
                    chart.data.labels = data.labels;
                    chart.update();
                })
                .catch(error => {
                    console.error('Error loading prices:', error);
                    document.getElementById('status').innerText = 'Error loading prices';
                });
        }

        // Load today's prices on page load
        loadPrices('today');
    </script>
</body>
</html>