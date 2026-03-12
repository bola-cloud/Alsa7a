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
    console.log(`📡 Connecting to: ${WS_URL}`);

    const ws = new WebSocket(WS_URL);

    ws.on('open', () => {
        console.log('✅ WebSocket Connection Opened');
    });

    ws.on('message', async (data) => {
        const message = JSON.parse(data.toString());
        console.log('📥 Received:', JSON.stringify(message));

        // 2. Handle connection established
        if (message.event === 'pusher:connection_established') {
            const socketId = JSON.parse(message.data).socket_id;
            console.log(`🔑 Socket ID: ${socketId}`);

            // 3. Authenticate with Laravel Sanctum
            try {
                console.log('🔐 Requesting Channel Authorization...');
                const authResponse = await axios.post(AUTH_ENDPOINT, {
                    socket_id: socketId,
                    channel_name: `private-chat.${CONVERSATION_ID}`
                }, {
                    headers: {
                        'Authorization': `Bearer ${TOKEN}`,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                });

                console.log('✅ Auth Success:', JSON.stringify(authResponse.data));

                // 4. Subscribe to private channel
                const subscribePayload = {
                    event: 'pusher:subscribe',
                    data: {
                        auth: authResponse.data.auth,
                        channel: `private-chat.${CONVERSATION_ID}`
                    }
                };
                ws.send(JSON.stringify(subscribePayload));
                console.log(`📤 Subscribed to private-chat.${CONVERSATION_ID}. Now send a message via API to test!`);

            } catch (error) {
                console.error('❌ Auth Failed:', error.response ? JSON.stringify(error.response.data) : error.message);
                process.exit(1);
            }
        }

        // 5. Subscription confirmed
        if (message.event === 'pusher_internal:subscription_succeeded') {
            console.log('🏁 SUCCESS: Subscribed to channel! Waiting for events...');
            console.log('');
            console.log('👉 Now run this curl to fire a test message:');
            console.log(`curl -X POST "https://alsaha.tech/api/v1/chat/conversations/${CONVERSATION_ID}/messages" \\`);
            console.log(`     -H "Authorization: Bearer ${TOKEN}" \\`);
            console.log(`     -H "Content-Type: application/json" \\`);
            console.log(`     -d '{"body": "Hello from Node.js test!"}'`);
        }

        // 6. Our real-time event
        if (message.event === 'chat.ping') {
            console.log('');
            console.log('🔔🔔🔔 REAL-TIME EVENT RECEIVED: chat.ping');
            console.log('📦 Data:', message.data);
            console.log('');
            console.log('✅✅✅ TEST PASSED! Real-time chat is fully working!');
            process.exit(0);
        }
    });

    ws.on('error', (err) => {
        console.error('❌ WebSocket Error:', err.message);
        process.exit(1);
    });

    setTimeout(() => {
        console.log('⏰ Timeout: No event received within 30 seconds.');
        process.exit(1);
    }, 30000);
}

runTest();
