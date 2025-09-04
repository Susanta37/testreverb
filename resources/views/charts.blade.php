<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Update Chart Data</title>
    @vite(['resources/js/app.js'])
</head>
<body class="p-6">
    <div id="app" class="max-w-4xl mx-auto">
        <h1 class="text-3xl mb-4">Update Chart Data</h1>
        <div class="mb-8">
            <h2 class="text-2xl mb-2">Update Company Investment</h2>
            <div class="flex space-x-4 mb-2">
                <input id="companyName" type="text" placeholder="Company Name" class="border px-2 py-1">
                <input id="companyAmount" type="number" placeholder="Investment Amount" class="border px-2 py-1">
                <button onclick="sendCompanyData()" class="px-4 py-2 bg-green-500 text-white rounded">Send</button>
            </div>
            <div id="companyStatus"></div>
        </div>
        <div class="mb-8">
            <h2 class="text-2xl mb-2">Update Country Investors</h2>
            <div class="flex space-x-4 mb-2">
                <input id="countryName" type="text" placeholder="Country Name" class="border px-2 py-1">
                <input id="countryInvestors" type="number" placeholder="Number of Investors" class="border px-2 py-1">
                <button onclick="sendCountryData()" class="px-4 py-2 bg-green-500 text-white rounded">Send</button>
            </div>
            <div id="countryStatus"></div>
        </div>
    </div>

    <script>
        function sendCompanyData() {
            const companyName = document.getElementById('companyName').value;
            const companyAmount = document.getElementById('companyAmount').value;
            const statusElement = document.getElementById('companyStatus');
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

            if (!companyName || !companyAmount) {
                statusElement.innerText = 'Please enter company name and amount';
                return;
            }

            statusElement.innerText = 'Sending company data...';
            fetch('/update-chart-data', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    type: 'company',
                    data: { company: companyName, amount: parseInt(companyAmount) }
                })
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
                statusElement.innerText = 'Error sending company data';
                console.error('Error:', error);
            });
        }

        function sendCountryData() {
            const countryName = document.getElementById('countryName').value;
            const countryInvestors = document.getElementById('countryInvestors').value;
            const statusElement = document.getElementById('countryStatus');
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

            if (!countryName || !countryInvestors) {
                statusElement.innerText = 'Please enter country name and investor count';
                return;
            }

            statusElement.innerText = 'Sending country data...';
            fetch('/update-chart-data', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    type: 'country',
                    data: { country: countryName, investors: parseInt(countryInvestors) }
                })
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
                statusElement.innerText = 'Error sending country data';
                console.error('Error:', error);
            });
        }
    </script>
</body>
</html>