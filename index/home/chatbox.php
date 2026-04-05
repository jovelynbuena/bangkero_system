<!-- chatbox.php -->

<!-- Floating Button -->
<div id="chat-toggle" title="Chat with our assistant">
    <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
    </svg>
</div>

<!-- Chatbox -->
<div id="chatbox">
    <!-- Header -->
    <div id="chat-header">
        <div id="chat-header-info">
            <div id="chat-avatar">
                <img src="../images/logo1.png" alt="BFMA Logo" style="width:30px;height:30px;object-fit:contain;border-radius:50%;">
            </div>
            <div>
                <div id="chat-title">BFA Virtual Assistant</div>
                <div id="chat-status">
                    <span class="status-dot"></span> Online
                </div>
            </div>
        </div>
        <span id="close-chat" title="Close">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="white" viewBox="0 0 24 24">
                <path d="M18 6L6 18M6 6l12 12" stroke="white" stroke-width="2.5" stroke-linecap="round"/>
            </svg>
        </span>
    </div>

    <!-- Chat Body -->
    <div id="chat-body"></div>

    <!-- Quick Replies -->
    <div id="quick-replies">
        <button class="qr-btn" onclick="quickReply('View Members')">View Members</button>
        <button class="qr-btn" onclick="quickReply('Meet Our Officers')">Meet Our Officers</button>
        <button class="qr-btn" onclick="quickReply('Upcoming Events')">Upcoming Events</button>
        <button class="qr-btn" onclick="quickReply('Announcements')">Announcements</button>
    </div>

    <!-- Input Area -->
    <div id="chat-input-area">
        <input type="text" id="chat-input" placeholder="Type a message..." autocomplete="off">
        <button id="send-btn" title="Send">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="white" viewBox="0 0 24 24">
                <path d="M2 21l21-9L2 3v7l15 2-15 2v7z"/>
            </svg>
        </button>
    </div>
</div>

<style>
/* ── Floating Toggle ── */
#chat-toggle {
    position: fixed;
    bottom: 24px;
    right: 24px;
    background: #1a56db;
    color: white;
    width: 56px;
    height: 56px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    z-index: 99999;
    box-shadow: 0 4px 16px rgba(26,86,219,0.45);
    animation: pulse 2.5s infinite;
    transition: transform 0.2s ease;
}
#chat-toggle:hover { transform: scale(1.08); }

/* ── Chatbox Container ── */
#chatbox {
    display: none;
    position: fixed;
    bottom: 92px;
    right: 24px;
    width: 360px;
    background: #ffffff;
    border-radius: 16px;
    box-shadow: 0 12px 40px rgba(0,0,0,0.18);
    font-family: "Segoe UI", Arial, sans-serif;
    z-index: 99998;
    overflow: hidden;
    animation: slideUp 0.28s ease;
    display: none;
    flex-direction: column;
}

/* ── Header ── */
#chat-header {
    background: linear-gradient(135deg, #1a56db 0%, #1e40af 100%);
    color: white;
    padding: 14px 16px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
#chat-header-info {
    display: flex;
    align-items: center;
    gap: 10px;
}
#chat-avatar {
    width: 36px;
    height: 36px;
    background: rgba(255,255,255,0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
#chat-title {
    font-size: 14px;
    font-weight: 700;
    letter-spacing: 0.2px;
}
#chat-status {
    font-size: 11px;
    opacity: 0.85;
    display: flex;
    align-items: center;
    gap: 4px;
    margin-top: 1px;
}
.status-dot {
    width: 7px;
    height: 7px;
    background: #4ade80;
    border-radius: 50%;
    display: inline-block;
}
#close-chat {
    cursor: pointer;
    opacity: 0.8;
    transition: opacity 0.2s;
    line-height: 1;
}
#close-chat:hover { opacity: 1; }

/* ── Chat Body ── */
#chat-body {
    height: 280px;
    padding: 16px 14px 10px;
    overflow-y: auto;
    background: #f4f6fb;
    scroll-behavior: smooth;
}
#chat-body::-webkit-scrollbar { width: 4px; }
#chat-body::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }

/* ── Messages ── */
.bot-msg, .user-msg {
    padding: 10px 14px;
    border-radius: 14px;
    margin-bottom: 10px;
    max-width: 82%;
    font-size: 13.5px;
    line-height: 1.55;
    animation: fadeInMsg 0.25s ease;
    word-break: break-word;
}
.bot-msg {
    background: #ffffff;
    color: #1e293b;
    border: 1px solid #e2e8f0;
    border-bottom-left-radius: 4px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.06);
}
.user-msg {
    background: #1a56db;
    color: white;
    margin-left: auto;
    border-bottom-right-radius: 4px;
    box-shadow: 0 2px 6px rgba(26,86,219,0.25);
}

/* Timestamp */
.msg-time {
    font-size: 10px;
    opacity: 0.5;
    margin-top: 3px;
    display: block;
}
.bot-msg .msg-time { text-align: left; }
.user-msg .msg-time { text-align: right; }

/* Action button inside bot message */
.chat-action-btn {
    display: inline-block;
    margin-top: 8px;
    padding: 6px 14px;
    background: #1a56db;
    color: white;
    border: none;
    border-radius: 20px;
    font-size: 12.5px;
    cursor: pointer;
    text-decoration: none;
    transition: background 0.2s;
}
.chat-action-btn:hover { background: #1e40af; color: white; }

/* ── Quick Replies ── */
#quick-replies {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    padding: 8px 12px;
    background: #f4f6fb;
    border-top: 1px solid #e8edf5;
}
.qr-btn {
    padding: 6px 12px;
    font-size: 12px;
    border: 1.5px solid #1a56db;
    border-radius: 20px;
    background: white;
    color: #1a56db;
    cursor: pointer;
    transition: all 0.18s ease;
    font-family: "Segoe UI", Arial, sans-serif;
    white-space: nowrap;
}
.qr-btn:hover {
    background: #1a56db;
    color: white;
}

/* ── Input Area ── */
#chat-input-area {
    display: flex;
    align-items: center;
    padding: 10px 12px;
    border-top: 1px solid #e2e8f0;
    background: #fff;
    gap: 8px;
}
#chat-input {
    flex: 1;
    padding: 9px 14px;
    border: 1.5px solid #d1d5db;
    border-radius: 22px;
    outline: none;
    font-size: 13.5px;
    font-family: "Segoe UI", Arial, sans-serif;
    background: #f9fafb;
    transition: border-color 0.2s;
    color: #1e293b;
}
#chat-input:focus { border-color: #1a56db; background: #fff; }
#send-btn {
    background: #1a56db;
    color: white;
    border: none;
    width: 38px;
    height: 38px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: transform 0.18s ease, background 0.18s ease;
}
#send-btn:hover { transform: scale(1.1); background: #1e40af; }

/* ── Typing Indicator ── */
.typing-indicator {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    padding: 10px 14px;
    border-radius: 14px;
    border-bottom-left-radius: 4px;
    display: inline-flex;
    gap: 4px;
    margin-bottom: 10px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.06);
    animation: fadeInMsg 0.2s ease;
}
.typing-indicator span {
    width: 7px;
    height: 7px;
    background: #94a3b8;
    border-radius: 50%;
    animation: blink 1.3s infinite both;
}
.typing-indicator span:nth-child(2) { animation-delay: 0.2s; }
.typing-indicator span:nth-child(3) { animation-delay: 0.4s; }

/* ── Animations ── */
@keyframes slideUp {
    from { transform: translateY(20px); opacity: 0; }
    to   { transform: translateY(0);    opacity: 1; }
}
@keyframes fadeInMsg {
    from { transform: translateY(6px); opacity: 0; }
    to   { transform: translateY(0);   opacity: 1; }
}
@keyframes blink {
    0%   { opacity: 0.2; transform: translateY(0); }
    25%  { opacity: 1;   transform: translateY(-3px); }
    50%  { opacity: 0.2; transform: translateY(0); }
    100% { opacity: 0.2; }
}
@keyframes pulse {
    0%   { box-shadow: 0 0 0 0   rgba(26,86,219,0.55); }
    70%  { box-shadow: 0 0 0 13px rgba(26,86,219,0);   }
    100% { box-shadow: 0 0 0 0   rgba(26,86,219,0);    }
}

/* ── Responsive ── */
@media (max-width: 400px) {
    #chatbox { width: calc(100vw - 32px); right: 16px; }
}
</style>

<script>
(function () {
    const STORAGE_KEY  = "bfa_chat_history";
    const OPEN_KEY     = "bfa_chat_open";

    const toggle   = document.getElementById("chat-toggle");
    const chatbox  = document.getElementById("chatbox");
    const closeBtn = document.getElementById("close-chat");
    const input    = document.getElementById("chat-input");
    const chatBody = document.getElementById("chat-body");
    const sendBtn  = document.getElementById("send-btn");

    /* ══════════════════════════════════════════
       SESSION STORAGE — save & restore history
    ══════════════════════════════════════════ */
    function saveHistory() {
        // Save each message: { type: 'bot'|'user', html, time }
        const msgs = [];
        chatBody.querySelectorAll(".bot-msg, .user-msg").forEach(el => {
            msgs.push({ type: el.classList.contains("bot-msg") ? "bot" : "user", html: el.innerHTML });
        });
        sessionStorage.setItem(STORAGE_KEY, JSON.stringify(msgs));
    }

    function restoreHistory() {
        const raw = sessionStorage.getItem(STORAGE_KEY);
        if (!raw) return false;
        try {
            const msgs = JSON.parse(raw);
            if (!msgs.length) return false;
            msgs.forEach(m => {
                const div = document.createElement("div");
                div.className = m.type === "bot" ? "bot-msg" : "user-msg";
                div.innerHTML = m.html;
                chatBody.appendChild(div);
            });
            chatBody.scrollTop = chatBody.scrollHeight;
            return true;
        } catch(e) { return false; }
    }

    /* ══════════════════════════════════════════
       OPEN / CLOSE
    ══════════════════════════════════════════ */
    function openChat() {
        chatbox.style.display = "flex";
        chatbox.style.flexDirection = "column";
        chatBody.scrollTop = chatBody.scrollHeight;
        input.focus();
        sessionStorage.setItem(OPEN_KEY, "1");
    }

    function closeChat() {
        chatbox.style.display = "none";
        sessionStorage.setItem(OPEN_KEY, "0");
    }

    toggle.onclick  = openChat;
    closeBtn.onclick = closeChat;

    /* ══════════════════════════════════════════
       HELPERS
    ══════════════════════════════════════════ */
    function getTime() {
        return new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }

    function addBotMsg(html, skipSave) {
        const div = document.createElement("div");
        div.className = "bot-msg";
        div.innerHTML = html + '<span class="msg-time">' + getTime() + '</span>';
        chatBody.appendChild(div);
        chatBody.scrollTop = chatBody.scrollHeight;
        if (!skipSave) saveHistory();
    }

    function addUserMsg(text) {
        const div = document.createElement("div");
        div.className = "user-msg";
        div.innerHTML = '<span>' + text.replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;") + '</span>'
                      + '<span class="msg-time">' + getTime() + '</span>';
        chatBody.appendChild(div);
        chatBody.scrollTop = chatBody.scrollHeight;
        saveHistory();
    }

    function showTyping() {
        const t = document.createElement("div");
        t.className = "typing-indicator";
        t.id = "typing-anim";
        t.innerHTML = "<span></span><span></span><span></span>";
        chatBody.appendChild(t);
        chatBody.scrollTop = chatBody.scrollHeight;
    }

    function removeTyping() {
        const t = document.getElementById("typing-anim");
        if (t) t.remove();
    }

    /* ══════════════════════════════════════════
       SEND
    ══════════════════════════════════════════ */
    function sendMessage(message) {
        message = message.trim();
        if (!message) return;
        addUserMsg(message);
        input.value = "";
        sendBtn.disabled = true;
        input.disabled   = true;
        showTyping();

        fetch("chatbot_api.php", {
            method:  "POST",
            headers: { "Content-Type": "application/json" },
            body:    JSON.stringify({ message: message })
        })
        .then(r => r.json())
        .then(data => {
            removeTyping();
            let html = data.reply || "Sorry, I couldn't process that.";
            if (data.action) {
                html += '<br><a class="chat-action-btn" href="' + data.action.url + '" '
                      + 'onclick="sessionStorage.setItem(\'bfa_chat_open\',\'1\')">'
                      + data.action.label + '</a>';
            }
            addBotMsg(html);
        })
        .catch(() => {
            removeTyping();
            addBotMsg("Sorry, something went wrong. Please try again.");
        })
        .finally(() => {
            sendBtn.disabled = false;
            input.disabled   = false;
            input.focus();
        });
    }

    sendBtn.onclick = () => sendMessage(input.value);
    input.addEventListener("keypress", e => { if (e.key === "Enter") sendMessage(input.value); });

    /* ── Quick Reply ── */
    window.quickReply = function(label) { sendMessage(label); };

    /* ══════════════════════════════════════════
       ON PAGE LOAD — restore history or welcome
    ══════════════════════════════════════════ */
    const hadHistory = restoreHistory();

    if (!hadHistory) {
        // First time ever — show welcome on first open
        toggle.addEventListener("click", function onFirstOpen() {
            setTimeout(() => {
                addBotMsg("Hi! Welcome to the <strong>Bankero and Fisherman Association</strong> portal.", true);
            }, 300);
            setTimeout(() => {
                addBotMsg("I can help you find real-time information about our events, announcements, officers, and membership. How can I assist you today?");
            }, 1000);
            toggle.removeEventListener("click", onFirstOpen);
        });
    }

    // Re-open chat if it was open before navigation
    if (sessionStorage.getItem(OPEN_KEY) === "1") {
        openChat();
    }
})();
</script>
