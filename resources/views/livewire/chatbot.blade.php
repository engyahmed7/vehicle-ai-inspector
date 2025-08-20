<div class="chat-widget">
    <div class="chat-bubble" onclick="toggleChat()">
        <i class="fas fa-comments"></i>
        <div class="notification-badge">1</div>
    </div>

    <div class="chat-window" id="chatWindow" wire:ignore.self>
        <div class="chat-header">
            <div class="chat-header-info">
                <div class="chat-avatar">
                    <i class="fas fa-robot"></i>
                </div>
                <div>
                    <h3 class="chat-title">AI Assistant</h3>
                    <p class="chat-subtitle">Online now</p>
                </div>
            </div>
            <button class="chat-close" onclick="toggleChat()">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="chat-messages" id="messagesContainer">
            <div class="welcome-message">
                <h3>👋 Hello there!</h3>
                <p>How can I help you today?</p>
            </div>

            <div id="messagesList">
                @foreach ($messages as $interaction)
                    <div class="message-item">
                        <div class="user-row">
                            <div class="user-message">
                                <strong>You:</strong> {{ $interaction->question }}
                            </div>
                        </div>

                        <div class="ai-row">
                            <div class="ai-message">
                                @if (preg_match('/\b(reset|start over)\b/i', $interaction->question))
                                    <strong>AI:</strong> <em>Conversation has been reset.</em>
                                @else
                                    <strong>AI:</strong> {!! preg_replace(
                                        ['/\*\*(.*?)\*\*/', '/\*(.*?)\*/', '/\*\s(.*?)\s\*/', '/\~\~(.*?)\~\~/', '/\_(.*?)\_/'],
                                        ['<strong>$1</strong>', '<strong>$1</strong>', '<strong>$1</strong>', '<del>$1</del>', '<em>$1</em>'],
                                        e($interaction->answer),
                                    ) !!}
                                @endif
                            </div>
                        </div>
                    </div>
                    <hr class="divider">
                @endforeach
            </div>
        </div>

        <div class="chat-input-area">
            <form wire:submit.prevent="askQuestion" class="chat-form" id="chatForm">
                <input type="text" wire:model.defer="question" placeholder="Type your message..." required
                    class="chat-input" wire:loading.attr="disabled" id="chatInput" />
                <button type="submit" class="send-btn" wire:loading.attr="disabled" wire:loading.class="loading"
                    wire:target="askQuestion">
                    <i class="fas fa-paper-plane send-icon"></i>
                    <i class="fas fa-spinner fa-spin loading-spinner"></i>
                </button>
            </form>
        </div>
    </div>
</div>


<script>
    let chatOpen = false;

    function toggleChat() {
        const chatWindow = document.getElementById('chatWindow');
        const chatBubble = document.querySelector('.chat-bubble');
        const notificationBadge = document.querySelector('.notification-badge');

        if (chatOpen) {
            chatWindow.classList.remove('open');
            chatBubble.innerHTML = '<i class="fas fa-comments"></i>';
            if (notificationBadge) {
                chatBubble.appendChild(notificationBadge);
            }
        } else {
            chatWindow.classList.add('open');
            chatBubble.innerHTML = '<i class="fas fa-times"></i>';
            if (notificationBadge) {
                notificationBadge.remove();
            }

            setTimeout(() => {
                const messagesContainer = document.querySelector('.chat-messages');
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            }, 100);
        }

        chatOpen = !chatOpen;
    }

    document.addEventListener('DOMContentLoaded', function() {
        const chatForm = document.querySelector('.chat-form');
        const chatInputArea = document.querySelector('.chat-input-area');
        const chatInput = document.getElementById('chatInput');

        if (chatForm) {
            chatForm.addEventListener('click', function(e) {
                e.stopPropagation();
            });

            chatForm.addEventListener('submit', function(e) {
                setTimeout(() => {
                    if (chatInput) {
                        chatInput.value = '';
                    }
                }, 100);
            });
        }

        if (chatInputArea) {
            chatInputArea.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        }
    });

    document.addEventListener('livewire:load', function() {
        Livewire.hook('message.processed', (message, component) => {
            const chatWindow = document.getElementById('chatWindow');
            const chatInput = document.getElementById('chatInput');

            if (chatOpen && chatWindow) {
                chatWindow.classList.add('open');
            }

            if (chatOpen) {
                setTimeout(() => {
                    const messagesContainer = document.querySelector('.chat-messages');
                    if (messagesContainer) {
                        messagesContainer.scrollTop = messagesContainer.scrollHeight;
                    }
                }, 100);
            }

            if (chatInput) {
                chatInput.value = '';
                chatInput.dispatchEvent(new Event('input'));
            }
        });
    });
</script>
