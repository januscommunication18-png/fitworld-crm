<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', $host->studio_name ?? config('app.name', 'FitCRM'))</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* Smooth scrolling */
        html { scroll-behavior: smooth; }

        /* Fixed width container - 1140px */
        .container-fixed {
            width: 1140px;
            max-width: 100%;
            margin-left: auto;
            margin-right: auto;
            padding-left: 1rem;
            padding-right: 1rem;
        }

        /* Custom gradient for hero */
        .hero-gradient {
            background: linear-gradient(135deg, oklch(var(--p)) 0%, oklch(var(--s)) 100%);
        }

        /* Card hover effect */
        .card-hover {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .card-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px -5px rgba(0, 0, 0, 0.1);
        }

        /* Fade in animation */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .animate-fade-in {
            animation: fadeInUp 0.5s ease forwards;
        }

        /* Gallery auto-scroll animation */
        @keyframes scrollGallery {
            0% { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }
        .gallery-scroll-container {
            mask-image: linear-gradient(to right, transparent, black 5%, black 95%, transparent);
            -webkit-mask-image: linear-gradient(to right, transparent, black 5%, black 95%, transparent);
        }
        .animate-scroll {
            animation: scrollGallery 30s linear infinite;
        }
        .animate-scroll:hover,
        .animate-scroll.paused {
            animation-play-state: paused;
        }
        .gallery-slide {
            position: relative;
        }
    </style>
</head>
<body class="bg-base-100 min-h-screen flex flex-col antialiased">
    {{-- Main Content --}}
    <main class="flex-1">
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="py-6 text-center border-t border-base-200">
        <p class="text-sm text-base-content/50">
            Powered by <a href="{{ config('app.url') }}" class="font-medium text-primary hover:underline">FitCRM</a>
        </p>
    </footer>

    @stack('scripts')
</body>
</html>
