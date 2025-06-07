// Chat bot functionality
class ChatBot {
    constructor() {
        this.responses = {
            // Greetings
            'hello': 'Hello! How can I help you today?',
            'hi': 'Hi there! What can I do for you?',
            'hey': 'Hello! How can I help you today?',
            'good morning': 'Good morning! How can I assist you today?',
            'good afternoon': 'Good afternoon! How can I help you?',
            'good evening': 'Good evening! How can I assist you?',
            
            // Personal
            'how are you': 'I am doing well, thank you for asking! How can I help you today?',
            'what is your name': 'I am a chatbot created by Kelvin Paa, you can call me Khristos.',
            'who are you': 'I am Khristos, a chatbot created by Kelvin Paa to help you with your questions and concerns.',
            'what can you do': 'I can help you with:\n- Answering questions\n- Providing information\n- Account assistance\n- Support issues\nJust ask me anything!',
            
            // Purpose and Goals
            'what is your purpose': 'I am here to help you with your questions and concerns.',
            'what is your goal': 'My goal is to provide helpful and accurate information to assist you.',
            'what is your mission': 'My mission is to make information easily accessible and help solve your problems.',
            'what is your vision': 'My vision is to be a reliable and helpful assistant for all your needs.',
            
            // Common Questions
            'help': 'I can help you with:\n- Account information\n- General questions\n- Support issues\n- Product information\n- Technical assistance\nJust ask me anything!',
            'account': 'You can manage your account settings in the dashboard. Would you like me to guide you through the process?',
            'support': 'For support, please contact our support team on Email at kelvinpaa135@gmail.com or call 0507787881 / 0509735236 thank you.',
            'contact': 'You can reach us through:\n- Email: kelvinpaa135@gmail.com\n- Phone: 0507787881 / 0509735236\n- Support Hours: 24/7',
            
            // Water-related
            'water': 'I can help you with water-related information. What would you like to know about water?',
            'water quality': 'Water quality is essential for health. Would you like information about water testing or purification?',
            'water conservation': 'Water conservation is important! I can provide tips on how to save water in your daily life.',
            'water treatment': 'Water treatment processes ensure safe drinking water. Would you like to know more about specific treatment methods?',
            
            // Technical
            'error': 'I\'m sorry to hear you\'re experiencing an error. Could you please describe the issue in detail?',
            'bug': 'If you\'ve found a bug, please provide details about what happened and I\'ll help you report it.',
            'problem': 'I\'m here to help solve your problem. Could you please describe it in detail?',
            'issue': 'I\'ll help you resolve your issue. Please provide more information about what you\'re experiencing.',
            
            // Confirmation
            'okay': 'Is there anything else I can help you with?',
            'yes': 'Great! What else would you like to know?',
            'no': 'Alright! Let me know if you need anything else.',
            'thanks': 'You\'re welcome! Is there anything else I can help you with?',
            'thank you': 'You\'re welcome! Feel free to ask if you need anything else.',
            
            // Default
            'default': 'I\'m not sure I understand. Could you please rephrase your question?'
        };
    }

    async getResponse(message) {
        message = message.toLowerCase().trim();
        
        // Check for exact matches first
        if (this.responses[message]) {
            return this.responses[message];
        }

        // Check for partial matches
        for (let key in this.responses) {
            if (message.includes(key)) {
                return this.responses[key];
            }
        }

        try {
            // Try to get response from web search
            const response = await this.getWebSearchResponse(message);
            return response;
        } catch (error) {
            console.error('Error getting web search response:', error);
            return this.responses.default;
        }
    }

    async getWebSearchResponse(message) {
        try {
            // Using DuckDuckGo Instant Answer API (free, no API key required)
            const response = await fetch(`https://api.duckduckgo.com/?q=${encodeURIComponent(message)}&format=json&pretty=1`);
            const data = await response.json();

            if (data.Abstract) {
                return data.Abstract;
            } else if (data.RelatedTopics && data.RelatedTopics.length > 0) {
                // Return the first related topic's text
                const firstTopic = data.RelatedTopics[0];
                return firstTopic.Text || firstTopic.FirstURL;
            } else {
                // If no direct answer, provide a search suggestion
                return `I found some information about "${message}". You can learn more by visiting: https://duckduckgo.com/?q=${encodeURIComponent(message)}`;
            }
        } catch (error) {
            console.error('Error calling web search:', error);
            return this.responses.default;
        }
    }
}

// Initialize chat UI
function initializeChat() {
    // Create chat icon
    const chatIcon = document.createElement('div');
    chatIcon.className = 'chat-icon';
    chatIcon.innerHTML = '<i class="fas fa-comments"></i>';
    document.body.appendChild(chatIcon);

    // Create chat container (initially hidden)
    const chatContainer = document.createElement('div');
    chatContainer.className = 'chat-container hidden';
    chatContainer.innerHTML = `
        <div class="chat-header">
            <h3>Chat Assistant</h3>
            <button class="close-btn"><i class="fas fa-times"></i></button>
        </div>
        <div class="chat-messages"></div>
        <div class="chat-input-container">
            <input type="text" class="chat-input" placeholder="Tell me your issue...">
            <button class="send-btn"><i class="fas fa-paper-plane"></i></button>
        </div>
    `;
    document.body.appendChild(chatContainer);

    const chatBot = new ChatBot();
    const messagesContainer = chatContainer.querySelector('.chat-messages');
    const input = chatContainer.querySelector('.chat-input');
    const sendBtn = chatContainer.querySelector('.send-btn');
    const closeBtn = chatContainer.querySelector('.close-btn');

    function addMessage(message, isUser = false) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${isUser ? 'user-message' : 'bot-message'}`;
        
        // Handle URLs in the message
        if (!isUser && message.includes('http')) {
            const urlMatch = message.match(/(https?:\/\/[^\s]+)/);
            if (urlMatch) {
                const url = urlMatch[0];
                const text = message.replace(url, '');
                messageDiv.innerHTML = `${text} <a href="${url}" target="_blank" rel="noopener noreferrer">Learn more</a>`;
            } else {
                messageDiv.textContent = message;
            }
        } else {
            messageDiv.textContent = message;
        }
        
        messagesContainer.appendChild(messageDiv);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    function addLoadingIndicator() {
        const loadingDiv = document.createElement('div');
        loadingDiv.className = 'message bot-message loading';
        loadingDiv.innerHTML = '<div class="typing-indicator"><span></span><span></span><span></span></div>';
        messagesContainer.appendChild(loadingDiv);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
        return loadingDiv;
    }

    function removeLoadingIndicator(loadingDiv) {
        if (loadingDiv && loadingDiv.parentNode) {
            loadingDiv.parentNode.removeChild(loadingDiv);
        }
    }

    function resetChat() {
        messagesContainer.innerHTML = '';
        input.value = '';
        input.disabled = false;
        sendBtn.disabled = false;
    }

    // Toggle chat window
    chatIcon.addEventListener('click', () => {
        chatContainer.classList.remove('hidden');
        chatIcon.classList.add('hidden');
        // Add initial greeting if it's the first time opening
        if (messagesContainer.children.length === 0) {
            setTimeout(() => addMessage('Hello! How can I help you today?'), 800);
        }
    });

    closeBtn.addEventListener('click', () => {
        chatContainer.classList.add('hidden');
        chatIcon.classList.remove('hidden');
        resetChat(); // Reset chat when closing
    });

    async function handleSend() {
        const message = input.value.trim();
        if (message) {
            addMessage(message, true);
            input.value = '';
            input.disabled = true;
            sendBtn.disabled = true;
            
            // Add loading indicator
            const loadingDiv = addLoadingIndicator();
            
            try {
                const response = await chatBot.getResponse(message);
                // Remove loading indicator
                removeLoadingIndicator(loadingDiv);
                // Add response with a small delay
                setTimeout(() => {
                    addMessage(response);
                    input.disabled = false;
                    sendBtn.disabled = false;
                    input.focus();
                }, 500);
            } catch (error) {
                console.error('Error getting response:', error);
                removeLoadingIndicator(loadingDiv);
                addMessage('Sorry, I encountered an error. Please try again.');
                input.disabled = false;
                sendBtn.disabled = false;
                input.focus();
            }
        }
    }

    sendBtn.addEventListener('click', handleSend);
    input.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') handleSend();
    });
}

// Initialize chat when DOM is loaded
document.addEventListener('DOMContentLoaded', initializeChat); 