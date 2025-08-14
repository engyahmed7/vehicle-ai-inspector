<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ Auth::user()->role === 'car_owner' ? __('My Vehicles') : __('Browse Vehicles') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <meta name="csrf-token" content="{{ csrf_token() }}">

            @if(Auth::user()->role === 'car_owner')
                <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h3 class="font-semibold text-blue-800 mb-2">Manage Your Vehicles</h3>
                    <p class="text-blue-600">These are your listed vehicles. Customers can contact you about these cars.</p>
                </div>
            @else
                <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
                    <h3 class="font-semibold text-green-800 mb-2">Available Vehicles</h3>
                    <p class="text-green-600">Browse available vehicles and start a conversation with the owners.</p>
                </div>
            @endif

            @if($cars->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($cars as $car)
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <div class="flex justify-between items-start mb-4">
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-900">
                                            {{ $car->license_plate ?: 'Vehicle #' . $car->id }}
                                        </h3>
                                        <p class="text-sm text-gray-600">Owner: {{ $car->user->name }}</p>
                                    </div>
                                    @if(Auth::user()->role === 'customer')
                                        <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">Available</span>
                                    @endif
                                </div>

                                <div class="space-y-2 text-sm text-gray-600 mb-4">
                                    @if($car->odometer)
                                        <div>📏 Odometer: {{ number_format($car->odometer) }} miles</div>
                                    @endif
                                    @if($car->fuel_level)
                                        <div>⛽ Fuel Level: {{ $car->fuel_level }}%</div>
                                    @endif
                                </div>

                                <div class="flex space-x-2">
                                    <a href="{{ route('cars.show', $car->id) }}"
                                       class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-center flex-1">
                                        View Details
                                    </a>

                                    @if(Auth::user()->role === 'customer')
                                        <button onclick="startConversation({{ $car->id }}, '{{ $car->license_plate ?: 'Vehicle #' . $car->id }}')"
                                                class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                                            💬 Chat
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-center text-gray-500">
                        @if(Auth::user()->role === 'car_owner')
                            No vehicles listed yet. Upload vehicle images to create listings.
                        @else
                            No vehicles available at the moment.
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div id="conversation-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-md mx-4">
            <h3 class="text-lg font-semibold mb-4">Start Conversation</h3>
            <p class="text-gray-600 mb-4">Send a message to the owner of <span id="car-name"></span></p>

            <form id="start-conversation-form">
                <input type="hidden" id="selected-car-id">
                <textarea
                    id="initial-message"
                    placeholder="Hi! I'm interested in your vehicle..."
                    class="w-full border rounded-lg px-3 py-2 h-24 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required
                ></textarea>

                <div class="flex justify-end space-x-2 mt-4">
                    <button type="button" onclick="closeModal()"
                            class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400">
                        Cancel
                    </button>
                    <button type="submit"
                            class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                        Start Chat
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function startConversation(carId, carName) {
            document.getElementById('selected-car-id').value = carId;
            document.getElementById('car-name').textContent = carName;
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

            const carId = document.getElementById('selected-car-id').value;
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
                        car_id: carId,
                        customer_id: {{ Auth::id() }},
                        initial_message: message
                    })
                });

                if (response.ok) {
                    const conversation = await response.json();
                    closeModal();

                    alert('Conversation started successfully!');
                    window.location.href = '/chat';
                } else {
                    const error = await response.json();
                    alert('Error starting conversation: ' + (error.message || 'Unknown error'));
                }
            } catch (error) {
                console.error('Error starting conversation:', error);
                alert('Error starting conversation. Please try again.');
            }
        });

        document.getElementById('conversation-modal').addEventListener('click', (e) => {
            if (e.target === e.currentTarget) {
                closeModal();
            }
        });
    </script>
</x-app-layout>
