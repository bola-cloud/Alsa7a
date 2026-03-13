const WebSocket = require('ws');
const axios = require('axios');

// TEST PARAMETERS
const TOKEN = '220|q7inlQqFwtgqmHaQpBoiWF8opn8Kbl52Vi0gv3Yof6cd9463';
const CONVERSATION_ID = 18;
const APP_KEY = 't6o995a86az28cff';
const WS_URL = `wss://alsaha.tech/app/${APP_KEY}?protocol=7&client=js&version=8.3.0&flash=false`;
const SEND_URL = `https://alsaha.tech/api/v1/chat/conversations/${CONVERSATION_ID}/messages`;

async function runTest() {
    console.log('🚀 Starting Reverb Public Channel Test (NO AUTH)...');
    console.log(`📡 Connecting to: ${WS_URL}`);

    const ws = new WebSocket(WS_URL);

    ws.on('open', () => {
        console.log('✅ WebSocket Connection Opened');
    });

    ws.on('message', async (data) => {
        const message = JSON.parse(data.toString());
        console.log('📥 Received:', JSON.stringify(message));

        // When connected, subscribe to PUBLIC channel (no auth needed)
        if (message.event === 'pusher:connection_established') {
            const socketId = JSON.parse(message.data).socket_id;
            console.log(`🔑 Socket ID: ${socketId}`);

            // Subscribe DIRECTLY - no auth needed for public channels
            const subscribePayload = {
                event: 'pusher:subscribe',
                data: {
                    channel: `chat.${CONVERSATION_ID}`  // public: no "private-" prefix
                }
            };
            ws.send(JSON.stringify(subscribePayload));
            console.log(`📤 Subscribed to public channel: chat.${CONVERSATION_ID}`);
        }

        // Subscription confirmed
        if (message.event === 'pusher_internal:subscription_succeeded') {
            console.log('🏁 SUCCESS: Subscribed! Sending test message via API in 2 seconds...');

            setTimeout(async () => {
                try {
                    const response = await axios.post(SEND_URL, {
                        body: 'Hello from script! Public channel test.'
                    }, {
                        headers: {
                            'Authorization': `Bearer ${TOKEN}`,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        }
                    });
                    console.log('✅ Message sent via API:', response.data.data?.body);
                } catch (err) {
                    console.error('❌ API error:', err.response ? JSON.stringify(err.response.data) : err.message);
                }
            }, 2000);
        }

        // Our real-time event
        if (message.event === 'chat.ping') {
            console.log('');
            console.log('🔔🔔🔔 REAL-TIME EVENT RECEIVED: chat.ping');
            console.log('📦 Payload:', message.data);
            console.log('');
            console.log('✅✅✅ TEST PASSED! Public channel real-time chat works!');
            process.exit(0);
        }
    });

    ws.on('error', (err) => {
        console.error('❌ WebSocket Error:', err.message);
        process.exit(1);
    });

    setTimeout(() => {
        console.log('⏰ Timeout after 30s. No event received.');
        process.exit(1);
    }, 30000);
}

runTest();
