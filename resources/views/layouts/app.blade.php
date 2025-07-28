<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @yield('styles')

    @yield('scripts')


    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Vehicle Image Analysis')</title>
    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <!-- <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet"> -->
    <script src="https://cdn.plaid.com/link/v2/stable/link-initialize.js"></script>

    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>

<body>
    <div id="app">
      
        <main class="py-4">
            @yield('content')
        </main>
    </div>
</body>

</html>