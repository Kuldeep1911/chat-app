<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Real-Time Chat App</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts (Vite) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-gray-100 flex items-center justify-center min-h-screen">

    <div class="max-w-3xl w-full px-6 py-12 text-center">
        <!-- Logo / Icon -->
        <div class="flex justify-center mb-6">
            <div class="bg-indigo-600 text-white p-4 rounded-full shadow-lg">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                </svg>
            </div>
        </div>

        <h1 class="text-4xl font-extrabold text-gray-900 mb-4 tracking-tight">
            Welcome to Live Chat
        </h1>
        <p class="text-lg text-gray-600 mb-8">
            Connect with people instantly using our blazing fast WebSockets powered by Laravel Reverb.
        </p>

        <!-- Authentication Links -->
        <div class="flex justify-center space-x-4">
            @if (Route::has('login'))
                @auth
                    <!-- If User is already logged in -->
                    <a href="{{ url('/dashboard') }}" class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 shadow-md transition transform hover:-translate-y-1">
                        Go to Dashboard
                    </a>
                    <a href="{{ route('chat.index') }}" class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 shadow-md transition transform hover:-translate-y-1">
                        Open Chat 💬
                    </a>
                @else
                    <!-- If User is NOT logged in -->
                    <a href="{{ route('login') }}" class="inline-flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 shadow-md transition transform hover:-translate-y-1">
                        Log in
                    </a>

                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="inline-flex items-center justify-center px-8 py-3 border border-indigo-600 text-base font-medium rounded-md text-indigo-600 bg-white hover:bg-indigo-50 shadow-md transition transform hover:-translate-y-1">
                            Register
                        </a>
                    @endif
                @endauth
            @endif
        </div>
        
        <div class="mt-12 text-sm text-gray-400">
            Powered by Laravel 13 & Reverb
        </div>
    </div>

</body>
</html>