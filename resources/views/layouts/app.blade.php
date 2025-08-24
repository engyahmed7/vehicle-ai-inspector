<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'Laravel'))</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
        }

        .chat-widget {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 9999;
        }

        .chat-bubble {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .chat-bubble:hover {
            transform: scale(1.1);
            box-shadow: 0 12px 35px rgba(102, 126, 234, 0.5);
        }

        .chat-bubble::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            transform: rotate(45deg);
            animation: shimmer 3s infinite;
        }

        @keyframes shimmer {
            0% {
                transform: translateX(-100%) translateY(-100%) rotate(45deg);
            }

            50% {
                transform: translateX(100%) translateY(100%) rotate(45deg);
            }

            100% {
                transform: translateX(-100%) translateY(-100%) rotate(45deg);
            }
        }

        .chat-bubble i {
            color: white;
            font-size: 24px;
            z-index: 1;
            transition: all 0.3s ease;
        }

        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #ff4757;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.2);
            }

            100% {
                transform: scale(1);
            }
        }

        .chat-window {
            position: fixed;
            bottom: 90px;
            right: 20px;
            width: 380px;
            height: 550px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
            transform: scale(0) translateY(100px);
            opacity: 0;
            transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .chat-window.open {
            transform: scale(1) translateY(0);
            opacity: 1;
        }

        .chat-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .chat-header-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .chat-avatar {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .chat-title {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
        }

        .chat-subtitle {
            margin: 2px 0 0 0;
            font-size: 12px;
            opacity: 0.9;
        }

        .chat-close {
            background: none;
            border: none;
            color: white;
            font-size: 18px;
            cursor: pointer;
            padding: 5px;
            border-radius: 50%;
            transition: background 0.3s ease;
        }

        .chat-close:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .chat-messages {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            background: #f8fafc;
        }

        .message-item {
            animation: messageSlide 0.4s ease-out;
            margin-bottom: 16px;
        }

        @keyframes messageSlide {
            from {
                opacity: 0;
                transform: translateY(15px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .user-message {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 16px;
            border-radius: 18px;
            border-bottom-right-radius: 4px;
            display: inline-block;
            max-width: 80%;
            margin-left: auto;
            box-shadow: 0 2px 10px rgba(102, 126, 234, 0.3);
            font-size: 14px;
            line-height: 1.4;
        }

        .ai-message {
            background: white;
            color: #374151;
            padding: 12px 16px;
            border-radius: 18px;
            border-bottom-left-radius: 4px;
            display: inline-block;
            max-width: 80%;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            font-size: 14px;
            line-height: 1.4;
            border: 1px solid #e5e7eb;
        }

        .user-row {
            text-align: right;
            margin-bottom: 4px;
        }

        .ai-row {
            text-align: left;
            margin-bottom: 4px;
        }

        .divider {
            border: none;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(102, 126, 234, 0.1), transparent);
            margin: 16px 0;
        }

        .chat-input-area {
            padding: 20px !important;
            background: white !important;
            border-top: 1px solid #e5e7eb !important;
        }

        .chat-form {
            display: flex !important;
            gap: 12px !important;
            align-items: center !important;
        }

        .chat-input {
            flex: 1 !important;
            padding: 12px 16px !important;
            border: 2px solid #e5e7eb !important;
            border-radius: 25px !important;
            font-size: 14px !important;
            transition: all 0.3s ease !important;
            background: #f8fafc !important;
        }

        .chat-input:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .send-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 16px;
            border-radius: 50%;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 2px 10px rgba(102, 126, 234, 0.3);
            width: 44px;
            height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .send-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }

        .send-btn:active {
            transform: translateY(0);
        }

        .send-btn .loading-spinner {
            display: none;
        }

        .send-btn.loading .loading-spinner {
            display: inline-block;
        }

        .send-btn.loading .send-icon {
            display: none;
        }

        /* Scrollbar */
        .chat-messages::-webkit-scrollbar {
            width: 4px;
        }

        .chat-messages::-webkit-scrollbar-track {
            background: transparent;
        }

        .chat-messages::-webkit-scrollbar-thumb {
            background: rgba(102, 126, 234, 0.3);
            border-radius: 2px;
        }

        @media (max-width: 480px) {
            .chat-window {
                width: calc(100vw - 40px);
                height: calc(100vh - 140px);
                right: 20px;
                left: 20px;
                bottom: 90px;
            }
        }

        .welcome-message {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            padding: 16px;
            border-radius: 12px;
            margin-bottom: 20px;
            text-align: center;
        }

        .welcome-message h3 {
            margin: 0 0 8px 0;
            font-size: 16px;
            font-weight: 600;
        }

        .welcome-message p {
            margin: 0;
            font-size: 14px;
            opacity: 0.9;
        }
    </style>


    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100">
        @include('layouts.navigation')

        <!-- Page Heading -->
        @isset($header)
            <header class="bg-white shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endisset

        <!-- Page Content -->
        <main>
            {{ $slot }}
        </main>
    </div>
    <livewire:chatbot />


    @livewireScripts
</body>

</html>
