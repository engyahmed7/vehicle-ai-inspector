@extends('layouts.app')

@section('title', 'Vehicle Image Analysis')
@section('styles')
<link rel="stylesheet" href="{{ asset('css/upload-index.css') }}">
@endsection

@section('scripts')
<script src="{{ asset('js/upload-index.js') }}"></script>
@endsection

@section('content')
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
                    <label class="upload-label">Front View</label>
                    <div class="upload-description">Clear front view of the vehicle</div>
                    <input type="file" name="images[front]" class="file-input" accept="image/*">
                    <div class="file-name"></div>
                </div>

                <div class="upload-item type-exterior" data-type="rear">
                    <div class="upload-icon">🔄</div>
                    <label class="upload-label">Rear View</label>
                    <div class="upload-description">Clear rear view of the vehicle</div>
                    <input type="file" name="images[rear]" class="file-input" accept="image/*">
                    <div class="file-name"></div>
                </div>

                <div class="upload-item type-exterior" data-type="left">
                    <div class="upload-icon">⬅️</div>
                    <label class="upload-label">Left Side</label>
                    <div class="upload-description">Left side profile view</div>
                    <input type="file" name="images[left]" class="file-input" accept="image/*">
                    <div class="file-name"></div>
                </div>

                <div class="upload-item type-exterior" data-type="right">
                    <div class="upload-icon">➡️</div>
                    <label class="upload-label">Right Side</label>
                    <div class="upload-description">Right side profile view</div>
                    <input type="file" name="images[right]" class="file-input" accept="image/*">
                    <div class="file-name"></div>
                </div>

                <!-- Interior Images -->
                <div class="upload-item type-interior" data-type="interior_front">
                    <div class="upload-icon">🪑</div>
                    <label class="upload-label">Interior Front</label>
                    <div class="upload-description">Front seats and interior view</div>
                    <input type="file" name="images[interior_front]" class="file-input" accept="image/*">
                    <div class="file-name"></div>
                </div>

                <div class="upload-item type-interior" data-type="interior_rear">
                    <div class="upload-icon">🛋️</div>
                    <label class="upload-label">Interior Rear</label>
                    <div class="upload-description">Rear seats and interior space</div>
                    <input type="file" name="images[interior_rear]" class="file-input" accept="image/*">
                    <div class="file-name"></div>
                </div>

                <div class="upload-item type-detail" data-type="dashboard">
                    <div class="upload-icon">📊</div>
                    <label class="upload-label">Dashboard</label>
                    <div class="upload-description">Dashboard and instrument panel</div>
                    <input type="file" name="images[dashboard]" class="file-input" accept="image/*">
                    <div class="file-name"></div>
                </div>

                <div class="upload-item type-detail" data-type="license_close">
                    <div class="upload-icon">🏷️</div>
                    <label class="upload-label">License Plate</label>
                    <div class="upload-description">Close-up of license plate</div>
                    <input type="file" name="images[license_close]" class="file-input" accept="image/*">
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
@endsection