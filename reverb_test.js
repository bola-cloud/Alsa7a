const WebSocket = require('ws');
const axios = require('axios');

// TEST PARAMETERS
const TOKEN = '220|q7inlQqFwtgqmHaQpBoiWF8opn8Kbl52Vi0gv3Yof6cd9463';
const CONVERSATION_ID = 18;
const APP_KEY = 't6o995a86az28cff';
const AUTH_ENDPOINT = 'https://alsaha.tech/api/v1/broadcasting/auth';
const WS_URL = `wss://alsaha.tech/app/${APP_KEY}?protocol=7&client=js&version=8.3.0&flash=false`;

async function runTest() {
    console.log('🚀 Starting Reverb Simulation Test...');

    // 1. Initialize WebSocket
    console.log(`📡 Connecting to: ${WS_URL}`);
    const ws = new WebSocket(WS_URL);

    ws.on('open', () => {
        console.log('✅ WebSocket Connection Opened');
    });

    ws.on('message', async (data) => {
        const message = JSON.parse(data.toString());
        console.log('📥 Received:', message);

        // 2. Handle connection established
        if (message.event === 'pusher:connection_established') {
            const socketId = JSON.parse(message.data).socket_id;
            console.log(`🔑 Socket ID: ${socketId}`);

            // 3. Perform Authentication (Simulating Flutter's delegate)
            try {
                console.log('🔐 Requesting Channel Authorization...');
                const authResponse = await axios.post(AUTH_ENDPOINT, {
                    socket_id: socketId,
                    channel_name: `private-chat.${CONVERSATION_ID}`
                }, {
                    headers: {
                        'Authorization': `Bearer ${TOKEN}`,
                        'Accept': 'application/json'
                    }
                });

                console.log('✅ Auth Response:', authResponse.data);

                // 4. Subscribe
                const subscribePayload = {
                    event: 'pusher:subscribe',
                    data: {
                        auth: authResponse.data.auth,
                        channel: `private-chat.${CONVERSATION_ID}`
                    }
                };
                ws.send(JSON.stringify(subscribePayload));
                console.log(`📤 Subscription request sent for private-chat.${CONVERSATION_ID}`);

            } catch (error) {
                console.error('❌ Auth Failed:', error.response ? error.response.data : error.message);
                process.exit(1);
            }
        }

        // 5. Listen for subscription success
        if (message.event === 'pusher_internal:subscription_succeeded') {
            console.log('🏁 SUCCESS: Subscribed to channel!');
            console.log('⏳ Waiting for incoming events... Send a message via API/Tinker now.');
        }

        // 6. Listen for our specific event
        if (message.event === 'chat.ping') {
            console.log('🔔 EVENT RECEIVED: .chat.ping');
            console.log('📦 Data:', message.data);
            console.log('🌈 TEST PASSED!');
            process.exit(0);
        }
    });

    ws.on('error', (err) => {
        console.error('❌ WebSocket Error:', err);
    });

    // Timeout after 30 seconds
    setTimeout(() => {
        console.log('⏰ Test timed out.');
        process.exit(1);
    }, 30000);
}

runTest();
