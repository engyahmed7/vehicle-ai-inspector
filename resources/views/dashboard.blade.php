<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">Welcome, {{ Auth::user()->name }}!</h3>
                    <p class="text-gray-600 mb-6">Account Type: {{ ucfirst(str_replace('_', ' ', Auth::user()->role)) }}</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="bg-blue-50 p-6 rounded-lg">
                            <h4 class="text-lg font-semibold text-blue-800 mb-2">Vehicle Analysis</h4>
                            <p class="text-blue-600 mb-4">Upload and analyze vehicle images using AI</p>
                            <a href="{{ route('upload.index') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                                Start Analysis
                            </a>
                        </div>
                        
                        <div class="bg-purple-50 p-6 rounded-lg">
                            <h4 class="text-lg font-semibold text-purple-800 mb-2">
                                {{ Auth::user()->role === 'car_owner' ? 'My Vehicles' : 'Browse Vehicles' }}
                            </h4>
                            <p class="text-purple-600 mb-4">
                                {{ Auth::user()->role === 'car_owner' ? 'View and manage your vehicles' : 'Find vehicles and contact owners' }}
                            </p>
                            <a href="{{ route('cars.index') }}" class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700">
                                {{ Auth::user()->role === 'car_owner' ? 'View My Cars' : 'Browse Cars' }}
                            </a>
                        </div>
                        
                        <div class="bg-green-50 p-6 rounded-lg">
                            <h4 class="text-lg font-semibold text-green-800 mb-2">Chat</h4>
                            <p class="text-green-600 mb-4">Communicate with {{ Auth::user()->role === 'car_owner' ? 'customers' : 'car owners' }}</p>
                            <a href="{{ route('chat') }}" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                                Open Chat
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
