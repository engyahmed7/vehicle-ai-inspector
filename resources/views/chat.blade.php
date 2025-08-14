<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Chat') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <meta name="csrf-token" content="{{ csrf_token() }}">
            <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>

            <div id="chat-app">
                <div class="bg-white rounded-lg shadow-lg h-96 flex flex-col">
                    <div class="bg-blue-600 text-white p-4 rounded-t-lg">
                        <h3 class="text-lg font-semibold">Chat with {{ Auth::user()->role === 'car_owner' ? 'Customers' : 'Car Owners' }}</h3>
                    </div>

                    <div id="messages" class="flex-1 p-4 overflow-y-auto space-y-3">
                        <div class="text-center text-gray-500">Select a conversation to start chatting</div>
                    </div>

                    <div class="border-t p-4">
                        <form id="message-form" class="flex space-x-2">
                            <input
                                type="text"
                                id="message-input"
                                placeholder="Type a message..."
                                class="flex-1 border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required
                                disabled
                            >
                            <button
                                type="submit"
                                class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 disabled:bg-gray-400"
                                disabled
                            >
                                Send
                            </button>
                        </form>
                    </div>
                </div>

                <div class="mt-6 bg-white rounded-lg shadow-lg p-4">
                    <h4 class="text-lg font-semibold mb-4">Conversations</h4>
                    <div id="conversations-list">
                        <div class="text-center text-gray-500 py-4">Loading conversations...</div>
                    </div>
                </div>
            </div>

            <script>
                   const pusher = new Pusher('{{ config('broadcasting.connections.pusher.key') }}', {
        cluster: '{{ config('broadcasting.connections.pusher.options.cluster') }}',
        authorizer: (channel, options) => {
            return {
                authorize: (socketId, callback) => {
                    fetch('/broadcasting/auth', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            socket_id: socketId,
                            channel_name: channel.name
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        callback(false, data);
                    })
                    .catch(error => {
                        console.error('Auth error:', error);
                        callback(true, error);
                    });
                }
            };
        }
    });

                let currentConversationId = null;
                const currentUserId = {{ Auth::id() }};

                async function loadConversations() {
                    try {
                        const response = await fetch('/api/conversations', {
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        });
                        const conversations = await response.json();
                        displayConversations(conversations);
                    } catch (error) {
                        console.error('Error loading conversations:', error);
                        document.getElementById('conversations-list').innerHTML =
                            '<div class="text-red-500 text-center py-4">Error loading conversations</div>';
                    }
                }

                function displayConversations(conversations) {
                    const container = document.getElementById('conversations-list');
                    if (conversations.length === 0) {
                        container.innerHTML = '<div class="text-center text-gray-500 py-4">No conversations yet</div>';
                        return;
                    }

                    container.innerHTML = conversations.map(conv => `
                        <div class="border rounded p-3 mb-2 cursor-pointer hover:bg-gray-50"
                             onclick="loadMessages(${conv.id})">
                            <div class="font-semibold">${conv.car.license_plate || 'Vehicle #' + conv.car.id}</div>
                            <div class="text-sm text-gray-600">${conv.latest_message?.content || 'No messages yet'}</div>
                        </div>
                    `).join('');
                }

                async function loadMessages(conversationId) {
                    currentConversationId = conversationId;

                    document.getElementById('message-input').disabled = false;
                    document.querySelector('#message-form button').disabled = false;

                    try {
                        const response = await fetch(`/api/conversations/${conversationId}/messages`, {
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        });
                        const messages = await response.json();
                        displayMessages(messages);

                        const channel = pusher.subscribe(`private-conversation.${conversationId}`);
                        channel.bind('MessageSent', function(data) {
                            addMessageToDisplay(data.message);
                        });
                    } catch (error) {
                        console.error('Error loading messages:', error);
                    }
                }

                function displayMessages(messages) {
                    const container = document.getElementById('messages');
                    if (messages.length === 0) {
                        container.innerHTML = '<div class="text-center text-gray-500">No messages yet. Start the conversation!</div>';
                        return;
                    }

                    container.innerHTML = messages.map(message => `
                        <div class="flex ${message.sender_id === currentUserId ? 'justify-end' : 'justify-start'}">
                            <div class="max-w-xs lg:max-w-md px-4 py-2 rounded-lg ${
                                message.sender_id === currentUserId
                                    ? 'bg-blue-600 text-white'
                                    : 'bg-gray-200 text-gray-800'
                            }">
                            <div class="text-sm font-semibold">
                                ${message.sender.name}
                                <span class="text-xs ${
                                        message.sender_id === currentUserId
                                            ? 'bg-blue-600 text-white'
                                            : 'bg-gray-200 text-gray-400'
                                    }">
                                    (${message.sender.role === 'car_owner' ? 'Car Owner' : message.sender.role === 'customer' ? 'Customer' : message.sender.role})
                                </span>
                            </div>
                                <div>${message.content}</div>
                                <div class="text-xs opacity-75">${new Date(message.created_at).toLocaleTimeString()}</div>
                            </div>
                        </div>
                    `).join('');
                    container.scrollTop = container.scrollHeight;
                }

                function addMessageToDisplay(message) {
                    const container = document.getElementById('messages');
                    const messageDiv = document.createElement('div');
                    messageDiv.className = `flex ${message.sender_id === currentUserId ? 'justify-end' : 'justify-start'}`;
                    messageDiv.innerHTML = `
                        <div class="max-w-xs lg:max-w-md px-4 py-2 rounded-lg ${
                            message.sender_id === currentUserId
                                ? 'bg-blue-600 text-white'
                                : 'bg-gray-200 text-gray-800'
                        }">
                            <div class="text-sm font-semibold">${message.sender.name}</div>
                            <div>${message.content}</div>
                            <div class="text-xs opacity-75">${new Date(message.created_at).toLocaleTimeString()}</div>
                        </div>
                    `;
                    container.appendChild(messageDiv);
                    container.scrollTop = container.scrollHeight;
                }

                document.getElementById('message-form').addEventListener('submit', async (e) => {
                    e.preventDefault();
                    const input = document.getElementById('message-input');
                    const content = input.value.trim();

                    if (!content || !currentConversationId) return;

                    try {
                        const response = await fetch(`/api/conversations/${currentConversationId}/messages`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({ content })
                        });

                        if (response.ok) {
                            input.value = '';
                        }
                    } catch (error) {
                        console.error('Error sending message:', error);
                    }
                });

                loadConversations();
            </script>
        </div>
    </div>
</x-app-layout>
