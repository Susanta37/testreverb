<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Price Graph - Trading Interface</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: #ffffff;
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h1 {
            font-size: 32px;
            font-weight: 700;
            background: linear-gradient(135deg, #60a5fa 0%, #34d399 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 10px;
        }

        .price-display {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .chart-container {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 30px;
            margin-bottom: 30px;
            backdrop-filter: blur(10px);
        }

        .chart-wrapper {
            position: relative;
            height: 400px;
            margin-bottom: 20px;
        }

        .controls {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 20px;
            justify-content: center;
        }

        .btn-group {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            font-size: 14px;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.1);
            color: #ffffff;
        }

        .btn:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        }

        .btn-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }

        .btn-danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        }

        .btn-secondary {
            background: rgba(100, 116, 139, 0.3);
        }

        .btn-secondary.active {
            background: linear-gradient(135deg, #60a5fa 0%, #34d399 100%);
        }

        .simulation-inputs {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .simulation-inputs.hidden {
            display: none;
        }

        .input-group {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
        }

        .input-group label {
            min-width: 100px;
            color: #94a3b8;
            font-weight: 500;
        }

        .input-group input {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 6px;
            padding: 8px 12px;
            color: #ffffff;
            font-size: 14px;
            width: 150px;
        }

        .input-group input:focus {
            outline: none;
            border-color: #60a5fa;
            box-shadow: 0 0 0 2px rgba(96, 165, 250, 0.2);
        }

        .status-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            padding: 15px 25px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            flex-wrap: wrap;
            gap: 15px;
        }

        .status-item {
            text-align: center;
        }

        .status-label {
            font-size: 12px;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }

        .status-value {
            font-size: 16px;
            font-weight: 600;
            color: #ffffff;
        }

        .direction {
            font-size: 18px;
            font-weight: 600;
        }

        .direction.up {
            color: #10b981;
        }

        .direction.down {
            color: #ef4444;
        }

        @media (max-width: 768px) {
            .controls {
                flex-direction: column;
            }
            
            .btn-group {
                flex-wrap: wrap;
                justify-content: center;
            }
            
            .status-bar {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Price Graph - Trading Interface</h1>
            <div class="price-display">
                Current Price: <span id="currentPriceDisplay">$40.00</span>
                <span id="direction" class="direction">-</span>
            </div>
        </div>

        <div class="chart-container">
            <div class="chart-wrapper">
                <canvas id="priceChart"></canvas>
            </div>
            
            <!-- Manual Price Controls -->
            <div class="controls">
                <div class="btn-group">
                    <button onclick="updatePrice('up')" class="btn btn-success">Price Up (+$10)</button>
                    <button onclick="updatePrice('down')" class="btn btn-danger">Price Down (-$10)</button>
                    <button onclick="showSimulationInputs()" class="btn btn-primary">Start Simulation</button>
                </div>
            </div>
            
            <!-- Simulation Inputs -->
            <div id="simulationInputs" class="simulation-inputs hidden">
                <h3 style="margin-bottom: 15px; color: #60a5fa;">Simulation Settings</h3>
                <div class="input-group">
                    <label for="minPrice">Min Price:</label>
                    <input id="minPrice" type="number" min="0" value="20" step="1">
                </div>
                <div class="input-group">
                    <label for="maxPrice">Max Price:</label>
                    <input id="maxPrice" type="number" min="0" value="60" step="1">
                </div>
                <div class="btn-group">
                    <button onclick="startSimulation()" class="btn btn-success">Start 5-min Simulation</button>
                    <button onclick="stopSimulation()" class="btn btn-danger">Stop Simulation</button>
                </div>
            </div>

            <!-- Time Range Controls -->
            <div class="controls">
                <div class="btn-group">
                    <button onclick="loadPrices('1m')" class="btn btn-secondary" data-range="1m">1 Minute</button>
                    <button onclick="loadPrices('1w')" class="btn btn-secondary" data-range="1w">1 Week</button>
                    <button onclick="loadPrices('today')" class="btn btn-secondary" data-range="today">Today</button>
                    <button onclick="loadPrices('6m')" class="btn btn-secondary active" data-range="6m">6 Months</button>
                    <button onclick="loadPrices('1y')" class="btn btn-secondary" data-range="1y">1 Year</button>
                    <button onclick="loadPrices('5y')" class="btn btn-secondary" data-range="5y">5 Years</button>
                    <button onclick="loadPrices('total')" class="btn btn-secondary" data-range="total">All Data</button>
                </div>
            </div>
        </div>

        <!-- Status Bar -->
        <div class="status-bar">
            <div class="status-item">
                <div class="status-label">Status</div>
                <div class="status-value" id="status">Ready</div>
            </div>
            <div class="status-item">
                <div class="status-label">Timer</div>
                <div class="status-value" id="timer">-</div>
            </div>
            <div class="status-item">
                <div class="status-label">Data Points</div>
                <div class="status-value" id="dataPoints">0</div>
            </div>
            <div class="status-item">
                <div class="status-label">Range</div>
                <div class="status-value" id="currentRange">6 Months</div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Global variables
        let chart;
        let price = 40;
        let chartData = { prices: [], labels: [] };
        let originalData = { prices: [], labels: [] }; // Store original data for range switching
        let minPrice = 20;
        let maxPrice = 60;
        let currentRange = '6m';
        let isSimulationRunning = false;
        let simulationInterval;
        let timerInterval;
        let remainingTime = 300;

        // Initialize Chart.js with stable configuration
        function initializeChart() {
            const ctx = document.getElementById('priceChart').getContext('2d');
            
            // Create gradient for line
            const gradient = ctx.createLinearGradient(0, 0, 0, 400);
            gradient.addColorStop(0, 'rgba(96, 165, 250, 0.8)');
            gradient.addColorStop(0.5, 'rgba(52, 211, 153, 0.4)');
            gradient.addColorStop(1, 'rgba(52, 211, 153, 0)');
            
            chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Price',
                        data: [],
                        borderColor: '#60a5fa',
                        backgroundColor: gradient,
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 0,
                        pointHoverRadius: 6,
                        pointHoverBackgroundColor: '#60a5fa',
                        pointHoverBorderColor: '#ffffff',
                        pointHoverBorderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    },
                    animation: {
                        duration: 0 // Disable animations to prevent zoom issues
                    },
                    scales: {
                        y: {
                            type: 'linear',
                            position: 'right',
                            beginAtZero: false,
                            grid: {
                                color: 'rgba(255, 255, 255, 0.1)',
                                drawBorder: false
                            },
                            ticks: {
                                color: '#94a3b8',
                                padding: 10,
                                callback: function(value) {
                                    return '$' + value.toFixed(2);
                                }
                            },
                            title: {
                                display: false
                            }
                        },
                        x: {
                            grid: {
                                color: 'rgba(255, 255, 255, 0.05)',
                                drawBorder: false
                            },
                            ticks: {
                                color: '#94a3b8',
                                maxTicksLimit: 10
                            },
                            title: {
                                display: false
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            enabled: true,
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleColor: '#ffffff',
                            bodyColor: '#94a3b8',
                            borderColor: '#60a5fa',
                            borderWidth: 1,
                            cornerRadius: 8,
                            displayColors: false,
                            callbacks: {
                                label: function(context) {
                                    return 'Price: $' + context.parsed.y.toFixed(2);
                                }
                            }
                        }
                    }
                }
            });
        }
        
        // Update chart data without causing zoom
        function updateChartData(prices, labels, maintainScale = false) {
            if (!chart) return;
            
            // Calculate Y-axis bounds with padding if not maintaining scale
            if (!maintainScale && prices.length > 0) {
                const minVal = Math.min(...prices);
                const maxVal = Math.max(...prices);
                const padding = (maxVal - minVal) * 0.15; // 15% padding
                
                chart.options.scales.y.min = Math.max(0, minVal - padding);
                chart.options.scales.y.max = maxVal + padding;
            }
            
            // Update data
            chart.data.labels = labels;
            chart.data.datasets[0].data = prices;
            
            // Update chart without animation to prevent zoom
            chart.update('none');
        }
        
        // Update active button style
        function updateActiveButton(selectedRange) {
            document.querySelectorAll('[data-range]').forEach(btn => {
                btn.classList.remove('active');
                if (btn.getAttribute('data-range') === selectedRange) {
                    btn.classList.add('active');
                }
            });
            
            const rangeNames = {
                '1m': '1 Minute',
                '1w': '1 Week', 
                'today': 'Today',
                '6m': '6 Months',
                '1y': '1 Year',
                '5y': '5 Years',
                'total': 'All Data'
            };
            document.getElementById('currentRange').textContent = rangeNames[selectedRange] || selectedRange;
        }
        
        // Update price display
        function updatePriceDisplay(newPrice, oldPrice = null) {
            document.getElementById('currentPriceDisplay').textContent = '$' + newPrice.toFixed(2);
            
            const directionElement = document.getElementById('direction');
            if (oldPrice !== null && newPrice !== oldPrice) {
                const isUp = newPrice > oldPrice;
                directionElement.textContent = isUp ? 'Up ↗' : 'Down ↘';
                directionElement.className = 'direction ' + (isUp ? 'up' : 'down');
            } else {
                directionElement.textContent = '-';
                directionElement.className = 'direction';
            }
        }

        // Manual price update function
        function updatePrice(direction) {
            const statusElement = document.getElementById('status');
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

            if (!csrfToken) {
                statusElement.textContent = 'Error: CSRF token not found';
                return;
            }

            const oldPrice = price;
            if (direction === 'up') {
                price += 10;
            } else if (direction === 'down') {
                price -= 10;
            }
            price = Math.max(5, Math.min(200, Math.round(price))); // Reasonable bounds

            updatePriceDisplay(price, oldPrice);

            // For live updates, only update if we're viewing real-time data (1m range)
            if (currentRange === '1m') {
                // Add to current data
                chartData.prices.push(price);
                chartData.labels.push(new Date().toLocaleTimeString());
                
                // Keep only last 50 points for 1m view
                if (chartData.prices.length > 50) {
                    chartData.prices.shift();
                    chartData.labels.shift();
                }
                
                updateChartData(chartData.prices, chartData.labels, true); // Maintain scale for smooth updates
            }

            // Send to server
            statusElement.textContent = 'Updating...';
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
                statusElement.textContent = 'Updated successfully';
                setTimeout(() => statusElement.textContent = 'Ready', 2000);
            })
            .catch(error => {
                statusElement.textContent = 'Update failed';
                console.error('Error:', error);
            });
        }

        // Show simulation inputs
        function showSimulationInputs() {
            document.getElementById('simulationInputs').classList.remove('hidden');
        }

        // Start simulation
        function startSimulation() {
            const statusElement = document.getElementById('status');
            const timerElement = document.getElementById('timer');
            const minPriceInput = document.getElementById('minPrice');
            const maxPriceInput = document.getElementById('maxPrice');

            minPrice = parseInt(minPriceInput.value) || 20;
            maxPrice = parseInt(maxPriceInput.value) || 60;
            
            if (minPrice >= maxPrice) {
                statusElement.textContent = 'Error: Min price must be less than max price';
                return;
            }

            // Hide input fields
            document.getElementById('simulationInputs').classList.add('hidden');

            statusElement.textContent = 'Simulation running';
            isSimulationRunning = true;
            remainingTime = 300;

            // Update every 5 seconds
            simulationInterval = setInterval(() => {
                if (!isSimulationRunning) return;
                
                const oldPrice = price;
                const changeOptions = [-5, -3, -1, 0, 0, 1, 3, 5];
                const change = changeOptions[Math.floor(Math.random() * changeOptions.length)];
                price += change;
                price = Math.max(minPrice, Math.min(maxPrice, Math.round(price)));

                updatePriceDisplay(price, oldPrice);

                // Update chart if viewing real-time
                if (currentRange === '1m') {
                    chartData.prices.push(price);
                    chartData.labels.push(new Date().toLocaleTimeString());
                    
                    if (chartData.prices.length > 50) {
                        chartData.prices.shift();
                        chartData.labels.shift();
                    }
                    
                    updateChartData(chartData.prices, chartData.labels, true);
                }

                // Send to server
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                if (csrfToken) {
                    fetch('/update-price', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({ price: price })
                    })
                    .then(response => response.json())
                    .then(data => console.log('Simulation update sent'))
                    .catch(error => console.error('Simulation error:', error));
                }
            }, 5000);

            // Timer countdown
            timerInterval = setInterval(() => {
                remainingTime -= 1;
                const minutes = Math.floor(remainingTime / 60);
                const seconds = remainingTime % 60;
                timerElement.textContent = `${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;

                if (remainingTime <= 0) {
                    stopSimulation();
                }
            }, 1000);
        }

        // Stop simulation
        function stopSimulation() {
            clearInterval(simulationInterval);
            clearInterval(timerInterval);
            isSimulationRunning = false;
            
            document.getElementById('status').textContent = 'Simulation stopped';
            document.getElementById('timer').textContent = '-';
            
            setTimeout(() => {
                document.getElementById('status').textContent = 'Ready';
            }, 2000);
        }

        // Load prices for different time ranges
        function loadPrices(range) {
            if (range === currentRange && originalData.prices.length > 0) {
                return; // Don't reload same data unnecessarily
            }
            
            const statusElement = document.getElementById('status');
            statusElement.textContent = 'Loading...';
            
            currentRange = range;
            updateActiveButton(range);

            fetch(`/prices?range=${range}`)
                .then(response => {
                    if (!response.ok) throw new Error('Failed to load data');
                    return response.json();
                })
                .then(data => {
                    // Store original data
                    originalData = {
                        prices: data.prices || [],
                        labels: data.labels || []
                    };
                    
                    // Update chart data
                    chartData = { ...originalData };
                    
                    if (chartData.prices.length > 0) {
                        // Update price display with latest price
                        const latestPrice = chartData.prices[chartData.prices.length - 1];
                        const previousPrice = chartData.prices.length > 1 ? chartData.prices[chartData.prices.length - 2] : null;
                        updatePriceDisplay(latestPrice, previousPrice);
                        price = latestPrice; // Update current price
                    }
                    
                    updateChartData(chartData.prices, chartData.labels);
                    document.getElementById('dataPoints').textContent = chartData.prices.length.toLocaleString();
                    statusElement.textContent = 'Ready';
                })
                .catch(error => {
                    console.error('Error loading prices:', error);
                    statusElement.textContent = 'Load error';
                    setTimeout(() => statusElement.textContent = 'Ready', 3000);
                });
        }

        // Initialize everything when page loads
        document.addEventListener('DOMContentLoaded', function() {
            initializeChart();
            loadPrices(currentRange); // Load default range (6m)
        });
    </script>
</body>
</html>
