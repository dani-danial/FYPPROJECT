<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="stylesheet" href="https://runtracker.fun/public/build/assets/app-YR1x179d.css">
        <script type="module" src="https://runtracker.fun/public/build/assets/app-OANExTJh.js"></script>
    </head>
    <body class="font-sans text-gray-200 antialiased bg-[#0a0a0a]">
        
        <div class="fixed inset-0 z-0">
            <img src="{{ asset('public/images/backgrounds/login-bg.jpg') }}" alt="Background" 
                class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-black/60"></div>
        </div>

        <div class="relative z-10 min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0">
            <div>
                <a href="/">
                  
                    <div class="w-24 h-24 rounded-2xl flex items-center justify-center overflow-hidden shadow-2xl shadow-[#6b6b4b]/30 border-2 border-[#6b6b4b]">
                        <img src="{{ asset('public/images/logo.png') }}" alt="RunTracker Logo" class="w-full h-full object-cover">
                    </div>
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-8 py-8 bg-[#1a1a1a] border border-[#2a2a2a] shadow-2xl overflow-hidden sm:rounded-2xl">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>