<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Generate Random Number</title>
    @vite(['resources/js/app.js'])
</head>
<body>
    <div id="app">
        <h1>Generate Random Number</h1>
        <button onclick="sendRandomNumber()">Generate and Send Random Number</button>
    </div>

    <script>
        function sendRandomNumber() {
            // Generate a random number (e.g., between 1 and 100)
            const randomNumber = Math.floor(Math.random() * 100) + 1;

            // Debugging: Log the CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            console.log('CSRF Token:', csrfToken);

            if (!csrfToken) {
                console.error('CSRF token not found');
                return;
            }

            // Send the random number to the server via AJAX
            fetch('/send-random', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ number: randomNumber })
            })
            .then(response => response.json())
            .then(data => {
                console.log('Server response:', data);
            })
            .catch(error => {
                console.error('Error sending random number:', error);
            });
        }
    </script>
</body>
</html>