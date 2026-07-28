<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Live Chat</h2>

            <!-- Notification Bell Icon -->
            <div class="relative cursor-pointer" id="bell-icon">
                <svg class="w-7 h-7 text-gray-600 hover:text-indigo-600 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                </svg>
                <span id="notification-badge" class="hidden absolute top-0 right-0 block w-3 h-3 bg-red-600 rounded-full border-2 border-white"></span>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 flex space-x-4">

            <!-- Users List -->
            <div class="w-1/3 bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                <h3 class="font-bold mb-4 border-b pb-2 text-gray-700">Users</h3>
                <ul id="user-list" class="space-y-1">
                    @foreach($users as $user)
                        <li
                            id="user-item-{{ $user->id }}"
                            class="user-item flex items-center gap-3 p-3 hover:bg-indigo-50 cursor-pointer rounded-lg transition"
                            onclick="openChat({{ $user->id }}, '{{ $user->name }}')"
                        >
                            <div class="w-9 h-9 rounded-full bg-indigo-600 text-white flex items-center justify-center text-sm font-semibold shrink-0 p-2">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                            <span class="font-medium text-gray-700 truncate">{{ $user->name }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>

            <!-- Chat Box -->
            <div class="w-2/3 bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 flex flex-col h-[500px]">

                <!-- Chat Header -->
                <h3 class="font-bold mb-4 border-b pb-2 flex justify-between items-center">
                    <span id="chat-header" class="text-gray-700">Select a user to chat</span>
                </h3>

                <div id="chat-box" class="flex-1 overflow-y-auto mb-4 p-4 bg-gray-50 rounded-lg hidden flex-col space-y-3 border">
                    <!-- Messages + typing bubble load here -->
                </div>

                <div id="message-form" class="hidden flex space-x-2">
                    <input type="text" id="message-input" class="flex-1 border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 rounded-lg" placeholder="Type a message...">
                    <button onclick="sendMessage()" class="bg-indigo-600 hover:bg-indigo-700 transition text-white px-6 py-2 rounded-lg shadow-md">Send</button>
                </div>
            </div>

        </div>
    </div>

    <!-- Typing bubble animation styles -->
    <style>
        .typing-bubble {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 10px 14px;
            background: #fff;
            border: 1px solid #f3f4f6;
            border-radius: 1rem 1rem 1rem 0.25rem;
            width: fit-content;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        }
        .typing-bubble .dot {
            width: 6px;
            height: 6px;
            background: #9ca3af;
            border-radius: 50%;
            animation: typing-bounce 1.2s infinite ease-in-out;
        }
        .typing-bubble .dot:nth-child(1) { animation-delay: 0s; }
        .typing-bubble .dot:nth-child(2) { animation-delay: 0.15s; }
        .typing-bubble .dot:nth-child(3) { animation-delay: 0.3s; }

        @keyframes typing-bounce {
            0%, 60%, 100% { transform: translateY(0); opacity: 0.5; }
            30% { transform: translateY(-4px); opacity: 1; }
        }

        .user-item.active {
            background-color: #eef2ff;
        }
    </style>

    <!-- JavaScript Logic -->
    <script type="module">
        let currentUserId = {{ auth()->id() }};
        let activeChatUserId = null;
        let typingTimer;

        const notifySound = new Audio('/notification.mp3');

        if ("Notification" in window) {
            Notification.requestPermission();
        }

        if (window.Echo) {

            // 1. Listen for New Messages
            window.Echo.private(`chat.${currentUserId}`)
                .listen('MessageSent', (e) => {

                    if (activeChatUserId === e.message.sender_id && !document.hidden) {
                        hideTypingBubble();
                        appendMessage(e.message.message, 'left');
                        notifySound.play().catch(err => console.log(err));
                    } else {
                        let badge = document.getElementById('notification-badge');
                        if (badge) {
                            badge.classList.remove('hidden');
                            badge.classList.add('animate-bounce');
                        }

                        if ("Notification" in window && Notification.permission === "granted") {
                            let sysNotification = new Notification("New Message", {
                                body: e.message.message,
                                vibrate: [200, 100, 200]
                            });
                            sysNotification.onclick = function () {
                                window.focus();
                                this.close();
                            };
                        }

                        notifySound.play().catch(err => console.log(err));
                    }
                });

            // 2. Listen for Typing Signal (shown as a bubble inside the chat area)
            window.Echo.join('typing')
                .listenForWhisper('typing', (e) => {
                    if (e.receiver_id === currentUserId && e.sender_id === activeChatUserId) {
                        showTypingBubble();

                        clearTimeout(typingTimer);
                        typingTimer = setTimeout(() => {
                            hideTypingBubble();
                        }, 2000);
                    }
                });

        } else {
            console.error("Laravel Echo load नहीं हुआ है।");
        }

        // 3. Open Chat Window
        window.openChat = function (userId, userName) {
            activeChatUserId = userId;

            document.querySelectorAll('.user-item').forEach(el => el.classList.remove('active'));
            let selected = document.getElementById(`user-item-${userId}`);
            if (selected) selected.classList.add('active');

            let badge = document.getElementById('notification-badge');
            if (badge) {
                badge.classList.add('hidden');
                badge.classList.remove('animate-bounce');
            }

            document.getElementById('chat-header').innerText = `Chatting with ${userName}`;
            document.getElementById('chat-box').classList.remove('hidden');
            document.getElementById('chat-box').classList.add('flex');
            document.getElementById('message-form').classList.remove('hidden');
            document.getElementById('chat-box').innerHTML = '';
            document.getElementById('message-input').focus();

            window.axios.get(`/chat/${userId}`).then(response => {
                response.data.forEach(msg => {
                    let side = msg.sender_id === currentUserId ? 'right' : 'left';
                    appendMessage(msg.message, side);
                });
            }).catch(error => console.error("पुराने मैसेज लोड करने में एरर:", error));
        }

        // 4. Send Message
        window.sendMessage = function () {
            let input = document.getElementById('message-input');
            let message = input.value;

            if (message.trim() === '') return;

            appendMessage(message, 'right');
            input.value = '';

            window.axios.post('/chat', {
                receiver_id: activeChatUserId,
                message: message
            }).catch(error => console.error("मैसेज भेजने में एरर:", error));
        }

        // 5. Send Typing Signal
        document.getElementById('message-input').addEventListener('input', function () {
            if (activeChatUserId && window.Echo) {
                window.Echo.join('typing')
                    .whisper('typing', {
                        sender_id: currentUserId,
                        receiver_id: activeChatUserId
                    });
            }
        });

        // 6. Helper: append a chat message bubble
        function appendMessage(text, side) {
            let box = document.getElementById('chat-box');
            let div = document.createElement('div');

            if (side === 'right') {
                div.className = "bg-indigo-600 text-white self-end px-4 py-2 rounded-2xl rounded-br-none max-w-[75%] shadow w-fit break-words";
            } else {
                div.className = "bg-white text-gray-800 self-start px-4 py-2 rounded-2xl rounded-bl-none max-w-[75%] shadow border border-gray-100 w-fit break-words";
            }

            div.innerText = text;

            // Keep the typing bubble (if present) pinned to the bottom
            let bubble = document.getElementById('typing-bubble-wrapper');
            if (bubble) {
                box.insertBefore(div, bubble);
            } else {
                box.appendChild(div);
            }

            box.scrollTop = box.scrollHeight;
        }

        // 7. Helper: show/hide the "typing..." bubble inside the chat area
        function showTypingBubble() {
            let box = document.getElementById('chat-box');
            if (document.getElementById('typing-bubble-wrapper')) return; // already showing

            let wrapper = document.createElement('div');
            wrapper.id = 'typing-bubble-wrapper';
            wrapper.className = 'self-start';
            wrapper.innerHTML = `
                <div class="typing-bubble">
                    <span class="dot"></span>
                    <span class="dot"></span>
                    <span class="dot"></span>
                </div>
            `;

            box.appendChild(wrapper);
            box.scrollTop = box.scrollHeight;
        }

        function hideTypingBubble() {
            let wrapper = document.getElementById('typing-bubble-wrapper');
            if (wrapper) wrapper.remove();
        }

        // 8. Enter key sends message
        document.getElementById('message-input').addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });
    </script>
</x-app-layout>