<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Vehicle Details') }} - {{ $car->license_plate ?: 'Vehicle #' . $car->id }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <meta name="csrf-token" content="{{ csrf_token() }}">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">

                    <div class="mb-6"p>
                        <a href="{{ route('cars.index') }}" class="text-blue-600 hover:text-blue-800">
                            ← Back to Vehicles
                        </a>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-lg font-semibold mb-4">Vehicle Information</h3>
                            <div class="space-y-3">
                                <div>
                                    <span class="font-medium text-gray-700">License Plate:</span>
                                    <span class="ml-2">{{ $car->license_plate ?: 'Not specified' }}</span>
                                </div>

                                @if($car->odometer)
                                <div>
                                    <span class="font-medium text-gray-700">Odometer:</span>
                                    <span class="ml-2">{{ number_format($car->odometer) }} miles</span>
                                </div>
                                @endif

                                @if($car->fuel_level)
                                <div>
                                    <span class="font-medium text-gray-700">Fuel Level:</span>
                                    <span class="ml-2">{{ $car->fuel_level }}%</span>
                                </div>
                                @endif

                                <div>
                                    <span class="font-medium text-gray-700">Owner:</span>
                                    <span class="ml-2">{{ $car->user->name }}</span>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-lg font-semibold mb-4">Actions</h3>

                            @if(Auth::user()->role === 'customer')
                                @if($existingConversation)
                                    <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                                        <p class="text-green-800 mb-2">You already have a conversation about this vehicle.</p>
                                        <a href="{{ route('chat') }}"
                                           class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                                            Continue Chat
                                        </a>
                                    </div>
                                @else
                                    <div class="mb-4">
                                        <button onclick="startConversation()"
                                                class="bg-blue-600 text-white px-6 py-3 rounded hover:bg-blue-700 w-full">
                                            💬 Contact Owner
                                        </button>
                                    </div>
                                @endif
                            @else
                                <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
                                    <p class="text-blue-800">This is your vehicle. Customers can contact you about it.</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    @if($car->image_url)
                    <div class="mt-8">
                        <h3 class="text-lg font-semibold mb-4">Vehicle Image</h3>
                        <img src="{{ $car->image_url }}"
                             alt="Vehicle Image"
                             class="w-full max-w-2xl rounded-lg shadow-lg">
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if(Auth::user()->role === 'customer' && !$existingConversation)
    <div id="conversation-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-md mx-4">
            <h3 class="text-lg font-semibold mb-4">Contact {{ $car->user->name }}</h3>
            <p class="text-gray-600 mb-4">Send a message about {{ $car->license_plate ?: 'Vehicle #' . $car->id }}</p>

            <form id="start-conversation-form">
                <textarea
                    id="initial-message"
                    placeholder="Hi! I'm interested in your vehicle. Could you tell me more about its condition and availability?"
                    class="w-full border rounded-lg px-3 py-2 h-32 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required
                ></textarea>

                <div class="flex justify-end space-x-2 mt-4">
                    <button type="button" onclick="closeModal()"
                            class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400">
                        Cancel
                    </button>
                    <button type="submit"
                            class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                        Send Message
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function startConversation() {
            document.getElementById('conversation-modal').classList.remove('hidden');
            document.getElementById('conversation-modal').classList.add('flex');
        }

        function closeModal() {
            document.getElementById('conversation-modal').classList.add('hidden');
            document.getElementById('conversation-modal').classList.remove('flex');
            document.getElementById('initial-message').value = '';
        }

        document.getElementById('start-conversation-form').addEventListener('submit', async (e) => {
            e.preventDefault();

            const message = document.getElementById('initial-message').value.trim();

            if (!message) return;

            try {
                const response = await fetch('/api/conversations', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        car_id: {{ $car->id }},
                        customer_id: {{ Auth::id() }},
                        initial_message: message
                    })
                });

                if (response.ok) {
                    const conversation = await response.json();
                    closeModal();

                    alert('Message sent successfully!');
                    window.location.href = '/chat';
                } else {
                    const error = await response.json();
                    alert('Error sending message: ' + (error.message || 'Unknown error'));
                }
            } catch (error) {
                console.error('Error starting conversation:', error);
                alert('Error sending message. Please try again.');
            }
        });

        document.getElementById('conversation-modal').addEventListener('click', (e) => {
            if (e.target === e.currentTarget) {
                closeModal();
            }
        });
    </script>
    @endif
</x-app-layout>
