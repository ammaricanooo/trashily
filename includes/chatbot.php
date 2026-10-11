<!-- ==================== FLOATING CHATBOT TRASHILY (POWERED BY GOOGLE GEMINI) ==================== -->
<style>
    #trashilyChatbotContainer {
        pointer-events: auto;
    }
    #chatWindow {
        overscroll-behavior: contain !important;
    }
    #chatMessages {
        overflow-y: auto !important;
        overscroll-behavior: contain !important;
        -webkit-overflow-scrolling: touch !important;
        touch-action: pan-y !important;
    }
    #chatMessages::-webkit-scrollbar {
        width: 6px;
    }
    #chatMessages::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 9999px;
    }
    #chatMessages::-webkit-scrollbar-thumb {
        background: #94a3b8;
        border-radius: 9999px;
    }
    #chatMessages::-webkit-scrollbar-thumb:hover {
        background: #64748b;
    }
    #quickPromptsBar::-webkit-scrollbar {
        display: none;
    }
</style>
<div id="trashilyChatbotContainer" class="fixed bottom-6 right-6 z-50 font-sans flex flex-col items-end">
    <!-- Chat Window Popup -->
    <div id="chatWindow" class="hidden flex-col w-[calc(100vw-2rem)] sm:w-[410px] h-[510px] max-h-[calc(100vh-115px)] min-h-[340px] bg-white rounded-3xl shadow-2xl border border-slate-200/90 overflow-hidden mb-3 transition-all duration-300 origin-bottom-right transform scale-95 opacity-0" style="overscroll-behavior: contain;">
        <!-- Header -->
        <div class="bg-gradient-to-r from-emerald-700 via-emerald-600 to-teal-700 text-white p-4 px-5 flex items-center justify-between shadow-md shrink-0">
            <div class="flex items-center gap-3">
                <div class="relative">
                    <div class="w-10 h-10 rounded-2xl bg-white/20 border border-white/30 flex items-center justify-center text-white text-lg backdrop-blur-md shadow-inner">
                        <i class="fa-solid fa-robot"></i>
                    </div>
                    <span class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-emerald-400 border-2 border-emerald-800 rounded-full"></span>
                </div>
                <div>
                    <div class="flex items-center gap-1.5">
                        <h3 class="font-display font-extrabold text-sm tracking-tight text-white">Trashy AI</h3>
                        <span class="px-2 py-0.5 rounded-full bg-amber-400/90 text-slate-950 font-black text-[9px] uppercase tracking-wider">Gemini</span>
                    </div>
                    <p class="text-[11px] text-emerald-100 flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-300 animate-pulse"></span>
                        Asisten Ekosistem Trashily
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-1">
                <button type="button" id="btnResetChat" class="w-8 h-8 rounded-full hover:bg-white/15 text-white/80 hover:text-white flex items-center justify-center transition" title="Mulai Obrolan Baru">
                    <i class="fa-solid fa-rotate-right text-xs"></i>
                </button>
                <button type="button" id="btnCloseChat" class="w-8 h-8 rounded-full hover:bg-white/15 text-white/80 hover:text-white flex items-center justify-center transition" title="Tutup Chat">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>
        </div>

        <!-- Quick Prompts Pill Bar -->
        <div id="quickPromptsBar" class="bg-slate-50 border-b border-slate-100 px-4 py-2 flex items-center gap-2 overflow-x-auto no-scrollbar text-xs shrink-0" style="overscroll-behavior: contain;">
            <button type="button" onclick="sendQuickPrompt('Jelaskan tentang TrashSmart IoT')" class="shrink-0 px-2.5 py-1 rounded-full bg-white border border-slate-200 text-slate-700 hover:border-emerald-500 hover:text-emerald-700 hover:bg-emerald-50/50 transition font-medium shadow-2xs">
                🗑️ TrashSmart
            </button>
            <button type="button" onclick="sendQuickPrompt('Apa itu SmartKolecer?')" class="shrink-0 px-2.5 py-1 rounded-full bg-white border border-slate-200 text-slate-700 hover:border-emerald-500 hover:text-emerald-700 hover:bg-emerald-50/50 transition font-medium shadow-2xs">
                🌀 SmartKolecer
            </button>
            <button type="button" onclick="sendQuickPrompt('Jelaskan program GEMBUL dan Creative Eco-Kit')" class="shrink-0 px-2.5 py-1 rounded-full bg-white border border-slate-200 text-slate-700 hover:border-amber-500 hover:text-amber-800 hover:bg-amber-50/50 transition font-medium shadow-2xs">
                🎲 GEMBUL
            </button>
            <button type="button" onclick="sendQuickPrompt('Berapa harga sampah dan poin per kg?')" class="shrink-0 px-2.5 py-1 rounded-full bg-white border border-slate-200 text-slate-700 hover:border-emerald-500 hover:text-emerald-700 hover:bg-emerald-50/50 transition font-medium shadow-2xs">
                💰 Harga Sampah
            </button>
            <button type="button" onclick="sendQuickPrompt('Kapan jadwal kegiatan terdekat?')" class="shrink-0 px-2.5 py-1 rounded-full bg-white border border-slate-200 text-slate-700 hover:border-emerald-500 hover:text-emerald-700 hover:bg-emerald-50/50 transition font-medium shadow-2xs">
                📅 Jadwal Kegiatan
            </button>
            <button type="button" onclick="sendQuickPrompt('Bagaimana cara jemput sampah?')" class="shrink-0 px-2.5 py-1 rounded-full bg-white border border-slate-200 text-slate-700 hover:border-emerald-500 hover:text-emerald-700 hover:bg-emerald-50/50 transition font-medium shadow-2xs">
                🚚 Jemput Sampah
            </button>
        </div>

        <!-- Chat Messages Area -->
        <div id="chatMessages" class="flex-1 min-h-0 p-4 overflow-y-auto space-y-3.5 bg-slate-50/50 text-xs sm:text-sm" style="overscroll-behavior: contain; -webkit-overflow-scrolling: touch;">
            <!-- Welcome Bot Message -->
            <div class="flex items-start gap-2.5 bot-msg">
                <div class="w-7 h-7 rounded-full bg-emerald-600 text-white flex items-center justify-center shrink-0 mt-0.5 text-xs shadow-sm">
                    <i class="fa-solid fa-robot"></i>
                </div>
                <div class="bg-white border border-slate-200 p-3.5 rounded-2xl rounded-tl-sm text-slate-800 shadow-sm max-w-[85%] leading-relaxed">
                    Halo! Saya <strong>Trashy</strong>, asisten AI Trashily yang didukung <strong>Google Gemini</strong>. 🌱<br><br>
                    Ada yang bisa saya bantu seputar <strong>TrashSmart IoT</strong>, <strong>SmartKolecer</strong>, <strong>GEMBUL Creative Eco-Kit</strong>, atau daftar harga &amp; penjemputan sampah?
                </div>
            </div>
        </div>

        <!-- Typing Indicator -->
        <div id="typingIndicator" class="hidden px-4 py-2 bg-slate-50/50 flex items-center gap-2 text-slate-400 text-xs">
            <div class="w-6 h-6 rounded-full bg-emerald-600/80 text-white flex items-center justify-center text-[10px]">
                <i class="fa-solid fa-robot"></i>
            </div>
            <div class="bg-white border border-slate-200 px-3 py-2 rounded-2xl rounded-tl-sm flex items-center gap-1 shadow-2xs">
                <span class="w-1.5 h-1.5 bg-slate-400 rounded-full animate-bounce" style="animation-delay: 0ms;"></span>
                <span class="w-1.5 h-1.5 bg-slate-400 rounded-full animate-bounce" style="animation-delay: 150ms;"></span>
                <span class="w-1.5 h-1.5 bg-slate-400 rounded-full animate-bounce" style="animation-delay: 300ms;"></span>
            </div>
            <span class="text-[11px] text-slate-400">Trashy sedang mengetik...</span>
        </div>

        <!-- Input Footer -->
        <div class="p-3 bg-white border-t border-slate-200">
            <form id="chatForm" onsubmit="handleSendChat(event)" class="flex items-center gap-2">
                <input type="text" id="chatInput" placeholder="Ketik pertanyaanmu di sini..." autocomplete="off" class="flex-1 px-4 py-2.5 rounded-full border border-slate-200 bg-slate-50 text-slate-800 text-xs sm:text-sm focus:outline-none focus:border-emerald-500 focus:bg-white transition placeholder:text-slate-400">
                <button type="submit" id="btnSendChat" class="w-10 h-10 rounded-full bg-emerald-600 hover:bg-emerald-700 text-white flex items-center justify-center shrink-0 shadow-md transition-all active:scale-95 disabled:opacity-50">
                    <i class="fa-solid fa-paper-plane text-xs"></i>
                </button>
            </form>
            <div class="mt-1.5 text-center text-[10px] text-slate-400">
                Powered by <strong>Google Gemini AI</strong> &bull; Trashily Platform
            </div>
        </div>
    </div>

    <!-- Floating Toggle Button -->
    <div class="relative flex items-center justify-end">
        <!-- Floating Tooltip Pill -->
        <div id="chatTooltip" class="hidden sm:flex items-center gap-2 bg-slate-900 text-white text-xs font-semibold px-3 py-1.5 rounded-full shadow-lg mr-3 pointer-events-none transition-all duration-300 opacity-90">
            <span>Tanya Trashy AI</span>
            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
        </div>

        <button type="button" id="btnToggleChat" class="relative group w-14 h-14 rounded-full bg-gradient-to-tr from-emerald-700 via-emerald-600 to-teal-500 text-white shadow-2xl flex items-center justify-center text-xl transition-all duration-300 hover:scale-105 active:scale-95 border-2 border-white ring-4 ring-emerald-500/20" aria-label="Buka Chatbot Trashily">
            <span class="absolute -top-1 -right-1 w-4 h-4 bg-amber-400 border-2 border-white rounded-full flex items-center justify-center">
                <span class="w-1.5 h-1.5 bg-slate-950 rounded-full"></span>
            </span>
            <i id="chatToggleIcon" class="fa-solid fa-comments transition-transform duration-300 group-hover:rotate-6"></i>
        </button>
    </div>
</div>

<!-- Chatbot Client Script -->
<script>
(function() {
    const btnToggle = document.getElementById('btnToggleChat');
    const btnClose = document.getElementById('btnCloseChat');
    const btnReset = document.getElementById('btnResetChat');
    const chatContainer = document.getElementById('trashilyChatbotContainer');
    const chatWindow = document.getElementById('chatWindow');
    const chatToggleIcon = document.getElementById('chatToggleIcon');
    const chatMessages = document.getElementById('chatMessages');
    const chatInput = document.getElementById('chatInput');
    const typingIndicator = document.getElementById('typingIndicator');
    const chatTooltip = document.getElementById('chatTooltip');

    let chatHistory = [];
    let isOpen = false;

    // Load state / history from sessionStorage if available
    try {
        const saved = sessionStorage.getItem('trashily_chat_history');
        if (saved) {
            chatHistory = JSON.parse(saved);
            chatHistory.forEach(msg => appendMessage(msg.role, msg.text, false));
        }
    } catch(e){}

    function toggleChat(open) {
        isOpen = (open !== undefined) ? open : !isOpen;
        if (isOpen) {
            chatWindow.style.display = 'flex';
            chatWindow.classList.remove('hidden');
            chatWindow.classList.add('flex');
            setTimeout(() => {
                chatWindow.classList.remove('scale-95', 'opacity-0');
                chatWindow.classList.add('scale-100', 'opacity-100');
            }, 10);
            chatToggleIcon.classList.remove('fa-comments');
            chatToggleIcon.classList.add('fa-xmark');
            if (chatTooltip) chatTooltip.classList.add('hidden');
            chatInput.focus();
            scrollToBottom();
        } else {
            chatWindow.classList.remove('scale-100', 'opacity-100');
            chatWindow.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                chatWindow.classList.remove('flex');
                chatWindow.classList.add('hidden');
                chatWindow.style.display = 'none';
            }, 250);
            chatToggleIcon.classList.remove('fa-xmark');
            chatToggleIcon.classList.add('fa-comments');
        }
    }

    // Cegah scroll mouse tembus ke halaman utama saat kursor berada di area chatbot
    chatWindow.addEventListener('wheel', function(e) {
        const quickPills = e.target.closest('#quickPromptsBar');
        if (quickPills) {
            quickPills.scrollLeft += (e.deltaY || e.deltaX);
            e.preventDefault();
            e.stopPropagation();
            return;
        }

        if (chatMessages) {
            let delta = e.deltaY;
            if (e.deltaMode === 1) { // Line delta (Firefox / standard mouse setting)
                delta *= 32;
            } else if (e.deltaMode === 2) { // Page delta
                delta *= chatMessages.clientHeight;
            }
            chatMessages.scrollTop += delta;
            e.preventDefault();
            e.stopPropagation();
        }
    }, { passive: false });

    // Cegah touch scroll tembus ke background di mobile/touch screen
    let touchStartY = 0;
    chatMessages.addEventListener('touchstart', function(e) {
        if (e.touches.length === 1) {
            touchStartY = e.touches[0].clientY;
        }
    }, { passive: true });

    chatMessages.addEventListener('touchmove', function(e) {
        if (e.touches.length === 1) {
            const currentY = e.touches[0].clientY;
            const deltaY = touchStartY - currentY;
            const isAtTop = chatMessages.scrollTop <= 0 && deltaY < 0;
            const isAtBottom = (chatMessages.scrollTop + chatMessages.clientHeight >= chatMessages.scrollHeight - 1) && deltaY > 0;
            if (isAtTop || isAtBottom) {
                e.preventDefault();
            }
        }
    }, { passive: false });

    if (btnToggle) btnToggle.addEventListener('click', () => toggleChat());
    if (btnClose) btnClose.addEventListener('click', () => toggleChat(false));
    
    if (btnReset) {
        btnReset.addEventListener('click', () => {
            if (confirm('Mulai obrolan baru dan hapus riwayat chat?')) {
                chatHistory = [];
                sessionStorage.removeItem('trashily_chat_history');
                chatMessages.innerHTML = `
                    <div class="flex items-start gap-2.5 bot-msg">
                        <div class="w-7 h-7 rounded-full bg-emerald-600 text-white flex items-center justify-center shrink-0 mt-0.5 text-xs shadow-sm">
                            <i class="fa-solid fa-robot"></i>
                        </div>
                        <div class="bg-white border border-slate-200 p-3.5 rounded-2xl rounded-tl-sm text-slate-800 shadow-sm max-w-[85%] leading-relaxed">
                            Obrolan telah direset. Silakan tanyakan apa saja seputar <strong>TrashSmart</strong>, <strong>SmartKolecer</strong>, <strong>GEMBUL</strong>, atau <strong>Trashily</strong>!
                        </div>
                    </div>
                `;
            }
        });
    }

    function scrollToBottom() {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    function formatMarkdown(text) {
        // Simple safe markdown formatting for chatbot
        let html = text
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');

        // Bold **text**
        html = html.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
        // Italic *text*
        html = html.replace(/\*(.*?)\*/g, '<em>$1</em>');
        // Bullet points
        html = html.replace(/^\s*[•\-\*]\s+(.*)$/gm, '<li class="ml-4 list-disc">$1</li>');
        // Numbered list
        html = html.replace(/^\s*(\d+)\.\s+(.*)$/gm, '<li class="ml-4 list-decimal" value="$1">$2</li>');
        // Links [label](url)
        html = html.replace(/\[(.*?)\]\((.*?)\)/g, '<a href="$2" class="text-emerald-700 underline font-semibold hover:text-emerald-900">$1</a>');
        // Newlines
        html = html.replace(/\n\n/g, '<br><br>').replace(/\n/g, '<br>');

        return html;
    }

    function appendMessage(role, text, save = true) {
        const wrap = document.createElement('div');
        if (role === 'user') {
            wrap.className = 'flex justify-end user-msg';
            wrap.innerHTML = `
                <div class="bg-gradient-to-r from-emerald-600 to-teal-600 text-white p-3 px-4 rounded-2xl rounded-tr-sm shadow-sm max-w-[85%] leading-relaxed break-words">
                    ${text.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')}
                </div>
            `;
        } else {
            wrap.className = 'flex items-start gap-2.5 bot-msg';
            wrap.innerHTML = `
                <div class="w-7 h-7 rounded-full bg-emerald-600 text-white flex items-center justify-center shrink-0 mt-0.5 text-xs shadow-sm">
                    <i class="fa-solid fa-robot"></i>
                </div>
                <div class="bg-white border border-slate-200 p-3.5 rounded-2xl rounded-tl-sm text-slate-800 shadow-sm max-w-[85%] leading-relaxed break-words">
                    ${formatMarkdown(text)}
                </div>
            `;
        }
        chatMessages.appendChild(wrap);
        scrollToBottom();

        if (save) {
            chatHistory.push({ role, text });
            try {
                sessionStorage.setItem('trashily_chat_history', JSON.stringify(chatHistory.slice(-20)));
            } catch(e){}
        }
    }

    window.sendQuickPrompt = function(promptText) {
        if (!isOpen) toggleChat(true);
        chatInput.value = promptText;
        handleSendChat(new Event('submit'));
    };

    window.handleSendChat = async function(e) {
        if (e) e.preventDefault();
        const msg = chatInput.value.trim();
        if (!msg) return;

        appendMessage('user', msg);
        chatInput.value = '';
        chatInput.focus();

        typingIndicator.classList.remove('hidden');
        scrollToBottom();

        try {
            const apiEndpoint = '<?= defined("BASE_URL") ? BASE_URL : "" ?>/api/chatbot.php';
            const res = await fetch(apiEndpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    message: msg,
                    history: chatHistory.slice(-10)
                })
            });

            const data = await res.json();
            typingIndicator.classList.add('hidden');

            if (data && data.reply) {
                appendMessage('model', data.reply);
            } else {
                appendMessage('model', 'Maaf, terjadi kendala saat memproses jawaban. Silakan coba kembali.');
            }
        } catch(err) {
            typingIndicator.classList.add('hidden');
            appendMessage('model', 'Koneksi terputus. Pastikan server web Anda aktif.');
        }
    };
})();
</script>
