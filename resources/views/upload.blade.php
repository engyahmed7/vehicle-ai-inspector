<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Vehicle Image Analysis') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <link rel="stylesheet" href="{{ asset('css/upload-index.css') }}">
                    <script src="{{ asset('js/upload-index.js') }}"></script>
                    
                    <div class="container">
                        <div class="header">
                            <h1>Vehicle Image Analysis</h1>
                            <p>Upload high-quality images of your vehicle for comprehensive analysis</p>
                        </div>

                        <div class="form-container">
                            <form action="{{ route('upload.analyze') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
                                @csrf

                                <div class="upload-grid">
                                    <!-- Exterior Images -->
                                    <div class="upload-item type-exterior" data-type="front">
                                        <div class="upload-icon">🚗</div>
                                        <label class="upload-label" for="front">Front View</label>
                                        <div class="upload-description">Clear front view of the vehicle</div>
                                        <input type="file" name="images[front]" id="front" class="file-input" accept="image/*">
                                        <div class="file-name"></div>
                                    </div>

                                    <div class="upload-item type-exterior" data-type="rear">
                                        <div class="upload-icon">🔄</div>
                                        <label class="upload-label" for="rear">Rear View</label>
                                        <div class="upload-description">Clear rear view of the vehicle</div>
                                        <input type="file" name="images[rear]" id="rear" class="file-input" accept="image/*">
                                        <div class="file-name"></div>
                                    </div>

                                    <div class="upload-item type-exterior" data-type="left">
                                        <div class="upload-icon">⬅️</div>
                                        <label class="upload-label" for="left">Left Side</label>
                                        <div class="upload-description">Left side profile view</div>
                                        <input type="file" name="images[left]" id="left" class="file-input" accept="image/*">
                                        <div class="file-name"></div>
                                    </div>

                                    <div class="upload-item type-exterior" data-type="right">
                                        <div class="upload-icon">➡️</div>
                                        <label class="upload-label" for="right">Right Side</label>
                                        <div class="upload-description">Right side profile view</div>
                                        <input type="file" name="images[right]" id="right" class="file-input" accept="image/*">
                                        <div class="file-name"></div>
                                    </div>

                                    <!-- Interior Images -->
                                    <div class="upload-item type-interior" data-type="interior_front">
                                        <div class="upload-icon">🪑</div>
                                        <label class="upload-label" for="interior_front">Interior Front</label>
                                        <div class="upload-description">Front seats and interior view</div>
                                        <input type="file" name="images[interior_front]" id="interior_front" class="file-input" accept="image/*">
                                        <div class="file-name"></div>
                                    </div>

                                    <div class="upload-item type-interior" data-type="interior_rear">
                                        <div class="upload-icon">🛋️</div>
                                        <label class="upload-label" for="interior_rear">Interior Rear</label>
                                        <div class="upload-description">Rear seats and interior space</div>
                                        <input type="file" name="images[interior_rear]" id="interior_rear" class="file-input" accept="image/*">
                                        <div class="file-name"></div>
                                    </div>

                                    <div class="upload-item type-detail" data-type="dashboard">
                                        <div class="upload-icon">📊</div>
                                        <label class="upload-label" for="dashboard">Dashboard</label>
                                        <div class="upload-description">Dashboard and instrument panel</div>
                                        <input type="file" name="images[dashboard]" id="dashboard" class="file-input" accept="image/*">
                                        <div class="file-name"></div>
                                    </div>

                                    <div class="upload-item type-detail" data-type="vin_area">
                                        <div class="upload-icon">🔢</div>
                                        <label class="upload-label" for="vin_area">VIN Area</label>
                                        <div class="upload-description">vin area and instrument panel</div>
                                        <input type="file" name="images[vin_area]" id="vin_area" class="file-input" accept="image/*">
                                        <div class="file-name"></div>
                                    </div>

                                    <div class="upload-item type-detail" data-type="license_close">
                                        <div class="upload-icon">🏷️</div>
                                        <label class="upload-label" for="license_close">License Plate</label>
                                        <div class="upload-description">Close-up of license plate</div>
                                        <input type="file" name="images[license_close]" id="license_close" class="file-input" accept="image/*">
                                        <div class="file-name"></div>
                                    </div>

                                    <div class="upload-item type-detail" data-type="insurance_card">
                                        <div class="upload-icon">🏷️</div>
                                        <label class="upload-label" for="insurance_card">Insurance Card</label>
                                        <div class="upload-description">Close-up of insurance card</div>
                                        <input type="file" name="images[insurance_card]" id="insurance_card" class="file-input" accept="image/*">
                                        <div class="file-name"></div>
                                    </div>
                                    
                                    <div class="upload-item type-detail" data-type="mvr">
                                        <div class="upload-icon">🏷️</div>
                                        <label class="upload-label" for="mvr">MVR</label>
                                        <div class="upload-description">Close-up of MVR</div>
                                        <input type="file" name="images[mvr]" id="mvr" class="file-input" accept="image/*">
                                        <div class="file-name"></div>
                                    </div>
                                </div>

                                <div class="submit-section">
                                    <button type="submit" class="analyze-btn" id="analyzeBtn">
                                        🔍 Start Analysis
                                    </button>
                                    <div class="progress-bar">
                                        <div class="progress-fill"></div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
