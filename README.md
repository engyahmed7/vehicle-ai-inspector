# Vehicle Image Analysis System
[ ![ Laravel Version ]( https://img.shields.io/badge/Laravel-12.x-FF2D20?logo=laravel ) ]( https://laravel.com )
[ ![ PHP Version ]( https://img.shields.io/badge/PHP-8.2+-777BB4?logo=php ) ]( https://php.net )
[ ![ Cloudinary ]( https://img.shields.io/badge/Cloudinary-API-12C6E2?logo=cloudinary ) ]( https://cloudinary.com )
[ ![ Google Vision API ]( https://img.shields.io/badge/Google%20Vision%20API-API-4285F4?logo=google-cloud ) ]( https://cloud.google.com/vision )

A comprehensive Laravel-based application for automated vehicle inspection and analysis using advanced computer vision and OCR technologies.

## Overview

This system processes vehicle images to extract critical information including license plates, odometer readings, and fuel levels. Built with Laravel 12 and powered by Google Cloud Vision API, it provides accurate automated vehicle inspections for insurance, fleet management, and automotive industry applications.
```mermaid
graph TD
    A[Image Upload] --> B[Cloud Storage]
    B --> C[OCR Processing]
    C --> D[License Plate Detection]
    C --> E[Odometer Extraction]
    C --> F[Fuel Level Analysis]
    D --> G[Result Aggregation]
    E --> G
    F --> G
    G --> H[Report Generation]
```
## Features

### **Core Analysis Capabilities**
- **License Plate Detection** - Advanced OCR with multiple pattern recognition algorithms
- **Odometer Reading** - Automatic mileage extraction from dashboard images
- **Fuel Level Assessment** - Visual and text-based fuel gauge analysis
- **Multi-angle Vehicle Capture** - Front, rear, side, interior, and detailed views

### **Technical Features**
- **Cloud Storage** - Cloudinary integration for scalable image management
- **Google Cloud Vision** - Enterprise-grade OCR and text detection
- **Responsive Design** - Modern drag-and-drop interface
- **Real-time Processing** - Live progress tracking and instant results
- **Error Handling** - Comprehensive validation and error recovery

## System Architecture
```mermaid
graph LR
    A[Client Browser] --> B[Laravel Application]
    B --> C[Cloudinary CDN]
    B --> D[Google Vision API]
    C --> E[(Persistent Storage)]
    D --> F[Result Processor]
    F --> B
    B --> A
    
    subgraph GCP Services
    D
    end
    
    subgraph Cloud Infrastructure
    C
    E
    end
```
## Quick Start

### Prerequisites

- **PHP** >= 8.2
- **Composer** 2.x
- **Node.js** >= 18.x
- **Google Cloud Vision API** credentials
- **Cloudinary** account

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/engyahmed7/vehicle-ai-inspector.git
   cd vehicle-ai-inspector
   ```

2. **Install dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configure services**
   ```bash
   # Google Cloud Vision
   GOOGLE_APPLICATION_CREDENTIALS="path/to/credentials.json"
   
   # Cloudinary
   CLOUDINARY_URL=cloudinary://api_key:api_secret@cloud_name
   ```

5. **Run the application**
   ```bash
   composer run dev
   ```

Visit `http://localhost:8000` to access the application.

## API Reference

### Image Analysis Endpoint

**POST** `/upload`

**Request:**
```bash
curl -X POST http://localhost:8000/upload \
  -F "images[front]=@front_view.jpg" \
  -F "images[rear]=@rear_view.jpg" \
  -F "images[dashboard]=@dashboard.jpg"
```

**Response:**
```json
{
  "front": {
    "image_url": "https://res.cloudinary.com/...",
    "license_plate": "ABC-1234"
  },
  "dashboard": {
    "image_url": "https://res.cloudinary.com/...",
    "odometer": "45678",
    "fuel_level": "3/4"
  }
}
```

## Image Types

| Type | Description | Analysis |
|------|-------------|----------|
| `front` | Front vehicle view | License plate detection |
| `rear` | Rear vehicle view | License plate detection |
| `left` | Left side profile | General inspection |
| `right` | Right side profile | General inspection |
| `interior_front` | Front interior | Interior condition |
| `interior_rear` | Rear interior | Interior condition |
| `dashboard` | Dashboard view | Odometer & fuel level |
| `license_close` | License plate close-up | Enhanced plate reading |

## Advanced Features

### Pattern Recognition

The system uses sophisticated regex patterns for license plate detection:

```php
$patterns = [
    '/\b[A-Z0-9]{2,3}-?[A-Z0-9]{3,4}\b/',  // Standard format
    '/\b[A-Z0-9]{7}\b/',                    // Continuous format
    '/\b\d{3,4}\s?[A-Z]{3}\b/'            // Number-letter format
];
```

### Fuel Level Detection

Multi-method approach for accurate fuel level assessment:

- **OCR Text Analysis** - Percentage and fraction detection
- **Visual Analysis** - Gauge needle position (Python integration)
- **Pattern Matching** - Keywords like "full", "empty", "half"

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request
