<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        @php
            $settings = \App\Models\Setting::first();
            $appTitle = $settings?->app_title ?? 'Unit Pembinaan Anggota';
            $favicon = $settings?->favicon_path ? asset('storage/' . $settings->favicon_path) : asset('favicon.ico');
        @endphp
        <title inertia>{{ $appTitle }}</title>
        <link rel="icon" type="image/x-icon" href="{{ $favicon }}">

        <!-- Google Fonts (Inter and Outfit for premium typography per guidelines) -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@350;400;500;600;700;800&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @inertiaHead
    </head>
    <body class="font-sans antialiased bg-[#FAFAF9] text-gray-800">
        @inertia
    </body>
</html>
