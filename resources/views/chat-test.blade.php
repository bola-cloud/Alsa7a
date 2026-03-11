<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat Reverb Test</title>
    <!-- Include Pusher and Laravel Echo from a CDN for testing -->
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .log-box { background: #f4f4f4; border: 1px solid #ccc; padding: 15px; height: 300px; overflow-y: scroll; margin-top: 10px; font-family: monospace; white-space: pre-wrap; }
    </style>
</head>
<body>
    <h1>Reverb WebSocket Test Channel</h1>

    <div>
        <label>Personal Access Token (Sanctum):</label><br>
        <input type="text" id="token" style="width: 100%; padding: 5px;" placeholder="Bearer ... (Keep empty if testing as web user)" autocomplete="off">
    </div>
    <br>
    <div>
        <label>Conversation ID:</label><br>
        <input type="number" id="conversation_id" placeholder="e.g 1" style="padding: 5px;">
        <button id="connect_btn" style="padding: 6px 12px;">Connect to chat</button>
    </div>

    <div class="log-box" id="logs">
        [System] Ready. Enter Token (if API user) and Conversation ID, then click Connect.
    </div>

    <script>
        const log = (msg) => {
            const logs = document.getElementById('logs');
            logs.innerHTML += `\n[${new Date().toLocaleTimeString()}] ${msg}`;
            logs.scrollTop = logs.scrollHeight;
        };

        document.getElementById('connect_btn').addEventListener('click', () => {
            let convoId = document.getElementById('conversation_id').value;
            let token = document.getElementById('token').value.trim();

            if(!convoId) {
                log('Error: Enter a Conversation ID');
                return;
            }

            log(`Attempting to connect to Reverb for chat.${convoId}...`);

            // Initialize Echo with Reverb Keys
            window.Echo = new Echo({
                broadcaster: 'reverb',
                key: '{{ env('REVERB_APP_KEY', 't6o995a86az28cff') }}',
                wsHost: '{{ env('REVERB_HOST', 'alsaha.tech') }}',
                wsPort: {{ env('REVERB_PORT', 443) }},
                wssPort: {{ env('REVERB_PORT', 443) }},
                forceTLS: true,
                enabledTransports: ['ws', 'wss'],
                authEndpoint: '/api/v1/broadcasting/auth', // Since this is expected for Sanctum
                auth: {
                    headers: {
                        Authorization: token ? (token.startsWith('Bearer') ? token : `Bearer ${token}`) : null,
                        Accept: 'application/json'
                    }
                }
            });

            log(`Echo Initialized. Subscribing to private-chat.${convoId}...`);

            window.Echo.private(`chat.${convoId}`)
                .subscribed(() => {
                    log(`✅ Successfully subscribed to private-chat.${convoId}`);
                })
                .error((err) => {
                    log(`❌ Subscription Error: ${JSON.stringify(err)}`);
                    console.error('Subscription error:', err);
                })
                .listen('.MessageSent', (e) => {
                    log(`💬 New Message Event Received: ${JSON.stringify(e)}`);
                })
                // fallback generic listener
                .listenToAll((event, data) => {
                    log(`🔔 Event Caught [${event}]: ${JSON.stringify(data)}`);
                });
        });
    </script>
</body>
</html>
