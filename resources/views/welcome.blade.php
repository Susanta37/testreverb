<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>TradingView Pro - {{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        
        <!-- Chart.js -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        
        <!-- Styles / Scripts -->
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <style>
                * {
                    margin: 0;
                    padding: 0;
                    box-sizing: border-box;
                }

                body {
                    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
                    background: linear-gradient(135deg, #0d1421 0%, #1a202c 100%);
                    color: #ffffff;
                    min-height: 100vh;
                    overflow-x: hidden;
                }

                .trading-container {
                    min-height: 100vh;
                    background: linear-gradient(135deg, #0d1421 0%, #1a202c 100%);
                    padding: 20px;
                }

                .header {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    margin-bottom: 30px;
                    padding: 20px 30px;
                    background: rgba(255, 255, 255, 0.05);
                    border-radius: 12px;
                    border: 1px solid rgba(255, 255, 255, 0.1);
                    backdrop-filter: blur(10px);
                }

                .header h1 {
                    font-size: 28px;
                    font-weight: 700;
                    background: linear-gradient(135deg, #60a5fa 0%, #34d399 100%);
                    -webkit-background-clip: text;
                    -webkit-text-fill-color: transparent;
                    background-clip: text;
                }

                .price-info {
                    display: flex;
                    align-items: center;
                    gap: 20px;
                }

                .current-price {
                    font-size: 32px;
                    font-weight: 700;
                    color: #22c55e;
                }

                .price-change {
                    display: flex;
                    align-items: center;
                    gap: 8px;
                    padding: 8px 16px;
                    border-radius: 8px;
                    font-weight: 600;
                    font-size: 14px;
                }

                .price-change.positive {
                    background: rgba(34, 197, 94, 0.2);
                    color: #22c55e;
                }

                .price-change.negative {
                    background: rgba(239, 68, 68, 0.2);
                    color: #ef4444;
                }

                .chart-container {
                    background: rgba(255, 255, 255, 0.05);
                    border-radius: 16px;
                    border: 1px solid rgba(255, 255, 255, 0.1);
                    padding: 30px;
                    margin-bottom: 30px;
                    backdrop-filter: blur(10px);
                }

                .chart-controls {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    margin-bottom: 25px;
                    flex-wrap: wrap;
                    gap: 20px;
                }

                .time-filters {
                    display: flex;
                    gap: 8px;
                    background: rgba(255, 255, 255, 0.05);
                    padding: 6px;
                    border-radius: 10px;
                    border: 1px solid rgba(255, 255, 255, 0.1);
                }

                .time-btn {
                    padding: 10px 20px;
                    border: none;
                    background: transparent;
                    color: #94a3b8;
                    border-radius: 6px;
                    cursor: pointer;
                    font-weight: 500;
                    font-size: 13px;
                    transition: all 0.3s ease;
                    text-transform: uppercase;
                    letter-spacing: 0.5px;
                }

                .time-btn:hover {
                    background: rgba(255, 255, 255, 0.1);
                    color: #ffffff;
                }

                .time-btn.active {
                    background: linear-gradient(135deg, #60a5fa 0%, #34d399 100%);
                    color: #ffffff;
                    box-shadow: 0 4px 15px rgba(96, 165, 250, 0.3);
                }

                .chart-filters {
                    display: flex;
                    gap: 15px;
                    align-items: center;
                }

                .filter-group {
                    display: flex;
                    align-items: center;
                    gap: 10px;
                }

                .filter-group label {
                    color: #94a3b8;
                    font-size: 13px;
                    font-weight: 500;
                }

                .filter-input, .filter-select {
                    background: rgba(255, 255, 255, 0.05);
                    border: 1px solid rgba(255, 255, 255, 0.2);
                    border-radius: 6px;
                    padding: 8px 12px;
                    color: #ffffff;
                    font-size: 13px;
                    min-width: 100px;
                }

                .filter-input:focus, .filter-select:focus {
                    outline: none;
                    border-color: #60a5fa;
                    box-shadow: 0 0 0 2px rgba(96, 165, 250, 0.2);
                }

                .chart-wrapper {
                    position: relative;
                    height: 500px;
                    background: rgba(0, 0, 0, 0.2);
                    border-radius: 12px;
                    overflow: hidden;
                }

                .status-bar {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    background: rgba(255, 255, 255, 0.05);
                    border-radius: 12px;
                    padding: 20px 30px;
                    margin-bottom: 20px;
                    border: 1px solid rgba(255, 255, 255, 0.1);
                }

                .status-item {
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    gap: 5px;
                }

                .status-label {
                    font-size: 12px;
                    color: #94a3b8;
                    text-transform: uppercase;
                    letter-spacing: 0.5px;
                    font-weight: 500;
                }

                .status-value {
                    font-size: 16px;
                    font-weight: 600;
                    color: #ffffff;
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

                .loading {
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    height: 400px;
                    color: #94a3b8;
                    font-size: 16px;
                }

                .spinner {
                    width: 30px;
                    height: 30px;
                    border: 3px solid rgba(255, 255, 255, 0.1);
                    border-top: 3px solid #60a5fa;
                    border-radius: 50%;
                    animation: spin 1s linear infinite;
                    margin-right: 10px;
                }

                @keyframes spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }

                @media (max-width: 768px) {
                    .header {
                        flex-direction: column;
                        gap: 15px;
                        padding: 15px;
                    }

                    .price-info {
                        flex-direction: column;
                        gap: 10px;
                    }

                    .chart-controls {
                        flex-direction: column;
                        align-items: stretch;
                    }

                    .time-filters {
                        justify-content: center;
                    }

                    .chart-filters {
                        justify-content: center;
                        flex-wrap: wrap;
                    }

                    .status-bar {
                        flex-direction: column;
                        gap: 15px;
                    }
                }
            </style>
        @endif
    </head>
    <body>
        <div class="trading-container">
            <!-- Header -->
            <header class="header">
                <h1>TradingView Pro</h1>
                <div class="price-info">
                    <div class="current-price" id="currentPrice">$42.50</div>
                    <div class="price-change positive" id="priceChange">
                        <span>+2.5%</span>
                        <span>↗</span>
                    </div>
                    <div class="websocket-status">
                        <div class="status-indicator" id="wsIndicator"></div>
                        <span id="wsStatus">Connected</span>
                    </div>
                </div>
            </header>

            <!-- Chart Section -->
            <div class="chart-container">
                <!-- Chart Controls -->
                <div class="chart-controls">
                    <div class="time-filters">
                        <button class="time-btn" onclick="loadPrices('1m')" data-range="1m">1M</button>
                        <button class="time-btn" onclick="loadPrices('1w')" data-range="1w">1W</button>
                        <button class="time-btn" onclick="loadPrices('6m')" data-range="6m">6M</button>
                        <button class="time-btn active" onclick="loadPrices('1y')" data-range="1y">1Y</button>
                        <button class="time-btn" onclick="loadPrices('5y')" data-range="5y">5Y</button>
                        <button class="time-btn" onclick="loadPrices('total')" data-range="total">ALL</button>
                    </div>
                    
                    <div class="chart-filters">
                        <div class="filter-group">
                            <label>Min Price:</label>
                            <input type="number" class="filter-input" id="minPriceFilter" placeholder="Auto" min="0">
                        </div>
                        <div class="filter-group">
                            <label>Max Price:</label>
                            <input type="number" class="filter-input" id="maxPriceFilter" placeholder="Auto" min="0">
                        </div>
                        <div class="filter-group">
                            <label>Interval:</label>
                            <select class="filter-select" id="intervalFilter">
                                <option value="all">All Data</option>
                                <option value="1min">1 Minute</option>
                                <option value="5min">5 Minutes</option>
                                <option value="1hour">1 Hour</option>
                                <option value="1day">1 Day</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Chart Wrapper -->
                <div class="chart-wrapper">
                    <canvas id="priceChart"></canvas>
                    <div class="loading" id="chartLoading" style="display: none;">
                        <div class="spinner"></div>
                        Loading chart data...
                    </div>
                </div>
            </div>

            <!-- Status Bar -->
            <div class="status-bar">
                <div class="status-item">
                    <span class="status-label">Last Update</span>
                    <span class="status-value" id="lastUpdate">--</span>
                </div>
                <div class="status-item">
                    <span class="status-label">Volume</span>
                    <span class="status-value" id="volume">--</span>
                </div>
                <div class="status-item">
                    <span class="status-label">High (24h)</span>
                    <span class="status-value" id="high24h">--</span>
                </div>
                <div class="status-item">
                    <span class="status-label">Low (24h)</span>
                    <span class="status-value" id="low24h">--</span>
                </div>
                <div class="status-item">
                    <span class="status-label">Data Points</span>
                    <span class="status-value" id="dataPoints">--</span>
                </div>
            </div>
        </div>

        <script>
            // Global variables
            let chart;
            let currentRange = '1y';
            let chartData = { prices: [], labels: [] };
            let lastPrice = 42.50;
            let priceHistory = [];
            
            // Initialize Chart.js with advanced configuration
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
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4,
                            pointRadius: 0,
                            pointHoverRadius: 8,
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
                            duration: 750,
                            easing: 'easeInOutQuart'
                        },
                        scales: {
                            y: {
                                type: 'linear',
                                position: 'right',
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
                                    maxTicksLimit: 8
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
                        },
                        elements: {
                            line: {
                                borderCapStyle: 'round',
                                borderJoinStyle: 'round'
                            }
                        }
                    }
                });
            }
            
            // Update active time filter button
            function updateActiveFilter(selectedRange) {
                document.querySelectorAll('.time-btn').forEach(btn => {
                    btn.classList.remove('active');
                    if (btn.getAttribute('data-range') === selectedRange) {
                        btn.classList.add('active');
                    }
                });
            }
            
            // Show loading state
            function showLoading() {
                document.getElementById('chartLoading').style.display = 'flex';
            }
            
            // Hide loading state
            function hideLoading() {
                document.getElementById('chartLoading').style.display = 'none';
            }
            
            // Update price information in header
            function updatePriceInfo(currentPrice, previousPrice = null) {
                const priceElement = document.getElementById('currentPrice');
                const changeElement = document.getElementById('priceChange');
                
                priceElement.textContent = '$' + currentPrice.toFixed(2);
                
                if (previousPrice !== null) {
                    const change = ((currentPrice - previousPrice) / previousPrice * 100);
                    const isPositive = change >= 0;
                    
                    changeElement.className = `price-change ${isPositive ? 'positive' : 'negative'}`;
                    changeElement.innerHTML = `
                        <span>${isPositive ? '+' : ''}${change.toFixed(2)}%</span>
                        <span>${isPositive ? '↗' : '↘'}</span>
                    `;
                }
            }
            
            // Update status bar information
            function updateStatusBar(data) {
                if (data.prices && data.prices.length > 0) {
                    const prices = data.prices;
                    const high = Math.max(...prices);
                    const low = Math.min(...prices);
                    
                    document.getElementById('high24h').textContent = '$' + high.toFixed(2);
                    document.getElementById('low24h').textContent = '$' + low.toFixed(2);
                    document.getElementById('dataPoints').textContent = prices.length.toLocaleString();
                    document.getElementById('volume').textContent = (prices.length * 1000).toLocaleString();
                }
                
                document.getElementById('lastUpdate').textContent = new Date().toLocaleTimeString();
            }
            
            // Apply filters to chart data
            function applyFilters() {
                const minPrice = parseFloat(document.getElementById('minPriceFilter').value);
                const maxPrice = parseFloat(document.getElementById('maxPriceFilter').value);
                const interval = document.getElementById('intervalFilter').value;
                
                let filteredData = [...chartData.prices];
                let filteredLabels = [...chartData.labels];
                
                // Apply price filters
                if (!isNaN(minPrice) || !isNaN(maxPrice)) {
                    const filtered = filteredData.map((price, index) => {
                        const withinMin = isNaN(minPrice) || price >= minPrice;
                        const withinMax = isNaN(maxPrice) || price <= maxPrice;
                        return withinMin && withinMax ? { price, label: filteredLabels[index], index } : null;
                    }).filter(item => item !== null);
                    
                    filteredData = filtered.map(item => item.price);
                    filteredLabels = filtered.map(item => item.label);
                }
                
                // Apply interval filter
                if (interval !== 'all' && filteredData.length > 0) {
                    const step = getIntervalStep(interval, filteredData.length);
                    if (step > 1) {
                        const sampledData = [];
                        const sampledLabels = [];
                        for (let i = 0; i < filteredData.length; i += step) {
                            sampledData.push(filteredData[i]);
                            sampledLabels.push(filteredLabels[i]);
                        }
                        filteredData = sampledData;
                        filteredLabels = sampledLabels;
                    }
                }
                
                // Update chart with filtered data
                updateChartData(filteredData, filteredLabels);
            }
            
            // Get interval step for data sampling
            function getIntervalStep(interval, dataLength) {
                const targetPoints = {
                    '1min': 100,
                    '5min': 200,
                    '1hour': 300,
                    '1day': 50
                };
                
                const target = targetPoints[interval] || dataLength;
                return Math.max(1, Math.floor(dataLength / target));
            }
            
            // Update chart data without zooming/jumping
            function updateChartData(prices, labels, animate = true) {
                if (!chart) return;
                
                // Calculate appropriate Y-axis bounds with padding
                const minPrice = Math.min(...prices);
                const maxPrice = Math.max(...prices);
                const padding = (maxPrice - minPrice) * 0.1; // 10% padding
                
                chart.options.scales.y.min = Math.max(0, minPrice - padding);
                chart.options.scales.y.max = maxPrice + padding;
                
                // Update data
                chart.data.labels = labels;
                chart.data.datasets[0].data = prices;
                
                // Update chart smoothly without zoom
                chart.update(animate ? 'active' : 'none');
            }
            
            // Load prices from server
            function loadPrices(range) {
                if (range === currentRange) return; // Don't reload same data
                
                showLoading();
                currentRange = range;
                updateActiveFilter(range);
                
                fetch(`/prices?range=${range}`)
                    .then(response => {
                        if (!response.ok) throw new Error('Network response was not ok');
                        return response.json();
                    })
                    .then(data => {
                        chartData = {
                            prices: data.prices || [],
                            labels: data.labels || []
                        };
                        
                        // Update price info if we have data
                        if (data.prices && data.prices.length > 0) {
                            const latestPrice = data.prices[data.prices.length - 1];
                            const previousPrice = data.prices.length > 1 ? data.prices[data.prices.length - 2] : null;
                            updatePriceInfo(latestPrice, previousPrice);
                            lastPrice = latestPrice;
                        }
                        
                        updateStatusBar(data);
                        applyFilters(); // This will update the chart
                        hideLoading();
                    })
                    .catch(error => {
                        console.error('Error loading prices:', error);
                        hideLoading();
                        // Show error in status
                        document.getElementById('lastUpdate').textContent = 'Error loading data';
                    });
            }
            
            // WebSocket connection status
            function updateWebSocketStatus(connected) {
                const indicator = document.getElementById('wsIndicator');
                const status = document.getElementById('wsStatus');
                
                if (connected) {
                    indicator.classList.remove('disconnected');
                    status.textContent = 'Connected';
                } else {
                    indicator.classList.add('disconnected');
                    status.textContent = 'Disconnected';
                }
            }
            
            // Initialize everything when page loads
            document.addEventListener('DOMContentLoaded', function() {
                initializeChart();
                loadPrices(currentRange);
                
                // Add event listeners for filters
                document.getElementById('minPriceFilter').addEventListener('change', applyFilters);
                document.getElementById('maxPriceFilter').addEventListener('change', applyFilters);
                document.getElementById('intervalFilter').addEventListener('change', applyFilters);
                
                // Initialize WebSocket connection
                try {
                    window.Echo.channel('test')
                        .listen('Tutorial', (event) => {
                            console.log('Received WebSocket event:', event);
                            
                            // Extract numeric value from the message
                            let value = null;
                            if (event.message && typeof event.message === 'string') {
                                const match = event.message.match(/\d+(\.\d+)?/);
                                if (match) {
                                    value = parseFloat(match[0]);
                                }
                            }
                            
                            if (value !== null && !isNaN(value)) {
                                // Store in price history
                                priceHistory.push({
                                    price: value,
                                    timestamp: new Date()
                                });
                                
                                // Keep only last 100 real-time updates
                                if (priceHistory.length > 100) {
                                    priceHistory.shift();
                                }
                                
                                // Update UI with new price
                                updatePriceInfo(value, lastPrice);
                                lastPrice = value;
                                
                                // If we're viewing real-time data (1m range), update chart
                                if (currentRange === '1m') {
                                    // Add new point to current data
                                    const newLabel = new Date().toLocaleTimeString();
                                    chartData.prices.push(value);
                                    chartData.labels.push(newLabel);
                                    
                                    // Keep only recent data points for 1m view
                                    if (chartData.prices.length > 50) {
                                        chartData.prices.shift();
                                        chartData.labels.shift();
                                    }
                                    
                                    applyFilters();
                                }
                            }
                        })
                        .subscribed(() => {
                            console.log('Subscribed to test channel');
                            updateWebSocketStatus(true);
                        })
                        .error((error) => {
                            console.error('WebSocket error:', error);
                            updateWebSocketStatus(false);
                        });
                } catch (error) {
                    console.error('Failed to initialize WebSocket:', error);
                    updateWebSocketStatus(false);
                }
            });
        </script>
    </body>
</html>
