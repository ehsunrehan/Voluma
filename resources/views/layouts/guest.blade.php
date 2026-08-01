<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="stylesheet" href="{{ asset('website/css/auth.css') }}">
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Styles -->
        @livewireStyles
    </head>
    <body>
        <div class="auth-page">
            {{ $slot }}
        </div>

        @livewireScripts

        <script>
    document.addEventListener('DOMContentLoaded', function () {
        function checkValue(input) {
            if (input.value.trim() !== '') {
                input.classList.add('has-value');
            } else {
                input.classList.remove('has-value');
            }
        }

        const inputs = document.querySelectorAll('.auth-field input');
        inputs.forEach(function (input) {
            checkValue(input);
            input.addEventListener('input', function () { checkValue(input); });
            input.addEventListener('change', function () { checkValue(input); });
            input.addEventListener('animationstart', function (e) {
                if (e.animationName === 'onAutoFillStart') checkValue(input);
            });
        });

        setInterval(function () {
            inputs.forEach(function (input) { checkValue(input); });
        }, 300);
    });
</script>
    </body>
</html>
