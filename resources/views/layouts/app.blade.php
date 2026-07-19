<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'Voluma'))</title>

    <!-- Fonts: Space Grotesk + Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('website/css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('website/css/navbar.css') }}">
    <link rel="stylesheet" href="{{ asset('website/css/hero.css') }}">
    <link rel="stylesheet" href="{{ asset('website/css/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('website/css/footer.css') }}">
    <link rel="stylesheet" href="{{ asset('website/css/responsive.css') }}">

    @livewireStyles
    @stack('styles')
</head>
<body>
    <div class="app">
        @include('partials.navbar')

        <main>
            @yield('content')
        </main>

        @include('partials.footer')
    </div>

    @livewireScripts
    <script src="{{ asset('website/js/app.js') }}"></script>
    <script src="{{ asset('website/js/navbar.js') }}"></script>
    @stack('scripts')
</body>
</html>