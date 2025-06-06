// Chat bot functionality
class ChatBot {
    constructor() {
        this.responses = {
            'hello': 'Hello! How can I help you today?',
            'hi': 'Hi there! What can I do for you?',
            'help': 'I can help you with:\n- Account information\n- General questions\n- Support issues\nJust ask me anything!',
            'account': 'You can manage your account settings in the dashboard.',
            'support': 'For support, please contact our support team at support@example.com',
            'default': 'I\'m not sure I understand. Could you please rephrase your question?'
        };
    }

    getResponse(message) {
        message = message.toLowerCase().trim();
        
        // Check for exact matches
        if (this.responses[message]) {
            return this.responses[message];
        }

        // Check for partial matches
        for (let key in this.responses) {
            if (message.includes(key)) {
                return this.responses[key];
            }
        }

        return this.responses.default;
    }
}

// Initialize chat UI
function initializeChat() {
    const chatContainer = document.createElement('div');
    chatContainer.className = 'chat-container';
    chatContainer.innerHTML = `
        <div class="chat-header">
            <h3>Chat Assistant</h3>
            <button class="minimize-btn">_</button>
        </div>
        <div class="chat-messages"></div>
        <div class="chat-input-container">
            <input type="text" class="chat-input" placeholder="Type your message...">
            <button class="send-btn">Send</button>
        </div>
    `;

    document.body.appendChild(chatContainer);

    const chatBot = new ChatBot();
    const messagesContainer = chatContainer.querySelector('.chat-messages');
    const input = chatContainer.querySelector('.chat-input');
    const sendBtn = chatContainer.querySelector('.send-btn');
    const minimizeBtn = chatContainer.querySelector('.minimize-btn');

    function addMessage(message, isUser = false) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${isUser ? 'user-message' : 'bot-message'}`;
        messageDiv.textContent = message;
        messagesContainer.appendChild(messageDiv);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    function handleSend() {
        const message = input.value.trim();
        if (message) {
            addMessage(message, true);
            const response = chatBot.getResponse(message);
            setTimeout(() => addMessage(response), 500);
            input.value = '';
        }
    }

    sendBtn.addEventListener('click', handleSend);
    input.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') handleSend();
    });

    minimizeBtn.addEventListener('click', () => {
        const messages = chatContainer.querySelector('.chat-messages');
        const inputContainer = chatContainer.querySelector('.chat-input-container');
        if (messages.style.display === 'none') {
            messages.style.display = 'block';
            inputContainer.style.display = 'flex';
            minimizeBtn.textContent = '_';
        } else {
            messages.style.display = 'none';
            inputContainer.style.display = 'none';
            minimizeBtn.textContent = '+';
        }
    });

    // Add initial greeting
    setTimeout(() => addMessage('Hello! How can I help you today?'), 500);
}

// Initialize chat when DOM is loaded
document.addEventListener('DOMContentLoaded', initializeChat); 