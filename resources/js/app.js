import './bootstrap';

document.addEventListener('DOMContentLoaded', () => {
    const chatEl = document.getElementById('chat-page');

    if (!chatEl) {
        console.log('chat-page element not found');
        return;
    }

    const conversationId = chatEl.dataset.conversationId;

    if (!conversationId) {
        console.error('No conversationId found');
        return;
    }

    console.log('Trying to subscribe to:', `chat.${conversationId}`);

    window.Echo.private(`chat.${conversationId}`)
        .subscribed(() => {
            console.log('✅ subscribed to private channel:', `chat.${conversationId}`);
        })
        .error((err) => {
            console.error('❌ subscription/auth failed:', err);
        })
        .listen('.chat.ping', (e) => {
            console.log('✅ event received:', e);
        });
});