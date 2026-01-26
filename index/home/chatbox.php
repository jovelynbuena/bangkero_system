<!-- chatbox.php -->

<!-- Floating Button -->
<div id="chat-toggle">💬</div>

<!-- Chatbox -->
<div id="chatbox">
    <div id="chat-header">
        Bangkero & Fishermen Assistant
        <span id="close-chat">✖</span>
    </div>

    <div id="chat-body">
        <div class="bot">Hello! 👋 I’m here to help you.</div>
        <div class="bot">
            You can ask me about:
            <ul>
                <li>Membership</li>
                <li>Officers</li>
                <li>Events</li>
                <li>Announcements</li>
            </ul>
        </div>
    </div>

    <div id="chat-input-area">
        <input type="text" id="chat-input" placeholder="Type your question...">
        <button id="send-btn">➤</button>
    </div>
</div>

<style>
/* Floating Button */
#chat-toggle {
    position: fixed;
    bottom: 20px;
    right: 20px;
    background: #0d6efd;
    color: white;
    width: 52px;
    height: 52px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 22px;
    z-index: 1001;
    animation: pulse 2s infinite;
}

/* Chatbox Container */
#chatbox {
    display: none;
    position: fixed;
    bottom: 90px;
    right: 20px;
    width: 340px;
    background: #ffffff;
    border-radius: 16px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.25);
    font-family: "Segoe UI", Arial, sans-serif;
    z-index: 1000;
    overflow: hidden;
    animation: slideUp 0.25s ease;
}

/* Header */
#chat-header {
    background: #0d6efd;
    color: white;
    padding: 14px 16px;
    font-size: 15px;
    font-weight: 600;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

#close-chat {
    cursor: pointer;
}

/* Chat Body */
#chat-body {
    height: 260px;
    padding: 14px;
    overflow-y: auto;
    background: #f9fafb;
}

/* Messages */
.bot, .user {
    padding: 10px 14px;
    border-radius: 14px;
    margin-bottom: 10px;
    max-width: 85%;
    font-size: 14px;
    line-height: 1.4;
    animation: popIn 0.25s ease;
}

.bot {
    background: #e9ecef;
    color: #333;
}

.user {
    background: #0d6efd;
    color: white;
    margin-left: auto;
}

/* Input Area */
#chat-input-area {
    display: flex;
    align-items: center;
    padding: 10px;
    border-top: 1px solid #ddd;
    background: #fff;
}

#chat-input {
    flex: 1;
    padding: 10px 12px;
    border: 1px solid #ccc;
    border-radius: 20px;
    outline: none;
    font-size: 14px;
}

#send-btn {
    margin-left: 8px;
    background: #0d6efd;
    color: white;
    border: none;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    cursor: pointer;
    font-size: 18px;
    transition: transform 0.2s ease, background 0.2s ease;
}

#send-btn:hover {
    transform: scale(1.1);
    background: #0b5ed7;
}

/* Typing Animation */
.typing {
    background: #e9ecef;
    padding: 10px 14px;
    border-radius: 14px;
    display: flex;
    gap: 4px;
    margin-bottom: 10px;
}

.typing span {
    width: 6px;
    height: 6px;
    background: #555;
    border-radius: 50%;
    animation: blink 1.4s infinite both;
}

.typing span:nth-child(2) { animation-delay: 0.2s; }
.typing span:nth-child(3) { animation-delay: 0.4s; }

/* Animations */
@keyframes slideUp {
    from { transform: translateY(25px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

@keyframes popIn {
    from { transform: scale(0.95); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
}

@keyframes blink {
    0% { opacity: 0.2; }
    20% { opacity: 1; }
    100% { opacity: 0.2; }
}

@keyframes pulse {
    0% { box-shadow: 0 0 0 0 rgba(13,110,253,0.6); }
    70% { box-shadow: 0 0 0 12px rgba(13,110,253,0); }
    100% { box-shadow: 0 0 0 0 rgba(13,110,253,0); }
}
</style>

<script>
const toggle = document.getElementById("chat-toggle");
const chatbox = document.getElementById("chatbox");
const closeChat = document.getElementById("close-chat");
const input = document.getElementById("chat-input");
const chatBody = document.getElementById("chat-body");
const sendBtn = document.getElementById("send-btn");

toggle.onclick = () => chatbox.style.display = "block";
closeChat.onclick = () => chatbox.style.display = "none";

// Enter key
input.addEventListener("keypress", function(e) {
    if (e.key === "Enter" && input.value.trim() !== "") {
        sendMessage(input.value);
        input.value = "";
    }
});

// Send button
sendBtn.onclick = () => {
    if (input.value.trim() !== "") {
        sendMessage(input.value);
        input.value = "";
    }
};

function sendMessage(message) {
    const userMsg = document.createElement("div");
    userMsg.className = "user";
    userMsg.innerText = message;
    chatBody.appendChild(userMsg);
    chatBody.scrollTop = chatBody.scrollHeight;

    const typing = document.createElement("div");
    typing.className = "typing";
    typing.innerHTML = "<span></span><span></span><span></span>";
    chatBody.appendChild(typing);
    chatBody.scrollTop = chatBody.scrollHeight;

    setTimeout(() => {
        chatBody.removeChild(typing);
        botReply(message);
    }, 900);
}

function botReply(message) {
    const botMsg = document.createElement("div");
    botMsg.className = "bot";
    message = message.toLowerCase();

    if (message.includes("member")) {
        botMsg.innerText =
            "To become a member, please contact the association officers and submit the required requirements.";
    } else if (message.includes("officer")) {
        botMsg.innerText =
            "You can view the list of officers in the Officers section of the system.";
    } else if (message.includes("event")) {
        botMsg.innerText =
            "All upcoming events are available in the Events page.";
    } else {
        botMsg.innerText =
            "I can help with membership, officers, events, and announcements 😊";
    }

    chatBody.appendChild(botMsg);
    chatBody.scrollTop = chatBody.scrollHeight;
}
</script>
