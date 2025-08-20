# Vehicle AI Inspector

[![Laravel Version](https://img.shields.io/badge/Laravel-12.x-FF2D20?logo=laravel)](https://laravel.com)
[![PHP Version](https://img.shields.io/badge/PHP-8.2+-777BB4?logo=php)](https://php.net)
[![Livewire](https://img.shields.io/badge/Livewire-3.x-FB70A9?logo=livewire)](https://livewire.laravel.com)
[![Cloudinary](https://img.shields.io/badge/Cloudinary-API-12C6E2?logo=cloudinary)](https://cloudinary.com)
[![Google Vision API](https://img.shields.io/badge/Google%20Vision%20API-API-4285F4?logo=google-cloud)](https://cloud.google.com/vision)
[![Prism AI](https://img.shields.io/badge/Prism-AI-00D4AA)](https://github.com/SteadyEquipment/prism)

**A comprehensive Laravel-based platform for automated vehicle inspection and analysis using advanced AI, computer vision, and OCR technologies with integrated KYC verification, real-time chat, and multi-role user management.**

## System Architecture

```mermaid
flowchart TB
    subgraph Client [Client Layer]
        A[Web Browser<br>Livewire Components]
    end

    subgraph App [Application Layer]
        C[Laravel Application]
        D[Livewire<br>Reactive UI]
        E[Task Queue<br>Redis/Horizon]
    end

    subgraph Services [AI & Service Layer]
        F[Cloudinary<br>Image CDN]
        G[Google Vision API<br>OCR & Detection]
        H[Python Engine<br>Visual Analysis]
        I[NHTSA API<br>VIN Decoding]
        J[Persona API<br>KYC Verification]
        K[Prism/Groq AI<br>Chatbot NLP]
    end

    subgraph Data [Data Layer]
        L[(MySQL/PostgreSQL<br>Database)]
        M[Redis<br>Cache & Sessions]
        N[Pusher<br>Real-time Events]
    end

    A --> C
    
    C --> F
    C --> G
    C --> H
    C --> I
    C --> J
    C --> K

    C --> L
    C --> M
    C --> N

    E --> G
    E --> H
    E --> I

    style App fill:#e1f5fe
    style Services fill:#f3e5f5
    style Data fill:#fff3e0
```

---
## Key Features Overview

### **Vehicle Analysis & Processing**
- **Multi-Angle Image Analysis** - Front, rear, side, interior, dashboard, and detailed views
- **License Plate Detection** - Advanced OCR with multiple pattern recognition algorithms
- **Odometer Reading Extraction** - Automatic mileage extraction from dashboard images
- **Fuel Level Assessment** - Combined OCR text analysis and Python-based visual gauge reading
- **VIN Processing** - Extract and decode VIN numbers with NHTSA API integration
- **Insurance Card Parsing** - Extract provider, policy number, and expiry dates
- **MVR Document Processing** - Parse Motor Vehicle Records for license details

### **User Management & Roles**
- **Multi-Role System**: Car Owners, Customers, and Administrators
- **KYC Identity Verification** - Persona API integration for compliance
- **Profile Management** - Complete user profile system with authentication
- **Role-Based Access Control** - Different permissions and features per role

### **Communication System**
- **Real-Time Chat** - Instant messaging between car owners and customers
- **Vehicle-Specific Conversations** - Chat linked to specific vehicle listings
- **AI Chatbot** - Intelligent FAQ assistant using Prism/Groq LLaMA models
- **Broadcasting** - Live message delivery with Laravel Broadcasting
- **Message History** - Complete conversation tracking with read status

### **AI & Machine Learning**
- **Google Cloud Vision API** - Enterprise-grade OCR and text detection
- **Computer Vision Processing** - Advanced image analysis capabilities
- **AI Chatbot** - Conversational AI for user support and FAQ
- **Pattern Recognition** - Custom algorithms for various document types
- **Python Integration** - Enhanced visual analysis for fuel gauge reading

### **Cloud & Third-Party Integrations**
- **Cloudinary CDN** - Scalable image storage, optimization, and delivery
- **NHTSA API** - Vehicle specifications and VIN decoding
- **Persona KYC API** - Identity verification and compliance
- **Pusher Broadcasting** - Real-time WebSocket communication
- **Tesseract OCR** - Local OCR processing capabilities

---

## Quick Start

### Prerequisites

- **PHP** >= 8.2 
- **Composer** 2.x
- **Node.js** >= 18.x & **npm**
- **MySQL** >= 8.0 or **PostgreSQL** >= 13
- **Google Cloud Vision API** credentials
- **Cloudinary** account
- **Persona API** account (for KYC)
- **Pusher** account (for real-time features)

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
   npm run build
   ```

3. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Database setup**
   ```bash
   # Create database and run migrations
   php artisan migrate
   
   # Optional: Seed with sample data
   php artisan db:seed
   ```

5. **Configure services** (Edit `.env` file)
   ```bash
   # Google Cloud Vision
   GOOGLE_APPLICATION_CREDENTIALS=path/to/service-account.json
   
   # Cloudinary
   CLOUDINARY_URL=cloudinary://api_key:api_secret@cloud_name
   CLOUDINARY_CLOUD_NAME=your_cloud_name
   CLOUDINARY_API_KEY=your_api_key
   CLOUDINARY_API_SECRET=your_api_secret
   
   # Persona KYC
   PERSONA_API_URL=https://withpersona.com/api/v1
   PERSONA_API_KEY=your_persona_api_key
   PERSONA_TEMPLATE_ID=your_template_id
   PERSONA_WEBHOOK_SECRET=your_webhook_secret
   
   # Pusher (for real-time chat)
   PUSHER_APP_ID=your_app_id
   PUSHER_APP_KEY=your_app_key
   PUSHER_APP_SECRET=your_app_secret
   PUSHER_APP_CLUSTER=your_cluster
   
   # AI Chatbot (Prism/Groq)
   GROQ_API_KEY=your_groq_api_key
   ```

6. **Start the application**
   ```bash
   # Development server
   php artisan serve
   
   # Or use the built-in dev command
   composer run dev
   ```

7. **Access the application**
   - **Main Application**: http://localhost:8000
   - **Upload Interface**: http://localhost:8000/upload
   - **Dashboard**: http://localhost:8000/dashboard (requires login)

---

## Application Features

### **Authentication & User Management**

#### User Roles
- **Car Owners**: Upload vehicles, manage listings, receive customer inquiries
- **Customers**: Browse vehicles, initiate conversations with owners
- **Administrators**: Platform management and oversight

#### Authentication Features
- User registration and login (Laravel Breeze)
- Email verification required
- Password reset functionality
- Profile management (edit/delete)
- Role-based dashboard customization

#### KYC Identity Verification
- **Persona API Integration**: Professional identity verification
- **Document Upload**: ID verification with photo matching
- **Compliance Tracking**: Complete audit trail
- **Status Monitoring**: Real-time verification status updates
- **Webhook Processing**: Automated status updates

### **Vehicle Management System**

#### Image Processing & Analysis
| Image Type | Purpose | AI Analysis |
|------------|---------|-------------|
| `front` | Front vehicle view | License plate detection, damage assessment |
| `rear` | Rear vehicle view | License plate detection, rear damage |
| `left` | Left side profile | Side damage, door condition |
| `right` | Right side profile | Side damage, door condition |
| `interior_front` | Front seats/dashboard | Interior condition, wear assessment |
| `interior_rear` | Back seats | Interior condition, space assessment |
| `dashboard` | Instrument cluster | Odometer reading, fuel level |
| `license_close` | License plate close-up | Enhanced plate OCR |
| `vin_area` | VIN location | VIN extraction and decoding |
| `insurance_card` | Insurance documentation | Policy details extraction |
| `mvr_document` | Motor Vehicle Record | License information parsing |

#### Advanced OCR Capabilities

**License Plate Detection**
```php
// Multiple pattern recognition
$patterns = [
    '/\b[A-Z]{3}-?\d{3,4}\b/',           // ABC-123 format
    '/\b\d{3,4}-?[A-Z]{3}\b/',          // 123-ABC format  
    '/\b[A-Z0-9]{2,3}-?[A-Z0-9]{3,4}\b/', // Mixed format
    '/\b[A-Z0-9]{6,8}\b/',              // Continuous format
    '/\b[A-Z]{2}\d{2}[A-Z]{3}\b/'       // EU format
];
```

**Odometer Reading**
- Digit recognition from analog and digital displays
- Mileage validation and formatting
- Multiple unit support (miles/kilometers)

**Fuel Level Detection**
- OCR text analysis for percentages and fractions
- Python-based visual gauge needle position detection
- Keyword matching ("Full", "Empty", "Half", "1/4", "3/4")

**VIN Processing**
- 17-character VIN extraction and validation
- NHTSA API integration for vehicle specifications
- Make, model, year, and detailed vehicle information

### **Communication System**

#### Real-Time Chat Features
- **Instant Messaging**: WebSocket-based real-time communication
- **Vehicle Context**: Conversations linked to specific vehicle listings
- **Message Types**: Text, images, file attachments
- **Read Status**: Message delivery and read confirmation
- **Conversation History**: Complete message history with search
- **Online Status**: User presence indicators

#### AI Chatbot Assistant
- **Prism Framework**: Advanced conversational AI
- **Groq LLaMA Integration**: State-of-the-art language model
- **Context Memory**: Maintains conversation context
- **FAQ Support**: Intelligent responses to common questions
- **Livewire Interface**: Reactive chat widget
- **Conversation Persistence**: Chat history storage

#### Broadcasting & Real-Time Updates
```php
// Real-time message broadcasting
broadcast(new MessageSent($message, $conversation))
    ->toOthers();
    
// Live presence tracking
Pusher::trigger('chat-channel', 'user-online', [
    'user_id' => $user->id,
    'status' => 'online'
]);
```
## 🛠️ API Documentation

### Vehicle Analysis API

#### Upload and Analyze Images
**Endpoint**: `POST /upload`

**Request**:
```bash
curl -X POST http://localhost:8000/upload \
  -H "Content-Type: multipart/form-data" \
  -F "images[front]=@front_view.jpg" \
  -F "images[rear]=@rear_view.jpg" \
  -F "images[dashboard]=@dashboard.jpg" \
  -F "images[vin_area]=@vin_area.jpg"
```

**Response**:
```json
{
  "success": true,
  "results": {
    "front": {
      "image_url": "https://res.cloudinary.com/demo/image/upload/v1/vehicles/front_abc123.jpg",
      "license_plate": "ABC-1234",
      "confidence": 0.98,
      "processing_time": "2.3s"
    },
    "dashboard": {
      "image_url": "https://res.cloudinary.com/demo/image/upload/v1/vehicles/dashboard_abc123.jpg",
      "odometer": "045,678",
      "fuel_level": "3/4 Full",
      "extracted_text": ["45678 Miles", "Fuel 3/4"],
      "confidence": 0.92
    },
    "vin_area": {
      "image_url": "https://res.cloudinary.com/demo/image/upload/v1/vehicles/vin_abc123.jpg",
      "vin": "1HGCM82633A123456",
      "vehicle_info": {
        "make": "Honda",
        "model": "Accord",
        "year": "2018",
        "body_type": "Sedan"
      }
    }
  },
  "processing_summary": {
    "total_images": 3,
    "successful": 3,
    "failed": 0,
    "total_time": "5.2s"
  }
}
```

### Chat API

#### Get Conversations
**Endpoint**: `GET /api/conversations`

**Response**:
```json
{
  "conversations": [
    {
      "id": 1,
      "car_id": 123,
      "car_owner": {
        "id": 10,
        "name": "John Doe"
      },
      "customer": {
        "id": 20,
        "name": "Jane Smith"
      },
      "last_message": {
        "content": "Is this car still available?",
        "created_at": "2024-01-15T10:30:00Z"
      },
      "unread_count": 2
    }
  ]
}
```

#### Send Message
**Endpoint**: `POST /api/conversations/{id}/messages`

**Request**:
```json
{
  "content": "Hello, I'm interested in this vehicle.",
  "type": "text"
}
```

**Response**:
```json
{
  "message": {
    "id": 456,
    "content": "Hello, I'm interested in this vehicle.",
    "user_id": 20,
    "conversation_id": 1,
    "created_at": "2024-01-15T10:35:00Z",
    "read_at": null
  }
}
```

### KYC Verification API

#### Create Verification Inquiry
**Endpoint**: `POST /persona/inquiry`

**Response**:
```json
{
  "inquiry_id": "inq_ABC123XYZ",
  "verification_url": "https://withpersona.com/verify?inquiry-id=inq_ABC123XYZ",
  "status": "created"
}
```

#### Check Verification Status
**Endpoint**: `GET /persona/status`

**Response**:
```json
{
  "kyc_status": "approved",
  "kyc_completed_at": "2024-01-15T09:15:00Z",
  "persona_inquiry_id": "inq_ABC123XYZ",
  "verification_details": {
    "document_verified": true,
    "selfie_verified": true,
    "address_verified": false
  }
}
```

---

## Configuration

### Service Account Setup

#### Google Cloud Vision API
1. Create a Google Cloud Project
2. Enable the Vision API
3. Create a service account
4. Download the JSON key file
5. Set `GOOGLE_APPLICATION_CREDENTIALS` to the file path

#### Cloudinary Setup
1. Sign up at [Cloudinary](https://cloudinary.com)
2. Get your cloud name, API key, and API secret
3. Configure the upload presets for vehicle images

#### Persona KYC Setup
1. Create account at [Persona](https://withpersona.com)
2. Set up verification templates
3. Configure webhook endpoints for status updates

---

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/feature`)
3. Make your changes
4. Add tests for new functionality
5. Ensure all tests pass
6. Commit your changes (`git commit -m 'Add feature'`)
7. Push to the branch (`git push origin feature/feature`)
8. Open a Pull Request

---

## Acknowledgments

*   **[Laravel](https://laravel.com) & [Livewire](https://livewire.laravel.com)** - The foundation of our application
*   **[Google Cloud Vision API](https://cloud.google.com/vision)** - Advanced OCR capabilities.
*   **[Cloudinary](https://cloudinary.com)** - Cloud-based image management
*   **[Pusher](https://pusher.com)** - Real-time WebSocket communication and instant notifications.
*   **[PrismPHP](https://prismphp.com/)** - Framework that seamlessly integrates powerful Large Language Models (LLaMA via Groq) to create our intelligent AI chatbot.
