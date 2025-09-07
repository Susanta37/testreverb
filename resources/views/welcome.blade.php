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
    
    <!-- Vite Assets -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <!-- Fallback for when Vite is not running - Load Echo manually -->
        <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.15.3/dist/echo.iife.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/pusher-js@8.4.0-rc2/dist/web/pusher.min.js"></script>
        <script>
            // Initialize Echo manually when Vite is not available
            if (typeof window.Echo === 'undefined') {
                window.Echo = new Echo({
                    broadcaster: 'reverb',
                    key: 'zpfmrgpl3p0bnd1zsewy',
                    wsHost: 'localhost',
                    wsPort: 4010,
                    wssPort: 4010,
                    forceTLS: false,
                    enabledTransports: ['ws', 'wss'],
                });
                console.log('✅ Echo initialized manually (fallback mode)');
            }
        </script>
    @endif
    
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

        .btn-secondary {
            background: rgba(100, 116, 139, 0.3);
        }

        .btn-secondary.active {
            background: linear-gradient(135deg, #60a5fa 0%, #34d399 100%);
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

        .websocket-status {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 20px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 8px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .status-indicator {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #22c55e;
            animation: pulse 2s infinite;
        }

        .status-indicator.disconnected {
            background: #ef4444;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
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
            <div class="websocket-status">
                <div class="status-indicator" id="wsIndicator"></div>
                <span id="wsStatus">Connecting...</span>
            </div>
        </div>

        <div class="chart-container">
            <div class="chart-wrapper">
                <canvas id="priceChart"></canvas>
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
                <div class="status-label">Last Update</div>
                <div class="status-value" id="lastUpdate">--</div>
            </div>
            <div class="status-item">
                <div class="status-label">Volume</div>
                <div class="status-value" id="volume">--</div>
            </div>
            <div class="status-item">
                <div class="status-label">High (24h)</div>
                <div class="status-value" id="high24h">--</div>
            </div>
            <div class="status-item">
                <div class="status-label">Low (24h)</div>
                <div class="status-value" id="low24h">--</div>
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
        // Ensure Chart.js is loaded before proceeding
        if (typeof Chart === 'undefined') {
            console.error('Chart.js failed to load from CDN!');
        } else {
            console.log('Chart.js loaded successfully:', Chart.version);
        }
        // Global variables
        let chart;
        let price = 40;
        let chartData = { prices: [], labels: [] };
        let originalData = { prices: [], labels: [] }; // Store original data for range switching
        let currentRange = '6m';

        // Initialize Chart.js with stable configuration (exact copy from graph.blade.php)
        function initializeChart() {
            const ctx = document.getElementById('priceChart').getContext('2d');
            
            // Ensure we have a clean context
            if (!ctx) {
                console.error('Canvas context not found!');
                return;
            }
            
            // Create gradient for line - ensuring it's created fresh each time
            const gradient = ctx.createLinearGradient(0, 0, 0, 400);
            gradient.addColorStop(0, 'rgba(96, 165, 250, 0.8)');
            gradient.addColorStop(0.5, 'rgba(52, 211, 153, 0.4)');
            gradient.addColorStop(1, 'rgba(52, 211, 153, 0)');
            
            console.log('Initializing chart with blue gradient...');
            
            // Destroy existing chart if it exists
            if (chart) {
                chart.destroy();
            }
            
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
            
            console.log('Chart initialized successfully with dataset:', chart.data.datasets[0]);
            
            // Force update the chart colors to ensure they're blue, not red
            chart.data.datasets[0].borderColor = '#60a5fa';
            chart.data.datasets[0].backgroundColor = gradient;
            chart.update('none');
        }
        
        // Update chart data without causing zoom
        function updateChartData(prices, labels, maintainScale = false) {
            if (!chart || !prices || prices.length === 0) return;
            
            // Only recalculate Y-axis bounds if NOT maintaining scale
            // This prevents zoom when adding real-time data
            if (!maintainScale) {
                const minVal = Math.min(...prices);
                const maxVal = Math.max(...prices);
                const padding = (maxVal - minVal) * 0.15; // 15% padding
                
                // Only update scale if values are valid
                if (isFinite(minVal) && isFinite(maxVal) && minVal !== maxVal) {
                    chart.options.scales.y.min = Math.max(0, minVal - padding);
                    chart.options.scales.y.max = maxVal + padding;
                    console.log('Updated Y-axis scale:', chart.options.scales.y.min, 'to', chart.options.scales.y.max);
                }
            } else {
                console.log('Maintaining current Y-axis scale to prevent zoom');
            }
            
            // Update data
            chart.data.labels = labels;
            chart.data.datasets[0].data = prices;
            
            // Force blue colors (prevent red line issue)
            chart.data.datasets[0].borderColor = '#60a5fa';
            if (chart.data.datasets[0].backgroundColor && typeof chart.data.datasets[0].backgroundColor === 'object') {
                // Keep gradient
            } else {
                // Recreate gradient if lost
                const ctx = chart.canvas.getContext('2d');
                const gradient = ctx.createLinearGradient(0, 0, 0, 400);
                gradient.addColorStop(0, 'rgba(96, 165, 250, 0.8)');
                gradient.addColorStop(0.5, 'rgba(52, 211, 153, 0.4)');
                gradient.addColorStop(1, 'rgba(52, 211, 153, 0)');
                chart.data.datasets[0].backgroundColor = gradient;
            }
            
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

        // WebSocket connection status
        function updateWebSocketStatus(connected) {
            const indicator = document.getElementById('wsIndicator');
            const status = document.getElementById('wsStatus');
            
            if (connected) {
                indicator.classList.remove('disconnected');
                status.textContent = 'Connected';
                document.getElementById('status').textContent = 'Ready';
            } else {
                indicator.classList.add('disconnected');
                status.textContent = 'Disconnected';
                document.getElementById('status').textContent = 'Disconnected';
            }
        }

        // Load prices for different time ranges
        function loadPrices(range) {
            if (range === currentRange && originalData.prices.length > 0) {
                return; // Don't reload same data unnecessarily
            }
            
            console.log('Loading prices for range:', range);
            document.getElementById('status').textContent = 'Loading...';
            
            // Update current range BEFORE making the request to prevent race conditions
            const previousRange = currentRange;
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
                    
                    // Use maintainScale = false for historical data to set proper Y-axis bounds
                    updateChartData(chartData.prices, chartData.labels, false);
                    document.getElementById('dataPoints').textContent = chartData.prices.length.toLocaleString();
                    
                    // Update status bar
                    if (chartData.prices.length > 0) {
                        const prices = chartData.prices;
                        const high = Math.max(...prices);
                        const low = Math.min(...prices);
                        
                        document.getElementById('high24h').textContent = '$' + high.toFixed(2);
                        document.getElementById('low24h').textContent = '$' + low.toFixed(2);
                        document.getElementById('volume').textContent = (prices.length * 1000).toLocaleString();
                    }
                    
                    document.getElementById('lastUpdate').textContent = new Date().toLocaleTimeString();
                    document.getElementById('status').textContent = 'Ready';
                })
                .catch(error => {
                    console.error('Error loading prices:', error);
                    document.getElementById('lastUpdate').textContent = 'Load error';
                    document.getElementById('status').textContent = 'Error';
                });
        }

        // Initialize everything when page loads
        document.addEventListener('DOMContentLoaded', function() {
            // Ensure no conflicting chart instances exist
            const existingCharts = Chart.getChart('priceChart');
            if (existingCharts) {
                console.log('Destroying existing chart instance...');
                existingCharts.destroy();
            }
            
            initializeChart();
            loadPrices(currentRange); // Load default range (6m)
            
            // Initialize WebSocket connection
            setTimeout(() => {
                initializeWebSocket();
            }, 1000);
        });
        
        function initializeWebSocket() {
            console.log('Initializing WebSocket connection...');
            
            if (typeof window.Echo === 'undefined') {
                console.error('Laravel Echo is not available.');
                updateWebSocketStatus(false);
                return;
            }
            
            try {
                // Add connection status listeners
                if (window.Echo.connector && window.Echo.connector.pusher) {
                    window.Echo.connector.pusher.connection.bind('connected', () => {
                        console.log('✅ WebSocket connected successfully!');
                        updateWebSocketStatus(true);
                    });
                    
                    window.Echo.connector.pusher.connection.bind('disconnected', () => {
                        console.log('🔥 WebSocket disconnected');
                        updateWebSocketStatus(false);
                    });
                }
                
                window.Echo.channel('test')
                    .listen('Tutorial', (event) => {
                        console.log('Received WebSocket event:', event, 'Current range:', currentRange);
                        
                        // Extract numeric value from the message
                        let value = null;
                        if (event.message && typeof event.message === 'string') {
                            const match = event.message.match(/\d+(\.\d+)?/);
                            if (match) {
                                value = parseFloat(match[0]);
                            }
                        }
                        
                        if (value !== null && !isNaN(value)) {
                            // Always update price display (top of page)
                            updatePriceDisplay(value, price);
                            price = value;
                            
                            // ONLY update chart data if we're viewing 1 Minute range
                            // This prevents zoom issues on other time ranges
                            if (currentRange === '1m') {
                                console.log('Adding real-time data point to 1m chart:', value);
                                chartData.prices.push(value);
                                chartData.labels.push(new Date().toLocaleTimeString());
                                
                                // Keep only last 50 points for 1m view
                                if (chartData.prices.length > 50) {
                                    chartData.prices.shift();
                                    chartData.labels.shift();
                                }
                                
                                // Update chart with maintained scale to prevent zoom
                                updateChartData(chartData.prices, chartData.labels, true);
                                document.getElementById('dataPoints').textContent = chartData.prices.length.toLocaleString();
                            } else {
                                console.log('Real-time data received but not updating chart (current range:', currentRange, ')');
                            }
                            
                            // Always update last update time
                            document.getElementById('lastUpdate').textContent = new Date().toLocaleTimeString();
                        }
                    })
                    .subscribed(() => {
                        console.log('✅ Successfully subscribed to test channel');
                        updateWebSocketStatus(true);
                    })
                    .error((error) => {
                        console.error('❌ WebSocket subscription error:', error);
                        updateWebSocketStatus(false);
                    });
                    
            } catch (error) {
                console.error('❌ Failed to initialize WebSocket:', error);
                updateWebSocketStatus(false);
            }
        }
    </script>
</body>
</html>
